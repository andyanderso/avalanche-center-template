<?php
/**
 * @file
 * Default views plugins.
 */

/**
 * Implements hook_views_plugins().
 */
function geofield_map_views_plugins() {
  $plugins = array(
    'module' => 'geofield_map',
    'style' => array(
      'geofield_map_map' => array(
        'title' => t('Geofield Map'),
        'help' => t('Displays a View as an Geofield map.'),
        'handler' => 'geofield_map_plugin_style_map',
        'theme' => 'geofield_map_map',
        'theme path' => backdrop_get_path('module', 'geofield_map'),
        'path' => backdrop_get_path('module', 'geofield_map') . '/views',
        'uses fields' => TRUE,
        'uses row plugin' => FALSE,
        'uses options' => TRUE,
        'uses grouping' => FALSE,
        'type' => 'normal',
        'even empty' => TRUE,
      ),
    ),
  );

  return $plugins;
}
