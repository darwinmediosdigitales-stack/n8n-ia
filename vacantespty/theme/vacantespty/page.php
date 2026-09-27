<?php
get_header();
while ( have_posts() ) :
	the_post();
	// Pages with card grids get the full-width container.
	$container = is_page( array( 'capacitate', 'curriculum-profesional' ) ) ? 'container' : 'container container--narrow';
	?>
	<section class="page-hero page-hero--compact">
		<div class="<?php echo esc_attr( $container ); ?>">
			<h1><?php the_title(); ?></h1>
		</div>
	</section>
	<div class="<?php echo esc_attr( $container ); ?> page-body">
		<div class="entry-content">
			<?php the_content(); ?>
		</div>
	</div>
	<?php
endwhile;
get_footer();
