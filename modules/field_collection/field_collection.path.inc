<?php

/**
 * @file
 */

/**
 * Implements hook_path_info().
 */
function field_collection_path_info() {
  $info['field_collection'] = array(
    'entity type' => 'field_collection',
    'token_type' => 'field_collection_item',
    'label' => t('Field collection'),
    'pattern label' => t('Field collection item paths'),
    'pattern description' => t('Default path pattern (applies to all field collections with blank patterns below)'),
    'pattern default' => '',
    'pattern items' => array(),
    'type delete callback' => '',
    'batch update callback' => '',
    'batch file' => '',
  );

  $instances = field_info_instances();
  foreach ($instances as $entity_type => $type_bundles) {
    foreach ($type_bundles as $bundle => $bundle_instances) {
      foreach ($bundle_instances as $field_name => $instance) {
        $field = field_info_field($field_name);
        if ($field['type'] === 'field_collection') {
          $info['field_collection']['pattern items'][$field_name] = t('Pattern for all field collection @field-collection paths', array('@field-collection' => $field_name));
        }
      }
    }
  }

  return $info;
}
