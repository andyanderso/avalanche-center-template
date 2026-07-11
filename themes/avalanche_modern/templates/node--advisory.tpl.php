<?php
/**
 * @file
 * Avalanche forecast (advisory) - faithful reproduction of the avalanche.org /
 * Sierra Avalanche Center NAC forecast widget markup and styling.
 */

$node = $variables['node'];
$a = isset($variables['advisory']) ? $variables['advisory'] : array();
$labels = avalanche_modern_danger_labels();

$created = isset($a['created']) ? $a['created'] : $node->created;
$expires = isset($a['expires']) ? $a['expires'] : $node->created;
$overall = isset($a['danger_rating']['overall']) ? (int) $a['danger_rating']['overall'] : 0;

$likelihood_labels = array(1 => t('Unlikely'), 2 => t('Possible'), 3 => t('Likely'), 4 => t('Very Likely'), 5 => t('Certain'));

$bands = array(
  'upper' => isset($a['danger_rating']['upper']) ? (int) $a['danger_rating']['upper'] : 0,
  'mid'   => isset($a['danger_rating']['mid']) ? (int) $a['danger_rating']['mid'] : 0,
  'lower' => isset($a['danger_rating']['lower']) ? (int) $a['danger_rating']['lower'] : 0,
);
$band_elev = array(
  'upper' => isset($a['danger_rating']['upper_elevation']) ? $a['danger_rating']['upper_elevation'] : t('Above Treeline'),
  'mid'   => isset($a['danger_rating']['mid_elevation']) ? $a['danger_rating']['mid_elevation'] : t('Near Treeline'),
  'lower' => isset($a['danger_rating']['lower_elevation']) ? $a['danger_rating']['lower_elevation'] : t('Below Treeline'),
);

// D-scale (1-5) to 4-step size category (1=Small..4=Historic), then to slider %.
$size_cat = function ($d) { $d = (int) $d; return $d >= 4 ? 4 : $d; };
$size_pos = function ($d) use ($size_cat) { return (($size_cat($d) - 1) / 3) * 100; };

$danger_scale = array(
  1 => array(t('Low'), 'rgb(80, 184, 72)'),
  2 => array(t('Moderate'), 'rgb(255, 242, 0)'),
  3 => array(t('Considerable'), 'rgb(247, 148, 30)'),
  4 => array(t('High'), 'rgb(237, 28, 36)'),
  5 => array(t('Extreme'), 'rgb(35, 31, 32)'),
);

// PHP 8 safety: $classes/$attributes can arrive as arrays or pre-rendered
// strings depending on caller; normalize before use in the <article> tag.
$article_classes = is_array($classes) ? implode(' ', $classes) : $classes;
$article_attributes = is_array($attributes) ? backdrop_attributes($attributes) : $attributes;
?>
<article id="node-<?php print $node->nid; ?>" class="am-forecast nac-html-body <?php print $article_classes; ?>"<?php print $article_attributes; ?>>
  <?php hide($content['comments']); hide($content['links']); ?>
  <div id="nac-app">

    <?php if (!empty($a['expired'])): ?>
      <div class="nac-expired-banner" role="alert">
        <svg class="nac-icon" viewBox="0 0 24 24" width="1em" height="1em" aria-hidden="true"><path fill="currentColor" d="M12 2 1 21h22L12 2m0 3.99L19.53 19H4.47L12 5.99M11 10v4h2v-4h-2m0 6v2h2v-2h-2"></path></svg>
        <div class="nac-expired-banner-text">
          <strong><?php print t('This forecast has expired.'); ?></strong>
          <?php print t('It was issued @date and is no longer current.', array('@date' => format_date($created, 'custom', 'l, F j, Y - g:iA'))); ?>
          <?php if (!empty($a['current_forecast_url'])): ?>
            <a href="<?php print check_url($a['current_forecast_url']); ?>"><?php print t('Get more information &raquo;'); ?></a>
          <?php endif; ?>
        </div>
      </div>
    <?php endif; ?>

    <?php
      $region_display = $a['region_name'] ? $a['region_name'] : $node->title;
      $region_display = trim(str_replace(array('Avalanche Association Forecast Region', 'Avalanche Center Forecast Region', 'Forecast Region', 'Avalanche Association'), 'Region', $region_display));
      $region_display = trim(preg_replace('/\bRegion(\s+Region)+\b/', 'Region', $region_display));
    ?>
    <div class="nac-forecast-title">
      <div class="nac-region-row">
        <svg class="nac-icon nac-region-pin" viewBox="0 0 24 24" width="1em" height="1em"><path fill="currentColor" d="M12 11.5A2.5 2.5 0 0 1 9.5 9A2.5 2.5 0 0 1 12 6.5A2.5 2.5 0 0 1 14.5 9a2.5 2.5 0 0 1-2.5 2.5M12 2a7 7 0 0 0-7 7c0 5.25 7 13 7 13s7-7.75 7-13a7 7 0 0 0-7-7"></path></svg>
        <h2 class="nac-h2 nac-region-name"><?php print check_plain($region_display); ?></h2>
      </div>
    </div>

    <div class="nac-header">
      <div class="nac-header-meta"><h6 class="nac-h6"><?php print t('Issued'); ?></h6> <?php print format_date($created, 'custom', 'l, F j, Y - g:iA'); ?></div>
      <?php if ($expires > $created): ?>
        <div class="nac-header-meta"><h6 class="nac-h6"><?php print t('Expires'); ?></h6> <?php print format_date($expires, 'custom', 'l, F j, Y - g:iA'); ?></div>
      <?php endif; ?>
      <?php if (!empty($a['forecaster'])): ?>
        <div class="nac-header-meta"><h6 class="nac-h6"><?php print t('Author'); ?></h6> <?php print check_plain($a['forecaster']); ?></div>
      <?php endif; ?>
    </div>

    <?php if (!empty($a['bottom_line'])): ?>
      <div class="nac-card nac-bottom-line">
        <div class="nac-card-body">
          <div class="nac-bottom-line-icon"><img src="<?php print avalanche_modern_danger_icon($overall); ?>" alt="Danger level <?php print $overall; ?>" /></div>
          <h5 class="nac-bottom-line-title"><?php print t('THE BOTTOM LINE'); ?></h5>
          <div class="nac-bottom-line-text nac-tinymce"><?php print check_markup($a['bottom_line'], 'full_html'); ?></div>
        </div>
      </div>
    <?php endif; ?>

    <div class="nac-card nac-content-panel">
      <div class="nac-card-body">

          <div class="nac-danger">
            <h2 class="nac-h2"><?php print t('Avalanche Danger'); ?></h2>
            <div class="nac-row">
              <div class="nac-dangerToday">
                <div class="nac-dangerDate"><?php print format_date($created, 'custom', 'l, F j, Y'); ?></div>
                <div class="nac-dangerGraphic">
                  <?php foreach (array('upper', 'mid', 'lower') as $b): ?>
                    <div class="nac-elevationBlock">
                      <span class="nac-elevationLabel"><?php print check_plain($band_elev[$b]); ?></span>
                      <span class="nac-dangerLabel"><?php print $bands[$b] . ' - ' . $labels[$bands[$b]]; ?></span>
                      <div class="nac-dangerIcon"><img src="<?php print avalanche_modern_danger_icon($bands[$b]); ?>" alt="" /></div>
                    </div>
                  <?php endforeach; ?>
                  <div id="nac-dangerGraphicToday"><?php print avalanche_modern_danger_pyramid($bands); ?></div>
                </div>
              </div>
            </div>

            <div class="nac-dangerScale">
              <div class="nac-dangerScale-row">
                <div class="nac-dangerScale-head"><h4 class="nac-h4"><?php print t('Danger Scale'); ?></h4></div>
                <div class="nac-dangerScale-ratings">
                  <?php foreach ($danger_scale as $n => $info): ?>
                    <div class="nac-dangerScale-rating">
                      <div class="nac-text-center" style="border-top: 10px solid <?php print $info[1]; ?>;">
                        <span><strong><?php print $n; ?></strong> - <?php print $info[0]; ?></span>
                      </div>
                    </div>
                  <?php endforeach; ?>
                </div>
              </div>
              <div class="nac-dangerScale-expand">
                <a class="nac-btn nac-btn-primary" href="https://avalanche.org/avalanche-encyclopedia/danger-scale/" target="_blank" rel="noopener">
                  <svg class="nac-icon" viewBox="0 0 24 24" width="1em" height="1em"><path fill="currentColor" d="M19 13h-6v6h-2v-6H5v-2h6V5h2v6h6z"></path></svg>
                  <?php print t('Danger Scale'); ?>
                </a>
              </div>
            </div>
          </div>

          <?php
          $problems = array();
          foreach (array('problem_1', 'problem_2', 'problem_3') as $pk) {
            if (isset($a[$pk]) && !empty($a[$pk]['type'])) { $problems[] = $a[$pk]; }
          }
          ?>
          <?php if (!empty($problems)): ?>
            <div class="nac-divider">
              <h2 class="nac-h2 nac-d-inline"><?php print t('Avalanche Problems (@count)', array('@count' => count($problems))); ?></h2>
              <?php foreach ($problems as $i => $p): ?>
                <?php
                  $type_label = avalanche_modern_problem_type_label($p['type']);
                  $icon = avalanche_modern_problem_icon($p['type']);
                  $like = (int) $p['likelihood'];
                  $like_pos = $like ? (($like - 1) / 4) * 100 : 0;
                  $pos_min = $size_pos($p['size_min']);
                  $pos_max = $size_pos($p['size_max']);
                ?>
                <div class="nac-problem">
                  <h3 class="nac-h3"><?php print t('Problem #@number: @type', array('@number' => $i + 1, '@type' => $type_label)); ?></h3>
                  <div class="nac-infoGraphics nac-row">

                    <div class="nac-problem-column">
                      <h5 class="nac-h5"><?php print t('Problem Type'); ?></h5>
                      <?php if ($icon): ?><img src="<?php print $icon; ?>" class="nac-problemIcon" alt="<?php print check_plain($type_label); ?>" /><?php endif; ?>
                      <div class="nac-problemText"><?php print check_plain($type_label); ?></div>
                    </div>

                    <div class="nac-problem-column">
                      <h5 class="nac-h5"><?php print t('Aspect/Elevation'); ?></h5>
                      <div class="nac-rose location">
                        <div class="nac-roseContainer">
                          <label class="nac-aspectMarker nac-north">N</label>
                          <label class="nac-aspectMarker nac-east">E</label>
                          <label class="nac-aspectMarker nac-south">S</label>
                          <label class="nac-aspectMarker nac-west">W</label>
                          <label class="nac-aspectMarker nac-northwest">NW</label>
                          <label class="nac-aspectMarker nac-northeast">NE</label>
                          <label class="nac-aspectMarker nac-southeast">SE</label>
                          <label class="nac-aspectMarker nac-southwest">SW</label>
                          <div>
                            <div class="nac-elevationMarker nac-elevationMarkerUpper"></div>
                            <div class="nac-elevationMarker nac-elevationMarkerMiddle"></div>
                            <div class="nac-elevationMarker nac-elevationMarkerLower"></div>
                            <div class="nac-elevationLabel nac-elevationLabelUpper"><?php print t('Above Treeline'); ?></div>
                            <div class="nac-elevationLabel nac-elevationLabelMiddle"><?php print t('Near Treeline'); ?></div>
                            <div class="nac-elevationLabel nac-elevationLabelLower"><?php print t('Below Treeline'); ?></div>
                          </div>
                          <?php print avalanche_modern_rose_svg($p['rose']); ?>
                        </div>
                      </div>
                    </div>

                    <div class="nac-problem-column">
                      <h5 class="nac-h5"><?php print t('Likelihood'); ?></h5>
                      <div class="nac-problemSlider">
                        <div class="nac-rail">
                          <?php foreach (array(0, 25, 50, 75, 100) as $pct): ?>
                            <div style="bottom: <?php print $pct; ?>%;" class="nac-step"></div>
                          <?php endforeach; ?>
                          <?php foreach ($likelihood_labels as $v => $l): ?>
                            <div style="bottom: <?php print (($v - 1) / 4) * 100; ?>%;" class="<?php print ($v == $like) ? 'active ' : ''; ?>nac-label"><?php print $l; ?></div>
                          <?php endforeach; ?>
                          <?php if ($like): ?><div class="nac-process" style="bottom: calc(<?php print $like_pos; ?>% - 4px); top: calc(<?php print 100 - $like_pos; ?>% - 4px);"></div><?php endif; ?>
                        </div>
                      </div>
                    </div>

                    <div class="nac-problem-column">
                      <h5 class="nac-h5"><?php print t('Size'); ?></h5>
                      <div class="nac-problemSlider">
                        <div class="nac-rail">
                          <?php foreach (array(0, 16.6667, 33.3333, 50, 66.6667, 83.3333, 100) as $idx => $pct): ?>
                            <div style="bottom: <?php print $pct; ?>%;" class="nac-step<?php print ($idx % 2 == 1) ? ' nac-d-none' : ''; ?>"></div>
                          <?php endforeach; ?>
                          <?php
                          // [percent, label] pairs - NOT a float-keyed map:
                          // PHP truncates float array keys to int (33.3333 -> 33),
                          // which broke the "active" comparison so a two-value size
                          // range only bolded one label. Keep the percent as a value.
                          $size_rows = array(
                            array(0, t('Small (D1)')),
                            array(33.3333, t('Large (D2)')),
                            array(66.6667, t('Very Large (D3)')),
                            array(100, t('Historic (D4-5)')),
                          );
                          foreach ($size_rows as $size_row):
                            list($pct, $l) = $size_row;
                            $active = ($pos_min !== '' && $pct >= $pos_min - 0.01 && $pct <= $pos_max + 0.01);
                          ?>
                            <div style="bottom: <?php print $pct; ?>%;" class="<?php print $active ? 'active ' : ''; ?>nac-label"><?php print $l; ?></div>
                          <?php endforeach; ?>
                          <?php if ($p['size_min']): ?><div class="nac-process" style="bottom: calc(<?php print $pos_min; ?>% - 4px); top: calc(<?php print 100 - $pos_max; ?>% - 4px);"></div><?php endif; ?>
                        </div>
                      </div>
                    </div>

                  </div>
                  <?php if (!empty($p['description'])): ?>
                    <div class="nac-tinymce"><?php print check_markup($p['description'], 'full_html'); ?></div>
                  <?php endif; ?>
                </div>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>

          <?php if (!empty($a['discussion'])): ?>
            <div class="nac-divider">
              <h2 class="nac-h2"><?php print t('Forecast Discussion'); ?></h2>
              <div class="nac-tinymce"><?php print check_markup($a['discussion'], $a['discussion_format'] ? $a['discussion_format'] : 'full_html'); ?></div>
            </div>
          <?php endif; ?>

          <?php if (!empty($a['recent_activity'])): ?>
            <div class="nac-divider">
              <h2 class="nac-h2"><?php print t('Recent Avalanche Activity'); ?></h2>
              <div class="nac-tinymce"><?php print check_markup($a['recent_activity'], $a['recent_activity_format'] ? $a['recent_activity_format'] : 'full_html'); ?></div>
            </div>
          <?php endif; ?>

          <?php $wx_html = function_exists('avalanche_modern_advisory_weather_html') ? avalanche_modern_advisory_weather_html($node) : ''; ?>
          <?php if (!empty($a['mountain_weather']) || $wx_html !== ''): ?>
            <div class="nac-divider nac-weather">
              <h2 class="nac-h2"><?php print t('Mountain Weather'); ?></h2>
              <?php if (!empty($a['mountain_weather'])): ?>
                <div class="nac-tinymce"><?php print check_markup($a['mountain_weather'], $a['mountain_weather_format'] ? $a['mountain_weather_format'] : 'full_html'); ?></div>
              <?php endif; ?>
              <?php print $wx_html; ?>
            </div>
          <?php endif; ?>

      </div>
    </div>

    <?php if (!empty($a['disclaimer'])): ?>
      <div class="nac-disclaimer"><?php print check_markup($a['disclaimer'], $a['disclaimer_format'] ? $a['disclaimer_format'] : 'full_html'); ?></div>
    <?php endif; ?>

  </div>
  <div class="am-forecast__links"><?php print render($content['links']); ?></div>
  <?php print render($content['comments']); ?>
</article>
