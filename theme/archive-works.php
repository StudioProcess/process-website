<?php get_header(); ?>

	<main role="main">
		<!-- section -->
		<section>

			<?php if (have_posts()): while (have_posts()) : the_post(); ?>
			<?php get_template_part('card-work') ?>
			<?php endwhile; endif; ?>

		</section>
		<!-- /section -->
	</main>

<?php get_footer(); ?>
