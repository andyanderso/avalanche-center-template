<?php
/**
 * @file
 * Provides hooks for integration with Schemaorg (https://www.drupal.org/project/schemaorg)
 */

/**
 * Schema.org format.
 *
 * Formats shapes for output in metadata using Schema.org format. This is
 * different from the WKT format provided by GeoGenerator, so just use a one
 * off function.
 */
function geofield_schemaorg_shape($item) {
  $output = '';
  $bottom = $item['bottom'];
  $left = $item['left'];
  $right = $item['right'];
  $top = $item['top'];

  switch ($item['geo_type']) {
    case 'polygon':
      $output  = $bottom . ',' . $left . ' ';
      $output .= $bottom . ',' . $right . ' ';
      $output .= $top . ',' . $right . ' ';
      $output .= $top . ',' . $left . ' ';
      $output .= $bottom . ',' . $left;
      break;
    case 'linestring':
      $output  = $bottom . ',' . $left . ' ';
      $output .= $bottom . ',' . $right . ' ';
      $output .= $top . ',' . $right . ' ';
      $output .= $top . ',' . $left;
      break;
  }

  return $output;
}

function _geofield_geo_types_options_callback() {
  module_load_include('inc', 'geofield', 'libraries/geophp/geoPHP');
  if (!class_exists('geoPHP')) {
    return;
  }
  return geoPHP::geometryList();
}

/**
 * Gets the a latlong property.
 */
function geofield_return_latlon($data, array $options, $name) {
  if ((is_array($data) || (is_object($data) && $data instanceof ArrayAccess)) && !is_null($data['lat']) && !is_null($data['lon'])) {
    return $data['lat'] . ',' . $data['lon'];
  }
  return NULL;
}

/**
 * Gets the Schema.org formatted shape value.
 */
function geofield_return_schemaorg_shape($data, array $options, $name) {
  if ((is_array($data) || (is_object($data) && $data instanceof ArrayAccess))) {
    return geofield_schemaorg_shape($data);
  }
  return NULL;
}
