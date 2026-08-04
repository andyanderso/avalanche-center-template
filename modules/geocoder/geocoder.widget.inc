<?php
/**
 * @file
 * Geocoder Widget.
 */

/**
 * Implements hook_field_widget_info().
 */
function geocoder_field_widget_info() {
  $info = array(
    'geocoder' => array(
      'label' => t('Geocode from another field'),
      'field types' => array(),
      'behaviors' => array(
        'multiple values' => FIELD_BEHAVIOR_CUSTOM,
        'default value' => FIELD_BEHAVIOR_NONE,
      ),
    ),
  );

  if (module_exists('geofield')) {
    $info['geocoder']['field types'][] = 'geofield';
  }
  if (module_exists('geolocation')) {
    $info['geocoder']['field types'][] = 'geolocation_latlng';
  }
  if (module_exists('location')) {
    $info['geocoder']['field types'][] = 'location';
  }
  if (module_exists('geofield_postgis')) {
    $info['geocoder']['field types'][] = 'postgis';
  }

  return $info;
}

/**
 * Implements field_widget_settings_form().
 */
function geocoder_field_widget_settings_form($this_field, $instance) {
  $settings = $instance['widget']['settings'];

  $entity_fields = field_info_instances($instance['entity_type'], $instance['bundle']);
  $all_fields = field_info_fields();
  $supported_field_types = geocoder_supported_field_types();
  $processors = geocoder_handler_info();
  $handlers_by_type = array();
  $field_types = array();
  $valid_fields = array();
  $available_handlers = array();

  // Add in the title/name
  //@@TODO Do this programatically by getting entity_info
  switch ($instance['entity_type']) {
    case 'node':
      $all_fields['title'] = array(
        'field_name' => 'title',
        'type' => 'text',
      );

      $entity_fields['title']['label'] = t('Title');
      break;

    case 'taxonomy_term':
      $all_fields['name'] = array(
        'field_name' => 'name',
        'type' => 'text',
      );

      $entity_fields['name']['label'] = t('Name');
      break;

    case 'country':
      $all_fields['name'] = array(
        'field_name' => 'name',
        'type' => 'text',
      );

      $entity_fields['name']['label'] = t('Name');
      break;
  }

  // Get a list of all valid fields that we both support and are part of this entity
  foreach ($all_fields as $field) {
    if (array_key_exists($field['field_name'], $entity_fields)) {
      if (in_array($field['type'], array_keys($supported_field_types)) && ($field['field_name'] != $this_field['field_name'])) {
        $valid_fields[$field['field_name']] = $entity_fields[$field['field_name']]['label'];
        foreach ($supported_field_types[$field['type']] as $handler) {
          $available_handlers[$handler] = $processors[$handler]['title'];
          $handlers_by_type[$field['type']][] = $handler;
          $field_types[$field['field_name']] = $field['type'];
        }
      }
    }
  }

  $form['geocoder_field'] = array(
    '#type' => 'select',
    '#title' => t('Geocode from field'),
    '#default_value' => isset($settings['geocoder_field']) ? $settings['geocoder_field']: '',
    '#options' => $valid_fields,
    '#description' => t('Select which field you would like to geocode from.'),
    '#required' => TRUE,
  );

  $form['geocoder_handler'] = array(
    '#type' => 'select',
    '#title' => t('Geocoder'),
    '#prefix' => '<div id="geocoder-handler-div">',
    '#suffix' => '</div>',
    '#default_value' => isset($settings['geocoder_handler']) ? $settings['geocoder_handler']: '',
    '#options' => $available_handlers,
    '#description' => t('Select which type of geocoding handler you would like to use'),
    '#required' => TRUE,
  );

  $form['handler_settings'] = array(
    '#tree' => TRUE,
  );

  // Add the handler settings forms
  foreach ($processors as $handler_id => $handler) {
    if (isset($handler['settings_callback']) || isset($handler['terms_of_service'])) {
      $default_values = isset($settings['handler_settings'][$handler_id]) ? $settings['handler_settings'][$handler_id] : array();
      $form['handler_settings'][$handler_id] = array();
      $form['handler_settings'][$handler_id]['#type'] = 'fieldset';
      $form['handler_settings'][$handler_id]['#attributes'] = array('class' => array('geocoder-handler-setting', 'geocoder-handler-setting-' . $handler_id));
      $form['handler_settings'][$handler_id]['#title'] = $handler['title'] . ' Settings';
      $form['handler_settings'][$handler_id]['#states'] = array(
        'visible' => array(
          ':input[id="edit-instance-widget-settings-geocoder-handler"]' => array('value' => $handler_id),
        ),
      );

      if (isset($handler['terms_of_service'])) {
        $form['handler_settings'][$handler_id]['tos'] = array(
          '#type' => 'item',
          '#markup' => t('This handler has terms of service. Click the following link to learn more.') . ' ' . l($handler['terms_of_service'], $handler['terms_of_service']),
        );
      }

      if (isset($handler['settings_callback'])) {
        // Load the file.
        geocoder_get_handler($handler_id);

        $settings_callback = $handler['settings_callback'];
        $form['handler_settings'][$handler_id] = array_merge($form['handler_settings'][$handler_id], $settings_callback($default_values));
      }
    }
  }

  $form['delta_handling'] = array(
    '#type' => 'select',
    '#title' => t('Multi-value input handling'),
    '#description' => t('Should geometries from multiple inputs be: <ul><li>Matched with each input (e.g. One POINT for each address field)</li><li>Aggregated into a single MULTIPOINT geofield (e.g. One MULTIPOINT polygon from multiple address fields)</li><li>Broken up into multiple geometries (e.g. One MULTIPOINT to multiple POINTs.)</li></ul>'),
    '#default_value' => isset($settings['delta_handling']) ? $settings['delta_handling']: 'default',
    '#options' => array(
      'default' => 'Match Multiples (default)',
      'm_to_s' =>  'Multiple to Single',
      's_to_m' =>  'Single to Multiple',
      'c_to_s' =>  'Concatenate to Single',
      'c_to_m' =>  'Concatenate to Multiple',
    ),
    '#required' => TRUE,
  );

  // Add javascript to sync allowed values. Note that we are not using AJAX because we do not have access to the raw form_state here
  backdrop_add_js(array('geocoder_widget_settings' => array('handlers' => $handlers_by_type, 'types' => $field_types)), 'setting');
  backdrop_add_js(backdrop_get_path('module', 'geocoder') . '/geocoder.admin.js', 'file');

  return $form;
}

/**
 * Implements hook_field_attach_presave().
 *
 * Geocoding for the geocoder widget is done here to ensure that only validated
 * and fully processed fields values are accessed.
 */
function geocoder_field_attach_presave($entity_type, $entity) {
  // Loop over any geofield using our geocode widget
  $entity_info = entity_get_info($entity_type);
  $bundle_name = empty($entity_info['entity keys']['bundle']) ? $entity_type : $entity->{$entity_info['entity keys']['bundle']};
  foreach (field_info_instances($entity_type, $bundle_name) as $field_instance) {
    if ($field_instance['widget']['type'] === 'geocoder') {
      if (($field_value = geocoder_widget_get_field_value($entity_type, $field_instance, $entity)) !== FALSE) {
        $entity->{$field_instance['field_name']} = $field_value;
      }
    }
  }
}

/**
 * Get a field's value based on geocoded data.
 *
 * @param $entity_type
 *   Type of entity
 * @para field_instance
 *   Field instance definition array
 * @param $entity
 *  Optionally, the entity. You must pass either the entity or $source_field_values
 * @param $source_field_values
 *  Array of deltas / source field values. You must pass either this or $entity.
 *
 * @return
 *  Three possibilities could be returned by this function:
 *    - FALSE: do nothing.
 *    - An empty array: use it to unset the existing field value.
 *    - A populated array: assign a new field value.
 */
function geocoder_widget_get_field_value($entity_type, $field_instance, $entity = NULL, $source_field_values = NULL) {
  if (!$source_field_values && !$entity) {
    trigger_error('geocoder_widget_get_field_value: You must pass either $source_field_values OR $entity', E_USER_ERROR);
    return FALSE;
  }
  $entity_info = entity_get_info($entity_type);

  // Required settings
  if (isset($field_instance['widget']['settings']['geocoder_handler']) && isset($field_instance['widget']['settings']['geocoder_field'])) {
    $handler = geocoder_get_handler($field_instance['widget']['settings']['geocoder_handler']);
    $field_name = is_array($field_instance['widget']['settings']['geocoder_field']) ? reset($field_instance['widget']['settings']['geocoder_field']) : $field_instance['widget']['settings']['geocoder_field'];
    $target_info = field_info_field($field_instance['field_name']);

    // Determine the source type, if it's a entity-key, we mock it as a "text" field
    if (in_array($field_name, $entity_info['entity keys']) && $entity) {
      $field_info = array('type' => 'text', 'entity_key' => TRUE);
    }
    else {
      $field_info = field_info_field($field_name);
      $field_info['entity_key'] = FALSE;
    }

    // Get the source values
    if (!$source_field_values) {
      if ($field_info['entity_key'] && $entity) {
        $source_field_values = array(array('value' => $entity->$field_name));
      }
      elseif ($entity) {
        $source_field_values = field_get_items($entity_type, $entity, $field_name, isset($entity->language) ? $entity->language : NULL);
      }
      else {
        // We can't find the source values
        return FALSE;
      }
    }

    // Remove source values that are not valid.
    if ($source_field_values) {
      foreach ($source_field_values as $delta => $item) {
        if (!is_numeric($delta)) {
          unset($source_field_values[$delta]);
        }
      }
    }

    // If no valid source values were passed.
    if (empty($source_field_values)) {
      return array();
    }

    // For entities being updated, determine if another geocode is necessary
    if ($entity) {
      if (!empty($entity->original)) {
        //@@TODO: Deal with entity-properties (non-fields)
        //@@TODO: This isn't working with file fields. Should use some kind of lookup / map
        $field_original = field_get_items($entity_type, $entity->original, $field_name, isset($entity->original->language) ? $entity->original->language : NULL);
        $target_original = field_get_items($entity_type, $entity->original, $field_instance['field_name'], isset($entity->original->language) ? $entity->original->language : NULL);
        if (!empty($field_original) && !empty($target_original)) {
          $diff = geocoder_widget_array_recursive_diff($field_original, $source_field_values);
          if (empty($diff)) {
            return FALSE;
          }
        }
      }
    }

    // Get the handler-specific-settings
    if (isset($field_instance['widget']['settings']['handler_settings'][$handler['name']])) {
      $handler_settings = $field_instance['widget']['settings']['handler_settings'][$handler['name']];
    }
    else {
      $handler_settings = array();
    }

    // Determine how we deal with deltas (multi-value fields)
    if (empty($field_instance['widget']['settings']['delta_handling'])) {
      $delta_handling = 'default';
    }
    else {
      $delta_handling = $field_instance['widget']['settings']['delta_handling'];
    }

    // Check to see if we should be concatenating
    if ($delta_handling == 'c_to_s' || $delta_handling == 'c_to_m') {
      $source_field_values = geocoder_widget_get_field_concat($source_field_values);
    }

    // Allow other modules to alter values before we geocode them.
    backdrop_alter('geocoder_geocode_values', $source_field_values, $field_info, $handler_settings, $field_instance);

    if (is_array($source_field_values) && count($source_field_values)) {
      // Geocode geometries.
      $geometries = array();
      foreach ($source_field_values as $delta => $item) {
        $geometry = NULL;
        $config = config('geocoder.settings');
        if (!$config->get('geocoder_recode')) {
          // Attempt to retrieve from persistent cache.
          $geometry = geocoder_cache_get($handler['name'], $item, $handler_settings);
        }

        // No cache record, so fetch live.
        if ($geometry === NULL) {
          // Geocode any value from our source field.
          try {
            $geometry = call_user_func($handler['field_callback'], $field_info, $item, $handler_settings);

            // Save result persistently.
            geocoder_cache_set($geometry, $handler['name'], $item, $handler_settings);
          }
          // Exception affects just this one field item.
          catch (Exception $e) {
            watchdog_exception('geocoder', $e, NULL, array(), WATCHDOG_ERROR, geocoder_widget_get_link($entity_type, $entity));
            continue;
          }
        }

        if ($geometry instanceof Geometry) {
          $geometries[] = $geometry;
        }
        elseif ($config->get('geocoder_log_empty_results')) {
          watchdog('geocoder', 'No results for geocoding', NULL, WATCHDOG_NOTICE, geocoder_widget_get_link($entity_type, $entity));
        }
      }

      if (empty($geometries)) {
        // This field has no data, so set the field to an empty array in
        // order to delete its saved data.
        return array();
      }
      else {
        // Resolve multiple-values - get back values from our delta-resolver
        $values = geocoder_widget_resolve_deltas($geometries, $delta_handling, $target_info);

        // Set the values - geofields do not support languages
        return array(LANGUAGE_NONE => $values);
      }
    }
  }
}


/**
 * Get field items and info
 *
 * We always pass the full field-item array (with all columns) to the handler, but there is some preprocessing
 * that we need to do for the special case of entity-labels and multi-field concatenation
 * For these two special cases we "mock-up" a text-field and pass it back for geocoding
 */
function geocoder_widget_get_field_concat($items) {
  // Check if we should concatenate
  $concat = '';
  foreach ($items as $item) {
    if (!empty($item['value'])) {
      $concat .= trim($item['value']) . ', ';
    }
  }
  $concat = trim($concat, ', ');
  $items = array(array('value' => $concat));
  return $items;
}

/**
 * Geocoder Widget - Resolve Deltas
 *
 * Given a list of geometries, and user configuration on how to handle deltas,
 * we created a list of items to be inserted into the fields.
 */
function geocoder_widget_resolve_deltas($geometries, $delta_handling, $target_info) {
  $values = array();

  // Default delta handling: just pass one delta to the next
  if ($delta_handling == 'default') {
    foreach ($geometries as $geometry) {
      $values[] = geocoder_widget_values_from_geometry($geometry, $target_info);
    }
  }

  // Single-to-multiple handling - if we can, explode out the component geometries
  if ($delta_handling == 's_to_m' || $delta_handling == 'c_to_m') {
    $type = $geometries[0]->geometryType();
    if (in_array($type, array('MultiPoint', 'MultiLineString', 'MultiPolygon', 'GeometryCollection'))) {
      $components = $geometries[0]->getComponents();
      foreach ($components as $component) {
        $values[] = geocoder_widget_values_from_geometry($component, $target_info);
      }
    }
    else {
      $values[] = geocoder_widget_values_from_geometry($geometries[0], $target_info);
    }
  }

  // For multiple-to-single handling, run it though geometryReduce
  if ($delta_handling == 'm_to_s' || $delta_handling == 'c_to_s') {
    $reduced_geom = geoPHP::geometryReduce($geometries);
    $values[] = geocoder_widget_values_from_geometry($reduced_geom, $target_info);
  }

  return $values;
}

/**
 * Geocoder Widget - Field values from geometry
 *
 * Given a geometry and the field type, return back a values array for that field.
 * The passed back array represents a single delta.
 */
function geocoder_widget_values_from_geometry($geometry, $target_info) {
  if ($target_info['type'] == 'geofield') return geofield_get_values_from_geometry($geometry);
  if ($target_info['type'] == 'geolocation_latlng') {
    $centroid = $geometry->centroid();
    $lat = $centroid->y();
    $lng = $centroid->x();

    return array(
      'lat' => $lat,
      'lng' => $lng,
      'lat_sin' => sin(deg2rad($lat)),
      'lat_cos' => cos(deg2rad($lat)),
      'lng_rad' => deg2rad($lng),
    );
  }
  if ($target_info['type'] == 'location') {
    $centroid = $geometry->centroid();
    return array(
      'latitude' => $centroid->y(),
      'longitude' => $centroid->x(),
      'source' => 2,
    );
  }
  if ($target_info['type'] == 'postgis') {
    $srid = $geometry->getSRID() ? $geometry->getSRID() : '4326';
    $type = $target_info['settings']['type'];
    $postgis_geometry = new PostgisGeometry($type, $srid);
    $postgis_geometry->fromText($geometry->asText());
    $postgis_geometry->transform($target_info['settings']['srid']);
    return array(
      'geometry' => $postgis_geometry->getGeometry(),
    );
  }
}

/**
 * Geocoder Widget - Parse an address field.
 */
function geocoder_widget_parse_addressfield($field_item) {
  $address = array();

  $address[] = !empty($field_item['organization']) ? $field_item['organization'] : NULL;
  $address[] = !empty($field_item['premise']) ? $field_item['premise'] : NULL;
  $address[] = !empty($field_item['sub_premise']) ? $field_item['sub_premise'] : NULL;
  $address[] = !empty($field_item['thoroughfare']) ? $field_item['thoroughfare'] : NULL;
  $address[] = !empty($field_item['locality']) ? $field_item['locality'] : NULL;
  if (!empty($field_item['country']) && !empty($field_item['administrative_area'])) {
    module_load_include('inc', 'addressfield', 'addressfield.administrative_areas');
    $country = addressfield_get_administrative_areas($field_item['country']);
    $address[] = $country[$field_item['administrative_area']];
  }
  else {
    $address[] = !empty($field_item['administrative_area']) ? $field_item['administrative_area'] : NULL;
  }
  $address[] = !empty($field_item['sub_administrative_area']) ? $field_item['sub_administrative_area'] : NULL;

  if (!empty($field_item['country'])) {
    if (module_exists('countries')) {
      $country = country_load($field_item['country']);
      $field_item['country'] = $country->name;
    }
    else {
      // Convert country code to country name.
      include_once BACKDROP_ROOT . '/core/includes/locale.inc';
      $countries = country_get_list();
      if (array_key_exists($field_item['country'], $countries)) {
        $field_item['country'] = $countries[$field_item['country']];
      }
    }
    $address[] = $field_item['country'];
  }

  $address[] = !empty($field_item['postal_code']) ? $field_item['postal_code'] : NULL;

  return implode(',', array_filter($address));
}

/**
 * Geocoder Widget - Parse a location field
 */
function geocoder_widget_parse_locationfield($field_item) {
  $address = '';
  if (!empty($field_item['street']))      $address .= $field_item['street'] . ',';
  if (!empty($field_item['additional']))  $address .= $field_item['additional'] . ',';
  if (!empty($field_item['city']))        $address .= $field_item['city'] . ',';
  if (!empty($field_item['province']) && function_exists('location_province_name')) {
    $province_fullname = location_province_name($field_item['country'], $field_item['province']);
    $address .=  $province_fullname . ',';
  }
  if (!empty($field_item['country']))     $address .= $field_item['country'] . ',';
  if (!empty($field_item['postal_code'])) $address .= $field_item['postal_code'] . ',';

  $address = rtrim($address, ', ');

  return $address;
}

/**
 * Geocoder Widget utility function to build a link for an entity, ready for use in watchdog().
 */
function geocoder_widget_get_link($entity_type, $entity) {
  $uri_info = entity_uri($entity_type, $entity);
  list($id, , $bundle) = entity_extract_ids($entity_type, $entity);
  $label = t('View offending @entity_type (@bundle # @id)', array(
    '@entity_type' => $entity_type,
    '@bundle' => $bundle,
    '@id' => $id,
  ));
  return l($label, $uri_info['path'], $uri_info);
}

function geocoder_widget_array_recursive_diff($aArray1, $aArray2) {
  $aReturn = array();
  if (empty($aArray1)) {
    return $aReturn;
  }
  foreach ($aArray1 as $mKey => $mValue) {
    if (array_key_exists($mKey, $aArray2)) {
      if (is_array($mValue)) {
        $aRecursiveDiff = geocoder_widget_array_recursive_diff($mValue, $aArray2[$mKey]);
        if (count($aRecursiveDiff)) {
          $aReturn[$mKey] = $aRecursiveDiff;
        }
      }
      elseif (!empty($mValue)) {
        if ($mValue != $aArray2[$mKey]) {
          $aReturn[$mKey] = $mValue;
        }
      }
    }
    elseif (!empty($mValue)) {
      $aReturn[$mKey] = $mValue;
    }
  }
  return $aReturn;
 }
