<?php
/**
 * @file
 * Theme setting callbacks for the avalanche_modern theme.
 */

/**
 * Implements hook_form_system_theme_settings_alter().
 *
 * Adds GUI toggles for the header site name and slogan. Backdrop moved the
 * core name/slogan/logo toggles into the Header block, so we expose theme-level
 * toggles here; avalanche_modern_preprocess_header() reads these settings.
 */
function avalanche_modern_form_system_theme_settings_alter(&$form, &$form_state) {
  $form['header_display'] = array(
    '#type' => 'fieldset',
    '#title' => t('Header display'),
    '#description' => t('Show or hide the site name and slogan text in the header. (The logo image is configured separately in the Header block / Appearance settings.)'),
    '#weight' => -20,
    '#collapsible' => TRUE,
    '#collapsed' => FALSE,
  );
  $form['header_display']['toggle_name'] = array(
    '#type' => 'checkbox',
    '#title' => t('Display site name'),
    '#default_value' => theme_get_setting('toggle_name'),
  );
  $form['header_display']['toggle_slogan'] = array(
    '#type' => 'checkbox',
    '#title' => t('Display site slogan'),
    '#default_value' => theme_get_setting('toggle_slogan'),
  );

  $form['advisory_form'] = array(
    '#type' => 'fieldset',
    '#title' => t('Advisory form'),
    '#description' => t('Options for the avalanche advisory create/edit form.'),
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
    '#title' => t('Advisory page labels'),
    '#description' => t('Elevation-band and weather labels shown on the published avalanche advisory.'),
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
