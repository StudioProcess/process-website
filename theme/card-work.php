   <?php if ( has_post_thumbnail()) {
      $img_scale = 1;
      $img = prcs_thumbnail_data('medium');
      $style_width = 'max-width:' . $img["width"]*$img_scale . 'px; ';
      $style_margins = prcs_rnd_margins(0, 10, 5);
      // print_r($img);
   } ?>
   <div class="work-container">
      <!-- article  -->
      <article class="work" id="post-<?php the_ID(); ?>" style="<?php echo $style_width . $style_margins; ?>" <?php post_class(); ?>>

      <!-- post thumbnail -->
      <?php if ( has_post_thumbnail()) : // Check if thumbnail exists ?>
         <a href="<?php the_permalink(); ?>">
            <?php echo $img[html]; // Declare pixel size you need inside the array ?>
         </a>
      <?php endif; ?>
      <!-- /post thumbnail -->

      <!-- post title -->
      <hgroup>
        <?php $title_extra = types_render_field('title-extra', array()); ?>
        <h2 class="title"><a href="<?php the_permalink(); ?>"><?php the_title(); if (!empty($title_extra)) echo " <span class='title-extra'> {$title_extra}</span>"; ?></a></h2>
        <h3 class="subtitle"><?php echo types_render_field('subtitle', array()); ?></h3>
      </hgroup>
      <!-- /post title -->

      </article>
      <!-- /article -->
   </div>
