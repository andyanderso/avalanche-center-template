<?php
/**
 * @file
 * Page callbacks for the Avalanche Forecast Tool wizard.
 */

/**
 * Page callback: the guided wizard.
 *
 * The UI is built client-side from the settings data; this callback renders the
 * mount point and attaches the assets + configuration.
 */
function avalanche_forecast_tool_wizard_page() {
  $path = backdrop_get_path('module', 'avalanche_forecast_tool');

  backdrop_add_css($path . '/css/wizard.css');
  backdrop_add_js($path . '/js/wizard.js');
  backdrop_add_js(array('avalancheForecastTool' => avalanche_forecast_tool_settings_data()), 'setting');

  // Intro copy is server-rendered so it goes through the theme + translation.
  $intro = '<p class="aft-intro">'
    . t('This tool walks you through each avalanche problem — where it is, how sensitive and how widespread it is (which sets the likelihood), and how big it could get — then shows a hazard chart and a suggested danger rating for each elevation band. Nothing is saved until you approve it and review the forecast form.')
    . '</p>';

  // Full danger-scale reference table at the bottom (same table + preset +
  // language as the /avalanche-danger-scale page). Server-rendered so it sits
  // outside the JS-managed wizard mount.
  $scale = avalanche_forecast_tool_danger_scale_table();
  $scale_html = '';
  if ($scale !== '') {
    $scale_html = '<section class="aft-card aft-scale-full">'
      . '<h2>' . t('Danger scale') . '</h2>'
      . '<div class="aft-scale-table">' . $scale . '</div>'
      . '</section>';
  }

  return '<div class="aft-wizard-intro">' . $intro . '</div>'
    . '<div id="aft-wizard" class="aft-wizard"><noscript>'
    . t('The guided forecast tool needs JavaScript enabled.')
    . '</noscript></div>'
    . $scale_html;
}

/**
 * Page callback: stash an approved wizard payload, then hand back the add-form
 * URL for the JS to redirect to.
 *
 * Expects a POST with a JSON 'payload' and the module token. Stores the
 * sanitised payload in the session; the advisory add-form alter picks it up.
 */
function avalanche_forecast_tool_apply() {
  if (empty($_POST['token']) || !backdrop_valid_token($_POST['token'], 'avalanche_forecast_tool')) {
    backdrop_json_output(array('error' => 'invalid token'));
    backdrop_exit();
  }

  $raw = isset($_POST['payload']) ? $_POST['payload'] : '';
  $data = json_decode($raw, TRUE);
  if (!is_array($data)) {
    backdrop_json_output(array('error' => 'bad payload'));
    backdrop_exit();
  }

  $_SESSION[AVALANCHE_FORECAST_TOOL_PENDING] = avalanche_forecast_tool_sanitize_payload($data);

  backdrop_json_output(array('redirect' => url('node/add/advisory')));
  backdrop_exit();
}

/**
 * Whitelists a wizard payload down to the field values the prefill JS uses.
 *
 * Never trust client JSON: keep only known keys, coerce to expected shapes.
 */
function avalanche_forecast_tool_sanitize_payload(array $data) {
  $out = array('problems' => array(), 'suggestions' => array(), 'notes' => array());

  if (!empty($data['problems']) && is_array($data['problems'])) {
    foreach (array_slice($data['problems'], 0, 3) as $p) {
      $out['problems'][] = array(
        'type' => isset($p['type']) ? preg_replace('/[^0-9]/', '', (string) $p['type']) : '',
        'likelihood' => isset($p['likelihood']) ? preg_replace('/[^0-9]/', '', (string) $p['likelihood']) : '',
        // Size stored as a range [min,max]; the field takes a single key, so
        // the prefill uses the upper size. Keep both for display.
        'sizeMin' => isset($p['sizeMin']) ? preg_replace('/[^0-9]/', '', (string) $p['sizeMin']) : '',
        'sizeMax' => isset($p['sizeMax']) ? preg_replace('/[^0-9]/', '', (string) $p['sizeMax']) : '',
        // Rose cells: list of "band:aspectIndex" e.g. "above:0".
        'rose' => avalanche_forecast_tool_clean_rose(isset($p['rose']) ? $p['rose'] : array()),
      );
    }
  }

  // Suggested danger per band + overall (display hints only, never written).
  if (!empty($data['suggestions']) && is_array($data['suggestions'])) {
    foreach (array('above', 'near', 'below', 'overall') as $band) {
      if (isset($data['suggestions'][$band])) {
        $out['suggestions'][$band] = check_plain((string) $data['suggestions'][$band]);
      }
    }
  }

  // Free-text justification captured for discouraged pairings.
  if (!empty($data['notes']) && is_array($data['notes'])) {
    foreach ($data['notes'] as $note) {
      $out['notes'][] = check_plain((string) $note);
    }
  }

  return $out;
}

/**
 * Cleans a rose cell list to "band:index" strings with a valid band + 0–7 index.
 */
function avalanche_forecast_tool_clean_rose($rose) {
  $clean = array();
  if (!is_array($rose)) {
    return $clean;
  }
  $bands = array('above', 'near', 'below');
  foreach ($rose as $cell) {
    $parts = explode(':', (string) $cell);
    if (count($parts) === 2 && in_array($parts[0], $bands, TRUE)) {
      $idx = (int) $parts[1];
      if ($idx >= 0 && $idx <= 7) {
        $clean[] = $parts[0] . ':' . $idx;
      }
    }
  }
  return array_values(array_unique($clean));
}
