<?php
defined( 'ABSPATH' ) || exit;

/**
 * Private integration settings (Vacantes → Ajustes). Kept out of the Customizer
 * because they hold API keys that must never reach the page source.
 */
function vpty_options_schema() {
	return array(
		'general' => array(
			'title'  => __( 'General', 'vacantespty' ),
			'fields' => array(
				'correo_marca'   => array( __( 'Correo de la marca (contacto y remitente de todos los correos)', 'vacantespty' ), 'email', 'vacantes@empleoshoypanama.com' ),
				'canal_whatsapp' => array( __( 'Canal de WhatsApp de Vacantes PTY', 'vacantespty' ), 'url', 'https://whatsapp.com/channel/0029VbCum9c2ER6cMRrrhX3h' ),
				'instagram_user' => array( __( 'Usuario de Instagram (para mensajes directos)', 'vacantespty' ), 'text', 'vacantes_pty_' ),
				'ga4_id'         => array( __( 'ID de Google Analytics 4 (G-XXXXXXX). Déjalo vacío si ya usas Site Kit.', 'vacantespty' ), 'text', '' ),
			),
		),
		'brevo'   => array(
			'title'  => __( 'Brevo (correo marketing)', 'vacantespty' ),
			'fields' => array(
				'brevo_api_key' => array( __( 'Clave API de Brevo', 'vacantespty' ), 'secret', '' ),
				'brevo_list_id' => array( __( 'ID de la lista de Brevo', 'vacantespty' ), 'number', '' ),
			),
		),
		'cv'      => array(
			'title'  => __( 'Currículum profesional y Yappy', 'vacantespty' ),
			'fields' => array(
				'cv_whatsapp'       => array( __( 'WhatsApp para enviar la confirmación de pago', 'vacantespty' ), 'text', '62260829' ),
				'yappy_merchant_id' => array( __( 'Yappy: ID del comercio', 'vacantespty' ), 'text', '' ),
				'yappy_secret'      => array( __( 'Yappy: clave secreta', 'vacantespty' ), 'secret', '' ),
				'yappy_sandbox'     => array( __( 'Yappy: modo de pruebas', 'vacantespty' ), 'checkbox', '' ),
			),
		),
		'planes'  => array(
			'title'  => __( 'Planes de currículum (nombre y precio)', 'vacantespty' ),
			'fields' => array(
				'plan_basico_nombre'      => array( __( 'Plan 1: nombre', 'vacantespty' ), 'text', 'Básico' ),
				'plan_basico_precio'      => array( __( 'Plan 1: precio (B/.)', 'vacantespty' ), 'price', '4.99' ),
				'plan_profesional_nombre' => array( __( 'Plan 2 (el destacado): nombre', 'vacantespty' ), 'text', 'Profesional' ),
				'plan_profesional_precio' => array( __( 'Plan 2: precio (B/.)', 'vacantespty' ), 'price', '6.99' ),
				'plan_premium_nombre'     => array( __( 'Plan 3: nombre', 'vacantespty' ), 'text', 'Marca Personal' ),
				'plan_premium_precio'     => array( __( 'Plan 3: precio (B/.)', 'vacantespty' ), 'price', '14.99' ),
			),
		),
		'reto'    => array(
			'title'  => __( 'Reto diario', 'vacantespty' ),
			'fields' => array(
				'reto_prize7'     => array( __( 'Premio por 7 días seguidos', 'vacantespty' ), 'text', '50% de descuento en el CV Profesional Premium' ),
				'reto_prize14'    => array( __( 'Premio por 14 días seguidos', 'vacantespty' ), 'text', 'un CV Básico gratis' ),
				'reto_house_name' => array( __( 'Anuncio propio: título', 'vacantespty' ), 'text', 'CV Impacto · Vacantes PTY' ),
				'reto_house_text' => array( __( 'Anuncio propio: texto', 'vacantespty' ), 'text', 'Tu CV Impacto hecho por profesionales por solo B/. 5.99. Pago fácil con Yappy.' ),
			),
		),
	);
}

function vpty_opt( $key ) {
	$options = get_option( 'vpty_options', array() );
	if ( isset( $options[ $key ] ) && '' !== $options[ $key ] ) {
		return $options[ $key ];
	}
	foreach ( vpty_options_schema() as $section ) {
		if ( isset( $section['fields'][ $key ] ) ) {
			return $section['fields'][ $key ][2];
		}
	}
	return '';
}

/* Every email the site sends (welcome, payments, WordPress notices) comes from the brand address. */
add_filter(
	'wp_mail_from',
	function ( $from ) {
		$brand = vpty_opt( 'correo_marca' );
		return is_email( $brand ) ? $brand : $from;
	}
);
add_filter( 'wp_mail_from_name', fn() => 'Vacantes PTY' );

add_action(
	'admin_menu',
	function () {
		add_submenu_page( 'edit.php?post_type=vacante', __( 'Ajustes de Vacantes PTY', 'vacantespty' ), __( 'Ajustes', 'vacantespty' ), 'manage_options', 'vpty-ajustes', 'vpty_render_settings_page' );
	}
);

add_action(
	'admin_init',
	function () {
		register_setting(
			'vpty_options',
			'vpty_options',
			array(
				'type'              => 'array',
				'sanitize_callback' => 'vpty_sanitize_options',
			)
		);
	}
);

function vpty_sanitize_options( $input ) {
	$current = get_option( 'vpty_options', array() );
	$clean   = array();
	foreach ( vpty_options_schema() as $section ) {
		foreach ( $section['fields'] as $key => $field ) {
			$raw = isset( $input[ $key ] ) ? trim( (string) $input[ $key ] ) : '';
			switch ( $field[1] ) {
				case 'secret':
					// An empty secret field means "keep the saved value".
					$clean[ $key ] = '' === $raw ? ( isset( $current[ $key ] ) ? $current[ $key ] : '' ) : sanitize_text_field( $raw );
					break;
				case 'email':
					$clean[ $key ] = sanitize_email( $raw );
					break;
				case 'url':
					$clean[ $key ] = esc_url_raw( $raw );
					break;
				case 'number':
					$clean[ $key ] = '' === $raw ? '' : (string) absint( $raw );
					break;
				case 'checkbox':
					$clean[ $key ] = $raw ? '1' : '';
					break;
				case 'price':
					$clean[ $key ] = '' === $raw ? '' : number_format( max( 0, (float) str_replace( ',', '.', $raw ) ), 2, '.', '' );
					break;
				default:
					$clean[ $key ] = sanitize_text_field( ltrim( $raw, '@' ) );
			}
		}
	}
	return $clean;
}

function vpty_render_settings_page() {
	$options = get_option( 'vpty_options', array() );
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Ajustes de Vacantes PTY', 'vacantespty' ); ?></h1>
		<p><?php esc_html_e( 'Las claves se guardan en tu base de datos y nunca se muestran en la web.', 'vacantespty' ); ?></p>
		<form method="post" action="options.php">
			<?php settings_fields( 'vpty_options' ); ?>
			<?php foreach ( vpty_options_schema() as $section ) : ?>
				<h2><?php echo esc_html( $section['title'] ); ?></h2>
				<table class="form-table" role="presentation">
					<?php
					foreach ( $section['fields'] as $key => $field ) :
						$value = isset( $options[ $key ] ) ? $options[ $key ] : '';
						$name  = 'vpty_options[' . $key . ']';
						?>
						<tr>
							<th scope="row"><label for="vpty_opt_<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $field[0] ); ?></label></th>
							<td>
								<?php if ( 'checkbox' === $field[1] ) : ?>
									<input type="checkbox" id="vpty_opt_<?php echo esc_attr( $key ); ?>" name="<?php echo esc_attr( $name ); ?>" value="1" <?php checked( $value, '1' ); ?>>
								<?php elseif ( 'secret' === $field[1] ) : ?>
									<input type="password" class="regular-text" autocomplete="new-password" id="vpty_opt_<?php echo esc_attr( $key ); ?>" name="<?php echo esc_attr( $name ); ?>" value="" placeholder="<?php echo esc_attr( $value ? __( '•••••••• guardada (escribe para cambiarla)', 'vacantespty' ) : '' ); ?>">
								<?php else : ?>
									<input type="<?php echo 'number' === $field[1] ? 'number' : 'text'; ?>" class="regular-text" id="vpty_opt_<?php echo esc_attr( $key ); ?>" name="<?php echo esc_attr( $name ); ?>" value="<?php echo esc_attr( $value ); ?>" placeholder="<?php echo esc_attr( $field[2] ); ?>">
								<?php endif; ?>
							</td>
						</tr>
					<?php endforeach; ?>
				</table>
			<?php endforeach; ?>
			<?php submit_button(); ?>
		</form>
		<?php if ( vpty_opt( 'yappy_merchant_id' ) ) : ?>
			<p><strong><?php esc_html_e( 'URL de notificación (IPN) para Yappy:', 'vacantespty' ); ?></strong> <code><?php echo esc_html( rest_url( 'vpty/v1/yappy/ipn' ) ); ?></code></p>
		<?php endif; ?>
	</div>
	<?php
}
