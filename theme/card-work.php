   <?php if ( has_post_thumbnail()) {
      $img_scale = 1;
      $img = prcs_thumbnail_data('medium');
      $style_width = 'width:' . $img[1]*$img_scale . 'px; ';
      $style_margins = prcs_rnd_margins(0, 10, 5);
   } ?>
   <!-- article -->
   <article class="work" id="post-<?php the_ID(); ?>" style="<?php echo $style_width . $style_margins; ?>" <?php post_class(); ?>>

   <!-- post thumbnail -->
   <?php if ( has_post_thumbnail()) : // Check if thumbnail exists ?>
      <a href="<?php the_permalink(); ?>">
         <?php the_post_thumbnail('medium'); // Declare pixel size you need inside the array ?>
      </a>
   <?php endif; ?>
   <!-- /post thumbnail -->

   <!-- post title --><?php ; ?>
   <?php $title_extra = types_render_field('title-extra', array()); ?>
   <h2 class="title"><a href="<?php the_permalink(); ?>"><?php the_title(); if (!empty($title_extra)) echo " <span class='title-extra'> {$title_extra}</span>"; ?></a></h2>
   <h3 class="subtitle"><?php echo types_render_field('subtitle', array()); ?></h3>
   <!-- /post title -->

   </article>
   <!-- /article -->
