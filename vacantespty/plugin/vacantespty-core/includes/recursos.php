<?php
defined( 'ABSPATH' ) || exit;

/*
 * "Capacítate y destácate": courses and certifications (own and affiliate) that
 * help candidates get hired, plus the per-category "Mejora tu perfil" block.
 */

function vpty_resource_areas() {
	return array(
		'empleabilidad' => array( __( 'Empleabilidad', 'vacantespty' ), '🚀' ),
		'excel'         => array( __( 'Excel y ofimática', 'vacantespty' ), '📊' ),
		'atencion'      => array( __( 'Atención al cliente', 'vacantespty' ), '🎧' ),
		'ventas'        => array( __( 'Ventas', 'vacantespty' ), '📈' ),
		'marketing'     => array( __( 'Marketing digital', 'vacantespty' ), '📣' ),
		'ingles'        => array( __( 'Inglés', 'vacantespty' ), '🗣️' ),
		'tecnologia'    => array( __( 'Tecnología', 'vacantespty' ), '💻' ),
	);
}

function vpty_resource_programs() {
	return array(
		'gratis'   => __( 'Gratis (no afiliado)', 'vacantespty' ),
		'hotmart'  => 'Hotmart',
		'coursera' => 'Coursera',
		'udemy'    => 'Udemy',
		'otro'     => __( 'Otro afiliado', 'vacantespty' ),
	);
}

function vpty_resource_fields() {
	return array(
		'area'        => array( 'label' => __( 'Área', 'vacantespty' ), 'type' => 'select', 'options' => wp_list_pluck( vpty_resource_areas(), 0 ) ),
		'programa'    => array( 'label' => __( 'Programa', 'vacantespty' ), 'type' => 'select', 'options' => vpty_resource_programs() ),
		'para_quien'  => array( 'label' => __( 'Para quién sirve (1-2 líneas)', 'vacantespty' ), 'type' => 'text' ),
		'por_que'     => array( 'label' => __( 'Por qué ayuda a conseguir empleo', 'vacantespty' ), 'type' => 'text' ),
		'url'         => array( 'label' => __( 'Enlace (pega aquí tu enlace de afiliado)', 'vacantespty' ), 'type' => 'url' ),
		'boton'       => array( 'label' => __( 'Texto del botón', 'vacantespty' ), 'type' => 'text', 'placeholder' => __( 'Ver curso', 'vacantespty' ) ),
		'gratis'      => array( 'label' => __( 'Es gratis', 'vacantespty' ), 'type' => 'checkbox' ),
		'certificado' => array( 'label' => __( 'Incluye certificado', 'vacantespty' ), 'type' => 'checkbox' ),
		'destacado'   => array( 'label' => __( 'Destacado', 'vacantespty' ), 'type' => 'checkbox' ),
	);
}

add_action(
	'init',
	function () {
		register_post_type(
			'recurso',
			array(
				'labels'        => array(
					'name'               => __( 'Capacítate', 'vacantespty' ),
					'singular_name'      => __( 'Recurso', 'vacantespty' ),
					'add_new'            => __( 'Añadir curso o recurso', 'vacantespty' ),
					'add_new_item'       => __( 'Añadir curso o recurso', 'vacantespty' ),
					'edit_item'          => __( 'Editar recurso', 'vacantespty' ),
					'all_items'          => __( 'Todos los recursos', 'vacantespty' ),
					'featured_image'     => __( 'Imagen del recurso', 'vacantespty' ),
					'set_featured_image' => __( 'Subir imagen', 'vacantespty' ),
				),
				'public'        => false,
				'show_ui'       => true,
				'menu_icon'     => 'dashicons-welcome-learn-more',
				'menu_position' => 7,
				'supports'      => array( 'title', 'thumbnail', 'page-attributes' ),
			)
		);
	}
);

add_action(
	'add_meta_boxes',
	function () {
		add_meta_box( 'vpty_recurso', __( 'Datos del recurso', 'vacantespty' ), 'vpty_render_resource_box', 'recurso', 'normal', 'high' );
	}
);

function vpty_render_resource_box( $post ) {
	wp_nonce_field( 'vpty_save_resource', 'vpty_resource_nonce' );
	echo '<div class="vpty-grid" style="display:grid;grid-template-columns:repeat(auto-fill,minmax(260px,1fr));gap:4px 20px">';
	foreach ( vpty_resource_fields() as $key => $field ) {
		vpty_render_field( $key, $field, get_post_meta( $post->ID, '_vpty_' . $key, true ) );
	}
	echo '</div>';

	$selected = (array) get_post_meta( $post->ID, '_vpty_job_cats', true );
	echo '<p><strong>' . esc_html__( 'Mostrar en las vacantes de estas categorías ("Mejora tu perfil para esta vacante")', 'vacantespty' ) . '</strong></p><div style="columns:3">';
	foreach ( (array) get_terms( array( 'taxonomy' => 'categoria_empleo', 'hide_empty' => false ) ) as $term ) {
		printf(
			'<label style="display:block"><input type="checkbox" name="vpty_job_cats[]" value="%d" %s> %s</label>',
			(int) $term->term_id,
			checked( in_array( (int) $term->term_id, array_map( 'intval', $selected ), true ), true, false ),
			esc_html( $term->name )
		);
	}
	echo '</div>';
}

add_action(
	'save_post_recurso',
	function ( $post_id ) {
		if ( ! vpty_can_save( $post_id, 'vpty_resource_nonce', 'vpty_save_resource' ) ) {
			return;
		}
		$input = isset( $_POST['vpty'] ) ? wp_unslash( (array) $_POST['vpty'] ) : array(); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		foreach ( vpty_resource_fields() as $key => $field ) {
			update_post_meta( $post_id, '_vpty_' . $key, vpty_sanitize_field( $field, isset( $input[ $key ] ) ? trim( (string) $input[ $key ] ) : '' ) );
		}
		$cats = isset( $_POST['vpty_job_cats'] ) ? array_map( 'absint', (array) wp_unslash( $_POST['vpty_job_cats'] ) ) : array();
		update_post_meta( $post_id, '_vpty_job_cats', $cats );
	}
);

function vpty_resource_card( $post_id, $compact = false ) {
	$area     = vpty_get( 'area', $post_id );
	$areas    = vpty_resource_areas();
	$programa = vpty_get( 'programa', $post_id ) ?: 'gratis';
	$url      = vpty_get( 'url', $post_id );
	$rel      = 'gratis' === $programa ? 'noopener' : 'sponsored nofollow noopener';
	$boton    = vpty_get( 'boton', $post_id ) ?: __( 'Ver curso', 'vacantespty' );

	ob_start();
	?>
	<article class="vpty-res<?php echo $compact ? ' vpty-res--compact' : ''; ?><?php echo vpty_get( 'destacado', $post_id ) ? ' vpty-res--featured' : ''; ?>">
		<div class="vpty-res__media">
			<?php
			if ( has_post_thumbnail( $post_id ) ) {
				echo get_the_post_thumbnail( $post_id, 'medium', array( 'loading' => 'lazy' ) );
			} else {
				echo '<span>' . esc_html( isset( $areas[ $area ] ) ? $areas[ $area ][1] : '🎓' ) . '</span>';
			}
			?>
		</div>
		<div class="vpty-res__body">
			<div class="vpty-card__badges">
				<?php if ( vpty_get( 'gratis', $post_id ) ) : ?>
					<span class="vpty-badge vpty-badge--free"><?php esc_html_e( 'Gratis', 'vacantespty' ); ?></span>
				<?php endif; ?>
				<?php if ( vpty_get( 'certificado', $post_id ) ) : ?>
					<span class="vpty-badge"><?php esc_html_e( 'Con certificado', 'vacantespty' ); ?></span>
				<?php endif; ?>
			</div>
			<h3 class="vpty-res__title"><?php echo esc_html( get_the_title( $post_id ) ); ?></h3>
			<?php if ( vpty_get( 'para_quien', $post_id ) ) : ?>
				<p class="vpty-res__who"><?php echo esc_html( vpty_get( 'para_quien', $post_id ) ); ?></p>
			<?php endif; ?>
			<?php if ( ! $compact && vpty_get( 'por_que', $post_id ) ) : ?>
				<p class="vpty-res__why">✅ <?php echo esc_html( vpty_get( 'por_que', $post_id ) ); ?></p>
			<?php endif; ?>
			<?php if ( $url ) : ?>
				<a class="vpty-btn vpty-btn--primary" href="<?php echo esc_url( $url ); ?>" target="_blank" rel="<?php echo esc_attr( $rel ); ?>" data-vpty-track="<?php echo 'gratis' === $programa ? 'resource_click' : 'affiliate_click'; ?>" data-vpty-program="<?php echo esc_attr( $programa ); ?>" data-vpty-point="<?php echo esc_attr( sanitize_title( get_the_title( $post_id ) ) ); ?>"><?php echo esc_html( $boton ); ?></a>
			<?php endif; ?>
		</div>
	</article>
	<?php
	return ob_get_clean();
}

function vpty_cv_promo_card( $point ) {
	ob_start();
	?>
	<div class="vpty-cv-promo">
		<div>
			<span class="vpty-badge vpty-badge--featured"><?php esc_html_e( 'Servicio de Vacantes PTY', 'vacantespty' ); ?></span>
			<h3><?php esc_html_e( 'Te creamos tu currículum profesional con los mejores sistemas', 'vacantespty' ); ?></h3>
			<p>
				<?php
				/* translators: %s: lowest plan price */
				echo esc_html( sprintf( __( 'Formato compatible con los filtros ATS que usan las empresas. Desde %s, listo en poco tiempo.', 'vacantespty' ), vpty_money( vpty_cv_min_price() ) ) );
				?>
			</p>
		</div>
		<a class="vpty-btn vpty-btn--accent" href="<?php echo esc_url( home_url( '/curriculum-profesional/' ) ); ?>" data-vpty-track="cv_plan_click" data-vpty-point="<?php echo esc_attr( $point ); ?>"><?php esc_html_e( 'Ver planes', 'vacantespty' ); ?></a>
	</div>
	<?php
	return ob_get_clean();
}

function vpty_affiliate_notice() {
	return '<p class="vpty-aff-note">' . wp_kses(
		sprintf(
			/* translators: %s: disclosure page URL */
			__( 'Algunos enlaces de esta sección son de afiliado: si compras a través de ellos, Vacantes PTY puede recibir una comisión sin costo extra para ti. Solo recomendamos lo que de verdad ayuda a conseguir empleo. <a href="%s">Más información</a>.', 'vacantespty' ),
			esc_url( home_url( '/divulgacion-de-afiliados/' ) )
		),
		array( 'a' => array( 'href' => array() ) )
	) . '</p>';
}

/** Full "Capacítate" section: own CV service first, then courses grouped by area. */
add_shortcode(
	'vpty_capacitate',
	function () {
		$resources = get_posts(
			array(
				'post_type'      => 'recurso',
				'posts_per_page' => 100,
				'orderby'        => array( 'menu_order' => 'ASC', 'date' => 'DESC' ),
			)
		);
		$by_area   = array();
		foreach ( $resources as $r ) {
			$by_area[ vpty_get( 'area', $r->ID ) ?: 'empleabilidad' ][] = $r->ID;
		}

		ob_start();
		echo vpty_cv_promo_card( 'capacitate' ); // phpcs:ignore WordPress.Security.EscapeOutput
		echo '<nav class="filters vpty-res-nav">';
		foreach ( vpty_resource_areas() as $key => $area ) {
			if ( ! empty( $by_area[ $key ] ) ) {
				echo '<a class="filters__chip" href="#area-' . esc_attr( $key ) . '">' . esc_html( $area[1] . ' ' . $area[0] ) . '</a>';
			}
		}
		echo '</nav>';
		foreach ( vpty_resource_areas() as $key => $area ) {
			if ( empty( $by_area[ $key ] ) ) {
				continue;
			}
			echo '<section class="vpty-res-group" id="area-' . esc_attr( $key ) . '"><h2>' . esc_html( $area[1] . ' ' . $area[0] ) . '</h2><div class="vpty-res-grid">';
			foreach ( $by_area[ $key ] as $id ) {
				echo vpty_resource_card( $id ); // phpcs:ignore WordPress.Security.EscapeOutput
			}
			echo '</div></section>';
		}
		echo vpty_affiliate_notice(); // phpcs:ignore WordPress.Security.EscapeOutput
		return ob_get_clean();
	}
);

/** Up to 3 resources configured for the vacancy's category, for the "Mejora tu perfil" block. */
function vpty_resources_for_job( $post_id, $limit = 3 ) {
	$term = vpty_first_term( 'categoria_empleo', $post_id );
	if ( ! $term ) {
		return array();
	}
	$matches = array();
	foreach ( get_posts( array( 'post_type' => 'recurso', 'posts_per_page' => 100, 'fields' => 'ids', 'orderby' => 'menu_order', 'order' => 'ASC' ) ) as $id ) {
		if ( in_array( (int) $term->term_id, array_map( 'intval', (array) get_post_meta( $id, '_vpty_job_cats', true ) ), true ) ) {
			$matches[] = $id;
		}
	}
	return array_slice( $matches, 0, $limit );
}

function vpty_profile_block( $post_id ) {
	$ids = vpty_resources_for_job( $post_id );
	if ( ! $ids ) {
		return '';
	}
	$html = '<section class="vpty-profile"><h3>🎯 ' . esc_html__( 'Mejora tu perfil para esta vacante', 'vacantespty' ) . '</h3><div class="vpty-profile__list">';
	foreach ( $ids as $id ) {
		$html .= vpty_resource_card( $id, true );
	}
	return $html . '</div><a class="vpty-link" href="' . esc_url( home_url( '/capacitate/' ) ) . '">' . esc_html__( 'Ver más cursos y certificaciones →', 'vacantespty' ) . '</a></section>';
}

/** Initial resources; the owner replaces URLs with affiliate links later. */
function vpty_seed_resources() {
	if ( get_posts( array( 'post_type' => 'recurso', 'post_status' => 'any', 'posts_per_page' => 1, 'fields' => 'ids' ) ) ) {
		return;
	}
	$cat_ids = function ( $prefixes ) {
		$ids = array();
		foreach ( (array) get_terms( array( 'taxonomy' => 'categoria_empleo', 'hide_empty' => false ) ) as $t ) {
			foreach ( $prefixes as $p ) {
				if ( 0 === strpos( $t->slug, $p ) ) {
					$ids[] = (int) $t->term_id;
				}
			}
		}
		return $ids;
	};

	$items = array(
		array( 'Capacítate para el Empleo (Fundación Carlos Slim)', 'empleabilidad', 'gratis', 'Para quien busca su primer empleo o quiere aprender un oficio nuevo.', 'Cursos por oficio con certificado gratuito que puedes agregar a tu CV.', 'https://capacitateparaelempleo.org/', 1, 1, array( 'sin-experiencia', 'supermercados', 'produccion', 'limpieza', 'seguridad' ) ),
		array( 'Cursos gratuitos del INADEH', 'empleabilidad', 'gratis', 'Para panameños que quieren formación técnica presencial o virtual.', 'Certificación reconocida en Panamá, muy valorada por empresas locales.', 'https://www.inadeh.edu.pa/', 1, 1, array( 'sin-experiencia', 'construccion', 'turismo', 'produccion' ) ),
		array( 'Certificados Profesionales de Google', 'tecnologia', 'coursera', 'Para quien quiere entrar a soporte de TI, análisis de datos, marketing digital o gestión de proyectos sin experiencia previa.', 'Certificado de Google reconocido por empresas; se completa en unos meses a tu ritmo.', 'https://www.coursera.org/google-career-certificates', 0, 1, array( 'tecnologia', 'marketing', 'administracion' ) ),
		array( 'Excel de cero a avanzado', 'excel', 'udemy', 'Para puestos administrativos, contables, logísticos y de recursos humanos.', 'Excel es la habilidad más pedida en vacantes de oficina en Panamá.', 'https://www.udemy.com/topic/excel/', 0, 1, array( 'administracion', 'banca', 'recursos', 'logistica' ) ),
		array( 'Excel gratis (GCFGlobal)', 'excel', 'gratis', 'Para aprender lo básico de Excel paso a paso, sin costo.', 'Te prepara para las pruebas de Excel que hacen en muchas entrevistas.', 'https://edu.gcfglobal.org/es/excel-2016/', 1, 0, array( 'administracion', 'supermercados', 'sin-experiencia' ) ),
		array( 'Certificación de servicio al cliente (HubSpot Academy)', 'atencion', 'gratis', 'Para call center, recepción y atención al cliente.', 'Certificado gratuito que demuestra que conoces las buenas prácticas de servicio.', 'https://academy.hubspot.com/', 1, 1, array( 'atencion', 'turismo', 'supermercados' ) ),
		array( 'Cursos de atención al cliente', 'atencion', 'coursera', 'Para quien quiere crecer en servicio al cliente y call center.', 'Aprende a manejar clientes difíciles y métricas de calidad que piden los call center.', 'https://www.coursera.org/search?query=atenci%C3%B3n%20al%20cliente', 0, 1, array( 'atencion' ) ),
		array( 'Técnicas de ventas', 'ventas', 'udemy', 'Para vendedores, ejecutivos comerciales y quien vende por comisión.', 'Las empresas contratan a quien demuestra que sabe cerrar ventas.', 'https://www.udemy.com/topic/sales-skills/', 0, 1, array( 'ventas', 'supermercados' ) ),
		array( 'Marketing digital con Meta Blueprint', 'marketing', 'gratis', 'Para community managers y quien quiere trabajar en redes sociales.', 'Formación oficial de Meta (Facebook e Instagram), gratuita.', 'https://www.facebookblueprint.com/', 1, 0, array( 'marketing' ) ),
		array( 'Test de inglés EF SET con certificado', 'ingles', 'gratis', 'Para vacantes bilingües de call center, turismo y empresas multinacionales.', 'Te da un certificado gratuito de tu nivel de inglés para poner en el CV.', 'https://www.efset.org/', 1, 1, array( 'atencion', 'turismo', 'logistica' ) ),
		array( 'Aprende inglés con Duolingo', 'ingles', 'gratis', 'Para practicar inglés todos los días desde el celular.', 'El inglés abre las vacantes mejor pagadas en Panamá.', 'https://www.duolingo.com/', 1, 0, array( 'atencion', 'turismo' ) ),
		array( 'Cisco Networking Academy', 'tecnologia', 'gratis', 'Para quien quiere trabajar en redes, ciberseguridad o soporte técnico.', 'Cursos gratuitos con insignias reconocidas por la industria tecnológica.', 'https://www.netacad.com/', 1, 1, array( 'tecnologia' ) ),
		array( 'Programación gratis con freeCodeCamp', 'tecnologia', 'gratis', 'Para aprender desarrollo web desde cero.', 'Certificaciones gratuitas y proyectos reales para tu portafolio.', 'https://www.freecodecamp.org/espanol/', 1, 1, array( 'tecnologia' ) ),
	);

	foreach ( $items as $i => $it ) {
		$id = wp_insert_post( array( 'post_type' => 'recurso', 'post_status' => 'publish', 'post_title' => $it[0], 'menu_order' => $i ) );
		if ( ! $id || is_wp_error( $id ) ) {
			continue;
		}
		$meta = array(
			'area'        => $it[1],
			'programa'    => $it[2],
			'para_quien'  => $it[3],
			'por_que'     => $it[4],
			'url'         => $it[5],
			'gratis'      => $it[6] ? '1' : '',
			'certificado' => $it[7] ? '1' : '',
			'boton'       => $it[6] ? __( 'Empezar gratis', 'vacantespty' ) : __( 'Ver curso', 'vacantespty' ),
		);
		foreach ( $meta as $k => $v ) {
			update_post_meta( $id, '_vpty_' . $k, $v );
		}
		update_post_meta( $id, '_vpty_job_cats', $cat_ids( $it[8] ) );
	}
}
