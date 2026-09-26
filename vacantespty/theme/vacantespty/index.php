<?php
get_header();
?>
<section class="page-hero page-hero--compact">
	<div class="container">
		<h1>
			<?php
			if ( is_home() ) {
				echo esc_html( get_the_title( get_option( 'page_for_posts' ) ) ?: __( 'Blog', 'vacantespty' ) );
			} elseif ( is_search() ) {
				/* translators: %s: search term */
				echo esc_html( sprintf( __( 'Resultados para "%s"', 'vacantespty' ), get_search_query() ) );
			} else {
				the_archive_title();
			}
			?>
		</h1>
		<?php if ( is_home() ) : ?>
			<p><?php esc_html_e( 'Guías, consejos y noticias para conseguir empleo en Panamá.', 'vacantespty' ); ?></p>
		<?php else : ?>
			<?php the_archive_description( '<div class="page-hero__desc">', '</div>' ); ?>
		<?php endif; ?>
	</div>
</section>

<div class="container layout">
	<div class="layout__main">
		<?php if ( have_posts() ) : ?>
			<div class="post-grid post-grid--2">
				<?php
				while ( have_posts() ) {
					the_post();
					get_template_part( 'template-parts/card-post' );
				}
				?>
			</div>
			<?php the_posts_pagination( array( 'prev_text' => '←', 'next_text' => '→' ) ); ?>
		<?php else : ?>
			<div class="empty"><p><?php esc_html_e( 'No hay publicaciones todavía.', 'vacantespty' ); ?></p></div>
		<?php endif; ?>
	</div>
	<?php get_sidebar(); ?>
</div>
<?php
get_footer();
