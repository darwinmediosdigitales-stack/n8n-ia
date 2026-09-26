<?php
defined( 'ABSPATH' ) || exit;

add_action( 'pre_get_posts', 'vpty_filter_job_queries' );

/** Meta clause that hides vacancies whose closing date has passed. */
function vpty_active_meta_clause() {
	return array(
		'relation' => 'OR',
		array(
			'key'     => '_vpty_vence',
			'compare' => 'NOT EXISTS',
		),
		array(
			'key'     => '_vpty_vence',
			'value'   => current_time( 'Y-m-d' ),
			'compare' => '>=', // Y-m-d strings sort chronologically.
		),
	);
}

/**
 * Applies the job search form (?q=&categoria=&prov=&modalidad=&tipo=) to the vacancy
 * archive and taxonomy pages, and hides expired vacancies from listings.
 */
function vpty_filter_job_queries( $query ) {
	if ( is_admin() || ! $query->is_main_query() ) {
		return;
	}
	if ( ! ( $query->is_post_type_archive( 'vacante' ) || $query->is_tax( array( 'categoria_empleo', 'provincia' ) ) ) ) {
		return;
	}

	// phpcs:disable WordPress.Security.NonceVerification.Recommended -- public search form.
	$q         = isset( $_GET['q'] ) ? sanitize_text_field( wp_unslash( $_GET['q'] ) ) : '';
	$cat       = isset( $_GET['categoria'] ) ? sanitize_title( wp_unslash( $_GET['categoria'] ) ) : '';
	$prov      = isset( $_GET['prov'] ) ? sanitize_title( wp_unslash( $_GET['prov'] ) ) : '';
	$modalidad = isset( $_GET['modalidad'] ) ? sanitize_key( wp_unslash( $_GET['modalidad'] ) ) : '';
	$tipo      = isset( $_GET['tipo'] ) ? sanitize_text_field( wp_unslash( $_GET['tipo'] ) ) : '';
	// phpcs:enable

	$query->set( 'posts_per_page', 20 );

	if ( $q ) {
		$query->set( 's', $q );
	}

	$tax_query = array();
	if ( $cat ) {
		$tax_query[] = array( 'taxonomy' => 'categoria_empleo', 'field' => 'slug', 'terms' => $cat );
	}
	if ( $prov ) {
		$tax_query[] = array( 'taxonomy' => 'provincia', 'field' => 'slug', 'terms' => $prov );
	}
	if ( $tax_query ) {
		$query->set( 'tax_query', array_merge( array( 'relation' => 'AND' ), $tax_query ) );
	}

	$meta_query = array( 'relation' => 'AND', vpty_active_meta_clause() );
	if ( $modalidad && array_key_exists( $modalidad, vpty_modalidades() ) ) {
		$meta_query[] = array( 'key' => '_vpty_modalidad', 'value' => $modalidad );
	}
	if ( $tipo && array_key_exists( $tipo, vpty_tipos_contrato() ) ) {
		$meta_query[] = array( 'key' => '_vpty_tipo', 'value' => $tipo );
	}
	$query->set( 'meta_query', $meta_query );
}

/**
 * Active vacancies for custom sections (home page, related jobs).
 */
function vpty_get_jobs( $args = array() ) {
	$defaults = array(
		'post_type'           => 'vacante',
		'posts_per_page'      => 8,
		'ignore_sticky_posts' => true,
		'meta_query'          => array( vpty_active_meta_clause() ),
	);
	if ( ! empty( $args['destacadas'] ) ) {
		$defaults['meta_query'][] = array( 'key' => '_vpty_destacada', 'value' => '1' );
		unset( $args['destacadas'] );
	}
	return new WP_Query( array_merge( $defaults, $args ) );
}

/** Keep expired vacancies out of the XML sitemap. */
add_filter(
	'wp_sitemaps_posts_query_args',
	function ( $args, $post_type ) {
		if ( 'vacante' === $post_type ) {
			$args['meta_query'] = array( vpty_active_meta_clause() ); // phpcs:ignore WordPress.DB.SlowDBQuery
		}
		return $args;
	},
	10,
	2
);

/** Expired vacancies stay reachable (for old links) but are not indexed. */
add_filter(
	'wp_robots',
	function ( $robots ) {
		if ( is_singular( 'vacante' ) && vpty_is_expired() ) {
			$robots['noindex'] = true;
		}
		return $robots;
	}
);
