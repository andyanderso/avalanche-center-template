<?php

/**
 * @file
 * Advisory (avalanche forecast) data preparation for the responsive_sac theme.
 *
 * Elevation-band labels are read from theme settings (one set per center)
 * rather than a hardcoded per-taxonomy-term-id table — the original table
 * here only matched Sierra Avalanche Center's own multi-zone region setup
 * and didn't generalize. See also avalanche_modern/inc/advisory.inc, which
 * carries the same fix.
 */

function responsive_sac_get_advisory_data($node) {
  $a = array();
  if (!is_object($node)) { return $a; }

  $labels = array(t('No Rating'), t('1. Low'), t('2. Moderate'), t('3. Considerable'), t('4. High'), t('5. Extreme'));

  // Fixed: the actual field is field_forecast_region (was field_region_forecaster).
  $region_id = 0;
  if (!empty($node->field_forecast_region['und'][0]['tid'])) {
    $region_id = $node->field_forecast_region['und'][0]['tid'];
  }
  $a['region_id'] = $region_id;
  $user_fields = user_load($node->uid);
  $forecaster = '';
  if (!empty($user_fields->field_user_first_name['und'][0]['safe_value'])) { $forecaster = $user_fields->field_user_first_name['und'][0]['safe_value']; }
  if (!empty($user_fields->field_user_last_name['und'][0]['safe_value'])) { $forecaster .= ' ' . $user_fields->field_user_last_name['und'][0]['safe_value']; }
  if (empty($forecaster)) { $forecaster = $user_fields->name; }
  $a['forecaster'] = $forecaster;

  $upper_band = theme_get_setting('upper_elevation_band');
  $mid_band = theme_get_setting('middle_elevation_band');
  $lower_band = theme_get_setting('lower_elevation_band');
  $a['elevation_labels'] = array(
    'upper' => !empty($upper_band) ? $upper_band : t('Above Treeline'),
    'mid' => !empty($mid_band) ? $mid_band : t('Near Treeline'),
    'lower' => !empty($lower_band) ? $lower_band : t('Below Treeline'),
  );

  // Danger ratings
  if (!empty($node->field_danger_rating_3)) {
    $a['danger_rating']['upper'] = $node->field_danger_rating_3['und'][0]['value'];
    $a['danger_rating']['upper_label'] = $labels[$a['danger_rating']['upper']];
    $a['danger_rating']['upper_elevation'] = $a['elevation_labels']['upper'];
  }

  if (!empty($node->field_danger_rating_2)) {
    $a['danger_rating']['mid'] = $node->field_danger_rating_2['und'][0]['value'];
    $a['danger_rating']['mid_label'] = $labels[$a['danger_rating']['mid']];
    $a['danger_rating']['mid_elevation'] = $a['elevation_labels']['mid'];
  }

  if (!empty($node->field_danger_rating_1)) {
    $a['danger_rating']['lower'] = $node->field_danger_rating_1['und'][0]['value'];
    $a['danger_rating']['lower_label'] = $labels[$a['danger_rating']['lower']];
    $a['danger_rating']['lower_elevation'] = $a['elevation_labels']['lower'];
  }

  if (!empty($node->field_overalldanger)) {
    $a['danger_rating']['overall'] = $node->field_overalldanger['und'][0]['value'];
  }

  // All other fields, optional
  if (!empty($node->field_bottom_line)) {
    $a['bottom_line'] = $node->field_bottom_line['und'][0]['value'];
  }

 //if (!empty($node->field_overall_danger_rose)) {
 //   $a['overall_danger_rose'] = $node->field_overall_danger_rose['und'][0]['value'];
 // }

  if (!empty($node->field_special_announcement)) {
    $a['special_announcement'] = $node->field_special_announcement['und'][0]['value'];
  }

  if (!empty($node->field_general_announcements)) {
    $a['general_announcements'] = $node->field_general_announcements['und'][0]['value'];
  }

  if (!empty($node->field_current_conditions)) {
    $a['current_conditions'] = $node->field_current_conditions['und'][0]['value'];
  }

  if (!empty($node->field_recent_activity)) {
    $a['recent_activity'] = $node->field_recent_activity['und'][0]['value'];
  }

  if (!empty($node->field_mountain_weather)) {
    $a['mountain_weather'] = $node->field_mountain_weather['und'][0]['value'];
  }

  if (!empty($node->field_avalanche_warning)) {
    $a['avalanche_warning'] = $node->field_avalanche_warning['und'][0]['value'];
  }

  if (!empty($node->field_avalanche_watch)) {
    $a['avalanche_watch'] = $node->field_avalanche_watch['und'][0]['value'];
  }

  if (!empty($node->field_avalanche_sab)) {
    $a['avalanche_sab'] = $node->field_avalanche_sab['und'][0]['value'];
  }

  // Problem 1
  if (!empty($node->field_rose_1)) {
    $a['problem_1']['rose'] = $node->field_rose_1['und'][0]['img_detailed_rose'];
  }

  if (!empty($node->field_duration_1)) {
    $a['problem_1']['duration'] = $node->field_duration['und'][0]['value'];
  }

  if (!empty($node->field_type_1)) {
    $a['problem_1']['type'] = $node->field_type_1['und'][0]['value'];
  }

  if (!empty($node->field_likelihood_1)) {
    $a['problem_1']['likelihood'] = $node->field_likelihood_1['und'][0]['value'];
  }

  if (!empty($node->field_size_1)) {
    $a['problem_1']['size'] = $node->field_size_1['und'][0]['value'];
  }

  if (!empty($node->field_distribution_1)) {
    $a['problem_1']['distribution'] = $node->field_distribution_1['und'][0]['value'];
  }

  if (!empty($node->field_trend_1)) {
    $a['problem_1']['trend'] = $node->field_trend_1['und'][0]['value'];
  }

  if (!empty($node->field_description_1)) {
    $a['problem_1']['description'] = $node->field_description_1['und'][0]['value'];
  }

  // Problem 2
  if (!empty($node->field_rose_2)) {
    $a['problem_2']['rose'] = $node->field_rose_2['und'][0]['img_detailed_rose'];
  }

  if (!empty($node->field_duration_2)) {
    $a['problem_2']['duration'] = $node->field_duration['und'][0]['value'];
  }

  if (!empty($node->field_type_2)) {
    $a['problem_2']['type'] = $node->field_type_2['und'][0]['value'];
  }

  if (!empty($node->field_likelihood_2)) {
    $a['problem_2']['likelihood'] = $node->field_likelihood_2['und'][0]['value'];
  }

  if (!empty($node->field_size_2)) {
    $a['problem_2']['size'] = $node->field_size_2['und'][0]['value'];
  }

  if (!empty($node->field_distribution_2)) {
    $a['problem_2']['distribution'] = $node->field_distribution_2['und'][0]['value'];
  }

  if (!empty($node->field_trend_2)) {
    $a['problem_2']['trend'] = $node->field_trend_2['und'][0]['value'];
  }

  if (!empty($node->field_description_2)) {
    $a['problem_2']['description'] = $node->field_description_2['und'][0]['value'];
  }

  // Problem 3
  if (!empty($node->field_rose_3)) {
    $a['problem_3']['rose'] = $node->field_rose_3['und'][0]['img_detailed_rose'];
  }

  if (!empty($node->field_duration_3)) {
    $a['problem_3']['duration'] = $node->field_duration['und'][0]['value'];
  }

  if (!empty($node->field_type_3)) {
    $a['problem_3']['type'] = $node->field_type_3['und'][0]['value'];
  }

  if (!empty($node->field_likelihood_3)) {
    $a['problem_3']['likelihood'] = $node->field_likelihood_3['und'][0]['value'];
  }

  if (!empty($node->field_size_3)) {
    $a['problem_3']['size'] = $node->field_size_3['und'][0]['value'];
  }

  if (!empty($node->field_distribution_3)) {
    $a['problem_3']['distribution'] = $node->field_distribution_3['und'][0]['value'];
  }

  if (!empty($node->field_trend_3)) {
    $a['problem_3']['trend'] = $node->field_trend_3['und'][0]['value'];
  }

  if (!empty($node->field_description_3)) {
    $a['problem_3']['description'] = $node->field_description_3['und'][0]['value'];
  }

  return $a;
}
