<?php
/**
 * @file
 * Twilio Actions.
 */


/**
 * Callback function for sending sms to user action.
 */
function twilio_send_sms_to_user_action($account, $context) {
  if (!empty($account->twilio_user['number']) && $account->twilio_user['status'] == 2) {
    $message = $context['message'];
    $media = !empty($context['media']) ? $context['media'] : NULL;
    twilio_send($account->twilio_user['number'], $message, $account->twilio_user['country'], $media);
  }
}

/**
 * Configuration form for the send sms to user action.
 */
function twilio_send_sms_to_user_action_form($context) {
  $form['message'] = array(
    '#type' => 'textarea',
    '#title' => t('SMS Message'),
    '#required' => TRUE,
    '#description' => t('Enter the message you would like to send.'),
  );
  $form['media'] = array(
    '#type' => 'textfield',
    '#title' => t('Media URL'),
    '#description' => t('URL to a media file you wish to send.'),
  );
  return $form;
}

/**
 * Submit handler for send sms to user action configuration form.
 */
function twilio_send_sms_to_user_action_submit($form, $form_state) {
  return array(
    'message' => $form_state['values']['message'],
    'media' => $form_state['values']['media'],
  );
}
