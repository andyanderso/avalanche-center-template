<?php

/**
 * @file
 * Administration forms for the Scheduler module.
 */

/**
 * Form constructor for the main admin form for configuring Scheduler.
 */
function scheduler_admin() {
  $config = config('scheduler.settings');

  $form['field_type'] = array(
    '#type' => 'radios',
    '#title' => t('Field type'),
    '#default_value' => $config->get('field_type'),
    '#options' => array(
      'date_popup' => t('HTML Datetime field with popup calendar'),
      'textfield' => t('Standard text field'),
    ),
  );

  if (!module_exists('date')) {
    $form['field_type']['#default_value'] = 'textfield';
    $form['field_type']['#disabled'] = TRUE;
    $form['field_type']['#description'] = t('To use the HTML Datetime field you need to enable the Date module.');
  }

  $form['extra_info'] = array(
    '#type' => 'textarea',
    '#title' => t('Description'),
    '#default_value' => $config->get('extra_info'),
    '#description' => t('The text entered into this field will be displayed above the scheduling fields in the content edit form.'),
  );

  $now = t('Example: %date', array(
    '%date' => format_date(REQUEST_TIME, 'custom', $config->get('date_format')),
  ));

  // Options for setting date-related details.
  $form['date_details'] = array(
    '#type' => 'fieldset',
    '#title' => t('Date details'),
    '#collapsible' => FALSE,
  );
  $form['date_details']['date_format'] = array(
    '#type' => 'textfield',
    '#title' => t('Date format'),
    '#default_value' => $config->get('date_format'),
    '#size' => 20,
    '#maxlength' => 20,
    '#required' => TRUE,
    '#field_suffix' => ' <small>' . $now . '</small>',
    '#description' => t('The format for entering scheduled dates and times. For the date use the letters !date_letters and for the time use !time_letters. See !url for more details.', array(
      '!date_letters' => SCHEDULER_DATE_LETTERS,
      '!time_letters' => SCHEDULER_TIME_LETTERS,
      '!url' => l(t('the PHP date() function'), 'https://www.php.net/manual/en/function.date.php'),
    )),
  );
  $form['date_details']['date_limit'] = array(
    '#type' => 'checkbox',
    '#title' => t('Limit how far into the future to allow scheduling.'),
    '#default_value' => $config->get('date_limit'),
    '#description' => t('Enter a quantity and select which type of units (days, weeks, months, years) you would like to use as the scheduling limit, measured from the current date when creating or editing content.'),
  );
  $form['date_details']['date_limits'] = array(
    '#type' => 'container',
    '#states' => array(
      'visible' => array(
        ':input[name="date_limit"]' => array('checked' => TRUE),
      ),
    ),
  );
  $form['date_details']['date_limits']['date_limit_quantity'] = array(
    '#type' => 'number',
    '#title' => t('Quantity'),
    '#default_value' => $config->get('date_limit_quantity'),
    '#size' => 2,
    '#min' => 1,
  );
  $form['date_details']['date_limits']['date_limit_units'] = array(
    '#type' => 'select',
    '#title' => t('Units'),
    '#default_value' => $config->get('date_limit_units'),
    '#options' => array(
      'day' => t('Days'),
      'week' => t('Weeks'),
      'month' => t('Months'),
      'year' => t('Years'),
    ),
  );
  $form['date_details']['allow_date_only'] = array(
    '#type' => 'checkbox',
    '#title' => t('Allow users to enter only a date and provide a default time.'),
    '#default_value' => $config->get('allow_date_only'),
    '#description' => t('When only a date is entered the time will default to a specified value, but the user can change this if required.'),
  );
  $form['date_details']['default_time'] = array(
    '#type' => 'textfield',
    '#title' => t('Default time'),
    '#default_value' => $config->get('default_time'),
    '#size' => 20,
    '#maxlength' => 20,
    '#description' => t('This is the time that will be used if the user does not enter a value. Format: HH:MM:SS.'),
    '#states' => array(
      'visible' => array(
        ':input[name="allow_date_only"]' => array('checked' => TRUE),
      ),
    ),
  );

  $form['cache_clear_all'] = array(
    '#prefix' => '<label>' . t('Cache') . '</label>',
    '#type' => 'checkbox',
    '#title' => t('Clear all expired block and page caches after publishing or unpublishing via cron.'),
    '#default_value' => $config->get('cache_clear_all'),
    '#description' => t('If a node has been published or unpublished by Scheduler during a cron run, this option will clear the caches instead of relying on the Backdrop system cron task. Warning: This may have a detrimental effect on performance for large sites.'),
  );

  $form['#attached']['css'][] = backdrop_get_path('module', 'scheduler') . '/css/scheduler.admin.css';
  // Add a submit handler function.
  $form['#submit'][] = 'scheduler_admin_submit';

  return system_settings_form($form);
}

/**
 * Form validation handler for scheduler_admin().
 */
function scheduler_admin_validate($form, &$form_state) {
  // Replace all contiguous whitespaces (including tabs and newlines) with a
  // single plain space.
  $form_state['values']['date_format'] = trim(preg_replace('/\s+/', ' ', $form_state['values']['date_format']));

  // Validate the letters used in the scheduler date format. All punctuation is
  // accepted, so remove everything except word characters then check that there
  // is nothing else which is not in the list of acceptable date/time letters.
  $no_punctuation = preg_replace('/[^\w+]/', '', $form_state['values']['date_format']);
  if (preg_match_all('/[^' . SCHEDULER_DATE_LETTERS . SCHEDULER_TIME_LETTERS . ']/', $no_punctuation, $extra)) {
    form_set_error('date_format', t('You may only use the letters $date_letters for the date and $time_letters for the time. Remove the extra characters $extra', array(
      '$date_letters' => SCHEDULER_DATE_LETTERS,
      '$time_letters' => SCHEDULER_TIME_LETTERS,
      '$extra' => implode(' ', $extra[0]),
    )));
  };

  $time_format = scheduler_get_time_only_format($form_state['values']['date_format']);

  if ($form_state['values']['field_type'] == 'date_popup') {
    // The Date Popup function date_popup_time_formats() only returns the values
    // 'H:i:s' and 'h:i:sA' but Scheduler can accept more variations than just
    // these. Firstly, we add the lowercase 'a' alternative. Secondly timepicker
    // always requires hours and minutes, but seconds are optional. Spaces are
    // allowed before the 'A' or 'a'.
    $acceptable = array(
      'H:i:s', 'h:i:sA', 'h:i:s A', 'h:i:sa', 'h:i:s a',
      'H:i', 'h:iA', 'h:i A', 'h:ia', 'h:i a',
    );

    if ($time_format && !in_array($time_format, $acceptable)) {
      form_set_error('date_format', t('When using the Date Popup module, the allowed time formats are: !formats', array('!formats' => implode(', ', $acceptable))));
    }
  }

  // If date-only is enabled then check if a valid default time was entered.
  // Leading zeros and seconds can be omitted, eg. 6:30 is considered valid.
  if ($form_state['values']['allow_date_only']) {
    $default_time = date_parse($form_state['values']['default_time']);
    if ($default_time['error_count']) {
      form_set_error('default_time', t('The default time should be in the format HH:MM:SS'));
    }
    else {
      // Insert any possibly omitted leading zeroes.
      $unix_time = mktime($default_time['hour'], $default_time['minute'], $default_time['second']);
      $form_state['values']['default_time'] = format_date($unix_time, 'custom', 'H:i:s');
    }
  }

  // Check that either the date format has a time part or the date-only option
  // is turned on.
  if ($time_format == '' && !$form_state['values']['allow_date_only']) {
    form_set_error('date_format', t('You must either include a time within the date format or enable the date-only option.'));
  }
}

/**
 * Form submission handler for scheduler_admin().
 */
function scheduler_admin_submit($form, &$form_state) {
  $config = config('scheduler.settings');

  // Extract the date part and time part of the full format, for use with the
  // default time functionality. Assume the date and time time parts begin and
  // end with a letter, but any punctuation between these will be retained.
  $format = $form_state['values']['date_format'];
  $time_only_format = scheduler_get_time_only_format($format);
  $config->set('time_only_format', $time_only_format);

  $date_only_format = scheduler_get_date_only_format($format);
  $config->set('date_only_format', $date_only_format);

  // Save config.
  $config->set('date_format', $format);
  $config->set('field_type', $form_state['values']['field_type']);
  $config->set('allow_date_only', $form_state['values']['allow_date_only']);
  $config->set('default_time', $form_state['values']['default_time']);
  $config->set('extra_info', $form_state['values']['extra_info']);
  $config->set('date_limit', $form_state['values']['date_limit']);
  $config->set('date_limit_quantity', $form_state['values']['date_limit_quantity']);
  $config->set('date_limit_units', $form_state['values']['date_limit_units']);
  $config->set('cache_clear_all', $form_state['values']['cache_clear_all']);
  $config->save();

  if (empty($time_only_format)) {
    backdrop_set_message(t('The date part of the Scheduler format is %date_part. There is no time part', array(
      '%date_part' => $date_only_format,
    )));
  }
  else {
    backdrop_set_message(t('The date part of the Scheduler format is %date_part and the time part is %time_part.', array(
      '%date_part' => $date_only_format,
      '%time_part' => $time_only_format,
    )));
  }
}

/**
 * Returns the time part of a date format.
 *
 * For example, when given the string 'Y-m-d H:s:i' it will return 'H:s:i'.
 *
 * @param string $format
 *   A date format compatible with the PHP date() function.
 *
 * @return string
 *   The time part of the date format, or an empty string if it does not contain
 *   a time part.
 */
function scheduler_get_time_only_format($format) {
  $time_start = strcspn($format, SCHEDULER_TIME_LETTERS);
  $time_length = strlen($format) - strcspn(strrev($format), SCHEDULER_TIME_LETTERS) - $time_start;
  return substr($format, $time_start, $time_length);
}

/**
 * Returns the date part of a date format.
 *
 * For example, when given the string 'Y-m-d H:s:i' it will return 'Y-m-d'.
 *
 * @param string $format
 *   A date format compatible with the PHP date() function.
 *
 * @return string
 *   The date part of the date format, or an empty string if it does not contain
 *   a date part.
 */
function scheduler_get_date_only_format($format) {
  $date_start = strcspn($format, SCHEDULER_DATE_LETTERS);
  $date_length = strlen($format) - strcspn(strrev($format), SCHEDULER_DATE_LETTERS) - $date_start;
  return substr($format, $date_start, $date_length);
}

/**
 * Helper function for the real hook_form_node_type_form_alter().
 *
 * @see scheduler_form_node_type_form_alter()
 */
function _scheduler_form_node_type_form_alter(&$form, $form_state) {
  $config = config('node.type.' . $form['#node_type']->type);
  $form['scheduler'] = array(
    '#type' => 'fieldset',
    '#title' => t('Scheduler'),
    '#weight' => 35,
    '#group' => 'additional_settings',
    '#attached' => array(
      'js' => array(
        'vertical-tabs' => backdrop_get_path('module', 'scheduler') . "/js/scheduler_vertical_tabs.js",
      ),
    ),
  );

  $form['scheduler']['publish'] = array(
    '#type' => 'fieldset',
    '#title' => t('Publishing'),
    '#collapsible' => FALSE,
    '#weight' => 1,
    '#group' => 'scheduler',
  );
  $form['scheduler']['publish']['scheduler_publish_enable'] = array(
    '#type' => 'checkbox',
    '#title' => t('Enable scheduled publishing for this content type'),
    '#default_value' => $config->get('scheduler_publish_enable'),
  );
  $scheduler_publishing_enabled = $config->get('scheduler_publish_enable');
  $core_scheduling_enabled = $form['workflow']['scheduling_enabled']['#default_value'];
  if ($core_scheduling_enabled) {
    $form['scheduler']['publish']['warning'] = array(
      '#type' => 'item',
      '#markup' => t('Please note that it may be confusing to activate both the simplified scheduled publishing functionality that is in Backdrop core (under "Publishing settings") and the more advanced scheduled publishing available here.'),
    );
    if (!$scheduler_publishing_enabled) {
      $form['scheduler']['publish']['scheduler_publish_enable']['#disabled'] = TRUE;
      $form['scheduler']['publish']['warning']['#markup'] .= ' ' . t('Scheduler\'s publishing functionality has been disabled since core scheduled publishing is active.');
    }
  }
  if ($scheduler_publishing_enabled) {
    $form['workflow']['warning'] = array(
      '#type' => 'item',
      '#markup' => t('Please note that it may be confusing to activate both the simplified scheduled publishing functionality here and the more advanced scheduled publishing available via the Scheduler module (under "Scheduler").'),
      '#weight' => 0,
    );
    if (!$core_scheduling_enabled) {
      $form['workflow']['scheduling_enabled']['#disabled'] = TRUE;
      $form['workflow']['warning']['#markup'] .= ' ' . t('Core simplified scheduled publishing functionality has been disabled since Scheduler publishing is active.');
    }
  }
  $form['scheduler']['publish']['scheduler_publish_touch'] = array(
    '#type' => 'checkbox',
    '#title' => t('Change content creation time to match the scheduled publish time'),
    '#default_value' => $config->get('scheduler_publish_touch'),
    '#states' => array(
      'visible' => array(
        ':input[name="scheduler_publish_enable"]' => array('checked' => TRUE),
      ),
    ),
  );
  $form['scheduler']['publish']['scheduler_publish_required'] = array(
    '#type' => 'checkbox',
    '#title' => t('Require scheduled publishing'),
    '#default_value' => $config->get('scheduler_publish_required'),
    '#states' => array(
      'visible' => array(
        ':input[name="scheduler_publish_enable"]' => array('checked' => TRUE),
      ),
    ),
  );
  $form['scheduler']['publish']['scheduler_publish_revision'] = array(
    '#type' => 'checkbox',
    '#title' => t('Create a new revision on publishing'),
    '#default_value' => $config->get('scheduler_publish_revision'),
    '#states' => array(
      'visible' => array(
        ':input[name="scheduler_publish_enable"]' => array('checked' => TRUE),
      ),
    ),
  );
  $form['scheduler']['publish']['advanced'] = array(
    '#type' => 'fieldset',
    '#title' => t('Advanced options'),
    '#collapsible' => TRUE,
    '#collapsed' => TRUE,
    '#states' => array(
      'visible' => array(
        ':input[name="scheduler_publish_enable"]' => array('checked' => TRUE),
      ),
    ),
  );
  $form['scheduler']['publish']['advanced']['scheduler_publish_past_date'] = array(
    '#type' => 'radios',
    '#title' => t('Action to be taken for publication dates in the past'),
    '#default_value' => $config->get('scheduler_publish_past_date'),
    '#options' => array(
      'error' => t('Display an error message - do not allow dates in the past'),
      'publish' => t('Publish the content immediately after saving'),
      'schedule' => t('Schedule the content for publication on the next cron run'),
    ),
  );
  $form['scheduler']['unpublish'] = array(
    '#type' => 'fieldset',
    '#title' => t('Unpublishing'),
    '#collapsible' => FALSE,
    '#weight' => 2,
    '#group' => 'scheduler',
  );
  $form['scheduler']['unpublish']['scheduler_unpublish_enable'] = array(
    '#type' => 'checkbox',
    '#title' => t('Enable scheduled unpublishing for this content type'),
    '#default_value' => $config->get('scheduler_unpublish_enable'),
  );
  $form['scheduler']['unpublish']['scheduler_unpublish_required'] = array(
    '#type' => 'checkbox',
    '#title' => t('Require scheduled unpublishing'),
    '#default_value' => $config->get('scheduler_unpublish_required'),
    '#states' => array(
      'visible' => array(
        ':input[name="scheduler_unpublish_enable"]' => array('checked' => TRUE),
      ),
    ),
  );
  $form['scheduler']['unpublish']['scheduler_unpublish_revision'] = array(
    '#type' => 'checkbox',
    '#title' => t('Create a new revision on unpublishing'),
    '#default_value' => $config->get('scheduler_unpublish_revision'),
    '#states' => array(
      'visible' => array(
        ':input[name="scheduler_unpublish_enable"]' => array('checked' => TRUE),
      ),
    ),
  );
  // The 'node_edit_layout' fieldset contains options to alter the layout of
  // node edit pages.
  $form['scheduler']['node_edit_layout'] = array(
    '#type' => 'fieldset',
    '#title' => t('Node edit page layout'),
    '#collapsible' => FALSE,
    '#weight' => 3,
    '#group' => 'scheduler',
    // The #states processing only caters for AND and does not do OR. So to set
    // the state to visible if either of the boxes are ticked we use the fact
    // that logical 'X = A or B' is equivalent to 'not X = not A and not B'.
    '#states' => array(
      '!visible' => array(
        ':input[name="scheduler_publish_enable"]' => array('!checked' => TRUE),
        ':input[name="scheduler_unpublish_enable"]' => array('!checked' => TRUE),
      ),
    ),
  );
  $form['scheduler']['node_edit_layout']['scheduler_use_vertical_tabs'] = array(
    '#type' => 'radios',
    '#title' => t('Display scheduling options as'),
    '#default_value' => $config->get('scheduler_use_vertical_tabs'),
    '#options' => array(
      1 => t('Vertical tab'),
      0 => t('Separate fieldset'),
    ),
    '#description' => t('Use this option to specify how the scheduling options will be displayed when editing a node.'),
  );
  $form['scheduler']['node_edit_layout']['scheduler_expand_fieldset'] = array(
    '#type' => 'radios',
    '#title' => t('Expand fieldset'),
    '#default_value' => $config->get('scheduler_expand_fieldset'),
    '#options' => array(
      0 => t('Expand only when a scheduled date exists or when a date is required'),
      1 => t('Always open the fieldset, even if no dates exist'),
    ),
    '#states' => array(
      'visible' => array(
        ':input[name="scheduler_use_vertical_tabs"]' => array('value' => '0'),
      ),
    ),
  );
  $form['#submit'][] = '_scheduler_form_node_type_form_submit';
}

/**
  * Form submit handler for _scheduler_form_node_type_form_alter
  */
function _scheduler_form_node_type_form_submit($form, &$form_state) {
  $config = config('node.type.' . $form['#node_type']->type);
  $config->set('scheduler_publish_enable', $form_state['values']['scheduler_publish_enable']);
  $config->set('scheduler_expand_fieldset', $form_state['values']['scheduler_expand_fieldset']);
  $config->set('scheduler_use_vertical_tabs', $form_state['values']['scheduler_use_vertical_tabs']);
  $config->set('scheduler_unpublish_revision', $form_state['values']['scheduler_unpublish_revision']);
  $config->set('scheduler_unpublish_required', $form_state['values']['scheduler_unpublish_required']);
  $config->set('scheduler_unpublish_enable', $form_state['values']['scheduler_unpublish_enable']);
  $config->set('scheduler_publish_past_date', $form_state['values']['scheduler_publish_past_date']);
  $config->set('scheduler_publish_revision', $form_state['values']['scheduler_publish_revision']);
  $config->set('scheduler_publish_required', $form_state['values']['scheduler_publish_required']);
  $config->set('scheduler_publish_touch', $form_state['values']['scheduler_publish_touch']);
  $config->save();
}


/**
 * Form constructor for the lightweight cron form to allow a manual run.
 */
function _scheduler_lightweight_cron($form, &$form_state) {
  $config = config('scheduler.settings');
  $form = array();
  $prefix_text = t("You can test Scheduler's lightweight cron process interactively");
  $form['#config'] = 'scheduler.settings';
  $form['cron'] = array(
    '#type' => 'submit',
    '#prefix' => '<div class="container-inline">' . $prefix_text . ': ',
    '#value' => t("Run Scheduler's lightweight cron now"),
    '#submit' => array('_scheduler_lightweight_cron_submit'),
    '#suffix' => "</div>\n",
  );
  $form['cron_settings'] = array(
    '#type' => 'fieldset',
    '#title' => t('Lightweight cron settings'),
  );
  $form['cron_settings']['lightweight_log'] = array(
    '#type' => 'checkbox',
    '#title' => t('Log every activation and completion message.'),
    '#default_value' => $config->get('lightweight_log'),
    '#description' => t("When this option is checked, Scheduler will write an entry to the log every time the lightweight cron process is started and completed. This is useful during set up and testing, but can result in a large number of log entries. Any actions performed during the lightweight cron run will always be logged regardless of this setting."),
  );
  $form['cron_settings']['lightweight_access_key'] = array(
    '#type' => 'textfield',
    '#title' => t('Lightweight cron access key'),
    '#size' => 100,
    '#default_value' => $config->get('lightweight_access_key'),
    '#description' => t("Similar to Backdrop's cron key this acts as a security token to prevent unauthorised calls to scheduler/cron. The key should be passed as scheduler/cron/&lt;this key&gt;. To disable security for lightweight cron leave this field blank."),
  );
  if (isset($form_state['generate_new_key'])) {
    $new_access_key = substr(md5(rand()), 0, 20);
    // @codingStandardsIgnoreStart
    // We have to use $form_state['input'] here as storing the new key in
    // $form_state['values'] does not work. It is acceptable to use it on the
    // left of an assignment even if PHPCS does not think so.
    $form_state['input']['lightweight_access_key'] = $new_access_key;
    // @codingStandardsIgnoreEnd
    backdrop_set_message(t('A new random key has been generated but not saved. If you wish to use this, first "Save Configuration" to store the value, then modify your crontab job.'), 'warning');
  }
  $form['cron_settings']['create_key'] = array(
    '#type' => 'submit',
    '#value' => t('Generate new random key'),
    '#submit' => array('_scheduler_generate_key'),
  );
  return system_settings_form($form);
}

/**
 * Form submission handler for _scheduler_lightweight_cron().
 */
function _scheduler_lightweight_cron_submit() {
  // Load the cron functions file.
  module_load_include('inc', 'scheduler', 'scheduler.cron');
  _scheduler_run_cron();
}

/**
 * Form submission handler for _scheduler_lightweight_cron().
 *
 * This only fires when the 'Generate new random key' button is clicked.
 */
function _scheduler_generate_key($form, &$form_state) {
  // Everything in $form_state is persistent, so we can use a direct attribute.
  $form_state['generate_new_key'] = TRUE;
  // Setting $form_state['rebuild'] = TRUE causes the form to be rebuilt again.
  $form_state['rebuild'] = TRUE;
}

/**
 * Page callback: Generate the timecheck admin page.
 */
function _scheduler_timecheck() {
  return theme('scheduler_timecheck', array('now' => REQUEST_TIME));
}

/**
 * Returns HTML the timecheck administration page.
 *
 * @param array $variables
 *   An associative array containing:
 *   - 'now': A UNIX timestamp to format.
 *
 * @ingroup themeable
 */
function theme_scheduler_timecheck(array $variables) {
  global $user;

  $now = $variables['now'];
  $date_default_timezone = config_get('system.date', 'default_timezone');

  $t_options = array(
    // For @utc specify 'GMT' as the timezone (4th parameter) so that no
    // timezone offset is returned.
    '@utc' => format_date($now, 'custom', 'jS F Y, H:i:s P', 'GMT'),
    // For @localtime do not specify any timezone parameter so that the user or
    // site default setting is returned.
    '@localtime' => format_date($now, 'custom', 'jS F Y, H:i:s P T e'),
    '@daylight_saving' => format_date($now, 'custom', 'I') ? t('currently in daylight saving mode') : t('not in daylight saving mode'),
    '@user_account' => url('user/' . $user->uid . '/edit'),
    '@date_default_timezone' => check_plain($date_default_timezone),
    '@date_default_offset' => format_date($now, 'custom', 'P', $date_default_timezone),
    '@date_default_code' => format_date($now, 'custom', 'T', $date_default_timezone),
    '@admin_regional_settings' => url('admin/config/regional/settings'),
  );

  $output = '<h4>' . t('Time check')
    . '</h4><p>' . t('Your server\'s time is @utc. In most cases this should match <a href="https://www.google.com/search?q=Greenwich%20Mean%20Time">Greenwich Mean Time (GMT) / Coordinated Universal Time (UTC)</a>', $t_options)
    . '</p><p>' . t('The website default timezone is @date_default_timezone (@date_default_code) which is offset from GMT by @date_default_offset hours. This timezone can be <a href="@admin_regional_settings">changed by admin users</a> with the appropriate access.', $t_options)
    . '</p>';

  if (config_get('system.date', 'user_configurable_timezones')) {
    $output .= '<p>' . t('Your local time is @localtime (@daylight_saving). You can change this via your <a href="@user_account">user account</a>.', $t_options) . '</p>';
    if (empty($user->timezone)) {
      $output .= '<p>' . t('Note: The user timezone has not been stored, so defaulting to the website timezone.') . '</p>';
    }
  }
  else {
    $output .= '<p>' . t('Your local time is @localtime (@daylight_saving). This is not configurable by you.', $t_options) . '</p>';
  }

  return $output;
}

/**
 * Page callback: Displays a list of nodes scheduled for (un)publication.
 *
 * This will appear as a tab on the Scheduler admin page at
 * admin/config/content/scheduler/list and the content admin page at
 * admin/content/scheduler. It is also shown on the 'My account' page
 * user/{uid}/scheduler if the user has permission to schedule content.
 *
 * @param string $show
 *   'user_only' if viewing a user page, NULL otherwise.
 * @param int $uid
 *   The uid when viewing a user page, NULL otherwise.
 *
 * @return array
 *   A render array for a page containing a list of nodes.
 */
function scheduler_list($show, $uid) {
  $header = array(
    array(
      'data' => t('Title'),
      'field' => 'n.title',
    ),
    array(
      'data' => t('Type'),
      'field' => 'n.type',
    ),
    array(
      'data' => t('Author'),
      'field' => 'u.name',
    ),
    array(
      'data' => t('Status'),
      'field' => 'n.status',
    ),
    array(
      'data' => t('Publish on'),
      'field' => 's.publish_on',
    ),
    array(
      'data' => t('Unpublish on'),
      'field' => 's.unpublish_on',
    ),
    array(
      'data' => t('Operations'),
    ),
  );

  // Default ordering.
  if (!isset($_GET['order']) && !isset($_GET['sort'])) {
    $_GET['order'] = t('Publish on');
    $_GET['sort'] = 'ASC';
  }

  $query = db_select('scheduler', 's')->extend('PagerDefault');
  $query->limit(50);
  $query->addJoin('LEFT', 'node', 'n', 's.nid = n.nid');
  $query->addJoin('LEFT', 'users', 'u', 'u.uid = n.uid');
  $query->fields('s', array('nid', 'publish_on', 'unpublish_on'));
  $query->fields('n', array('uid', 'status', 'title', 'type', 'status'));
  $query->addField('u', 'name');

  // If this function is being called from a user account page then only select
  // the nodes owned by that user. If the current user is viewing another users'
  // profile and they do not have 'administer nodes' permission then it won't
  // even get this far, as the tab will not be accessible.
  if ($show == 'user_only') {
    $query->condition('n.uid', $uid, '=');
    // Get user account for use later.
    $user = user_load($uid);
  }
  $query = $query->extend('TableSort')->orderByHeader($header);
  $result = $query->execute();
  $destination = backdrop_get_destination();
  $rows = array();

  foreach ($result as $node_data) {
    $node = node_load($node_data->nid);
    // Check the access allowed on this node.
    $can_edit = node_access('update', $node);
    $can_delete = node_access('delete', $node);
    // Set the operations depending on whether the node is valid or corrupt.
    $ops = array();
    if ($node->type) {
      // Node type is present so this indicates a valid join with the node
      // table. Provide regular operations to edit and delete the node.
      if ($can_edit) {
        $ops['edit'] = array(
          'title' => t('Edit'),
          'href' => 'node/' . $node->nid . '/edit',
          'query' => $destination,
        );
      }
      if ($can_delete) {
        $ops['delete'] = array(
          'title' => t('Delete'),
          'href' => 'node/' . $node->nid . '/delete',
          'query' => $destination,
        );
      }
    }
    else {
      // There was no matching row in the node table. Provide a special link to
      // delete the row from the Scheduler table.
      if ($can_delete) {
        $ops['delete'] = array(
          'title' => t('Delete'),
          'href' => 'admin/content/scheduler/delete/' . $node->nid,
          'query' => $destination,
        );
      }
    }

    $rows[] = array(
      ($node->title ? l($node->title, "node/$node->nid") : t('Missing data for node @nid', array('@nid' => $node->nid))),
      ($node->type ? check_plain(node_type_get_name($node)) : ''),
      ($node->type ? theme('username', array('account' => $node)) : ''),
      ($node->type ? ($node->status ? t('Published') : t('Unpublished')) : ''),
      ($node->publish_on ? format_date($node->publish_on) : '&nbsp;'),
      ($node->unpublish_on ? format_date($node->unpublish_on) : '&nbsp;'),
      array('data' => array(
        '#type' => 'operations',
        '#links' => $ops,
      )),
    );
  }
  if (count($rows) && ($pager = theme('pager'))) {
    $rows[] = array(
      array(
        'data' => $pager,
        'colspan' => count($rows['0']),
      ),
    );
  }
  $build['scheduler_list'] = array(
    '#theme' => 'table',
    '#header' => $header,
    '#rows' => $rows,
    '#empty' => ($show == 'user_only') ? t('There are no scheduled nodes for @username.', array('@username' => $user->name)) : t('There are no scheduled nodes.'),
  );
  return $build;
}

/**
 * Form constructor for the Scheduler Delete Row confirmation form.
 *
 * @see _scheduler_delete_row_confirm_submit()
 */
function _scheduler_delete_row_confirm($form, &$form_state, $nid) {
  $form['nid'] = array('#type' => 'value', '#value' => $nid);
  $details = array(t('Are you sure you want to delete the Scheduler row for missing node @nid?', array('@nid' => $nid)));
  $details[] = t('This action cannot be undone.');
  return confirm_form(
    $form,
    $details[0],
    // The cancel path will be changed automatically because the calling url
    // contains a destination value. We need to provide a default here anyway.
    'admin/content/scheduler',
    implode('<br>', $details),
    t('Delete'),
    t('Cancel')
  );
}

/**
 * Form submission handler for _scheduler_delete_row_confirm().
 */
function _scheduler_delete_row_confirm_submit($form, &$form_state) {
  // This is a special case, where we only want to delete the row in the
  // Scheduler table. The associated node does not exist any more so we cannot
  // use the normal node/$nid/delete process as that requires a node object.
  if ($form_state['values']['confirm']) {
    $nid = $form_state['values']['nid'];
    db_delete('scheduler')->condition('nid', $nid)->execute();
    watchdog('scheduler', 'Row for missing node @nid deleted from scheduler table.', array('@nid' => $nid));
    backdrop_set_message(t('The scheduler data for missing node @nid has been deleted.', array('@nid' => $nid)));
  }
}
