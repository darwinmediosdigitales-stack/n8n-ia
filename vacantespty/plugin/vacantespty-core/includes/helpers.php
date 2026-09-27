<?php
defined( 'ABSPATH' ) || exit;

/**
 * Fields stored on each vacancy. Keys are meta keys without the `_vpty_` prefix.
 */
function vpty_job_fields() {
	return array(
		'empresa'         => array( 'label' => __( 'Empresa', 'vacantespty' ), 'type' => 'text' ),
		'empresa_web'     => array( 'label' => __( 'Web de la empresa', 'vacantespty' ), 'type' => 'url' ),
		'verificada'      => array( 'label' => __( 'Empresa verificada', 'vacantespty' ), 'type' => 'checkbox' ),
		'destacada'       => array( 'label' => __( 'Vacante destacada (pagada)', 'vacantespty' ), 'type' => 'checkbox' ),
		'ciudad'          => array( 'label' => __( 'Ciudad / corregimiento', 'vacantespty' ), 'type' => 'text', 'placeholder' => 'Ciudad de Panamá, Obarrio' ),
		'modalidad'       => array( 'label' => __( 'Modalidad', 'vacantespty' ), 'type' => 'select', 'options' => vpty_modalidades() ),
		'tipo'            => array( 'label' => __( 'Tipo de contrato', 'vacantespty' ), 'type' => 'select', 'options' => vpty_tipos_contrato() ),
		'salario_min'     => array( 'label' => __( 'Salario mínimo (B/.)', 'vacantespty' ), 'type' => 'number' ),
		'salario_max'     => array( 'label' => __( 'Salario máximo (B/.)', 'vacantespty' ), 'type' => 'number' ),
		'salario_periodo' => array( 'label' => __( 'Salario por', 'vacantespty' ), 'type' => 'select', 'options' => vpty_periodos_salario() ),
		'experiencia'     => array( 'label' => __( 'Experiencia requerida', 'vacantespty' ), 'type' => 'text', 'placeholder' => '1 año / Sin experiencia' ),
		'url'             => array( 'label' => __( 'Enlace para aplicar (formulario o publicación original)', 'vacantespty' ), 'type' => 'url' ),
		'email'           => array( 'label' => __( 'Correo de la empresa para aplicar', 'vacantespty' ), 'type' => 'email' ),
		'whatsapp'        => array( 'label' => __( 'WhatsApp de la empresa (solo si la empresa lo publicó)', 'vacantespty' ), 'type' => 'text', 'placeholder' => '6000-0000' ),
		'vence'           => array( 'label' => __( 'Fecha de cierre', 'vacantespty' ), 'type' => 'date' ),
	);
}

function vpty_modalidades() {
	return array(
		'presencial' => __( 'Presencial', 'vacantespty' ),
		'hibrido'    => __( 'Híbrido', 'vacantespty' ),
		'remoto'     => __( 'Remoto', 'vacantespty' ),
	);
}

/** Keys match schema.org employmentType values. */
function vpty_tipos_contrato() {
	return array(
		'FULL_TIME'  => __( 'Tiempo completo', 'vacantespty' ),
		'PART_TIME'  => __( 'Medio tiempo', 'vacantespty' ),
		'TEMPORARY'  => __( 'Temporal', 'vacantespty' ),
		'CONTRACTOR' => __( 'Por servicios profesionales', 'vacantespty' ),
		'INTERN'     => __( 'Pasantía', 'vacantespty' ),
	);
}

/** Keys match schema.org unitText values. */
function vpty_periodos_salario() {
	return array(
		'MONTH' => __( 'mes', 'vacantespty' ),
		'WEEK'  => __( 'semana', 'vacantespty' ),
		'HOUR'  => __( 'hora', 'vacantespty' ),
		'YEAR'  => __( 'año', 'vacantespty' ),
	);
}

function vpty_get( $key, $post_id = null ) {
	$post_id = $post_id ? $post_id : get_the_ID();
	return get_post_meta( $post_id, '_vpty_' . $key, true );
}

function vpty_option_label( $options, $key ) {
	return isset( $options[ $key ] ) ? $options[ $key ] : '';
}

function vpty_money( $amount ) {
	$decimals = floor( (float) $amount ) == (float) $amount ? 0 : 2;
	return 'B/. ' . number_format_i18n( (float) $amount, $decimals );
}

/** Human-readable salary, or empty string when not published. */
function vpty_salary_text( $post_id = null ) {
	$min     = vpty_get( 'salario_min', $post_id );
	$max     = vpty_get( 'salario_max', $post_id );
	$periodo = vpty_option_label( vpty_periodos_salario(), vpty_get( 'salario_periodo', $post_id ) ?: 'MONTH' );

	if ( '' === $min && '' === $max ) {
		return '';
	}
	if ( '' !== $min && '' !== $max && (float) $min !== (float) $max ) {
		return sprintf( '%s – %s / %s', vpty_money( $min ), vpty_money( $max ), $periodo );
	}
	return sprintf( '%s / %s', vpty_money( '' !== $min ? $min : $max ), $periodo );
}

function vpty_is_expired( $post_id = null ) {
	$vence = vpty_get( 'vence', $post_id );
	return $vence && $vence < current_time( 'Y-m-d' );
}

function vpty_is_new( $post_id = null ) {
	return ( current_time( 'timestamp' ) - get_post_time( 'U', false, $post_id ) ) < 3 * DAY_IN_SECONDS;
}

/** Normalizes a Panamanian phone to international digits (8-digit locals get +507). */
function vpty_phone_digits( $phone ) {
	$digits = preg_replace( '/\D+/', '', (string) $phone );
	if ( strlen( $digits ) === 7 || strlen( $digits ) === 8 ) {
		$digits = '507' . $digits;
	}
	return $digits;
}

function vpty_whatsapp_url( $phone, $message = '' ) {
	$digits = vpty_phone_digits( $phone );
	if ( ! $digits ) {
		return '';
	}
	$url = 'https://wa.me/' . $digits;
	return $message ? add_query_arg( 'text', rawurlencode( $message ), $url ) : $url;
}

function vpty_apply_whatsapp_url( $post_id = null ) {
	$post_id = $post_id ? $post_id : get_the_ID();
	/* translators: 1: job title, 2: site name */
	$msg = sprintf( __( 'Hola, vi la vacante "%1$s" en %2$s y me interesa aplicar.', 'vacantespty' ), get_the_title( $post_id ), get_bloginfo( 'name' ) );
	return vpty_whatsapp_url( vpty_get( 'whatsapp', $post_id ), $msg );
}

function vpty_first_term( $taxonomy, $post_id = null ) {
	$terms = get_the_terms( $post_id ? $post_id : get_the_ID(), $taxonomy );
	return ( $terms && ! is_wp_error( $terms ) ) ? $terms[0] : null;
}

/** Initials used as a logo placeholder when a vacancy has no company logo. */
function vpty_initials( $text ) {
	$words    = preg_split( '/\s+/', trim( wp_strip_all_tags( $text ) ) );
	$initials = '';
	foreach ( array_slice( $words, 0, 2 ) as $w ) {
		$initials .= mb_strtoupper( mb_substr( $w, 0, 1 ) );
	}
	return $initials ? $initials : 'VP';
}

/**
 * Main "Aplicar" action: the company's real application channel, in order of
 * preference: external form/original post, email, company WhatsApp.
 */
function vpty_apply_action( $post_id = null ) {
	$post_id = $post_id ? $post_id : get_the_ID();
	$url     = vpty_get( 'url', $post_id );
	$email   = vpty_get( 'email', $post_id );
	if ( $url ) {
		return array( 'url' => $url, 'label' => __( 'Aplicar a esta vacante', 'vacantespty' ), 'type' => 'url' );
	}
	if ( $email ) {
		return array( 'url' => 'mailto:' . $email . '?subject=' . rawurlencode( get_the_title( $post_id ) ), 'label' => __( 'Aplicar por correo', 'vacantespty' ), 'type' => 'email' );
	}
	$wa = vpty_apply_whatsapp_url( $post_id );
	if ( $wa ) {
		return array( 'url' => $wa, 'label' => __( 'Aplicar por WhatsApp', 'vacantespty' ), 'type' => 'whatsapp' );
	}
	return null;
}
