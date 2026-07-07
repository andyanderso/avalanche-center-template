<?php

/**
 * @file
 * Scheduler cron functions.
 *
 * This file is included only when running a crontab job or executing the
 * lightweight cron via the admin interface.
 */

/**
 * Publish scheduled nodes.
 *
 * @return bool
 *   TRUE if any node has been published, FALSE otherwise.
 */
function _scheduler_publish() {
  $result = FALSE;

  // If the time now is greater than the time to publish a node, publish it.
  // The INNER join on 'node' and 'users' is just to ensure the nodes are valid.
  $query = db_select('scheduler', 's');
  $query->addField('s', 'nid');
  $query->addJoin('INNER', 'node', 'n', 's.nid = n.nid');
  $query->addJoin('INNER', 'users', 'u', 'u.uid = n.uid');
  $query->condition('s.publish_on', 0, '>');
  $query->condition('s.publish_on', REQUEST_TIME, '<=');
  $query_result = $query->execute();
  $nids = array();
  while ($node = $query_result->fetchObject()) {
    $nids[] = $node->nid;
  }

  $action = 'publish';

  // Allow other modules to add to the list of nodes to be published.
  $nids = array_unique(array_merge($nids, _scheduler_scheduler_nid_list($action)));

  // Allow other modules to alter the list of nodes to be published.
  backdrop_alter('scheduler_nid_list', $nids, $action);

  foreach ($nids as $nid) {
    $node = node_load($nid);
    $config = config('node.type.' . $node->type);

    // Check that scheduled publishing is (still) enabled for this type.
    if (!$config->get('scheduler_publish_enable')) {
      continue;
    }

    // Check that other modules allow the action on this node.
    if (!_scheduler_allow($node, $action)) {
      continue;
    }

    // Update timestamps.
    $node->changed = $node->publish_on;
    $old_creation_date = $node->created;
    if ($config->get('scheduler_publish_touch') == 1) {
      $node->created = $node->publish_on;
    }

    $create_publishing_revision = $config->get('scheduler_publish_revision') == 1;
    if ($create_publishing_revision) {
      $node->revision = TRUE;
      // Use a core date format to guarantee a time is included.
      $node->log = t('Node published by Scheduler on @now. Previous creation date was @date.', array(
        '@now' => format_date(REQUEST_TIME, 'short'),
        '@date' => format_date($old_creation_date, 'short'),
      ));
    }
    // Unset publish_on so the node will not get rescheduled by subsequent calls
    // to node_save(). Save the value for use when calling Rules.
    $publish_on = $node->publish_on;
    $node->publish_on = NULL;

    // Invoke scheduler API to allow modules to alter the node before it is
    // saved.
    // For 8.x this 'pre' call is moved up to just before 'Update timestamps'.
    // See https://www.drupal.org/node/2311273
    _scheduler_scheduler_api($node, 'pre_' . $action);

    // Use the actions system to publish the node.
    watchdog('scheduler', '@type: scheduled publishing of %title.', array('@type' => $node->type, '%title' => $node->title), WATCHDOG_NOTICE, l(t('view'), 'node/' . $node->nid, array('alias' => TRUE)));
    $node->status = NODE_PUBLISHED;
    $node->save();

    // Invoke the event to tell Rules that Scheduler has published this node.
    if (module_exists('rules')) {
      rules_invoke_event('scheduler_node_has_been_published_event', $node, $publish_on, $node->unpublish_on);
    }

    // Invoke scheduler API for modules to react after the node is published.
    _scheduler_scheduler_api($node, $action);

    $result = TRUE;
  }

  return $result;
}

/**
 * Unpublish scheduled nodes.
 *
 * @return bool
 *   TRUE is any node has been unpublished, FALSE otherwise.
 */
function _scheduler_unpublish() {
  $result = FALSE;

  // If the time is greater than the time to unpublish a node, unpublish it.
  // The INNER join on 'node' and 'users' is just to ensure the nodes are valid.
  $query = db_select('scheduler', 's');
  $query->addField('s', 'nid');
  $query->addJoin('INNER', 'node', 'n', 's.nid = n.nid');
  $query->addJoin('INNER', 'users', 'u', 'u.uid = n.uid');
  $query->condition('s.unpublish_on', 0, '>');
  $query->condition('s.unpublish_on', REQUEST_TIME, '<=');
  $query_result = $query->execute();
  $nids = array();
  while ($node = $query_result->fetchObject()) {
    $nids[] = $node->nid;
  }

  $action = 'unpublish';

  // Allow other modules to add to the list of nodes to be unpublished.
  $nids = array_unique(array_merge($nids, _scheduler_scheduler_nid_list($action)));

  // Allow other modules to alter the list of nodes to be unpublished.
  backdrop_alter('scheduler_nid_list', $nids, $action);

  foreach ($nids as $nid) {
    $node = node_load($nid);
    $config = config('node.type.' . $node->type);

    // Check that scheduled publishing is (still) enabled for this type.
    if (!$config->get('scheduler_unpublish_enable')) {
      continue;
    }

    // Check that other modules allow the action on this node.
    if (!_scheduler_allow($node, $action)) {
      continue;
    }

    // Do not process the node if it has a publish_on time which is in the past,
    // as this implies that scheduled publishing has been blocked by one of the
    // API functions we provide. Hence unpublishing should be halted too.
    if (!empty($node->publish_on) && $node->publish_on <= REQUEST_TIME) {
      continue;
    }

    // Update timestamps.
    $old_change_date = $node->changed;
    $node->changed = $node->unpublish_on;

    $create_unpublishing_revision = $config->get('scheduler_unpublish_revision') == 1;
    if ($create_unpublishing_revision) {
      $node->revision = TRUE;
      // Use a core date format to guarantee a time is included.
      $node->log = t('Node unpublished by Scheduler on @now. Previous change date was @date.', array(
        '@now' => format_date(REQUEST_TIME, 'short'),
        '@date' => format_date($old_change_date, 'short'),
      ));
    }
    // Unset unpublish_on so the node will not get rescheduled by subsequent
    // calls to node_save(). Save the value for use when calling Rules.
    $unpublish_on = $node->unpublish_on;
    $node->unpublish_on = NULL;

    // Invoke scheduler API to allow modules to alter the node before it is
    // saved.
    // For 8.x this 'pre' call is moved up to just before 'Update timestamps'.
    // See https://www.drupal.org/node/2311273
    _scheduler_scheduler_api($node, 'pre_' . $action);

    // Use the actions system to unpublish the node.
    watchdog('scheduler', '@type: scheduled unpublishing of %title.', array('@type' => $node->type, '%title' => $node->title), WATCHDOG_NOTICE, l(t('view'), 'node/' . $node->nid, array('alias' => TRUE)));
    $node->status = NODE_NOT_PUBLISHED;
    $node->save();

    // Invoke event to tell Rules that Scheduler has unpublished this node.
    if (module_exists('rules')) {
      rules_invoke_event('scheduler_node_has_been_unpublished_event', $node, $node->publish_on, $unpublish_on);
    }

    // Invoke scheduler API for modules to react after the node is unpublished.
    _scheduler_scheduler_api($node, 'unpublish');

    $result = TRUE;
  }

  return $result;
}

/**
 * Gather node IDs for all nodes that need to be $action'ed.
 *
 * @param string $action
 *   The action being performed, either "publish" or "unpublish".
 *
 * @return array
 *   An array of node ids.
 */
function _scheduler_scheduler_nid_list($action) {
  $nids = array();

  foreach (module_implements('scheduler_nid_list') as $module) {
    $function = $module . '_scheduler_nid_list';
    $nids = array_merge($nids, $function($action));
  }

  return $nids;
}

/**
 * Run the lightweight cron.
 *
 * The Scheduler part of the processing performed here is the same as in the
 * normal Backdrop cron run. The difference is that only scheduler_cron() is
 * executed, no other modules hook_cron() functions are called.
 *
 * This function is called from the external crontab job via url /scheduler/cron
 * or it can be run interactively from the Scheduler configuration page at
 * /admin/config/content/scheduler/cron.
 */
function _scheduler_run_cron() {
  $log = config_get('scheduler.settings', 'lightweight_log');
  if ($log) {
    watchdog('scheduler', 'Lightweight cron run activated', array(), WATCHDOG_NOTICE);
  }
  scheduler_cron();
  if (ob_get_level() > 0) {
    $handlers = ob_list_handlers();
    if (isset($handlers[0]) && $handlers[0] == 'default output handler') {
      ob_clean();
    }
  }
  if ($log) {
    watchdog('scheduler', 'Lightweight cron run completed', array(), WATCHDOG_NOTICE, l(t('settings'), 'admin/config/content/scheduler/cron'));
  }

  $menu_item = menu_get_item();
  if ($menu_item['path'] == 'admin/config/content/scheduler/cron') {
    // This cron run has been initiated manually from the configuration form.
    // Give a message and return something so that an output page is created.
    // No output should be returned if running from a crontab job.
    if (module_exists('dblog')) {
      backdrop_set_message(t('Lightweight cron run completed - see <a href="@url">log</a> for details.', array('@url' => url('admin/reports/dblog'))));
    }
    else {
      backdrop_set_message(t('Lightweight cron run completed.'));
    }
    return ' ';
  }
  // backdrop_exit() is the proper controlled way to terminate the request, as
  // this will invoke all implementations of hook_exit().
  backdrop_exit();
}
