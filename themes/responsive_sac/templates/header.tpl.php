<?php
/**
 * @file
 * Backdrop header block template for responsive_sac.
 *
 * Available variables:
 * - $logo: Path to the logo image.
 * - $front_page: URL of the home page.
 * - $site_name: The site name.
 * - $site_slogan: The site slogan.
 * - $is_front: TRUE if this is the front page.
 * - $title: The page title (used to decide h1 vs div for site name).
 * - $hide_site_name: TRUE if the site name is toggled off.
 * - $hide_site_slogan: TRUE if the site slogan is toggled off.
 * - $email: Email subscriptions URL (added via preprocess).
 * - $facebook: Facebook URL.
 * - $twitter: Twitter URL.
 * - $instagram: Instagram URL.
 * - $youtube: YouTube URL.
 */
?>

<?php if ($logo): ?>
  <a href="<?php print $front_page; ?>" title="<?php print t('Home'); ?>" rel="home" id="logo">
    <img src="<?php print $logo; ?>" alt="<?php print t('Home'); ?>" />
  </a>
<?php endif; ?>

<?php if ($site_name || $site_slogan): ?>
  <div id="name-and-slogan"<?php if (!empty($hide_site_name) && !empty($hide_site_slogan)) { print ' class="element-invisible"'; } ?>>
    <?php if ($site_name): ?>
      <?php if (!empty($title)): ?>
        <div id="site-name"<?php if (!empty($hide_site_name)) { print ' class="element-invisible"'; } ?>>
          <strong>
            <a href="<?php print $front_page; ?>" title="<?php print t('Home'); ?>" rel="home"><span><?php print $site_name; ?></span></a>
          </strong>
        </div>
      <?php else: ?>
        <h1 id="site-name"<?php if (!empty($hide_site_name)) { print ' class="element-invisible"'; } ?>>
          <a href="<?php print $front_page; ?>" title="<?php print t('Home'); ?>" rel="home"><span><?php print $site_name; ?></span></a>
        </h1>
      <?php endif; ?>
    <?php endif; ?>
    <?php if ($site_slogan): ?>
      <div id="site-slogan"<?php if (!empty($hide_site_slogan)) { print ' class="element-invisible"'; } ?>>
        <?php print $site_slogan; ?>
      </div>
    <?php endif; ?>
  </div><!-- /#name-and-slogan -->
<?php endif; ?>
