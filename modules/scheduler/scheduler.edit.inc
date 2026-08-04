<?php

/**
 * @file
 * Scheduler node edit functions.
 *
 * This file is included only when a node is being edited by a user who has
 * permission to schedule nodes, and the node-type is enabled for scheduling.
 */

/**
 * Helper function that does all the work for the real hook_form_alter().
 *
 * @see scheduler_form_alter()
 */
function _scheduler_form_alter(&$form, $form_state) {
  $form['#config'] = 'node.type.' . $form['type']['#value'];
  $config = config('node.type.' . $form['type']['#value']);
  $scheduler_config = config('scheduler.settings');
  $publishing_enabled = $config->get('scheduler_publish_enable') == 1;
  $unpublishing_enabled = $config->get('scheduler_unpublish_enable') == 1;
  $date_format = $scheduler_config->get('date_format');
  $date_only_format = $scheduler_config->get('date_only_format');
  $time_only_format = $scheduler_config->get('time_only_format');
  $date_only_allowed = $scheduler_config->get('allow_date_only');
  $date_min = date('Y-m-d');
  $date_limit = $scheduler_config->get('date_limit');
  if ($date_limit) {
    $date_limit_quantity = $scheduler_config->get('date_limit_quantity');
    $date_limit_units = $scheduler_config->get('date_limit_units');
    $date_max = date('Y-m-d', strtotime('+' . $date_limit_quantity . ' ' . $date_limit_units));
  }
  else {
    $date_max = date('Y-m-d', strtotime('+30 years'));
  }
  $use_date_popup = _scheduler_use_date_popup();
  $internal_date_format = $use_date_popup ? SCHEDULER_DATE_FORMAT : $date_format;
  $node = $form['#node'];

  // If this is a preview then get the values from the form, not the
  // database.
  if (isset($form_state['values']['op']) && $form_state['values']['op'] == t('Preview')) {
    $defaults = new stdClass();
    $defaults->publish_on = $publishing_enabled ? $form_state['values']['publish_on'] : NULL;
    $defaults->unpublish_on = $unpublishing_enabled ? $form_state['values']['unpublish_on'] : NULL;
  }
  elseif (isset($node->nid) && $node->nid > 0) {
    // Load the values from the database if we are viewing an existing node.
    $query = db_select('scheduler', 's');
    $query->fields('s', array('publish_on', 'unpublish_on'));
    $query->condition('s.nid', $node->nid, '=');
    $defaults = $query->execute()->fetchObject();
  }
  else {
    // Initialise standard values.
    $defaults = new stdClass();
    // Respect presets added by functions like
    // scheduler_field_attach_prepare_translation_alter().
    $defaults->publish_on = isset($node->publish_on) ? $node->publish_on : NULL;
    $defaults->unpublish_on = isset($node->unpublish_on) ? $node->unpublish_on : NULL;
  }

  // If there is a text value then convert it to a Unix timestamp.
  if (isset($defaults->publish_on) && $defaults->publish_on && !is_numeric($defaults->publish_on)) {
    $defaults->publish_on = _scheduler_strtotime($defaults->publish_on);
  }
  if (isset($defaults->unpublish_on) && $defaults->unpublish_on && !is_numeric($defaults->unpublish_on)) {
    $defaults->unpublish_on = _scheduler_strtotime($defaults->unpublish_on);
  }

  // A publish_on date is required if the content type option is set and the
  // node is being created or it currently has a scheduled publishing date.
  $publishing_required = $config->get('scheduler_publish_required') == 1
    && (empty($node->nid) || ($node->status == 0 && !empty($node->publish_on)));

  // An unpublish_on date is required if the content type option is set and
  // the node is being created or the current status is published or the
  // node is scheduled to be published.
  $unpublishing_required = $config->get('scheduler_unpublish_required') == 1
    && (empty($node->nid) || $node->status == 1 || !empty($node->publish_on));

  $use_vertical_tabs = $config->get('scheduler_use_vertical_tabs');
  $fieldset_extended = ((isset($defaults->publish_on) && $defaults->publish_on != 0)
    || (isset($defaults->unpublish_on) && $defaults->unpublish_on != 0)
    || $publishing_required
    || $unpublishing_required
    || $config->get('scheduler_expand_fieldset'));

  $form['scheduler_settings'] = array(
    '#type' => 'fieldset',
    '#title' => t('Scheduling options'),
    '#collapsible' => TRUE,
    '#collapsed' => !$fieldset_extended,
    '#weight' => 35,
  );

  // Add Scheduler settings to Vertical Tabs group and attach the javascript.
  if ($use_vertical_tabs) {
    $form['scheduler_settings']['#group'] = 'additional_settings';
    $form['scheduler_settings']['#attached']['js'][] = backdrop_get_path('module', 'scheduler') . '/js/scheduler_vertical_tabs.js';
  }

  $extra_info = config_get('scheduler.settings', 'extra_info');
  if ($extra_info && $extra_info != '') {
    $form['scheduler_settings']['extra_info'] = array(
      '#type' => 'item',
      '#markup' => filter_xss_admin($extra_info),
    );
  }

  // Define the descriptions depending on whether the time can be skipped.
  $descriptions = array();
  if ($date_only_allowed && ($date_only_format != $date_format)) {
    $descriptions['format'] = t('Format: %date_only_format or %standard_format.', array(
      '%date_only_format' => format_date(time(), 'custom', $date_only_format),
      '%standard_format' => format_date(time(), 'custom', $date_format),
    ));
  }
  else {
    $descriptions['format'] = t('Format: %standard_format.', array(
      '%standard_format' => format_date(time(), 'custom', $date_format),
    ));
  }
  // Show the default time so users know what they will get if they do not
  // enter a time.
  $default_time = '00:00:00';
  if ($date_only_allowed) {
    $default_time = $scheduler_config->get('default_time');
    $default_time_strtotime = strtotime($default_time);
    $descriptions['default'] = t('The default time is @default_time.', array(
      '@default_time' => format_date($default_time_strtotime, 'custom', $time_only_format ? $time_only_format : SCHEDULER_TIME_ONLY_FORMAT),
    ));
  }
  // Show the date range if there is a limit.
  if ($date_limit) {
    $descriptions['range'] = t('Entered date must be before @date_max.', array(
      '@date_max' => format_date(strtotime($date_max), 'custom', $date_only_format),
    ));
  }

  if ($publishing_enabled) {
    if (!$publishing_required) {
      $descriptions['blank'] = t('Leave the date blank for no scheduled publishing.');
    }

    if ($use_date_popup) {
      unset($descriptions['format']);
      $form['scheduler_settings']['publish_on'] = array(
        '#type' => 'html_datetime',
        '#title' => t('Publish on'),
        '#default_value' => array(
          'date' => !empty($defaults->publish_on) ? format_date($defaults->publish_on, 'custom', DATE_FORMAT_DATE) : '',
          'time' => !empty($defaults->publish_on) ? format_date($defaults->publish_on, 'custom', DATE_FORMAT_TIME) : $default_time,
        ),
        '#attributes' => array(
          'date' => array(
            'min' => $date_min,
            'max' => $date_max,
            'placeholder' => t('e.g. @date', array(
              '@date' => format_date(REQUEST_TIME, 'custom', DATE_FORMAT_DATE),
            )),
          ),
          'time' => array(
            'placeholder' => t('e.g. @date', array(
              '@date' => format_date(REQUEST_TIME, 'custom', DATE_FORMAT_TIME),
            )),
          ),
        ),
        '#description' => filter_xss(implode(' ', $descriptions)),
        '#required' => $publishing_required,
      );
    }
    else {
      $form['scheduler_settings']['publish_on'] = array(
        '#type' => 'textfield',
        '#title' => t('Publish on'),
        '#maxlength' => 30,
        '#required' => $publishing_required,
        '#default_value' => !empty($defaults->publish_on) ? format_date($defaults->publish_on, 'custom', $internal_date_format) : '',
        '#description' => filter_xss(implode(' ', $descriptions)),
        '#value_callback' => 'scheduler_date_value_callback',
      );
    }
  }

  if ($unpublishing_enabled) {
    if (!$unpublishing_required) {
      $descriptions['blank'] = t('Leave the date blank for no scheduled unpublishing.');
    }
    else {
      unset($descriptions['blank']);
    }
    if ($use_date_popup) {
      unset($descriptions['format']);
      $form['scheduler_settings']['unpublish_on'] = array(
        '#type' => 'html_datetime',
        '#title' => t('Unpublish on'),
        '#default_value' => array(
          'date' => !empty($defaults->unpublish_on) ? format_date($defaults->unpublish_on, 'custom', DATE_FORMAT_DATE) : '',
          'time' => !empty($defaults->unpublish_on) ? format_date($defaults->unpublish_on, 'custom', DATE_FORMAT_TIME) : $default_time,
        ),
        '#attributes' => array(
          'date' => array(
            'min' => $date_min,
            'max' => $date_max,
            'placeholder' => t('e.g. @date', array(
              '@date' => format_date(REQUEST_TIME, 'custom', DATE_FORMAT_DATE),
            )),
          ),
          'time' => array(
            'placeholder' => t('e.g. @date', array(
              '@date' => format_date(REQUEST_TIME, 'custom', DATE_FORMAT_TIME),
            )),
          ),
        ),
        '#description' => filter_xss(implode(' ', $descriptions)),
        '#required' => $unpublishing_required,
      );
    }
    else {
      $form['scheduler_settings']['unpublish_on'] = array(
        '#type' => 'textfield',
        '#title' => t('Unpublish on'),
        '#maxlength' => 30,
        '#required' => $unpublishing_required,
        '#default_value' => !empty($defaults->unpublish_on) ? format_date($defaults->unpublish_on, 'custom', $internal_date_format) : '',
        '#description' => filter_xss(implode(' ', $descriptions)),
        '#value_callback' => 'scheduler_date_value_callback',
      );
    }
  }
}

/**
 * Callback function for the Scheduler date entry elements.
 */
function scheduler_date_value_callback(&$element, $input, &$form_state) {
  // When processing a delete operation the user should not be forced to enter a
  // date. Hence set the scheduler date element's #required attribute to FALSE.
  // Test the 'triggering_element' value against $form_state['values']['delete']
  // as this will match Delete button even if the text is translated.
  // @see https://www.drupal.org/node/1614880
  if (isset($form_state['triggering_element']['#value']) && isset($form_state['values']['delete']) && $form_state['triggering_element']['#value'] == $form_state['values']['delete']) {
    // At some point between October 2013 and August 2017 this code became
    // unnecessary. Nodes can now be deleted when 'required' is set and when no
    // date is entered, even without setting #required to FALSE here. It may be
    // due to a core change between 7.23 and 7.56? Leave this line as-is just
    // for safety.
    $element['#required'] = FALSE;
  }
  // If using date popup then process the callback that would have been done had
  // Scheduler not replaced this with its own one. If using plain text entry
  // then no return value is needed.
  if (_scheduler_use_date_popup()) {
    return date_popup_element_value_callback($element, $input, $form_state);
  }
}
