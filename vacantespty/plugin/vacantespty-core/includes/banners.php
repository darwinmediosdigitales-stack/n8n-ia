<?php
defined( 'ABSPATH' ) || exit;

function vpty_active_banners( $zone ) {
	$today = current_time( 'Y-m-d' );
	$posts = get_posts(
		array(
			'post_type'      => 'banner',
			'post_status'    => 'publish',
			'posts_per_page' => 20,
			'meta_key'       => '_vpty_zonas', // phpcs:ignore WordPress.DB.SlowDBQuery
			'meta_value'     => '"' . $zone . '"', // phpcs:ignore WordPress.DB.SlowDBQuery
			'meta_compare'   => 'LIKE',
		)
	);
	return array_values(
		array_filter(
			$posts,
			function ( $p ) use ( $today ) {
				$from = get_post_meta( $p->ID, '_vpty_inicio', true );
				$to   = get_post_meta( $p->ID, '_vpty_fin', true );
				return has_post_thumbnail( $p ) && ( ! $from || $from <= $today ) && ( ! $to || $to >= $today );
			}
		)
	);
}

function vpty_banner_click_url( $banner_id ) {
	return rest_url( 'vpty/v1/banner/' . $banner_id );
}

/**
 * Renders the banners for a zone. When several brands share a zone they rotate
 * client-side so page caching still gives every advertiser exposure.
 * Returns a "your brand here" slot for admins when the zone is empty.
 */
function vpty_banner( $zone ) {
	$banners = vpty_active_banners( $zone );

	if ( ! $banners ) {
		if ( current_user_can( 'edit_posts' ) ) {
			$zones = vpty_banner_zones();
			return sprintf(
				'<div class="vpty-ad vpty-ad--empty vpty-ad--%1$s"><a href="%2$s">%3$s<br><small>%4$s</small></a></div>',
				esc_attr( $zone ),
				esc_url( admin_url( 'post-new.php?post_type=banner' ) ),
				esc_html__( 'Espacio publicitario disponible', 'vacantespty' ),
				esc_html( isset( $zones[ $zone ] ) ? $zones[ $zone ] : $zone )
			);
		}
		return '';
	}

	shuffle( $banners );
	$html = '<div class="vpty-ad vpty-ad--' . esc_attr( $zone ) . '" data-vpty-rotate><span class="vpty-ad__label">' . esc_html__( 'Publicidad', 'vacantespty' ) . '</span>';
	foreach ( $banners as $i => $banner ) {
		$html .= sprintf(
			'<a class="vpty-ad__item%1$s" href="%2$s" target="_blank" rel="sponsored noopener">%3$s</a>',
			0 === $i ? ' is-active' : '',
			esc_url( vpty_banner_click_url( $banner->ID ) ),
			get_the_post_thumbnail( $banner->ID, 'full', array( 'alt' => esc_attr( get_the_title( $banner ) ), 'loading' => 'lazy' ) )
		);
	}
	return $html . '</div>';
}

add_action(
	'wp_footer',
	function () {
		?>
<script>document.querySelectorAll('[data-vpty-rotate]').forEach(function(z){var a=z.querySelectorAll('.vpty-ad__item');if(a.length<2)return;a.forEach(function(e){e.classList.remove('is-active')});a[Math.floor(Math.random()*a.length)].classList.add('is-active');});</script>
		<?php
	}
);

/** Click tracker: counts the click and redirects to the advertiser. REST is not page-cached. */
add_action(
	'rest_api_init',
	function () {
		register_rest_route(
			'vpty/v1',
			'/banner/(?P<id>\d+)',
			array(
				'methods'             => 'GET',
				'permission_callback' => '__return_true',
				'callback'            => function ( WP_REST_Request $request ) {
					$id  = (int) $request['id'];
					$url = get_post_meta( $id, '_vpty_url', true );
					if ( 'banner' !== get_post_type( $id ) || 'publish' !== get_post_status( $id ) || ! $url ) {
						wp_safe_redirect( home_url( '/' ) );
						exit;
					}
					update_post_meta( $id, '_vpty_clicks', (int) get_post_meta( $id, '_vpty_clicks', true ) + 1 );
					nocache_headers();
					wp_redirect( $url ); // phpcs:ignore WordPress.Security.SafeRedirect -- advertiser URL set by an editor.
					exit;
				},
			)
		);
	}
);
