<?php if($content) :?>
<div <?php print backdrop_attributes($attributes);?> class="<?php print implode(' ', $classes);?>">
  <div class="block-inner">
    <?php print $content ?>
  </div>
</div>
<?php endif;?>
