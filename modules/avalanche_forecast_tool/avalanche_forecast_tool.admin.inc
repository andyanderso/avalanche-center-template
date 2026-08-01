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
  $form['mode']['require_guided'] = array(
    '#type' => 'checkbox',
    '#title' => t('Require the guided tool for new forecasts'),
    '#default_value' => (bool) $conf->get('require_guided'),
    '#description' => t('When off (default), forecasters see a prompt at the top of the forecast form offering the guided tool, and can still fill the form by hand. When on, the forecast form leads with the guided tool. Turn this on once your forecasters are comfortable with the tool.'),
  );

  // Phase 6 (AI review) settings are added here later, behind their own
  // fieldset and an enable toggle. Left out until that phase is built.

  return system_settings_form($form);
}
