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

  $form['emergency'] = array(
    '#type' => 'fieldset',
    '#title' => t('Emergency response plan'),
    '#collapsible' => FALSE,
  );
  $form['emergency']['emergency_response_link'] = array(
    '#type' => 'url',
    '#title' => t('Emergency response plan link'),
    '#default_value' => $conf->get('emergency_response_link'),
    '#description' => t('If set, a prominent link to your emergency response plan is shown at the top of every Field Work Plan page.'),
  );
  $form['emergency']['emergency_response_text'] = array(
    '#type' => 'textfield',
    '#title' => t('Link text'),
    '#default_value' => $conf->get('emergency_response_text') ?: t('Emergency Response Plan'),
    '#size' => 40,
  );

  $form['access'] = array(
    '#type' => 'fieldset',
    '#title' => t('Public access password'),
    '#collapsible' => FALSE,
  );
  $form['access']['view_password'] = array(
    '#type' => 'textfield',
    '#title' => t('Field Work Plan access password'),
    '#default_value' => $conf->get('view_password'),
    '#size' => 30,
    '#description' => t('If set, Field Work Plan pages are hidden from the public. Logged-in users see them normally; anyone else must enter this password (share it with partners/dispatch who do not have accounts). Leave blank to make the plans fully public.'),
  );

  $form['approval'] = array(
    '#type' => 'fieldset',
    '#title' => t('Plan approval'),
    '#collapsible' => FALSE,
  );
  // Roles whose members appear in the "Approved By" dropdown on a plan.
  $role_options = user_roles(TRUE);
  unset($role_options[BACKDROP_AUTHENTICATED_ROLE]);
  $default_roles = $conf->get('approver_roles');
  if ($default_roles === NULL) {
    // Sensible default: the roles that can already check in any plan.
    $default_roles = array('administrator', 'forecaster', 'program_manager');
  }
  $form['approval']['approver_roles'] = array(
    '#type' => 'checkboxes',
    '#title' => t('Approver roles'),
    '#options' => $role_options,
    '#default_value' => is_array($default_roles) ? $default_roles : array(),
    '#description' => t('Users with any of these roles appear in the "Approved By" dropdown when filling out a Field Work Plan.'),
  );

  return system_settings_form($form);
}
