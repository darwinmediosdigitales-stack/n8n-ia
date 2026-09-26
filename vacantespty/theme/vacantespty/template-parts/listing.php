<?php
/**
 * Vacancy listing used by the archive and the category/province pages.
 */

$archive_url = get_post_type_archive_link( 'vacante' );
// phpcs:disable WordPress.Security.NonceVerification.Recommended -- read-only filters.
$modalidad = isset( $_GET['modalidad'] ) ? sanitize_key( wp_unslash( $_GET['modalidad'] ) ) : '';
$tipo      = isset( $_GET['tipo'] ) ? sanitize_text_field( wp_unslash( $_GET['tipo'] ) ) : '';
// phpcs:enable

if ( is_tax() ) {
	$term = get_queried_object();
	/* translators: %s: category name */
	$title = 'provincia' === $term->taxonomy ? sprintf( __( 'Empleos en %s', 'vacantespty' ), $term->name ) : sprintf( __( 'Empleos de %s en Panamá', 'vacantespty' ), $term->name );
	$intro = term_description();
} else {
	$title = __( 'Empleos en Panamá', 'vacantespty' );
	$intro = '';
}

global $wp_query;
?>

<section class="page-hero">
	<div class="container">
		<h1><?php echo esc_html( $title ); ?></h1>
		<p class="page-hero__count">
			<?php
			/* translators: %s: number of vacancies */
			echo esc_html( sprintf( _n( '%s vacante activa', '%s vacantes activas', $wp_query->found_posts, 'vacantespty' ), number_format_i18n( $wp_query->found_posts ) ) );
			?>
		</p>
		<?php echo vpty_search_form(); // phpcs:ignore WordPress.Security.EscapeOutput ?>
	</div>
</section>

<div class="container layout">
	<div class="layout__main">
		<div class="filters">
			<?php
			$current = remove_query_arg( array( 'modalidad', 'paged' ) );
			foreach ( array_merge( array( '' => __( 'Todas', 'vacantespty' ) ), vpty_modalidades() ) as $key => $label ) {
				printf(
					'<a class="filters__chip%s" href="%s">%s</a>',
					$modalidad === $key ? ' is-active' : '',
					esc_url( $key ? add_query_arg( 'modalidad', $key, $current ) : $current ),
					esc_html( $label )
				);
			}
			?>
			<span class="filters__sep"></span>
			<?php
			$current = remove_query_arg( array( 'tipo', 'paged' ) );
			foreach ( array( 'FULL_TIME', 'PART_TIME', 'INTERN' ) as $key ) {
				printf(
					'<a class="filters__chip%s" href="%s">%s</a>',
					$tipo === $key ? ' is-active' : '',
					esc_url( $tipo === $key ? $current : add_query_arg( 'tipo', $key, $current ) ),
					esc_html( vpty_tipos_contrato()[ $key ] )
				);
			}
			?>
		</div>

		<?php if ( have_posts() ) : ?>
			<div class="vpty-list">
				<?php
				$i = 0;
				while ( have_posts() ) {
					the_post();
					echo vpty_job_card(); // phpcs:ignore WordPress.Security.EscapeOutput
					if ( 5 === ++$i ) {
						vpty_theme_banner( 'listing' );
					}
				}
				?>
			</div>
			<?php
			the_posts_pagination(
				array(
					'prev_text' => '←',
					'next_text' => '→',
				)
			);
			?>
		<?php else : ?>
			<div class="empty">
				<p><?php esc_html_e( 'No encontramos vacantes con esos filtros.', 'vacantespty' ); ?></p>
				<a class="vpty-btn vpty-btn--primary" href="<?php echo esc_url( $archive_url ); ?>"><?php esc_html_e( 'Ver todas las vacantes', 'vacantespty' ); ?></a>
			</div>
		<?php endif; ?>

		<?php if ( $intro ) : ?>
			<div class="entry-content term-intro"><?php echo wp_kses_post( $intro ); ?></div>
		<?php endif; ?>
	</div>

	<aside class="layout__side">
		<?php vpty_theme_banner( 'sidebar' ); ?>
		<div class="side-box">
			<h3><?php esc_html_e( 'Categorías', 'vacantespty' ); ?></h3>
			<ul>
				<?php
				foreach ( (array) get_terms( array( 'taxonomy' => 'categoria_empleo', 'hide_empty' => false, 'parent' => 0 ) ) as $t ) {
					printf( '<li><a href="%s">%s</a> <span>%s</span></li>', esc_url( get_term_link( $t ) ), esc_html( $t->name ), esc_html( number_format_i18n( $t->count ) ) );
				}
				?>
			</ul>
		</div>
	</aside>
</div>
