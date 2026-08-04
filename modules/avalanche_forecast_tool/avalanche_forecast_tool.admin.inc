<?php
/**
 * @file
 * Settings form for the Avalanche Forecast Tool.
 */

/**
 * Forecast-tool settings.
 */
function avalanche_forecast_tool_settings_form($form, &$form_state) {
  $form['#config'] = 'avalanche_forecast_tool.settings';
  $conf = config('avalanche_forecast_tool.settings');

  $form['mode'] = array(
    '#type' => 'fieldset',
    '#title' => t('How the tool is offered'),
    '#collapsible' => FALSE,
  );
  $form['mode']['enabled'] = array(
    '#type' => 'checkbox',
    '#title' => t('Enable the guided forecast tool'),
    '#default_value' => avalanche_forecast_tool_enabled(),
    '#description' => t('When off, forecasters no longer see the guided-tool prompt on the forecast form and cannot open the wizard — the form works as a plain form. You can also toggle this straight from the forecast form. This is a lighter switch than disabling the whole module on the modules page.'),
  );
  $form['mode']['require_guided'] = array(
    '#type' => 'checkbox',
    '#title' => t('Require the guided tool for new forecasts'),
    '#default_value' => (bool) $conf->get('require_guided'),
    '#description' => t('When off (default), forecasters see a prompt at the top of the forecast form offering the guided tool, and can still fill the form by hand. When on, the forecast form leads with the guided tool. Turn this on once your forecasters are comfortable with the tool.'),
    '#states' => array('visible' => array(':input[name="enabled"]' => array('checked' => TRUE))),
  );

  $form['display'] = array(
    '#type' => 'fieldset',
    '#title' => t('Display'),
    '#collapsible' => FALSE,
  );
  $form['display']['units'] = array(
    '#type' => 'radios',
    '#title' => t('Measurement units'),
    '#options' => array(
      'metric' => t('Metric (m, t)'),
      'imperial' => t('Imperial (ft, tons)'),
      'both' => t('Both (metric with imperial in parentheses)'),
    ),
    '#default_value' => avalanche_forecast_tool_units(),
    '#description' => t('Units used where the tool shows measurements — e.g. the typical size (path length and mass) of each avalanche size class. Defaults from the site language (Spanish → metric, otherwise both) until you set it here.'),
  );

  $form['ai'] = array(
    '#type' => 'fieldset',
    '#title' => t('AI review (optional)'),
    '#collapsible' => TRUE,
    '#collapsed' => !$conf->get('enable_ai'),
    '#description' => t('Adds an optional "AI review" tab on each forecast that sends the saved forecast to an AI model for writing and consistency suggestions. It is advisory only — it never sets the danger rating or the avalanche problems. The forecast text is sent to the external AI service, and API usage is billed to the key you provide.'),
  );
  $form['ai']['enable_ai'] = array(
    '#type' => 'checkbox',
    '#title' => t('Enable AI review'),
    '#default_value' => (bool) $conf->get('enable_ai'),
  );
  $key_in_settings = settings_get('avalanche_forecast_tool_api_key', '');
  $form['ai']['ai_api_key'] = array(
    '#type' => 'textfield',
    '#title' => t('Anthropic API key'),
    '#default_value' => $conf->get('ai_api_key'),
    '#size' => 60,
    '#description' => $key_in_settings
      ? t('A key is currently set in settings.php, which takes precedence over anything entered here.')
      : t('Your Anthropic API key. For better security you can instead set it in settings.php as <code>$settings["avalanche_forecast_tool_api_key"]</code>, which then takes precedence and keeps it out of exported configuration.'),
    '#states' => array('visible' => array(':input[name="enable_ai"]' => array('checked' => TRUE))),
  );
  $form['ai']['ai_model'] = array(
    '#type' => 'textfield',
    '#title' => t('Model'),
    '#default_value' => $conf->get('ai_model') ?: 'claude-opus-5',
    '#size' => 30,
    '#description' => t('The Anthropic model to use. Defaults to the current most capable model.'),
    '#states' => array('visible' => array(':input[name="enable_ai"]' => array('checked' => TRUE))),
  );

  return system_settings_form($form);
}
