<?php
defined( 'ABSPATH' ) || exit;

/*
 * "Reto diario": the game runs in the visitor's browser. The server only keeps an
 * anonymous per-device streak so prize codes can be issued and validated here
 * instead of being generated (and forged) on the phone.
 */

function vpty_reto_table() {
	global $wpdb;
	return $wpdb->prefix . 'vpty_reto';
}

function vpty_reto_codes_table() {
	global $wpdb;
	return $wpdb->prefix . 'vpty_reto_codes';
}

function vpty_reto_prizes() {
	return array(
		'p7'  => array( 'days' => 7, 'prefix' => 'VPTY', 'text' => vpty_opt( 'reto_prize7' ) ),
		'p14' => array( 'days' => 14, 'prefix' => 'VPTY-CV', 'text' => vpty_opt( 'reto_prize14' ) ),
	);
}

/** Front-end settings merged into the game's CONFIG. */
function vpty_reto_client_config() {
	return array(
		'channel'    => vpty_channel_url( 'reto' ),
		'instagram'  => vpty_instagram_dm_url(),
		'prize7'     => vpty_opt( 'reto_prize7' ),
		'prize14'    => vpty_opt( 'reto_prize14' ),
		'house'      => array(
			'name'    => vpty_opt( 'reto_house_name' ),
			'text'    => vpty_opt( 'reto_house_text' ),
			'cta'     => __( 'Pedir mi CV', 'vacantespty' ),
			'url'     => home_url( '/curriculum-profesional/?utm_source=reto&utm_medium=web&utm_content=anuncio' ),
			'initial' => 'V',
		),
		'api'        => esc_url_raw( rest_url( 'vpty/v1/reto/' ) ),
	);
}

add_shortcode(
	'vpty_reto',
	function () {
		$file = VPTY_DIR . 'assets/reto/reto.html';
		if ( ! is_readable( $file ) ) {
			return '';
		}
		$config = '<script>window.VPTY_RETO=' . wp_json_encode( vpty_reto_client_config(), JSON_UNESCAPED_UNICODE | JSON_HEX_TAG ) . ';</script>';
		return $config . file_get_contents( $file ); // phpcs:ignore WordPress.WP.AlternativeFunctions -- bundled, trusted markup.
	}
);

function vpty_reto_device( $raw ) {
	$device = preg_replace( '/[^a-zA-Z0-9-]/', '', (string) $raw );
	return strlen( $device ) >= 16 && strlen( $device ) <= 40 ? $device : '';
}

add_action(
	'rest_api_init',
	function () {
		register_rest_route(
			'vpty/v1',
			'/reto/sello',
			array(
				'methods'             => 'POST',
				'permission_callback' => '__return_true',
				'callback'            => 'vpty_rest_reto_sello',
			)
		);
		register_rest_route(
			'vpty/v1',
			'/reto/codigo',
			array(
				'methods'             => 'POST',
				'permission_callback' => '__return_true',
				'callback'            => 'vpty_rest_reto_codigo',
			)
		);
	}
);

/**
 * Records that a device sealed a day. The phone may resend yesterday's seal if it
 * was offline. One missed day keeps the streak (mirrors the game's weekly
 * wildcard and token rescue); more than that restarts it.
 */
function vpty_rest_reto_sello( WP_REST_Request $request ) {
	global $wpdb;
	$device = vpty_reto_device( $request->get_param( 'device' ) );
	if ( ! $device ) {
		return new WP_REST_Response( array( 'ok' => false ), 400 );
	}

	$today     = current_time( 'Y-m-d' );
	$yesterday = gmdate( 'Y-m-d', strtotime( $today . ' -1 day' ) );
	$date      = (string) $request->get_param( 'fecha' );
	// Phones a few hours off the server's timezone may report "tomorrow": treat it as today.
	$date      = in_array( $date, array( $today, $yesterday ), true ) ? $date : $today;
	$table     = vpty_reto_table();
	$row       = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$table} WHERE device = %s", $device ), ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared

	if ( ! $row ) {
		// Limit new devices per IP per day so streaks can't be farmed in bulk.
		$rate_key = 'vpty_reto_new_' . substr( vpty_client_ip_hash(), 0, 20 );
		$created  = (int) get_transient( $rate_key );
		if ( $created >= 15 ) {
			return new WP_REST_Response( array( 'ok' => false ), 429 );
		}
		set_transient( $rate_key, $created + 1, DAY_IN_SECONDS );
		$wpdb->insert( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
			$table,
			array(
				'device'     => $device,
				'streak'     => 1,
				'best'       => 1,
				'last_date'  => $date,
				'days'       => 1,
				'created_at' => current_time( 'mysql' ),
				'updated_at' => current_time( 'mysql' ),
			)
		);
		return new WP_REST_Response( array( 'ok' => true, 'racha' => 1 ), 200 );
	}

	if ( $row['last_date'] >= $date ) {
		return new WP_REST_Response( array( 'ok' => true, 'racha' => (int) $row['streak'] ), 200 );
	}
	$gap    = (int) round( ( strtotime( $date ) - strtotime( $row['last_date'] ) ) / DAY_IN_SECONDS );
	$streak = $gap <= 2 ? (int) $row['streak'] + 1 : 1;
	$wpdb->update( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$table,
		array(
			'streak'     => $streak,
			'best'       => max( $streak, (int) $row['best'] ),
			'last_date'  => $date,
			'days'       => (int) $row['days'] + 1,
			'updated_at' => current_time( 'mysql' ),
		),
		array( 'device' => $device )
	);
	return new WP_REST_Response( array( 'ok' => true, 'racha' => $streak ), 200 );
}

function vpty_reto_generate_code( $prefix ) {
	$chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
	$code  = '';
	for ( $i = 0; $i < 6; $i++ ) {
		$code .= $chars[ wp_rand( 0, strlen( $chars ) - 1 ) ];
	}
	return $prefix . '-' . current_time( 'dm' ) . '-' . $code;
}

/** Issues (once per device and prize) a code the brand can verify in the admin. */
function vpty_rest_reto_codigo( WP_REST_Request $request ) {
	global $wpdb;
	$device = vpty_reto_device( $request->get_param( 'device' ) );
	$prizes = vpty_reto_prizes();
	$key    = sanitize_key( (string) $request->get_param( 'premio' ) );
	if ( ! $device || ! isset( $prizes[ $key ] ) ) {
		return new WP_REST_Response( array( 'ok' => false ), 400 );
	}

	$codes    = vpty_reto_codes_table();
	$existing = $wpdb->get_var( $wpdb->prepare( "SELECT code FROM {$codes} WHERE device = %s AND premio = %s", $device, $key ) ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
	if ( $existing ) {
		return new WP_REST_Response( array( 'ok' => true, 'codigo' => $existing ), 200 );
	}

	$best = (int) $wpdb->get_var( $wpdb->prepare( 'SELECT best FROM ' . vpty_reto_table() . ' WHERE device = %s', $device ) ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
	if ( $best < $prizes[ $key ]['days'] ) {
		return new WP_REST_Response( array( 'ok' => false, 'motivo' => 'racha' ), 403 );
	}

	do {
		$code = vpty_reto_generate_code( $prizes[ $key ]['prefix'] );
	} while ( $wpdb->get_var( $wpdb->prepare( "SELECT id FROM {$codes} WHERE code = %s", $code ) ) ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared

	$wpdb->insert( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$codes,
		array(
			'code'       => $code,
			'device'     => $device,
			'premio'     => $key,
			'estado'     => 'emitido',
			'created_at' => current_time( 'mysql' ),
		)
	);
	return new WP_REST_Response( array( 'ok' => true, 'codigo' => $code ), 200 );
}

/* ---------- Admin: verify and redeem codes ---------- */

add_action(
	'admin_menu',
	function () {
		add_submenu_page( 'edit.php?post_type=vacante', __( 'Códigos del Reto', 'vacantespty' ), __( 'Códigos del Reto', 'vacantespty' ), 'manage_options', 'vpty-reto', 'vpty_render_reto_page' );
	}
);

add_action(
	'admin_post_vpty_reto_canjear',
	function () {
		if ( ! current_user_can( 'manage_options' ) || ! check_admin_referer( 'vpty_reto_canjear' ) ) {
			wp_die( esc_html__( 'Sin permiso.', 'vacantespty' ) );
		}
		global $wpdb;
		$code = isset( $_POST['code'] ) ? strtoupper( sanitize_text_field( wp_unslash( $_POST['code'] ) ) ) : '';
		$wpdb->update( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
			vpty_reto_codes_table(),
			array(
				'estado'      => 'canjeado',
				'redeemed_at' => current_time( 'mysql' ),
			),
			array(
				'code'   => $code,
				'estado' => 'emitido',
			)
		);
		wp_safe_redirect( admin_url( 'edit.php?post_type=vacante&page=vpty-reto&buscar=' . rawurlencode( $code ) . '&canjeado=1' ) );
		exit;
	}
);

function vpty_render_reto_page() {
	global $wpdb;
	$codes  = vpty_reto_codes_table();
	$reto   = vpty_reto_table();
	$search = isset( $_GET['buscar'] ) ? strtoupper( sanitize_text_field( wp_unslash( $_GET['buscar'] ) ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$today  = current_time( 'Y-m-d' );
	$prizes = vpty_reto_prizes();

	$stats = array(
		__( 'Jugadores de hoy', 'vacantespty' )      => (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$reto} WHERE last_date = %s", $today ) ), // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		__( 'Jugadores totales', 'vacantespty' )     => (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$reto}" ), // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		__( 'Rachas de 7+ días activas', 'vacantespty' ) => (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$reto} WHERE streak >= 7 AND last_date >= %s", gmdate( 'Y-m-d', strtotime( $today . ' -2 day' ) ) ) ), // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		__( 'Códigos emitidos', 'vacantespty' )      => (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$codes}" ), // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		__( 'Códigos canjeados', 'vacantespty' )     => (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$codes} WHERE estado = 'canjeado'" ), // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
	);
	$found = $search ? $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$codes} WHERE code = %s", $search ), ARRAY_A ) : null; // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
	$rows  = $wpdb->get_results( "SELECT * FROM {$codes} ORDER BY id DESC LIMIT 50", ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Códigos del Reto diario', 'vacantespty' ); ?></h1>
		<p>
			<?php foreach ( $stats as $label => $value ) : ?>
				<span style="display:inline-block;margin:0 8px 8px 0;padding:8px 14px;background:#fff;border:1px solid #ccd0d4;border-radius:4px"><?php echo esc_html( $label ); ?>: <strong><?php echo (int) $value; ?></strong></span>
			<?php endforeach; ?>
		</p>

		<h2><?php esc_html_e( 'Verificar un código', 'vacantespty' ); ?></h2>
		<p><?php esc_html_e( 'Cuando alguien te envíe su código por DM, pégalo aquí antes de entregar el premio.', 'vacantespty' ); ?></p>
		<form method="get">
			<input type="hidden" name="post_type" value="vacante">
			<input type="hidden" name="page" value="vpty-reto">
			<input type="text" name="buscar" value="<?php echo esc_attr( $search ); ?>" placeholder="VPTY-2809-ABC123" class="regular-text" style="text-transform:uppercase">
			<?php submit_button( __( 'Verificar', 'vacantespty' ), 'primary', '', false ); ?>
		</form>

		<?php if ( $search ) : ?>
			<?php if ( ! $found ) : ?>
				<div class="notice notice-error inline"><p><strong>❌ <?php esc_html_e( 'Código no válido.', 'vacantespty' ); ?></strong> <?php esc_html_e( 'No fue emitido por el sitio: no entregues el premio.', 'vacantespty' ); ?></p></div>
			<?php elseif ( 'canjeado' === $found['estado'] ) : ?>
				<div class="notice notice-warning inline"><p><strong>⚠️ <?php esc_html_e( 'Código ya canjeado', 'vacantespty' ); ?></strong> (<?php echo esc_html( $found['redeemed_at'] ); ?>). <?php esc_html_e( 'No lo entregues otra vez.', 'vacantespty' ); ?></p></div>
			<?php else : ?>
				<div class="notice notice-success inline">
					<p><strong>✅ <?php esc_html_e( 'Código válido', 'vacantespty' ); ?></strong> — <?php echo esc_html( isset( $prizes[ $found['premio'] ] ) ? $prizes[ $found['premio'] ]['text'] : $found['premio'] ); ?> (<?php echo esc_html( $found['created_at'] ); ?>)</p>
					<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
						<?php wp_nonce_field( 'vpty_reto_canjear' ); ?>
						<input type="hidden" name="action" value="vpty_reto_canjear">
						<input type="hidden" name="code" value="<?php echo esc_attr( $found['code'] ); ?>">
						<p><?php submit_button( __( 'Marcar como canjeado', 'vacantespty' ), 'secondary', '', false ); ?></p>
					</form>
				</div>
			<?php endif; ?>
		<?php endif; ?>

		<h2><?php esc_html_e( 'Últimos códigos', 'vacantespty' ); ?></h2>
		<table class="widefat striped">
			<thead><tr><th><?php esc_html_e( 'Código', 'vacantespty' ); ?></th><th><?php esc_html_e( 'Premio', 'vacantespty' ); ?></th><th><?php esc_html_e( 'Estado', 'vacantespty' ); ?></th><th><?php esc_html_e( 'Emitido', 'vacantespty' ); ?></th><th><?php esc_html_e( 'Canjeado', 'vacantespty' ); ?></th></tr></thead>
			<tbody>
				<?php foreach ( $rows as $r ) : ?>
					<tr>
						<td><code><?php echo esc_html( $r['code'] ); ?></code></td>
						<td><?php echo esc_html( isset( $prizes[ $r['premio'] ] ) ? $prizes[ $r['premio'] ]['text'] : $r['premio'] ); ?></td>
						<td><?php echo esc_html( $r['estado'] ); ?></td>
						<td><?php echo esc_html( $r['created_at'] ); ?></td>
						<td><?php echo esc_html( (string) $r['redeemed_at'] ); ?></td>
					</tr>
				<?php endforeach; ?>
				<?php if ( ! $rows ) : ?>
					<tr><td colspan="5"><?php esc_html_e( 'Todavía no hay códigos emitidos.', 'vacantespty' ); ?></td></tr>
				<?php endif; ?>
			</tbody>
		</table>
	</div>
	<?php
}
