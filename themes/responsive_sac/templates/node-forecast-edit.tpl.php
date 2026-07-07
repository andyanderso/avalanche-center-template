<script type="text/javascript" src="/themes/responsive_sac/js/navigateawayalert.js"></script>
<div>
<!--Overall danger dropdown menu-->
  <?php
      print drupal_render($form['field_overalldanger']);
    ?>
<!--end of overall danger menu-->
</div>
<div>
<!--SAB checkbox-->
  <?php
      print drupal_render($form['field_sab']);
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
		    unset($form['field_abnorthwest']['value']['#title']);
			print drupal_render($form['field_abnorthwest']);
		?>
	</td>
	<td>
		<?php
			unset($form['field_abnorth']['value']['#title']);
			print drupal_render($form['field_abnorth']);
		?>
	</td>
    <td>
		<?php
			unset($form['field_abnortheast']['value']['#title']);
			print drupal_render($form['field_abnortheast']);
		?>
	</td>
	<td>
		<?php
			unset($form['field_abeast']['value']['#title']);
			print drupal_render($form['field_abeast']);
		?>
	</td>
	<td>
		<?php
			unset($form['field_absoutheast']['value']['#title']);
			print drupal_render($form['field_absoutheast']);
		?>
	</td>
	<td>
		<?php
			unset($form['field_absouth']['value']['#title']);
			print drupal_render($form['field_absouth']);
		?>
	</td>
	<td>
		<?php
			unset($form['field_absouthwest']['value']['#title']);
			print drupal_render($form['field_absouthwest']);
		?>
	</td>
	<td>
		<?php
			unset($form['field_abwest']['value']['#title']);
			print drupal_render($form['field_abwest']);
		?>
	</td>
  </tr>  
  <!-- end above treeline danger rating options-->
  <!--below treeline danger rating options-->
  <tr>
    <td>Below treeline:</td>
    <td>
		<?php
			unset($form['field_bnorthwest']['value']['#title']);
			print drupal_render($form['field_bnorthwest']);
		?>
	</td>
	<td>
		<?php
			unset($form['field_bnorth']['value']['#title']);
			print drupal_render($form['field_bnorth']);
		?>
	</td>
    <td>
		<?php
		unset($form['field_bnortheast']['value']['#title']);
		print drupal_render($form['field_bnortheast']);
		?>
	</td>
	<td>
		<?php
			unset($form['field_beast']['value']['#title']);
			print drupal_render($form['field_beast']);
		?>
	</td>
	<td>
		<?php
			unset($form['field_bsoutheast']['value']['#title']);
			print drupal_render($form['field_bsoutheast']);
		?>
	</td>
	<td>
		<?php
			unset($form['field_bsouth']['value']['#title']);
			print drupal_render($form['field_bsouth']);
		?>
	</td>
	<td>
		<?php
		unset($form['field_bsouthwest']['value']['#title']);
		print drupal_render($form['field_bsouthwest']);
		?>
	</td>
	<td>
		<?php
		unset($form['field_bwest']['value']['#title']);
		print drupal_render($form['field_bwest']);
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
  <!--bottom line text area--><?php unset($form['field_bottomlinetext']['value']['#title']); ?>
		<?php
			print drupal_render($form['field_bottomlinetext']);
		?>

<br />
  <!--end of bottom line text area-->

		<?php
			print drupal_render($form['field_forecastdiscussion']);
		?>
</div>
<div id="forecastweaterdata">
  <table border="0" cellpadding="5" cellspacing="5" width="737">
    <tbody>
      <tr>
        <th colspan="2" scope="col">Today's Weather Observations Along the Sierra Crest Between 8200 ft and 8800 ft.:</th>

      </tr>
      <tr>
        <td width="384">0600 temperature:</td>
        <td width="318"><?php unset($form['field_temp8700'][0]['value']['#title']); ?>
			<?php print drupal_render($form['field_temp8700']);?>deg. F.</td>
      </tr>
      <tr>
        <td>Max. temperature in the last 24 hours:</td>

        <td><?php
			unset($form['field_hr24maxtemp'][0]['value']['#title']);
			print drupal_render($form['field_hr24maxtemp']);
		?>deg. F.</td>
      </tr>
      <tr>
        <td>Average wind direction during the last 24 hours:</td>
        <td><?php
			unset($form['field_hr24winddir'][0]['value']['#title']);
			print drupal_render($form['field_hr24winddir']);
		?></td>
      </tr>
      <tr>

        <td>Average wind speed during the last 24 hours:</td>
        <td><?php
			unset($form['field_hr24windspeed'][0]['value']['#title']);
			print drupal_render($form['field_hr24windspeed']);
		?>mph</td>
      </tr>
      <tr>
        <td>Maximum wind gust in the last 24 hours:</td>
        <td><?php
			unset($form['field_hr24maxgust'][0]['value']['#title']);
			print drupal_render($form['field_hr24maxgust']);
		?>mph</td>
      </tr>

      <tr>
        <td>New snowfall in the last 24 hours:</td>
        <td><?php
			unset($form['field_hr24snowfall'][0]['value']['#title']);
			print drupal_render($form['field_hr24snowfall']);
		?>inches
	</td>
      </tr>
      <tr>
        <td>Total snow depth:</td>
        <td><?php
			unset($form['field_totalsnowdepth'][0]['value']['#title']);
			print drupal_render($form['field_totalsnowdepth']);
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
        <td colspan="4" align="center">For 7000-8000 ft:</td>
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
			unset($form['field_today7to8weather'][0]['value']['#title']);
			print drupal_render($form['field_today7to8weather']);
		?></td>
        <td><?php
			unset($form['field_tonight7to8weather'][0]['value']['#title']);
			print drupal_render($form['field_tonight7to8weather']);
		?></td>
        <td><?php
			unset($form['field_tomorrow7to8weather'][0]['value']['#title']);
			print drupal_render($form['field_tomorrow7to8weather']);
		?></td>

      </tr>
      <tr>
        <td>Temperatures:</td>
        <td><?php
			unset($form['field_today7to8temp'][0]['value']['#title']);
			print drupal_render($form['field_today7to8temp']);
		?>deg. F.</td>
        <td><?php
			unset($form['field_tonight7to8temp'][0]['value']['#title']);
			print drupal_render($form['field_tonight7to8temp']);
		?>deg. F.</td>
        <td><?php
			unset($form['field_tomorrow7to8temp'][0]['value']['#title']);
			print drupal_render($form['field_tomorrow7to8temp']);
		?>deg. F.</td>
      </tr>

      <tr>
        <td>Wind direction:</td>
        <td><?php
			unset($form['field_today7to8winddirection'][0]['value']['#title']);
			print drupal_render($form['field_today7to8winddirection']);
		?></td>
        <td><?php
			unset($form['field_tonight7to8winddirection'][0]['value']['#title']);
			print drupal_render($form['field_tonight7to8winddirection']);
		?></td>
        <td><?php
			unset($form['field_tomorrow7to8winddirection'][0]['value']['#title']);
			print drupal_render($form['field_tomorrow7to8winddirection']);
		?></td>
      </tr>
      <tr>
        <td>Wind speed:</td>

        <td><?php
			unset($form['field_today7to8windspeed'][0]['value']['#title']);
			print drupal_render($form['field_today7to8windspeed']);
		?></td>
        <td><?php
			unset($form['field_tonight7to8windspeed'][0]['value']['#title']);
			print drupal_render($form['field_tonight7to8windspeed']);
		?></td>
        <td><?php
			unset($form['field_tomorrow7to8windspeed'][0]['value']['#title']);
			print drupal_render($form['field_tomorrow7to8windspeed']);
		?></td>
      </tr>
      <tr>
        <td>Expected snowfall:</td>
        <td><?php
			unset($form['field_today7to8snow'][0]['value']['#title']);
			print drupal_render($form['field_today7to8snow']);
		?>in.</td>
        <td><?php
			unset($form['field_tonight7to8snow'][0]['value']['#title']);
			print drupal_render($form['field_tonight7to8snow']);
		?>in.</td>

        <td><?php
			unset($form['field_tomorrow7to8snow'][0]['value']['#title']);
			print drupal_render($form['field_tomorrow7to8snow']);
		?>in.</td>
      </tr>
      <tr>
        <td colspan="4" align="center">For 8000-9000 ft:</td>
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
			unset($form['field_today8to9weather'][0]['value']['#title']);
			print drupal_render($form['field_today8to9weather']);
		?></td>
        <td><?php
			unset($form['field_tonight8to9weather'][0]['value']['#title']);
			print drupal_render($form['field_tonight8to9weather']);
		?></td>
        <td><?php
			unset($form['field_tomorrow8to9weather'][0]['value']['#title']);
			print drupal_render($form['field_tomorrow8to9weather']);
		?></td>

      </tr>
      <tr>
        <td>Temperatures:</td>
        <td><?php
			unset($form['field_today8to9temp'][0]['value']['#title']);
			print drupal_render($form['field_today8to9temp']);
		?>deg. F.</td>
        <td><?php
			unset($form['field_tonight8to9temp'][0]['value']['#title']);
			print drupal_render($form['field_tonight8to9temp']);
		?>deg. F.</td>
        <td><?php
			unset($form['field_tomorrow8to9temp'][0]['value']['#title']);
			print drupal_render($form['field_tomorrow8to9temp']);
		?>deg. F.</td>
      </tr>

      <tr>
        <td>Wind direction:</td>
        <td><?php
			unset($form['field_today8to9winddirection'][0]['value']['#title']);
			print drupal_render($form['field_today8to9winddirection']);
		?></td>
        <td><?php
			unset($form['field_tonight8to9winddirection'][0]['value']['#title']);
			print drupal_render($form['field_tonight8to9winddirection']);
		?></td>
        <td><?php
			unset($form['field_tomorrow8to9winddirection'][0]['value']['#title']);
			print drupal_render($form['field_tomorrow8to9winddirection']);
		?></td>
      </tr>
      <tr>
        <td>Wind speed:</td>

        <td><?php
			unset($form['field_today8to9windspeed'][0]['value']['#title']);
			print drupal_render($form['field_today8to9windspeed']);
		?></td>
        <td><?php
			unset($form['field_tonight8to9windspeed'][0]['value']['#title']);
			print drupal_render($form['field_tonight8to9windspeed']);
		?></td>
        <td><?php
			unset($form['field_tomorrow8to9windspeed'][0]['value']['#title']);
			print drupal_render($form['field_tomorrow8to9windspeed']);
		?></td>
      </tr>
      <tr>
        <td>Expected snowfall:</td>
        <td><?php
			unset($form['field_today8to9snow'][0]['value']['#title']);
			print drupal_render($form['field_today8to9snow']);
		?>in.</td>
        <td><?php
			unset($form['field_tonight8to9snow'][0]['value']['#title']);
			print drupal_render($form['field_tonight8to9snow']);
		?>in.</td>

        <td><?php
			unset($form['field_tomorrow8to9snow'][0]['value']['#title']);
			print drupal_render($form['field_tomorrow8to9snow']);
		?>in.</td>
      </tr>
    </tbody>
  </table>
</div>
<br />
<div>
<?php
			unset($form['author'][0]['value']['#title']);
			print drupal_render($form['author']);
		?>
</div>
<?php
  print drupal_render($form);

  //Enable below to show all Array Variables of Form

  //print '<pre>';
  //print_r($form);
  //print '</pre>';
?>
