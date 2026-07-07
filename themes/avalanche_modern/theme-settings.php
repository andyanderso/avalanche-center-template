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
}
