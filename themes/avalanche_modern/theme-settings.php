<?php
/**
 * @file
 * Theme setting callbacks for the avalanche_modern theme.
 */

/**
 * Implements hook_form_system_theme_settings_alter().
 *
 * Adds GUI options for the avalanche advisory form and published advisory
 * page. (The site name/slogan/logo are shown by the Header block placed in the
 * layout, and toggled in that block's own settings — not here.)
 */
function avalanche_modern_form_system_theme_settings_alter(&$form, &$form_state) {
  $form['advisory_form'] = array(
    '#type' => 'fieldset',
    '#title' => t('Forecast form'),
    '#description' => t('Options for the avalanche forecast create/edit form.'),
    '#weight' => -18,
    '#collapsible' => TRUE,
    '#collapsed' => FALSE,
  );
  $form['advisory_form']['advisory_weather_expanded'] = array(
    '#type' => 'checkbox',
    '#title' => t('Expand the detailed-weather fields by default'),
    '#description' => t('When checked, the "Detailed weather data" group (current conditions and the two-day forecast tables) starts open on the advisory form. When unchecked, it starts collapsed.'),
    '#default_value' => theme_get_setting('advisory_weather_expanded'),
  );

  // Labels shown on the published advisory (and mirrored on the advisory
  // form). These are read by inc/advisory.inc and the advisory templates.
  $form['advisory_page'] = array(
    '#type' => 'fieldset',
    '#title' => t('Forecast page labels'),
    '#description' => t('Elevation-band and weather labels shown on the published avalanche forecast.'),
    '#weight' => -16,
    '#collapsible' => TRUE,
    '#collapsed' => FALSE,
  );
  $form['advisory_page']['upper_elevation_band'] = array(
    '#type' => 'textfield',
    '#title' => t('Upper elevation band'),
    '#default_value' => theme_get_setting('upper_elevation_band'),
    '#description' => t('Danger-rating label for the upper elevation band. Example: Above Treeline'),
  );
  $form['advisory_page']['middle_elevation_band'] = array(
    '#type' => 'textfield',
    '#title' => t('Middle elevation band'),
    '#default_value' => theme_get_setting('middle_elevation_band'),
    '#description' => t('Danger-rating label for the middle elevation band. Example: Near Treeline'),
  );
  $form['advisory_page']['lower_elevation_band'] = array(
    '#type' => 'textfield',
    '#title' => t('Lower elevation band'),
    '#default_value' => theme_get_setting('lower_elevation_band'),
    '#description' => t('Danger-rating label for the lower elevation band. Example: Below Treeline'),
  );
  $form['advisory_page']['wx_elevation_low'] = array(
    '#type' => 'textfield',
    '#title' => t('Lower weather-forecast elevation label'),
    '#default_value' => theme_get_setting('wx_elevation_low'),
    '#description' => t('Label for the lower band in the two-day mountain weather forecast. Example: 7,000&ndash;8,000 ft. Leave blank to omit the label.'),
  );
  $form['advisory_page']['wx_elevation_high'] = array(
    '#type' => 'textfield',
    '#title' => t('Upper weather-forecast elevation label'),
    '#default_value' => theme_get_setting('wx_elevation_high'),
    '#description' => t('Label for the upper band in the two-day mountain weather forecast. Example: 8,000&ndash;9,000 ft. Leave blank to omit the label.'),
  );
  $form['advisory_page']['current_wx_conditions_desc'] = array(
    '#type' => 'textfield',
    '#title' => t('Current weather conditions heading'),
    '#default_value' => theme_get_setting('current_wx_conditions_desc'),
    '#description' => t('Heading shown above the current-conditions table in the weather section.'),
  );
  $form['advisory_page']['weather_units'] = array(
    '#type' => 'select',
    '#title' => t('Weather units'),
    '#options' => array(
      'imperial' => t('Imperial (°F, mph, inches)'),
      'metric' => t('Metric (°C, km/h, cm)'),
    ),
    '#default_value' => theme_get_setting('weather_units') ? theme_get_setting('weather_units') : 'imperial',
    '#description' => t('Units appended to the numeric values in the mountain-weather tables.'),
  );
  $form['advisory_page']['local_nws_name'] = array(
    '#type' => 'textfield',
    '#title' => t('Local weather service name'),
    '#default_value' => theme_get_setting('local_nws_name'),
    '#description' => t('Named in the "produced in partnership with" line under the two-day forecast.'),
  );
  $form['advisory_page']['local_nws_url'] = array(
    '#type' => 'textfield',
    '#title' => t('Local weather service URL'),
    '#default_value' => theme_get_setting('local_nws_url'),
    '#description' => t('Link for the local weather service name above.'),
  );
}
