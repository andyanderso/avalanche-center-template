<?php
/**
 * @file
 * Preprocess and theme functions for the Avalanche Center Modern theme.
 */

/**
 * North American Public Avalanche Danger Scale colors (level 0-5).
 */
function avalanche_modern_danger_colors() {
  return array(
    0 => '#939598',
    1 => '#50B848',
    2 => '#FFF200',
    3 => '#F7941E',
    4 => '#ED1C24',
    5 => '#231F20',
  );
}

/**
 * Danger level labels (0-5).
 */
function avalanche_modern_danger_labels() {
  return array(0 => t('No Rating'), 1 => t('Low'), 2 => t('Moderate'), 3 => t('Considerable'), 4 => t('High'), 5 => t('Extreme'));
}

/**
 * Maps a problem-type machine value to its official avalanche.org icon file.
 */
function avalanche_modern_problem_icon($type) {
  $map = array(
    1 => 'StormSlab',
    2 => 'DeepPersistentSlab',
    3 => 'WindSlab',
    4 => 'WetSlab',
    5 => 'PersistentSlab',
    6 => 'WetLoose',
    7 => 'DryLoose',
    9 => 'Cornice',
    10 => 'Glide',
  );
  if (!isset($map[$type])) {
    return '';
  }
  return base_path() . backdrop_get_path('theme', 'avalanche_modern') . '/img/problems/' . $map[$type] . '.png';
}

/**
 * Returns the URL to an official NAC danger icon (level 0-5).
 */
function avalanche_modern_danger_icon($level) {
  $level = (int) $level;
  if ($level < 0 || $level > 5) { $level = 0; }
  return base_path() . backdrop_get_path('theme', 'avalanche_modern') . '/img/danger/' . $level . '.png';
}

/**
 * Builds the avalanche.org danger-by-elevation pyramid (exact NAC SVG paths).
 *
 * @param array $levels
 *   Keyed 'upper', 'mid', 'lower' danger levels (0-5).
 */
function avalanche_modern_danger_pyramid($levels) {
  $colors = avalanche_modern_danger_colors();
  // NAC pyramid: 3 separated slices. lower (base), middle, upper (apex).
  $paths = array(
    'lower' => 'M40.632,203.317l169.248,0.079l40.166,96.604l-249.519,-0.097l40.105,-96.586Z',
    'mid'   => 'M207.532,197.909l-164.605,0.049l40.134,-96.303l84.407,-0.046l40.064,96.3Z',
    'upper' => 'M165.209,96.238l-80.038,-0.012l40.041,-96.226l39.997,96.238Z',
  );
  $svg = '<svg width="100%" height="100%" class="am-dangerGraphic-svg" viewBox="0 0 250 300" xmlns="http://www.w3.org/2000/svg">';
  foreach (array('lower', 'mid', 'upper') as $b) {
    $lvl = isset($levels[$b]) ? (int) $levels[$b] : 0;
    $fill = $lvl > 0 ? $colors[$lvl] : '#939598';
    $svg .= '<path d="' . $paths[$b] . '" style="fill:' . $fill . ';" />';
  }
  $svg .= '</svg>';
  return $svg;
}

/**
 * Builds the avalanche.org aspect/elevation rose (exact NAC octagon SVG paths).
 *
 * @param array $rose
 *   24 values; index 0-7 above (N,NE,E,SE,S,SW,W,NW), 8-15 near, 16-23 below.
 *   Any value > 0 marks the sector as affected.
 */
function avalanche_modern_rose_svg($rose) {
  $paths = array(
    'north upper' => 'M529.716,527l68.371,-166.7l-138.1,0l69.729,166.7Z',
    'north middle' => 'M666.581,193.63l-277.081,0l69.27,166.67l138.541,0l69.27,-166.67Z',
    'north lower' => 'M734.1,26.997l-414.2,0l69.943,166.67l275.865,0l68.392,-166.67Z',
    'northeast upper' => 'M529.716,527l166.22,-69.529l-97.651,-97.652l-68.569,167.181Z',
    'northeast middle' => 'M862.222,388.05l-195.925,-195.926l-68.873,166.835l97.963,97.963l166.835,-68.872Z',
    'northeast lower' => 'M1027.79,317.966l-292.884,-292.884l-68.396,167.311l195.066,195.066l166.214,-69.493Z',
    'east upper' => 'M527.716,528.339l166.7,68.371l0,-138.1l-166.7,69.729Z',
    'east middle' => 'M861.086,665.204l0,-277.081l-166.67,69.27l0,138.541l166.67,69.27Z',
    'east lower' => 'M1027.72,732.723l0,-414.2l-166.67,69.943l0,275.865l166.67,68.392Z',
    'southeast upper' => 'M527.716,528.339l69.529,166.22l97.652,-97.651l-167.181,-68.569Z',
    'southeast middle' => 'M666.666,860.845l195.926,-195.925l-166.835,-68.872l-97.963,97.962l68.872,166.835Z',
    'southeast lower' => 'M736.75,1026.42l292.884,-292.884l-167.311,-68.396l-195.066,195.066l69.493,166.214Z',
    'south upper' => 'M527.918,528.416l-68.371,166.7l138.1,0l-69.729,-166.7Z',
    'south middle' => 'M391.053,861.786l277.081,0l-69.27,-166.67l-138.541,0l-69.27,166.67Z',
    'south lower' => 'M323.534,1028.42l414.2,0l-69.943,-166.67l-275.865,0l-68.392,166.67Z',
    'southwest upper' => 'M527.918,528.416l-166.22,69.529l97.651,97.652l68.569,-167.181Z',
    'southwest middle' => 'M195.412,667.366l195.926,195.926l68.872,-166.835l-97.963,-97.963l-166.835,68.872Z',
    'southwest lower' => 'M29.841,737.451l292.884,292.883l68.396,-167.31l-195.066,-195.067l-166.214,69.494Z',
    'west upper' => 'M528.918,527.077l-166.7,-68.371l0,138.1l166.7,-69.729Z',
    'west middle' => 'M195.548,390.212l0,277.081l166.67,-69.27l0,-138.541l-166.67,-69.27Z',
    'west lower' => 'M28.915,322.693l0,414.2l166.67,-69.943l0,-275.865l-166.67,-68.392Z',
    'northwest upper' => 'M529.918,527.077l-69.529,-166.22l-97.652,97.651l167.181,68.569Z',
    'northwest middle' => 'M390.968,194.571l-195.926,195.926l166.835,68.872l97.963,-97.963l-68.872,-166.835Z',
    'northwest lower' => 'M318.884,27l-292.884,292.884l167.311,68.396l195.066,-195.066l-69.493,-166.214Z',
  );
  // Map rose index -> "aspect band" data-id.
  $aspects = array(0 => 'north', 1 => 'northeast', 2 => 'east', 3 => 'southeast', 4 => 'south', 5 => 'southwest', 6 => 'west', 7 => 'northwest');
  $band_names = array(0 => 'upper', 1 => 'middle', 2 => 'lower');

  $svg = '<svg class="am-rose-svg" width="150px" height="150px" viewBox="0 0 1050 1050" xmlns="http://www.w3.org/2000/svg" style="fill-rule:evenodd;clip-rule:evenodd;stroke-linecap:round;stroke-linejoin:round;stroke-miterlimit:1.5;">';
  for ($c = 0; $c < 24; $c++) {
    $aspect = $aspects[$c % 8];
    $band = $band_names[(int) floor($c / 8)];
    $key = $aspect . ' ' . $band;
    if (!isset($paths[$key])) { continue; }
    $affected = isset($rose[$c]) && (int) $rose[$c] > 0;
    $fill = $affected ? 'rgb(200,202,206)' : 'rgb(255,255,255)';
    $svg .= '<path data-id="' . $key . '" d="' . $paths[$key] . '" style="stroke:rgb(81,85,88);stroke-width:10px;fill:' . $fill . ';" />';
  }
  $svg .= '</svg>';
  return $svg;
}

/**
 * Implements template_preprocess_page().
 *
 * Load modern web fonts (added during page build so the link is emitted) and
 * expose the search form for the layout template.
 */
function avalanche_modern_preprocess_page(&$variables) {
  backdrop_add_css('https://fonts.googleapis.com/css2?family=Barlow+Semi+Condensed:wght@500;600;700&family=Inter:wght@400;500;600;700&family=Lato:wght@400;700;900&display=swap', array(
    'group' => CSS_THEME,
    'every_page' => TRUE,
    'weight' => -100,
    'basename' => 'avalanche-modern-google-fonts.css',
  ));

  $form = backdrop_get_form('search_form');
  $variables['search_box'] = backdrop_render($form);
}

/**
 * Implements template_preprocess_layout().
 *
 * Inject social/contact URLs and the header background for the layout template.
 */
function avalanche_modern_preprocess_layout(&$variables) {
  $variables['email'] = theme_get_setting('email_url');
  $variables['facebook'] = theme_get_setting('facebook_url');
  $variables['twitter'] = theme_get_setting('twitter_url');
  $variables['instagram'] = theme_get_setting('instagram_url');
  $variables['youtube'] = theme_get_setting('youtube_url');
}

/**
 * Implements theme_field__taxonomy_term_reference().
 *
 * Null-safe override of the responsive_bartik base-theme function, which calls
 * in_array() on a possibly-NULL classes_array and fatals under PHP 8.
 */
function avalanche_modern_field__taxonomy_term_reference($variables) {
  $output = '';

  if (empty($variables['label_hidden'])) {
    $output .= '<h3 class="field-label">' . $variables['label'] . ': </h3>';
  }

  $output .= ($variables['element']['#label_display'] == 'inline') ? '<ul class="links inline">' : '<ul class="links">';
  foreach ($variables['items'] as $delta => $item) {
    $item_attr = isset($variables['item_attributes'][$delta]) ? $variables['item_attributes'][$delta] : '';
    // Backdrop passes attributes as arrays; coerce to an attribute string.
    if (is_array($item_attr)) {
      $item_attr = backdrop_attributes($item_attr);
    }
    $output .= '<li class="taxonomy-term-reference-' . $delta . '"' . $item_attr . '>' . backdrop_render($item) . '</li>';
  }
  $output .= '</ul>';

  // Backdrop passes $classes and $attributes as arrays (see
  // template_preprocess_field()), so build the wrapper the same way the core
  // field.tpl.php does rather than concatenating the arrays as strings.
  $classes = isset($variables['classes']) && is_array($variables['classes']) ? $variables['classes'] : array();
  if (!in_array('clearfix', $classes)) {
    $classes[] = 'clearfix';
  }
  $attributes = isset($variables['attributes']) ? $variables['attributes'] : '';
  if (is_array($attributes)) {
    $attributes = backdrop_attributes($attributes);
  }
  $output = '<div class="' . implode(' ', $classes) . '"' . $attributes . '>' . $output . '</div>';

  return $output;
}

/**
 * Implements template_preprocess_node().
 *
 * Prepares structured advisory data for the SAC-style forecast template.
 */
function avalanche_modern_preprocess_node(&$variables) {
  if ($variables['view_mode'] == 'full') {
    $variables['classes_array'][] = 'node-full';
    if ($variables['node']->type == 'advisory') {
      require_once dirname(__FILE__) . '/inc/advisory.inc';
      $variables['advisory'] = avalanche_modern_get_advisory_data($variables['node']);
    }
  }
}

/**
 * Implements hook_theme().
 *
 * Routes the advisory add/edit form through a custom template so authoring a
 * forecast mirrors the layout of the published advisory (node--advisory).
 */
function avalanche_modern_theme($existing, $type, $theme, $path) {
  return array(
    'advisory_node_form' => array(
      'render element' => 'form',
      'template' => 'templates/advisory-node-form',
    ),
  );
}
