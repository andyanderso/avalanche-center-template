<?php
/**
 * @file
 * Views integration for the Avalanche Field Work Plan module.
 */

/**
 * Implements hook_views_data().
 *
 * Exposes the safe-return check-in status, LEFT-joined to nodes so every plan
 * shows (checked-in or not).
 */
function avalanche_fwp_views_data() {
  $data['avalanche_fwp_status']['table']['group'] = t('Field Work Plan');
  $data['avalanche_fwp_status']['table']['join']['node'] = array(
    'left_field' => 'nid',
    'field' => 'nid',
    'type' => 'LEFT',
  );

  $data['avalanche_fwp_status']['checked_in'] = array(
    'title' => t('Checked in'),
    'help' => t('Whether the field party has checked in safely.'),
    'field' => array(
      'handler' => 'views_handler_field_boolean',
      'click sortable' => TRUE,
    ),
    'filter' => array('handler' => 'views_handler_filter_boolean_operator'),
    'sort' => array('handler' => 'views_handler_sort'),
  );

  $data['avalanche_fwp_status']['check_in_time'] = array(
    'title' => t('Time checked in'),
    'help' => t('When the field party checked in.'),
    'field' => array(
      'handler' => 'views_handler_field_date',
      'click sortable' => TRUE,
    ),
    'sort' => array('handler' => 'views_handler_sort_date'),
    'filter' => array('handler' => 'views_handler_filter_date'),
  );

  return $data;
}

/**
 * Implements hook_views_data_alter().
 *
 * Swap the node "created" date field handler for one that renders forecast
 * dates in the region timezone (scoped to advisory_views inside the handler).
 */
function avalanche_fwp_views_data_alter(&$data) {
  if (isset($data['node']['created']['field'])) {
    $data['node']['created']['field']['handler'] = 'avalanche_fwp_handler_field_forecast_date';
  }
}
