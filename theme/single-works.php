<?php get_header(); ?>

	<main role="main">
	<!-- section -->
	<section>

	<?php if (have_posts()): while (have_posts()) : the_post(); $current_work = get_the_ID(); ?>

		<!-- article -->
		<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

			<!-- post title -->
			<h1 class="title"><?php the_title(); ?></h1>
			<h2 class="subtitle"><?php echo types_render_field('subtitle', array()); ?></h2>
			<!-- /post title -->

			<section class="images">
				<?php foreach (hue_gallery_ids() as $id) {
					echo wp_get_attachment_image($id, array(1000,1000)) . " " . PHP_EOL;
				} ?>
			</section>

			<section class="text">
				<?php echo types_render_field('text', array()); ?>
			</section>

			<section class="credits">
			<?php echo types_render_field('credits', array()); ?>
			</section>

			<section class="credits">
			<?php edit_post_link(); // Always handy to have Edit Post Links available ?>
			</section>

			<style>
			<?php $fg = types_render_field('foreground-color-post', array()); ?>
			<?php $bg = types_render_field('background-color-post', array()); ?>
			body {
				background-color: <?php echo $bg; ?>;
				color: <?php echo $fg; ?>;
				}
				body.page article a, body.single-works .text a {
					color: <?php echo $fg; ?>;
					border-bottom: 1px solid <?php echo $fg; ?>;
				}

				body.single-works .text a:hover {
					color: <?php echo $fg; ?>;
					border-bottom: 2px solid <?php echo $fg; ?>;
				}
				body.single-works .credits a {
					color: <?php echo $fg; ?>;
					text-decoration: none;
				}
				.header nav a {
					color: <?php echo $fg; ?>;
				}
				.header nav li.current-menu-item a {
					background-color: <?php echo $fg; ?>;
					color: <?php echo $bg; ?>;
				}
				.header nav li a:hover {
					background-color: <?php echo $fg; ?>;
					color: <?php echo $bg; ?>;
				}
				.pace .pace-progress {
					background: <?php echo $fg; ?>;
				}

				<?php if(!empty($fg)): ?>
				section.work-links-in-post article hgroup:before {
					 background-color: <?php echo $fg; ?> !important;
				}
				<?php endif; ?>

				<?php if(!empty($bg)): ?>
				section.work-links-in-post article .title a {
					 color: <?php echo $bg; ?> !important;
				}
				<?php endif; ?>
			</style>

		</article>
		<!-- /article -->

	<?php endwhile; endif; ?>

	</section>

<h2 class="more-works">More Works</h2>

	<section class="work-links-in-post">

		<?php
			// The Query
			query_posts(array(
				'post_type' => 'works',
				'tag__not_in' => array(3),
				'post__not_in' => array($current_work)
			) );

			// The Loop
			while ( have_posts() ) : the_post();
				get_template_part('card-work');
			endwhile;

			// Reset Query
			wp_reset_query();
		?>

	</section>
	<!-- /section -->
	</main>

<?php get_footer(); ?>
