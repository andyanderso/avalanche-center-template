<?php
/**
 * @file
 * Settings form.
 */

/**
 * Module settings page.
 */
function geocoder_admin_settings($form, &$form_state) {
  $config = config('geocoder.settings');
  $form['geocoder_apikey_yahoo'] = array(
    '#type' => 'textfield',
    '#title' => t('Yahoo PlaceFinder API Key'),
    '#description' => t('You can obtain a Yahoo PlaceFinder consumer key at') . ' ' . 'http://developer.yahoo.com/geo/placefinder/',
    '#default_value' => $config->get('geocoder_apikey_yahoo'),
    '#required' => FALSE,
  );

  $form['geocoder_apikey_yandex'] = array(
    '#type' => 'textfield',
    '#title' => t('Yandex Maps API Key'),
    '#description' => t('You can obtain a Yandex API Key at ') . 'http://api.yandex.ru/maps/getkey.xml',
    '#default_value' => $config->get('geocoder_apikey_yandex'),
    '#required' => FALSE,
  );

  $form['geocoder_apikey_bing'] = array(
    '#type' => 'textfield',
    '#title' => t('Bing API Key'),
    '#description' => t('You can obtain a Bing API Key at ') . 'http://msdn.microsoft.com/en-us/library/ff428642.aspx',
    '#default_value' => $config->get('geocoder_apikey_bing'),
    '#required' => FALSE,
  );

  $form['geocoder_apikey_mapzen'] = array(
    '#type' => 'textfield',
    '#title' => t('Mapzen Search API Key'),
    '#description' => t('You can obtain a Mapzen Search API Key at ') . 'https://mapzen.com/developers',
    '#default_value' => $config->get('geocoder_apikey_mapzen'),
    '#required' => FALSE,
  );

  $form['geocoder_apikey_mapbox'] = array(
    '#type' => 'textfield',
    '#title' => t('Mapbox Access Token'),
    '#description' => t('You can obtain a Mapbox access token at ') . ' ' . 'http://www.mapbox.com',
    '#default_value' => $config->get('geocoder_apikey_mapbox'),
    '#required' => FALSE,
  );

  $form['geocoder_google_auth_method'] = array(
    '#type'          => 'select',
    '#title'         => t('Google API Authorization Method'),
    '#description'   => t("If your website runs on shared hosting, you'll want to authenticate requests to the Google Geocoding API to reduce the likelihood of being rate limited (2500 requests per day / 5 requests per second). Alternatively, Google Maps for Work customers may use their Client ID and Signing Key to authenticate."),
    '#default_value' => $config->get('geocoder_google_auth_method'),
    '#options'       => array(
      GEOCODER_GOOGLE_AUTH_NONE => 'None',
      GEOCODER_GOOGLE_AUTH_KEY  => 'Google API Key',
      GEOCODER_GOOGLE_AUTH_WORK => 'Google Maps API for Work',
    ),
  );

  $form['geocoder_apikey_google'] = array(
    '#type'        => 'textfield',
    '#title'       => t('Google Maps API Key'),
    '#description' => t('Obtain a Google API Key at <a href="@link">@link</a>', array(
      '@link' => 'https://developers.google.com/maps/documentation/geocoding/start#get-a-key',
    )),
    '#default_value' => $config->get('geocoder_apikey_google'),
    '#required'      => FALSE,
    '#states'        => array(
      'visible' => array(
        ':input[name="geocoder_google_auth_method"]' => array('value' => GEOCODER_GOOGLE_AUTH_KEY),
      ),
    ),
  );

  $form['geocoder_google_client_id'] = array(
    '#type'        => 'textfield',
    '#title'       => t('Google Maps API for Work: Client ID'),
    '#description' => t('For more information, visit <a href="@link">@link</a>', array(
      '@link' => 'https://developers.google.com/maps/documentation/business/webservices/auth#business-specific_parameters',
    )),
    '#default_value' => $config->get('geocoder_google_client_id'),
    '#required'      => FALSE,
    '#states'        => array(
      'visible' => array(
        ':input[name="geocoder_google_auth_method"]' => array(
          'value' => GEOCODER_GOOGLE_AUTH_WORK,
        ),
      ),
    ),
  );

  $form['geocoder_google_private_key'] = array(
    '#type'        => 'textfield',
    '#title'       => t('Google Maps API for Work: Private/Signing Key'),
    '#description' => t('For more information, visit <a href="@link">@link</a>', array(
      '@link' => 'https://developers.google.com/maps/documentation/business/webservices/auth#how_do_i_get_my_signing_key',
    )),
    '#default_value' => $config->get('geocoder_google_private_key'),
    '#required'      => FALSE,
    '#states'        => array(
      'visible' => array(
        ':input[name="geocoder_google_auth_method"]' => array(
          'value' => GEOCODER_GOOGLE_AUTH_WORK,
        ),
      ),
    ),
  );

  $form['geocoder_google_delay'] = array(
    '#type' => 'number',
    '#title' => t('Delay between Google geocoding requests (in milliseconds)'),
    '#description' => t('This delay will help avoid OVER_QUERY_LIMIT errors from Google. 200ms is recommended.'),
    '#default_value' => $config->get('geocoder_google_delay'),
    '#field_suffix' => t('ms'),
    '#size' => 10,
  );

  $form['geocoder_cache_empty_results'] = array(
    '#type' => 'checkbox',
    '#title' => t('Cache empty results'),
    '#default_value' => $config->get('geocoder_cache_empty_results'),
    '#description' => t('Geocoder caches all queries by default. Do you want that to include the ones with no results? If not, it will be checked every time, probably with the same no-result.'),
  );

  $form['geocoder_log_empty_results'] = array(
    '#type' => 'checkbox',
    '#title' => t('Log empty results'),
    '#default_value' => $config->get('geocoder_log_empty_results'),
    '#description' => t('Whether to generate a watchdog log when geocoding returns no results.'),
  );
  $form['geocoder_recode'] = array(
     '#type' => 'checkbox',
     '#title' => t('Force re-geocoding'),
     '#description' => t('Enable forced re-geocoding of all geocoder fields on entity save (even if there are no changes to the source of the geocoded data). This option can slow down bulk saves and increases the amount of calls to the geocoding service.'),
     '#default_value' => $config->get('geocoder_recode'),
  );

  $form['#submit'][] = 'geocoder_admin_settings_submit';
  $form['actions']['#type'] = 'actions';
  $form['actions']['submit'] = array(
    '#type' => 'submit',
    '#value' => t('Save configuration')
  );
  return $form;
}

function geocoder_admin_settings_validate($form_id, $form_values) {
  if (!empty($form_values['values']['geocoder_apikey_yahoo']) && preg_match("/[^A-Za-z0-9\\-]/", trim($form_values['values']['geocoder_apikey_yahoo']))) {
    form_set_error('geocoder_apikey_yahoo', t('Yahoo API keys are alpha numeric and dashes only.'));
  }
}

function geocoder_admin_settings_submit($form, &$form_state) {
  $config = config('geocoder.settings');
  $config->set('geocoder_apikey_yahoo', trim($form_state['values']['geocoder_apikey_yahoo']));
  $config->set('geocoder_apikey_yandex', trim($form_state['values']['geocoder_apikey_yandex']));
  $config->set('geocoder_apikey_bing', trim($form_state['values']['geocoder_apikey_bing']));
  $config->set('geocoder_apikey_mapzen', trim($form_state['values']['geocoder_apikey_mapzen']));
  $config->set('geocoder_apikey_mapbox', trim($form_state['values']['geocoder_apikey_mapbox']));
  $config->set('geocoder_apikey_google', trim($form_state['values']['geocoder_apikey_google']));
  $config->set('geocoder_google_auth_method', $form_state['values']['geocoder_google_auth_method']);
  $config->set('geocoder_google_client_id', trim($form_state['values']['geocoder_google_client_id']));
  $config->set('geocoder_google_private_key', trim($form_state['values']['geocoder_google_private_key']));
  $config->set('geocoder_google_delay', $form_state['values']['geocoder_google_delay']);
  $config->set('geocoder_cache_empty_results', $form_state['values']['geocoder_cache_empty_results']);
  $config->set('geocoder_log_empty_results', $form_state['values']['geocoder_log_empty_results']);
  $config->set('geocoder_recode', $form_state['values']['geocoder_recode']);
  $config->save();
  backdrop_set_message(t('The configuration options have been saved.'));
}
