<?php
/**
 * @file
 * Optional AI review layer for the Avalanche Forecast Tool.
 *
 * A secondary, opt-in quality check: it sends a SAVED forecast to the Anthropic
 * Messages API and returns writing/consistency suggestions for the forecaster to
 * consider. It reviews and suggests only — it never sets or changes the danger
 * rating or the avalanche problems. The deterministic wizard is what delivers
 * rating consistency; this layer just helps polish the written product.
 *
 * Raw HTTPS is used deliberately: this Backdrop 1.x distribution has no
 * per-module Composer autoloading, so bundling the official PHP SDK + its
 * dependencies is impractical (the same reason the twilio integration is
 * vendored thin). Model/version defaults follow current Anthropic guidance.
 */

define('AVALANCHE_FORECAST_TOOL_API_URL', 'https://api.anthropic.com/v1/messages');
define('AVALANCHE_FORECAST_TOOL_API_VERSION', '2023-06-01');
define('AVALANCHE_FORECAST_TOOL_DEFAULT_MODEL', 'claude-opus-5');

/**
 * Resolves the API key: settings.php wins over stored config (keeps the secret
 * out of exported config when the operator prefers that).
 */
function avalanche_forecast_tool_api_key() {
  $key = settings_get('avalanche_forecast_tool_api_key', '');
  if (!$key) {
    $key = (string) config_get('avalanche_forecast_tool.settings', 'ai_api_key');
  }
  return trim($key);
}

/**
 * Whether AI review is enabled AND configured.
 */
function avalanche_forecast_tool_ai_ready() {
  return config_get('avalanche_forecast_tool.settings', 'enable_ai')
    && avalanche_forecast_tool_api_key() !== '';
}

/**
 * Access callback for the per-node "AI Review" tab.
 */
function avalanche_forecast_tool_ai_review_access($node) {
  return is_object($node)
    && $node->type === AVALANCHE_FORECAST_TOOL_TYPE
    && user_access('review forecasts with ai')
    && config_get('avalanche_forecast_tool.settings', 'enable_ai');
}

/**
 * Page callback: run (or offer to run) an AI review of a saved forecast.
 */
function avalanche_forecast_tool_ai_review_page($node) {
  $build = array();
  $build['#title'] = t('AI review: @title', array('@title' => $node->title));

  if (!avalanche_forecast_tool_ai_ready()) {
    $build['notice'] = array(
      '#markup' => '<div class="messages warning">' . t('AI review is enabled but no API key is configured. Add one on the <a href="@url">forecast tool settings page</a>.', array('@url' => url('admin/config/system/avalanche-forecast-tool'))) . '</div>',
    );
    return $build;
  }

  $build['intro'] = array(
    '#markup' => '<p>' . t('This sends the saved forecast to an AI model for a writing and consistency review. The suggestions below are advisory only — they do not change your forecast, and the danger rating is always yours to set. The forecast text is sent to the external AI service.') . '</p>',
  );

  // Run on submit of the button (simple POST form), so a plain page view does
  // not fire an API call automatically.
  $build['form'] = backdrop_get_form('avalanche_forecast_tool_ai_review_form', $node);
  return $build;
}

/**
 * Simple form with a "Review this forecast" button + results area.
 */
function avalanche_forecast_tool_ai_review_form($form, &$form_state, $node) {
  // Ensure this include is loaded on the submit rebuild (the submit handler
  // and API client live here, not in the .module).
  form_load_include($form_state, 'inc', 'avalanche_forecast_tool', 'avalanche_forecast_tool.ai');
  $form_state['aft_node'] = $node;
  $form['actions'] = array('#type' => 'actions');
  $form['actions']['review'] = array(
    '#type' => 'submit',
    '#value' => t('Review this forecast with AI'),
  );
  if (!empty($form_state['aft_review_html'])) {
    $form['result'] = array(
      '#markup' => '<div class="aft-ai-review">' . $form_state['aft_review_html'] . '</div>',
      '#weight' => 10,
    );
  }
  if (!empty($form_state['aft_review_error'])) {
    $form['error'] = array(
      '#markup' => '<div class="messages error">' . check_plain($form_state['aft_review_error']) . '</div>',
      '#weight' => 10,
    );
  }
  return $form;
}

/**
 * Submit handler: assemble the forecast, call Claude, stash the rendered result.
 */
function avalanche_forecast_tool_ai_review_form_submit($form, &$form_state) {
  $node = $form_state['aft_node'];
  $summary = avalanche_forecast_tool_forecast_summary($node);
  $result = avalanche_forecast_tool_run_review($summary);
  if (!empty($result['error'])) {
    $form_state['aft_review_error'] = $result['error'];
  }
  else {
    // The model returns plain text with simple structure; present it safely.
    $form_state['aft_review_html'] = _filter_autop(check_plain($result['text']));
  }
  $form_state['rebuild'] = TRUE;
}

/**
 * Builds a plain-text summary of a saved forecast for the model to review.
 */
function avalanche_forecast_tool_forecast_summary($node) {
  $lang = LANGUAGE_NONE;
  $get = function ($field) use ($node, $lang) {
    $items = field_get_items('node', $node, $field);
    if (!$items) {
      return '';
    }
    $out = array();
    foreach ($items as $it) {
      if (isset($it['safe_value'])) {
        $out[] = trim(strip_tags($it['safe_value']));
      }
      elseif (isset($it['value'])) {
        $out[] = trim(strip_tags((string) $it['value']));
      }
    }
    return implode(', ', array_filter($out));
  };
  // Map a list field's stored key to its human label.
  $label = function ($field, $key) {
    if ($key === '') {
      return '';
    }
    $info = field_info_field($field);
    $allowed = isset($info['settings']['allowed_values']) ? $info['settings']['allowed_values'] : array();
    return isset($allowed[$key]) ? $allowed[$key] : $key;
  };

  $lines = array();
  $lines[] = t('Title: @t', array('@t' => $node->title));
  $bl = $get('field_bottom_line');
  if ($bl) {
    $lines[] = t('Bottom line: @t', array('@t' => $bl));
  }
  // field_overalldanger stores the integer key; map it to its label.
  $overall = $label('field_overalldanger', $get('field_overalldanger'));
  if ($overall !== '') {
    $lines[] = t('Overall danger: @t', array('@t' => $overall));
  }
  $bands = array(
    'field_danger_rating_1' => t('Above Treeline'),
    'field_danger_rating_2' => t('Near Treeline'),
    'field_danger_rating_3' => t('Below Treeline'),
  );
  foreach ($bands as $field => $bandlabel) {
    $v = $get($field);
    if ($v !== '') {
      $lines[] = t('@band danger: @v', array('@band' => $bandlabel, '@v' => $v));
    }
  }
  // Problems 1..3.
  for ($i = 1; $i <= 3; $i++) {
    $type = $label('field_type_' . $i, $get('field_type_' . $i));
    if ($type === '' || strtolower($type) === 'normal caution') {
      continue;
    }
    $like = $label('field_likelihood_' . $i, $get('field_likelihood_' . $i));
    $size = $label('field_size_' . $i, $get('field_size_' . $i));
    $lines[] = t('Problem @n: @type (likelihood: @l, size: @s)', array(
      '@n' => $i, '@type' => $type, '@l' => $like ?: t('n/a'), '@s' => $size ?: t('n/a'),
    ));
  }
  $disc = $get('field_text_discussion');
  if ($disc) {
    $lines[] = t('Discussion: @t', array('@t' => $disc));
  }
  return implode("\n", $lines);
}

/**
 * Calls the model to review a forecast summary. Returns array(text|error).
 */
function avalanche_forecast_tool_run_review($summary) {
  $system = avalanche_forecast_tool_review_system_prompt();
  $user = t("Please review this avalanche forecast and give concise, actionable suggestions. Do not rewrite it wholesale and do not change the danger rating.\n\n@summary", array('@summary' => $summary));
  return avalanche_forecast_tool_call_claude($system, $user);
}

/**
 * The reviewer's system prompt: NAPADS + USFS writing-guidance rules.
 */
function avalanche_forecast_tool_review_system_prompt() {
  return t(implode("\n", array(
    'You are an experienced avalanche forecaster and editor reviewing a public avalanche forecast before it is published.',
    'Apply the North American / conceptual model of avalanche hazard (CMAH, Statham et al. 2018) and standard forecast writing guidance.',
    'Review for, and give specific suggestions on:',
    '1. Bottom line: is it short (about three sentences or fewer), clear, and does it say what the danger is, where the problem is, and what to do? Flag jargon and passive voice.',
    '2. Consistency: does the bottom line match the danger rating, the avalanche problems, and the implied travel advice? Flag contradictions.',
    '3. Sanity check: does the danger rating make sense given the problems, their likelihood and size? If it looks high or low, explain why and what to double-check — but never state a rating as the correct one.',
    '4. Problem selection: flag discouraged pairings (e.g. Storm Slab with Wind Slab; Persistent with Deep Persistent; Loose Wet with Wet Slab) unless the forecast distinguishes them clearly.',
    'Be concise and practical. Use short bullet points grouped under those headings. Do NOT rewrite the whole forecast, and do NOT tell the forecaster what the danger rating must be — you advise, the forecaster decides.',
  )));
}

/**
 * Thin raw-HTTPS client for the Anthropic Messages API.
 *
 * Non-streaming with a generous timeout: the review output is bounded, and the
 * whole result is returned to the page at once. Returns array('text' => ...) on
 * success or array('error' => ...) on failure.
 */
function avalanche_forecast_tool_call_claude($system, $user_message) {
  $key = avalanche_forecast_tool_api_key();
  if ($key === '') {
    return array('error' => t('No API key configured.'));
  }
  $model = config_get('avalanche_forecast_tool.settings', 'ai_model');
  if (!$model) {
    $model = AVALANCHE_FORECAST_TOOL_DEFAULT_MODEL;
  }

  $body = array(
    'model' => $model,
    'max_tokens' => 2500,
    // Adaptive thinking per current guidance (budget_tokens is rejected on
    // Opus 5); the response's text blocks are what we surface.
    'thinking' => array('type' => 'adaptive'),
    'system' => $system,
    'messages' => array(
      array('role' => 'user', 'content' => $user_message),
    ),
  );

  $ch = curl_init(AVALANCHE_FORECAST_TOOL_API_URL);
  curl_setopt_array($ch, array(
    CURLOPT_POST => TRUE,
    CURLOPT_RETURNTRANSFER => TRUE,
    CURLOPT_TIMEOUT => 180,
    CURLOPT_CONNECTTIMEOUT => 15,
    CURLOPT_HTTPHEADER => array(
      'Content-Type: application/json',
      'x-api-key: ' . $key,
      'anthropic-version: ' . AVALANCHE_FORECAST_TOOL_API_VERSION,
    ),
    CURLOPT_POSTFIELDS => json_encode($body),
  ));
  $raw = curl_exec($ch);
  $errno = curl_errno($ch);
  $http = curl_getinfo($ch, CURLINFO_HTTP_CODE);
  $curlerr = curl_error($ch);
  curl_close($ch);

  if ($errno) {
    watchdog('avalanche_forecast_tool', 'AI review request failed: @e', array('@e' => $curlerr), WATCHDOG_ERROR);
    return array('error' => t('Could not reach the AI service (@e).', array('@e' => $curlerr)));
  }
  $data = json_decode($raw, TRUE);
  if ($http !== 200) {
    $msg = isset($data['error']['message']) ? $data['error']['message'] : ('HTTP ' . $http);
    watchdog('avalanche_forecast_tool', 'AI review API error @h: @m', array('@h' => $http, '@m' => $msg), WATCHDOG_ERROR);
    return array('error' => t('The AI service returned an error: @m', array('@m' => $msg)));
  }

  // Concatenate all text blocks (skip any thinking blocks).
  $text = '';
  if (!empty($data['content']) && is_array($data['content'])) {
    foreach ($data['content'] as $block) {
      if (isset($block['type']) && $block['type'] === 'text' && isset($block['text'])) {
        $text .= $block['text'];
      }
    }
  }
  if ($text === '') {
    return array('error' => t('The AI service returned no review text.'));
  }
  return array('text' => $text);
}
