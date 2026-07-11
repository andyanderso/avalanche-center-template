<?php
/**
 * @file
 * Install profile hooks for the Avalanche Center distribution.
 *
 * Structural config (profiles/avalanche_center/config/) auto-imports during
 * module installation via Backdrop's own config_install_default_config(),
 * before any hook here runs. This file only handles the "Center setup" form
 * task and the site-specific values it collects.
 *
 * Ordering note: hook_install() (avalanche_center.install) runs during
 * profile/module installation, *before* hook_install_tasks() below is ever
 * shown to the user — so it cannot write the values this form collects,
 * despite AVALANCHE_CENTER_DISTRIBUTION_PLAN.md §9 originally describing
 * hook_install() as the place that does that. This form's own submit
 * handler does it instead, since it's the first point in the install where
 * those values actually exist. See plan §19 (Phase 6 log).
 */

/**
 * Implements hook_install_tasks().
 */
function avalanche_center_install_tasks(&$install_state) {
  return array(
    'avalanche_center_setup_form' => array(
      'display_name' => t('Center setup'),
      'type' => 'form',
    ),
  );
}

/**
 * Form builder: the "Center setup" install task.
 *
 * Collects exactly what plan §9 specifies: center name, language,
 * danger-scale preset, map center/zoom, NWS name/URL, social URLs.
 */
function avalanche_center_setup_form($form, &$form_state) {
  $form['center_name'] = array(
    '#type' => 'textfield',
    '#title' => t('Avalanche center name'),
    '#description' => t('Used as the site name and in page titles, e.g. "Example Avalanche Center".'),
    '#required' => TRUE,
    '#default_value' => 'Avalanche Center',
  );

  $form['language'] = array(
    '#type' => 'radios',
    '#title' => t('Language'),
    '#options' => array(
      'en' => t('English'),
      'es' => t('Spanish (Español)'),
    ),
    '#default_value' => 'en',
    '#description' => t('Sets the default text direction/locale preference. Full Spanish translation coverage ships separately as a .po file (see plan §10) - selecting Spanish here without one imported yet leaves interface text in English.'),
  );

  $form['danger_scale'] = array(
    '#type' => 'radios',
    '#title' => t('Danger-scale preset'),
    '#options' => array(
      'NAC' => t('North American (NAC)'),
      'SAC' => t('South American (SAC)'),
    ),
    '#default_value' => 'NAC',
    '#required' => TRUE,
    '#description' => t('Selects the danger-rating color palette and travel-advice text used by the danger map and legend. Individual colors/labels can be overridden later in the danger map settings.'),
  );

  $form['map'] = array(
    '#type' => 'fieldset',
    '#title' => t('Map defaults'),
    '#description' => t('Where the danger map centers by default, and a small demo forecast zone to get you started (delete it once you have real zones).'),
  );
  $form['map']['map_center_lat'] = array(
    '#type' => 'textfield',
    '#title' => t('Map center latitude'),
    '#default_value' => '0',
    '#size' => 15,
    '#required' => TRUE,
  );
  $form['map']['map_center_lng'] = array(
    '#type' => 'textfield',
    '#title' => t('Map center longitude'),
    '#default_value' => '0',
    '#size' => 15,
    '#required' => TRUE,
  );
  $form['map']['map_zoom'] = array(
    '#type' => 'textfield',
    '#title' => t('Map zoom level'),
    '#default_value' => '9',
    '#size' => 5,
    '#required' => TRUE,
  );

  $form['nws'] = array(
    '#type' => 'fieldset',
    '#title' => t('Weather service (optional)'),
    '#collapsible' => TRUE,
    '#collapsed' => TRUE,
  );
  $form['nws']['local_nws_name'] = array(
    '#type' => 'textfield',
    '#title' => t('Name of your local weather service/office'),
  );
  $form['nws']['local_nws_url'] = array(
    '#type' => 'textfield',
    '#title' => t('Local weather service URL'),
  );

  $form['social'] = array(
    '#type' => 'fieldset',
    '#title' => t('Social media (optional)'),
    '#collapsible' => TRUE,
    '#collapsed' => TRUE,
  );
  $form['social']['facebook_url'] = array(
    '#type' => 'textfield',
    '#title' => t('Facebook URL'),
  );
  $form['social']['twitter_url'] = array(
    '#type' => 'textfield',
    '#title' => t('Twitter URL'),
  );
  $form['social']['youtube_url'] = array(
    '#type' => 'textfield',
    '#title' => t('YouTube URL'),
  );
  $form['social']['instagram_url'] = array(
    '#type' => 'textfield',
    '#title' => t('Instagram URL'),
  );
  $form['social']['email_url'] = array(
    '#type' => 'textfield',
    '#title' => t('Email subscription page URL'),
  );

  $form['actions'] = array('#type' => 'actions');
  $form['actions']['submit'] = array(
    '#type' => 'submit',
    '#value' => t('Save and continue'),
  );

  return $form;
}

/**
 * Submit handler for avalanche_center_setup_form().
 *
 * Writes the collected values into avalanche_center.settings + the two
 * shipped themes' settings, and creates a tiny demo dataset (one forecast
 * region + one advisory) so the danger map isn't empty on first login.
 */
function avalanche_center_setup_form_submit($form, &$form_state) {
  $values = $form_state['values'];

  config_set('system.core', 'site_name', $values['center_name']);

  $settings = config('avalanche_center.settings');
  $settings->set('danger_scale', $values['danger_scale']);
  $settings->set('map_center_lat', $values['map_center_lat']);
  $settings->set('map_center_lng', $values['map_center_lng']);
  $settings->set('map_zoom', (int) $values['map_zoom']);
  $settings->set('language', $values['language']);
  $settings->save();

  // Populate both shipped themes' settings, not just whichever is
  // currently the default - a center may switch between them later and
  // shouldn't find the other one blank.
  $theme_keys = array('local_nws_name', 'local_nws_url', 'facebook_url',
    'twitter_url', 'youtube_url', 'instagram_url', 'email_url');
  foreach (array('avalanche_modern', 'responsive_sac') as $theme_name) {
    $theme_settings = config($theme_name . '.settings');
    foreach ($theme_keys as $key) {
      if (!empty($values[$key])) {
        $theme_settings->set($key, $values[$key]);
      }
    }
    $theme_settings->save();
  }

  avalanche_center_create_demo_content(
    (float) $values['map_center_lat'],
    (float) $values['map_center_lng']
  );
}

/**
 * Implements hook_form_FORM_ID_alter() for the observation node form.
 *
 * Removes the vestigial single-value "Region" field (field_region, whose only
 * allowed value is "Other") from the observation submission form. The field
 * is left in place for the several views that still reference it - only its
 * form widget is hidden.
 */
function avalanche_center_form_observation_node_form_alter(&$form, &$form_state, $form_id) {
  if (isset($form['field_region'])) {
    $form['field_region']['#access'] = FALSE;
  }
}
