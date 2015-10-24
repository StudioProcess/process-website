<?php
   $style_margins = prcs_rnd_margins(0, 10, 5);
?>
<article class="instagram" id="<?php echo $prcs->id; ?>" style="<?php echo $style_margins; ?>">
   <img src="<?php echo $prcs->image->url; ?>" style="max-height:500px; width:auto; max-width:100%;">
   <a href="<?php echo $prcs->link; ?>" target="_blank">
      <h3 class="subtitle"><?php echo $prcs->text; ?></h3>
      <h3 class="subtitle subtitle2"><?php echo prcs_time_ago($prcs->timestamp); ?> via Instagram</h3>
   </a>
</article>
