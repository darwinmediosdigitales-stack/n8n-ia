<?php
defined( 'ABSPATH' ) || exit;

function vpty_leads_table() {
	global $wpdb;
	return $wpdb->prefix . 'vpty_leads';
}

function vpty_lead_categories() {
	return array( 'Administrativo', 'Ventas', 'Call Center', 'Atención al cliente', 'Logística', 'Salud', 'Tecnología', 'Construcción', 'Hotelería y turismo', 'Otro' );
}

function vpty_consent_text() {
	return __( 'Acepto recibir vacantes, novedades y ofertas de Vacantes PTY por WhatsApp y correo. Puedo darme de baja cuando quiera. Mis datos se tratan según la Política de Privacidad.', 'vacantespty' );
}

/** Maps a vacancy's job category to the lead interest category used to prefill forms. */
function vpty_lead_category_for_job( $post_id ) {
	$term = vpty_first_term( 'categoria_empleo', $post_id );
	if ( ! $term ) {
		return '';
	}
	$map = array(
		'administracion' => 'Administrativo',
		'banca'          => 'Administrativo',
		'recursos'       => 'Administrativo',
		'atencion'       => 'Call Center',
		'ventas'         => 'Ventas',
		'supermercados'  => 'Ventas',
		'marketing'      => 'Ventas',
		'logistica'      => 'Logística',
		'produccion'     => 'Logística',
		'salud'          => 'Salud',
		'tecnologia'     => 'Tecnología',
		'construccion'   => 'Construcción',
		'turismo'        => 'Hotelería y turismo',
	);
	foreach ( $map as $prefix => $category ) {
		if ( 0 === strpos( $term->slug, $prefix ) ) {
			return $category;
		}
	}
	return 'Otro';
}

/**
 * Lead capture form. Args: punto (capture point for analytics), categoria (prefill),
 * multiple (checkbox list of categories), compact (hide province).
 */
function vpty_lead_form( $args = array() ) {
	$args = wp_parse_args(
		$args,
		array(
			'punto'     => 'formulario',
			'categoria' => '',
			'multiple'  => false,
			'compact'   => false,
			'boton'     => __( 'Quiero recibir vacantes', 'vacantespty' ),
		)
	);
	$uid     = wp_unique_id( 'vpty-lead-' );
	$privacy = home_url( '/politica-de-privacidad/' );

	ob_start();
	?>
	<form class="vpty-lead<?php echo $args['compact'] ? ' vpty-lead--compact' : ''; ?>" data-vpty-lead data-point="<?php echo esc_attr( $args['punto'] ); ?>" novalidate>
		<input type="hidden" name="punto" value="<?php echo esc_attr( $args['punto'] ); ?>">
		<input type="hidden" name="ts" value="">
		<input type="hidden" name="variante" value="">
		<div class="vpty-lead__hp" aria-hidden="true">
			<label>Empresa <input type="text" name="empresa_web" tabindex="-1" autocomplete="off"></label>
		</div>

		<div class="vpty-lead__row">
			<label class="vpty-lead__field">
				<span><?php esc_html_e( 'Nombre', 'vacantespty' ); ?></span>
				<input type="text" name="nombre" required maxlength="80" autocomplete="given-name">
			</label>
			<label class="vpty-lead__field">
				<span><?php esc_html_e( 'Correo electrónico', 'vacantespty' ); ?></span>
				<input type="email" name="email" required maxlength="120" autocomplete="email" inputmode="email">
			</label>
		</div>
		<div class="vpty-lead__row">
			<label class="vpty-lead__field">
				<span><?php esc_html_e( 'Tu WhatsApp', 'vacantespty' ); ?></span>
				<span class="vpty-lead__phone"><b>+507</b><input type="tel" name="whatsapp" required pattern="[0-9]{4}-?[0-9]{4}" maxlength="9" inputmode="numeric" autocomplete="tel-national" placeholder="6000-0000"></span>
			</label>
			<?php if ( ! $args['multiple'] ) : ?>
				<label class="vpty-lead__field">
					<span><?php esc_html_e( 'Área de interés', 'vacantespty' ); ?></span>
					<select name="categorias[]" required>
						<option value=""><?php esc_html_e( 'Elige una', 'vacantespty' ); ?></option>
						<?php foreach ( vpty_lead_categories() as $cat ) : ?>
							<option <?php selected( $args['categoria'], $cat ); ?>><?php echo esc_html( $cat ); ?></option>
						<?php endforeach; ?>
					</select>
				</label>
			<?php endif; ?>
		</div>

		<?php if ( $args['multiple'] ) : ?>
			<fieldset class="vpty-lead__cats">
				<legend><?php esc_html_e( '¿De qué áreas quieres recibir vacantes? (elige una o varias)', 'vacantespty' ); ?></legend>
				<?php foreach ( vpty_lead_categories() as $cat ) : ?>
					<label><input type="checkbox" name="categorias[]" value="<?php echo esc_attr( $cat ); ?>" <?php checked( $args['categoria'], $cat ); ?>> <?php echo esc_html( $cat ); ?></label>
				<?php endforeach; ?>
			</fieldset>
		<?php endif; ?>

		<?php if ( ! $args['compact'] ) : ?>
			<label class="vpty-lead__field">
				<span><?php esc_html_e( 'Provincia (opcional)', 'vacantespty' ); ?></span>
				<select name="provincia">
					<option value=""><?php esc_html_e( 'Cualquiera', 'vacantespty' ); ?></option>
					<?php foreach ( vpty_default_provinces() as $prov ) : ?>
						<option><?php echo esc_html( $prov ); ?></option>
					<?php endforeach; ?>
				</select>
			</label>
		<?php endif; ?>

		<label class="vpty-lead__consent" for="<?php echo esc_attr( $uid ); ?>">
			<input type="checkbox" id="<?php echo esc_attr( $uid ); ?>" name="consentimiento" value="1" required>
			<span>
				<?php
				echo wp_kses(
					str_replace(
						__( 'Política de Privacidad', 'vacantespty' ),
						'<a href="' . esc_url( $privacy ) . '" target="_blank">' . esc_html__( 'Política de Privacidad', 'vacantespty' ) . '</a>',
						esc_html( vpty_consent_text() )
					),
					array( 'a' => array( 'href' => array(), 'target' => array() ) )
				);
				?>
			</span>
		</label>

		<button type="submit" class="vpty-btn vpty-btn--primary vpty-btn--block"><?php echo esc_html( $args['boton'] ); ?></button>
		<p class="vpty-lead__msg" role="status" aria-live="polite"></p>
	</form>
	<?php
	return ob_get_clean();
}

add_shortcode(
	'vpty_alertas',
	function ( $atts ) {
		$atts = shortcode_atts( array( 'punto' => 'alertas', 'multiple' => '1' ), $atts );
		return vpty_lead_form( array( 'punto' => sanitize_key( $atts['punto'] ), 'multiple' => (bool) $atts['multiple'] ) );
	}
);

/* ---------- Saving leads ---------- */

add_action(
	'rest_api_init',
	function () {
		register_rest_route(
			'vpty/v1',
			'/lead',
			array(
				'methods'             => 'POST',
				'permission_callback' => '__return_true',
				'callback'            => 'vpty_rest_save_lead',
			)
		);
	}
);

function vpty_client_ip_hash() {
	$ip = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';
	return hash_hmac( 'sha256', $ip, wp_salt() );
}

/**
 * Public endpoint (no nonce, because pages are cached): protected by a honeypot,
 * a minimum fill time and a per-IP rate limit.
 */
function vpty_rest_save_lead( WP_REST_Request $request ) {
	$p = $request->get_params();

	$started = isset( $p['ts'] ) ? (int) $p['ts'] : 0;
	if ( ! empty( $p['empresa_web'] ) || ( $started && ( time() * 1000 - $started ) < 2500 ) ) {
		return new WP_REST_Response( array( 'ok' => true ), 200 ); // Silently accept bots.
	}

	$rate_key = 'vpty_rl_' . substr( vpty_client_ip_hash(), 0, 20 );
	$hits     = (int) get_transient( $rate_key );
	if ( $hits >= 8 ) {
		return new WP_REST_Response( array( 'ok' => false, 'message' => __( 'Demasiados intentos. Prueba en unos minutos.', 'vacantespty' ) ), 429 );
	}
	set_transient( $rate_key, $hits + 1, 10 * MINUTE_IN_SECONDS );

	$nombre   = sanitize_text_field( isset( $p['nombre'] ) ? $p['nombre'] : '' );
	$email    = sanitize_email( isset( $p['email'] ) ? $p['email'] : '' );
	$whatsapp = preg_replace( '/\D+/', '', isset( $p['whatsapp'] ) ? (string) $p['whatsapp'] : '' );
	$cats     = array_values( array_intersect( array_map( 'sanitize_text_field', (array) ( isset( $p['categorias'] ) ? $p['categorias'] : array() ) ), vpty_lead_categories() ) );
	$prov     = sanitize_text_field( isset( $p['provincia'] ) ? $p['provincia'] : '' );
	$prov     = in_array( $prov, vpty_default_provinces(), true ) ? $prov : '';
	$punto    = sanitize_key( isset( $p['punto'] ) ? $p['punto'] : '' );
	$variante = in_array( isset( $p['variante'] ) ? $p['variante'] : '', array( 'a', 'b' ), true ) ? $p['variante'] : '';

	if ( strlen( $whatsapp ) === 11 && 0 === strpos( $whatsapp, '507' ) ) {
		$whatsapp = substr( $whatsapp, 3 );
	}

	$errors = array();
	if ( '' === $nombre ) {
		$errors[] = __( 'Escribe tu nombre.', 'vacantespty' );
	}
	if ( ! is_email( $email ) ) {
		$errors[] = __( 'Escribe un correo válido.', 'vacantespty' );
	}
	if ( ! preg_match( '/^[0-9]{8}$/', $whatsapp ) ) {
		$errors[] = __( 'El WhatsApp debe tener 8 dígitos (ej. 6000-0000).', 'vacantespty' );
	}
	if ( ! $cats ) {
		$errors[] = __( 'Elige al menos un área de interés.', 'vacantespty' );
	}
	if ( empty( $p['consentimiento'] ) ) {
		$errors[] = __( 'Debes aceptar recibir comunicaciones para registrarte.', 'vacantespty' );
	}
	if ( $errors ) {
		return new WP_REST_Response( array( 'ok' => false, 'message' => implode( ' ', $errors ) ), 400 );
	}

	$lead = vpty_upsert_lead(
		array(
			'nombre'     => $nombre,
			'email'      => strtolower( $email ),
			'whatsapp'   => '+507' . $whatsapp,
			'categorias' => implode( ', ', $cats ),
			'provincia'  => $prov,
			'punto'      => $punto,
			'variante'   => $variante,
		)
	);

	if ( ! $lead ) {
		return new WP_REST_Response( array( 'ok' => false, 'message' => __( 'No pudimos guardar tus datos. Inténtalo de nuevo.', 'vacantespty' ) ), 500 );
	}

	vpty_brevo_sync( $lead );
	if ( $lead['_is_new'] ) {
		vpty_send_welcome_email( $lead );
	}

	return new WP_REST_Response( array( 'ok' => true ), 200 );
}

/** Inserts a lead or updates it when the email is already registered (re-subscribing it). */
function vpty_upsert_lead( $data ) {
	global $wpdb;
	$table    = vpty_leads_table();
	$existing = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$table} WHERE email = %s", $data['email'] ), ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared

	$data['estado']        = 'activo';
	$data['consentimiento'] = vpty_consent_text();
	$data['consent_at']    = current_time( 'mysql' );
	$data['ip_hash']       = vpty_client_ip_hash();

	if ( $existing ) {
		// Keep the categories they chose before and add the new ones.
		$merged             = array_unique( array_filter( array_map( 'trim', array_merge( explode( ',', $existing['categorias'] ), explode( ',', $data['categorias'] ) ) ) ) );
		$data['categorias'] = implode( ', ', $merged );
		$data['provincia']  = $data['provincia'] ? $data['provincia'] : $existing['provincia'];
		$data['punto']      = $existing['punto'];
		$data['variante']   = $existing['variante'];
		$wpdb->update( $table, $data, array( 'id' => $existing['id'] ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$lead            = array_merge( $existing, $data );
		$lead['_is_new'] = false;
		return $lead;
	}

	$data['token']      = wp_generate_password( 32, false );
	$data['created_at'] = current_time( 'mysql' );
	if ( ! $wpdb->insert( $table, $data ) ) { // phpcs:ignore WordPress.DB.DirectDatabaseQuery
		return null;
	}
	$data['id']      = $wpdb->insert_id;
	$data['_is_new'] = true;
	return $data;
}

/* ---------- Brevo ---------- */

function vpty_brevo_request( $method, $path, $body = null ) {
	$key = vpty_opt( 'brevo_api_key' );
	if ( ! $key ) {
		return null;
	}
	return wp_remote_request(
		'https://api.brevo.com/v3' . $path,
		array(
			'method'  => $method,
			'timeout' => 8,
			'headers' => array(
				'api-key'      => $key,
				'accept'       => 'application/json',
				'content-type' => 'application/json',
			),
			'body'    => null === $body ? null : wp_json_encode( $body ),
		)
	);
}

/** Creates/updates the contact in Brevo with segmentation attributes. */
function vpty_brevo_sync( $lead ) {
	$body = array(
		'email'         => $lead['email'],
		'updateEnabled' => true,
		'attributes'    => array(
			'FIRSTNAME'  => $lead['nombre'],
			'WHATSAPP'   => $lead['whatsapp'],
			'CATEGORIAS' => $lead['categorias'],
			'PROVINCIA'  => $lead['provincia'],
			'PUNTO'      => $lead['punto'],
		),
	);
	$list = (int) vpty_opt( 'brevo_list_id' );
	if ( $list ) {
		$body['listIds'] = array( $list );
	}
	$response = vpty_brevo_request( 'POST', '/contacts', $body );
	if ( $response && ! is_wp_error( $response ) && wp_remote_retrieve_response_code( $response ) < 300 ) {
		global $wpdb;
		$wpdb->update( vpty_leads_table(), array( 'brevo' => 1 ), array( 'email' => $lead['email'] ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
	}
}

/* ---------- Welcome email ---------- */

function vpty_unsubscribe_url( $lead ) {
	return add_query_arg( array( 'vpty_baja' => $lead['token'], 'e' => rawurlencode( $lead['email'] ) ), home_url( '/' ) );
}

function vpty_send_welcome_email( $lead ) {
	$channel = vpty_channel_url( 'bienvenida', 'email' );
	$cv      = home_url( '/curriculum-profesional/' );
	$name    = esc_html( $lead['nombre'] );

	/* translators: %s: first name */
	$subject = sprintf( __( '%s, ya estás en las alertas de Vacantes PTY 🇵🇦', 'vacantespty' ), $lead['nombre'] );
	$body    = '<div style="font-family:Arial,sans-serif;max-width:560px;margin:auto;color:#14203a;line-height:1.6">'
		. '<div style="background:#0A2463;padding:24px;border-radius:12px 12px 0 0;text-align:center"><span style="color:#fff;font-size:24px;font-weight:bold">vacantes</span><span style="color:#F7B928;font-size:24px;font-weight:bold">pty</span></div>'
		. '<div style="padding:24px;border:1px solid #e3e8f2;border-top:0;border-radius:0 0 12px 12px">'
		. '<p>¡Hola, ' . $name . '! 👋</p>'
		. '<p>Ya estás registrado(a) para recibir vacantes de <strong>' . esc_html( $lead['categorias'] ) . '</strong>. Te escribiremos cuando salgan oportunidades de tu área.</p>'
		. '<p><strong>¿Quieres enterarte primero?</strong> En nuestro canal de WhatsApp publicamos vacantes nuevas todos los días. Síguelo y activa la campanita 🔔:</p>'
		. '<p style="text-align:center"><a href="' . esc_url( $channel ) . '" style="display:inline-block;background:#1fae54;color:#fff;padding:14px 26px;border-radius:999px;text-decoration:none;font-weight:bold">Seguir el canal de WhatsApp</a></p>'
		. '<p><strong>¿Tu currículum está listo para pasar los filtros de las empresas?</strong> Te lo hacemos profesional desde ' . esc_html( vpty_money( vpty_cv_min_price() ) ) . ', con formato compatible con los sistemas que usan los reclutadores.</p>'
		. '<p style="text-align:center"><a href="' . esc_url( $cv ) . '" style="color:#0B6BF2;font-weight:bold">Ver planes de currículum →</a></p>'
		. '<p>Recuerda: en Vacantes PTY nunca te cobraremos por aplicar a una vacante.</p>'
		. '<p>¡Éxitos en tu búsqueda!<br>Equipo Vacantes PTY</p>'
		. '<hr style="border:0;border-top:1px solid #e3e8f2">'
		. '<p style="font-size:12px;color:#5b6785">Recibes este correo porque te registraste en empleoshoypanama.com. <a href="' . esc_url( vpty_unsubscribe_url( $lead ) ) . '" style="color:#5b6785">Darme de baja</a> · <a href="' . esc_url( home_url( '/politica-de-privacidad/' ) ) . '" style="color:#5b6785">Política de Privacidad</a></p>'
		. '</div></div>';

	$headers = array( 'Content-Type: text/html; charset=UTF-8' );
	$from    = vpty_opt( 'correo_marca' );
	if ( $from ) {
		$headers[] = 'From: Vacantes PTY <' . $from . '>';
		$headers[] = 'Reply-To: ' . $from;
	}
	$headers[] = 'List-Unsubscribe: <' . vpty_unsubscribe_url( $lead ) . '>';
	wp_mail( $lead['email'], $subject, $body, $headers );
}

/* ---------- Unsubscribe ---------- */

add_action(
	'template_redirect',
	function () {
		// phpcs:disable WordPress.Security.NonceVerification.Recommended -- token-authenticated link.
		if ( empty( $_GET['vpty_baja'] ) || empty( $_GET['e'] ) ) {
			return;
		}
		$token = sanitize_text_field( wp_unslash( $_GET['vpty_baja'] ) );
		$email = sanitize_email( wp_unslash( $_GET['e'] ) );
		// phpcs:enable
		global $wpdb;
		$table = vpty_leads_table();
		$lead  = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$table} WHERE email = %s", $email ), ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$ok    = $lead && hash_equals( $lead['token'], $token );
		if ( $ok ) {
			$wpdb->update( $table, array( 'estado' => 'baja' ), array( 'id' => $lead['id'] ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
			vpty_brevo_request( 'PUT', '/contacts/' . rawurlencode( $email ), array( 'emailBlacklisted' => true ) );
		}
		nocache_headers();
		wp_die(
			$ok ? esc_html__( 'Listo, te dimos de baja. Ya no recibirás más correos de Vacantes PTY. Si fue un error, puedes volver a registrarte en el sitio.', 'vacantespty' ) : esc_html__( 'El enlace de baja no es válido o ya fue usado.', 'vacantespty' ),
			esc_html__( 'Baja de Vacantes PTY', 'vacantespty' ),
			array( 'response' => 200, 'link_url' => esc_url( home_url( '/' ) ), 'link_text' => esc_html__( 'Volver a Vacantes PTY', 'vacantespty' ) )
		);
	}
);

/* ---------- Admin: list and CSV export ---------- */

add_action(
	'admin_menu',
	function () {
		add_submenu_page( 'edit.php?post_type=vacante', __( 'Leads', 'vacantespty' ), __( 'Leads (alertas)', 'vacantespty' ), 'manage_options', 'vpty-leads', 'vpty_render_leads_page' );
	}
);

function vpty_lead_filters_sql() {
	global $wpdb;
	// phpcs:disable WordPress.Security.NonceVerification.Recommended -- read-only filters.
	$cat    = isset( $_GET['cat'] ) ? sanitize_text_field( wp_unslash( $_GET['cat'] ) ) : '';
	$estado = isset( $_GET['estado'] ) ? sanitize_key( wp_unslash( $_GET['estado'] ) ) : 'activo';
	// phpcs:enable
	$where = $wpdb->prepare( 'WHERE estado = %s', $estado );
	if ( $cat ) {
		$where .= $wpdb->prepare( ' AND categorias LIKE %s', '%' . $wpdb->esc_like( $cat ) . '%' );
	}
	return array( $where, $cat, $estado );
}

add_action(
	'admin_post_vpty_export_leads',
	function () {
		if ( ! current_user_can( 'manage_options' ) || ! check_admin_referer( 'vpty_export_leads' ) ) {
			wp_die( esc_html__( 'Sin permiso.', 'vacantespty' ) );
		}
		global $wpdb;
		list( $where ) = vpty_lead_filters_sql();
		$rows          = $wpdb->get_results( 'SELECT nombre, email, whatsapp, categorias, provincia, punto, variante, estado, consent_at, created_at FROM ' . vpty_leads_table() . " {$where} ORDER BY id DESC", ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared

		nocache_headers();
		header( 'Content-Type: text/csv; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename=leads-vacantespty-' . gmdate( 'Y-m-d' ) . '.csv' );
		$out = fopen( 'php://output', 'w' );
		fwrite( $out, "\xEF\xBB\xBF" ); // BOM so Excel opens accents correctly.
		fputcsv( $out, array( 'Nombre', 'Correo', 'WhatsApp', 'Categorías', 'Provincia', 'Punto de captura', 'Variante', 'Estado', 'Consentimiento', 'Registrado' ) );
		foreach ( $rows as $row ) {
			// Prefix values that spreadsheets would evaluate as formulas (phone numbers are safe).
			fputcsv( $out, array_map( fn( $v ) => preg_match( '/^([=@\t\r]|[+\-](?!\d+$))/', (string) $v ) ? "'" . $v : $v, $row ) );
		}
		fclose( $out );
		exit;
	}
);

function vpty_render_leads_page() {
	global $wpdb;
	$table                       = vpty_leads_table();
	list( $where, $cat, $estado ) = vpty_lead_filters_sql();
	$paged                       = max( 1, isset( $_GET['paged'] ) ? absint( $_GET['paged'] ) : 1 ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$per_page                    = 50;
	$total                       = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$table} {$where}" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
	$rows                        = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$table} {$where} ORDER BY id DESC LIMIT %d OFFSET %d", $per_page, ( $paged - 1 ) * $per_page ), ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
	$by_point                    = $wpdb->get_results( "SELECT punto, variante, COUNT(*) AS total FROM {$table} WHERE estado = 'activo' GROUP BY punto, variante ORDER BY total DESC", ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Leads de alertas de vacantes', 'vacantespty' ); ?></h1>

		<p>
			<?php foreach ( $by_point as $row ) : ?>
				<span style="display:inline-block;margin:0 8px 8px 0;padding:6px 12px;background:#fff;border:1px solid #ccd0d4;border-radius:4px">
					<strong><?php echo esc_html( $row['punto'] ? $row['punto'] : '—' ); ?></strong><?php echo $row['variante'] ? ' (' . esc_html( strtoupper( $row['variante'] ) ) . ')' : ''; ?>: <?php echo (int) $row['total']; ?>
				</span>
			<?php endforeach; ?>
		</p>

		<form method="get" style="margin:12px 0">
			<input type="hidden" name="post_type" value="vacante">
			<input type="hidden" name="page" value="vpty-leads">
			<select name="cat">
				<option value=""><?php esc_html_e( 'Todas las áreas', 'vacantespty' ); ?></option>
				<?php foreach ( vpty_lead_categories() as $c ) : ?>
					<option <?php selected( $cat, $c ); ?>><?php echo esc_html( $c ); ?></option>
				<?php endforeach; ?>
			</select>
			<select name="estado">
				<option value="activo" <?php selected( $estado, 'activo' ); ?>><?php esc_html_e( 'Activos', 'vacantespty' ); ?></option>
				<option value="baja" <?php selected( $estado, 'baja' ); ?>><?php esc_html_e( 'Dados de baja', 'vacantespty' ); ?></option>
			</select>
			<?php submit_button( __( 'Filtrar', 'vacantespty' ), 'secondary', '', false ); ?>
			<a class="button button-primary" href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=vpty_export_leads&cat=' . rawurlencode( $cat ) . '&estado=' . $estado ), 'vpty_export_leads' ) ); ?>"><?php esc_html_e( 'Exportar a Excel (CSV)', 'vacantespty' ); ?></a>
		</form>

		<p>
			<?php
			/* translators: %d: number of leads */
			echo esc_html( sprintf( __( '%d registros', 'vacantespty' ), $total ) );
			?>
		</p>
		<table class="widefat striped">
			<thead><tr><th><?php esc_html_e( 'Nombre', 'vacantespty' ); ?></th><th><?php esc_html_e( 'Correo', 'vacantespty' ); ?></th><th>WhatsApp</th><th><?php esc_html_e( 'Áreas', 'vacantespty' ); ?></th><th><?php esc_html_e( 'Provincia', 'vacantespty' ); ?></th><th><?php esc_html_e( 'Punto', 'vacantespty' ); ?></th><th>Brevo</th><th><?php esc_html_e( 'Fecha', 'vacantespty' ); ?></th></tr></thead>
			<tbody>
				<?php foreach ( $rows as $row ) : ?>
					<tr>
						<td><?php echo esc_html( $row['nombre'] ); ?></td>
						<td><?php echo esc_html( $row['email'] ); ?></td>
						<td><?php echo esc_html( $row['whatsapp'] ); ?></td>
						<td><?php echo esc_html( $row['categorias'] ); ?></td>
						<td><?php echo esc_html( $row['provincia'] ); ?></td>
						<td><?php echo esc_html( $row['punto'] . ( $row['variante'] ? ' (' . strtoupper( $row['variante'] ) . ')' : '' ) ); ?></td>
						<td><?php echo $row['brevo'] ? '✔' : '—'; ?></td>
						<td><?php echo esc_html( $row['created_at'] ); ?></td>
					</tr>
				<?php endforeach; ?>
				<?php if ( ! $rows ) : ?>
					<tr><td colspan="8"><?php esc_html_e( 'Todavía no hay registros.', 'vacantespty' ); ?></td></tr>
				<?php endif; ?>
			</tbody>
		</table>
		<?php
		echo wp_kses_post(
			paginate_links(
				array(
					'base'    => add_query_arg( 'paged', '%#%' ),
					'current' => $paged,
					'total'   => max( 1, (int) ceil( $total / $per_page ) ),
				)
			)
		);
		?>
	</div>
	<?php
}
