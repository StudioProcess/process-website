<?php get_header(); ?>

	<main role="main">
	<!-- section -->
	<section>

	<?php if (have_posts()): while (have_posts()) : the_post(); ?>

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

		</article>
		<!-- /article -->

	<?php endwhile; endif; ?>

	</section>
	<!-- /section -->
	</main>

<?php get_footer(); ?>
