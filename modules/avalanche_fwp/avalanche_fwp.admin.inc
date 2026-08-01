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
  $conf = config('avalanche_fwp.settings');

  $form['timing'] = array(
    '#type' => 'fieldset',
    '#title' => t('When notifications are sent'),
    '#collapsible' => FALSE,
  );

  $form['timing']['notify_on_submit'] = array(
    '#type' => 'checkbox',
    '#title' => t('When a plan is submitted, notify the responsible party right away'),
    '#default_value' => $conf->get('notify_on_submit'),
    '#description' => t('Sends the responsible party a heads-up as soon as a plan is published, telling them who is heading out and when they are due back.'),
  );

  $form['timing']['lead_minutes'] = array(
    '#type' => 'number',
    '#title' => t('Advance warning before expected return'),
    '#field_suffix' => t('minutes before'),
    '#default_value' => ($conf->get('lead_minutes') === NULL) ? 15 : (int) $conf->get('lead_minutes'),
    '#min' => 0,
    '#max' => 1440,
    '#description' => t('How long before the expected return time to warn the responsible party that a check-in is due soon — e.g. 15, 30, or 60. Set to 0 to skip the advance warning and only notify at the return time itself.'),
  );

  $form['timing']['at_return'] = array(
    '#type' => 'item',
    '#markup' => '<p class="description">' . t('At the expected return time, if no one has checked in yet, the responsible party is alerted that the party is overdue and the field worker is reminded to check in. When they check in, the responsible party is told they are back safe. These run automatically.') . '</p>',
  );

  $form['channels'] = array(
    '#type' => 'fieldset',
    '#title' => t('How notifications are sent'),
    '#collapsible' => FALSE,
  );

  $form['channels']['enable_sms'] = array(
    '#type' => 'checkbox',
    '#title' => t('Also send SMS text messages (via Twilio)'),
    '#default_value' => $conf->get('enable_sms'),
    '#description' => t('Email is always sent. Enable this to additionally text the responsible party and field worker. Requires Twilio credentials on the <a href="@url">Twilio settings page</a> (Account SID, Auth Token, and a sending number).', array('@url' => url('admin/config/system/twilio'))),
  );

  return system_settings_form($form);
}
