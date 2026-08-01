<?php
/**
 * @file
 * Advisory (avalanche forecast) data preparation for the avalanche_modern theme.
 *
 * Ported from responsive_sac/inc/advisory.inc, fixing the field_forecast_region
 * bug and exposing the raw 24-column danger-rose values so the modern template
 * can render the rose client-side as SVG. Elevation-band labels are read from
 * theme settings (one set per center) rather than the original hardcoded
 * per-taxonomy-term-id table, which only matched Sierra Avalanche Center's
 * own multi-zone region setup.
 */

/**
 * Builds a structured array of forecast data from an advisory node.
 */
function avalanche_modern_get_advisory_data($node) {
  $a = array();
  if (!is_object($node)) {
    return $a;
  }

  $labels = array(t('No Rating'), t('Low'), t('Moderate'), t('Considerable'), t('High'), t('Extreme'));

  // Fixed: the actual field is field_forecast_region (was field_region_forecaster).
  $region_id = 0;
  $region_name = '';
  if (!empty($node->field_forecast_region['und'][0]['tid'])) {
    $region_id = $node->field_forecast_region['und'][0]['tid'];
    if (!empty($node->field_forecast_region['und'][0]['taxonomy_term']->name)) {
      $region_name = $node->field_forecast_region['und'][0]['taxonomy_term']->name;
    }
    else {
      $term = taxonomy_term_load($region_id);
      if ($term) {
        $region_name = $term->name;
      }
    }
  }
  $a['region_id'] = $region_id;
  $a['region_name'] = $region_name;

  // Elevation-band labels come from the theme's own settings (one set per
  // center, already exposed on the theme settings form) rather than a
  // hardcoded per-region table — the original per-taxonomy-term-id table
  // here was Sierra Avalanche Center's specific multi-zone setup and doesn't
  // generalize to other centers' region structures.
  $upper_band = theme_get_setting('upper_elevation_band');
  $mid_band = theme_get_setting('middle_elevation_band');
  $lower_band = theme_get_setting('lower_elevation_band');
  $a['elevation_labels'] = array(
    'upper' => !empty($upper_band) ? $upper_band : t('Above Treeline'),
    'mid' => !empty($mid_band) ? $mid_band : t('Near Treeline'),
    'lower' => !empty($lower_band) ? $lower_band : t('Below Treeline'),
  );

  // Forecaster name from the node author (uses their "Name as displayed on the
  // website" when set, else the username).
  $author = user_load($node->uid);
  $a['forecaster'] = $author ? user_format_name($author) : '';

  // Danger ratings. _3 = upper/above, _2 = mid/near, _1 = lower/below.
  $rating_map = array('upper' => 'field_danger_rating_3', 'mid' => 'field_danger_rating_2', 'lower' => 'field_danger_rating_1');
  foreach ($rating_map as $key => $field) {
    $val = isset($node->{$field}['und'][0]['value']) ? (int) $node->{$field}['und'][0]['value'] : 0;
    $a['danger_rating'][$key] = $val;
    $a['danger_rating'][$key . '_label'] = isset($labels[$val]) ? $labels[$val] : $labels[0];
    $a['danger_rating'][$key . '_elevation'] = $a['elevation_labels'][$key];
  }
  $a['danger_rating']['overall'] = isset($node->field_overalldanger['und'][0]['value']) ? (int) $node->field_overalldanger['und'][0]['value'] : 0;
  $a['danger_rating']['overall_label'] = isset($labels[$a['danger_rating']['overall']]) ? $labels[$a['danger_rating']['overall']] : $labels[0];

  if (!empty($node->field_bottom_line['und'][0]['value'])) {
    $a['bottom_line'] = $node->field_bottom_line['und'][0]['value'];
  }

  // Avalanche problems 1-3.
  for ($n = 1; $n <= 3; $n++) {
    $key = 'problem_' . $n;
    $type_field = 'field_type_' . $n;
    if (empty($node->{$type_field}['und'][0]['value'])) {
      continue;
    }
    $p = array();
    $p['type'] = (int) $node->{$type_field}['und'][0]['value'];
    $p['likelihood'] = isset($node->{'field_likelihood_' . $n}['und'][0]['value']) ? (int) $node->{'field_likelihood_' . $n}['und'][0]['value'] : 0;
    $p['description'] = isset($node->{'field_description_' . $n}['und'][0]['value']) ? $node->{'field_description_' . $n}['und'][0]['value'] : '';

    // Size is a 2-value range (D-scale). Collect all set deltas.
    $sizes = array();
    if (!empty($node->{'field_size_' . $n}['und'])) {
      foreach ($node->{'field_size_' . $n}['und'] as $item) {
        if (isset($item['value']) && $item['value'] !== '') {
          $sizes[] = (int) $item['value'];
        }
      }
    }
    $p['size_min'] = !empty($sizes) ? min($sizes) : 0;
    $p['size_max'] = !empty($sizes) ? max($sizes) : 0;

    // Raw 24-column rose values (0-13). Columns 0-7 above, 8-15 near, 16-23 below.
    $rose = array_fill(0, 24, 0);
    if (!empty($node->{'field_rose_' . $n}['und'][0])) {
      $item = $node->{'field_rose_' . $n}['und'][0];
      for ($c = 0; $c < 24; $c++) {
        if (isset($item[$c]) && $item[$c] !== '') {
          $rose[$c] = (int) $item[$c];
        }
      }
    }
    $p['rose'] = $rose;
    $a[$key] = $p;
  }

  // Combined bottom-line rose: per-column max across the three problem roses.
  $blr = array_fill(0, 24, 0);
  foreach (array('problem_1', 'problem_2', 'problem_3') as $item) {
    if (isset($a[$item]['rose'])) {
      for ($i = 0; $i < 24; $i++) {
        if ($a[$item]['rose'][$i] > $blr[$i]) {
          $blr[$i] = $a[$item]['rose'][$i];
        }
      }
    }
  }
  $a['bottom_line_rose'] = $blr;

  // Duration / dates.
  $a['duration'] = isset($node->field_duration['und'][0]['value']) ? (int) $node->field_duration['und'][0]['value'] : 0;
  $a['created'] = $node->created;
  $a['expires'] = $node->created + ($a['duration'] * 3600);
  $a['expired'] = REQUEST_TIME > $a['expires'];
  // Show forecast times in the region's local timezone (NULL = site default).
  $a['timezone'] = function_exists('avalanche_fwp_forecast_timezone') ? avalanche_fwp_forecast_timezone($node) : NULL;

  // If this forecast has expired, link to the region's latest advisory
  // (which may be a newer node, or this same one if nothing newer exists).
  $a['current_forecast_url'] = '';
  if ($a['expired'] && $region_id && function_exists('avalanche_danger_map_get_advisory_info')) {
    $latest = avalanche_danger_map_get_advisory_info($region_id);
    if (!empty($latest['url']) && (int) $latest['nid'] !== (int) $node->nid) {
      $a['current_forecast_url'] = $latest['url'];
    }
  }

  // Text sections.
  foreach (array('field_text_discussion' => 'discussion', 'field_recent_activity' => 'recent_activity', 'field_mountain_weather' => 'mountain_weather', 'field_disclaimer' => 'disclaimer') as $field => $alias) {
    if (!empty($node->{$field}['und'][0]['value'])) {
      $a[$alias] = $node->{$field}['und'][0]['value'];
      $a[$alias . '_format'] = isset($node->{$field}['und'][0]['format']) ? $node->{$field}['und'][0]['format'] : NULL;
    }
  }

  return $a;
}

/**
 * Builds the detailed-weather display markup (current conditions + two-day
 * forecast) from the legacy SAC weather fields, mirroring the responsive_sac
 * layout. Returns '' when none of the fields hold content; individual rows /
 * bands are omitted when empty.
 */
function avalanche_modern_advisory_weather_html($node) {
  $wx = function ($field) use ($node) {
    if (isset($node->{$field}['und'][0]['value']) && trim((string) $node->{$field}['und'][0]['value']) !== '') {
      return check_plain(trim((string) $node->{$field}['und'][0]['value']));
    }
    return '';
  };

  $current_desc = theme_get_setting('current_wx_conditions_desc');
  $wx_low = theme_get_setting('wx_elevation_low');
  $wx_high = theme_get_setting('wx_elevation_high');
  $nws_name = theme_get_setting('local_nws_name');
  $nws_url = theme_get_setting('local_nws_url');

  // Unit suffixes for the numeric weather values (imperial by default; metric
  // when the theme's "Weather units" setting is metric). The suffixes are the
  // only thing that changes - the field values are entered in whichever unit
  // the center forecasts in.
  $metric = (theme_get_setting('weather_units') === 'metric');
  $u_temp = $metric ? ' °C' : ' deg. F.';
  $u_speed = $metric ? ' km/h' : ' mph';
  $u_depth = $metric ? ' cm' : ' inches';
  $u_snow = $metric ? ' cm' : ' in.';

  $out = '';

  // Current conditions table.
  $cur = array(
    array(t('0600 temperature'), 'field_temp8700', $u_temp),
    array(t('Max. temperature in the last 24 hours'), 'field_hr24maxtemp', $u_temp),
    array(t('Average wind direction during the last 24 hours'), 'field_hr24winddir', ''),
    array(t('Average wind speed during the last 24 hours'), 'field_hr24windspeed', $u_speed),
    array(t('Maximum wind gust in the last 24 hours'), 'field_hr24maxgust', $u_speed),
    array(t('New snowfall in the last 24 hours'), 'field_hr24snowfall', $u_depth),
    array(t('Total snow depth'), 'field_totalsnowdepth', $u_depth),
  );
  $rows = '';
  foreach ($cur as $c) {
    $v = $wx($c[1]);
    if ($v !== '') {
      $rows .= '<tr><th scope="row">' . $c[0] . '</th><td>' . $v . $c[2] . '</td></tr>';
    }
  }
  if ($rows !== '') {
    $out .= '<div class="nac-wx-block">';
    if (!empty($current_desc)) {
      $out .= '<h4 class="nac-h4 nac-wx-subtitle">' . check_plain($current_desc) . '</h4>';
    }
    $out .= '<table class="nac-wx-table nac-wx-current"><tbody>' . $rows . '</tbody></table></div>';
  }

  // Two-day mountain weather forecast, one table per elevation band.
  $cols = array('today' => t('Today'), 'tonight' => t('Tonight'), 'tomorrow' => t('Tomorrow'));
  $metrics = array(
    array(t('Weather'), 'weather', ''),
    array(t('Temperatures'), 'temp', $u_temp),
    array(t('Wind Direction'), 'winddirection', ''),
    array(t('Wind Speed'), 'windspeed', ''),
    array(t('Expected snowfall'), 'snow', $u_snow),
  );
  $bands = array(array($wx_low, '7to8'), array($wx_high, '8to9'));
  $forecast = '';
  foreach ($bands as $band) {
    list($band_label, $suffix) = $band;
    $band_has = FALSE;
    foreach ($cols as $col => $cl) {
      foreach ($metrics as $m) {
        if ($wx('field_' . $col . $suffix . $m[1]) !== '') {
          $band_has = TRUE;
          break 2;
        }
      }
    }
    if (!$band_has) {
      continue;
    }
    $thead = '<thead><tr><th></th>';
    foreach ($cols as $col => $cl) {
      $thead .= '<th>' . $cl . '</th>';
    }
    $thead .= '</tr></thead>';
    $tbody = '';
    foreach ($metrics as $m) {
      $row_has = FALSE;
      foreach ($cols as $col => $cl) {
        if ($wx('field_' . $col . $suffix . $m[1]) !== '') {
          $row_has = TRUE;
          break;
        }
      }
      if (!$row_has) {
        continue;
      }
      $tbody .= '<tr><th scope="row">' . $m[0] . '</th>';
      foreach ($cols as $col => $cl) {
        $v = $wx('field_' . $col . $suffix . $m[1]);
        $tbody .= '<td>' . ($v !== '' ? $v . $m[2] : '&mdash;') . '</td>';
      }
      $tbody .= '</tr>';
    }
    if (!empty($band_label)) {
      $forecast .= '<div class="nac-wx-band-label">' . check_plain($band_label) . '</div>';
    }
    $forecast .= '<table class="nac-wx-table nac-wx-forecast">' . $thead . '<tbody>' . $tbody . '</tbody></table>';
  }
  if ($forecast !== '') {
    $sub = t('Two-Day Mountain Weather Forecast');
    if (!empty($nws_name)) {
      $sub .= ' — ' . t('produced in partnership with') . ' ' . (!empty($nws_url)
        ? '<a href="' . check_url($nws_url) . '" target="_blank" rel="noopener">' . check_plain($nws_name) . '</a>'
        : check_plain($nws_name));
    }
    $out .= '<div class="nac-wx-block"><h4 class="nac-h4 nac-wx-subtitle">' . $sub . '</h4>' . $forecast . '</div>';
  }

  return $out;
}

/**
 * Problem type machine value to human label.
 */
function avalanche_modern_problem_type_label($type) {
  $types = array(
    1 => t('Storm Slab'),
    2 => t('Deep Slab'),
    3 => t('Wind Slab'),
    4 => t('Wet Slab'),
    5 => t('Persistent Slab'),
    6 => t('Loose Wet'),
    7 => t('Loose Dry'),
    8 => t('Normal Caution'),
    9 => t('Cornice'),
    10 => t('Glide'),
  );
  return isset($types[$type]) ? $types[$type] : '';
}
