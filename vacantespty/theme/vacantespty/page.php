<?php
get_header();
while ( have_posts() ) :
	the_post();
	?>
	<section class="page-hero page-hero--compact">
		<div class="container container--narrow">
			<h1><?php the_title(); ?></h1>
		</div>
	</section>
	<div class="container container--narrow page-body">
		<div class="entry-content">
			<?php the_content(); ?>
		</div>
	</div>
	<?php
endwhile;
get_footer();
