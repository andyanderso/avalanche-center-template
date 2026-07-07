<?php

function _get_advisory_data($node) {
  $a = array();
  if (!is_object($node)) { return $a; }

  $labels = array('No Rating', '1. Low', '2. Moderate', '3. Considerable', '4. High', '5. Extreme');

  // These should really exist within a Drupal settings page, but this will do for now.
  $elevations = array(
    1 => array('Below Treeline', 'Near Treeline', 'Above Treeline'), // Central Sierra
    2 => array('Below treeline', 'Near treeline', 'Above treeline'), // 
    4 => array('Below 7,000 ft.', '7,000-8,500 ft.', 'Above 8,500 ft.'), // 
    5 => array('Below 8,000 ft.', '8,000-9,500 ft.', 'Above 9,500 ft.'), // 
    6 => array('Below 8,000 ft.', '8,000-9,500 ft.', 'Above 9,500 ft.'), // 
    7 => array('Below treeline', 'Near treeline', 'Above treeline'), // 
    8 => array('Below treeline', 'Near treeline', 'Above treeline'), // 
  );

  $region_id = $node->field_region_forecaster['und'][0]['tid'];
  $a['region_id'] = $region_id;
  $user_fields = user_load($node->uid);
  $forecaster = '';
  if (!empty($user_fields->field_user_first_name['und'][0]['safe_value'])) { $forecaster = $user_fields->field_user_first_name['und'][0]['safe_value']; }
  if (!empty($user_fields->field_user_last_name['und'][0]['safe_value'])) { $forecaster .= ' ' . $user_fields->field_user_last_name['und'][0]['safe_value']; }
  if (empty($forecaster)) { $forecaster = $user_fields->name; }
  $a['forecaster'] = $forecaster;

  $a['elevation_labels'] = array(
      'upper' => $elevations[$region_id][2],
      'mid' => $elevations[$region_id][1],
      'lower' => $elevations[$region_id][0],
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

$duration = $node->field_duration['und'][0]['value'];




function _build_problem_html($problem, $title = '', $elevation_labels) {

  $type = $problem['type'];
 if (!empty($problem['rose'])) { $rose = $problem['rose']; }
  if (!empty($problem['likelihood'])) { $likelihood = $problem['likelihood']; }
  else { $likelihood = 0; }
  if (!empty($problem['distribution'])) { $distribution = $problem['distribution']; }
  else { $distribution = 0; }
  if (!empty($problem['size'])) { $size = $problem['size']; }
  else { $size = 0; }
  if (!empty($problem['trend'])) { $trend = $problem['trend']; }
  else { $trend = 0; }
  $a = array('','Decreasing Danger','Same Danger','Increasing Danger');
  $trend_text = $a[$trend];
  $description = $problem['description'];

$table = <<<EOT
<div class="clearbg avalanche-problem-row">
  <span class="title">$title</span>
<ul>
    <li id="problem-type" class="border-right">
      <div class="problem-char-label">Type
         <span id="type-$type-info" class="info">?</span>
      </div>
      <div class="problem-char-wrapper problem-type">
	  <a href="http://www.sierraavalanchecenter.org/avalanche-problems#$type" target="_blank">
	      <img src="/themes/responsive_sac/img/api/$type.jpg">
	  </a>
      </div>
    </li>

    <li id="problem-rose" class="border-right">
      <div class="problem-char-label">Aspect/Elevation
        <span id="problem-rose-info" class="info">?</span>
      </div>
      <div class="problem-char-wrapper">
	  <img id="rose" src="$rose">
      </div>
    </li>

    <li class="border-right">
      <div class="problem-char-label">Likelihood
          <span id="likelihood-info" class="info">?</span>
      </div>
      <div class="problem-char-wrapper">
	  <div class="top-label">likely</div>
          <div class="middle-data">
            <div class="middle-data-overlay middle-data-overlay-$likelihood"></div>
          </div>
          <div class="bottom-label">unlikely</div>
      </div>
    </li>

    <li class="border-right">
      <div class="problem-char-label">Size
        <span id="size-info" class="info">?</span>
      </div>
      <div class="problem-char-wrapper">
	  <div class="top-label">large</div>
          <div class="middle-data">
            <div class="middle-data-overlay middle-data-overlay-$size"></div>
          </div>
          <div class="bottom-label">small</div>
      </div>
    </li>
    
    <li id="trend">
      <div class="problem-char-label">Trend
        <span id="trend-info" class="info">?</span>
      </div>
      <div class="problem-char-wrapper">
	  <div class="middle-data-graph"><img src="/themes/responsive_sac/img/trend/$trend.png"></div>
          <div class="middle-data-graph-text">$trend_text</div>
      </div>
    </li>
  </ul>
<div id="problem-description">$description</div>

</div>

EOT;

  return $table;
}
