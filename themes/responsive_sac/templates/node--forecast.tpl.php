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
<!--Start Teaser Code-->
 <?php if ($teaser): ?>
<h3>
<?php 
$forecastdate = date("F j, Y", $node->created);
if (date('Y-m-d') == date('Y-m-d', $node->created)) {
print "Today's Avalanche Advisory:";
}
else { 
echo 'Avalanche Advisory published on '.$forecastdate.':';
} ?>
</h3>
<hr style="height:3px;color:#;background-color:#133463;border:none;" />
<!--Danger Rose-->
<table >
 <tr  style="background-color:#ffffff">
   <td rowspan="3">
<?php  $nodeid = $node->nid;
 $dangerrose = '/dangerrose.png?a='.$nodeid;
$norating = 'http://www.sierraavalanchecenter.org/sites/default/files/images/danger_icons_and_bars/0_nodangerrate.png';
$summer = 'http://www.sierraavalanchecenter.org/sites/default/files/images/danger_icons_and_bars/6_summer.jpg';
?>
<a href="http://avalanche.state.co.us/pub/fx_danger_rose.php"><img src = "

<?php if ($content['field_overalldanger']['#items']['0']['value'] == 0)
{
echo $norating;
}
elseif ($content['field_overalldanger']['#items']['0']['value'] == 6)
{
echo $summer;
}
else 
{
echo $dangerrose;
}
?>
" />
</a>
<!-- End Danger Rose -->
  </td>
  <td><strong><?php echo date("F j, Y \a\\t G:i a", $node->created) ?></br></strong>
  </td>
 </tr>
 <tr  style="background-color:#ffffff">
   <td>
<?php print render($content['field_bottomlinetext']['#items']['0']['value']); ?>
   </td>
  </tr>
 <tr  style="background-color:#ffffff">
   <td>
<?php
$low = "http://www.sierraavalanchecenter.org/sites/default/files/images/danger_icons_and_bars/low_bar.png";
$moderate = "http://www.sierraavalanchecenter.org/sites/default/files/images/danger_icons_and_bars/moderate_bar.png";
$considerable = "http://www.sierraavalanchecenter.org/sites/default/files/images/danger_icons_and_bars/considerable_bar.png";
$high = "http://www.sierraavalanchecenter.org/sites/default/files/images/danger_icons_and_bars/high_bar.png";
$extreme = "http://www.sierraavalanchecenter.org/sites/default/files/images/danger_icons_and_bars/extreme_bar.png";
?>
<a href="http://www.avalanche.org/danger_card.php" target="_blank"> <img alt="" src="<?php if ($content['field_overalldanger']['#items']['0']['value'] == 1) {
print $low;
}
elseif ($content['field_overalldanger']['#items']['0']['value'] == 2) {
print $moderate;
}
elseif ($content['field_overalldanger']['#items']['0']['value'] == 3) {
print $considerable;
}
elseif ($content['field_overalldanger']['#items']['0']['value'] == 4) {
print $high;
}
elseif ($content['field_overalldanger']['#items']['0']['value'] == 5) {
print $extreme;
}
?>" /></a>
   </td>
  </tr>
</table>

<!--end Teaser code-->
<?php else: ?>
<?php unset($node->title)?>
<!--disclaimer-->
<div style="text-align:center; font-size:11px">
This avalanche advisory is provided through a partnership between the Tahoe National Forest and the Sierra Avalanche Center. This advisory covers the Central Sierra Nevada Mountains between Yuba Pass on the north and Ebbetts Pass on the south. <a target="_blank" href="http://www.sierraavalanchecenter.org/forecast-area-map">Click here for a map of the forecast area.</a> This advisory applies only to backcountry areas outside established ski area boundaries. This advisory describes general avalanche conditions and local variations always occur. This advisory expires 24 hours after the posted time unless otherwise noted. The information in this advisory is provided by the USDA Forest Service who is solely responsible for its content.
<br />
<br />
<br />
</div>
<!-- end disclaimer-->
<h2>
<?php 
$forecastdate = date("F j, Y", $node->created);
if (date('Y-m-d') == date('Y-m-d', $node->created)) {
print "Today's Avalanche Advisory:";
}
else { 
echo 'This Avalanche Advisory was published on '.$forecastdate.':';
} ?>
</h2>
<hr style="height:3px;color:#;background-color:#133463;border:none;" />
<!--Danger Rose-->
<table class="forecast">
 <tr  style="background-color:#ffffff">
   <td rowspan="3">
<?php  $nodeid = $node->nid;
$dangerrose = '/dangerrose.png?a='.$nodeid;
$norating = 'http://www.sierraavalanchecenter.org/sites/default/files/images/danger_icons_and_bars/0_nodangerrate.png';
$summer = 'http://www.sierraavalanchecenter.org/sites/default/files/images/danger_icons_and_bars/6_summer.jpg';
?>
<a href="http://avalanche.state.co.us/pub/fx_danger_rose.php"><img src = "

<?php if ($content['field_overalldanger']['#items']['0']['value'] == 0)
{
echo $norating;
}
elseif ($content['field_overalldanger']['#items']['0']['value'] == 6)
{
echo $summer;
}
else 
{
echo $dangerrose;
}
?>
" />
</a>
<!-- End Danger Rose -->
  </td>
  <td><strong><?php echo date("F j, Y \a\\t G:i a", $node->created) ?></strong>
  </td>
 </tr>
 <tr  style="background-color:#ffffff">
   <td>
<?php print render($content['field_bottomlinetext']['#items']['0']['value']) ?>
   </td>
  </tr>
 <tr  style="background-color:#ffffff">
   <td>
<?php
$low = "http://www.sierraavalanchecenter.org/sites/default/files/images/danger_icons_and_bars/low_bar.png";
$moderate = "http://www.sierraavalanchecenter.org/sites/default/files/images/danger_icons_and_bars/moderate_bar.png";
$considerable = "http://www.sierraavalanchecenter.org/sites/default/files/images/danger_icons_and_bars/considerable_bar.png";
$high = "http://www.sierraavalanchecenter.org/sites/default/files/images/danger_icons_and_bars/high_bar.png";
$extreme = "http://www.sierraavalanchecenter.org/sites/default/files/images/danger_icons_and_bars/extreme_bar.png";
?>
<a href="http://www.avalanche.org/danger_card.php" target="_blank"> <img alt="" src="<?php if ($content['field_overalldanger']['#items']['0']['value'] == 1) {
print $low;
}
elseif ($content['field_overalldanger']['#items']['0']['value'] == 2) {
print $moderate;
}
elseif ($content['field_overalldanger']['#items']['0']['value'] == 3) {
print $considerable;
}
elseif ($content['field_overalldanger']['#items']['0']['value'] == 4) {
print $high;
}
elseif ($content['field_overalldanger']['#items']['0']['value'] == 5) {
print $extreme;
}
?>" /></a>
   </td>
  </tr>
</table>
<hr style="height:1px;color:#;background-color:#133463;border:none;" />
<h3>Forecast Discussion:</h3>
<hr style="height:3px;color:#;background-color:#133463;border:none;" />
<div>
<?php print render($content['field_forecastdiscussion']['#items']['0']['value']) ?>
</div>
<div>
<br />
<h3>The bottom line:</h3>
<?php print render($content['field_bottomlinetext']['#items']['0']['value']) ?>
<br />
</div>
<div>
<?php
 if ($node->name == 'andy') {
 print 'Andy Anderson';}
elseif ($node->name == 'brandon') {
print 'Brandon Schwartz';}
?>  - Avalanche Forecaster, Tahoe National Forest
</div>
<hr style="height:1px;color:#;background-color:#133463;border:none;" />
<div>
<br />
<h3>Weather Observations from along the Sierra Crest between 8200 ft and 8800 ft:</h3>
<table width="100%" cellspacing="5" cellpadding="5" border="0" id="Wx-obs" class="forecast">
    <tbody>
        <tr  style="background-color:#ffffff">
            <td >0600 temperature:</td>
            <td ><?php print render($content['field_temp8700']['#items']['0']['value']);?> deg. F.</td>
        </tr>
        <tr  style="background-color:#ffffff">
            <td >Max. temperature in the last 24 hours:</td>
            <td ><?php print render($content['field_hr24maxtemp']['#items']['0']['value']);?> deg. F.</td>
        </tr>
        <tr  style="background-color:#ffffff">
            <td >Average wind direction during the last 24 hours:</td>
            <td ><?php print render($content['field_hr24winddir']['#items']['0']['value']);?></td>
        </tr>
        <tr  style="background-color:#ffffff">
            <td >Average wind speed during the last 24 hours:</td>
            <td ><?php print render($content['field_hr24windspeed']['#items']['0']['value']);?> mph</td>
        </tr>
        <tr  style="background-color:#ffffff">
            <td >Maximum wind gust in the last 24 hours:</td>
            <td ><?php print render($content['field_hr24maxgust']['#items']['0']['value']);?> mph</td>
        </tr>
        <tr  style="background-color:#ffffff">
            <td >New snowfall in the last 24 hours:</td>
            <td ><?php print render($content['field_hr24snowfall']['#items']['0']['value']);?> inches</td>
        </tr>
        <tr  style="background-color:#ffffff">
            <td >Total snow depth:</td>
            <td ><?php print render($content['field_totalsnowdepth']['#items']['0']['value']);?> inches</td>
        </tr>
    </tbody>
</table>
</div>
<div>
<br />
<h3>Two-Day Mountain Weather Forecast - Produced in partnership with the <a href="http://www.wrh.noaa.gov/rev/" target="_blank">Reno NWS</a></h3>
<table width="100%" cellspacing="2" cellpadding="2" id="Wx-forecast" class="forecast">
    <tbody>
        <tr  style="background-color:#ffffff">
            <td style="text-align:center" colspan="4">
            <h3>For 7000-8000 ft:</h3>
            </td>
        </tr>
        <tr  style="background-color:#ffffff">
            <td>&nbsp;</td>
            <td style="text-align:center"><?php if (date('Y-m-d') == date('Y-m-d', $node->created)) {
print "Today";
}
else { 
echo date("l", $node->created);
} ?>:</td>
            <td style="text-align:center"><?php if (date('Y-m-d') == date('Y-m-d', $node->created)) {
print "Tonight";
}
else { 
echo date("l", $node->created)." Night";
} ?>:</td>
            <td style="text-align:center"><?php $tomorrow  = strtotime('+1 day', $node->created);
echo date("l", $tomorrow);?>:</td>
        </tr>
        <tr  style="background-color:#ffffff">
            <td width="15%" style="border-top: 3px groove rgb(126, 41, 11); border-left: 3px groove rgb(126, 41, 11); border-right: 1px solid rgb(126, 41, 11);">Weather:</td>
            <td width="28%" align="center" style="text-align:center; border-top: 3px groove rgb(126, 41, 11); border-left: 1px solid rgb(126, 41, 11); border-right: 1px solid rgb(126, 41, 11);"><?php print render($content['field_today7to8weather']['#items']['0']['value']) ?></td>
            <td width="28%" align="center" style="text-align:center; border-top: 3px groove rgb(126, 41, 11); border-left: 1px solid rgb(126, 41, 11); border-right: 1px solid rgb(126, 41, 11);"><?php print render($content['field_tonight7to8weather']['#items']['0']['value']) ?></td>
            <td width="28%" align="center" style="text-align:center; border-top: 3px groove rgb(126, 41, 11); border-left: 1px solid rgb(126, 41, 11); border-right: 3px groove rgb(126, 41, 11);"><?php print render($content['field_tomorrow7to8weather']['#items']['0']['value']) ?></td>
        </tr>
        <tr  style="background-color:#ffffff">
            <td style="border-top: 3px groove rgb(126, 41, 11); border-left: 3px groove rgb(126, 41, 11); border-right: 1px solid rgb(126, 41, 11);">Temperatures:</td>
            <td style="text-align:center; border-top: 2px solid rgb(126, 41, 11); border-left: 1px solid rgb(126, 41, 11); border-right: 1px solid rgb(126, 41, 11);"><?php print render($content['field_today7to8temp']['#items']['0']['value']) ?> deg. F.</td>
            <td  style="text-align:center; border-top: 2px solid rgb(126, 41, 11); border-left: 1px solid rgb(126, 41, 11); border-right: 1px solid rgb(126, 41, 11);"><?php print render($content['field_tonight7to8temp']['#items']['0']['value']) ?> deg. F.</td>
            <td  style="text-align:center; border-top: 2px solid rgb(126, 41, 11); border-left: 1px solid rgb(126, 41, 11); border-right: 3px groove rgb(126, 41, 11);"><?php print render($content['field_tomorrow7to8temp']['#items']['0']['value']) ?> deg. F.</td>
        </tr>
        <tr  style="background-color:#ffffff">
            <td style="border-top: 3px groove rgb(126, 41, 11); border-left: 3px groove rgb(126, 41, 11); border-right: 1px solid rgb(126, 41, 11);">Wind direction:</td>
            <td  style="text-align:center; border-top: 2px solid rgb(126, 41, 11); border-left: 1px solid rgb(126, 41, 11); border-right: 1px solid rgb(126, 41, 11);"><?php print render($content['field_today7to8winddirection']['#items']['0']['value']) ?></td>
            <td  style="text-align:center; border-top: 2px solid rgb(126, 41, 11); border-left: 1px solid rgb(126, 41, 11); border-right: 1px solid rgb(126, 41, 11);"><?php print render($content['field_tonight7to8winddirection']['#items']['0']['value']) ?></td>
            <td  style="text-align:center; border-top: 2px solid rgb(126, 41, 11); border-left: 1px solid rgb(126, 41, 11); border-right: 3px groove rgb(126, 41, 11);"><?php print render($content['field_tomorrow7to8winddirection']['#items']['0']['value']) ?></td>
        </tr>
        <tr  style="background-color:#ffffff">
            <td style="border-top: 3px groove rgb(126, 41, 11); border-left: 3px groove rgb(126, 41, 11); border-right: 1px solid rgb(126, 41, 11);">Wind speed:</td>
            <td  style="text-align:center; border-top: 2px solid rgb(126, 41, 11); border-left: 1px solid rgb(126, 41, 11); border-right: 1px solid rgb(126, 41, 11);"><?php print render($content['field_today7to8windspeed']['#items']['0']['value']) ?> </td>
            <td  style="text-align:center; border-top: 2px solid rgb(126, 41, 11); border-left: 1px solid rgb(126, 41, 11); border-right: 1px solid rgb(126, 41, 11);"><?php print render($content['field_tonight7to8windspeed']['#items']['0']['value']) ?> </td>
            <td  style="text-align:center; border-top: 2px solid rgb(126, 41, 11); border-left: 1px solid rgb(126, 41, 11); border-right: 3px groove rgb(126, 41, 11);"><?php print render($content['field_tomorrow7to8windspeed']['#items']['0']['value']) ?> </td>
        </tr>
        <tr  style="background-color:#ffffff">
            <td style="border-style: groove solid groove groove; border-color: rgb(126, 41, 11); border-width: 3px 1px 3px 3px;">Expected snowfall:</td>
            <td  style="text-align:center; border-style: solid solid groove; border-color: rgb(126, 41, 11); border-width: 2px 1px 3px;"><?php print render($content['field_today7to8snow']['#items']['0']['value']) ?> in.</td>
            <td  style="text-align:center; border-style: solid solid groove; border-color: rgb(126, 41, 11); border-width: 2px 1px 3px;"><?php print render($content['field_tonight7to8snow']['#items']['0']['value']) ?> in.</td>
            <td  style="text-align:center; border-style: solid groove groove solid; border-color: rgb(126, 41, 11); border-width: 2px 3px 3px 1px;"><?php print render($content['field_tomorrow7to8snow']['#items']['0']['value']) ?> in.</td>
        </tr>
        <tr  style="background-color:#ffffff">
            <td style="text-align:center" colspan="4">
            <h3>For 8000-9000 ft:</h3>
            </td>
        </tr>
        <tr  style="background-color:#ffffff">
            <td>&nbsp;</td>
            <td style="text-align:center"><?php if (date('Y-m-d') == date('Y-m-d', $node->created)) {
print "Today";
}
else { 
echo date("l", $node->created);
} ?>:</td>
            <td style="text-align:center"><?php if (date('Y-m-d') == date('Y-m-d', $node->created)) {
print "Tonight";
}
else { 
echo date("l", $node->created)." Night";
} ?>:</td>
            <td style="text-align:center"><?php $tomorrow  = strtotime('+1 day', $node->created);
echo date("l", $tomorrow);?>:</td>
        </tr>
        <tr  style="background-color:#ffffff">
            <td style="border-top: 3px groove rgb(126, 41, 11); border-left: 3px groove rgb(126, 41, 11); border-right: 1px solid rgb(126, 41, 11);">Weather:</td>
            <td  style="text-align:center; border-top: 3px groove rgb(126, 41, 11); border-left: 1px solid rgb(126, 41, 11); border-right: 1px solid rgb(126, 41, 11);"><?php print render($content['field_today8to9weather']['#items']['0']['value']) ?></td>
            <td  style="text-align:center; border-top: 3px groove rgb(126, 41, 11); border-left: 1px solid rgb(126, 41, 11); border-right: 1px solid rgb(126, 41, 11);"><?php print  render($content['field_tonight8to9weather']['#items']['0']['value']) ?></td>            
            <td  style="text-align:center; border-top: 3px groove rgb(126, 41, 11); border-left: 1px solid rgb(126, 41, 11); border-right: 3px groove rgb(126, 41, 11);"><?php print render($content['field_tomorrow8to9weather']['#items']['0']['value']) ?></td>
        </tr>
        <tr  style="background-color:#ffffff">
            <td style="border-top: 2px solid rgb(126, 41, 11); border-left: 3px groove rgb(126, 41, 11); border-right: 1px solid rgb(126, 41, 11);">Temperatures:</td>
            <td  style="text-align:center; border-top: 2px solid rgb(126, 41, 11); border-left: 1px solid rgb(126, 41, 11); border-right: 1px solid rgb(126, 41, 11);"><?php print render($content['field_today8to9temp']['#items']['0']['value']) ?> deg. F.</td>
            <td  style="text-align:center; border-top: 2px solid rgb(126, 41, 11); border-left: 1px solid rgb(126, 41, 11); border-right: 1px solid rgb(126, 41, 11);"><?php print render($content['field_tonight8to9temp']['#items']['0']['value']) ?> deg. F.</td>
            <td  style="text-align:center; border-top: 2px solid rgb(126, 41, 11); border-left: 1px solid rgb(126, 41, 11); border-right: 3px groove rgb(126, 41, 11);"><?php print render($content['field_tomorrow8to9temp']['#items']['0']['value']) ?> deg. F.</td>
        </tr>
        <tr  style="background-color:#ffffff">
            <td style="border-top: 2px solid rgb(126, 41, 11); border-left: 3px groove rgb(126, 41, 11); border-right: 1px solid rgb(126, 41, 11);">Wind direction:</td>
            <td  style="text-align:center; border-top: 2px solid rgb(126, 41, 11); border-left: 1px solid rgb(126, 41, 11); border-right: 1px solid rgb(126, 41, 11);"><?php print render($content['field_today8to9winddirection']['#items']['0']['value']) ?></td>
            <td  style="text-align:center; border-top: 2px solid rgb(126, 41, 11); border-left: 1px solid rgb(126, 41, 11); border-right: 1px solid rgb(126, 41, 11);"><?php print render($content['field_tonight8to9winddirection']['#items']['0']['value']) ?></td>
            <td  style="text-align:center; border-top: 2px solid rgb(126, 41, 11); border-left: 1px solid rgb(126, 41, 11); border-right: 3px groove rgb(126, 41, 11);"><?php print render($content['field_tomorrow8to9winddirection']['#items']['0']['value']) ?></td>
        </tr>
        <tr  style="background-color:#ffffff">
            <td style="border-top: 2px solid rgb(126, 41, 11); border-left: 3px groove rgb(126, 41, 11); border-right: 1px solid rgb(126, 41, 11);">Wind speed:</td>
            <td  style="text-align:center; border-top: 2px solid rgb(126, 41, 11); border-left: 1px solid rgb(126, 41, 11); border-right: 1px solid rgb(126, 41, 11);"><?php print render($content['field_today8to9windspeed']['#items']['0']['value']) ?> </td>
            <td  style="text-align:center; border-top: 2px solid rgb(126, 41, 11); border-left: 1px solid rgb(126, 41, 11); border-right: 1px solid rgb(126, 41, 11);"><?php print render($content['field_tonight8to9windspeed']['#items']['0']['value']) ?> </td>
            <td  style="text-align:center; border-top: 2px solid rgb(126, 41, 11); border-left: 1px solid rgb(126, 41, 11); border-right: 3px groove rgb(126, 41, 11);"><?php print render($content['field_tomorrow8to9windspeed']['#items']['0']['value']) ?> </td>
        </tr>
        <tr  style="background-color:#ffffff">
            <td style="border-style: groove solid groove groove; border-color: rgb(126, 41, 11); border-width: 3px 1px 3px 3px;">Expected snowfall:</td>
            <td  style="text-align:center; border-style: solid solid groove; border-color: rgb(126, 41, 11); border-width: 2px 1px 3px;"><?php print render($content['field_today8to9snow']['#items']['0']['value']) ?> in.</td>
            <td  style="text-align:center; border-style: solid solid groove; border-color: rgb(126, 41, 11); border-width: 2px 1px 3px;"><?php print render($content['field_tonight8to9snow']['#items']['0']['value']) ?> in.</td>
            <td  style="text-align:center; border-style: solid groove groove solid; border-color: rgb(126, 41, 11); border-width: 2px 3px 3px 1px;"><?php print render($content['field_tomorrow8to9snow']['#items']['0']['value']) ?> in.</td>
        </tr>
    </tbody>
</table>
<br />

</div>
  <?php endif; ?>

