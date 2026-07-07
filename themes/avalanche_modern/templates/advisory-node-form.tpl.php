<?php
/**
 * @file
 * Modern advisory (avalanche forecast) add/edit form.
 *
 * Laid out to mirror the published advisory (node--advisory.tpl.php): the same
 * sections, headings and column structure (The Bottom Line, Avalanche Danger by
 * elevation band, Avalanche Problems with Problem Type / Aspect-Elevation /
 * Likelihood / Size columns, Forecast Discussion, Recent Avalanche Activity,
 * Mountain Weather) so creating a forecast feels like building the finished
 * product.
 *
 * Standard widgets are preserved; we only regroup and restyle them. Anything we
 * don't place explicitly (legacy detailed-weather fields, vertical tabs, author
 * / publishing options and the Save / Preview buttons) is rendered untouched at
 * the end via drupal_render_children().
 */

/**
 * Renders a single form element, optionally stripping the widget's own label
 * (so our section headings act as the label). Returns '' for absent fields.
 */
$rf = function ($key, $strip_title = FALSE) use (&$form) {
  if (empty($form[$key])) {
    return '';
  }
  if ($strip_title) {
    if (isset($form[$key]['und']['#title'])) {
      unset($form[$key]['und']['#title']);
    }
    if (isset($form[$key]['und'][0]['#title'])) {
      unset($form[$key]['und'][0]['#title']);
    }
    if (isset($form[$key]['und'][0]['value']['#title'])) {
      unset($form[$key]['und'][0]['value']['#title']);
    }
  }
  return render($form[$key]);
};

// Elevation bands top-to-bottom, mapped to their danger-rating fields and the
// pyramid band keys, matching the display (upper = _3, mid = _2, lower = _1).
$band_labels = array(
  'upper' => theme_get_setting('upper_elevation_band') ? theme_get_setting('upper_elevation_band') : t('Above Treeline'),
  'mid'   => theme_get_setting('middle_elevation_band') ? theme_get_setting('middle_elevation_band') : t('Near Treeline'),
  'lower' => theme_get_setting('lower_elevation_band') ? theme_get_setting('lower_elevation_band') : t('Below Treeline'),
);
$danger_bands = array(
  array('band' => 'upper', 'field' => 'field_danger_rating_3', 'label' => $band_labels['upper']),
  array('band' => 'mid',   'field' => 'field_danger_rating_2', 'label' => $band_labels['mid']),
  array('band' => 'lower', 'field' => 'field_danger_rating_1', 'label' => $band_labels['lower']),
);

// Current danger values (from the node being edited) so the mountain graphic
// reflects the saved forecast; new advisories start neutral/grey.
$adv_node = isset($form['#node']) ? $form['#node'] : NULL;
$pyr_levels = array(
  'upper' => ($adv_node && isset($adv_node->field_danger_rating_3['und'][0]['value'])) ? (int) $adv_node->field_danger_rating_3['und'][0]['value'] : 0,
  'mid'   => ($adv_node && isset($adv_node->field_danger_rating_2['und'][0]['value'])) ? (int) $adv_node->field_danger_rating_2['und'][0]['value'] : 0,
  'lower' => ($adv_node && isset($adv_node->field_danger_rating_1['und'][0]['value'])) ? (int) $adv_node->field_danger_rating_1['und'][0]['value'] : 0,
);

// Per-problem column headings, mirroring the published advisory.
$problem_cols = array(
  t('Problem Type')     => 'field_type_%d',
  t('Aspect/Elevation') => 'field_rose_%d',
  t('Likelihood')       => 'field_likelihood_%d',
  t('Size')             => 'field_size_%d',
);

// Detailed-weather layout, mirroring the published advisory. Labels come from
// this theme's own settings (Appearance → theme settings).
$wx_current_desc = theme_get_setting('current_wx_conditions_desc');
$wx_low = theme_get_setting('wx_elevation_low');
$wx_high = theme_get_setting('wx_elevation_high');
// Default open/collapsed state of the detailed-weather group (theme setting).
$wx_open = (bool) theme_get_setting('advisory_weather_expanded');
$wx_current_rows = array(
  array(t('0600 temperature'), 'field_temp8700', t('deg. F.')),
  array(t('Max. temperature in the last 24 hours'), 'field_hr24maxtemp', t('deg. F.')),
  array(t('Average wind direction during the last 24 hours'), 'field_hr24winddir', ''),
  array(t('Average wind speed during the last 24 hours'), 'field_hr24windspeed', t('mph')),
  array(t('Maximum wind gust in the last 24 hours'), 'field_hr24maxgust', t('mph')),
  array(t('New snowfall in the last 24 hours'), 'field_hr24snowfall', t('inches')),
  array(t('Total snow depth'), 'field_totalsnowdepth', t('inches')),
);
$wx_cols = array('today' => t('Today'), 'tonight' => t('Tonight'), 'tomorrow' => t('Tomorrow'));
$wx_metrics = array(
  array(t('Weather'), 'weather', ''),
  array(t('Temperatures'), 'temp', t('deg. F.')),
  array(t('Wind Direction'), 'winddirection', ''),
  array(t('Wind Speed'), 'windspeed', ''),
  array(t('Expected snowfall'), 'snow', t('in.')),
);
$wx_bands = array(array($wx_low, '7to8'), array($wx_high, '8to9'));
?>
<div class="am-forecast-form nac-html-body">

  <div class="nac-form-card nac-form-meta">
    <h2 class="nac-h2"><?php print t('Region &amp; Validity'); ?></h2>
    <div class="nac-form-grid nac-form-grid-3">
      <div class="nac-form-col"><?php print render($form['title']); ?></div>
      <div class="nac-form-col"><?php print $rf('field_forecast_region'); ?></div>
      <div class="nac-form-col"><?php print $rf('field_duration'); ?></div>
    </div>
    <?php if (!empty($form['field_bulletin'])): ?>
      <div class="nac-form-col nac-form-bulletin"><?php print $rf('field_bulletin'); ?></div>
    <?php endif; ?>
  </div>

  <?php if (!empty($form['field_bottom_line']) || !empty($form['field_overalldanger'])): ?>
    <div class="nac-form-card nac-form-bottom-line">
      <h2 class="nac-h2"><?php print t('The Bottom Line'); ?></h2>
      <div class="nac-form-col nac-form-overall"><?php print $rf('field_overalldanger'); ?></div>
      <div class="nac-form-col"><?php print $rf('field_bottom_line'); ?></div>
    </div>
  <?php endif; ?>

  <div class="nac-form-card nac-form-danger">
    <h2 class="nac-h2"><?php print t('Avalanche Danger'); ?></h2>
    <div class="nac-form-danger-layout">
      <div class="nac-form-pyramid" aria-hidden="true"><?php print avalanche_modern_danger_pyramid($pyr_levels); ?></div>
      <div class="nac-form-band-rows">
        <?php foreach ($danger_bands as $b): ?>
          <?php if (!empty($form[$b['field']])): ?>
            <div class="nac-form-band-row nac-form-band-<?php print $b['band']; ?>">
              <h5 class="nac-h5"><?php print check_plain($b['label']); ?></h5>
              <?php print $rf($b['field'], TRUE); ?>
            </div>
          <?php endif; ?>
        <?php endforeach; ?>
      </div>
    </div>
    <script>
    (function () {
      var colors = <?php print json_encode(avalanche_modern_danger_colors()); ?>;
      var svg = document.querySelector('.am-forecast-form .nac-form-pyramid svg');
      if (!svg) { return; }
      var paths = svg.querySelectorAll('path'); /* order: lower, mid, upper */
      var map = [
        { name: 'field_danger_rating_1', idx: 0 },
        { name: 'field_danger_rating_2', idx: 1 },
        { name: 'field_danger_rating_3', idx: 2 }
      ];
      map.forEach(function (m) {
        var el = document.querySelector('select[name="' + m.name + '[und]"]');
        if (!el || !paths[m.idx]) { return; }
        var paint = function () {
          var v = parseInt(el.value, 10) || 0;
          paths[m.idx].style.fill = (v > 0 && colors[v]) ? colors[v] : '#939598';
        };
        el.addEventListener('change', paint);
        paint();
      });
    })();
    </script>
  </div>

  <div class="nac-form-card nac-form-problems">
    <h2 class="nac-h2"><?php print t('Avalanche Problems'); ?></h2>
    <p class="nac-form-hint"><?php print t('Fill in only the problems that apply. Leave "Problem Type" empty to skip a problem.'); ?></p>
    <?php for ($n = 1; $n <= 3; $n++): ?>
      <?php if (!empty($form['field_type_' . $n])): ?>
        <div class="nac-form-problem">
          <h3 class="nac-h3"><?php print t('Problem #@number', array('@number' => $n)); ?></h3>
          <div class="nac-form-grid nac-form-grid-4">
            <?php foreach ($problem_cols as $heading => $pattern): ?>
              <?php $fname = sprintf($pattern, $n); ?>
              <?php if (!empty($form[$fname])): ?>
                <div class="nac-form-col">
                  <h5 class="nac-h5"><?php print $heading; ?></h5>
                  <?php print $rf($fname, TRUE); ?>
                </div>
              <?php endif; ?>
            <?php endforeach; ?>
          </div>
          <div class="nac-form-col nac-form-problem-desc">
            <?php print $rf('field_description_' . $n); ?>
          </div>
        </div>
      <?php endif; ?>
    <?php endfor; ?>
  </div>

  <?php if (!empty($form['field_text_discussion'])): ?>
    <div class="nac-form-card">
      <h2 class="nac-h2"><?php print t('Forecast Discussion'); ?></h2>
      <?php print $rf('field_text_discussion', TRUE); ?>
    </div>
  <?php endif; ?>

  <?php if (!empty($form['field_recent_activity'])): ?>
    <div class="nac-form-card">
      <h2 class="nac-h2"><?php print t('Recent Avalanche Activity'); ?></h2>
      <?php print $rf('field_recent_activity', TRUE); ?>
    </div>
  <?php endif; ?>

  <?php
    $has_wx_fields = !empty($form['field_temp8700']) || !empty($form['field_today7to8weather']) || !empty($form['field_today8to9weather']);
  ?>
  <?php if (!empty($form['field_mountain_weather']) || $has_wx_fields): ?>
    <div class="nac-form-card nac-form-weather">
      <h2 class="nac-h2"><?php print t('Mountain Weather'); ?></h2>
      <?php if (!empty($form['field_mountain_weather'])): ?>
        <?php print $rf('field_mountain_weather', TRUE); ?>
      <?php endif; ?>

      <?php
        // Current conditions.
        $cur_out = '';
        foreach ($wx_current_rows as $c) {
          if (!empty($form[$c[1]])) {
            $unit = $c[2] !== '' ? ' <span class="nac-wx-unit">' . $c[2] . '</span>' : '';
            $cur_out .= '<tr><th scope="row">' . $c[0] . '</th><td>' . $rf($c[1], TRUE) . $unit . '</td></tr>';
          }
        }

        // Two-day forecast tables (one per elevation band).
        $fc_out = '';
        foreach ($wx_bands as $band) {
          list($band_label, $suffix) = $band;
          $band_has = FALSE;
          foreach ($wx_cols as $col => $cl) {
            foreach ($wx_metrics as $m) {
              if (!empty($form['field_' . $col . $suffix . $m[1]])) { $band_has = TRUE; break 2; }
            }
          }
          if (!$band_has) { continue; }
          $thead = '<thead><tr><th></th>';
          foreach ($wx_cols as $col => $cl) { $thead .= '<th>' . $cl . '</th>'; }
          $thead .= '</tr></thead>';
          $tbody = '';
          foreach ($wx_metrics as $m) {
            $tbody .= '<tr><th scope="row">' . $m[0] . '</th>';
            foreach ($wx_cols as $col => $cl) {
              $fname = 'field_' . $col . $suffix . $m[1];
              $cell = !empty($form[$fname]) ? $rf($fname, TRUE) : '';
              $unit = ($m[2] !== '' && $cell !== '') ? ' <span class="nac-wx-unit">' . $m[2] . '</span>' : '';
              $tbody .= '<td>' . $cell . $unit . '</td>';
            }
            $tbody .= '</tr>';
          }
          if (!empty($band_label)) { $fc_out .= '<div class="nac-wx-band-label">' . check_plain($band_label) . '</div>'; }
          $fc_out .= '<table class="nac-wx-table nac-wx-forecast">' . $thead . '<tbody>' . $tbody . '</tbody></table>';
        }
      ?>
      <?php if ($cur_out !== '' || $fc_out !== ''): ?>
        <details class="nac-form-wx-details"<?php print $wx_open ? ' open' : ''; ?>>
          <summary><?php print t('Detailed weather data'); ?></summary>
          <?php if ($cur_out !== ''): ?>
            <div class="nac-wx-block">
              <?php if (!empty($wx_current_desc)): ?><h4 class="nac-h4 nac-wx-subtitle"><?php print check_plain($wx_current_desc); ?></h4><?php endif; ?>
              <table class="nac-wx-table nac-wx-current"><tbody><?php print $cur_out; ?></tbody></table>
            </div>
          <?php endif; ?>
          <?php if ($fc_out !== ''): ?>
            <div class="nac-wx-block">
              <h4 class="nac-h4 nac-wx-subtitle"><?php print t('Two-Day Mountain Weather Forecast'); ?></h4>
              <?php print $fc_out; ?>
            </div>
          <?php endif; ?>
        </details>
      <?php endif; ?>
    </div>
  <?php endif; ?>

  <?php if (!empty($form['field_disclaimer'])): ?>
    <div class="nac-form-card">
      <h2 class="nac-h2"><?php print t('Disclaimer'); ?></h2>
      <?php print $rf('field_disclaimer', TRUE); ?>
    </div>
  <?php endif; ?>

  <?php
    // Any remaining advisory fields that don't belong to a dedicated section.
    $legacy_fields = array('field_overall_danger_rose');
    $legacy_out = '';
    foreach ($legacy_fields as $lf) {
      $legacy_out .= $rf($lf);
    }
  ?>
  <?php if (trim($legacy_out) !== ''): ?>
    <details class="nac-form-card nac-form-advanced">
      <summary><?php print t('Other fields (optional)'); ?></summary>
      <div class="nac-form-advanced-body"><?php print $legacy_out; ?></div>
    </details>
  <?php endif; ?>

  <?php if (!empty($form['field_simplenews_term'])): ?>
    <div class="nac-form-card nac-form-newsletter-card">
      <h2 class="nac-h2"><?php print t('Newsletter category'); ?></h2>
      <?php print $rf('field_simplenews_term', TRUE); ?>
    </div>
  <?php endif; ?>

  <div class="nac-form-tail">
    <?php print drupal_render_children($form); ?>
  </div>

</div>
