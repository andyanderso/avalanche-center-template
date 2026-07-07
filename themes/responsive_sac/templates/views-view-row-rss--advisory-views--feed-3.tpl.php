<?php

// $Id: views-view-row-rss--advisory-views--feed-1.tpl.php
/**
* @file views-view-row-rss.tpl.php
* Default view template to display a item in an RSS feed.
*
* @ingroup views_templates
*/

?>
<item>
<title><?php print $title; ?></title>
<link><?php print $link; ?></link>
<description>
<?php print t('Today the avalanche danger is'); ?> <?php if ($node->field_overalldanger['und'][0]['value']=='1'){print t('LOW');}
elseif ($node->field_overalldanger['und'][0]['value']=='2')
{
print t('MODERATE');
}
elseif ($node->field_overalldanger['und'][0]['value']=='3')
{
print t('CONSIDERABLE');
}
elseif ($node->field_overalldanger['und'][0]['value']=='4')
{
print t('HIGH');
}
elseif ($node->field_overalldanger['und'][0]['value']=='5')
{
print t('EXTREME');
}
else {
print t('NO RATING');
}?>

</description>
<?php print $item_elements; ?>
</item>
