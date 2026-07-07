<script type="text/javascript">
   var needToConfirm;
   needToConfirm = true;
   window.onsubmit = submitexit;
   function submitexit()
   {
   needToConfirm = false;
   }
   window.onbeforeunload = confirmExit;
   function confirmExit()
   {
   if (needToConfirm)
   return "Don't do it! If you have not saved this page, it will be lost and you will have to retype the whole darn thing in a short amount of time.";
   }
</script>
<?php
   $nws_name = theme_get_setting('local_nws_name');
   $nws_url = theme_get_setting('local_nws_url');
   $wx_low = theme_get_setting('wx_elevation_low');
   $wx_high = theme_get_setting('wx_elevation_high');
   $current_wx_desc = theme_get_setting('current_wx_conditions_desc');
   ?>
<div class="avalanche-problem-row">
   <h2>BOTTOM LINE</h2>
   <div><?php print render($form['field_forecast_region']); ?></div>
   <div><?php print render($form['field_duration']); ?></div>
   <div><?php print render($form['field_bottom_line']);?></div>
</div>
<!--Avalanche Problems-->
<div class="avalanche-problem-row">
   <div>
      <h2>Avalanche Problem 1:</h2>
   </div>
   <div><?php print render($form['field_type_1']);?></div>
   <div><?php print render($form['field_description_1']); ?></div>
</div>
<div class="avalanche-problem-row">
   <div>
      <h2>Avalanche Problem 2:</h2>
   </div>
   <div><?php print render($form['field_type_2']);?></div>
   <div><?php print render($form['field_description_2']); ?></div>
</div>
<div class="avalanche-problem-row">
   <div>
      <h2>Avalanche Problem 3:</h2>
   </div>
   <div><?php print render($form['field_type_3']);?></div>
   <div><?php print render($form['field_description_3']); ?></div>
</div>
<!-- End Avalanche Problems-->
<div class="avalanche-problem-row">
   <h2>RECENT OBSERVATIONS:</h2>
   <?php print render($form['field_recent_activity']); ?>
   <div id="recent-obs" class="">
      <?php echo views_embed_view('media_gallery', 'block_1'); ?>
   </div>
</div>
<div><?php print render($form['field_text_discussion']);?></div>
<div class="avalanche-problem-row">
   <h2>WEATHER AND CURRENT CONDITIONS</h2>
   <div>
      <?php print render($form['field_mountain_weather']); ?>
   </div>
   <table border="0" cellpadding="5" cellspacing="5" width="737">
      <tbody>
         <tr>
            <th colspan="2" scope="col"><?php print $current_wx_desc;?>:</th>
         </tr>
         <tr>
            <td width="384">0600 temperature:</td>
            <td width="318"><?php unset($form['field_temp8700']['und'][0]['value']['#title']); ?>
               <?php print render($form['field_temp8700']);?>deg. F.
            </td>
         </tr>
         <tr>
            <td>Max. temperature in the last 24 hours:</td>
            <td><?php
               unset($form['field_hr24maxtemp']['und'][0]['value']['#title']);
               print render($form['field_hr24maxtemp']);
               ?>deg. F.</td>
         </tr>
         <tr>
            <td>Average wind direction during the last 24 hours:</td>
            <td><?php
               unset($form['field_hr24winddir']['und'][0]['value']['#title']);
               print render($form['field_hr24winddir']);
               ?></td>
         </tr>
         <tr>
            <td>Average wind speed during the last 24 hours:</td>
            <td><?php
               unset($form['field_hr24windspeed']['und'][0]['value']['#title']);
               print render($form['field_hr24windspeed']);
               ?>mph</td>
         </tr>
         <tr>
            <td>Maximum wind gust in the last 24 hours:</td>
            <td><?php
               unset($form['field_hr24maxgust']['und'][0]['value']['#title']);
               print render($form['field_hr24maxgust']);
               ?>mph</td>
         </tr>
         <tr>
            <td>New snowfall in the last 24 hours:</td>
            <td><?php
               unset($form['field_hr24snowfall']['und'][0]['value']['#title']);
               print render($form['field_hr24snowfall']);
               ?>inches
            </td>
         </tr>
         <tr>
            <td>Total snow depth:</td>
            <td><?php
               unset($form['field_totalsnowdepth']['und'][0]['value']['#title']);
               print render($form['field_totalsnowdepth']);
               ?>inches
            </td>
         </tr>
      </tbody>
   </table>
   <table width="100%" cellspacing="1" border="1" cellpadding="1">
      <tbody>
         <tr>
            <th colspan="4" bordercolor="#7E290B">2 Day Mountain Weather Forecast:</th>
         </tr>
         <tr>
            <td colspan="4" align="center"><strong><?php print $wx_low;?></strong></td>
         </tr>
         <tr>
            <td></td>
            <td>Today</td>
            <td>Tonight</td>
            <td>Tomorrow</td>
         </tr>
         <tr>
            <td>Weather:</td>
            <td><?php
               unset($form['field_today7to8weather']['und'][0]['value']['#title']);
               print render($form['field_today7to8weather']);
               ?></td>
            <td><?php
               unset($form['field_tonight7to8weather']['und'][0]['value']['#title']);
               print render($form['field_tonight7to8weather']);
               ?></td>
            <td><?php
               unset($form['field_tomorrow7to8weather']['und'][0]['value']['#title']);
               print render($form['field_tomorrow7to8weather']);
               ?></td>
         </tr>
         <tr>
            <td>Temperatures:</td>
            <td><?php
               unset($form['field_today7to8temp']['und'][0]['value']['#title']);
               print render($form['field_today7to8temp']);
               ?>deg. F.</td>
            <td><?php
               unset($form['field_tonight7to8temp']['und'][0]['value']['#title']);
               print render($form['field_tonight7to8temp']);
               ?>deg. F.</td>
            <td><?php
               unset($form['field_tomorrow7to8temp']['und'][0]['value']['#title']);
               print render($form['field_tomorrow7to8temp']);
               ?>deg. F.</td>
         </tr>
         <tr>
            <td>Wind direction:</td>
            <td><?php
               unset($form['field_today7to8winddirection']['und'][0]['value']['#title']);
               print render($form['field_today7to8winddirection']);
               ?></td>
            <td><?php
               unset($form['field_tonight7to8winddirection']['und'][0]['value']['#title']);
               print render($form['field_tonight7to8winddirection']);
               ?></td>
            <td><?php
               unset($form['field_tomorrow7to8winddirection']['und'][0]['value']['#title']);
               print render($form['field_tomorrow7to8winddirection']);
               ?></td>
         </tr>
         <tr>
            <td>Wind speed:</td>
            <td><?php
               unset($form['field_today7to8windspeed']['und'][0]['value']['#title']);
               print render($form['field_today7to8windspeed']);
               ?></td>
            <td><?php
               unset($form['field_tonight7to8windspeed']['und'][0]['value']['#title']);
               print render($form['field_tonight7to8windspeed']);
               ?></td>
            <td><?php
               unset($form['field_tomorrow7to8windspeed']['und'][0]['value']['#title']);
               print render($form['field_tomorrow7to8windspeed']);
               ?></td>
         </tr>
         <tr>
            <td>Expected snowfall:</td>
            <td><?php
               unset($form['field_today7to8snow']['und'][0]['value']['#title']);
               print render($form['field_today7to8snow']);
               ?>in.</td>
            <td><?php
               unset($form['field_tonight7to8snow']['und'][0]['value']['#title']);
               print render($form['field_tonight7to8snow']);
               ?>in.</td>
            <td><?php
               unset($form['field_tomorrow7to8snow']['und'][0]['value']['#title']);
               print render($form['field_tomorrow7to8snow']);
               ?>in.</td>
         </tr>
         <tr>
            <td colspan="4" align="center"><strong><?php print $wx_high;?>:<strong></td>
         </tr>
         <tr>
            <td></td>
            <td>Today</td>
            <td>Tonight</td>
            <td>Tomorrow</td>
         </tr>
         <tr>
            <td>Weather:</td>
            <td><?php
               unset($form['field_today8to9weather']['und'][0]['value']['#title']);
               print render($form['field_today8to9weather']);
               ?></td>
            <td><?php
               unset($form['field_tonight8to9weather']['und'][0]['value']['#title']);
               print render($form['field_tonight8to9weather']);
               ?></td>
            <td><?php
               unset($form['field_tomorrow8to9weather']['und'][0]['value']['#title']);
               print render($form['field_tomorrow8to9weather']);
               ?></td>
         </tr>
         <tr>
            <td>Temperatures:</td>
            <td><?php
               unset($form['field_today8to9temp']['und'][0]['value']['#title']);
               print render($form['field_today8to9temp']);
               ?>deg. F.</td>
            <td><?php
               unset($form['field_tonight8to9temp']['und'][0]['value']['#title']);
               print render($form['field_tonight8to9temp']);
               ?>deg. F.</td>
            <td><?php
               unset($form['field_tomorrow8to9temp']['und'][0]['value']['#title']);
               print render($form['field_tomorrow8to9temp']);
               ?>deg. F.</td>
         </tr>
         <tr>
            <td>Wind direction:</td>
            <td><?php
               unset($form['field_today8to9winddirection']['und'][0]['value']['#title']);
               print render($form['field_today8to9winddirection']);
               ?></td>
            <td><?php
               unset($form['field_tonight8to9winddirection']['und'][0]['value']['#title']);
               print render($form['field_tonight8to9winddirection']);
               ?></td>
            <td><?php
               unset($form['field_tomorrow8to9winddirection']['und'][0]['value']['#title']);
               print render($form['field_tomorrow8to9winddirection']);
               ?></td>
         </tr>
         <tr>
            <td>Wind speed:</td>
            <td><?php
               unset($form['field_today8to9windspeed']['und'][0]['value']['#title']);
               print render($form['field_today8to9windspeed']);
               ?></td>
            <td><?php
               unset($form['field_tonight8to9windspeed']['und'][0]['value']['#title']);
               print render($form['field_tonight8to9windspeed']);
               ?></td>
            <td><?php
               unset($form['field_tomorrow8to9windspeed']['und'][0]['value']['#title']);
               print render($form['field_tomorrow8to9windspeed']);
               ?></td>
         </tr>
         <tr>
            <td>Expected snowfall:</td>
            <td><?php
               unset($form['field_today8to9snow']['und'][0]['value']['#title']);
               print render($form['field_today8to9snow']);
               ?>in.</td>
            <td><?php
               unset($form['field_tonight8to9snow']['und'][0]['value']['#title']);
               print render($form['field_tonight8to9snow']);
               ?>in.</td>
            <td><?php
               unset($form['field_tomorrow8to9snow']['und'][0]['value']['#title']);
               print render($form['field_tomorrow8to9snow']);
               ?>in.</td>
         </tr>
      </tbody>
   </table>
</div>
<div>
   <table>
      <tr>
         <td><?php print render($form['title']); ?> </td>
      </tr>
   </table>
</div>
<?php
   print drupal_render_children($form);

    //Enable below to show all Array Variables of Form

   // print '<pre>';
   // print_r($form);
   // print '</pre>';
   ?>

