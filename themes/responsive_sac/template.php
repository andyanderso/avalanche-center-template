<?php

/**
 * Add body classes if certain regions have content.
 *
 * In Backdrop, $variables['page'] in html preprocess is the page render array,
 * not an array of regions. Body classes based on region content are instead set
 * in responsive_sac_preprocess_layout() below.
 */
function responsive_sac_preprocess_html(&$variables) {
  // IE conditional stylesheets (files may not exist; wrapped in file_exists check).
  $ie_css = path_to_theme() . '/css/ie.css';
  if (file_exists(BACKDROP_ROOT . '/' . $ie_css)) {
    backdrop_add_css($ie_css, array('group' => CSS_THEME, 'browsers' => array('IE' => 'lte IE 7', '!IE' => FALSE), 'preprocess' => FALSE));
  }
  $ie6_css = path_to_theme() . '/css/ie6.css';
  if (file_exists(BACKDROP_ROOT . '/' . $ie6_css)) {
    backdrop_add_css($ie6_css, array('group' => CSS_THEME, 'browsers' => array('IE' => 'IE 6', '!IE' => FALSE), 'preprocess' => FALSE));
  }
}


/**
 * Override or insert variables into the page template for HTML output.
 */
function responsive_sac_process_html(&$variables) {
  // Color module integration (function name differs between D7 and Backdrop).
  if (module_exists('color') && function_exists('_color_html_alter')) {
    _color_html_alter($variables);
  }
}


function responsive_sac_preprocess_page(&$variables) {
  // Load modern web fonts. The theme's D7-style fonts[google_fonts_api] .info
  // directive is ignored by Backdrop, so the stylesheet is added here (during
  // page build, like other external CSS) so it is reliably emitted.
  backdrop_add_css('https://fonts.googleapis.com/css2?family=Barlow+Semi+Condensed:wght@500;600;700&family=Inter:wght@400;500;600;700&display=swap', array(
    'group' => CSS_THEME,
    'every_page' => TRUE,
    'weight' => -100,
    'basename' => 'responsive-sac-google-fonts.css',
  ));

  // Search box is no longer rendered in the page template; it is now a block
  // placed in a layout region. Kept here for backwards compatibility if
  // any template still references $search_box.
  $form = backdrop_get_form('search_form');
  $search_box = backdrop_render($form);
  $variables['search_box'] = $search_box;
}

/**
 * Preprocess variables for the layout template.
 *
 * Injects theme settings and computes body classes based on region content.
 */
function responsive_sac_preprocess_layout(&$variables) {
  // Inject social/contact URLs from theme settings.
  $variables['email']    = theme_get_setting('email_url');
  $variables['facebook'] = theme_get_setting('facebook_url');
  $variables['twitter']  = theme_get_setting('twitter_url');
  $variables['instagram'] = theme_get_setting('instagram_url');
  $variables['youtube']  = theme_get_setting('youtube_url');
  $variables['header_background'] = theme_get_setting('backgroung_header_img');

  // Add body classes based on which regions have content.
  if (!empty($variables['content']['featured'])) {
    $variables['classes'][] = 'featured';
  }
  if (!empty($variables['content']['triptych_first'])
    || !empty($variables['content']['triptych_middle'])
    || !empty($variables['content']['triptych_last'])) {
    $variables['classes'][] = 'triptych';
  }
  if (!empty($variables['content']['footer_firstcolumn'])
    || !empty($variables['content']['footer_secondcolumn'])
    || !empty($variables['content']['footer_thirdcolumn'])
    || !empty($variables['content']['footer_fourthcolumn'])
    || !empty($variables['content']['footer_fifthcolumn'])) {
    $variables['classes'][] = 'footer-columns';
  }

  // Map data for node 13628 (danger ratings map page).
  if (!empty($variables['node']) && $variables['node']->nid == 13628) {
    $map_inc = backdrop_get_path('theme', 'responsive_sac') . '/inc/map.inc';
    if (file_exists(BACKDROP_ROOT . '/' . $map_inc)) {
      require_once(BACKDROP_ROOT . '/' . $map_inc);
      $regions = _get_regions();
      $data = _get_ratings($regions);
      $variables['danger_ratings'] = $data;
      $js_path = backdrop_get_path('theme', 'responsive_sac') . '/js/';
      if (file_exists(BACKDROP_ROOT . '/' . $js_path . 'jquery.dimensions.js')) {
        backdrop_add_js($js_path . 'jquery.dimensions.js', 'file');
      }
      if (file_exists(BACKDROP_ROOT . '/' . $js_path . 'jquery.tooltip.min.js')) {
        backdrop_add_js($js_path . 'jquery.tooltip.min.js', 'file');
      }
    }
  }
}


/**
 * Override or insert variables into the page template.
 */
function responsive_sac_process_page(&$variables) {
  // Color module integration (function name differs between D7 and Backdrop).
  if (module_exists('color') && function_exists('_color_page_alter')) {
    _color_page_alter($variables);
  }
  // Always print the site name and slogan, but if they are toggled off, we'll
  // just hide them visually.
  $variables['hide_site_name']   = theme_get_setting('toggle_name') ? FALSE : TRUE;
  $variables['hide_site_slogan'] = theme_get_setting('toggle_slogan') ? FALSE : TRUE;
  if ($variables['hide_site_name']) {
    $variables['site_name'] = filter_xss_admin(config_get('system.core', 'site_name'));
  }
  if ($variables['hide_site_slogan']) {
    $variables['site_slogan'] = filter_xss_admin(config_get('system.core', 'site_slogan'));
  }

  if (isset($variables['node'])) {
    $node = $variables['node'];
    if ($node->type == 'advisory') {
      $variables['theme_hook_suggestions'][] = 'page__advisory';
    }
    if ($node->type == 'snowpack_summary') {
      $variables['theme_hook_suggestions'][] = 'page__snowpack_summary';
    }
  }
}

/**
 * Preprocess variables for the header block (header.tpl.php).
 */
function responsive_sac_preprocess_header(&$variables) {
  $variables['email']    = theme_get_setting('email_url');
  $variables['facebook'] = theme_get_setting('facebook_url');
  $variables['twitter']  = theme_get_setting('twitter_url');
  $variables['instagram'] = theme_get_setting('instagram_url');
  $variables['youtube']  = theme_get_setting('youtube_url');
  $variables['hide_site_name']   = theme_get_setting('toggle_name') ? FALSE : TRUE;
  $variables['hide_site_slogan'] = theme_get_setting('toggle_slogan') ? FALSE : TRUE;
  if ($variables['hide_site_name']) {
    $variables['site_name'] = filter_xss_admin(config_get('system.core', 'site_name'));
  }
  if ($variables['hide_site_slogan']) {
    $variables['site_slogan'] = filter_xss_admin(config_get('system.core', 'site_slogan'));
  }
}


/**
 * Implements hook_preprocess_maintenance_page().
 */
function responsive_sac_preprocess_maintenance_page(&$variables) {
  // By default, site_name is set to Backdrop if no db connection is available
  // or during site installation. Setting site_name to an empty string makes
  // the site and update pages look cleaner.
  // @see template_preprocess_maintenance_page
  if (!$variables['db_is_active']) {
    $variables['site_name'] = '';
  }
  $bartik_path = backdrop_get_path('theme', 'bartik');
  if (!empty($bartik_path) && file_exists(BACKDROP_ROOT . '/' . $bartik_path . '/css/maintenance-page.css')) {
    backdrop_add_css($bartik_path . '/css/maintenance-page.css');
  }
}


/**
 * Override or insert variables into the maintenance page template.
 */
function responsive_sac_process_maintenance_page(&$variables) {
  // Always print the site name and slogan, but if they are toggled off, we'll
  // just hide them visually.
  $variables['hide_site_name']   = theme_get_setting('toggle_name') ? FALSE : TRUE;
  $variables['hide_site_slogan'] = theme_get_setting('toggle_slogan') ? FALSE : TRUE;
  if ($variables['hide_site_name']) {
    $variables['site_name'] = filter_xss_admin(config_get('system.core', 'site_name'));
  }
  if ($variables['hide_site_slogan']) {
    $variables['site_slogan'] = filter_xss_admin(config_get('system.core', 'site_slogan'));
  }
}


/**
 * Override or insert variables into the node template.
 */
function responsive_sac_preprocess_node(&$variables) {
  $node = $variables['node'];
  // if ($variables['view_mode'] == 'full' && node_is_page($variables['node'])) {
   if ($variables['view_mode'] == 'full') {
    $variables['classes_array'][] = 'node-full';
    if ($variables['node']->type == 'advisory') {
      require_once('inc/advisory.inc');
      $variables['advisory'] = responsive_sac_get_advisory_data($variables['node']);
      $blr = array_fill(0, 24, 0);
      $a = array('problem_1', 'problem_2', 'problem_3');
      foreach ($a as $item) {
        if (isset($variables['advisory'][$item])) {
          $rose = $variables['advisory'][$item]['rose'];
          $i = 0;
          while ($i < 24) {
            if ($rose[$i] > $blr[$i]) { $blr[$i] = $rose[$i]; }
            $i++;
          }
        }
      }
      $variables['advisory']['bottom_line_rose'] = $blr;
    }
  }
}


/**
 * Override or insert variables into the block template.
 */
function responsive_sac_preprocess_block(&$variables) {
  // In the header region visually hide block titles.
  if (isset($variables['block']->region) && $variables['block']->region == 'header') {
    $variables['title_attributes_array']['class'][] = 'element-invisible';
  }
}


/**
 * Implements theme_field__field_type().
 */
function responsive_sac_field__taxonomy_term_reference($variables) {
  $output = '';

  // Render the label, if it's not hidden.
  if (!$variables['label_hidden']) {
    $output .= '<h3 class="field-label">' . $variables['label'] . ': </h3>';
  }

  // Render the items.
  $output .= ($variables['element']['#label_display'] == 'inline') ? '<ul class="links inline">' : '<ul class="links">';
  foreach ($variables['items'] as $delta => $item) {
    $output .= '<li class="taxonomy-term-reference-' . $delta . '"' . $variables['item_attributes'][$delta] . '>' . backdrop_render($item) . '</li>';
  }
  $output .= '</ul>';

  // Render the top-level DIV.
  $output = '<div class="' . $variables['classes'] . (!in_array('clearfix', $variables['classes_array']) ? ' clearfix' : '') . '"' . $variables['attributes'] .'>' . $output . '</div>';

  return $output;
}


/**
 * Override default form element placement.
 *
 * 1. Move description directly below label and before input element.
 *
 */
function responsive_sac_form_element($variables) {
  $element = &$variables['element'];
  // This is also used in the installer, pre-database setup.
  $t = get_t();

  // This function is invoked as theme wrapper, but the rendered form element
  // may not necessarily have been processed by form_builder().
  $element += array(
    '#title_display' => 'before',
  );

  // Add element #id for #type 'item'.
  if (isset($element['#markup']) && !empty($element['#id'])) {
    $attributes['id'] = $element['#id'];
  }
  // Add element's #type and #name as class to aid with JS/CSS selectors.
  $attributes['class'] = array('form-item');
  if (!empty($element['#type'])) {
    $attributes['class'][] = 'form-type-' . strtr($element['#type'], '_', '-');
  }
  if (!empty($element['#name'])) {
    $attributes['class'][] = 'form-item-' . strtr($element['#name'], array(' ' => '-', '_' => '-', '[' => '-', ']' => ''));
  }
  // Add a class for disabled elements to facilitate cross-browser styling.
  if (!empty($element['#attributes']['disabled'])) {
    $attributes['class'][] = 'form-disabled';
  }
  $output = '<div' . backdrop_attributes($attributes) . '>' . "\n";

  // If #title is not set, we don't display any label or required marker.
  if (!isset($element['#title'])) {
    $element['#title_display'] = 'none';
  }
  $prefix = isset($element['#field_prefix']) ? '<span class="field-prefix">' . $element['#field_prefix'] . '</span> ' : '';
  $suffix = isset($element['#field_suffix']) ? ' <span class="field-suffix">' . $element['#field_suffix'] . '</span>' : '';

  switch ($element['#title_display']) {
    case 'before':
    case 'invisible':
      $output .= ' ' . theme('form_element_label', $variables);
      if (!empty($element['#description'])) {
        $output .= '<div class="description">' . $element['#description'] . "</div>\n";
      }
      $output .= ' ' . $prefix . $element['#children'] . $suffix . "\n";
      break;

    case 'after':
      if (!empty($element['#description'])) {
        $output .= '<div class="description">' . $element['#description'] . "</div>\n";
      }
      $output .= ' ' . $prefix . $element['#children'] . $suffix;
      $output .= ' ' . theme('form_element_label', $variables) . "\n";
      break;

    case 'none':
    case 'attribute':
      // Output no label and no required marker, only the children.
      $output .= ' ' . $prefix . $element['#children'] . $suffix . "\n";
      break;
  }

  $output .= "</div>\n";

  return $output;
}

function responsive_sac_form_alter(&$form, &$form_state, $form_id) {
  if ($form_id == 'search_form'){
    unset($form['basic']['keys']['#title']);
    $form['basic']['submit']['#value'] = '';
  }
}

/**
* Default theme function for all RSS rows.
*/
function responsive_sac_preprocess_views_view_row_rss(&$vars) {
$view = &$vars['view'];
$options = &$vars['options'];
$item = &$vars['row'];

// Use the [id] of the returned results to determine the nid in [results]
$result = &$vars['view']->result;
$id = &$vars['id'];
$node = node_load( $result[$id-1]->nid );

$vars['title'] = check_plain($item->title);
$vars['link'] = check_url($item->link);
$vars['description'] = check_plain($item->description);
//$vars['description'] = check_plain($node->teaser);
$vars['node'] = $node;
$vars['item_elements'] = empty($item->elements) ? '' : format_xml_elements($item->elements);
}

// allow responsive_sac theme to use custom input forms
function responsive_sac_theme() {
return array(
    'advisory_node_form' => array(
      'arguments' => array(
          'form' => NULL,
      ),
      'template' => 'templates/advisory-node-form', // set the path here if not in root theme directory
      'render element' => 'form',
    ),

    'observation_node_form' => array(
      'arguments' => array(
          'form' => NULL,
      ),
      'template' => 'templates/observation-node-form', // set the path here if not in root theme directory
      'render element' => 'form',
    ),

    'snowpack_summary_node_form' => array(
      'arguments' => array(
          'form' => NULL,
      ),
      'template' => 'templates/snowpack-summary-node-form', // set the path here if not in root theme directory
      'render element' => 'form',
    ),

  );
}

/**
 * Implements hook_preprocess_field() -- adding classes to fields for styling
 */

function responsive_sac_preprocess_field(&$vars) {
  /* Set shortcut variables. Hooray for less typing! */
  $name = $vars['element']['#field_name'];
  $bundle = $vars['element']['#bundle'];
  $mode = $vars['element']['#view_mode'];
  $classes = &$vars['classes_array'];
  $title_classes = &$vars['title_attributes_array']['class'];
  $content_classes = &$vars['content_attributes_array']['class'];
  $item_classes = array();

  /* Global field classes */
  $classes[] = 'field-wrapper';
  $title_classes[] = 'field-label';
  $content_classes[] = 'field-items';
  $item_classes[] = 'field-item';

  /* Uncomment the lines below to see variables you can use to target a field */
  // print '<strong>Name:</strong> ' . $name . '<br/>';
  // print '<strong>Bundle:</strong> ' . $bundle  . '<br/>';
  // print '<strong>Mode:</strong> ' . $mode .'<br/>';

  /* Add specific classes to targeted fields */
  switch ($mode) {
    /* All teasers */
    case 'teaser':
      switch ($name) {
        /* Teaser read more links */
        case 'node_link':
          $item_classes[] = 'more-link';
          break;
        /* Teaser descriptions */
        case 'body':
        case 'field_description':
          $item_classes[] = 'description';
          break;
      }
      break;
  }

  switch ($name) {
    case 'field_ob_avy_type':
      $classes[] = 'inline';
      // $title_classes[] = 'inline';
      //$content_classes[] = 'authors';
      //$item_classes[] = 'author';
      break;
  }

    switch ($name) {
    case 'field_ob_trigger':
      $classes[] = 'inline';
      break;
  }

      switch ($name) {
    case 'field_slope':
      $classes[] = 'inline';
      break;
  }

      switch ($name) {
    case 'field_ob_aspect':
      $classes[] = 'inline';
      break;
  }

      switch ($name) {
    case 'field_elevation':
      $classes[] = 'inline';
      break;
  }

      switch ($name) {
    case 'field_ob_terrain':
      $classes[] = 'inline';
      break;
  }

        switch ($name) {
    case 'field_ob_weak_layer':
      $classes[] = 'inline';
      break;
  }

        switch ($name) {
    case 'field_ob_bed_surface':
      $classes[] = 'inline';
      break;
  }

        switch ($name) {
    case 'field_crown_height':
      $classes[] = 'inline';
      break;
  }

        switch ($name) {
    case 'field_avalanche_width':
      $classes[] = 'inline';
      break;
  }

        switch ($name) {
    case 'field_avalanche_length':
      $classes[] = 'inline';
      break;
  }

        switch ($name) {
    case 'field_number_of_similar_avalanch':
      $classes[] = 'inline';
      break;
  }

        switch ($name) {
    case 'field_number_of_people_caught':
      $classes[] = 'inline';
      break;
  }

        switch ($name) {
    case 'field_number_of_partial_burials':
      $classes[] = 'inline';
      break;
  }

        switch ($name) {
    case 'field_number_of_full_burials':
      $classes[] = 'inline';
      break;
  }

          switch ($name) {
    case 'field_ob_blowing_snow':
      $classes[] = 'inline';
      break;
  }

          switch ($name) {
    case 'field_ob_wind_speed':
      $classes[] = 'inline';
      break;
  }

          switch ($name) {
    case 'field_ob_wind_dir':
      $classes[] = 'inline';
      break;
  }


          switch ($name) {
    case 'field_ob_air_temp':
      $classes[] = 'inline';
      break;
  }


          switch ($name) {
    case 'field_ob_air_temp_trend':
      $classes[] = 'inline';
      break;
  }


          switch ($name) {
    case 'field_cloud_cover':
      $classes[] = 'inline';
      break;
  }


          switch ($name) {
    case 'field_ob_precip':
      $classes[] = 'inline';
      break;
  }


          switch ($name) {
    case 'field_ob_precip_rate':
      $classes[] = 'inline';
      break;
  }



  // Apply odd or even classes along with our custom classes to each item */
  foreach ($vars['items'] as $delta => $item) {
    $item_classes[] = $delta % 2 ? 'odd' : 'even';
    $vars['item_attributes_array'][$delta]['class'] = $item_classes;
  }
}

/**
 * Overrides theme_field()
 * Remove the hard coded classes so we can add them in preprocess functions.
 */

function responsive_sac_field($variables) {
  $output = '';

  // Render the label, if it's not hidden.
  if (!$variables['label_hidden']) {
    $output .= '<div ' . backdrop_attributes((array) $variables['title_attributes']) . '>' . $variables['label'] . ':&nbsp;</div>';
  }

  // Render the items.
  $output .= '<div ' . backdrop_attributes((array) $variables['content_attributes']) . '>';
  foreach ($variables['items'] as $delta => $item) {
    $output .= '<div ' . backdrop_attributes((array) $variables['item_attributes'][$delta]) . '>' . backdrop_render($item) . '</div>';
  }
  $output .= '</div>';

  // Render the top-level DIV.
  $classes = is_array($variables['classes']) ? implode(' ', $variables['classes']) : $variables['classes'];
  $output = '<div class="' . $classes . '"' . backdrop_attributes((array) $variables['attributes']) . '>' . $output . '</div>';

  return $output;
}

/**
* Override node preview to remove trimmed teaser version. Eliminate node preview
*/
function responsive_sac_node_preview($variables) {
  $node = $variables['node'];

  $output = '<div class="preview">';

  $preview_trimmed_version = FALSE;

  $elements = node_view(clone $node, 'teaser');
  $trimmed = backdrop_render($elements);
  $elements = node_view($node, 'full');
  $full = backdrop_render($elements);

  // Do we need to preview trimmed version of post as well as full version?
  if ($trimmed != $full) {
   // drupal_set_message(t('The trimmed version of your post shows what your post looks like when promoted to the main page or when exported for syndication.<span class="no-js"> You can insert the delimiter "&lt;!--break--&gt;" (without the quotes) to fine-tune where your post gets split.</span>'));
    //$output .= '<h3>' . t('Preview trimmed version') . '</h3>';
    //$output .= $trimmed;
    //$output .= '<h3>' . t('Preview full version') . '</h3>';
    $output .= $full;
  }
  else {
    $output .= $full;
  }
  $output .= "</div>\n";

  return $output;
}

// In Backdrop, blocks are managed by the Layout module and are not available
// via block_get_blocks_by_region() in theme preprocess. Region content is
// rendered by the layout template, not injected into node templates.
// This function is intentionally left as a no-op to avoid fatal errors.
function theme_preprocess_node(&$variables) {
  $variables['region'] = array();
}
