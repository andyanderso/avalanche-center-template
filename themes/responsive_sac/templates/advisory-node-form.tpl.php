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
   return "<?php print t('If you have not submitted the forecast, it will be lost and you will need to re-enter it.'); ?>";
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
   <h2><?php print t('Bottom Line'); ?></h2>
   <div><?php print render($form['field_forecast_region']); ?></div>
   <div><?php print render($form['field_duration']); ?></div>
   <!--
      <div><?php print render($form['field_bulletin']); ?></div>
      <div><?php print render($form['field_overall_danger_rose'])?></div>
      -->
   <div><?php print render($form['field_bottom_line']); ?></div>
   <div><?php print render($form['field_overalldanger']); ?></div>
   <div><?php print render($form['field_danger_rating_3']); ?></div>
   <div><?php print render($form['field_danger_rating_2']); ?></div>
   <div><?php print render($form['field_danger_rating_1']); ?></div>
</div>
<!--Avalanche Problems-->
<div class="avalanche-problem-row">
   <h2><?php print t('Avalanche Problem 1:'); ?></h2>
   <table>
      <tr align="center">
         <th><?php print t('Type'); ?></th>
         <th><?php print t('Aspect/Elevation'); ?></th>
         <th><?php print t('Likelihood'); ?></th>
         <th><?php print t('Size'); ?></th>
      </tr>
      <tr align="center">
         <td>
            <?php
               unset($form['field_type_1']['und']['#title']);
               print render($form['field_type_1']);
               ?>
         </td>
         <td>
            <?php print render($form['field_rose_1']);?>
         </td>
         <td>
            <?php
               unset($form['field_likelihood_1']['und']['#title']);
               print render($form['field_likelihood_1']);
               ?>
         </td>
         <td>
            <?php
               unset($form['field_size_1']['und']['#title']);
               print render($form['field_size_1']);
               ?>
         </td>
      </tr>
   </table>
   <div><?php print render($form['field_description_1']); ?></div>
</div>
<div class="avalanche-problem-row">
   <h2><?php print t('Avalanche Problem 2:'); ?></h2>
   <table>
      <tr align="center">
         <th><?php print t('Type'); ?></th>
         <th><?php print t('Aspect/Elevation'); ?></th>
         <th><?php print t('Likelihood'); ?></th>
         <th><?php print t('Size'); ?></th>
      </tr>
      <tr align="center">
         <td>
            <?php
               unset($form['field_type_2']['und']['#title']);
               print render($form['field_type_2']);
               ?>
         </td>
         <td>
            <?php print render($form['field_rose_2']);?>
         </td>
         <td>
            <?php
               unset($form['field_likelihood_2']['und']['#title']);
               print render($form['field_likelihood_2']);
               ?>
         </td>
         <td>
            <?php
               unset($form['field_size_2']['und']['#title']);
               print render($form['field_size_2']);
               ?>
         </td>
      </tr>
   </table>
   <div><?php print render($form['field_description_2']); ?></div>
</div>
<div class="avalanche-problem-row">
   <h2><?php print t('Avalanche Problem 3:'); ?></h2>
   <table>
      <tr align="center">
         <th><?php print t('Type'); ?></th>
         <th><?php print t('Aspect/Elevation'); ?></th>
         <th><?php print t('Likelihood'); ?></th>
         <th><?php print t('Size'); ?></th>
      </tr>
      <tr align="center">
         <td>
            <?php
               unset($form['field_type_3']['und']['#title']);
               print render($form['field_type_3']);
               ?>
         </td>
         <td>
            <?php print render($form['field_rose_3']);?>
         </td>
         <td>
            <?php
               unset($form['field_likelihood_3']['und']['#title']);
               print render($form['field_likelihood_3']);
               ?>
         </td>
         <td>
            <?php
               unset($form['field_size_3']['und']['#title']);
               print render($form['field_size_3']);
               ?>
         </td>
      </tr>
   </table>
   <div><?php print render($form['field_description_3']); ?></div>
</div>
<!-- End Avalanche Problems-->
<div class="avalanche-problem-row">
   <h2><?php print t('Recent Observations:'); ?></h2>
   <?php
      unset($form['field_recent_activity']['und']['#title']);
      print render($form['field_recent_activity']); ?>
   <div id="recent-obs" class="">
      <?php echo views_embed_view('media_gallery', 'block_1'); ?>
   </div>
</div>
<div class="avalanche-problem-row">
   <h2><?php print t('Weather and Current Conditions'); ?></h2>
   <div>
      <?php print render($form['field_mountain_weather']); ?>
   </div>
   <table border="0" cellpadding="5" cellspacing="5" width="737">
      <tbody>
         <tr>
            <th colspan="2" scope="col"><?php print $current_wx_desc;?>:</th>
         </tr>
         <tr>
            <td width="384"><?php print t('0600 temperature:'); ?></td>
            <td width="318"><?php unset($form['field_temp8700']['und'][0]['value']['#title']); ?>
               <?php print render($form['field_temp8700']);?> <?php print t('deg. F.'); ?>
            </td>
         </tr>
         <tr>
            <td><?php print t('Max. temperature in the last 24 hours:'); ?></td>
            <td><?php
               unset($form['field_hr24maxtemp']['und'][0]['value']['#title']);
               print render($form['field_hr24maxtemp']);
               ?> <?php print t('deg. F.'); ?></td>
         </tr>
         <tr>
            <td><?php print t('Average wind direction during the last 24 hours:'); ?></td>
            <td><?php
               unset($form['field_hr24winddir']['und'][0]['value']['#title']);
               print render($form['field_hr24winddir']);
               ?></td>
         </tr>
         <tr>
            <td><?php print t('Average wind speed during the last 24 hours:'); ?></td>
            <td><?php
               unset($form['field_hr24windspeed']['und'][0]['value']['#title']);
               print render($form['field_hr24windspeed']);
               ?> <?php print t('mph'); ?></td>
         </tr>
         <tr>
            <td><?php print t('Maximum wind gust in the last 24 hours:'); ?></td>
            <td><?php
               unset($form['field_hr24maxgust']['und'][0]['value']['#title']);
               print render($form['field_hr24maxgust']);
               ?> <?php print t('mph'); ?></td>
         </tr>
         <tr>
            <td><?php print t('New snowfall in the last 24 hours:'); ?></td>
            <td><?php
               unset($form['field_hr24snowfall']['und'][0]['value']['#title']);
               print render($form['field_hr24snowfall']);
               ?> <?php print t('inches'); ?>
            </td>
         </tr>
         <tr>
            <td><?php print t('Total snow depth:'); ?></td>
            <td><?php
               unset($form['field_totalsnowdepth']['und'][0]['value']['#title']);
               print render($form['field_totalsnowdepth']);
               ?> <?php print t('inches'); ?>
            </td>
         </tr>
      </tbody>
   </table>
   <table width="100%" cellspacing="1" border="1" cellpadding="1">
      <tbody>
         <tr>
            <th colspan="4" bordercolor="#7E290B"><?php print t('2 Day Mountain Weather Forecast:'); ?></th>
         </tr>
         <tr>
            <td colspan="4" align="center"><strong><?php print $wx_low;?></strong></td>
         </tr>
         <tr>
            <td></td>
            <td><?php print t('Today'); ?></td>
            <td><?php print t('Tonight'); ?></td>
            <td><?php print t('Tomorrow'); ?></td>
         </tr>
         <tr>
            <td><?php print t('Weather:'); ?></td>
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
            <td><?php print t('Temperatures:'); ?></td>
            <td><?php
               unset($form['field_today7to8temp']['und'][0]['value']['#title']);
               print render($form['field_today7to8temp']);
               ?> <?php print t('deg. F.'); ?></td>
            <td><?php
               unset($form['field_tonight7to8temp']['und'][0]['value']['#title']);
               print render($form['field_tonight7to8temp']);
               ?> <?php print t('deg. F.'); ?></td>
            <td><?php
               unset($form['field_tomorrow7to8temp']['und'][0]['value']['#title']);
               print render($form['field_tomorrow7to8temp']);
               ?> <?php print t('deg. F.'); ?></td>
         </tr>
         <tr>
            <td><?php print t('Wind direction:'); ?></td>
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
            <td><?php print t('Wind speed:'); ?></td>
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
            <td><?php print t('Expected snowfall:'); ?></td>
            <td><?php
               unset($form['field_today7to8snow']['und'][0]['value']['#title']);
               print render($form['field_today7to8snow']);
               ?> <?php print t('in.'); ?></td>
            <td><?php
               unset($form['field_tonight7to8snow']['und'][0]['value']['#title']);
               print render($form['field_tonight7to8snow']);
               ?> <?php print t('in.'); ?></td>
            <td><?php
               unset($form['field_tomorrow7to8snow']['und'][0]['value']['#title']);
               print render($form['field_tomorrow7to8snow']);
               ?> <?php print t('in.'); ?></td>
         </tr>
         <tr>
            <td colspan="4" align="center"><strong><?php print $wx_high;?>:</strong></td>
         </tr>
         <tr>
            <td></td>
            <td><?php print t('Today'); ?></td>
            <td><?php print t('Tonight'); ?></td>
            <td><?php print t('Tomorrow'); ?></td>
         </tr>
         <tr>
            <td><?php print t('Weather:'); ?></td>
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
            <td><?php print t('Temperatures:'); ?></td>
            <td><?php
               unset($form['field_today8to9temp']['und'][0]['value']['#title']);
               print render($form['field_today8to9temp']);
               ?> <?php print t('deg. F.'); ?></td>
            <td><?php
               unset($form['field_tonight8to9temp']['und'][0]['value']['#title']);
               print render($form['field_tonight8to9temp']);
               ?> <?php print t('deg. F.'); ?></td>
            <td><?php
               unset($form['field_tomorrow8to9temp']['und'][0]['value']['#title']);
               print render($form['field_tomorrow8to9temp']);
               ?> <?php print t('deg. F.'); ?></td>
         </tr>
         <tr>
            <td><?php print t('Wind direction:'); ?></td>
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
            <td><?php print t('Wind speed:'); ?></td>
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
            <td><?php print t('Expected snowfall:'); ?></td>
            <td><?php
               unset($form['field_today8to9snow']['und'][0]['value']['#title']);
               print render($form['field_today8to9snow']);
               ?> <?php print t('in.'); ?></td>
            <td><?php
               unset($form['field_tonight8to9snow']['und'][0]['value']['#title']);
               print render($form['field_tonight8to9snow']);
               ?> <?php print t('in.'); ?></td>
            <td><?php
               unset($form['field_tomorrow8to9snow']['und'][0]['value']['#title']);
               print render($form['field_tomorrow8to9snow']);
               ?> <?php print t('in.'); ?></td>
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
   
    //print '<pre>';
    //print_r($form);
    //print '</pre>';
   ?>
