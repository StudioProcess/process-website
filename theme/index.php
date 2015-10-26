<?php get_header(); ?>

	<main role="main">
		<!-- section -->
		<section>

			<?php //PrcsSync::sync(); // TODO: this takes too long
			$social_posts = prcs_get_social_posts(40);
			$num_social = sizeof($social_posts);
			$num_works = $wp_query->found_posts;
			$idx = 0;

			if (have_posts()): while (have_posts()) : the_post();

				// insert social posts if appropriate
				while ( prcs_should_insert_social($idx++, $num_social, $num_works) ) {
					$prcs = current($social_posts);
					if ($prcs->service == 'twitter') include( locate_template('card-twitter.php') );
					else if ($prcs->service == 'instagram') include( locate_template('card-instagram.php') );
					next($social_posts); // advance pointer
				}

				// insert next work
				get_template_part('card-work');

			endwhile; endif; ?>

		</section>
		<!-- /section -->
	</main>

<?php get_footer(); ?>
