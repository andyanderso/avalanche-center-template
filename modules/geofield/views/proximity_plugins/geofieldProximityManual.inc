<?php

/**
 * @file
 * Contains geofieldProximityManual.
 */

class geofieldProximityManual extends geofieldProximityBase implements geofieldProximityPluginInterface {
  public function option_definition(&$options, $views_plugin) {
    $options['geofield_proximity_manual'] = array(
      'default' => array(
        'lat' => 0,
        'lon' => 0,
      )
    );
  }

  public function options_form(&$form, &$form_state, $views_plugin) {
    $form['geofield_proximity_manual'] = array(
      '#type' => 'geofield_latlon',
      '#title' => t('Source'),
      '#default_value' => $views_plugin->options['geofield_proximity_manual'],
      '#proximity_plugin_value_element' => TRUE,
      '#states' => array(
        'visible' => array(
          ':input[name*="options[source]"]' => array('value' => 'manual'),
        ),
      ),
    );
  }

  public function value_form(&$form, &$form_state, $views_plugin) {
    $form['value']['#origin_element'] = 'geofield_latlon';
  }

  /**
   * {@inheritdoc}
   */
  public function getSourceValue($views_plugin) {
    if (empty($views_plugin->value) || empty($views_plugin->value['origin'])) {
      return;
    }
    // Provided value or default value set by admin, both can be empty.
    // If the field were required, we wouldn't even get here.
    $lat = ($views_plugin->value['origin']['lat']) ?? $views_plugin->options['geofield_proximity_manual']['lat'];
    $lon = ($views_plugin->value['origin']['lon']) ?? $views_plugin->options['geofield_proximity_manual']['lon'];
    $lat = trim($lat);
    $lon = trim($lon);
    // We can not use empty(), as "0" is valid, too.
    if (strlen($lat) && strlen($lon)) {
      return array(
        'latitude' => $lat,
        'longitude' => $lon,
      );
    }
    return;
  }
}
