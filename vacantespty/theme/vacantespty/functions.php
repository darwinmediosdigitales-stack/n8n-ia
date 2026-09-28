<?php
/**
 * Vacantes PTY theme.
 */

defined( 'ABSPATH' ) || exit;

define( 'VPTY_THEME_VERSION', '1.2.0' );

add_action(
	'after_setup_theme',
	function () {
		load_theme_textdomain( 'vacantespty', get_template_directory() . '/languages' );
		add_theme_support( 'title-tag' );
		add_theme_support( 'post-thumbnails' );
		add_theme_support( 'responsive-embeds' );
		add_theme_support( 'html5', array( 'search-form', 'gallery', 'caption', 'style', 'script' ) );
		add_theme_support(
			'custom-logo',
			array(
				'height'      => 80,
				'width'       => 260,
				'flex-height' => true,
				'flex-width'  => true,
			)
		);
		register_nav_menus(
			array(
				'principal' => __( 'Menú principal', 'vacantespty' ),
				'footer'    => __( 'Menú del pie de página', 'vacantespty' ),
			)
		);
		add_image_size( 'vpty-card', 640, 360, true );
	}
);

add_action(
	'wp_enqueue_scripts',
	function () {
		wp_enqueue_style( 'vpty-fonts', 'https://fonts.googleapis.com/css2?family=Lexend:wght@400;500;600;700;800&display=swap', array(), null );
		wp_enqueue_style( 'vpty-style', get_stylesheet_uri(), array( 'vpty-fonts' ), VPTY_THEME_VERSION );
		wp_enqueue_script( 'vpty-main', get_template_directory_uri() . '/assets/js/main.js', array(), VPTY_THEME_VERSION, true );
	}
);

add_filter(
	'wp_resource_hints',
	function ( $urls, $relation ) {
		if ( 'preconnect' === $relation ) {
			$urls[] = array( 'href' => 'https://fonts.gstatic.com', 'crossorigin' );
		}
		return $urls;
	},
	10,
	2
);

add_action(
	'widgets_init',
	function () {
		register_sidebar(
			array(
				'name'          => __( 'Barra lateral del blog', 'vacantespty' ),
				'id'            => 'blog',
				'before_widget' => '<section class="widget %2$s">',
				'after_widget'  => '</section>',
				'before_title'  => '<h3 class="widget__title">',
				'after_title'   => '</h3>',
			)
		);
	}
);

/** The theme needs the Vacantes PTY Core plugin for vacancies, banners and the calculator. */
function vpty_core_active() {
	return function_exists( 'vpty_job_card' );
}

add_action(
	'admin_notices',
	function () {
		if ( ! vpty_core_active() && current_user_can( 'activate_plugins' ) ) {
			echo '<div class="notice notice-error"><p><strong>Vacantes PTY:</strong> ' . esc_html__( 'Activa el plugin "Vacantes PTY Core" para ver vacantes, banners y la calculadora.', 'vacantespty' ) . '</p></div>';
		}
	}
);

function vpty_theme_setting( $key, $fallback = '' ) {
	return function_exists( 'vpty_setting' ) ? vpty_setting( $key ) : $fallback;
}

function vpty_theme_banner( $zone ) {
	if ( function_exists( 'vpty_banner' ) ) {
		echo vpty_banner( $zone ); // phpcs:ignore WordPress.Security.EscapeOutput -- escaped in vpty_banner().
	}
}

/** Emoji shown next to each default job category. */
function vpty_category_icon( $slug ) {
	$icons = array(
		'administracion'  => '🗂️',
		'atencion'        => '🎧',
		'ventas'          => '📈',
		'tecnologia'      => '💻',
		'logistica'       => '🚢',
		'turismo'         => '🏨',
		'salud'           => '🩺',
		'construccion'    => '🏗️',
		'banca'           => '🏦',
		'marketing'       => '📣',
		'recursos'        => '🤝',
		'educacion'       => '🎓',
		'supermercados'   => '🛒',
		'produccion'      => '🏭',
		'seguridad'       => '🛡️',
		'limpieza'        => '🧹',
		'legal'           => '⚖️',
		'sin-experiencia' => '🚀',
	);
	foreach ( $icons as $prefix => $icon ) {
		if ( 0 === strpos( $slug, $prefix ) ) {
			return $icon;
		}
	}
	return '💼';
}

/** Wraps the last word of a title in a highlight span (brand yellow underline). */
function vpty_highlight_last_word( $text ) {
	$words = explode( ' ', trim( $text ) );
	$last  = array_pop( $words );
	return esc_html( implode( ' ', $words ) ) . ' <span class="hl">' . esc_html( $last ) . '</span>';
}

function vpty_active_job_count() {
	$count = get_transient( 'vpty_active_job_count' );
	if ( false === $count && function_exists( 'vpty_get_jobs' ) ) {
		$count = vpty_get_jobs( array( 'posts_per_page' => 1, 'fields' => 'ids' ) )->found_posts;
		set_transient( 'vpty_active_job_count', $count, HOUR_IN_SECONDS );
	}
	return (int) $count;
}

add_action(
	'save_post_vacante',
	function () {
		delete_transient( 'vpty_active_job_count' );
	}
);

function vpty_social_links() {
	return function_exists( 'vpty_social_icons' ) ? vpty_social_icons() : '';
}

/** Fallback menu until the owner builds one in Apariencia → Menús. */
function vpty_default_menu() {
	$items = array(
		get_post_type_archive_link( 'vacante' ) => __( 'Empleos', 'vacantespty' ),
		home_url( '/reto/' )                   => __( '🎯 Reto diario', 'vacantespty' ),
		home_url( '/alertas-de-vacantes/' )    => __( 'Alertas', 'vacantespty' ),
		home_url( '/capacitate/' )             => __( 'Capacítate', 'vacantespty' ),
		home_url( '/curriculum-profesional/' ) => __( 'Tu CV', 'vacantespty' ),
	);
	$blog = get_option( 'page_for_posts' );
	if ( $blog ) {
		$items[ get_permalink( $blog ) ] = __( 'Blog', 'vacantespty' );
	}
	echo '<ul class="menu">';
	foreach ( $items as $url => $label ) {
		if ( $url ) {
			echo '<li><a href="' . esc_url( $url ) . '">' . esc_html( $label ) . '</a></li>';
		}
	}
	echo '</ul>';
}

/** Inserts the "blog" banner zone after the second paragraph of blog posts. */
add_filter(
	'the_content',
	function ( $content ) {
		if ( ! is_singular( 'post' ) || ! in_the_loop() || ! is_main_query() || ! function_exists( 'vpty_banner' ) ) {
			return $content;
		}
		$banner = vpty_banner( 'blog' );
		if ( ! $banner ) {
			return $content;
		}
		$parts = explode( '</p>', $content );
		if ( count( $parts ) > 3 ) {
			$parts[2] = $banner . $parts[2];
			return implode( '</p>', $parts );
		}
		return $content . $banner;
	}
);

add_filter( 'excerpt_length', fn() => 22 );
add_filter( 'excerpt_more', fn() => '…' );
