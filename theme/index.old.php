<?php get_header(); ?>

	<main role="main">
		<!-- section -->
		<section>

			<?php
				PrcsSync::sync();
				$ig_posts = PrcsSync::get_instagram_posts(20);
				foreach ($ig_posts as $post):
				$style_margins = prcs_rnd_margins(0, 10, 5);
					//include( locate_template('card-instagram.php') );
			?>
			<article class="instagram" id="<?php echo $post->id; ?>" style="<?php echo $style_margins; ?>">
				<img src="<?php echo $post->image->url; ?>" style="max-height:500px; width:auto; max-width:100%;">
				<a href="<?php echo $post->link; ?>" target="_blank">
					<h3 class="subtitle"><?php echo $post->text; ?></h3>
					<h3 class="subtitle subtitle2"><?php echo prcs_time_ago($post->timestamp); ?> via Instagram</h3>
				</a>
			</article>
			<?php endforeach; ?>

			<?php
				$tw_posts = PrcsSync::get_twitter_posts(20);
				foreach ($tw_posts as $post):
				$style_margins = prcs_rnd_margins(0, 10, 5);

			?>
			<article class="twitter" id="<?php echo $post->id; ?>" style="<?php echo $style_margins; ?>">
				<div class="tweet"><?php echo $post->text; ?></div>
				<a href="<?php echo $post->link; ?>" target="_blank">
					<h3 class="subtitle subtitle2"><?php echo prcs_time_ago($post->timestamp); ?> via Twitter</h3>
				</a>
			</article>
			<?php endforeach; ?>

			<?php if (have_posts()): while (have_posts()) : the_post(); ?>
			<?php get_template_part('card-work') ?>
			<?php endwhile; endif; ?>

		</section>
		<!-- /section -->
	</main>

<?php get_footer(); ?>
