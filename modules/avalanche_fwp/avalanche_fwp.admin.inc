<?php
/**
 * @file
 * Settings form for the Avalanche Field Work Plan module.
 */

/**
 * Field Work Plan notification settings.
 */
function avalanche_fwp_settings_form($form, &$form_state) {
  $form['#config'] = 'avalanche_fwp.settings';

  $form['lead_minutes'] = array(
    '#type' => 'number',
    '#title' => t('Pre-return warning lead time (minutes)'),
    '#default_value' => config_get('avalanche_fwp.settings', 'lead_minutes') ?: 15,
    '#min' => 1,
    '#max' => 240,
    '#description' => t('How long before the expected return time to send the responsible party the first "check-in due soon" warning. Note: site cron must run at least this often for the timing to be accurate.'),
  );

  $form['enable_sms'] = array(
    '#type' => 'checkbox',
    '#title' => t('Also send SMS text messages (via Twilio)'),
    '#default_value' => config_get('avalanche_fwp.settings', 'enable_sms'),
    '#description' => t('Email is always sent. Enable this to additionally text the responsible party and field worker. Requires Twilio credentials on the <a href="@url">Twilio settings page</a> (Account SID, Auth Token, and a sending number).', array('@url' => url('admin/config/system/twilio'))),
  );

  return system_settings_form($form);
}
