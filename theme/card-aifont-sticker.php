<style>
  #aifont-sticker {
    display: block !important;
    width: 200px;
    height: auto;
    position: fixed;
    top: 20px;
    right: 20px;
    transform: rotate(10deg) scale(1.0);
    transition: transform 0.2s;
    z-index: 11;
  }
  #aifont-sticker:hover {
    transform: rotate(12.5deg) scale(1.03);
  }
  @media (max-width: 900px) {
    #aifont-sticker {
      top: auto;
      bottom: 20px;
    }
  }
  @media (max-width: 600px) {
    #aifont-sticker {
      top: auto;
      bottom: 20px;
      width: 132px;
    }
  }
</style>
<a href="https://aifont.process.studio" id="aifont-sticker"><img src="<?php echo get_template_directory_uri(); ?>/_/img/aifont_sticker_web.svg" /></a>