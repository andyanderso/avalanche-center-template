<?php
/**
 * @file
 * Layout template for responsive_sac.
 *
 * Ported from the Drupal 7 page.tpl.php. In Backdrop, layout templates receive
 * regions via $content['region_name'] (rendered strings) rather than $page['region_name'].
 *
 * Available variables:
 * - $content['banner'], $content['header'], $content['header_middle'], etc.
 * - $messages: Status/error messages.
 * - $title: The page title.
 * - $tabs: Admin tabs.
 * - $action_links: Admin action links.
 * - $classes: CSS classes array for the layout wrapper.
 * - $header_background: Header background image URL (set via preprocess).
 * - $email: Email subscriptions URL (set via preprocess).
 * - $facebook, $twitter, $instagram, $youtube: Social media URLs (set via preprocess).
 * - $secondary_menu: Secondary menu HTML (set via preprocess).
 * - $breadcrumb: Breadcrumb HTML.
 */

// Provide fallback values for theme settings if not already set by preprocess.
if (!isset($header_background)) {
  $header_background = theme_get_setting('backgroung_header_img');
}
if (!isset($email)) {
  $email = theme_get_setting('email_url');
}
if (!isset($facebook)) {
  $facebook = theme_get_setting('facebook_url');
}
if (!isset($twitter)) {
  $twitter = theme_get_setting('twitter_url');
}
if (!isset($instagram)) {
  $instagram = theme_get_setting('instagram_url');
}
if (!isset($youtube)) {
  $youtube = theme_get_setting('youtube_url');
}
?>
<div id="page-wrapper"><div id="page" class="<?php print implode(' ', $classes); ?>">

  <?php if (!empty($content['banner'])): ?>
    <div class="banner">
      <?php print $content['banner']; ?>
    </div><!-- /.banner -->
  <?php endif; ?>

  <div id="header" class="<?php print !empty($secondary_menu) ? 'with-secondary-menu' : 'without-secondary-menu'; ?>">
    <div class="section clearfix">

      <?php if (!empty($content['header_middle'])): ?>
        <div class="header_middle">
          <?php print $content['header_middle']; ?>
        </div>
      <?php endif; ?>

      <!-- Row 1: logo centred -->
      <div class="rs-logo-row">
        <?php print $content['header']; ?>
      </div>

      <?php if (!empty($secondary_menu)): ?>
        <div id="secondary-menu" class="navigation">
          <?php print $secondary_menu; ?>
        </div><!-- /#secondary-menu -->
      <?php endif; ?>

    </div>

    <!-- Row 2: megamenu navigation bar, centred (full-width strip) -->
    <?php if (!empty($content['megamenu'])): ?>
      <nav class="rs-nav-row" role="navigation" aria-label="<?php print t('Main navigation'); ?>">
        <div class="section"><?php print $content['megamenu']; ?></div>
      </nav>
    <?php endif; ?>

  </div><!-- /#header -->

  <?php if (!empty($messages)): ?>
    <div id="messages"><div class="section clearfix">
      <?php print $messages; ?>
    </div></div><!-- /.section, /#messages -->
  <?php endif; ?>

  <?php if (!empty($content['featured'])): ?>
    <div id="featured"><div class="section clearfix">
      <?php print $content['featured']; ?>
    </div></div><!-- /.section, /#featured -->
  <?php endif; ?>

  <div id="main-wrapper" class="clearfix"><div id="main" class="clearfix">

    <?php $is_front = backdrop_is_front_page(); ?>
    <div<?php print $is_front ? ' id="danger-ratings-content"' : ''; ?> class="sidebarbg">

      <?php if (!$is_front && !empty($content['sidebar_first'])): ?>
        <div id="sidebar-first" class="column sidebar"><div class="section">
          <?php print $content['sidebar_first']; ?>
        </div></div><!-- /.section, /#sidebar-first -->
      <?php endif; ?>

      <?php
        // Add node type as a class to #content so type-specific CSS rules match (e.g. div.advisory .darkbg h3)
        $content_extra_class = '';
        if (!empty($node) && !empty($node->type)) {
          $content_extra_class = ' ' . check_plain($node->type);
        } elseif (function_exists('menu_get_object') && ($n = menu_get_object('node'))) {
          $content_extra_class = ' ' . check_plain($n->type);
        }
      ?>
      <div id="content" class="column<?php print $content_extra_class; ?>"><div class="section">

        <?php if (!empty($content['highlighted'])): ?>
          <div id="highlighted"><?php print $content['highlighted']; ?></div>
        <?php endif; ?>

        <a id="main-content"></a>

        <?php if (!empty($title) && !$is_front): ?>
          <?php print render($title_prefix); ?>
          <?php print $title; ?>
          <?php print render($title_suffix); ?>
        <?php endif; ?>

        <?php if (!empty($tabs)): ?>
          <div class="tabs"><?php print $tabs; ?></div>
        <?php endif; ?>

        <?php if (!empty($content['help'])): ?>
          <?php print $content['help']; ?>
        <?php endif; ?>

        <?php if (!empty($action_links)): ?>
          <ul class="action-links"><?php print $action_links; ?></ul>
        <?php endif; ?>

        <?php print $content['content']; ?>

        <?php if (!empty($feed_icons)): ?>
          <?php print $feed_icons; ?>
        <?php endif; ?>

      </div></div><!-- /.section, /#content -->

      <?php if ($is_front && !empty($content['sidebar_first'])): ?>
        <div id="sidebar-first" class="column sidebar"><div class="section">
          <?php print $content['sidebar_first']; ?>
        </div></div><!-- /.section, /#sidebar-first -->
      <?php endif; ?>

      <?php if (!empty($content['sidebar_second'])): ?>
        <div id="sidebar-second" class="column sidebar"><div class="section">
          <?php print $content['sidebar_second']; ?>
        </div></div><!-- /.section, /#sidebar-second -->
      <?php endif; ?>

    </div>

  </div></div><!-- /#main, /#main-wrapper -->

  <?php if (!empty($content['triptych_first']) || !empty($content['triptych_middle']) || !empty($content['triptych_last'])): ?>
    <div id="triptych-wrapper"><div id="triptych" class="clearfix">
      <?php print $content['triptych_first']; ?>
      <?php print $content['triptych_middle']; ?>
      <?php print $content['triptych_last']; ?>
    </div></div><!-- /#triptych, /#triptych-wrapper -->
  <?php endif; ?>

  <div id="footer-wrapper"><div class="section">

    <?php if (!empty($content['footer_firstcolumn']) || !empty($content['footer_secondcolumn']) || !empty($content['footer_thirdcolumn']) || !empty($content['footer_fourthcolumn']) || !empty($content['footer_fifthcolumn'])): ?>
      <div id="footer-columns" class="clearfix">
        <?php if (!empty($content['footer_firstcolumn'])): ?>
          <div class="region region-footer-firstcolumn"><?php print $content['footer_firstcolumn']; ?></div>
        <?php endif; ?>
        <?php if (!empty($content['footer_secondcolumn'])): ?>
          <div class="region region-footer-secondcolumn"><?php print $content['footer_secondcolumn']; ?></div>
        <?php endif; ?>
        <?php if (!empty($content['footer_thirdcolumn'])): ?>
          <div class="region region-footer-thirdcolumn"><?php print $content['footer_thirdcolumn']; ?></div>
        <?php endif; ?>
        <?php if (!empty($content['footer_fourthcolumn'])): ?>
          <div class="region region-footer-fourthcolumn"><?php print $content['footer_fourthcolumn']; ?></div>
        <?php endif; ?>
        <?php if (!empty($content['footer_fifthcolumn'])): ?>
          <div class="region region-footer-fifthcolumn"><?php print $content['footer_fifthcolumn']; ?></div>
        <?php endif; ?>
      </div><!-- /#footer-columns -->
    <?php endif; ?>

    <?php if (!empty($content['footer'])): ?>
      <div id="footer" class="clearfix">
        <?php print $content['footer']; ?>
      </div><!-- /#footer -->
    <?php endif; ?>

  </div></div><!-- /.section, /#footer-wrapper -->

</div></div><!-- /#page, /#page-wrapper -->
