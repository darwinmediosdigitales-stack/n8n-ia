<?php
defined( 'ABSPATH' ) || exit;

/*
 * Replacing the plugin zip does not run the activation hook, so new tables and
 * pages are created here whenever the stored schema version is older.
 */

define( 'VPTY_SCHEMA_VERSION', '3' );

add_action( 'admin_init', 'vpty_maybe_upgrade' );

function vpty_maybe_upgrade() {
	if ( get_option( 'vpty_schema_version' ) === VPTY_SCHEMA_VERSION ) {
		return;
	}
	vpty_create_tables();
	vpty_create_growth_pages();
	vpty_seed_resources();
	update_option( 'vpty_schema_version', VPTY_SCHEMA_VERSION );
	flush_rewrite_rules();
}

function vpty_create_tables() {
	global $wpdb;
	require_once ABSPATH . 'wp-admin/includes/upgrade.php';
	$charset = $wpdb->get_charset_collate();

	dbDelta(
		'CREATE TABLE ' . vpty_leads_table() . " (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			nombre varchar(80) NOT NULL DEFAULT '',
			email varchar(120) NOT NULL DEFAULT '',
			whatsapp varchar(20) NOT NULL DEFAULT '',
			categorias varchar(255) NOT NULL DEFAULT '',
			provincia varchar(40) NOT NULL DEFAULT '',
			punto varchar(30) NOT NULL DEFAULT '',
			variante varchar(2) NOT NULL DEFAULT '',
			estado varchar(10) NOT NULL DEFAULT 'activo',
			consentimiento text NOT NULL,
			consent_at datetime DEFAULT NULL,
			ip_hash varchar(64) NOT NULL DEFAULT '',
			token varchar(40) NOT NULL DEFAULT '',
			brevo tinyint(1) NOT NULL DEFAULT 0,
			created_at datetime DEFAULT NULL,
			PRIMARY KEY  (id),
			UNIQUE KEY email (email),
			KEY estado (estado)
		) $charset;"
	);

	dbDelta(
		'CREATE TABLE ' . vpty_orders_table() . " (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			order_id varchar(15) NOT NULL DEFAULT '',
			plan varchar(20) NOT NULL DEFAULT '',
			total varchar(10) NOT NULL DEFAULT '',
			nombre varchar(80) NOT NULL DEFAULT '',
			alias varchar(20) NOT NULL DEFAULT '',
			estado varchar(20) NOT NULL DEFAULT 'pendiente',
			transaction_id varchar(80) NOT NULL DEFAULT '',
			confirmacion varchar(80) NOT NULL DEFAULT '',
			created_at datetime DEFAULT NULL,
			updated_at datetime DEFAULT NULL,
			PRIMARY KEY  (id),
			UNIQUE KEY order_id (order_id)
		) $charset;"
	);

	dbDelta(
		'CREATE TABLE ' . vpty_reto_table() . " (
			device varchar(40) NOT NULL,
			streak int(10) unsigned NOT NULL DEFAULT 0,
			best int(10) unsigned NOT NULL DEFAULT 0,
			days int(10) unsigned NOT NULL DEFAULT 0,
			last_date date DEFAULT NULL,
			created_at datetime DEFAULT NULL,
			updated_at datetime DEFAULT NULL,
			PRIMARY KEY  (device),
			KEY last_date (last_date)
		) $charset;"
	);

	dbDelta(
		'CREATE TABLE ' . vpty_reto_codes_table() . " (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			code varchar(30) NOT NULL DEFAULT '',
			device varchar(40) NOT NULL DEFAULT '',
			premio varchar(10) NOT NULL DEFAULT '',
			estado varchar(10) NOT NULL DEFAULT 'emitido',
			created_at datetime DEFAULT NULL,
			redeemed_at datetime DEFAULT NULL,
			PRIMARY KEY  (id),
			UNIQUE KEY code (code),
			UNIQUE KEY device_premio (device,premio)
		) $charset;"
	);
}

function vpty_block_p( $text ) {
	return "<!-- wp:paragraph -->\n<p>" . $text . "</p>\n<!-- /wp:paragraph -->\n\n";
}

function vpty_block_h2( $text ) {
	return "<!-- wp:heading -->\n<h2 class=\"wp-block-heading\">" . $text . "</h2>\n<!-- /wp:heading -->\n\n";
}

function vpty_block_shortcode( $code ) {
	return "<!-- wp:shortcode -->\n" . $code . "\n<!-- /wp:shortcode -->\n\n";
}

function vpty_create_growth_pages() {
	vpty_create_page(
		'alertas-de-vacantes',
		__( 'Alertas de vacantes', 'vacantespty' ),
		vpty_block_p( 'Elige las áreas que te interesan y te avisamos por correo y WhatsApp cuando salgan vacantes nuevas. Es gratis y te das de baja cuando quieras.' )
		. vpty_block_shortcode( '[vpty_alertas]' )
	);

	vpty_create_page( 'gracias', __( '¡Listo! Ya estás en las alertas', 'vacantespty' ), vpty_block_shortcode( '[vpty_gracias]' ) );

	vpty_create_page(
		'curriculum-profesional',
		__( 'Te creamos tu currículum profesional con los mejores sistemas', 'vacantespty' ),
		vpty_block_p( 'Muchas empresas usan filtros automáticos (ATS) que descartan currículums antes de que un reclutador los lea. Nosotros te lo dejamos listo para pasar esos filtros y llamar la atención de quien contrata.' )
		. vpty_block_shortcode( '[vpty_planes_cv]' )
		. vpty_block_h2( '¿Cómo funciona?' )
		. "<!-- wp:list {\"ordered\":true} -->\n<ol class=\"wp-block-list\"><li>Elige tu plan y paga con Yappy.</li><li>Envía tu confirmación de pago y tu currículum actual a nuestro WhatsApp.</li><li>Recibes tu nuevo currículum listo para aplicar.</li></ol>\n<!-- /wp:list -->\n\n"
		. vpty_block_p( '¿Tienes dudas antes de comprar? Escríbenos un mensaje directo por Instagram: <a href="https://ig.me/m/vacantes_pty_" target="_blank" rel="noopener">@vacantes_pty_</a>.' )
	);

	vpty_create_page(
		'capacitate',
		__( 'Capacítate y destácate', 'vacantespty' ),
		vpty_block_p( '<strong>Hay cientos de personas aplicando a la misma vacante que tú.</strong> Lo que te hace destacar es lo que sabes hacer y puedes demostrar. Aquí tienes los cursos y certificaciones que más piden las empresas en Panamá, muchos gratis.' )
		. vpty_block_shortcode( '[vpty_capacitate]' )
	);

	$reto = vpty_create_page( 'reto', __( 'Reto diario: consigue empleo jugando', 'vacantespty' ), vpty_block_shortcode( '[vpty_reto]' ) );
	if ( $reto && ! has_excerpt( $reto ) ) {
		wp_update_post(
			array(
				'ID'           => $reto,
				'post_excerpt' => 'Reto diario de Vacantes PTY: trivia de empleo, simulador de entrevista y premios por racha. Prepárate para conseguir trabajo en Panamá en 5 minutos al día.',
			)
		);
	}

	vpty_create_page( 'politica-de-privacidad', __( 'Política de Privacidad', 'vacantespty' ), vpty_privacy_policy_content() );
	vpty_create_page( 'divulgacion-de-afiliados', __( 'Divulgación de afiliados', 'vacantespty' ), vpty_affiliate_disclosure_content() );

	$privacy = get_page_by_path( 'politica-de-privacidad' );
	if ( $privacy ) {
		update_option( 'wp_page_for_privacy_policy', $privacy->ID );
	}
}

add_shortcode(
	'vpty_correo',
	function () {
		$email = vpty_opt( 'correo_marca' ) ? vpty_opt( 'correo_marca' ) : get_option( 'admin_email' );
		return '<a href="mailto:' . esc_attr( antispambot( $email ) ) . '">' . esc_html( antispambot( $email ) ) . '</a>';
	}
);

add_shortcode(
	'vpty_gracias',
	function () {
		ob_start();
		?>
		<div class="vpty-thanks">
			<p class="vpty-thanks__lead">🎉 <?php esc_html_e( 'Te enviamos un correo de bienvenida. Revisa también la carpeta de spam o promociones.', 'vacantespty' ); ?></p>
			<div class="vpty-thanks__card">
				<h2><?php esc_html_e( 'Entérate primero: sigue nuestro canal de WhatsApp', 'vacantespty' ); ?></h2>
				<p><?php esc_html_e( 'Publicamos vacantes nuevas todos los días. Activa la campanita 🔔 para que no se te pase ninguna.', 'vacantespty' ); ?></p>
				<?php echo vpty_channel_link( 'gracias', __( 'Seguir el canal de WhatsApp', 'vacantespty' ), 'vpty-btn vpty-btn--whatsapp vpty-btn--lg' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
			</div>
			<h3><?php esc_html_e( 'Síguenos en redes', 'vacantespty' ); ?></h3>
			<?php echo vpty_social_icons(); // phpcs:ignore WordPress.Security.EscapeOutput ?>
			<?php echo vpty_cv_promo_card( 'gracias' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
			<p><a class="vpty-link" href="<?php echo esc_url( home_url( '/capacitate/' ) ); ?>"><?php esc_html_e( 'Capacítate y destácate: cursos gratis y con certificado →', 'vacantespty' ); ?></a></p>
		</div>
		<?php
		return ob_get_clean();
	}
);

function vpty_privacy_policy_content() {
	$updated = date_i18n( 'j \d\e F \d\e Y' );
	return vpty_block_p( '<em>Última actualización: ' . $updated . '</em>' )
		. vpty_block_p( 'En <strong>Vacantes PTY</strong> (empleoshoypanama.com) respetamos tu privacidad. Esta política explica qué datos personales recopilamos, para qué los usamos y cuáles son tus derechos, conforme a la Ley 81 de 2019 sobre Protección de Datos Personales de la República de Panamá y su reglamentación.' )
		. vpty_block_h2( '1. Responsable del tratamiento' )
		. vpty_block_p( 'El responsable de tus datos es Vacantes PTY. Para cualquier consulta sobre tus datos puedes escribirnos a [vpty_correo].' )
		. vpty_block_h2( '2. Qué datos recopilamos' )
		. vpty_block_p( 'Solo cuando te registras voluntariamente en nuestras alertas de vacantes: nombre, correo electrónico, número de WhatsApp, áreas de interés laboral y, de forma opcional, provincia. También registramos la fecha de tu consentimiento y el formulario desde el que te registraste. Si compras un plan de currículum, guardamos tu nombre, tu número de Yappy y el estado del pago.' )
		. vpty_block_p( 'Para ver vacantes o aplicar a ellas <strong>no necesitas darnos ningún dato</strong>: la postulación se hace directamente con la empresa, en su propio canal.' )
		. vpty_block_h2( '3. Para qué usamos tus datos' )
		. "<!-- wp:list -->\n<ul class=\"wp-block-list\"><li>Enviarte vacantes y alertas de empleo según tus áreas de interés, por correo y WhatsApp.</li><li>Invitarte a nuestro canal de WhatsApp y a nuestras redes sociales.</li><li>Ofrecerte servicios de currículum, cursos y recursos de formación, y contenido patrocinado relacionado con el empleo.</li><li>Gestionar los planes de currículum que compres.</li><li>Elaborar estadísticas internas y anónimas para mejorar el sitio.</li></ul>\n<!-- /wp:list -->\n\n"
		. vpty_block_h2( '4. Base legal' )
		. vpty_block_p( 'Tratamos tus datos con base en tu <strong>consentimiento</strong> expreso, que das al marcar la casilla del formulario (nunca viene marcada por defecto). Puedes retirarlo en cualquier momento.' )
		. vpty_block_h2( '5. Con quién compartimos tus datos' )
		. vpty_block_p( 'No vendemos tus datos. Los compartimos solo con proveedores que nos ayudan a prestar el servicio y que actúan por nuestra cuenta: nuestro proveedor de alojamiento web (Hostinger), nuestra herramienta de envío de correos (Brevo), Google Analytics para estadísticas de uso y Yappy (Banco General) para procesar pagos. Algunos de estos proveedores pueden almacenar datos fuera de Panamá, con medidas de seguridad adecuadas.' )
		. vpty_block_h2( '6. Cuánto tiempo guardamos tus datos' )
		. vpty_block_p( 'Mientras sigas suscrito a nuestras alertas. Si te das de baja, dejamos de enviarte comunicaciones de inmediato y conservamos solo el registro mínimo necesario para demostrar tu consentimiento y tu baja.' )
		. vpty_block_h2( '7. Tus derechos' )
		. vpty_block_p( 'Puedes ejercer en cualquier momento tus derechos de <strong>acceso, rectificación, cancelación, oposición y portabilidad</strong> escribiéndonos a [vpty_correo]. Responderemos en los plazos que establece la ley. Si consideras que no atendimos tu solicitud, puedes acudir a la Autoridad Nacional de Transparencia y Acceso a la Información (ANTAI).' )
		. vpty_block_h2( '8. Cómo darte de baja' )
		. vpty_block_p( 'Todos nuestros correos incluyen un enlace de <strong>"Darme de baja"</strong>. También puedes pedirla escribiendo a [vpty_correo] o respondiendo "BAJA" a cualquiera de nuestros mensajes.' )
		. vpty_block_h2( '9. Cookies y medición' )
		. vpty_block_p( 'Usamos cookies técnicas para que el sitio funcione y Google Analytics para medir de forma agregada qué páginas y botones se usan más. Tu navegador te permite bloquear o borrar las cookies cuando quieras.' )
		. vpty_block_h2( '10. Seguridad' )
		. vpty_block_p( 'Aplicamos medidas técnicas y organizativas razonables para proteger tus datos: conexión cifrada (HTTPS), acceso restringido al panel de administración y protección contra registros automatizados.' )
		. vpty_block_h2( '11. Cambios en esta política' )
		. vpty_block_p( 'Si cambiamos esta política, publicaremos la nueva versión en esta página con su fecha de actualización.' );
}

function vpty_affiliate_disclosure_content() {
	return vpty_block_p( 'Vacantes PTY es un sitio gratuito para quienes buscan empleo en Panamá. Para mantenerlo, algunos enlaces que recomendamos, sobre todo en la sección <a href="/capacitate/">Capacítate y destácate</a> y en el blog, son <strong>enlaces de afiliado</strong>.' )
		. vpty_block_p( 'Esto significa que si haces clic y compras un curso o producto, podemos recibir una pequeña comisión, <strong>sin ningún costo adicional para ti</strong>. Participamos, entre otros, en los programas de afiliados de Hotmart, Coursera y Udemy.' )
		. vpty_block_p( 'Solo recomendamos recursos que creemos que de verdad ayudan a conseguir empleo. Las comisiones nunca influyen en las vacantes que publicamos: las vacantes son siempre gratuitas y la postulación se hace directamente con cada empresa.' )
		. vpty_block_p( 'Los enlaces de afiliado están marcados técnicamente como patrocinados. Si tienes preguntas, escríbenos a [vpty_correo].' );
}
