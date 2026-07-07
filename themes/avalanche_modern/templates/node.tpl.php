<?php
/**
 * @file
 * Avalanche Center Modern generic node template.
 *
 * Mirrors the responsive_bartik base markup but is PHP 8 safe: the base
 * template calls backdrop_attributes() on attribute variables that can be NULL
 * (e.g. $title_attributes for teasers), which fatals under PHP 8.
 */
// Attribute variables may be arrays, strings, or NULL here; normalize each to a
// safe attribute string (the base template's backdrop_attributes() calls fatal
// on NULL under PHP 8).
$norm_attr = function ($a) {
  if (is_array($a)) { return backdrop_attributes($a); }
  return is_string($a) ? $a : '';
};
$attributes = $norm_attr(isset($attributes) ? $attributes : NULL);
$title_attributes = $norm_attr(isset($title_attributes) ? $title_attributes : NULL);
$content_attributes = $norm_attr(isset($content_attributes) ? $content_attributes : NULL);
?>
<article id="node-<?php print $node->nid; ?>" class="<?php print implode(' ', $classes); ?> clearfix"<?php print $attributes; ?> role="article">

  <?php print render($title_prefix); ?>
  <?php if (!$page): ?>
    <h2<?php print $title_attributes; ?>>
      <a href="<?php print $node_url; ?>"><?php print $title; ?></a>
    </h2>
  <?php endif; ?>
  <?php print render($title_suffix); ?>

  <?php if ($display_submitted): ?>
    <footer class="meta submitted">
      <?php print $user_picture; ?>
      <?php print $submitted; ?>
    </footer>
  <?php endif; ?>

  <div class="content clearfix"<?php print $content_attributes; ?>>
    <?php
      hide($content['comments']);
      hide($content['links']);
      print render($content);
    ?>
  </div>

  <?php
    if ($teaser || !empty($content['comments']['comment_form'])) {
      unset($content['links']['comment']['#links']['comment-add']);
    }
    $links = render($content['links']);
    if ($links):
  ?>
    <div class="link-wrapper">
      <?php print $links; ?>
    </div>
  <?php endif; ?>

  <?php print render($content['comments']); ?>

</article>
