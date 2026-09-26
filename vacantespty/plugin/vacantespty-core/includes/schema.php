<?php
defined( 'ABSPATH' ) || exit;

add_action( 'wp_head', 'vpty_output_job_schema' );

/**
 * JobPosting structured data so vacancies are eligible for Google for Jobs.
 * https://developers.google.com/search/docs/appearance/structured-data/job-posting
 */
function vpty_job_schema( $post_id ) {
	$empresa   = vpty_get( 'empresa', $post_id );
	$provincia = vpty_first_term( 'provincia', $post_id );
	$ciudad    = vpty_get( 'ciudad', $post_id );
	$modalidad = vpty_get( 'modalidad', $post_id );
	$tipo      = vpty_get( 'tipo', $post_id );
	$vence     = vpty_get( 'vence', $post_id );

	$data = array(
		'@context'       => 'https://schema.org/',
		'@type'          => 'JobPosting',
		'title'          => get_the_title( $post_id ),
		'description'    => wpautop( get_post_field( 'post_content', $post_id ) ),
		'datePosted'     => get_the_date( 'c', $post_id ),
		'identifier'     => array(
			'@type' => 'PropertyValue',
			'name'  => get_bloginfo( 'name' ),
			'value' => (string) $post_id,
		),
		'hiringOrganization' => array_filter(
			array(
				'@type'  => 'Organization',
				'name'   => $empresa ? $empresa : __( 'Empresa confidencial', 'vacantespty' ),
				'sameAs' => vpty_get( 'empresa_web', $post_id ),
				'logo'   => get_the_post_thumbnail_url( $post_id, 'medium' ),
			)
		),
		'jobLocation'    => array(
			'@type'   => 'Place',
			'address' => array_filter(
				array(
					'@type'           => 'PostalAddress',
					'addressLocality' => $ciudad,
					'addressRegion'   => $provincia ? $provincia->name : '',
					'addressCountry'  => 'PA',
				)
			),
		),
		'directApply'    => (bool) ( vpty_get( 'whatsapp', $post_id ) || vpty_get( 'email', $post_id ) ),
	);

	if ( $vence ) {
		$data['validThrough'] = $vence . 'T23:59:59-05:00';
	}
	if ( $tipo ) {
		$data['employmentType'] = $tipo;
	}
	if ( 'remoto' === $modalidad ) {
		$data['jobLocationType']                 = 'TELECOMMUTE';
		$data['applicantLocationRequirements'] = array( '@type' => 'Country', 'name' => 'Panamá' );
	}

	$min = vpty_get( 'salario_min', $post_id );
	$max = vpty_get( 'salario_max', $post_id );
	if ( '' !== $min || '' !== $max ) {
		$value = array(
			'@type'    => 'QuantitativeValue',
			'unitText' => vpty_get( 'salario_periodo', $post_id ) ?: 'MONTH',
		);
		if ( '' !== $min && '' !== $max && (float) $min !== (float) $max ) {
			$value['minValue'] = (float) $min;
			$value['maxValue'] = (float) $max;
		} else {
			$value['value'] = (float) ( '' !== $min ? $min : $max );
		}
		$data['baseSalary'] = array(
			'@type'    => 'MonetaryAmount',
			'currency' => 'USD',
			'value'    => $value,
		);
	}

	return apply_filters( 'vpty_job_schema', $data, $post_id );
}

function vpty_output_job_schema() {
	if ( ! is_singular( 'vacante' ) || vpty_is_expired() ) {
		return;
	}
	echo '<script type="application/ld+json">' . wp_json_encode( vpty_job_schema( get_the_ID() ), JSON_UNESCAPED_UNICODE | JSON_HEX_TAG ) . "</script>\n";
}

/**
 * Basic meta description when no SEO plugin is active.
 */
add_action(
	'wp_head',
	function () {
		if ( defined( 'RANK_MATH_VERSION' ) || defined( 'WPSEO_VERSION' ) || defined( 'AIOSEO_VERSION' ) || defined( 'SEOPRESS_VERSION' ) ) {
			return;
		}
		$desc = '';
		if ( is_singular() ) {
			$desc = has_excerpt() ? get_the_excerpt() : wp_trim_words( wp_strip_all_tags( get_post_field( 'post_content', get_the_ID() ) ), 28, '…' );
		} elseif ( is_tax( 'categoria_empleo' ) ) {
			/* translators: %s: category name */
			$desc = sprintf( __( 'Vacantes de %s en Panamá actualizadas hoy. Salarios visibles y aplicación directa por WhatsApp.', 'vacantespty' ), single_term_title( '', false ) );
		} elseif ( is_tax( 'provincia' ) ) {
			/* translators: %s: province name */
			$desc = sprintf( __( 'Empleos en %s, Panamá: vacantes nuevas cada día con salario visible y aplicación directa.', 'vacantespty' ), single_term_title( '', false ) );
		} elseif ( is_front_page() || is_post_type_archive( 'vacante' ) ) {
			$desc = __( 'Encuentra empleo en Panamá hoy: vacantes con salario visible, empresas verificadas y aplicación directa por WhatsApp.', 'vacantespty' );
		}
		if ( $desc ) {
			echo '<meta name="description" content="' . esc_attr( $desc ) . '">' . "\n";
		}
	},
	1
);
