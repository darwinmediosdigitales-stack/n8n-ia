<?php
defined( 'ABSPATH' ) || exit;

/**
 * WhatsApp channel link tagged with the capture point, so each source can be measured.
 */
function vpty_channel_url( $point, $medium = 'web' ) {
	return add_query_arg(
		array(
			'utm_source'  => 'empleoshoypanama',
			'utm_medium'  => $medium,
			'utm_content' => $point,
		),
		vpty_opt( 'canal_whatsapp' )
	);
}

/** Link to the channel with GA4 tracking attributes. */
function vpty_channel_link( $point, $label, $class = 'vpty-btn vpty-btn--whatsapp' ) {
	return sprintf(
		'<a class="%s" href="%s" target="_blank" rel="noopener" data-vpty-track="whatsapp_channel_click" data-vpty-point="%s">%s</a>',
		esc_attr( $class ),
		esc_url( vpty_channel_url( $point ) ),
		esc_attr( $point ),
		esc_html( $label )
	);
}

function vpty_instagram_dm_url( $text = '' ) {
	$url = 'https://ig.me/m/' . rawurlencode( vpty_opt( 'instagram_user' ) );
	return $text ? add_query_arg( 'text', rawurlencode( $text ), $url ) : $url;
}

function vpty_icon( $name ) {
	$paths = array(
		'instagram' => 'M12 2.2c3.2 0 3.6 0 4.8.1 3.3.1 4.8 1.7 4.9 4.9.1 1.3.1 1.6.1 4.8s0 3.6-.1 4.8c-.1 3.2-1.7 4.8-4.9 4.9-1.3.1-1.6.1-4.8.1s-3.6 0-4.8-.1c-3.3-.1-4.8-1.7-4.9-4.9C2.2 15.6 2.2 15.2 2.2 12s0-3.6.1-4.8C2.4 3.9 3.9 2.4 7.2 2.3 8.4 2.2 8.8 2.2 12 2.2zM12 7a5 5 0 1 0 0 10 5 5 0 0 0 0-10zm0 8.2a3.2 3.2 0 1 1 0-6.4 3.2 3.2 0 0 1 0 6.4zm5.2-9.6a1.2 1.2 0 1 0 0 2.4 1.2 1.2 0 0 0 0-2.4z',
		'youtube'   => 'M23.5 6.2a3 3 0 0 0-2.1-2.1C19.5 3.6 12 3.6 12 3.6s-7.5 0-9.4.5A3 3 0 0 0 .5 6.2 31 31 0 0 0 0 12a31 31 0 0 0 .5 5.8 3 3 0 0 0 2.1 2.1c1.9.5 9.4.5 9.4.5s7.5 0 9.4-.5a3 3 0 0 0 2.1-2.1A31 31 0 0 0 24 12a31 31 0 0 0-.5-5.8zM9.6 15.6V8.4l6.3 3.6-6.3 3.6z',
		'tiktok'    => 'M19.6 6.7a4.8 4.8 0 0 1-3.8-4.2V2h-3.4v13.4a2.9 2.9 0 1 1-2-2.7V9.2a6.3 6.3 0 1 0 5.4 6.2V8.6a8.2 8.2 0 0 0 4.8 1.5V6.7h-1z',
		'facebook'  => 'M24 12a12 12 0 1 0-13.9 11.9v-8.4H7.1V12h3V9.4c0-3 1.8-4.7 4.5-4.7 1.3 0 2.7.2 2.7.2v3h-1.5c-1.5 0-2 .9-2 1.9V12h3.4l-.5 3.5h-2.9v8.4A12 12 0 0 0 24 12z',
		'linkedin'  => 'M20.4 20.5h-3.6v-5.6c0-1.3 0-3-1.8-3s-2.1 1.4-2.1 2.9v5.7H9.4V9h3.4v1.6c.5-.9 1.6-1.8 3.4-1.8 3.6 0 4.3 2.4 4.3 5.5v6.2zM5.3 7.4a2.1 2.1 0 1 1 0-4.1 2.1 2.1 0 0 1 0 4.1zM7.1 20.5H3.6V9h3.5v11.5zM22.2 0H1.8C.8 0 0 .8 0 1.7v20.6c0 .9.8 1.7 1.8 1.7h20.4c1 0 1.8-.8 1.8-1.7V1.7C24 .8 23.2 0 22.2 0z',
		'whatsapp'  => 'M12 2a10 10 0 0 0-8.6 15.1L2 22l5-1.3A10 10 0 1 0 12 2zm5.3 14.2c-.2.6-1.3 1.2-1.8 1.3-.5 0-1 .2-3.3-.7a11.6 11.6 0 0 1-4.5-4c-.4-.5-1.1-1.6-1.1-3s.8-2.1 1-2.4c.3-.3.6-.4.8-.4h.6c.2 0 .4 0 .6.5l.9 2.1c0 .2.1.4 0 .6l-.4.6-.4.5c-.2.2-.3.3-.1.6.2.3.8 1.3 1.7 2.1 1.2 1 2.2 1.4 2.5 1.5.3.2.5.1.7-.1l1-1.2c.2-.3.4-.3.7-.2l2 1c.3.1.5.2.6.3 0 .2 0 .8-.2 1.4z',
		'x'         => 'M18.2 2.3h3.4l-7.4 8.4 8.7 11.5h-6.8l-5.3-7-6.1 7H1.3l7.9-9L.8 2.3h7l4.8 6.4 5.6-6.4zm-1.2 17.9h1.9L7.1 4.2H5.1l11.9 16z',
		'share'     => 'M18 16a3 3 0 0 0-2.4 1.2l-6.7-3.4a3 3 0 0 0 0-1.6l6.7-3.4A3 3 0 1 0 15 7l-6.7 3.4a3 3 0 1 0 0 3.2L15 17a3 3 0 1 0 3-1z',
		'link'      => 'M10.6 13.4a1 1 0 0 1 0-1.4l3.5-3.5a1 1 0 1 1 1.4 1.4L12 13.4a1 1 0 0 1-1.4 0zm-2.1 5.3a4 4 0 0 1-2.9-6.8l2.1-2.1a1 1 0 1 1 1.4 1.4L7 13.3a2 2 0 0 0 2.8 2.8l2.1-2.1a1 1 0 0 1 1.4 1.4l-2.1 2.1a4 4 0 0 1-2.7 1.2zm7-4.9a1 1 0 0 1-.7-1.7l2.1-2.1A2 2 0 0 0 14.1 7L12 9.1a1 1 0 0 1-1.4-1.4l2.1-2.1a4 4 0 0 1 5.7 5.7l-2.1 2.1a1 1 0 0 1-.8.4z',
	);
	return isset( $paths[ $name ] ) ? '<svg viewBox="0 0 24 24" width="20" height="20" aria-hidden="true" focusable="false"><path fill="currentColor" d="' . $paths[ $name ] . '"/></svg>' : '';
}

/** Brand social profiles with clickable icons (footer, thank-you page). */
function vpty_social_icons() {
	$networks = array(
		'instagram' => 'Instagram',
		'youtube'   => 'YouTube',
		'tiktok'    => 'TikTok',
		'facebook'  => 'Facebook',
		'linkedin'  => 'LinkedIn',
	);
	$html = '<div class="vpty-social">';
	foreach ( $networks as $key => $label ) {
		$url = vpty_setting( $key );
		if ( $url ) {
			$html .= sprintf(
				'<a href="%s" target="_blank" rel="noopener me" aria-label="%s" title="%s" data-vpty-track="social_click" data-vpty-point="%s">%s</a>',
				esc_url( $url ),
				esc_attr( $label ),
				esc_attr( $label ),
				esc_attr( $key ),
				vpty_icon( $key )
			);
		}
	}
	return $html . '</div>';
}

/**
 * Share bar for vacancies and blog posts. The native share button opens the
 * phone's share sheet, which is the only way to share a link to Instagram.
 */
function vpty_share_buttons( $post_id = null ) {
	$post_id = $post_id ? $post_id : get_the_ID();
	$url     = get_permalink( $post_id );
	$title   = get_the_title( $post_id );
	$type    = get_post_type( $post_id );
	$links   = array(
		'whatsapp' => array( 'WhatsApp', 'https://wa.me/?text=' . rawurlencode( $title . ' 👉 ' . $url ) ),
		'facebook' => array( 'Facebook', 'https://www.facebook.com/sharer/sharer.php?u=' . rawurlencode( $url ) ),
		'x'        => array( 'X', 'https://twitter.com/intent/tweet?text=' . rawurlencode( $title ) . '&url=' . rawurlencode( $url ) ),
		'linkedin' => array( 'LinkedIn', 'https://www.linkedin.com/sharing/share-offsite/?url=' . rawurlencode( $url ) ),
	);

	$html  = '<div class="vpty-share" data-vpty-share data-title="' . esc_attr( $title ) . '" data-url="' . esc_url( $url ) . '">';
	$html .= '<span class="vpty-share__label">' . esc_html( 'vacante' === $type ? __( 'Compártela con quien la necesite:', 'vacantespty' ) : __( 'Comparte este artículo:', 'vacantespty' ) ) . '</span>';
	$html .= '<div class="vpty-share__buttons">';
	$html .= '<button type="button" class="vpty-share__btn vpty-share__btn--native" data-share-native hidden>' . vpty_icon( 'share' ) . '<span>' . esc_html__( 'Instagram y más', 'vacantespty' ) . '</span></button>';
	foreach ( $links as $key => $link ) {
		$html .= sprintf(
			'<a class="vpty-share__btn vpty-share__btn--%1$s" href="%2$s" target="_blank" rel="noopener" data-vpty-track="share" data-vpty-point="%1$s" aria-label="%3$s">%4$s<span>%3$s</span></a>',
			esc_attr( $key ),
			esc_url( $link[1] ),
			esc_attr( $link[0] ),
			vpty_icon( $key )
		);
	}
	$html .= '<button type="button" class="vpty-share__btn" data-share-copy>' . vpty_icon( 'link' ) . '<span>' . esc_html__( 'Copiar enlace', 'vacantespty' ) . '</span></button>';
	return $html . '</div></div>';
}

/* ---------- Front-end assets, floating button, exit popup, GA4 ---------- */

add_action(
	'wp_enqueue_scripts',
	function () {
		wp_enqueue_script( 'vpty-growth', VPTY_URL . 'assets/growth.js', array(), VPTY_VERSION, true );
		wp_localize_script(
			'vpty-growth',
			'vptyGrowth',
			array(
				'endpoint' => esc_url_raw( rest_url( 'vpty/v1/lead' ) ),
				'thanks'   => esc_url_raw( home_url( '/gracias/' ) ),
				'copied'   => __( '¡Enlace copiado!', 'vacantespty' ),
				'error'    => __( 'No pudimos guardar tus datos. Revisa los campos e inténtalo de nuevo.', 'vacantespty' ),
			)
		);
	}
);

add_action(
	'wp_head',
	function () {
		$id = vpty_opt( 'ga4_id' );
		if ( ! $id || ! preg_match( '/^G-[A-Z0-9]+$/', $id ) ) {
			return;
		}
		?>
<script async src="https://www.googletagmanager.com/gtag/js?id=<?php echo esc_attr( $id ); ?>"></script>
<script>window.dataLayer=window.dataLayer||[];function gtag(){dataLayer.push(arguments);}gtag('js',new Date());gtag('config','<?php echo esc_js( $id ); ?>');</script>
		<?php
	},
	5
);

add_action(
	'wp_footer',
	function () {
		if ( ! vpty_opt( 'canal_whatsapp' ) ) {
			return;
		}
		echo '<a class="vpty-float" href="' . esc_url( vpty_channel_url( 'flotante' ) ) . '" target="_blank" rel="noopener" data-vpty-track="whatsapp_channel_click" data-vpty-point="flotante" aria-label="' . esc_attr__( 'Síguenos: vacantes diarias en WhatsApp', 'vacantespty' ) . '">' . vpty_icon( 'whatsapp' ) . '<span class="vpty-float__label">' . esc_html__( 'Síguenos: vacantes diarias en WhatsApp', 'vacantespty' ) . '</span></a>';

		// Exit-intent popup: never on the pages where the visitor already signs up or has signed up.
		if ( is_page( array( 'alertas-de-vacantes', 'gracias', 'politica-de-privacidad' ) ) ) {
			return;
		}
		$category = is_singular( 'vacante' ) ? vpty_lead_category_for_job( get_the_ID() ) : '';
		?>
		<div class="vpty-popup" data-vpty-popup hidden>
			<div class="vpty-popup__backdrop" data-popup-close></div>
			<div class="vpty-popup__dialog" role="dialog" aria-modal="true" aria-labelledby="vpty-popup-title">
				<button type="button" class="vpty-popup__close" data-popup-close aria-label="<?php esc_attr_e( 'Cerrar', 'vacantespty' ); ?>">×</button>
				<h2 id="vpty-popup-title" data-ab-a="<?php esc_attr_e( '¿Te vas sin tu próxima vacante?', 'vacantespty' ); ?>" data-ab-b="<?php esc_attr_e( 'Espera, no te pierdas la próxima vacante', 'vacantespty' ); ?>"><?php esc_html_e( '¿Te vas sin tu próxima vacante?', 'vacantespty' ); ?></h2>
				<p data-ab-a="<?php esc_attr_e( 'Te avisamos cuando salga una de tu área. Gratis, sin spam.', 'vacantespty' ); ?>" data-ab-b="<?php esc_attr_e( 'Déjanos tu correo y WhatsApp y te llegan primero las de tu área.', 'vacantespty' ); ?>"><?php esc_html_e( 'Te avisamos cuando salga una de tu área. Gratis, sin spam.', 'vacantespty' ); ?></p>
				<?php echo vpty_lead_form( array( 'punto' => 'popup', 'categoria' => $category, 'compact' => true ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
				<p class="vpty-popup__alt"><?php echo vpty_channel_link( 'popup', __( 'o sigue el canal de Vacantes PTY en WhatsApp', 'vacantespty' ), 'vpty-link' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></p>
			</div>
		</div>
		<?php
	}
);
