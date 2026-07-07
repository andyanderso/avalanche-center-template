<?php
/**
* @file
* Bartik's theme implementation to display a node.
*
* Available variables:
* - $title: the (sanitized) title of the node.
* - $content: An array of node items. Use render($content) to print them all,
*   or print a subset such as render($content['field_example']). Use
*   hide($content['field_example']) to temporarily suppress the printing of a
*   given element.
* - $user_picture: The node author's picture from user-picture.tpl.php.
* - $date: Formatted creation date. Preprocess functions can reformat it by
*   calling format_date() with the desired parameters on the $created variable.
* - $name: Themed username of node author output from theme_username().
* - $node_url: Direct url of the current node.
* - $display_submitted: Whether submission information should be displayed.
* - $submitted: Submission information created from $name and $date during
*   template_preprocess_node().
* - $classes: String of classes that can be used to style contextually through
*   CSS. It can be manipulated through the variable $classes_array from
*   preprocess functions. The default values can be one or more of the
*   following:
*   - node: The current template type, i.e., "theming hook".
*   - node-[type]: The current node type. For example, if the node is a
*     "Blog entry" it would result in "node-blog". Note that the machine
*     name will often be in a short form of the human readable label.
*   - node-teaser: Nodes in teaser form.
*   - node-preview: Nodes in preview mode.
*   The following are controlled through the node publishing options.
*   - node-promoted: Nodes promoted to the front page.
*   - node-stxt-1y: Nodes ordered above other non-stxt-1y nodes in teaser
*     listings.
*   - node-unpublished: Unpublished nodes visible only to administrators.
* - $title_prefix (array): An array containing additional output populated by
*   modules, intended to be displayed in front of the main title tag that
*   appears in the template.
* - $title_suffix (array): An array containing additional output populated by
*   modules, intended to be displayed after the main title tag that appears in
*   the template.
*
* Other variables:
* - $node: Full node object. Contains data that may not be safe.
* - $type: Node type, i.e. story, page, blog, etc.
* - $comment_count: Number of comments attached to the node.
* - $uid: User ID of the node author.
* - $created: Time the node was published formatted in Unix timestamp.
* - $classes_array: Array of html class attribute values. It is flattened
*   into a string within the variable $classes.
* - $zebra: Outputs either "even" or "odd". Useful for zebra striping in
*   teaser listings.
* - $id: Position of the node. Increments each time it's output.
*
* Node status variables:
* - $view_mode: View mode, e.g. 'full', 'teaser'...
* - $teaser: Flag for the teaser state (shortcut for $view_mode == 'teaser').
* - $page: Flag for the full page state.
* - $promote: Flag for front page promotion state.
* - $stxt-1y: Flags for stxt-1y post setting.
* - $status: Flag for published status.
* - $comment: State of comment settings for the node.
* - $readmore: Flags true if the teaser content of the node cannot hold the
*   main body content.
* - $is_front: Flags true when presented in the front page.
* - $logged_in: Flags true when the current user is a logged-in member.
* - $is_admin: Flags true when the current user is an administrator.
*
* Field variables: for each field instance attached to the node a corresponding
* variable is defined, e.g. $node->body becomes $body. When needing to access
* a field's raw values, developers/themers are strongly encouraged to use these
* variables. Otherwise they will have to explicitly specify the desired field
* language, e.g. $node->body['en'], thus overriding any language negotiation
* rule that was previously applied.
*
* @see template_preprocess()
* @see template_preprocess_node()
* @see template_process()
*/
?>
  <script type="text/javascript">
// wx Responsive table JS
(function($) {
      Drupal.behaviors.wxToggler = {
        attach: function(context, settings) {
$( "ul" ).on( "click", "li", function() {
  var pos = $(this).index()+2;
  $("#wxforecast tr").find('td:not(:eq(0))').hide();
  $('#wxforecast td:nth-child('+pos+')').css('display','table-cell');
  $("#wxforecast tr").find('th:not(:eq(0))').hide();
  $('li').removeClass('active');
  $(this).addClass('active');
});

// Initialize the media query
  var mediaQuery = window.matchMedia('(min-width: 640px)');

  // Add a listen event
  mediaQuery.addListener(doSomething);

  // Function to do something with the media query
  function doSomething(mediaQuery) {
    if (mediaQuery.matches) {
      $('.sep').attr('colspan',5);
    } else {
      $('.sep').attr('colspan',2);
    }
  }

  // On load
  doSomething(mediaQuery);
          // javascript code
        }
      };
    }) (jQuery);
</script>
<?php
//ini_set('display_errors', 1);
//ini_set('display_startup_errors', 1);
//error_reporting(E_ALL);
global $base_path, $base_url;
$url = $base_url;
$node = $variables['node'];
$author = user_load($node->uid);
$forecaster_name = theme_get_setting('forecaster_name_field');
$display_name = field_get_items('user', $author, $forecaster_name);
$company = field_get_items('user', $author, 'field__company_link');
$userid = $node->uid;
$duration=$node->field_duration['und'][0]['value'];
$expduration = $duration*3600;
$forecastdate = date("F j, Y \@ g:i a", $node->created);
$forcastTimestamp = strtotime(date("F j, Y G:i", $node->created));
$expdateTimestamp = $node->created + $expduration;
$expdate = date("F j, Y \@ g:i a", $expdateTimestamp);
$now = date("F j, Y G:i");
$nowTimestamp = strtotime($now);
$now1 = new DateTime();
$now1->setTimestamp($nowTimestamp);
$future_date = new DateTime();
$future_date->setTimestamp($expdateTimestamp);
$timeLeftToExpire = $future_date->diff($now1);
$nws_name = theme_get_setting('local_nws_name');
$nws_url = theme_get_setting('local_nws_url');
$wx_low = theme_get_setting('wx_elevation_low');
$wx_high = theme_get_setting('wx_elevation_high');
$current_wx_desc = theme_get_setting('current_wx_conditions_desc');
$show_regions = theme_get_setting('show_forecast_region');
$wx_map_page = theme_get_setting('wx_map_page');
$wx_table_page = theme_get_setting('wx_table_page');
$obs_page = theme_get_setting('obs_page');
$submit_snowpack_obs_page = theme_get_setting('submit_snowpack_obs_page');
$submit_avalanche_obs_page = theme_get_setting('submit_avalanche_obs_page');
$upper_band = theme_get_setting('upper_elevation_band');
$mid_band = theme_get_setting('middle_elevation_band');
$lower_band = theme_get_setting('lower_elevation_band');
$show_danger_rose = theme_get_setting('enable_danger_rose_view');
$rose_1 = array_slice($node->field_rose_1['und'][0],0,23);
$rose_1_tmp = array_filter($rose_1);
$display_rose_1 = "show";
if (empty(array_filter($rose_1))) {
  $display_rose_1 = "hide";
}
$rose_2 = array_slice($node->field_rose_2['und'][0],0,23);
$rose_2_tmp = array_filter($rose_2);
$display_rose_2 = "show";
if (empty(array_filter($rose_2))) {
  $display_rose_2 = "hide";
}
$rose_3 = array_slice($node->field_rose_3['und'][0],0,23);
$rose_3_tmp = array_filter($rose_3);
$display_rose_3 = "show";
if (empty(array_filter($rose_3))) {
  $display_rose_3 = "hide";
}


if (!empty($node->field_size_1['und'][0]['value'])) {$size11 = $node->field_size_1['und'][0]['value'];}
else { $size11 = 0; }
if (!empty($node->field_size_1['und'][1]['value'])) {$size12 = $node->field_size_1['und'][1]['value'];}
else { $size12 = 0; }
if (!empty($node->field_size_1['und'][2]['value'])) {$size13 = $node->field_size_1['und'][2]['value'];}
else { $size13 = 0; }
if (!empty($node->field_size_1['und'][3]['value'])) {$size14 = $node->field_size_1['und'][3]['value'];}
else { $size14 = 0; }
if (!empty($node->field_size_1['und'][4]['value'])) {$size15 = $node->field_size_1['und'][4]['value'];}
else { $size15 = 0; }

if (!empty($node->field_size_2['und'][0]['value'])) {$size21 = $node->field_size_2['und'][0]['value'];}
else { $size21 = 0; }
if (!empty($node->field_size_2['und'][1]['value'])) {$size22 = $node->field_size_2['und'][1]['value'];}
else { $size22 = 0; }
if (!empty($node->field_size_2['und'][2]['value'])) {$size23 = $node->field_size_2['und'][2]['value'];}
else { $size23 = 0; }
if (!empty($node->field_size_2['und'][3]['value'])) {$size24 = $node->field_size_2['und'][3]['value'];}
else { $size24 = 0; }
if (!empty($node->field_size_2['und'][4]['value'])) {$size25 = $node->field_size_2['und'][4]['value'];}
else { $size25 = 0; }

if (!empty($node->field_size_3['und'][0]['value'])) {$size31 = $node->field_size_3['und'][0]['value'];}
else { $size31 = 0; }
if (!empty($node->field_size_3['und'][1]['value'])) {$size32 = $node->field_size_3['und'][1]['value'];}
else { $size32 = 0; }
if (!empty($node->field_size_3['und'][2]['value'])) {$size33 = $node->field_size_3['und'][2]['value'];}
else { $size33 = 0; }
if (!empty($node->field_size_3['und'][3]['value'])) {$size34 = $node->field_size_3['und'][3]['value'];}
else { $size34 = 0; }
if (!empty($node->field_size_3['und'][4]['value'])) {$size35 = $node->field_size_3['und'][4]['value'];}
else { $size35 = 0; }
?>
<div id="node-<?php print $node->nid; ?>" class="<?php print implode(' ', $classes); ?> clearfix"<?php print backdrop_attributes($attributes); ?>>
  <div class="content clearfix"<?php print backdrop_attributes($content_attributes); ?>>
    <?php
    // We hide the comments and links now so that we can render them later.
    hide($content['comments']);
    hide($content['links']);
    ?>
    <?php if ($show_danger_rose  == 2): ?>
      <div id="title-date-row" class="clearbg">
        <span>
          <a style="display:none;" class="toggle-advisory" id="advanced-link" href="#advanced">
            <div class="switch-button rose">
              <?php print render(field_view_field('node', $node, 'field_overall_danger_rose', array('label' => 'hidden'))); ?>
              <?php print t('Switch to DANGER ROSE View'); ?>
            </div>
          </a>
        </span>
        <span>
          <a style="display:none;" class="toggle-advisory" id="standard-link" href="#simple">
            <div class="switch-button mtn">
              <img src="/<?php print path_to_theme(); ?>/img/basic-view.png">
              <?php print t('Switch to BASIC View'); ?>
            </div>
          </a>
        </span>
      </div>
    <?php endif; ?>
    <!--PUBLISHED DATE AND FORECASTER NAME-->
    <div id="subtitle-date-row" class="darkbg">
      <div id="advisory-published-date" class="advisory-date">
        <?php
        if ($duration >= '120') {
          echo t('Avalanche Advisory published on @date', array('@date' => $forecastdate));
        }
        elseif ($nowTimestamp <= $expdateTimestamp) {
          echo t('Avalanche Advisory published on @date', array('@date' => $forecastdate)) . '</br><span id="timeToExp">' . t('This Avalanche Advisory expires in') . ' ';
          if (($timeLeftToExpire->format("%h") < 2) && ($timeLeftToExpire->format("%d") == 0) ) {
            echo '<span style="color:red">';
            echo t('@hours hours, @minutes minutes', array('@hours' => $timeLeftToExpire->format('%h'), '@minutes' => $timeLeftToExpire->format('%i')));
            echo '</span></span>';
          }
          elseif (($timeLeftToExpire->format("%d") >= 1) && ($timeLeftToExpire->format("%d") < 2)) {
            echo t('@days day, @hours hours, @minutes minutes', array('@days' => $timeLeftToExpire->format('%d'), '@hours' => $timeLeftToExpire->format('%h'), '@minutes' => $timeLeftToExpire->format('%i')));
            echo '</span>';
          }
          elseif ($timeLeftToExpire->format("%d") > 2) {
            echo t('@days days, @hours hours, @minutes minutes', array('@days' => $timeLeftToExpire->format('%d'), '@hours' => $timeLeftToExpire->format('%h'), '@minutes' => $timeLeftToExpire->format('%i')));
            echo '</span>';
          }
          elseif ($timeLeftToExpire->format("%d") < 1) {
            echo t('@hours hours, @minutes minutes', array('@hours' => $timeLeftToExpire->format('%h'), '@minutes' => $timeLeftToExpire->format('%i')));
            echo '</span>';
          }

        }
        elseif ($nowTimestamp > $expdateTimestamp) {
          echo '<span style="background:red">' . t('THIS AVALANCHE ADVISORY EXPIRED ON @date', array('@date' => $expdate)) . '</span></br>' . t('Avalanche Advisory published on @date', array('@date' => $forecastdate));
        }
        ?>
      </div>
      <div id="forecaster-name">                 <?php
      if ($nowTimestamp > $expdateTimestamp) {
        echo '';
      }
      elseif ($duration <= '120'){
        print '<span style=\'font-weight:bold; text-transform:uppercase\'>' . t('This advisory is valid for @duration hours', array('@duration' => $duration)) . '</span><br>';
      }
      ?>
      <?php print t('Issued by'); ?>
      <?php print render($display_name[0]['name_line']);?> - <?php print render($company[0]['title']);?>
    </div>
  </div>
  <!--BOTTOM LINE WITH DANGER ROSE-->
  <?php if (isset($content['field_bottom_line'][0]['#markup'])): ?>
    <?php if ($show_danger_rose  != 0): ?>
      <div id="avalanche-danger-row" class="advanced adv-bottom-line">
        <?php require_once(path_to_theme() . '/inc/advisory.inc'); ?>
        <?php $advisory = $variables['advisory']; ?>
        <?php if ($show_regions == 1):?>
          <h3 id="simple-avalanche-danger-title">
            <?php foreach($node->field_forecast_region['und'] as $tag) {
              $region = $tag['taxonomy_term']->name."<br>";
              print $region;
            }
            ?>
          </h3>
        <?php endif;?>
        <div class="advanced" id="problem-rose">
          <div id="overall-rose" class="row-sub-div">
            <?php print render(field_view_field('node', $node, 'field_overall_danger_rose', array('label' => 'hidden'))); ?>
          </div>
          <div id="bottom-line-adv" class="advisory-div-text">
            <span class="title"><?php print t('bottom line'); ?></span>
            <br /><br />
            <?php if (isset($content['field_bottom_line'][0]['#markup'])) { print render($content['field_bottom_line'][0]['#markup']); } ?>
            <p id="how-to-read-advisory">
              <a href="<?php print $url; ?>/how-to-read-avalanche-advisory"><?php print t('How to read the advisory'); ?>
              </a>
            </p>
          </div>
</div>
</div>
<?php endif?>
<!--BOTTOM LINE WITH MOUNTAIN-->
<?php if ($show_danger_rose  != 1): ?>
  <div id="bottom-line-container">
  <div id="avalanche-danger-row" class="darkbg simple">
    <?php if ($show_regions == 1):?>
      <h3 id="simple-avalanche-danger-title">
        <?php foreach($node->field_forecast_region['und'] as $tag) {
          $region = $tag['taxonomy_term']->name."<br>";
          print $region;
        }
        ?>
      </h3>
    <?php elseif ($show_regions == 0):?>
      <h3 id="simple-avalanche-danger-title"><?php print t('Bottom Line'); ?></h3>
    <?php endif;?>
    <a id="how-to-read-advisory-simple" href="<?php print $url; ?>/how-to-read-avalanche-advisory"><?php print t('How to read the advisory'); ?></a>
    <?php $a = $advisory['danger_rating']; ?>
  </div>
  <div id="bottom-line">
    <?php if (isset($content['field_bottom_line'][0]['#markup'])) { print render($content['field_bottom_line'][0]['#markup']); } ?>
  </div>
  </div>
  <div id="elevation-ratings-div" class="darkbg simple">
    <img id="mountain-icon" src="/<?php print path_to_theme(); ?>/img/advisory-mountain.png">
    <div id="upper-rating" class="rating-<?php print $a['upper']; ?>">
      <span>
        <h2><?php print $a['upper_label']; ?></h2>
        <p id="upper-rating-info" class="treeline-info">?</p>
        <?php print $upper_band; ?>
      </span>
      <img class="upper-dli dli" src="/<?php print path_to_theme() . '/img/dli/' . $a['upper'] . '.png'; ?>">
    </div>
    <div class="ta-container upper-container">
      <div class="ta-text">
        <?php
        $dr_upper = $a['upper'];
        $ta_upper = array('', t('Generally safe avalanche conditions. Watch for unstable snow on isolated terrain features.'), t('Heightened avalanche conditions on specific terrain features. Evaluate snow and terrain carefully; identify features of concern.'), t('Dangerous avalanche conditions. Careful snowpack evaluation, cautious route-finding and conservative decision-making essential.'), t('Very dangerous avalanche conditions. Travel in avalanche terrain not recommended.'), t('Avoid all avalanche terrain.'));
        $ta_text_upper = $ta_upper[$dr_upper];
        print $ta_text_upper; ?>
      </div>
    </div>
    <div id="mid-rating" class="rating-<?php print $a['mid']; ?>">
      <span>
        <h2><?php print $a['mid_label']; ?></h2>
        <p id="mid-rating-info" class="treeline-info">?</p>
        <?php print $mid_band; ?>
      </span>
      <img class="mid-dli dli" src="/<?php print path_to_theme() . '/img/dli/' . $a['mid'] . '.png'; ?>">
    </div>
    <div class="ta-container mid-container">
      <div class="ta-text">
        <?php
        $dr_mid = $a['mid'];
        $ta_mid = array('', t('Generally safe avalanche conditions. Watch for unstable snow on isolated terrain features.'), t('Heightened avalanche conditions on specific terrain features. Evaluate snow and terrain carefully; identify features of concern.'), t('Dangerous avalanche conditions. Careful snowpack evaluation, cautious route-finding and conservative decision-making essential.'), t('Very dangerous avalanche conditions. Travel in avalanche terrain not recommended.'), t('Avoid all avalanche terrain.'));
        $ta_text_mid = $ta_mid[$dr_mid];
        print $ta_text_mid; ?>
      </div>
    </div>
    <div id="lower-rating" class="rating-<?php print $a['lower']; ?>">
      <span>
        <h2><?php print $a['lower_label']; ?></h2>
        <p id="lower-rating-info" class="treeline-info">?</p>
        <?php print $lower_band; ?>
      </span>
      <img class="lower-dli dli" src="/<?php print path_to_theme() . '/img/dli/' . $a['lower'] . '.png'; ?>">
    </div>
    <div class="ta-container lower-container">
      <div class="ta-text">
        <?php
        $dr_lower = $a['lower'];
        $ta_lower = array('', t('Generally safe avalanche conditions. Watch for unstable snow on isolated terrain features.'), t('Heightened avalanche conditions on specific terrain features. Evaluate snow and terrain carefully; identify features of concern.'), t('Dangerous avalanche conditions. Careful snowpack evaluation, cautious route-finding and conservative decision-making essential.'), t('Very dangerous avalanche conditions. Travel in avalanche terrain not recommended.'), t('Avoid all avalanche terrain.'));
        $ta_text_lower = $ta_lower[$dr_lower];
        print $ta_text_lower; ?>
      </div>
    </div>

  </div>
  <?php if ($node->field_overalldanger['und'][0]['value']>0):?>
<div id="basic-danger-bar" class="darkbg simple">
      <ul id="danger-rating-bar-simple">
        <!--<li>
        <a href="http://www.avalanche.org/danger_card.php" target="_blank">
        <img src="/themes/responsive_sac/img/rating-icons/<?php print $node->field_overalldanger['und'][0]['value'];?>.png">
      </a>
    </li>
    <li>
  -->
  <div>
    <?php
    $dr = $node->field_overalldanger['und'][0]['value'];
    $ta = array('', t('Generally safe avalanche conditions. Watch for unstable snow on isolated terrain features.'), t('Heightened avalanche conditions on specific terrain features. Evaluate snow and terrain carefully; identify features of concern.'), t('Dangerous avalanche conditions. Careful snowpack evaluation, cautious route-finding and conservative decision-making essential.'), t('Very dangerous avalanche conditions. Travel in avalanche terrain not recommended.'), t('Avoid all avalanche terrain.'));
    $ta_text = $ta[$dr];
    print $ta_text; ?>
  </div>
</li>
</ul>
<!--<li>
<?php
$dr = $node->field_overalldanger['und'][0]['value'];
$lh = array('', t('Natural and human-triggered avalanches unlikely.'), t('Natural avalanches unlikely; human-triggered avalanches possible.'), t('Natural avalanches possible; human-triggered avalanches likely.'), t('Natural avalanches likely; human-triggered avalanches very likely.'), t('Natural and human-triggered avalanches certain.'));
$lh_text = $lh[$dr];
print $lh_text; ?>
</li>
<li>
<?php
$dr = $node->field_overalldanger['und'][0]['value'];
$sd = array('', t('Small avalanches in isolated areas or extreme terrain.'), t('Small avalanches in specific areas; or large avalanches in isolated areas.'), t('Small avalanches in many areas; or large avalanches in specific areas; or very large avalanches in isolated areas.'), t('Large avalanches in many areas; or very large avalanches in specific areas.'), t('Large to very large avalanches in many areas'));
$sd_text = $sd[$dr];
print $sd_text; ?>
</li>
</ul>
-->
</div>
<div id="danger-scale-bar-container">
  <ul id="danger-scale-bar">
    <a href="http://www.avalanche.org/danger_card.php" target="_blank">
      <li id="low-li-item" class="danger-scale-rating-item"><?php print t('1. Low'); ?></li>
      <li id="mod-li-item" class="danger-scale-rating-item"><?php print t('2. Moderate'); ?></li>
      <li id="cons-li-item" class="danger-scale-rating-item"><?php print t('3. Considerable'); ?></li>
      <li id="high-li-item" class="danger-scale-rating-item"><?php print t('4. High'); ?></li>
      <li id="extr-li-item" class="danger-scale-rating-item"><?php print t('5. Extreme'); ?></li>
    </a>
  </ul>
</div>
<?php endif?>
<?php endif; ?>
<?php endif; ?>

<!--AVALANCHE PROBLEMS -->
<!--Problem 1-->
<?php if (isset($content['field_type_1']['0']['#markup'])): ?>
  <div class="clearbg avalanche-problem-row">
    <span class="title"><?php print t('Avalanche Problem 1:'); ?> <?php print render($content['field_type_1']['0']['#markup']);?></span>
    <ul>
      <li id="problem-type" class="border-right">
        <div class="problem-char-label"><?php print t('Type'); ?>
          <span id="type-<?php print $node->field_type_1['und'][0]['value'];?>-info" class="info">?</span>
        </div>
        <div class="problem-char-wrapper problem-type">
          <a href="<?php print $url; ?>/avalanche-problems#<?php print $node->field_type_1['und'][0]['value'];?>" target="_blank">
            <img src="/themes/responsive_sac/img/api/<?php print $node->field_type_1['und'][0]['value'];?>.png">
          </a>
        </div>
      </li>
      <?php if ($display_rose_1 == "show"):?>
        <li id="problem-rose" class="border-right">
          <div class="problem-char-label"><?php print t('Aspect/Elevation'); ?>
            <span id="problem-rose-info" class="info">?</span>
          </div>
          <div class="problem-char-wrapper">
            <img id="rose" src=<?php print $node->field_rose_1['und'][0]['img_detailed_rose'];?>>
          </div>
        </li>
      <?php endif;?>
      <?php if (isset($node->field_likelihood_1['und'][0]['value'])): ?>
        <li class="border-right">
          <div class="problem-char-label"><?php print t('Likelihood'); ?>
            <span id="likelihood-info" class="info">?</span>
          </div>
          <div class="problem-char-wrapper">
            <div class="likelihood-size-label top-label <?php if ($node->field_likelihood_1['und'][0]['value'] == 5) {print "label-bold";};?>"><?php print t('Certain'); ?></div>
            <div class="likelihood-size-label very-likely-label <?php if ($node->field_likelihood_1['und'][0]['value'] == 4) {print "label-bold";};?>"><?php print t('Very Likely'); ?></div>
            <div class="likelihood-size-label likely-label <?php if ($node->field_likelihood_1['und'][0]['value'] == 3) {print "label-bold";};?>"><?php print t('Likely'); ?></div>
            <div class="likelihood-size-label possible-label <?php if ($node->field_likelihood_1['und'][0]['value'] == 2) {print "label-bold";};?>"><?php print t('Possible'); ?></div>
            <img src="/themes/responsive_sac/img/slider/likelihood.png">
            <div class="middle-data-overlay likelihood-overlay-<?php print $node->field_likelihood_1['und'][0]['value'];?>"></div>
            <div class="likelihood-size-label bottom-label <?php if ($node->field_likelihood_1['und'][0]['value'] == 1) {print "label-bold";};?>"><?php print t('Unlikely'); ?></div>
          </div>
        </li>
      <?php endif; ?>
      <?php if (isset($node->field_size_1['und'][0]['value'])): ?>
        <li class="border-right">
          <div class="problem-char-label"><?php print t('Size'); ?>
            <span id="size-info" class="info">?</span>
          </div>
          <div class="problem-char-wrapper">
            <div class="likelihood-size-label top-label <?php if (($size11 == 4) || ($size12 == 5)  || ($size12 == 4)) {print "label-bold";}?>"><?php print t('Historic'); ?></div>
            <div class="likelihood-size-label very-large-size-label <?php if (($size11 == 3) || ($size12 == 3)) {print "label-bold";}?>"><?php print t('Very Large'); ?></div>
            <div class="likelihood-size-label large-size-label  <?php if (($size11 == 2) || ($size12 == 2)) {print "label-bold";}?>"><?php print t('Large'); ?></div>
            <div class="likelihood-size-label bottom-label  <?php if (($size11 == 1) || ($size12 == 1)) {print "label-bold";}?>"><?php print t('Small'); ?></div>
            <img src="/themes/responsive_sac/img/slider/size.png">
            <div class="size-overlay-<?php print $size11.$size12;?>"></div>
          </div>
        </li>
      <?php endif; ?>
      <!-- TREND NOT USED
      <?php if (isset($content['field_trend_1'][0]['#markup'])): ?>
      <li id="trend">
      <div class="problem-char-label"><?php print t('Trend'); ?>
      <span id="trend-info" class="info">?</span>
    </div>
    <div class="problem-char-wrapper">
    <div class="middle-data-graph"><img src="/themes/responsive_sac/img/trend/<?php print $node->field_trend_1['und'][0]['value'];?>.png"></div>
    <div class="middle-data-graph-text"><?php print render($content['field_trend_1'][0]['#markup']);?></div>
  </div>
</li>
<?php endif; ?>
-->
</ul>
<?php if (isset($content['field_description_1'][0]['#markup'])): ?>
  <div id="problem-description"><?php print render($content['field_description_1'][0]['#markup']);?></div>
<?php endif; ?>
</div>
<?php endif; ?>
<!--End problem 1 -->
<!--Problem 2-->
<?php if (isset($content['field_type_2']['0']['#markup'])): ?>
  <div class="clearbg avalanche-problem-row">
    <span class="title"><?php print t('Avalanche Problem 2:'); ?> <?php print render($content['field_type_2']['0']['#markup']);?></span>
    <ul>
      <li id="problem-type" class="border-right">
        <div class="problem-char-label"><?php print t('Type'); ?>
          <span id="type-<?php print $node->field_type_2['und'][0]['value'];?>-info" class="info">?</span>
        </div>
        <div class="problem-char-wrapper problem-type">
          <a href="<?php print $url; ?>/avalanche-problems#<?php print $node->field_type_2['und'][0]['value'];?>" target="_blank">
            <img src="/themes/responsive_sac/img/api/<?php print $node->field_type_2['und'][0]['value'];?>.png">
          </a>
          <!--<div class="char-symbol-label"><?php print render($content['field_type_2']['0']['#markup']);?></div>-->
        </div>
      </li>
      <?php if ($display_rose_2 == "show"):?>
        <li id="problem-rose" class="border-right">
          <div class="problem-char-label"><?php print t('Aspect/Elevation'); ?>
            <span id="problem-rose-info" class="info">?</span>
          </div>
          <div class="problem-char-wrapper">
            <img id="rose" src=<?php print $node->field_rose_2['und'][0]['img_detailed_rose'];?>>
          </div>
        </li>
      <?php endif; ?>
      <?php if (isset($node->field_likelihood_2['und'][0]['value'])): ?>
        <li class="border-right">
          <div class="problem-char-label"><?php print t('Likelihood'); ?>
            <span id="likelihood-info" class="info">?</span>
          </div>
          <div class="problem-char-wrapper">
            <div class="likelihood-size-label top-label <?php if ($node->field_likelihood_2['und'][0]['value'] == 5) {print "label-bold";};?>"><?php print t('Certain'); ?></div>
            <div class="likelihood-size-label very-likely-label <?php if ($node->field_likelihood_2['und'][0]['value'] == 4) {print "label-bold";};?>"><?php print t('Very Likely'); ?></div>
            <div class="likelihood-size-label likely-label <?php if ($node->field_likelihood_2['und'][0]['value'] == 3) {print "label-bold";};?>"><?php print t('Likely'); ?></div>
            <div class="likelihood-size-label possible-label <?php if ($node->field_likelihood_2['und'][0]['value'] == 2) {print "label-bold";};?>"><?php print t('Possible'); ?></div>
            <img src="/themes/responsive_sac/img/slider/likelihood.png">
            <div class="middle-data-overlay likelihood-overlay-<?php print $node->field_likelihood_2['und'][0]['value'];?>"></div>
            <div class="likelihood-size-label bottom-label <?php if ($node->field_likelihood_2['und'][0]['value'] == 1) {print "label-bold";};?>"><?php print t('Unlikely'); ?></div>
          </div>
        </li>
      <?php endif; ?>
      <?php if (isset($node->field_size_2['und'][0]['value'])): ?>
        <li class="border-right">
          <div class="problem-char-label"><?php print t('Size'); ?>
            <span id="size-info" class="info">?</span>
          </div>
          <div class="problem-char-wrapper">
            <div class="likelihood-size-label top-label <?php if (($size21 == 4) || ($size22 == 5)  || ($size22 == 4)) {print "label-bold";}?>"><?php print t('Historic'); ?></div>
            <div class="likelihood-size-label very-large-size-label <?php if (($size21 == 3) || ($size22 == 3)) {print "label-bold";}?>"><?php print t('Very Large'); ?></div>
            <div class="likelihood-size-label large-size-label  <?php if (($size21 == 2) || ($size22 == 2)) {print "label-bold";}?>"><?php print t('Large'); ?></div>
            <div class="likelihood-size-label bottom-label  <?php if (($size21 == 1) || ($size22 == 1)) {print "label-bold";}?>"><?php print t('Small'); ?></div>
            <img src="/themes/responsive_sac/img/slider/size.png">
            <div class="size-overlay-<?php print $size21.$size22;?>"></div>
          </div>
        </li>
      <?php endif; ?>
      <!-- TREND NOT USED
      <?php if (isset($content['field_trend_2'][0]['#markup'])): ?>
      <li id="trend">
      <div class="problem-char-label"><?php print t('Trend'); ?>
      <span id="trend-info" class="info">?</span>
    </div>
    <div class="problem-char-wrapper">
    <div class="middle-data-graph"><img src="/themes/responsive_sac/img/trend/<?php print $node->field_trend_2['und'][0]['value'];?>.png"></div>
    <div class="middle-data-graph-text"><?php print render($content['field_trend_2'][0]['#markup']);?></div>
  </div>
</li>
<?php endif; ?>
-->
</ul>
<?php if (isset($content['field_description_2'][0]['#markup'])): ?>
  <div id="problem-description"><?php print render($content['field_description_2'][0]['#markup']);?></div>
<?php endif; ?>
</div>
<?php endif; ?>
<!--End problem 2 -->
<!--Problem 3-->
<?php if (isset($content['field_type_3']['0']['#markup'])): ?>
  <div class="clearbg avalanche-problem-row">
    <span class="title"><?php print t('Avalanche Problem 3:'); ?> <?php print render($content['field_type_3']['0']['#markup']);?></span>
    <ul>
      <li id="problem-type" class="border-right">
        <div class="problem-char-label"><?php print t('Type'); ?>
          <span id="type-<?php print $node->field_type_3['und'][0]['value'];?>-info" class="info">?</span>
        </div>
        <div class="problem-char-wrapper problem-type">
          <a href="<?php print $url; ?>/avalanche-problems#<?php print $node->field_type_3['und'][0]['value'];?>" target="_blank">
            <img src="/themes/responsive_sac/img/api/<?php print $node->field_type_3['und'][0]['value'];?>.png">
          </a>
        </div>
      </li>
      <?php if ($display_rose_3 == "show"):?>
        <li id="problem-rose" class="border-right">
          <div class="problem-char-label"><?php print t('Aspect/Elevation'); ?>
            <span id="problem-rose-info" class="info">?</span>
          </div>
          <div class="problem-char-wrapper">
            <img id="rose" src=<?php print $node->field_rose_3['und'][0]['img_detailed_rose'];?>>
          </div>
        </li>
      <?php endif; ?>
      <?php if (isset($node->field_likelihood_3['und'][0]['value'])): ?>
        <li class="border-right">
          <div class="problem-char-label"><?php print t('Likelihood'); ?>
            <span id="likelihood-info" class="info">?</span>
          </div>
          <div class="problem-char-wrapper">
            <div class="likelihood-size-label top-label <?php if ($node->field_likelihood_3['und'][0]['value'] == 5) {print "label-bold";};?>"><?php print t('Certain'); ?></div>
            <div class="likelihood-size-label very-likely-label <?php if ($node->field_likelihood_3['und'][0]['value'] == 4) {print "label-bold";};?>"><?php print t('Very Likely'); ?></div>
            <div class="likelihood-size-label likely-label <?php if ($node->field_likelihood_3['und'][0]['value'] == 3) {print "label-bold";};?>"><?php print t('Likely'); ?></div>
            <div class="likelihood-size-label possible-label <?php if ($node->field_likelihood_3['und'][0]['value'] == 2) {print "label-bold";};?>"><?php print t('Possible'); ?></div>
            <img src="/themes/responsive_sac/img/slider/likelihood.png">
            <div class="middle-data-overlay likelihood-overlay-<?php print $node->field_likelihood_3['und'][0]['value'];?>"></div>
            <div class="likelihood-size-label bottom-label <?php if ($node->field_likelihood_3['und'][0]['value'] == 1) {print "label-bold";};?>"><?php print t('Unlikely'); ?></div>
          </div>
        </li>
      <?php endif; ?>
      <?php if (isset($node->field_size_3['und'][0]['value'])): ?>
        <li class="border-right">
          <div class="problem-char-label"><?php print t('Size'); ?>
            <span id="size-info" class="info">?</span>
          </div>
          <div class="problem-char-wrapper">
            <div class="likelihood-size-label top-label <?php if (($size31 == 4) || ($size32 == 5) || ($size32 == 4)) {print "label-bold";}?>"><?php print t('Historic'); ?></div>
            <div class="likelihood-size-label very-large-size-label <?php if (($size31 == 3) || ($size32 == 3)) {print "label-bold";}?>"><?php print t('Very Large'); ?></div>
            <div class="likelihood-size-label large-size-label  <?php if (($size31 == 2) || ($size32 == 2)) {print "label-bold";}?>"><?php print t('Large'); ?></div>
            <div class="likelihood-size-label bottom-label  <?php if (($size31 == 1) || ($size32 == 1)) {print "label-bold";}?>"><?php print t('Small'); ?></div>
            <img src="/themes/responsive_sac/img/slider/size.png">
            <div class="size-overlay-<?php print $size31.$size32;?>"></div>
          </div>
        </li>
      <?php endif; ?>
      <!-- TREND NOT USED
      <?php if (isset($content['field_trend_3'][0]['#markup'])): ?>
      <li id="trend">
      <div class="problem-char-label"><?php print t('Trend'); ?>
      <span id="trend-info" class="info">?</span>
    </div>
    <div class="problem-char-wrapper">
    <div class="middle-data-graph"><img src="/themes/responsive_sac/img/trend/<?php print $node->field_trend_3['und'][0]['value'];?>.png"></div>
    <div class="middle-data-graph-text"><?php print render($content['field_trend_3'][0]['#markup']);?></div>
  </div>
</li>
<?php endif; ?>
-->
</ul>
<?php if (isset($content['field_description_3'][0]['#markup'])): ?>
  <div id="problem-description"><?php print render($content['field_description_3'][0]['#markup']);?></div>
<?php endif; ?>
</div>
<?php endif; ?>
<!--End problem 3 -->
<!--AVALANCHE TEXT DISCUSSION-->
<?php if ($node->field_text_discussion['und'][0]['value'] != ''): ?>
  <div class="clearbg avalanche-problem-row">
    <span class="title"><?php print t('advisory discussion'); ?></span>
    <div class="advisory-div">
      <div class="disc-advisory-div-text">
        <?php print render($content['field_text_discussion'][0]['#markup']); ?>
      </div>
    </div>
  </div>
<?php endif; ?>
<!--RECENT OBS WITH GALLERY-->
<?php if ($node->field_recent_activity['und'][0]['value'] != ''): ?>
  <div class="clearbg avalanche-problem-row">
    <span class="title">
      <?php if (!empty($obs_page)):?>
        <a style="color:#ddd;" href="<?php print $url.$obs_page; ?>">
        <?php endif;?>
        <?php print t('recent observations'); ?>
        <?php if (!empty($obs_page)):?>
        </a>
      <?php endif;?>
    </span>
    <div class="advisory-div">
      <div class="advisory-div-icon">
        <?php if (!empty($submit_snowpack_obs_page)):?>
          <div class="submit-obs-button">
            <a href="<?php print $url.$submit_snowpack_obs_page; ?>"><?php print t('Submit Observations'); ?></a>
          </div>
        <?php endif;?>
        <?php if (!empty($submit_avalanche_obs_page)):?>
          <div class="submit-obs-button">
            <a href="<?php print $url.$submit_avalanche_obs_page; ?>"><?php print t('Submit Avalanche Observations'); ?></a>
          </div>
        <?php endif;?>
      </div>
      <div class="advisory-div-text">
        <?php print render($content['field_recent_activity'][0]['#markup']); ?>
      </div>
      <?php if ($nowTimestamp <= $expdateTimestamp):?>
        <div id="recent-obs" class="">
          <?php echo views_embed_view('media_gallery', 'block_1'); ?>
        </div>
      <?php endif; ?>
    </div>
  </div>
<?php endif; ?>

<div class="clearbg avalanche-problem-row">
  <span class="title"><?php print t('Weather and Current Conditions'); ?></span>
  <!--MOUNTAIN WEATHER-->
  <?php if ($node->field_mountain_weather['und'][0]['value'] != ''): ?>
    <div id="wx-summary">
      <span class="wx-sub-title"><?php print t('weather summary'); ?></span>
      <div class="advisory-div">
        <div class="disc-advisory-div-text">
          <?php print render($content['field_mountain_weather'][0]['#markup']); ?>
        </div>
      </div>
    </div>
  <?php endif; ?>
  <!--WEATHER OBS - CURRENT CONDITIONS-->
  <?php if (isset($content['field_temp8700']['#items']['0']['value'])): ?>
    <div>
      <span class="wx-sub-title"><?php print $current_wx_desc;?></span>
    </div>
    <div class="advisory-div">
      <div class="advisory-div-icon">
        <?php if (!empty($wx_map_page)):?>
          <div class="submit-obs-button">
            <a href="<?php print $url.$wx_map_page; ?>"><?php print t('Weather Station Map'); ?></a>
          </div>
        <?php endif;?>
        <?php if (!empty($wx_table_page)):?>
          <div class="submit-obs-button">
            <a href="<?php print $url.$wx_table_page; ?>"><?php print t('Weather Station Table'); ?></a>
          </div>
        <?php endif;?>
      </div>
      <table id="Wx-obs" class="row-sub-table">
        <tbody>
          <tr>
            <td ><?php print t('0600 temperature:'); ?></td>
            <td ><?php print render($content['field_temp8700']['#items']['0']['value']);?> <?php print t('deg. F.'); ?></td>
          </tr>
          <tr>
            <td ><?php print t('Max. temperature in the last 24 hours:'); ?></td>
            <td ><?php print render($content['field_hr24maxtemp']['#items']['0']['value']);?> <?php print t('deg. F.'); ?></td>
          </tr>
          <tr>
            <td ><?php print t('Average wind direction during the last 24 hours:'); ?></td>
            <td ><?php print render($content['field_hr24winddir']['#items']['0']['value']);?></td>
          </tr>
          <tr>
            <td ><?php print t('Average wind speed during the last 24 hours:'); ?></td>
            <td ><?php print render($content['field_hr24windspeed']['#items']['0']['value']);?> <?php print t('mph'); ?></td>
          </tr>
          <tr>
            <td ><?php print t('Maximum wind gust in the last 24 hours:'); ?></td>
            <td ><?php print render($content['field_hr24maxgust']['#items']['0']['value']);?> <?php print t('mph'); ?></td>
          </tr>
          <tr>
            <td ><?php print t('New snowfall in the last 24 hours:'); ?></td>
            <td ><?php print render($content['field_hr24snowfall']['#items']['0']['value']);?> <?php print t('inches'); ?></td>
          </tr>
          <tr>
            <td ><?php print t('Total snow depth:'); ?></td>
            <td ><?php print render($content['field_totalsnowdepth']['#items']['0']['value']);?> <?php print t('inches'); ?></td>
          </tr>
        </tbody>
      </table>
    </div>
  <?php endif;?>
  <!--TWO DAY WEATHER FORECAST-->
  <?php if (isset($content['field_today7to8weather']['#items']['0']['value'])): ?>
    <div>
      <span class="wx-sub-title"><?php print t('Two-Day Mountain Weather Forecast Produced in partnership with the'); ?> <a href="<?php print $nws_url;?>" target="_blank"><?php print $nws_name;?></a></span>
      <!-- Weather Table Responsive -->

      <article>

        <ul id="wxfx">
          <li class="bg-dark wxbuttons active">
            <button>
            <?php
                if (date('Y-m-d') == date('Y-m-d', $node->created)) {
                  print t('Today');
                }
                else {
                  echo date("l", $node->created);
                }
                ?>
            </button>
          </li>
          <li class="bg-dark wxbuttons">
            <button>
            <?php
                if (date('Y-m-d') == date('Y-m-d', $node->created)) {
                  print t('Tonight');
                }
                else {
                  echo date("l", $node->created) . " " . t("Night");
                }
                ?>
           </button>
          </li>
          <li class="bg-dark wxbuttons">
            <button>
                <?php
                $tomorrow  = strtotime('+1 day', $node->created);
                echo date("l", $tomorrow);
                ?>
            </button>
          </li>
        </ul>
        <div class="wxfxelevations"><span class="txt-l"><?php print $wx_low;?></span></div>
        <table id="wxforecast" class="row-sub-table">
          <thead>
            <tr class="wxtoprow">
              <th class="hide wxfirstcol"></th>
              <th class="default">
                <?php
                if (date('Y-m-d') == date('Y-m-d', $node->created)) {
                  print t('Today');
                }
                else {
                  echo date("l", $node->created);
                }
                ?>
              </th>
              <th class="bg-light">
                <?php
                if (date('Y-m-d') == date('Y-m-d', $node->created)) {
                  print t('Tonight');
                }
                else {
                  echo date("l", $node->created) . " " . t("Night");
                }
                ?>
              </th>
              <th>
                <?php
                $tomorrow  = strtotime('+1 day', $node->created);
                echo date("l", $tomorrow);
                ?>
              </th>
            </tr>
          </thead>
          <tbody>
            <tr class="wxmidrow">
              <td class="wxfirstcol"><?php print t('Weather:'); ?></td>
              <td class="default"><span class="txt-l"><?php print render($content['field_today7to8weather']['#items']['0']['value']) ?></span></td>
              <td><span class="txt-l"><?php print render($content['field_tonight7to8weather']['#items']['0']['value']) ?></span></td>
              <td><span class="txt-l"><?php print render($content['field_tomorrow7to8weather']['#items']['0']['value']) ?></span></td>
            </tr>
            <tr class="wxmidrow">
              <td class="wxfirstcol"><?php print t('Temperatures:'); ?></td>
              <td class="default"><span class="txt-1"><?php print render($content['field_today7to8temp']['#items']['0']['value']) ?> <?php print t('deg. F.'); ?>
              </span></td>
              <td><span class="txt-1"><?php print render($content['field_tonight7to8temp']['#items']['0']['value']) ?> <?php print t('deg. F.'); ?>
              </span></td>
              <td><span class="txt-1"><?php print render($content['field_tomorrow7to8temp']['#items']['0']['value']) ?> <?php print t('deg. F.'); ?>
              </span></td>
            </tr>
            <tr class="wxmidrow">
              <td class="wxfirstcol"><?php print t('Wind Direction:'); ?></td>
              <td class="default"><span class="txt-1"><?php print render($content['field_today7to8winddirection']['#items']['0']['value']) ?></span></td>
              <td><span class="txt-1"><?php print render($content['field_tonight7to8winddirection']['#items']['0']['value']) ?></span></td>
              <td><span class="txt-1"><?php print render($content['field_tomorrow7to8winddirection']['#items']['0']['value']) ?></span></td>
            </tr>
            <tr class="wxmidrow">
              <td class="wxfirstcol"><?php print t('Wind Speed:'); ?></td>
              <td class="default"><span class="txt-1"><?php print render($content['field_today7to8windspeed']['#items']['0']['value']) ?></span></td>
              <td><span class="txt-1"><?php print render($content['field_tonight7to8windspeed']['#items']['0']['value']) ?></span></td>
              <td><span class="txt-1"><?php print render($content['field_tomorrow7to8windspeed']['#items']['0']['value']) ?></span></td>
            </tr>
            <tr class="wxbottomrow">
              <td class="wxfirstcol"><?php print t('Expected snowfall:'); ?></td>
              <td class="default"><span class="txt-1"><?php print render($content['field_today7to8snow']['#items']['0']['value']) ?> <?php print t('in.'); ?></span></td>
              <td><span class="txt-1"><?php print render($content['field_tonight7to8snow']['#items']['0']['value']) ?> <?php print t('in.'); ?></span></td>
              <td><span class="txt-1"><?php print render($content['field_tomorrow7to8snow']['#items']['0']['value']) ?> <?php print t('in.'); ?></span></td>
            </tr>
          </tbody>
        </table>
<div class="wxfxelevations"><span class="txt-l"><?php print $wx_high;?></span></div>
        <table id="wxforecast" class="row-sub-table">
          <thead>
            <tr class="wxtoprow">
              <th class="hide wxfirstcol"></th>
                            <th class="default">
                <?php
                if (date('Y-m-d') == date('Y-m-d', $node->created)) {
                  print t('Today');
                }
                else {
                  echo date("l", $node->created);
                }
                ?>
              </th>
              <th class="bg-light">
                <?php
                if (date('Y-m-d') == date('Y-m-d', $node->created)) {
                  print t('Tonight');
                }
                else {
                  echo date("l", $node->created) . " " . t("Night");
                }
                ?>
              </th>
              <th>
                <?php
                $tomorrow  = strtotime('+1 day', $node->created);
                echo date("l", $tomorrow);
                ?>
              </th>
            </tr>
          </thead>
          <tbody>
            <tr class="wxmidrow">
              <td class="wxfirstcol"><?php print t('Weather:'); ?></td>
              <td class="default"><span class="txt-l"><?php print render($content['field_today8to9weather']['#items']['0']['value']) ?></span></td>
              <td><span class="txt-l"><?php print render($content['field_tonight8to9weather']['#items']['0']['value']) ?></span></td>
              <td><span class="txt-l"><?php print render($content['field_tomorrow8to9weather']['#items']['0']['value']) ?></span></td>
            </tr>
            <tr class="wxmidrow">
              <td class="wxfirstcol"><?php print t('Temperatures:'); ?></td>
              <td class="default"><span class="txt-1"><?php print render($content['field_today8to9temp']['#items']['0']['value']) ?> <?php print t('deg. F.'); ?>
              </span></td>
              <td><span class="txt-1"><?php print render($content['field_tonight8to9temp']['#items']['0']['value']) ?> <?php print t('deg. F.'); ?>
              </span></td>
              <td><span class="txt-1"><?php print render($content['field_tomorrow8to9temp']['#items']['0']['value']) ?> <?php print t('deg. F.'); ?>
              </span></td>
            </tr>
            <tr class="wxmidrow">
              <td class="wxfirstcol"><?php print t('Wind Direction:'); ?></td>
              <td class="default"><span class="txt-1"><?php print render($content['field_today8to9winddirection']['#items']['0']['value']) ?></span></td>
              <td><span class="txt-1"><?php print render($content['field_tonight8to9winddirection']['#items']['0']['value']) ?></span></td>
              <td><span class="txt-1"><?php print render($content['field_tomorrow8to9winddirection']['#items']['0']['value']) ?></span></td>
            </tr>
            <tr class="wxmidrow">
              <td class="wxfirstcol"><?php print t('Wind Speed:'); ?></td>
              <td class="default"><span class="txt-1"><?php print render($content['field_today8to9windspeed']['#items']['0']['value']) ?></span></td>
              <td><span class="txt-1"><?php print render($content['field_tonight8to9windspeed']['#items']['0']['value']) ?></span></td>
              <td><span class="txt-1"><?php print render($content['field_tomorrow8to9windspeed']['#items']['0']['value']) ?></span></td>
            </tr>
            <tr class="wxbottomrow">
              <td class="wxfirstcol"><?php print t('Expected snowfall:'); ?></td>
              <td class="default"><span class="txt-1"><?php print render($content['field_today8to9snow']['#items']['0']['value']) ?> <?php print t('in.'); ?></span></td>
              <td><span class="txt-1"><?php print render($content['field_tonight8to9snow']['#items']['0']['value']) ?> <?php print t('in.'); ?></span></td>
              <td><span class="txt-1"><?php print render($content['field_tomorrow8to9snow']['#items']['0']['value']) ?> <?php print t('in.'); ?></span></td>
            </tr>
          </tbody>
        </table>
      </article>
    </div>
  </div>
<?php endif; ?>
<!-- end weather-->
<!--DISCLAIMER-->
<div class="clearbg avalanche-problem-row">
  <span class="title"><?php print t('Disclaimer'); ?></span>
  <div class="advisory-div">
    <div class="disc-advisory-div-text">
      <?php print_r($node->field_disclaimer['und'][0]['value']); ?>
    </div>
  </div>
</div>
<!-- end disclaimer-->
<?php // print render($content); ?>
</div>
<div class="social-buttons">
  <iframe src="https://www.facebook.com/plugins/like.php?href=<?php $curr_url = check_plain("https://" .$_SERVER['HTTP_HOST'] .$node_url); echo $curr_url; ?>&amp;layout=button_count&amp;show_faces=false&amp;width=200&amp;action=like&amp;font=verdana&amp;colorscheme=light&amp;height=21" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:130px; height:21px;" allowTransparency="true"></iframe>
  <a class="twitter-share-button" href="https://twitter.com/share" data-lang="en" data-size="small" data-count="none">Tweet</a><script type="text/javascript">// <![CDATA[
    !function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0];if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src="https://platform.twitter.com/widgets.js";fjs.parentNode.insertBefore(js,fjs);}}(document,"script","twitter-wjs");
    // ]]>
    </script>
  </div>
  <?php
  // Remove the "Add new comment" link on the teaser page or if the comment
  // form is being displayed on the same page.
  if ($teaser || !empty($content['comments']['comment_form'])) {
    unset($content['links']['comment']['#links']['comment-add']);
  }
  // Only display the wrapper div if there are links.
  $links = render($content['links']);
  if ($links):
    ?>
    <div class="link-wrapper">
      <?php print $links; ?>
    </div>
  <?php endif; ?>
  <?php
  /* print '<pre>';
  //var_dump(get_defined_vars());
  print_r($node->field_rose_1);
  print_r($node->field_size_2);
  print_r($node->field_size_3);
  print '</pre>';
  */
  ?>
</div>
<?php
//Enable below to show all Array Variables of Form

//print '<pre>';
//print render($content['field_type_1']['0']['#markup']);
//print print_r($content);
//print '</pre>';

//print '<pre>';
//print render($content['field_type_1']['0']['#markup']);
//print print_r($node);
//print '</pre>';
?>
<!-- Not currently used
<?php if (isset($advisory['avalanche_warning'])): ?>
<div id="warning-div" class="advisory-div">
<table class="row-sub-table">
<tr>
<td class="first"><img src="/themes/responsive_sac/img/row/heading-awarning.jpg"></td>
<td><span class="uppercase"><?php print t('avalanche warning'); ?></span><br /><?php print $advisory['avalanche_warning']; ?></td>
</table>
</div>
<?php endif; ?>
-->

<!--not currently used
<?php if (isset($advisory['avalanche_sab'])): ?>
<div id="sab-div" class="advisory-div">
<table class="row-sub-table">
<tr>
<td class="first"><img src="/themes/responsive_sac/img/row/heading-asab.jpg"></td>
<td><span class="uppercase"><?php print t('special avalanche bulletin'); ?></span><br /><?php print $advisory['avalanche_sab']; ?></td>
</table>
</div>
<?php endif; ?>
-->
<?php if ($show_danger_rose  == 2): ?>
<script type="text/javascript">
   // http://www.quirksmode.org/js/cookies.html
   function readCookie(name) {
     var nameEQ = name + "=";
     var ca = document.cookie.split(';');
     for(var i=0;i < ca.length;i++) {
       var c = ca[i];
       while (c.charAt(0)==' ') c = c.substring(1,c.length);
       if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length,c.length);
     }
     return null;
   }

   function setCookie(val) {
     document.cookie = 'avyviewpref' + '=' + val + ';expires=Wed, 1 May 2013 00:00:00 GMT';
   }

   function initCookie() {
     var c = readCookie('avyviewpref');
     if (c == null) { setCookie("#simple"); }
   }

   function toggleCookie() {
        initCookie();
        var c = readCookie('avyviewpref');
        if (c == '#advanced') { setCookie('#simple'); }
        else { setCookie('#advanced'); }
      }

      (function($) {
        initCookie();
        if (window.location.hash == '') {
          window.location.hash = readCookie('avyviewpref');
        }
        if (window.location.hash == '#advanced') {
          $('div#avalanche-danger-row.advanced').show();
          $('div#avalanche-danger-row.simple').hide();
          $('div#elevation-ratings-div.simple').hide();
          $('div#bottom-line-container').hide();
          $('a#standard-link').show();
          $('div#basic-danger-bar').show();
          setCookie('#advanced');
        } else {
          $('div#avalanche-danger-row.advanced').hide();
          $('div#avalanche-danger-row.simple').show();
          $('div#elevation-ratings-div.simple').show();
          $('div#bottom-line-container').show();
          $('a#advanced-link').show();
          $('div#basic-danger-bar').hide();
          setCookie('#simple');
        }
        $("a.toggle-advisory").click(function() {
          toggleCookie();
          $("a.toggle-advisory").show();
          $(this).hide();
          $('div#avalanche-danger-row.simple').toggle();
          $('div#elevation-ratings-div.simple').toggle();
          $('div#avalanche-danger-row.advanced').toggle();
          $('div#bottom-line-container').toggle();
          $('div#basic-danger-bar').toggle();
        });
      })(jQuery);
   </script>
   <?php endif; ?>
