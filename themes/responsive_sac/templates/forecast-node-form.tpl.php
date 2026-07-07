<script type="text/javascript" src="/sites/all/default/javascripts/navigateawayalert.js"></script>
<?php
$nws_name = theme_get_setting('local_nws_name');
$nws_url = theme_get_setting('local_nws_url');
$wx_low = theme_get_setting('wx_elevation_low');
$wx_high = theme_get_setting('wx_elevation_high');
$current_wx_desc = theme_get_setting('current_wx_conditions_desc');
?>
<div>
<!--Overall danger dropdown menu-->
  <?php
      print render($form['field_overalldanger']);
    ?>
<!--end of overall danger menu-->
</div>
<div>
<!--SAB checkbox-->
  <?php
      print render($form['field_sab']);
    ?>
<!--end of SAB checkbox-->
</div>
<br />
<div>
<!--danger rose ratings entry menus-->
  <!--above treeline danger rating options-->
<h4>Danger rating by aspect and elevation:</h4>

<table id="dangerroseinput" cellpadding="3" border="1">
<tbody>	
  <tr>
    <th>Elevation:</th>
    <th>NW:</th>
    <th>N:</th>
    <th>NE:</th>
    <th>E:</th>
    <th>SE:</th>
    <th>S:</th>
    <th>SW:</th>
    <th>W:</th>
  </tr>
  <tr>
  <!-- begin above treeline danger rating options-->
    <td>Near and above treeline:</td>
    <td>
		<?php
		    unset($form['field_abnorthwest']['und'][0]['value']['#title']);
			print render($form['field_abnorthwest']);
		?>
	</td>
	<td>
		<?php
			unset($form['field_abnorth']['und']['value']['#title']);
			print render($form['field_abnorth']);
		?>
	</td>
    <td>
		<?php
			unset($form['field_abnortheast']['und']['value']['#title']);
			print render($form['field_abnortheast']);
		?>
	</td>
	<td>
		<?php
			unset($form['field_abeast']['und']['value']['#title']);
			print render($form['field_abeast']);
		?>
	</td>
	<td>
		<?php
			unset($form['field_absoutheast']['und']['value']['#title']);
			print render($form['field_absoutheast']);
		?>
	</td>
	<td>
		<?php
			unset($form['field_absouth']['und']['value']['#title']);
			print render($form['field_absouth']);
		?>
	</td>
	<td>
		<?php
			unset($form['field_absouthwest']['und']['value']['#title']);
			print render($form['field_absouthwest']);
		?>
	</td>
	<td>
		<?php
			unset($form['field_abwest']['und']['value']['#title']);
			print render($form['field_abwest']);
		?>
	</td>
  </tr>  
  <!-- end above treeline danger rating options-->
  <!--below treeline danger rating options-->
  <tr>
    <td>Below treeline:</td>
    <td>
		<?php
			unset($form['field_bnorthwest']['und']['value']['#title']);
			print render($form['field_bnorthwest']);
		?>
	</td>
	<td>
		<?php
			unset($form['field_bnorth']['und']['value']['#title']);
			print render($form['field_bnorth']);
		?>
	</td>
    <td>
		<?php
		unset($form['field_bnortheast']['und']['value']['#title']);
		print render($form['field_bnortheast']);
		?>
	</td>
	<td>
		<?php
			unset($form['field_beast']['und']['value']['#title']);
			print render($form['field_beast']);
		?>
	</td>
	<td>
		<?php
			unset($form['field_bsoutheast']['und']['value']['#title']);
			print render($form['field_bsoutheast']);
		?>
	</td>
	<td>
		<?php
			unset($form['field_bsouth']['und']['value']['#title']);
			print render($form['field_bsouth']);
		?>
	</td>
	<td>
		<?php
		unset($form['field_bsouthwest']['und']['value']['#title']);
		print render($form['field_bsouthwest']);
		?>
	</td>
	<td>
		<?php
		unset($form['field_bwest']['und']['value']['#title']);
		print render($form['field_bwest']);
		?>
	</td>
  </tr> 
</tbody>
  <!--end below treeline danger rating options-->
</table>
</div>
<!--end danger rose ratings entry menus-->
<br />
<!--Advisory text area-->
<div>
  <!--bottom line text area--><?php unset($form['field_bottomlinetext']['und']['value']['#title']); ?>
		<?php
			print render($form['field_bottomlinetext']);
		?>

<br />
  <!--end of bottom line text area-->

		<?php
			print render($form['field_forecastdiscussion']);
		?>
</div>
<div id="forecastweaterdata">
  <table border="0" cellpadding="5" cellspacing="5" width="737">
    <tbody>
      <tr>
        <th colspan="2" scope="col"><?php print $current_wx_desc;?>:</th>

      </tr>
      <tr>
        <td width="384">0600 temperature:</td>
        <td width="318"><?php unset($form['field_temp8700']['und'][0]['value']['#title']); ?>
			<?php print render($form['field_temp8700']);?>deg. F.</td>
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
        <td colspan="4" align="center"><?php print $wx_low;?>:</td>
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
        <td colspan="4" align="center"><?php print $wx_high;?>:</td>
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
<br />
<div>
<?php
			unset($form['author']['und'][0]['value']['#title']);
			print render($form['author']);
		?>
</div>
<?php
  print drupal_render_children($form);

  //Enable below to show all Array Variables of Form

  //print '<pre>';
  //print_r($form);
  //print '</pre>';
?>
