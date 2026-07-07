<?php
// block--menu

?>
<nav class="<?php print implode(' ', (array) $classes); ?>"<?php print backdrop_attributes((array) $attributes); ?> role="navigation">

  <?php print render($title_prefix); ?>
<?php if (!empty($title)): ?>
  <h2<?php print backdrop_attributes((array) (isset($title_attributes) ? $title_attributes : array())); ?>><?php print $title; ?></h2>
<?php endif;?>
  <?php print render($title_suffix); ?>

  <div class="content"<?php print backdrop_attributes((array) $content_attributes); ?>>
    <?php print render($content); ?>
  </div>
</nav>
