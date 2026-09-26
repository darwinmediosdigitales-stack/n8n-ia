<?php
defined( 'ABSPATH' ) || exit;

/* ---------- Site settings (Apariencia → Personalizar → Vacantes PTY) ---------- */

function vpty_settings_defaults() {
	return array(
		'hero_titulo'       => __( 'Tu próximo empleo en Panamá, hoy.', 'vacantespty' ),
		'hero_subtitulo'    => __( 'Vacantes nuevas cada día, con salario visible, empresas verificadas y aplicación directa por WhatsApp.', 'vacantespty' ),
		'whatsapp_empresas' => '',
		'canal_alertas'     => '',
		'facebook'          => '',
		'instagram'         => '',
		'tiktok'            => '',
		'linkedin'          => '',
	);
}

function vpty_setting( $key ) {
	$defaults = vpty_settings_defaults();
	$value    = get_theme_mod( 'vpty_' . $key, isset( $defaults[ $key ] ) ? $defaults[ $key ] : '' );
	return is_string( $value ) ? trim( $value ) : $value;
}

add_action(
	'customize_register',
	function ( WP_Customize_Manager $wp_customize ) {
		$wp_customize->add_section( 'vpty', array( 'title' => __( 'Vacantes PTY', 'vacantespty' ), 'priority' => 25 ) );

		$fields = array(
			'hero_titulo'       => array( __( 'Título principal de la portada', 'vacantespty' ), 'text', 'sanitize_text_field' ),
			'hero_subtitulo'    => array( __( 'Subtítulo de la portada', 'vacantespty' ), 'textarea', 'sanitize_textarea_field' ),
			'whatsapp_empresas' => array( __( 'WhatsApp para empresas (publicar vacantes)', 'vacantespty' ), 'text', 'sanitize_text_field' ),
			'canal_alertas'     => array( __( 'Enlace del canal de WhatsApp o Telegram (alertas de empleo)', 'vacantespty' ), 'url', 'esc_url_raw' ),
			'facebook'          => array( 'Facebook', 'url', 'esc_url_raw' ),
			'instagram'         => array( 'Instagram', 'url', 'esc_url_raw' ),
			'tiktok'            => array( 'TikTok', 'url', 'esc_url_raw' ),
			'linkedin'          => array( 'LinkedIn', 'url', 'esc_url_raw' ),
		);
		$defaults = vpty_settings_defaults();

		foreach ( $fields as $key => $f ) {
			$wp_customize->add_setting( 'vpty_' . $key, array( 'default' => $defaults[ $key ], 'sanitize_callback' => $f[2] ) );
			$wp_customize->add_control( 'vpty_' . $key, array( 'label' => $f[0], 'type' => $f[1], 'section' => 'vpty' ) );
		}
	}
);

/* ---------- Activation: default categories, provinces and pages ---------- */

function vpty_default_categories() {
	return array(
		'Administración y Oficina',
		'Atención al Cliente y Call Center',
		'Ventas y Comercial',
		'Tecnología e Informática',
		'Logística, Puertos y Transporte',
		'Turismo, Hoteles y Restaurantes',
		'Salud y Farmacia',
		'Construcción e Ingeniería',
		'Banca, Finanzas y Contabilidad',
		'Marketing y Comunicación',
		'Recursos Humanos',
		'Educación',
		'Supermercados y Retail',
		'Producción y Operarios',
		'Seguridad',
		'Limpieza y Mantenimiento',
		'Legal',
		'Sin experiencia / Primer empleo',
	);
}

function vpty_default_provinces() {
	return array( 'Panamá', 'Panamá Oeste', 'Colón', 'Chiriquí', 'Coclé', 'Veraguas', 'Herrera', 'Los Santos', 'Bocas del Toro', 'Darién' );
}

function vpty_activate() {
	vpty_register_types();

	foreach ( vpty_default_categories() as $name ) {
		if ( ! term_exists( $name, 'categoria_empleo' ) ) {
			wp_insert_term( $name, 'categoria_empleo' );
		}
	}
	foreach ( vpty_default_provinces() as $name ) {
		if ( ! term_exists( $name, 'provincia' ) ) {
			wp_insert_term( $name, 'provincia' );
		}
	}

	vpty_create_page( 'calculadora-salario-neto-panama', __( 'Calculadora de salario neto en Panamá', 'vacantespty' ), "<!-- wp:paragraph -->\n<p>" . __( 'Calcula cuánto recibirás realmente después de Seguro Social, Seguro Educativo e Impuesto sobre la Renta. También calcula tu décimo tercer mes.', 'vacantespty' ) . "</p>\n<!-- /wp:paragraph -->\n\n<!-- wp:shortcode -->\n[vpty_calculadora]\n<!-- /wp:shortcode -->" );
	vpty_create_page( 'publicar-vacante', __( 'Publicar vacante', 'vacantespty' ), "<!-- wp:paragraph -->\n<p>" . __( '¿Tu empresa está contratando? Publica tu vacante en Vacantes PTY y llega a miles de candidatos en todo Panamá. Tu vacante aparece en Google Empleos, en nuestras redes y en nuestro canal de alertas.', 'vacantespty' ) . "</p>\n<!-- /wp:paragraph -->\n\n<!-- wp:shortcode -->\n[vpty_publicar]\n<!-- /wp:shortcode -->" );

	// Only set up a static home + blog page when the site still uses the default "latest posts" home.
	if ( 'posts' === get_option( 'show_on_front' ) ) {
		$home = vpty_create_page( 'inicio', __( 'Inicio', 'vacantespty' ), '' );
		$blog = vpty_create_page( 'blog', __( 'Blog', 'vacantespty' ), '' );
		if ( $home && $blog ) {
			update_option( 'show_on_front', 'page' );
			update_option( 'page_on_front', $home );
			update_option( 'page_for_posts', $blog );
		}
	}

	vpty_create_sample_jobs();
	flush_rewrite_rules();
}

function vpty_create_page( $slug, $title, $content ) {
	$existing = get_page_by_path( $slug );
	if ( $existing ) {
		return $existing->ID;
	}
	return wp_insert_post(
		array(
			'post_type'    => 'page',
			'post_status'  => 'publish',
			'post_name'    => $slug,
			'post_title'   => $title,
			'post_content' => $content,
		)
	);
}

/** A few example vacancies so the design can be previewed; delete them before launch. */
function vpty_create_sample_jobs() {
	if ( get_posts( array( 'post_type' => 'vacante', 'post_status' => 'any', 'posts_per_page' => 1, 'fields' => 'ids' ) ) ) {
		return;
	}
	$vence   = gmdate( 'Y-m-d', strtotime( '+30 days' ) );
	$samples = array(
		array( 'Agente de Call Center Bilingüe (EJEMPLO)', 'Atención al Cliente y Call Center', 'Panamá', 'Empresa Ejemplo BPO', 900, 1100, 'presencial', 'FULL_TIME', '1' ),
		array( 'Desarrollador Web WordPress (EJEMPLO)', 'Tecnología e Informática', 'Panamá', 'Agencia Digital Ejemplo', 1500, 2200, 'remoto', 'FULL_TIME', '' ),
		array( 'Cajero(a) de Supermercado (EJEMPLO)', 'Supermercados y Retail', 'Chiriquí', 'Supermercado Ejemplo', 650, '', 'presencial', 'FULL_TIME', '' ),
		array( 'Asistente Administrativo (EJEMPLO)', 'Administración y Oficina', 'Panamá Oeste', 'Grupo Ejemplo S.A.', 800, 950, 'hibrido', 'FULL_TIME', '' ),
		array( 'Operador de Montacargas (EJEMPLO)', 'Logística, Puertos y Transporte', 'Colón', 'Logística Ejemplo Zona Libre', 850, 1000, 'presencial', 'FULL_TIME', '' ),
		array( 'Mesero(a) medio tiempo (EJEMPLO)', 'Turismo, Hoteles y Restaurantes', 'Panamá', 'Restaurante Ejemplo', 4, '', 'presencial', 'PART_TIME', '' ),
	);

	foreach ( $samples as $s ) {
		$id = wp_insert_post(
			array(
				'post_type'    => 'vacante',
				'post_status'  => 'publish',
				'post_title'   => $s[0],
				'post_content' => "<h2>Funciones</h2>\n<ul><li>Función principal del puesto.</li><li>Otra responsabilidad importante.</li></ul>\n<h2>Requisitos</h2>\n<ul><li>Bachiller completo.</li><li>Disponibilidad inmediata.</li></ul>\n<h2>Beneficios</h2>\n<ul><li>Prestaciones de ley.</li><li>Buen ambiente laboral.</li></ul>",
			)
		);
		if ( ! $id || is_wp_error( $id ) ) {
			continue;
		}
		wp_set_object_terms( $id, $s[1], 'categoria_empleo' );
		wp_set_object_terms( $id, $s[2], 'provincia' );
		$meta = array(
			'empresa'         => $s[3],
			'salario_min'     => $s[4],
			'salario_max'     => $s[5],
			'salario_periodo' => 'PART_TIME' === $s[7] ? 'HOUR' : 'MONTH',
			'modalidad'       => $s[6],
			'tipo'            => $s[7],
			'destacada'       => $s[8],
			'verificada'      => '1',
			'ciudad'          => $s[2],
			'whatsapp'        => '60000000',
			'vence'           => $vence,
		);
		foreach ( $meta as $key => $value ) {
			if ( '' !== $value ) {
				update_post_meta( $id, '_vpty_' . $key, (string) $value );
			}
		}
	}
}
