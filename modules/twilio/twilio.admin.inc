<?php
/**
 * @file
 * Administrative forms section
 */

/**
 * Administration form for twilio settings.
 */
function twilio_admin_form($form, &$form_state) {

  // config object for backdrop
  $config = config('twilio.settings');

  $form['twilio_account'] = array(
    '#type' => 'textfield',
    '#required' => TRUE,
    '#title' => t('Twilio Account SID'),    
    '#default_value' => $config->get('twilio_account'),
    '#description' => t('Enter your Twilio account id'),
  );
  $form['twilio_token'] = array(
    '#type' => 'textfield',
    '#required' => TRUE,
    '#title' => t('Twilio Auth Token'),    
    '#default_value' => $config->get('twilio_token'),
    '#description' => t('Enter your Twilio token id'),
  );
  $form['twilio_number'] = array(
    '#type' => 'textfield',
    '#required' => TRUE,
    '#title' => t('Twilio Phone Number'),    
    '#default_value' => $config->get('twilio_number'),
    
    '#description' => t('Enter your Twilio phone number'),
  );
  $form['twilio_long_sms'] = array(
    '#type' => 'radios',
    '#title' => t('Long SMS handling'),
    '#description' => t('How would you like to handle SMS messages longer than 160 characters.'),
    '#options' => array(t('Send multiple messages'), t('Truncate message to 160 characters')),   
    '#default_value' => $config->get('twilio_long_sms'),
  );
  $form['twilio_registration_form'] = array(
    '#type' => 'radios',
    '#title' => t('Show mobile fields during user registration'),
    '#description' => t('Specify if the site should collect mobile information during registration.'),
    '#options' => array(t('Disabled'), t('Optional'), t('Required')), 
    '#default_value' => $config->get('twilio_registration_form'),
  );

  $form['twilio_country_codes_container'] = array(
    '#type' => 'fieldset',
    '#title' => t('Country codes'),
    '#description' => t('Select the country codes you would like available, If none are selected all will be available.'),
    '#collapsible' => TRUE,
    '#collapsed' => TRUE,
  );

  $form['twilio_country_codes_container']['twilio_country_codes'] = array(
    '#type' => 'checkboxes',
    '#options' => twilio_country_codes(TRUE),   
    '#default_value' => $config->get('twilio_country_codes'),
  );

  // Expose the callback URLs to the user for convenience.
  $form['twilio_callback_container'] = array(
    '#type' => 'fieldset',
    '#title' => t('Module callbacks'),
    '#description' => t('Enter these callback addresses into your Twilio phone number configuration on the Twilio dashboard to allow your site to respond to incoming voice calls and SMS messages.'),
  );

  // Initialize URL variables.
  $voice_callback = $GLOBALS['base_url'] . '/twilio/voice';
  $sms_callback = $GLOBALS['base_url'] . '/twilio/sms';

  $form['twilio_callback_container']['voice_callback'] = array(
    '#type' => 'item',
    '#title' => t('Voice request URL'),
    '#markup' => '<p>' . $voice_callback . '</p>',
  );

  $form['twilio_callback_container']['sms_callback'] = array(
    '#type' => 'item',
    '#title' => t('SMS request URL'),
    '#markup' => '<p>' . $sms_callback . '</p>',
  );

  //return system_settings_form($form);
  
  $form['submit'] = array(
    '#type' => 'submit',
    '#value' => t('Save Twilio Settings'),
  );
  
  
  return $form;
  
}

/**
 * Backdrop submit for config, replace call to
 *  system_settings_form()
 */

function twilio_admin_form_submit($form, &$form_state) {	
	// new config object
	$config = config('twilio.settings');
	$config->set('twilio_number', $form_state['values']['twilio_number']);
	$config->set('twilio_account', $form_state['values']['twilio_account']);
	$config->set('twilio_token', $form_state['values']['twilio_token']);
	$config->set('twilio_long_sms', $form_state['values']['twilio_long_sms']);
	$config->set('twilio_registration_form', $form_state['values']['twilio_registration_form']);
	$config->set('twilio_country_codes', $form_state['values']['twilio_country_codes']);
    
    $config->save();
    backdrop_set_message("Config values saved for Twilio settings");
}



/**
 * Administrative testing form for SMS.
 */
function twilio_admin_test_form($form, &$form_state) {
  $form['country'] = array(
    '#type' => 'select',
    '#title' => t('Country code'),
    '#options' => twilio_country_codes(),
  );
  $form['number'] = array(
    '#type' => 'textfield',
    '#required' => TRUE,
    '#title' => t('Phone Number'),
    '#description' => t('The number to send your message to. Include all numbers except the country code'),
  );
  $form['message'] = array(
    '#type' => 'textarea',
    '#required' => TRUE,
    '#title' => t('Message'),
    '#description' => t("The body of your SMS message.")
  );
  $form['media'] = array(
    '#type' => 'textfield',
    '#required' => FALSE,
    '#title' => t('Media URL (MMS)'),
    '#description' => t('A publicly accessible URL to a media file such as a png, jpg, gif, etc. (e.g. http://something.com/photo.jpg)'),
  );
  $form['submit'] = array(
    '#type' => 'submit',
    '#value' => t('Send SMS'),
  );
  return $form;
}

/**
 * Validation handler for the administrative test message form.
 */
function twilio_admin_test_form_validate($form, &$form_state) {
  $value = $form_state['values']['number'];
  if (!is_numeric($value)) {
    form_set_error('number', t('You must enter a phone number'));
  }
  if (!empty($form_state['values']['media']) && !valid_url($form_state['values']['media'], TRUE)) {
    form_set_error('media', t('Media must be a valid URL'));
  }
}

/**
 * Submit handler for the administrative test message testing form.
 */
function twilio_admin_test_form_submit($form, &$form_state) {
  $sent = twilio_send(
    $form_state['values']['number'],
    $form_state['values']['message'],
    $form_state['values']['country'],
    !empty($form_state['values']['media']) ? $form_state['values']['media'] : NULL
  );
  if ($sent) {
    backdrop_set_message(t('Your message has been sent'));
  }
  else {
    backdrop_set_message(t('Unable to send your message.'), 'error');
  }
}
