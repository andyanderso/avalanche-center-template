<?php
      $author = user_load($node->uid);
      $company = field_get_items('user', $author, 'field__company_link');
?>
<table class="obs-pages">
 <tr>
   <th id="ob-header" colspan="2">
     <div><?php print render($content['field_ob_loc_name']);?></div>
     <div><?php print render($content['field_region']); ?></div>
     <div><?php print render($content['field_ob_date_time']);?></div>
     <div><?php print render($content['field_observer_name']);?></div>
     <div><?php print render($content['field_email']);?></div>
   </th>
 </tr>
 <tr>
   <th id="ob-header">
     <div><?php print render($content['field_location']);?></div>
   </th>
   <td>
       <?php print render($content['field_is_this_an_avalanche_obser']);?>
       <?php print render($content['field_share_this_observation_']);?><br>
       <?php print render($content['field_published_ob']);?><br>
       <?php print render($content['field_conditions_alerts_tax_term']);?><br>
       <?php print render($content['field_terrain_alerts']);?><br>
   </td>
 </tr>
</table>
<div id="position" class="<?php print_r($node->field_position['und'][0]['value']);?>">
	<?php hide ($content['field_position']);
	print '<strong>'.render($content['field_position']['#title']).": ".'</strong>'.render($content['field_position']['#items'][0]['value']);
	if ($content['field_position']['#items'][0]['value'] == 'Guide'){
		print ' at '.'<a target="_blank" href="'.render($company[0]['url']).'">'.render($company[0]['title']).'</a>';
	}
	elseif ($content['field_position']['#items'][0]['value'] == 'Educator'){
	print ' at '.'<a target="_blank" href="'.render($company[0]['url']).'">'.render($company[0]['title']).'</a>';
	}
	?>
</div>
<?php
 print "<div class='obs-pages'>";
 print render($content);
 print "</div>";
  //Enable below to show all Array Variables of Form

//print '<pre>';
//print_r($node or $content);
//print '</pre>';
?>
