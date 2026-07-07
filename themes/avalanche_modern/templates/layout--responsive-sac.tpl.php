<?php
/**
 * @file
 * Modern layout template for the Avalanche Center Modern theme.
 *
 * Overrides the responsive_sac layout template's markup while this theme is the
 * active theme. Receives the same rendered region strings via $content[...].
 */

if (!isset($facebook)) { $facebook = theme_get_setting('facebook_url'); }
if (!isset($twitter)) { $twitter = theme_get_setting('twitter_url'); }
if (!isset($instagram)) { $instagram = theme_get_setting('instagram_url'); }
if (!isset($youtube)) { $youtube = theme_get_setting('youtube_url'); }
if (!isset($email)) { $email = theme_get_setting('email_url'); }

$is_front = backdrop_is_front_page();
$has_sidebar = !empty($content['sidebar_first']) || !empty($content['sidebar_second']);
// Resolve the current node (not exposed directly to the layout template).
$page_node = menu_get_object();
// Advisory forecast pages render their own title; suppress the page title.
$hide_page_title = (isset($page_node->type) && $page_node->type == 'advisory');
?>
<div id="page-wrapper" class="am-page-wrapper"><div id="page" class="<?php print implode(' ', $classes); ?>">

  <?php if (!empty($content['banner'])): ?>
    <div class="am-banner"><?php print $content['banner']; ?></div>
  <?php endif; ?>

  <header id="header" class="am-header">

    <!-- Row 1: logo centred, social actions pinned right -->
    <div class="am-logo-row">
      <div class="am-brand">
        <?php print $content['header']; ?>
      </div>
      <div class="am-header-actions">
        <?php if ($facebook): ?><a class="am-social" href="<?php print $facebook; ?>" title="Facebook" target="_blank" rel="noopener"><i class="fa fa-facebook"></i></a><?php endif; ?>
        <?php if ($twitter): ?><a class="am-social" href="<?php print $twitter; ?>" title="Twitter" target="_blank" rel="noopener"><i class="fa fa-twitter"></i></a><?php endif; ?>
        <?php if ($instagram): ?><a class="am-social" href="<?php print $instagram; ?>" title="Instagram" target="_blank" rel="noopener"><i class="fa fa-instagram"></i></a><?php endif; ?>
      </div>
    </div>

    <!-- Row 2: megamenu navigation bar, centred -->
    <?php if (!empty($content['megamenu'])): ?>
      <nav class="am-nav-row" role="navigation" aria-label="<?php print t('Main navigation'); ?>">
        <?php print $content['megamenu']; ?>
      </nav>
    <?php endif; ?>

    <?php if (!empty($content['header_middle'])): ?>
      <div class="am-header-middle"><?php print $content['header_middle']; ?></div>
    <?php endif; ?>

  </header>

  <?php if (!empty($messages)): ?>
    <div id="messages"><div class="am-container"><?php print $messages; ?></div></div>
  <?php endif; ?>

  <?php if (!empty($content['featured'])): ?>
    <div id="featured"><div class="am-container"><?php print $content['featured']; ?></div></div>
  <?php endif; ?>

  <main id="main-wrapper" class="am-main-wrapper<?php print $is_front ? ' is-front' : ''; ?>">
    <div class="am-container am-main<?php print $has_sidebar ? ' has-sidebar' : ''; ?>">

      <?php if (!$is_front && !empty($content['sidebar_first'])): ?>
        <aside id="sidebar-first" class="am-sidebar"><?php print $content['sidebar_first']; ?></aside>
      <?php endif; ?>

      <div id="content" class="am-content<?php print !empty($page_node->type) ? ' ' . check_plain($page_node->type) : ''; ?>">
        <?php if (!empty($content['highlighted'])): ?>
          <div id="highlighted"><?php print $content['highlighted']; ?></div>
        <?php endif; ?>

        <a id="main-content"></a>

        <?php if (!empty($title) && !$is_front && !$hide_page_title): ?>
          <?php print render($title_prefix); ?>
          <h1 class="am-page-title"><?php print $title; ?></h1>
          <?php print render($title_suffix); ?>
        <?php endif; ?>

        <?php if (!empty($tabs)): ?><div class="tabs"><?php print $tabs; ?></div><?php endif; ?>
        <?php if (!empty($content['help'])): print $content['help']; endif; ?>
        <?php if (!empty($action_links)): ?><ul class="action-links"><?php print $action_links; ?></ul><?php endif; ?>

        <?php print $content['content']; ?>

        <?php if (!empty($feed_icons)): print $feed_icons; endif; ?>
      </div>

      <?php if ($is_front && !empty($content['sidebar_first'])): ?>
        <aside id="sidebar-first" class="am-sidebar"><?php print $content['sidebar_first']; ?></aside>
      <?php endif; ?>

      <?php if (!empty($content['sidebar_second'])): ?>
        <aside id="sidebar-second" class="am-sidebar"><?php print $content['sidebar_second']; ?></aside>
      <?php endif; ?>

    </div>
  </main>

  <footer id="footer-wrapper" class="am-footer">
    <div class="am-container">
      <?php if (!empty($content['footer_firstcolumn']) || !empty($content['footer_secondcolumn']) || !empty($content['footer_thirdcolumn']) || !empty($content['footer_fourthcolumn']) || !empty($content['footer_fifthcolumn'])): ?>
        <div id="footer-columns" class="am-footer-columns">
          <?php if (!empty($content['footer_firstcolumn'])): ?><div class="region region-footer-firstcolumn"><?php print $content['footer_firstcolumn']; ?></div><?php endif; ?>
          <?php if (!empty($content['footer_secondcolumn'])): ?><div class="region region-footer-secondcolumn"><?php print $content['footer_secondcolumn']; ?></div><?php endif; ?>
          <?php if (!empty($content['footer_thirdcolumn'])): ?><div class="region region-footer-thirdcolumn"><?php print $content['footer_thirdcolumn']; ?></div><?php endif; ?>
          <?php if (!empty($content['footer_fourthcolumn'])): ?><div class="region region-footer-fourthcolumn"><?php print $content['footer_fourthcolumn']; ?></div><?php endif; ?>
          <?php if (!empty($content['footer_fifthcolumn'])): ?><div class="region region-footer-fifthcolumn"><?php print $content['footer_fifthcolumn']; ?></div><?php endif; ?>
        </div>
      <?php endif; ?>
      <?php if (!empty($content['footer'])): ?>
        <div id="footer" class="am-footer-bottom"><?php print $content['footer']; ?></div>
      <?php endif; ?>
    </div>
  </footer>

</div></div>
