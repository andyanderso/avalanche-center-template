<table class="obs-pages">
 <tr>
   <th colspan=2>
     <div><?php print render($form['title']); ?></div>
   </th>
 </tr>
 <tr>
   <td>
     <div><?php print render($form['field_region']); ?></div>
   </td>
   <td>
     <div><?php print render($form['field_ob_loc_name']);?></div>
   </td>
 </tr>
 <tr>
   <td><?php print render($form['field_observer_name']);?>
   </td>
   <td><?php print render($form['field_share_this_observation_']);?>
   </td>

 </tr>
 <tr>
   <td><?php print render($form['field_email']);?>
   </td>
   <td ><?php print render($form['field_published_ob']);?>
   </td>
 </tr>
  <tr>
   <td><?php print render($form['field_ob_date_time']);?>
   </td>
   <td>
     <?php print render($form['field_is_this_an_avalanche_obser']);?>
     <?php print render($form['field_position']);?>
   </td>
 </tr>
</table>

<?php
 print "<div class='obs-pages'>";
 print drupal_render_children($form);
 print "</div>";

  //Enable below to show all Array Variables of Form

// print '<pre>';
// print_r($form);
// print '</pre>';
?>
