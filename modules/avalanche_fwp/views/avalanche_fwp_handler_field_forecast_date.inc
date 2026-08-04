<?php
/**
 * @file
 * A node "created" date field handler that renders forecast dates in the
 * forecast's region timezone (only inside the advisory_views view; elsewhere it
 * behaves exactly like the standard date field).
 */

class avalanche_fwp_handler_field_forecast_date extends views_handler_field_date {

  /**
   * Ensure the node id is available so we can find the region timezone.
   */
  public function init(&$view, &$options) {
    parent::init($view, $options);
    $this->additional_fields['nid'] = array('table' => 'node', 'field' => 'nid');
  }

  public function query() {
    parent::query();
    $this->add_additional_fields();
  }

  public function render($values) {
    $tz = $this->_forecast_timezone($values);
    if ($tz === NULL) {
      return parent::render($values);
    }
    $value = $this->get_value($values);
    if (empty($value)) {
      return parent::render($values);
    }
    // Relative formats ("time ago" etc.) don't depend on timezone.
    $relative = array(
      'raw time ago', 'time ago', 'today time ago',
      'raw time hence', 'time hence', 'time span', 'raw time span',
      'inverse time span',
    );
    if (in_array($this->options['date_format'], $relative, TRUE)) {
      return parent::render($values);
    }
    $format = $this->options['date_format'];
    $custom = !empty($this->options['custom_date_format']) ? $this->options['custom_date_format'] : '';
    return format_date($value, $format, $custom, $tz);
  }

  /**
   * The region timezone for this forecast row, or NULL to use the default.
   * Scoped to the advisory view so no other view pays a node_load cost.
   */
  protected function _forecast_timezone($values) {
    if (empty($this->view->name) || $this->view->name !== 'advisory_views') {
      return NULL;
    }
    if (!function_exists('avalanche_fwp_forecast_timezone')) {
      return NULL;
    }
    $nid = $this->get_value($values, 'nid');
    if (!$nid) {
      return NULL;
    }
    $node = node_load($nid);
    if (!$node || $node->type !== 'advisory') {
      return NULL;
    }
    return avalanche_fwp_forecast_timezone($node);
  }

}
