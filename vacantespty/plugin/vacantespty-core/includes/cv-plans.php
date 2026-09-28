<?php
defined( 'ABSPATH' ) || exit;

function vpty_orders_table() {
	global $wpdb;
	return $wpdb->prefix . 'vpty_orders';
}

/** Plan catalog; names and prices can be changed in Vacantes → Ajustes. */
function vpty_cv_plans() {
	$plans = vpty_cv_plans_defaults();
	foreach ( $plans as $key => $plan ) {
		$plans[ $key ]['name']  = vpty_opt( 'plan_' . $key . '_nombre' );
		$plans[ $key ]['price'] = vpty_opt( 'plan_' . $key . '_precio' );
	}
	return $plans;
}

function vpty_cv_min_price() {
	return min( array_map( fn( $p ) => (float) $p['price'], vpty_cv_plans() ) );
}

function vpty_cv_plans_defaults() {
	return array(
		'basico'      => array(
			'name'    => __( 'Básico', 'vacantespty' ),
			'price'   => '4.99',
			'tagline' => __( 'Ideal para una corrección rápida antes de aplicar', 'vacantespty' ),
			'bullets' => array(
				__( 'Corrección de formato y estructura del currículum que ya tienes', 'vacantespty' ),
				__( 'Optimización para que sea compatible con los sistemas ATS que filtran candidatos', 'vacantespty' ),
				__( 'Ajustes puntuales sobre tu CV actual', 'vacantespty' ),
			),
		),
		'profesional' => array(
			'name'    => __( 'Profesional', 'vacantespty' ),
			'price'   => '6.99',
			'tagline' => __( 'El más elegido: el equilibrio entre resultado y costo', 'vacantespty' ),
			'badge'   => __( 'Más elegido', 'vacantespty' ),
			'bullets' => array(
				__( 'Redacción completa del currículum adaptada al área a la que aplicas', 'vacantespty' ),
				__( 'Lenguaje ajustado a las palabras clave que buscan los reclutadores de tu sector', 'vacantespty' ),
				__( 'Corrección de estructura, logros y jerarquía de la información', 'vacantespty' ),
				__( 'Formato compatible con sistemas ATS', 'vacantespty' ),
			),
		),
		'premium'     => array(
			'name'    => __( 'Marca Personal', 'vacantespty' ),
			'price'   => '14.99',
			'tagline' => __( 'La opción más completa: para que te recuerden, no solo para que te llamen', 'vacantespty' ),
			'bullets' => array(
				__( 'Currículum optimizado con el sistema ATS 2026, el estándar que usan las empresas para filtrar candidatos', 'vacantespty' ),
				__( 'Optimización de tu perfil en plataformas profesionales de empleo', 'vacantespty' ),
				__( 'Prioridad de acceso a nuestro grupo premium de vacantes', 'vacantespty' ),
				__( 'Consejo y estrategia personalizada para tu búsqueda de empleo', 'vacantespty' ),
				__( 'Asesoría para emprender y vender por comisiones con marcas de alto valor desde tu casa', 'vacantespty' ),
			),
		),
	);
}

function vpty_yappy_enabled() {
	return vpty_opt( 'yappy_merchant_id' ) && vpty_opt( 'yappy_secret' );
}

function vpty_cv_whatsapp_url( $message ) {
	return vpty_whatsapp_url( vpty_opt( 'cv_whatsapp' ), $message );
}

function vpty_format_phone( $digits ) {
	$digits = preg_replace( '/\D+/', '', (string) $digits );
	return strlen( $digits ) === 8 ? substr( $digits, 0, 4 ) . '-' . substr( $digits, 4 ) : $digits;
}

/**
 * Pricing table with Yappy checkout. After paying, the customer sends the
 * confirmation and their current CV to the brand's WhatsApp.
 */
add_shortcode(
	'vpty_planes_cv',
	function () {
		$yappy = vpty_yappy_enabled();
		if ( $yappy ) {
			wp_enqueue_script( 'vpty-yappy', VPTY_URL . 'assets/yappy.js', array(), VPTY_VERSION, true );
			wp_localize_script(
				'vpty-yappy',
				'vptyYappy',
				array(
					'endpoint' => esc_url_raw( rest_url( 'vpty/v1/yappy/order' ) ),
					'whatsapp' => vpty_phone_digits( vpty_opt( 'cv_whatsapp' ) ),
					'msgOk'    => __( '¡Pago recibido! 🎉 Último paso: envíanos tu confirmación y tu currículum actual por WhatsApp.', 'vacantespty' ),
					'msgError' => __( 'El pago no se completó. Puedes intentarlo de nuevo o escribirnos por WhatsApp.', 'vacantespty' ),
				)
			);
		}

		ob_start();
		?>
		<div class="vpty-plans" data-vpty-plans>
			<?php foreach ( vpty_cv_plans() as $key => $plan ) : ?>
				<?php
				/* translators: 1: plan name, 2: price */
				$wa_msg = sprintf( __( 'Hola Vacantes PTY 👋 Quiero el Plan %1$s (B/. %2$s) para mi currículum.', 'vacantespty' ), $plan['name'], $plan['price'] );
				?>
				<article class="vpty-plan<?php echo ! empty( $plan['badge'] ) ? ' vpty-plan--featured' : ''; ?>">
					<?php if ( ! empty( $plan['badge'] ) ) : ?>
						<span class="vpty-plan__badge"><?php echo esc_html( $plan['badge'] ); ?></span>
					<?php endif; ?>
					<h3 class="vpty-plan__name"><?php echo esc_html( sprintf( /* translators: %s: plan name */ __( 'Plan %s', 'vacantespty' ), $plan['name'] ) ); ?></h3>
					<p class="vpty-plan__price"><small>B/.</small><?php echo esc_html( $plan['price'] ); ?></p>
					<p class="vpty-plan__tagline"><?php echo esc_html( $plan['tagline'] ); ?></p>
					<ul>
						<?php foreach ( $plan['bullets'] as $bullet ) : ?>
							<li><?php echo esc_html( $bullet ); ?></li>
						<?php endforeach; ?>
					</ul>
					<?php if ( $yappy ) : ?>
						<button type="button" class="vpty-btn vpty-btn--accent vpty-btn--block" data-plan="<?php echo esc_attr( $key ); ?>" data-plan-name="<?php echo esc_attr( $plan['name'] ); ?>" data-plan-price="<?php echo esc_attr( $plan['price'] ); ?>" data-vpty-track="cv_plan_click" data-vpty-point="<?php echo esc_attr( $key ); ?>"><?php esc_html_e( 'Pagar con Yappy', 'vacantespty' ); ?></button>
					<?php endif; ?>
					<a class="vpty-btn <?php echo $yappy ? 'vpty-btn--ghost' : 'vpty-btn--accent'; ?> vpty-btn--block" href="<?php echo esc_url( vpty_cv_whatsapp_url( $wa_msg ) ); ?>" target="_blank" rel="noopener" data-vpty-track="cv_plan_click" data-vpty-point="<?php echo esc_attr( $key ); ?>_whatsapp"><?php esc_html_e( 'Pedir por WhatsApp', 'vacantespty' ); ?></a>
				</article>
			<?php endforeach; ?>
		</div>

		<?php if ( $yappy ) : ?>
			<div class="vpty-checkout" data-vpty-checkout hidden>
				<button type="button" class="vpty-popup__close" data-checkout-close aria-label="<?php esc_attr_e( 'Cerrar', 'vacantespty' ); ?>">×</button>
				<h3 data-checkout-title></h3>
				<form data-checkout-form novalidate>
					<input type="hidden" name="plan" value="">
					<label class="vpty-lead__field"><span><?php esc_html_e( 'Tu nombre', 'vacantespty' ); ?></span><input type="text" name="nombre" required maxlength="80" autocomplete="name"></label>
					<label class="vpty-lead__field"><span><?php esc_html_e( 'Tu número de Yappy', 'vacantespty' ); ?></span><span class="vpty-lead__phone"><b>+507</b><input type="tel" name="alias" required inputmode="numeric" maxlength="9" placeholder="6000-0000"></span></label>
					<p class="vpty-checkout__hint"><?php esc_html_e( 'Toca el botón de Yappy y aprueba el pago en la app de tu celular.', 'vacantespty' ); ?></p>
					<btn-yappy theme="blue" rounded="true"></btn-yappy>
					<p class="vpty-lead__msg" role="status" aria-live="polite" data-checkout-msg></p>
				</form>
				<div data-checkout-done hidden>
					<p data-checkout-done-msg></p>
					<a class="vpty-btn vpty-btn--whatsapp vpty-btn--block" data-checkout-wa href="#" target="_blank" rel="noopener"><?php esc_html_e( 'Enviar confirmación por WhatsApp', 'vacantespty' ); ?></a>
				</div>
			</div>
		<?php endif; ?>

		<div class="vpty-plans-note">
			<strong>📲 <?php esc_html_e( '¿Ya pagaste?', 'vacantespty' ); ?></strong>
			<?php
			/* translators: %s: WhatsApp number */
			echo esc_html( sprintf( __( 'Envía tu confirmación de pago y tu currículum actual a nuestro WhatsApp %s y empezamos a trabajar en tu CV.', 'vacantespty' ), vpty_format_phone( vpty_opt( 'cv_whatsapp' ) ) ) );
			?>
			<a href="<?php echo esc_url( vpty_cv_whatsapp_url( __( 'Hola Vacantes PTY 👋 Ya realicé mi pago del plan de currículum. Te envío la confirmación.', 'vacantespty' ) ) ); ?>" target="_blank" rel="noopener" data-vpty-track="cv_confirmation_click" data-vpty-point="nota"><?php esc_html_e( 'Enviar confirmación', 'vacantespty' ); ?> →</a>
		</div>
		<?php
		return ob_get_clean();
	}
);

/* ---------- Yappy Botón de Pago (v2) ---------- */

function vpty_yappy_api_base() {
	return vpty_opt( 'yappy_sandbox' ) ? 'https://api-comecom-uat.yappycloud.com' : 'https://apipagosbg.bgeneral.cloud';
}

function vpty_site_domain() {
	$parts = wp_parse_url( home_url() );
	return $parts['scheme'] . '://' . $parts['host'];
}

function vpty_yappy_post( $path, $body, $token = '' ) {
	$headers = array( 'Content-Type' => 'application/json' );
	if ( $token ) {
		$headers['Authorization'] = $token;
	}
	$response = wp_remote_post(
		vpty_yappy_api_base() . $path,
		array(
			'timeout' => 15,
			'headers' => $headers,
			'body'    => wp_json_encode( $body ),
		)
	);
	if ( is_wp_error( $response ) ) {
		return $response;
	}
	$data = json_decode( wp_remote_retrieve_body( $response ), true );
	if ( ! is_array( $data ) || empty( $data['body'] ) ) {
		$msg = isset( $data['status']['description'] ) ? $data['status']['description'] : wp_remote_retrieve_response_code( $response );
		return new WP_Error( 'vpty_yappy', 'Yappy: ' . $msg );
	}
	return $data['body'];
}

add_action(
	'rest_api_init',
	function () {
		register_rest_route(
			'vpty/v1',
			'/yappy/order',
			array(
				'methods'             => 'POST',
				'permission_callback' => '__return_true',
				'callback'            => 'vpty_rest_yappy_order',
			)
		);
		register_rest_route(
			'vpty/v1',
			'/yappy/ipn',
			array(
				'methods'             => 'GET',
				'permission_callback' => '__return_true',
				'callback'            => 'vpty_rest_yappy_ipn',
			)
		);
	}
);

/** Creates the order in Yappy and returns the data the <btn-yappy> component needs. */
function vpty_rest_yappy_order( WP_REST_Request $request ) {
	if ( ! vpty_yappy_enabled() ) {
		return new WP_REST_Response( array( 'ok' => false ), 404 );
	}
	$plans = vpty_cv_plans();
	$plan  = sanitize_key( (string) $request->get_param( 'plan' ) );
	$name  = sanitize_text_field( (string) $request->get_param( 'nombre' ) );
	$alias = preg_replace( '/\D+/', '', (string) $request->get_param( 'alias' ) );
	if ( strlen( $alias ) === 11 && 0 === strpos( $alias, '507' ) ) {
		$alias = substr( $alias, 3 );
	}
	if ( ! isset( $plans[ $plan ] ) || '' === $name || ! preg_match( '/^[0-9]{8}$/', $alias ) ) {
		return new WP_REST_Response( array( 'ok' => false, 'message' => __( 'Revisa tu nombre y tu número de Yappy (8 dígitos).', 'vacantespty' ) ), 400 );
	}

	$rate_key = 'vpty_yrl_' . substr( vpty_client_ip_hash(), 0, 20 );
	$hits     = (int) get_transient( $rate_key );
	if ( $hits >= 10 ) {
		return new WP_REST_Response( array( 'ok' => false, 'message' => __( 'Demasiados intentos. Prueba en unos minutos.', 'vacantespty' ) ), 429 );
	}
	set_transient( $rate_key, $hits + 1, 10 * MINUTE_IN_SECONDS );

	$merchant = vpty_yappy_post(
		'/payments/validate/merchant',
		array(
			'merchantId' => vpty_opt( 'yappy_merchant_id' ),
			'urlDomain'  => vpty_site_domain(),
		)
	);
	if ( is_wp_error( $merchant ) || empty( $merchant['token'] ) ) {
		return new WP_REST_Response( array( 'ok' => false, 'message' => __( 'Yappy no está disponible en este momento. Pide tu plan por WhatsApp.', 'vacantespty' ) ), 502 );
	}

	$order_id = 'VP' . strtoupper( base_convert( (string) time(), 10, 36 ) . wp_generate_password( 4, false ) );
	$order_id = substr( preg_replace( '/[^A-Z0-9]/', '', strtoupper( $order_id ) ), 0, 15 );
	$total    = $plans[ $plan ]['price'];

	$payment = vpty_yappy_post(
		'/payments/payment-wc',
		array(
			'merchantId'  => vpty_opt( 'yappy_merchant_id' ),
			'orderId'     => $order_id,
			'domain'      => vpty_site_domain(),
			'paymentDate' => isset( $merchant['epochTime'] ) ? $merchant['epochTime'] : time(),
			'aliasYappy'  => $alias,
			'ipnUrl'      => rest_url( 'vpty/v1/yappy/ipn' ),
			'discount'    => '0.00',
			'taxes'       => '0.00',
			'subtotal'    => $total,
			'total'       => $total,
		),
		$merchant['token']
	);
	if ( is_wp_error( $payment ) || empty( $payment['transactionId'] ) ) {
		return new WP_REST_Response( array( 'ok' => false, 'message' => __( 'No pudimos crear el pago en Yappy. Revisa tu número o pide tu plan por WhatsApp.', 'vacantespty' ) ), 502 );
	}

	global $wpdb;
	$wpdb->insert( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
		vpty_orders_table(),
		array(
			'order_id'       => $order_id,
			'plan'           => $plan,
			'total'          => $total,
			'nombre'         => $name,
			'alias'          => '+507' . $alias,
			'estado'         => 'pendiente',
			'transaction_id' => sanitize_text_field( $payment['transactionId'] ),
			'created_at'     => current_time( 'mysql' ),
		)
	);

	return new WP_REST_Response(
		array(
			'ok'            => true,
			'orderId'       => $order_id,
			'transactionId' => $payment['transactionId'],
			'documentName'  => isset( $payment['documentName'] ) ? $payment['documentName'] : '',
			'token'         => isset( $payment['token'] ) ? $payment['token'] : '',
		),
		200
	);
}

/**
 * Yappy notifies the result here. The hash is an HMAC-SHA256 of orderId+status+domain
 * keyed with the first part of the decoded secret key.
 */
function vpty_rest_yappy_ipn( WP_REST_Request $request ) {
	$order_id = sanitize_text_field( (string) $request->get_param( 'orderId' ) );
	$status   = sanitize_text_field( (string) $request->get_param( 'status' ) );
	$domain   = (string) $request->get_param( 'domain' );
	$hash     = (string) ( $request->get_param( 'hash' ) ? $request->get_param( 'hash' ) : $request->get_param( 'Hash' ) );

	$secret = explode( '.', (string) base64_decode( vpty_opt( 'yappy_secret' ) ) ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions
	$valid  = $order_id && $hash && ! empty( $secret[0] ) && hash_equals( hash_hmac( 'sha256', $order_id . $status . $domain, $secret[0] ), $hash );
	if ( ! $valid ) {
		return new WP_REST_Response( array( 'success' => false ), 400 );
	}

	$states = array(
		'E' => 'pagado',
		'R' => 'rechazado',
		'C' => 'cancelado',
		'X' => 'expirado',
	);
	global $wpdb;
	$table = vpty_orders_table();
	$order = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$table} WHERE order_id = %s", $order_id ), ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
	if ( $order ) {
		$estado = isset( $states[ $status ] ) ? $states[ $status ] : $status;
		$wpdb->update( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
			$table,
			array(
				'estado'       => $estado,
				'confirmacion' => sanitize_text_field( (string) $request->get_param( 'confirmationNumber' ) ),
				'updated_at'   => current_time( 'mysql' ),
			),
			array( 'id' => $order['id'] )
		);
		$to = vpty_opt( 'correo_marca' ) ? vpty_opt( 'correo_marca' ) : get_option( 'admin_email' );
		if ( 'pagado' === $estado && 'pagado' !== $order['estado'] ) {
			/* translators: 1: plan, 2: customer name */
			wp_mail( $to, sprintf( __( '💰 Nuevo pago Yappy: Plan %1$s – %2$s', 'vacantespty' ), $order['plan'], $order['nombre'] ), sprintf( "Pedido: %s\nPlan: %s\nTotal: B/. %s\nCliente: %s\nYappy: %s", $order_id, $order['plan'], $order['total'], $order['nombre'], $order['alias'] ) );
		}
	}
	return new WP_REST_Response( array( 'success' => true ), 200 );
}

/** Lets the checkout confirm the payment state after the Yappy success event. */
add_action(
	'rest_api_init',
	function () {
		register_rest_route(
			'vpty/v1',
			'/yappy/status/(?P<order>[A-Z0-9]{4,15})',
			array(
				'methods'             => 'GET',
				'permission_callback' => '__return_true',
				'callback'            => function ( WP_REST_Request $request ) {
					global $wpdb;
					$table  = vpty_orders_table();
					$estado = $wpdb->get_var( $wpdb->prepare( "SELECT estado FROM {$table} WHERE order_id = %s", $request['order'] ) ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
					return new WP_REST_Response( array( 'estado' => $estado ? $estado : 'desconocido' ), 200 );
				},
			)
		);
	}
);

add_action(
	'admin_menu',
	function () {
		add_submenu_page(
			'edit.php?post_type=vacante',
			__( 'Pedidos de currículum', 'vacantespty' ),
			__( 'Pedidos CV (Yappy)', 'vacantespty' ),
			'manage_options',
			'vpty-pedidos',
			function () {
				global $wpdb;
				$rows = $wpdb->get_results( 'SELECT * FROM ' . vpty_orders_table() . ' ORDER BY id DESC LIMIT 200', ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				echo '<div class="wrap"><h1>' . esc_html__( 'Pedidos de currículum (Yappy)', 'vacantespty' ) . '</h1><table class="widefat striped"><thead><tr>';
				foreach ( array( 'Pedido', 'Plan', 'Total', 'Cliente', 'Yappy', 'Estado', 'Confirmación', 'Fecha' ) as $h ) {
					echo '<th>' . esc_html( $h ) . '</th>';
				}
				echo '</tr></thead><tbody>';
				foreach ( $rows as $r ) {
					echo '<tr><td>' . esc_html( $r['order_id'] ) . '</td><td>' . esc_html( $r['plan'] ) . '</td><td>B/. ' . esc_html( $r['total'] ) . '</td><td>' . esc_html( $r['nombre'] ) . '</td><td>' . esc_html( $r['alias'] ) . '</td><td><strong>' . esc_html( $r['estado'] ) . '</strong></td><td>' . esc_html( $r['confirmacion'] ) . '</td><td>' . esc_html( $r['created_at'] ) . '</td></tr>';
				}
				if ( ! $rows ) {
					echo '<tr><td colspan="8">' . esc_html__( 'Todavía no hay pedidos.', 'vacantespty' ) . '</td></tr>';
				}
				echo '</tbody></table></div>';
			}
		);
	}
);
