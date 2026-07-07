<?php

/**
 * @file
 * Theme setting callbacks for the responsive_sac theme.
 */

/**
 * Implements hook_form_FORM_ID_alter().
 *
 * @param $form
 *   The form.
 * @param $form_state
 *   The form state.
 */
function responsive_sac_settings_form_submit(&$form, $form_state) {
$bg_header_fid = $form_state['values']['backgroung_header_img'];
  $bg_header = file_load($bg_header_fid);
  if (is_object($bg_header)) {
    // Check to make sure that the file is set to be permanent.
    if ($bg_header->status == 0) {
      // Update the status.
      $bg_header->status = FILE_STATUS_PERMANENT;
      // Save the update.
      file_save($bg_header);
      // Add a reference to prevent warnings.
      file_usage_add($bg_header, 'responsive_sac', 'theme', 1);
     }
   }
$email_header_fid = $form_state['values']['email_header_img'];
  $email_header = file_load($email_header_fid);
  if (is_object($email_header)) {
    // Check to make sure that the file is set to be permanent.
    if ($email_header->status == 0) {
      // Update the status.
      $email_header->status = FILE_STATUS_PERMANENT;
      // Save the update.
      file_save($email_header);
      // Add a reference to prevent warnings.
      file_usage_add($email_header, 'responsive_sac', 'theme', 1);
     }
  }
}
 
function responsive_sac_form_system_theme_settings_alter(&$form, &$form_state) {
$form['#submit'][] = 'responsive_sac_settings_form_submit';

// Get all themes.
$themes = list_themes();
// Get the current theme
$active_theme = $GLOBALS['theme_key'];
$form_state['build_info']['files'][] = str_replace("/$active_theme.info", '', $themes[$active_theme]->filename) . '/theme-settings.php';

// Header display: GUI toggles for the site name and slogan. Backdrop moved the
// core name/slogan/logo toggles into the Header block, so expose them here;
// responsive_sac_preprocess_header() / _process_page() read these settings.
$form['header_display'] = array(
  '#type' => 'fieldset',
  '#title' => t('Header display'),
  '#description' => t('Show or hide the site name and slogan text in the header.'),
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
	
$form['backgroung_header_img'] = array(
    '#title' => t('Image for the Header background'),
    '#description' => t('This image will be used as the header background'),
    '#type' => 'managed_file',
    '#upload_location' => 'public://theme-backgrounds/',
    '#upload_validators' => array(
      'file_validate_extensions' => array('gif png jpg jpeg'),
    ),
    '#default_value' => theme_get_setting('backgroung_header_img'),
  );
$form['email_header_img'] = array(
    '#title' => t('Header Image for Emails'),
    '#description' => t('This image will be used as the header background for simplenews emails'),
    '#type' => 'managed_file',
    '#upload_location' => 'public://theme-backgrounds/',
    '#upload_validators' => array(
      'file_validate_extensions' => array('gif png jpg jpeg'),
    ),
    '#default_value' => theme_get_setting('email_header_img'),
  );
$form['forecaster_name_field'] = array(
    '#type' => 'textfield',
    '#title' => t('Machine name of the forecaster name field'),
    '#default_value' => theme_get_setting('forecaster_name_field'),
    '#description'   => t("This can be found in configuration->people->manage fields"),
  );
$form['map_first'] = array(
    '#type' => 'checkbox',
    '#title' => t('Show the map at the top of mobile views?'),
    '#default_value' => theme_get_setting('map_first'),
    '#description'   => t("Check this box to display the danger rating map at the top of the page on mobile devices."),
  );

//social links
 $form['social_media_links'] = array(
    '#type' => 'fieldset',
    '#title' => t('Social Media Links'),
    '#collapsible' => TRUE,
    '#collapsed' => FALSE,
  );
  $form['social_media_links']['email_url'] = array(
    '#type' => 'textfield',
    '#title' => t('Email Subscription Page URL'),
    '#default_value' => theme_get_setting('email_url'),
    '#description'   => t("Sets the url for the email subscription icon on the menu bar of the site."),
  );
  $form['social_media_links']['facebook_url'] = array(
    '#type' => 'textfield',
    '#title' => t('Facebook Page URL'),
    '#default_value' => theme_get_setting('facebook_url'),
    '#description'   => t("Sets the url for the Facebook icon to your facebook page."),
  );
  $form['social_media_links']['twitter_url'] = array(
    '#type' => 'textfield',
    '#title' => t('Twitter Page URL'),
    '#default_value' => theme_get_setting('twitter_url'),
    '#description'   => t("Sets the url for the Twitter icon to your twitter page."),
  );
  $form['social_media_links']['youtube_url'] = array(
    '#type' => 'textfield',
    '#title' => t('Youtube Page URL'),
    '#default_value' => theme_get_setting('youtube_url'),
    '#description'   => t("Sets the url for the Youtube icon to your youtube page."),
  );
  $form['social_media_links']['instagram_url'] = array(
    '#type' => 'textfield',
    '#title' => t('Instagram Page URL'),
    '#default_value' => theme_get_setting('instagram_url'),
    '#description'   => t("Sets the url for the Instagram icon to your instagram page."),
  );
//advisory page settings
$form['advisory_page_settings'] = array(
    '#type' => 'fieldset',
    '#title' => t('Advisory Page Settings'),
    '#collapsible' => TRUE,
    '#collapsed' => FALSE,
  );
  $form['advisory_page_settings']['show_forecast_region'] = array(
    '#type' => 'checkbox',
    '#title' => t('Show the forecast regions at the top of the advisory?'),
    '#default_value' => theme_get_setting('show_forecast_region'),
    '#description'   => t("Check this box to display the regions that the advisory covers at the top of the advisory. Useful if you have more than one forecast region."),
  );
    $form['advisory_page_settings']['enable_danger_rose_view'] = array(
    '#type' => 'radios',
    '#title' => t('Allow users to see an overall danger rose?'),
    '#options' => array(
         0 => t('Show just the elevation danger ratings'),
	 1 => t('Show just the Danger Rose view'),
         2 => t('Show both danger rose and the mountain/elevation view')),
    '#default_value' => theme_get_setting('enable_danger_rose_view'),
    '#description'   => t("Select whether to allow users to switch between a basic overall danger view by elevation and a danger rose view showing danger by aspect and elevation or just use one of those views. You must have a danger rose field available for the overall danger rose."),
  );
  $form['advisory_page_settings']['upper_elevation_band'] = array(
    '#type' => 'textfield',
    '#title' => t('Advisory upper elevation zone'),
    '#default_value' => theme_get_setting('upper_elevation_band'),
    '#description'   => t("Sets the upper elevation zone on the advisory page. Example: Above Treeline"),
  );
  $form['advisory_page_settings']['middle_elevation_band'] = array(
    '#type' => 'textfield',
    '#title' => t('Advisory middle elevation zone'),
    '#default_value' => theme_get_setting('middle_elevation_band'),
    '#description'   => t("Sets the middle elevation zone on the advisory page. Example: Near Treeline"),
  );
  $form['advisory_page_settings']['lower_elevation_band'] = array(
    '#type' => 'textfield',
    '#title' => t('Advisory lower elevation zone'),
    '#default_value' => theme_get_setting('lower_elevation_band'),
    '#description'   => t("Sets the lower elevation zone on the advisory page. Example: Lower Treeline"),
  );
  $form['advisory_page_settings']['local_nws_name'] = array(
    '#type' => 'textfield',
    '#title' => t('Name of your NWS office'),
    '#default_value' => theme_get_setting('local_nws_name'),
    '#description'   => t("Sets the name for you local NWS office on the advisory page."),
  );
  $form['advisory_page_settings']['local_nws_url'] = array(
    '#type' => 'textfield',
    '#title' => t('Local NWS Page URL'),
    '#default_value' => theme_get_setting('local_nws_url'),
    '#description'   => t("Sets the url for the link to the NWS in the weather section of the advisory page."),
  );
  $form['advisory_page_settings']['wx_elevation_low'] = array(
    '#type' => 'textfield',
    '#title' => t('Lower weather forecast elevation range'),
    '#default_value' => theme_get_setting('wx_elevation_low'),
    '#description'   => t("Sets the lower elevation zone for the weather forecast on the advisory page."),
  );
  $form['advisory_page_settings']['wx_elevation_high'] = array(
    '#type' => 'textfield',
    '#title' => t('Upper weather forecast elevation range'),
    '#default_value' => theme_get_setting('wx_elevation_high'),
    '#description'   => t("Sets the upper elevation zone for the weather forecast on the advisory page."),
  );
  $form['advisory_page_settings']['current_wx_conditions_desc'] = array(
    '#type' => 'textfield',
    '#title' => t('Current weather conditions text'),
    '#default_value' => theme_get_setting('current_wx_conditions_desc'),
    '#description'   => t("Sets the description for the current weather description part of the advisory. For example: 'Weather observations from along the ridgeline between 8,200 ft. and 8,800 ft.'"),
  );
  $form['advisory_page_settings']['wx_map_page'] = array(
    '#type' => 'textfield',
    '#title' => t('Link for the weather maps button in the weather section'),
    '#default_value' => theme_get_setting('wx_map_page'),
    '#description'   => t("Use a relative link and include the '/' like this: /weather-station-map."),
  );
  $form['advisory_page_settings']['wx_table_page'] = array(
    '#type' => 'textfield',
    '#title' => t('Link for the weather stations table button in the weather section'),
    '#default_value' => theme_get_setting('wx_table_page'),
    '#description'   => t("Use a relative link and include the '/' like this: /weather-stations."),
  );
  $form['advisory_page_settings']['obs_page'] = array(
    '#type' => 'textfield',
    '#title' => t('Link for the Recent Observations Header'),
    '#default_value' => theme_get_setting('obs_page'),
    '#description'   => t("Use a relative link and include the '/' like this: /observations."),
  );
  $form['advisory_page_settings']['submit_snowpack_obs_page'] = array(
    '#type' => 'textfield',
    '#title' => t('Link for the submit snowpack observations button in the observations section'),
    '#default_value' => theme_get_setting('submit_snowpack_obs_page'),
    '#description'   => t("Use a relative link and include the '/' like this: /node/add/observation."),
  );
  $form['advisory_page_settings']['submit_avalanche_obs_page'] = array(
    '#type' => 'textfield',
    '#title' => t('Link for the submit avalanche observations button in the observations section'),
    '#default_value' => theme_get_setting('submit_avalanche_obs_page'),
    '#description'   => t("Use a relative link and include the '/' like this: /node/add/observation."),
  );
}

