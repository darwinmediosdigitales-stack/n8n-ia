<?php
defined( 'ABSPATH' ) || exit;

add_action( 'add_meta_boxes', 'vpty_add_meta_boxes' );
add_action( 'save_post_vacante', 'vpty_save_job_meta' );
add_action( 'save_post_banner', 'vpty_save_banner_meta' );

function vpty_add_meta_boxes() {
	add_meta_box( 'vpty_job', __( 'Datos de la vacante', 'vacantespty' ), 'vpty_render_job_box', 'vacante', 'normal', 'high' );
	add_meta_box( 'vpty_banner', __( 'Configuración del banner', 'vacantespty' ), 'vpty_render_banner_box', 'banner', 'normal', 'high' );
}

function vpty_render_field( $key, $field, $value ) {
	$name = 'vpty[' . $key . ']';
	$id   = 'vpty_' . $key;
	echo '<p class="vpty-field"><label for="' . esc_attr( $id ) . '"><strong>' . esc_html( $field['label'] ) . '</strong></label><br>';

	switch ( $field['type'] ) {
		case 'checkbox':
			printf( '<input type="checkbox" id="%s" name="%s" value="1" %s>', esc_attr( $id ), esc_attr( $name ), checked( $value, '1', false ) );
			break;
		case 'select':
			printf( '<select id="%s" name="%s"><option value="">—</option>', esc_attr( $id ), esc_attr( $name ) );
			foreach ( $field['options'] as $opt => $label ) {
				printf( '<option value="%s" %s>%s</option>', esc_attr( $opt ), selected( $value, $opt, false ), esc_html( $label ) );
			}
			echo '</select>';
			break;
		default:
			printf(
				'<input type="%s" id="%s" name="%s" value="%s" placeholder="%s" class="widefat" %s>',
				esc_attr( $field['type'] ),
				esc_attr( $id ),
				esc_attr( $name ),
				esc_attr( $value ),
				esc_attr( isset( $field['placeholder'] ) ? $field['placeholder'] : '' ),
				'number' === $field['type'] ? 'step="0.01" min="0"' : ''
			);
	}
	echo '</p>';
}

function vpty_render_job_box( $post ) {
	wp_nonce_field( 'vpty_save_job', 'vpty_job_nonce' );
	echo '<style>.vpty-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(240px,1fr));gap:4px 20px}</style>';
	echo '<p>' . esc_html__( 'Tip: publica el salario. Las vacantes con salario reciben más postulaciones y Google las muestra mejor.', 'vacantespty' ) . '</p>';
	echo '<div class="vpty-grid">';
	foreach ( vpty_job_fields() as $key => $field ) {
		vpty_render_field( $key, $field, get_post_meta( $post->ID, '_vpty_' . $key, true ) );
	}
	echo '</div>';
}

function vpty_sanitize_field( $field, $raw ) {
	switch ( $field['type'] ) {
		case 'checkbox':
			return $raw ? '1' : '';
		case 'url':
			return esc_url_raw( $raw );
		case 'email':
			return sanitize_email( $raw );
		case 'number':
			return '' === $raw ? '' : (string) max( 0, (float) $raw );
		case 'date':
			return preg_match( '/^\d{4}-\d{2}-\d{2}$/', $raw ) ? $raw : '';
		case 'select':
			return array_key_exists( $raw, $field['options'] ) ? $raw : '';
		default:
			return sanitize_text_field( $raw );
	}
}

function vpty_can_save( $post_id, $nonce_field, $action ) {
	if ( ! isset( $_POST[ $nonce_field ] ) || ! wp_verify_nonce( sanitize_key( $_POST[ $nonce_field ] ), $action ) ) {
		return false;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return false;
	}
	return current_user_can( 'edit_post', $post_id );
}

function vpty_save_job_meta( $post_id ) {
	if ( ! vpty_can_save( $post_id, 'vpty_job_nonce', 'vpty_save_job' ) ) {
		return;
	}
	$input = isset( $_POST['vpty'] ) ? wp_unslash( (array) $_POST['vpty'] ) : array(); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- sanitized per field below.
	foreach ( vpty_job_fields() as $key => $field ) {
		$raw   = isset( $input[ $key ] ) ? trim( (string) $input[ $key ] ) : '';
		$value = vpty_sanitize_field( $field, $raw );
		if ( '' === $value ) {
			delete_post_meta( $post_id, '_vpty_' . $key );
		} else {
			update_post_meta( $post_id, '_vpty_' . $key, $value );
		}
	}
}

/* ---------- Banners ---------- */

function vpty_banner_zones() {
	return array(
		'home-top'        => __( 'Inicio – debajo del buscador (horizontal 970×250 / 728×90)', 'vacantespty' ),
		'home-middle'     => __( 'Inicio – entre secciones (horizontal)', 'vacantespty' ),
		'sidebar'         => __( 'Barra lateral (300×250 / 300×600)', 'vacantespty' ),
		'listing'         => __( 'Listado de vacantes – entre resultados (horizontal)', 'vacantespty' ),
		'job-bottom'      => __( 'Detalle de vacante – debajo de la descripción', 'vacantespty' ),
		'blog'            => __( 'Artículos del blog – dentro del contenido', 'vacantespty' ),
	);
}

function vpty_render_banner_box( $post ) {
	wp_nonce_field( 'vpty_save_banner', 'vpty_banner_nonce' );
	$zones  = (array) get_post_meta( $post->ID, '_vpty_zonas', true );
	$clicks = (int) get_post_meta( $post->ID, '_vpty_clicks', true );

	echo '<p>' . esc_html__( 'Sube la imagen del banner en "Imagen del banner" (columna derecha).', 'vacantespty' ) . '</p>';
	vpty_render_field( 'url', array( 'label' => __( 'Enlace de destino', 'vacantespty' ), 'type' => 'url' ), get_post_meta( $post->ID, '_vpty_url', true ) );

	echo '<p><strong>' . esc_html__( 'Dónde se muestra', 'vacantespty' ) . '</strong></p>';
	foreach ( vpty_banner_zones() as $zone => $label ) {
		printf(
			'<label style="display:block;margin:4px 0"><input type="checkbox" name="vpty_zonas[]" value="%s" %s> %s</label>',
			esc_attr( $zone ),
			checked( in_array( $zone, $zones, true ), true, false ),
			esc_html( $label )
		);
	}

	vpty_render_field( 'inicio', array( 'label' => __( 'Mostrar desde', 'vacantespty' ), 'type' => 'date' ), get_post_meta( $post->ID, '_vpty_inicio', true ) );
	vpty_render_field( 'fin', array( 'label' => __( 'Mostrar hasta', 'vacantespty' ), 'type' => 'date' ), get_post_meta( $post->ID, '_vpty_fin', true ) );

	/* translators: %d: click count */
	echo '<p style="font-size:14px">' . esc_html( sprintf( __( 'Clics registrados: %d', 'vacantespty' ), $clicks ) ) . '</p>';
}

function vpty_save_banner_meta( $post_id ) {
	if ( ! vpty_can_save( $post_id, 'vpty_banner_nonce', 'vpty_save_banner' ) ) {
		return;
	}
	$input = isset( $_POST['vpty'] ) ? wp_unslash( (array) $_POST['vpty'] ) : array(); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
	update_post_meta( $post_id, '_vpty_url', esc_url_raw( isset( $input['url'] ) ? $input['url'] : '' ) );
	foreach ( array( 'inicio', 'fin' ) as $key ) {
		$value = vpty_sanitize_field( array( 'type' => 'date' ), isset( $input[ $key ] ) ? $input[ $key ] : '' );
		update_post_meta( $post_id, '_vpty_' . $key, $value );
	}
	$zones = isset( $_POST['vpty_zonas'] ) ? array_map( 'sanitize_key', (array) wp_unslash( $_POST['vpty_zonas'] ) ) : array();
	update_post_meta( $post_id, '_vpty_zonas', array_values( array_intersect( $zones, array_keys( vpty_banner_zones() ) ) ) );
}

add_filter( 'manage_banner_posts_columns', 'vpty_banner_columns' );
add_action( 'manage_banner_posts_custom_column', 'vpty_banner_column_content', 10, 2 );

function vpty_banner_columns( $columns ) {
	return array(
		'cb'           => $columns['cb'],
		'title'        => $columns['title'],
		'vpty_preview' => __( 'Imagen', 'vacantespty' ),
		'vpty_zonas'   => __( 'Zonas', 'vacantespty' ),
		'vpty_fechas'  => __( 'Vigencia', 'vacantespty' ),
		'vpty_clicks'  => __( 'Clics', 'vacantespty' ),
	);
}

function vpty_banner_column_content( $column, $post_id ) {
	switch ( $column ) {
		case 'vpty_preview':
			echo get_the_post_thumbnail( $post_id, array( 160, 60 ), array( 'style' => 'max-height:60px;width:auto' ) );
			break;
		case 'vpty_zonas':
			echo esc_html( implode( ', ', (array) get_post_meta( $post_id, '_vpty_zonas', true ) ) );
			break;
		case 'vpty_fechas':
			$from = get_post_meta( $post_id, '_vpty_inicio', true );
			$to   = get_post_meta( $post_id, '_vpty_fin', true );
			echo esc_html( ( $from ? $from : '…' ) . ' → ' . ( $to ? $to : '…' ) );
			break;
		case 'vpty_clicks':
			echo (int) get_post_meta( $post_id, '_vpty_clicks', true );
			break;
	}
}
