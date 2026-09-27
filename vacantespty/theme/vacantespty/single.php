<?php
get_header();
while ( have_posts() ) :
	the_post();
	?>
	<section class="page-hero page-hero--compact">
		<div class="container container--narrow">
			<?php
			$cat = get_the_category();
			if ( $cat ) {
				echo '<a class="vpty-badge vpty-badge--light" href="' . esc_url( get_category_link( $cat[0] ) ) . '">' . esc_html( $cat[0]->name ) . '</a>';
			}
			?>
			<h1><?php the_title(); ?></h1>
			<p class="page-hero__meta">
				<?php
				/* translators: 1: date, 2: reading minutes */
				echo esc_html( sprintf( __( '%1$s · %2$d min de lectura', 'vacantespty' ), get_the_date(), max( 1, (int) ceil( str_word_count( wp_strip_all_tags( get_the_content() ) ) / 200 ) ) ) );
				?>
			</p>
		</div>
	</section>

	<div class="container layout">
		<article <?php post_class( 'layout__main' ); ?>>
			<?php if ( has_post_thumbnail() ) : ?>
				<figure class="featured"><?php the_post_thumbnail( 'large' ); ?></figure>
			<?php endif; ?>
			<div class="entry-content">
				<?php the_content(); ?>
				<?php wp_link_pages(); ?>
			</div>
			<?php if ( vpty_core_active() ) : ?>
				<?php echo vpty_share_buttons(); // phpcs:ignore WordPress.Security.EscapeOutput ?>
				<div class="inline-cta">
					<div>
						<strong><?php esc_html_e( '¿Quieres enterarte primero de las vacantes?', 'vacantespty' ); ?></strong>
						<span><?php esc_html_e( 'Síguenos en WhatsApp: publicamos vacantes nuevas todos los días.', 'vacantespty' ); ?></span>
					</div>
					<?php echo vpty_channel_link( 'blog', __( 'Seguir el canal', 'vacantespty' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
				</div>
				<?php echo vpty_cv_promo_card( 'blog' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
			<?php endif; ?>
			<?php
			if ( comments_open() || get_comments_number() ) {
				comments_template();
			}
			?>
		</article>
		<?php get_sidebar(); ?>
	</div>
	<?php
endwhile;
get_footer();
