<?php

// $Id: views-view-row-rss--advisory-views--feed-1.tpl.php
/**
* @file views-view-row-rss.tpl.php
* Default view template to display a item in an RSS feed.
*
* @ingroup views_templates
*/
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
      $email_header_img = theme_get_setting('email_header_img');
      $site_name = variable_get('site_name');
?>
<item>
<title><?php print $title; ?></title>
<link><?php print $link; ?></link>
<description><![CDATA[
<div>
   <table>
      <tr>
	<th>
<?php 
$forecastdate = date("F j, Y", $node->created);
if (date('Y-m-d') == date('Y-m-d', $node->created)) {
print "Today's Avalanche Advisory:";
}
else { 
echo 'Avalanche Advisory published on '.$forecastdate.':';
} ?>
        </th>
        <td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Issued by <?php print render($display_name[0]['name_line']);?> - <?php print render($company[0]['title']);?>
  at <?php print format_date($node->created, 'custom', 'g:ia'); ?>
        </td>
      </table>
</div>
 <div>
  <div>
    <table>
    <tr>
      <!--<td><img src="<?php print $url.$node->field_overall_danger_rose['und'][0]['img_detailed_rose']?>"/ >
      </td>--!>
      <td><strong>Bottom Line:</strong><br /><?php print $node->field_bottom_line['und'][0]['value']; ?> 
      </td>
    </table>
  </div>

    <?php if (isset($node->field_avalanche_watch['und'][0]['value'])): ?>
      <div>
        <table>
        <tr>
          <td><img src="<?php print $url;?>/themes/responsive_sac/img/row/heading-awatch.jpg"></td>
          <td><strong>Avalanche Watch:</strong><br />
	   <?php print $node->field_avalanche_watch['und'][0]['value']; ?></td>
        </table>
      </div>
    <?php endif; ?>
 </div>   
<?php
//print '<pre>';
// print_r($node);
// print '</pre>';
 ?>

]]></description>
<?php print $item_elements; ?>
</item>
