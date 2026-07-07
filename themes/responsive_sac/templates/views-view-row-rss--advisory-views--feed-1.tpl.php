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
   <!--PUBLISHED DATE AND FORECASTER NAME-->
      <div id="subtitle-date-row" class="darkbg">
             <div id="advisory-published-date" class="advisory-date">
 <?php
		if ($duration >= '120') {
                echo 'Avalanche Advisory published on '.$forecastdate;
                }
                elseif ($nowTimestamp <= $expdateTimestamp) {
                echo 'Snowpack Summary published on '.$forecastdate.'</br><span id="timeToExp">This Avalanche Advisory expires in ';
                if ($timeLeftToExpire->format("%h") < 2) {
                   echo '<span style="color:red">';
				   echo $timeLeftToExpire->format("%h hours, %i minutes");
				   echo '</span></span>';
		        }
		        elseif (($timeLeftToExpire->format("%d") >= 1) && ($timeLeftToExpire->format("%d") < 2)) {
		           echo $timeLeftToExpire->format("%d day, %h hours, %i minutes");
		           echo '</span>';
		        }
		        elseif ($timeLeftToExpire->format("%d") > 2) {
		           echo $timeLeftToExpire->format("%d days, %h hours, %i minutes");
		           echo '</span>';
		        }
		        elseif ($timeLeftToExpire->format("%d") < 1) {
		           echo $timeLeftToExpire->format("%h hours, %i minutes");
		           echo '</span>';
		        }
		                        
        }
        elseif ($nowTimestamp > $expdateTimestamp) {
                echo '<span style="background:red">THIS AVALANCHE ADVISORY EXPIRED ON '.$expdate.'</span></br>'.'Avalanche Advisory published on '.$forecastdate;
        }
    ?>
             </div>
	     <div id="forecaster-name">                 <?php
                   if ($nowTimestamp > $expdateTimestamp) {
                    echo '';
                   }
                   elseif ($duration <= '120'){
                   print "<span style='font-weight:bold; text-transform:uppercase'>This advisory is valid for $duration hours</span><br>";
                   }
                 ?> 
               Issued by 
                 <?php print render($display_name[0]['name_line']);?> - <?php print render($company[0]['title']);?>
             </div>
      </div>
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
		<strong>Avalanche Watch:</strong><br />
	   <?php print $node->field_avalanche_watch['und'][0]['value']; ?>
      </div>
    <?php endif; ?>

<!--Avalanche Problems-->
<?php if (isset($node->field_type_1['und'][0]['value'])): ?>
<div><strong>Avalanche Problem 1:</strong>
 <table>
  <tbody>
     <tr>
       <th>Type</th>
       <th>Aspect/Elevation</th>
       <th>Characteristics</th>
     </tr>
     <tr align="center">
       <td>
         <img height="175px" src="<?php print $url;?>/themes/responsive_sac/img/api/<?php print $node->field_type_1['und'][0]['value'];?>.png"/>
       </td>
       <td>
         <img height="192px" src="<?php print $url.$node->field_rose_1['und'][0]['img_detailed_rose'];?>"/>
       </td>
       <td>
         <table width="100%">
           <tbody align="center">
             <tr>
               <th>Likelihood</th>
               <th>Size</th>
               <!--<th>Trend</th>-->
             </tr>
             <tr>
               <td>
		<font size="2">
                 <div><img height="175px" src="<?php print $url;?>/themes/responsive_sac/img/slider/likelihood-<?php print $node->field_likelihood_1['und'][0]['value'];?>.png"/></div>
		</font>
               </td>        
               <td>
		<font size="2">
                 <div><img height="175px" src="<?php print $url;?>/themes/responsive_sac/img/slider/size-<?php print $node->field_size_1['und'][0]['value'].$node->field_size_1['und'][1]['value'];?>.png"/></div>          
		</font>
               </td>
               <td>
               <!--
		<font size="2">
                 <div>More Dangerous</div>
                 <div><img src="<?php print $url;?>/themes/responsive_sac/img/trend/<?php print $node->field_trend_1['und'][0]['value'];?>.png"/></div>
                 <div>Less Dangerous</div>
		</font>
		-->
               </td>
              </tr>
            </tbody>
          </table>
        </td>
      <tr>
       <td colspan="3" align="left"><strong>Description:</strong><?php print $node->field_description_1['und'][0]['value']; ?></td>
     </table>
</div>
<?php endif; ?>

<?php if (isset($node->field_type_2['und'][0]['value'])): ?>
<div><strong>Avalanche Problem 2:</strong>
 <table>
  <tbody>
     <tr>
       <th>Type</th>
       <th>Aspect/Elevation</th>
       <th>Characteristics</th>
     </tr>
     <tr align="center">
       <td>
         <img height="175px" src="<?php print $url;?>/themes/responsive_sac/img/api/<?php print $node->field_type_2['und'][0]['value'];?>.png"/>
       </td>
       <td>
         <img height="192px" src="<?php print $url.$node->field_rose_2['und'][0]['img_detailed_rose'];?>"/>
       </td>
       <td>
         <table width="100%">
           <tbody align="center">
             <tr>
               <th>Likelihood</th>
               <th>Size</th>
               <!--<th>Trend</th>-->
             </tr>
             <tr>
               <td>
		<font size="2">
                 <div><img height="175px" src="<?php print $url;?>/themes/responsive_sac/img/slider/likelihood-<?php print $node->field_likelihood_2['und'][0]['value'];?>.png"/></div>
		</font>
               </td>        
               <td>
		<font size="2">
                 <div><img height="175px" src="<?php print $url;?>/themes/responsive_sac/img/slider/size-<?php print $node->field_size_2['und'][0]['value'].$node->field_size_2['und'][1]['value'];?>.png"/></div>          
		</font>
               </td>
               <td>
               <!--
		<font size="2">
                 <div>More Dangerous</div>
                 <div><img src="<?php print $url;?>/themes/responsive_sac/img/trend/<?php print $node->field_trend_2['und'][0]['value'];?>.png"/></div>
                 <div>Less Dangerous</div>
		</font>
		-->
               </td>
              </tr>
            </tbody>
          </table>
        </td>
      <tr>
       <td colspan="3" align="left"><strong>Description:</strong><?php print $node->field_description_2['und'][0]['value']; ?></td>
     </table>
</div>
<?php endif; ?>

<?php if (isset($node->field_type_3['und'][0]['value'])): ?>
<div><strong>Avalanche Problem 3:</strong>
 <table>
  <tbody>
     <tr>
       <th>Type</th>
       <th>Aspect/Elevation</th>
       <th>Characteristics</th>
     </tr>
     <tr align="center">
       <td>
         <img height="175px" src="<?php print $url;?>/themes/responsive_sac/img/api/<?php print $node->field_type_3['und'][0]['value'];?>.png"/>
       </td>
       <td>
         <img height="192px" src="<?php print $url.$node->field_rose_3['und'][0]['img_detailed_rose'];?>"/>
       </td>
       <td>
         <table width="100%">
           <tbody align="center">
             <tr>
               <th>Likelihood</th>
               <th>Size</th>
               <!--<th>Trend</th>-->
             </tr>
             <tr>
               <td>
		<font size="2">
                 <div><img height="175px" src="<?php print $url;?>/themes/responsive_sac/img/slider/likelihood-<?php print $node->field_likelihood_3['und'][0]['value'];?>.png"/></div>
		</font>
               </td>        
               <td>
		<font size="2">
                 <div><img height="175px" src="<?php print $url;?>/themes/responsive_sac/img/slider/size-<?php print $node->field_size_3['und'][0]['value'].$node->field_size_3['und'][1]['value'];?>.png"/></div>          
		</font>
               </td>
               <td>
               <!--
		<font size="2">
                 <div>More Dangerous</div>
                 <div><img src="<?php print $url;?>/themes/responsive_sac/img/trend/<?php print $node->field_trend_3['und'][0]['value'];?>.png"/></div>
                 <div>Less Dangerous</div>
		</font>
		-->
               </td>
              </tr>
            </tbody>
          </table>
        </td>
      <tr>
       <td colspan="3" align="left"><strong>Description:</strong><?php print $node->field_description_3['und'][0]['value']; ?></td>
     </table>
</div>
<?php endif; ?>


<!-- End Avalanche Problems-->

<?php if (isset($node->field_text_discussion['und'][0]['value'])): ?>
<div id="current-conditions-row" class="advisory-row">
<strong>Advisory discussion</strong><br />
<?php print_r($node->field_text_discussion['und'][0]['value']); ?>
</div>
<?php endif; ?>

<?php if (isset($node->field_recent_activity['und'][0]['value'])): ?>
    <div id="recent-activity-row" class="advisory-row">
		<strong>Recent Observations:</strong><br />
		<?php print $node->field_recent_activity['und'][0]['value']; ?>
    </div>
<?php endif; ?>

<div id="current-conditions-row" class="advisory-row">
<table width="100%" cellspacing="5" cellpadding="5" border="0" class="row-sub-table"><!--id="Wx-obs" class="forecast"-->
    <tbody>
        <tr>
            <td colspan="2">
        <strong>Current Conditions:<strong>&nbsp;- <?php print $current_wx_desc;?>:
            </td>
        </tr>
        <tr style="background-color:#ffffff">
            <td >0600 temperature:</td>
            <td ><?php print $node->field_temp8700['und'][0]['value'];?> deg. F.</td>
        </tr>
        <tr  style="background-color:#ffffff">
            <td >Max. temperature in the last 24 hours:</td>
            <td ><?php print $node->field_hr24maxtemp['und'][0]['value'];?> deg. F.</td>
        </tr>
        <tr  style="background-color:#ffffff">
            <td >Average wind direction during the last 24 hours:</td>
            <td ><?php print $node->field_hr24winddir['und'][0]['value'];?></td>
        </tr>
        <tr  style="background-color:#ffffff">
            <td >Average wind speed during the last 24 hours:</td>
            <td ><?php print $node->field_hr24windspeed['und'][0]['value'];?> mph</td>
        </tr>
        <tr  style="background-color:#ffffff">
            <td >Maximum wind gust in the last 24 hours:</td>
            <td ><?php print $node->field_hr24maxgust['und'][0]['value'];?> mph</td>
        </tr>
        <tr  style="background-color:#ffffff">
            <td >New snowfall in the last 24 hours:</td>
            <td ><?php print $node->field_hr24snowfall['und'][0]['value'];?> inches</td>
        </tr>
        <tr  style="background-color:#ffffff">
            <td >Total snow depth:</td>
            <td ><?php print $node->field_totalsnowdepth['und'][0]['value'];?> inches</td>
        </tr>
    </tbody>
</table>
</div>

<?php if (isset($node->field_mountain_weather['und'][0]['value'])): ?>
    <div id="weather-row" class="advisory-row">
		<strong>Weather:</strong><br /><?php print $node->field_mountain_weather['und'][0]['value']; ?>
     </div>       
<?php endif; ?>


<div id="current-conditions-row" class="advisory-row">
    <strong>Two-Day Mountain Weather Forecast</strong>&nbsp;- Produced in partnership with the <a href="<?php print $nws_url;?>" target="_blank"><?php print $nws_name;?>:</a>
   <table width="100%" cellspacing="5" cellpadding="5" border="0" class="row-sub-table">
    <tbody>
        <tr  style="background-color:#ffffff">
            <td align="center" colspan="4">
            <strong><?php print $wx_low;?>:</strong>
            </td>
        </tr>
        <tr  style="background-color:#ffffff">
            <td>&nbsp;</td>
            <td style="text-align:center">
<?php 
if (date('Y-m-d') == date('Y-m-d', $node->created)) {
	print "Today";
}
else { 
	echo date("l", $node->created);
} ?>:
            </td>
            <td style="text-align:center">
<?php 
if (date('Y-m-d') == date('Y-m-d', $node->created)) {
	print "Tonight";
}
else { 
	echo date("l", $node->created)." Night";
} ?>:
            </td>
            <td style="text-align:center">
<?php 
$tomorrow  = strtotime('+1 day', $node->created);
echo date("l", $tomorrow);?>:
            </td>
        </tr>
        <tr  style="background-color:#ffffff">
            <td width="15%" style="border-top: 3px groove rgb(126, 41, 11); border-left: 3px groove rgb(126, 41, 11); border-right: 1px solid rgb(126, 41, 11);">Weather:</td>
            <td width="28%"  style="text-align:center; border-top: 3px groove rgb(126, 41, 11); border-left: 1px solid rgb(126, 41, 11); border-right: 1px solid rgb(126, 41, 11);"><?php print $node->field_today7to8weather['und'][0]['value'] ?></td>
            <td width="28%"  style="text-align:center; border-top: 3px groove rgb(126, 41, 11); border-left: 1px solid rgb(126, 41, 11); border-right: 1px solid rgb(126, 41, 11);"><?php print $node->field_tonight7to8weather['und'][0]['value'] ?></td>
            <td width="28%"  style="text-align:center; border-top: 3px groove rgb(126, 41, 11); border-left: 1px solid rgb(126, 41, 11); border-right: 3px groove rgb(126, 41, 11);"><?php print $node->field_tomorrow7to8weather['und'][0]['value'] ?></td>
        </tr>
        <tr  style="background-color:#ffffff">
            <td style="border-top: 3px groove rgb(126, 41, 11); border-left: 3px groove rgb(126, 41, 11); border-right: 1px solid rgb(126, 41, 11);">Temperatures:</td>
            <td style="text-align:center; border-top: 2px solid rgb(126, 41, 11); border-left: 1px solid rgb(126, 41, 11); border-right: 1px solid rgb(126, 41, 11);"><?php print $node->field_today7to8temp['und'][0]['value'] ?> deg. F.</td>
            <td  style="text-align:center; border-top: 2px solid rgb(126, 41, 11); border-left: 1px solid rgb(126, 41, 11); border-right: 1px solid rgb(126, 41, 11);"><?php print $node->field_tonight7to8temp['und'][0]['value'] ?> deg. F.</td>
            <td  style="text-align:center; border-top: 2px solid rgb(126, 41, 11); border-left: 1px solid rgb(126, 41, 11); border-right: 3px groove rgb(126, 41, 11);"><?php print $node->field_tomorrow7to8temp['und'][0]['value'] ?> deg. F.</td>
        </tr>
        <tr  style="background-color:#ffffff">
            <td style="border-top: 3px groove rgb(126, 41, 11); border-left: 3px groove rgb(126, 41, 11); border-right: 1px solid rgb(126, 41, 11);">Wind direction:</td>
            <td  style="text-align:center; border-top: 2px solid rgb(126, 41, 11); border-left: 1px solid rgb(126, 41, 11); border-right: 1px solid rgb(126, 41, 11);"><?php print $node->field_today7to8winddirection['und'][0]['value'] ?></td>
            <td  style="text-align:center; border-top: 2px solid rgb(126, 41, 11); border-left: 1px solid rgb(126, 41, 11); border-right: 1px solid rgb(126, 41, 11);"><?php print $node->field_tonight7to8winddirection['und'][0]['value'] ?></td>
            <td  style="text-align:center; border-top: 2px solid rgb(126, 41, 11); border-left: 1px solid rgb(126, 41, 11); border-right: 3px groove rgb(126, 41, 11);"><?php print $node->field_tomorrow7to8winddirection['und'][0]['value'] ?></td>
        </tr>
        <tr  style="background-color:#ffffff">
            <td style="border-top: 3px groove rgb(126, 41, 11); border-left: 3px groove rgb(126, 41, 11); border-right: 1px solid rgb(126, 41, 11);">Wind speed:</td>
            <td  style="text-align:center; border-top: 2px solid rgb(126, 41, 11); border-left: 1px solid rgb(126, 41, 11); border-right: 1px solid rgb(126, 41, 11);"><?php print $node->field_today7to8windspeed['und'][0]['value'] ?> </td>
            <td  style="text-align:center; border-top: 2px solid rgb(126, 41, 11); border-left: 1px solid rgb(126, 41, 11); border-right: 1px solid rgb(126, 41, 11);"><?php print $node->field_tonight7to8windspeed['und'][0]['value'] ?> </td>
            <td  style="text-align:center; border-top: 2px solid rgb(126, 41, 11); border-left: 1px solid rgb(126, 41, 11); border-right: 3px groove rgb(126, 41, 11);"><?php print $node->field_tomorrow7to8windspeed['und'][0]['value'] ?> </td>
        </tr>
        <tr  style="background-color:#ffffff">
            <td style="border-style: groove solid groove groove; border-color: rgb(126, 41, 11); border-width: 3px 1px 3px 3px;">Expected snowfall:</td>
            <td  style="text-align:center; border-style: solid solid groove; border-color: rgb(126, 41, 11); border-width: 2px 1px 3px;"><?php print $node->field_today7to8snow['und'][0]['value'] ?> in.</td>
            <td  style="text-align:center; border-style: solid solid groove; border-color: rgb(126, 41, 11); border-width: 2px 1px 3px;"><?php print $node->field_tonight7to8snow['und'][0]['value'] ?> in.</td>
            <td  style="text-align:center; border-style: solid groove groove solid; border-color: rgb(126, 41, 11); border-width: 2px 3px 3px 1px;"><?php print $node->field_tomorrow7to8snow['und'][0]['value'] ?> in.</td>
        </tr>
        <tr  style="background-color:#ffffff">
            <td align="center" colspan="4">
            <strong><?php print $wx_high;?>:</strong>
            </td>
        </tr>
        <tr  style="background-color:#ffffff">
            <td>&nbsp;</td>
            <td style="text-align:center">
<?php 
if (date('Y-m-d') == date('Y-m-d', $node->created)) {
	print "Today";
}
else { 
	echo date("l", $node->created);
} ?>:
	    </td>
            <td style="text-align:center">
<?php 
if (date('Y-m-d') == date('Y-m-d', $node->created)) {
	print "Tonight";
}
else { 
	echo date("l", $node->created)." Night";
} ?>:
	    </td>
            <td style="text-align:center">
<?php 
$tomorrow  = strtotime('+1 day', $node->created);
echo date("l", $tomorrow);?>:
	    </td>
        </tr>
        <tr  style="background-color:#ffffff">
            <td style="border-top: 3px groove rgb(126, 41, 11); border-left: 3px groove rgb(126, 41, 11); border-right: 1px solid rgb(126, 41, 11);">Weather:</td>
            <td  style="text-align:center; border-top: 3px groove rgb(126, 41, 11); border-left: 1px solid rgb(126, 41, 11); border-right: 1px solid rgb(126, 41, 11);"><?php print $node->field_today8to9weather['und'][0]['value'] ?></td>
            <td  style="text-align:center; border-top: 3px groove rgb(126, 41, 11); border-left: 1px solid rgb(126, 41, 11); border-right: 1px solid rgb(126, 41, 11);"><?php print  $node->field_tonight8to9weather['und'][0]['value'] ?></td>            
            <td  style="text-align:center; border-top: 3px groove rgb(126, 41, 11); border-left: 1px solid rgb(126, 41, 11); border-right: 3px groove rgb(126, 41, 11);"><?php print $node->field_tomorrow8to9weather['und'][0]['value'] ?></td>
        </tr>
        <tr  style="background-color:#ffffff">
            <td style="border-top: 2px solid rgb(126, 41, 11); border-left: 3px groove rgb(126, 41, 11); border-right: 1px solid rgb(126, 41, 11);">Temperatures:</td>
            <td  style="text-align:center; border-top: 2px solid rgb(126, 41, 11); border-left: 1px solid rgb(126, 41, 11); border-right: 1px solid rgb(126, 41, 11);"><?php print $node->field_today8to9temp['und'][0]['value'] ?> deg. F.</td>
            <td  style="text-align:center; border-top: 2px solid rgb(126, 41, 11); border-left: 1px solid rgb(126, 41, 11); border-right: 1px solid rgb(126, 41, 11);"><?php print $node->field_tonight8to9temp['und'][0]['value'] ?> deg. F.</td>
            <td  style="text-align:center; border-top: 2px solid rgb(126, 41, 11); border-left: 1px solid rgb(126, 41, 11); border-right: 3px groove rgb(126, 41, 11);"><?php print $node->field_tomorrow8to9temp['und'][0]['value'] ?> deg. F.</td>
        </tr>
        <tr  style="background-color:#ffffff">
            <td style="border-top: 2px solid rgb(126, 41, 11); border-left: 3px groove rgb(126, 41, 11); border-right: 1px solid rgb(126, 41, 11);">Wind direction:</td>
            <td  style="text-align:center; border-top: 2px solid rgb(126, 41, 11); border-left: 1px solid rgb(126, 41, 11); border-right: 1px solid rgb(126, 41, 11);"><?php print $node->field_today8to9winddirection['und'][0]['value'] ?></td>
            <td  style="text-align:center; border-top: 2px solid rgb(126, 41, 11); border-left: 1px solid rgb(126, 41, 11); border-right: 1px solid rgb(126, 41, 11);"><?php print $node->field_tonight8to9winddirection['und'][0]['value'] ?></td>
            <td  style="text-align:center; border-top: 2px solid rgb(126, 41, 11); border-left: 1px solid rgb(126, 41, 11); border-right: 3px groove rgb(126, 41, 11);"><?php print $node->field_tomorrow8to9winddirection['und'][0]['value'] ?></td>
        </tr>
        <tr  style="background-color:#ffffff">
            <td style="border-top: 2px solid rgb(126, 41, 11); border-left: 3px groove rgb(126, 41, 11); border-right: 1px solid rgb(126, 41, 11);">Wind speed:</td>
            <td  style="text-align:center; border-top: 2px solid rgb(126, 41, 11); border-left: 1px solid rgb(126, 41, 11); border-right: 1px solid rgb(126, 41, 11);"><?php print $node->field_today8to9windspeed['und'][0]['value'] ?> </td>
            <td  style="text-align:center; border-top: 2px solid rgb(126, 41, 11); border-left: 1px solid rgb(126, 41, 11); border-right: 1px solid rgb(126, 41, 11);"><?php print $node->field_tonight8to9windspeed['und'][0]['value'] ?> </td>
            <td  style="text-align:center; border-top: 2px solid rgb(126, 41, 11); border-left: 1px solid rgb(126, 41, 11); border-right: 3px groove rgb(126, 41, 11);"><?php print $node->field_tomorrow8to9windspeed['und'][0]['value'] ?> </td>
        </tr>
        <tr  style="background-color:#ffffff">
            <td style="border-style: groove solid groove groove; border-color: rgb(126, 41, 11); border-width: 3px 1px 3px 3px;">Expected snowfall:</td>
            <td  style="text-align:center; border-style: solid solid groove; border-color: rgb(126, 41, 11); border-width: 2px 1px 3px;"><?php print $node->field_today8to9snow['und'][0]['value'] ?> in.</td>
            <td  style="text-align:center; border-style: solid solid groove; border-color: rgb(126, 41, 11); border-width: 2px 1px 3px;"><?php print $node->field_tonight8to9snow['und'][0]['value'] ?> in.</td>
            <td  style="text-align:center; border-style: solid groove groove solid; border-color: rgb(126, 41, 11); border-width: 2px 3px 3px 1px;"><?php print $node->field_tomorrow8to9snow['und'][0]['value'] ?> in.</td>
        </tr>
    </tbody>
</table>
</div>

<?php if (isset($node->field_disclaimer['und'][0]['value'])): ?>
    <div id="weather-row" class="advisory-row">
		<strong>Disclaimer:</strong><br /><?php print $node->field_disclaimer['und'][0]['value']; ?>
     </div>       
<?php endif; ?>

<?php
//print '<pre>';
//print $node->field_trend_1['und'][0]['value'];
//print_r($node);
//  print '</pre>';
 ?>

]]></description>
<?php print $item_elements; ?>
</item>
