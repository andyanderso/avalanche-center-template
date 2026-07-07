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
    *   - node-sticky: Nodes ordered above other non-sticky nodes in teaser
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
    * - $sticky: Flags for sticky post setting.
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
<?php
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
         $now1->setTimestamp($now);
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
   ?>
<div id="node-<?php print $node->nid; ?>" class="<?php print implode(' ', $classes); ?> clearfix"<?php print backdrop_attributes($attributes); ?>>
   <div class="content clearfix"<?php print backdrop_attributes($content_attributes); ?>>
      <!--PUBLISHED DATE AND FORECASTER NAME-->
      <div id="subtitle-date-row" class="darkbg">
         <div id="advisory-published-date" class="advisory-date">
            <?php
               if ($duration >= '120') {
                             echo t('Snowpack Summary published on @date', array('@date' => $forecastdate));
                             }
                             elseif ($nowTimestamp <= $expdateTimestamp) {
                             echo t('Snowpack Summary published on @date', array('@date' => $forecastdate)) . '</br><span id="timeToExp">' . t('This snowpack summary expires in') . ' ';
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
                             echo '<span style="background:red">' . t('THIS SNOWPACK SUMMARY EXPIRED ON @date', array('@date' => $expdate)) . '</span></br>' . t('Snowpack Summary published on @date', array('@date' => $forecastdate));
                     }
                 ?>
         </div>
         <div id="forecaster-name">                 <?php
            if ($nowTimestamp > $expdateTimestamp) {
             echo '';
            }
            elseif ($duration <= '120'){
            print '<span style=\'font-weight:bold; text-transform:uppercase\'>' . t('This snowpack summary is valid for @duration hours', array('@duration' => $duration)) . '</span><br>';
            }
            ?>
            <?php print t('Issued by'); ?>
            <?php print render($display_name[0]['name_line']);?> - <?php print render($company[0]['title']);?>
         </div>
      </div>
      <!--BOTTOM LINE-->
      <?php if (isset($content['field_bottom_line'][0]['#markup'])): ?>
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
         <?php endif;?>
         <!-- How to read the summary link once a page is built
            <a id="how-to-read-advisory-simple" href="<?php print $url; ?>/how-to-read-snowpack-summary"><?php print t('How to read the snowpack summary'); ?></a>
            -->
      </div>
      </br>
      <span class="title"><?php print t('bottom line:'); ?></span>
      <div id="bottom-line-adv" class="snowpack-summary-div-text">
         <?php if (isset($content['field_bottom_line'][0]['#markup'])) { print render($content['field_bottom_line'][0]['#markup']); } ?>
      </div>
      <?php endif; ?>
      <!--AVALANCHE PROBLEMS -->
      <!--Problem 1-->
      <?php if (isset($node->field_type_1['und'][0]['value'])): ?>
      <div class="clearbg avalanche-problem-row">
         <span class="title"><?php print t('Avalanche Character 1:'); ?> <?php print render($content['field_type_1']['0']['#markup']);?></span>
         <div class="snowpack-summary">
            <div id="problem-type" class="border-right">
               <div class="problem-char-label">
               </div>
               <div class="problem-char-wrapper problem-type">
                  <a href="<?php print $url; ?>/avalanche-problems#<?php print $node->field_type_1['und'][0]['value'];?>" target="_blank">
                  <img src="/themes/responsive_sac/img/api/<?php print $node->field_type_1['und'][0]['value'];?>.png">
                  </a>
               </div>
            </div>
            <div id="problem-canned-text" class="border-right">
               <div class="problem-char-label">
               </div>
               <div class="problem-char-wrapper">
                  <div class="problem-text"><?php $ac = $node->field_type_1['und'][0]['value'];
                     $ct = array(
                       '',
                       t('Storm Slab avalanches release naturally during snow storms and can be triggered for a few days after a storm. They often release at or below the trigger point. They exist throughout the terrain. Avoid them by waiting for the storm snow to stabilize.'),
                       t('Deep Slab avalanches are destructive and deadly events that can release months after the weak layer was buried. They are scarce compared to Storm or Wind Slab avalanches. Their cycles include fewer avalanches and occur over a larger region. You can triggered them from well down in the avalanche path, and after dozens of tracks have crossed the slope. Avoid the terrain identified in the forecast and give yourself a wide safety buffer to address the uncertainty.'),
                       t('Wind Slab avalanches release naturally during wind events and can be triggered for up to a week after a wind event. They form in lee and cross-loaded terrain features. Avoid them by sticking to wind sheltered or wind scoured areas.'),
                       t('Wet Slab avalanches occur when there is liquid water in the snowpack, and can release during the first few days of a warming period. Travel early in the day and avoid avalanche terrain when you see pinwheels, roller balls, loose wet avalanches, or during rain-on-snow events.'),
                       t('Persistent Slab avalanches can be triggered days to weeks after the last storm. They often propagate across and beyond terrain features that would otherwise confine Wind and Storm Slab avalanches. In some cases they can be triggered remotely, from low-angle terrain or adjacent slopes. Give yourself a wide safety buffer to address the uncertainty.'),
                       t('Loose Wet avalanches occur when water is running through the snowpack, and release at or below the trigger point. Avoid very steep slopes and terrain traps such as cliffs, gullies, or tree wells. Exit avalanche terrain when you see pinwheels, roller balls, a slushy surface, or during rain-on-snow events.'),
                       t('Loose Dry avalanches exist throughout the terrain, release at or below the trigger point, and can run in densely-treed areas. Avoid very steep slopes and terrain traps such as cliffs, gullies, or tree wells.'),
                       t('Generally safe avalanche conditions. Watch for unstable snow on isolated terrain features. Use normal caution when travelling in the backcountry.'),
                       t('Cornice Fall avalanches are caused by a release of overhanging, wind drifted snow. Cornices form on lee and cross-loaded ridges, sub-ridges, and sharp convexities. They are easiest to trigger during periods of rapid growth from wind drifting, rapid warming, or during rain-on-snow events. Cornices may break farther back onto flatter areas than expected.'),
                       t('Glide avalanches occur when water lubricates the interface between the snowpack and the ground. The entire snowpack releases. These avalanches are difficult to predict. Avoid the terrain identified in the forecast or below glide cracks.'),
                     );
                     $ct_text = $ct[$ac];
                     print $ct_text; ?>
                  </div>
               </div>
            </div>
         </div>
         <?php if (isset($node->field_description_1['und'][0]['value'])): ?>
         <div id="problem-description"><?php print render($content['field_description_1'][0]['#markup']);?></div>
         <?php endif; ?>
      </div>
      <?php endif; ?>
      <!--End problem 1 -->
      <!--Problem 2-->
      <?php if (isset($node->field_type_2['und'][0]['value'])): ?>
      <div class="clearbg avalanche-problem-row">
         <span class="title"><?php print t('Avalanche Character 2:'); ?> <?php print render($content['field_type_2']['0']['#markup']);?></span>
         <div class="snowpack-summary">
            <div id="problem-type" class="border-right">
               <div class="problem-char-label">
               </div>
               <div class="problem-char-wrapper problem-type">
                  <a href="<?php print $url; ?>/avalanche-problems#<?php print $node->field_type_2['und'][0]['value'];?>" target="_blank">
                  <img src="/themes/responsive_sac/img/api/<?php print $node->field_type_2['und'][0]['value'];?>.png">
                  </a>
               </div>
            </div>
            <div id="problem-canned-text" class="border-right">
               <div class="problem-char-label">
               </div>
               <div class="problem-char-wrapper">
                  <div class="problem-text"><?php $ac = $node->field_type_2['und'][0]['value'];
                     $ct = array(
                       '',
                       t('Storm Slab avalanches release naturally during snow storms and can be triggered for a few days after a storm. They often release at or below the trigger point. They exist throughout the terrain. Avoid them by waiting for the storm snow to stabilize.'),
                       t('Deep Slab avalanches are destructive and deadly events that can release months after the weak layer was buried. They are scarce compared to Storm or Wind Slab avalanches. Their cycles include fewer avalanches and occur over a larger region. You can triggered them from well down in the avalanche path, and after dozens of tracks have crossed the slope. Avoid the terrain identified in the forecast and give yourself a wide safety buffer to address the uncertainty.'),
                       t('Wind Slab avalanches release naturally during wind events and can be triggered for up to a week after a wind event. They form in lee and cross-loaded terrain features. Avoid them by sticking to wind sheltered or wind scoured areas.'),
                       t('Wet Slab avalanches occur when there is liquid water in the snowpack, and can release during the first few days of a warming period. Travel early in the day and avoid avalanche terrain when you see pinwheels, roller balls, loose wet avalanches, or during rain-on-snow events.'),
                       t('Persistent Slab avalanches can be triggered days to weeks after the last storm. They often propagate across and beyond terrain features that would otherwise confine Wind and Storm Slab avalanches. In some cases they can be triggered remotely, from low-angle terrain or adjacent slopes. Give yourself a wide safety buffer to address the uncertainty.'),
                       t('Loose Wet avalanches occur when water is running through the snowpack, and release at or below the trigger point. Avoid very steep slopes and terrain traps such as cliffs, gullies, or tree wells. Exit avalanche terrain when you see pinwheels, roller balls, a slushy surface, or during rain-on-snow events.'),
                       t('Loose Dry avalanches exist throughout the terrain, release at or below the trigger point, and can run in densely-treed areas. Avoid very steep slopes and terrain traps such as cliffs, gullies, or tree wells.'),
                       t('Generally safe avalanche conditions. Watch for unstable snow on isolated terrain features. Use normal caution when travelling in the backcountry.'),
                       t('Cornice Fall avalanches are caused by a release of overhanging, wind drifted snow. Cornices form on lee and cross-loaded ridges, sub-ridges, and sharp convexities. They are easiest to trigger during periods of rapid growth from wind drifting, rapid warming, or during rain-on-snow events. Cornices may break farther back onto flatter areas than expected.'),
                       t('Glide avalanches occur when water lubricates the interface between the snowpack and the ground. The entire snowpack releases. These avalanches are difficult to predict. Avoid the terrain identified in the forecast or below glide cracks.'),
                     );
                     $ct_text = $ct[$ac];
                     print $ct_text; ?>
                  </div>
               </div>
            </div>
         </div>
         <?php if (isset($node->field_description_2['und'][0]['value'])): ?>
         <div id="problem-description"><?php print render($content['field_description_2'][0]['#markup']);?></div>
         <?php endif; ?>
      </div>
      <?php endif; ?>
      <!--End problem 2 -->
      <!--Problem 3-->
      <?php if (isset($node->field_type_3['und'][0]['value'])): ?>
      <div class="clearbg avalanche-problem-row">
         <span class="title"><?php print t('Avalanche Character 3:'); ?> <?php print render($content['field_type_3']['0']['#markup']);?></span>
         <div class="snowpack-summary">
            <div id="problem-type" class="border-right">
               <div class="problem-char-label">
               </div>
               <div class="problem-char-wrapper problem-type">
                  <a href="<?php print $url; ?>/avalanche-problems#<?php print $node->field_type_3['und'][0]['value'];?>" target="_blank">
                  <img src="/themes/responsive_sac/img/api/<?php print $node->field_type_3['und'][0]['value'];?>.png">
                  </a>
               </div>
            </div>
            <div id="problem-canned-text" class="border-right">
               <div class="problem-char-label">
               </div>
               <div class="problem-char-wrapper">
                  <div class="problem-text"><?php $ac = $node->field_type_3['und'][0]['value'];
                     $ct = array(
                       '',
                       t('Storm Slab avalanches release naturally during snow storms and can be triggered for a few days after a storm. They often release at or below the trigger point. They exist throughout the terrain. Avoid them by waiting for the storm snow to stabilize.'),
                       t('Deep Slab avalanches are destructive and deadly events that can release months after the weak layer was buried. They are scarce compared to Storm or Wind Slab avalanches. Their cycles include fewer avalanches and occur over a larger region. You can triggered them from well down in the avalanche path, and after dozens of tracks have crossed the slope. Avoid the terrain identified in the forecast and give yourself a wide safety buffer to address the uncertainty.'),
                       t('Wind Slab avalanches release naturally during wind events and can be triggered for up to a week after a wind event. They form in lee and cross-loaded terrain features. Avoid them by sticking to wind sheltered or wind scoured areas.'),
                       t('Wet Slab avalanches occur when there is liquid water in the snowpack, and can release during the first few days of a warming period. Travel early in the day and avoid avalanche terrain when you see pinwheels, roller balls, loose wet avalanches, or during rain-on-snow events.'),
                       t('Persistent Slab avalanches can be triggered days to weeks after the last storm. They often propagate across and beyond terrain features that would otherwise confine Wind and Storm Slab avalanches. In some cases they can be triggered remotely, from low-angle terrain or adjacent slopes. Give yourself a wide safety buffer to address the uncertainty.'),
                       t('Loose Wet avalanches occur when water is running through the snowpack, and release at or below the trigger point. Avoid very steep slopes and terrain traps such as cliffs, gullies, or tree wells. Exit avalanche terrain when you see pinwheels, roller balls, a slushy surface, or during rain-on-snow events.'),
                       t('Loose Dry avalanches exist throughout the terrain, release at or below the trigger point, and can run in densely-treed areas. Avoid very steep slopes and terrain traps such as cliffs, gullies, or tree wells.'),
                       t('Generally safe avalanche conditions. Watch for unstable snow on isolated terrain features. Use normal caution when travelling in the backcountry.'),
                       t('Cornice Fall avalanches are caused by a release of overhanging, wind drifted snow. Cornices form on lee and cross-loaded ridges, sub-ridges, and sharp convexities. They are easiest to trigger during periods of rapid growth from wind drifting, rapid warming, or during rain-on-snow events. Cornices may break farther back onto flatter areas than expected.'),
                       t('Glide avalanches occur when water lubricates the interface between the snowpack and the ground. The entire snowpack releases. These avalanches are difficult to predict. Avoid the terrain identified in the forecast or below glide cracks.'),
                     );
                     $ct_text = $ct[$ac];
                     print $ct_text; ?>
                  </div>
               </div>
            </div>
         </div>
         <?php if (isset($node->field_description_3['und'][0]['value'])): ?>
         <div id="problem-description"><?php print render($content['field_description_3'][0]['#markup']);?></div>
         <?php endif; ?>
      </div>
      <?php endif; ?>
      <!--End problem 3 -->
      <!--AVALANCHE TEXT DISCUSSION-->
      <?php if ($node->field_text_discussion['und'][0]['value'] != ''): ?>
      <div class="clearbg avalanche-problem-row">
         <span class="title"><?php print t('Snowpack Discussion'); ?></span>
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
                  <a href="<?php print $url.$submit_snowpack_obs_page; ?>"><?php print t('Submit Snowpack Observations'); ?></a>
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
      /*  print '<pre>';
        var_dump(get_defined_vars());
      print '</pre>';
      */
      ?>
</div>
<?php
   //Enable below to show all Array Variables of Form

   //print '<pre>';
   //print render($content['field_type_1']['0']['#markup']);
   //print print_r($node);
   //print '</pre>';

   ?>
