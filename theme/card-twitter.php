<?php
   $style_margins = prcs_rnd_margins(0, 10, 5);
?>
<article class="twitter" id="<?php echo $prcs->id; ?>" style="<?php echo $style_margins; ?>">
   <div class="tweet"><?php echo $prcs->text; ?></div>
   <a href="<?php echo $prcs->link; ?>" target="_blank">
      <h3 class="subtitle subtitle2"><?php echo prcs_time_ago($prcs->timestamp); ?> via Twitter</h3>
   </a>
</article>
