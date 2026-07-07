<?php

/**
 * @file
 * Implementation of OpenLayers behavior.
 */

/**
 * Map Form Values Behavior
 */
class openlayers_behavior_geofield extends openlayers_behavior {
  function options_init() {
    return array(
      'geofield' => '',
    );
  }

  function render(&$map) {
    $geopath = backdrop_get_path('module', 'geofield');
    backdrop_add_css($geopath . '/includes/behaviors/css/openlayers_behavior_geofield.css');
    backdrop_add_js($geopath . '/includes/behaviors/js/openlayers_behavior_geofield.js');
    return $this->options;
  }
}
