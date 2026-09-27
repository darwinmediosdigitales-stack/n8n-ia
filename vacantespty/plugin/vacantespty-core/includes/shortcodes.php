<?php
defined( 'ABSPATH' ) || exit;

/**
 * Vacancy card markup, shared by the theme and the [vpty_vacantes] shortcode.
 */
function vpty_job_card( $post_id = null ) {
	$post_id   = $post_id ? $post_id : get_the_ID();
	$empresa   = vpty_get( 'empresa', $post_id );
	$salario   = vpty_salary_text( $post_id );
	$provincia = vpty_first_term( 'provincia', $post_id );
	$categoria = vpty_first_term( 'categoria_empleo', $post_id );
	$modalidad = vpty_option_label( vpty_modalidades(), vpty_get( 'modalidad', $post_id ) );
	$tipo      = vpty_option_label( vpty_tipos_contrato(), vpty_get( 'tipo', $post_id ) );
	$classes   = 'vpty-card' . ( vpty_get( 'destacada', $post_id ) ? ' vpty-card--featured' : '' );

	ob_start();
	?>
	<article class="<?php echo esc_attr( $classes ); ?>">
		<div class="vpty-card__logo">
			<?php
			if ( has_post_thumbnail( $post_id ) ) {
				echo get_the_post_thumbnail( $post_id, 'thumbnail', array( 'loading' => 'lazy', 'alt' => esc_attr( $empresa ) ) );
			} else {
				echo '<span>' . esc_html( vpty_initials( $empresa ? $empresa : get_the_title( $post_id ) ) ) . '</span>';
			}
			?>
		</div>
		<div class="vpty-card__body">
			<div class="vpty-card__badges">
				<?php if ( vpty_get( 'destacada', $post_id ) ) : ?>
					<span class="vpty-badge vpty-badge--featured"><?php esc_html_e( 'Destacada', 'vacantespty' ); ?></span>
				<?php endif; ?>
				<?php if ( vpty_is_new( $post_id ) ) : ?>
					<span class="vpty-badge vpty-badge--new"><?php esc_html_e( 'Nueva', 'vacantespty' ); ?></span>
				<?php endif; ?>
				<?php if ( $categoria ) : ?>
					<span class="vpty-badge"><?php echo esc_html( $categoria->name ); ?></span>
				<?php endif; ?>
			</div>
			<h3 class="vpty-card__title"><a href="<?php echo esc_url( get_permalink( $post_id ) ); ?>"><?php echo esc_html( get_the_title( $post_id ) ); ?></a></h3>
			<p class="vpty-card__company">
				<?php echo esc_html( $empresa ? $empresa : __( 'Empresa confidencial', 'vacantespty' ) ); ?>
				<?php if ( vpty_get( 'verificada', $post_id ) ) : ?>
					<span class="vpty-verified" title="<?php esc_attr_e( 'Empresa verificada', 'vacantespty' ); ?>">✔</span>
				<?php endif; ?>
			</p>
			<ul class="vpty-card__meta">
				<?php if ( $provincia ) : ?>
					<li>📍 <?php echo esc_html( $provincia->name ); ?></li>
				<?php endif; ?>
				<?php if ( $modalidad ) : ?>
					<li>🏢 <?php echo esc_html( $modalidad ); ?></li>
				<?php endif; ?>
				<?php if ( $tipo ) : ?>
					<li>🕒 <?php echo esc_html( $tipo ); ?></li>
				<?php endif; ?>
			</ul>
		</div>
		<div class="vpty-card__side">
			<?php if ( $salario ) : ?>
				<span class="vpty-card__salary"><?php echo esc_html( $salario ); ?></span>
			<?php else : ?>
				<span class="vpty-card__salary vpty-card__salary--none"><?php esc_html_e( 'Salario a convenir', 'vacantespty' ); ?></span>
			<?php endif; ?>
			<time datetime="<?php echo esc_attr( get_the_date( 'c', $post_id ) ); ?>">
				<?php
				/* translators: %s: relative time, e.g. "2 horas" */
				echo esc_html( sprintf( __( 'hace %s', 'vacantespty' ), human_time_diff( get_post_time( 'U', true, $post_id ), time() ) ) );
				?>
			</time>
		</div>
	</article>
	<?php
	return ob_get_clean();
}

function vpty_search_form( $args = array() ) {
	// phpcs:disable WordPress.Security.NonceVerification.Recommended -- public search form.
	$q    = isset( $_GET['q'] ) ? sanitize_text_field( wp_unslash( $_GET['q'] ) ) : '';
	$cat  = isset( $_GET['categoria'] ) ? sanitize_title( wp_unslash( $_GET['categoria'] ) ) : '';
	$prov = isset( $_GET['prov'] ) ? sanitize_title( wp_unslash( $_GET['prov'] ) ) : '';
	// phpcs:enable

	if ( ! $cat && is_tax( 'categoria_empleo' ) ) {
		$cat = get_queried_object()->slug;
	}
	if ( ! $prov && is_tax( 'provincia' ) ) {
		$prov = get_queried_object()->slug;
	}

	$categorias = get_terms( array( 'taxonomy' => 'categoria_empleo', 'hide_empty' => false, 'parent' => 0 ) );
	$provincias = get_terms( array( 'taxonomy' => 'provincia', 'hide_empty' => false, 'parent' => 0 ) );

	ob_start();
	?>
	<form class="vpty-search" role="search" method="get" action="<?php echo esc_url( get_post_type_archive_link( 'vacante' ) ); ?>">
		<label class="vpty-search__field vpty-search__field--q">
			<span class="screen-reader-text"><?php esc_html_e( 'Puesto o palabra clave', 'vacantespty' ); ?></span>
			<input type="search" name="q" value="<?php echo esc_attr( $q ); ?>" placeholder="<?php esc_attr_e( 'Puesto, empresa o palabra clave', 'vacantespty' ); ?>">
		</label>
		<label class="vpty-search__field">
			<span class="screen-reader-text"><?php esc_html_e( 'Categoría', 'vacantespty' ); ?></span>
			<select name="categoria">
				<option value=""><?php esc_html_e( 'Todas las categorías', 'vacantespty' ); ?></option>
				<?php foreach ( (array) $categorias as $term ) : ?>
					<option value="<?php echo esc_attr( $term->slug ); ?>" <?php selected( $cat, $term->slug ); ?>><?php echo esc_html( $term->name ); ?></option>
				<?php endforeach; ?>
			</select>
		</label>
		<label class="vpty-search__field">
			<span class="screen-reader-text"><?php esc_html_e( 'Provincia', 'vacantespty' ); ?></span>
			<select name="prov">
				<option value=""><?php esc_html_e( 'Todo Panamá', 'vacantespty' ); ?></option>
				<?php foreach ( (array) $provincias as $term ) : ?>
					<option value="<?php echo esc_attr( $term->slug ); ?>" <?php selected( $prov, $term->slug ); ?>><?php echo esc_html( $term->name ); ?></option>
				<?php endforeach; ?>
			</select>
		</label>
		<button type="submit" class="vpty-btn vpty-btn--accent"><?php esc_html_e( 'Buscar empleo', 'vacantespty' ); ?></button>
	</form>
	<?php
	return ob_get_clean();
}

add_shortcode( 'vpty_buscador', 'vpty_search_form' );

add_shortcode(
	'vpty_banner',
	function ( $atts ) {
		$atts = shortcode_atts( array( 'zona' => 'sidebar' ), $atts );
		return vpty_banner( sanitize_key( $atts['zona'] ) );
	}
);

add_shortcode(
	'vpty_vacantes',
	function ( $atts ) {
		$atts = shortcode_atts(
			array(
				'cantidad'   => 6,
				'categoria'  => '',
				'provincia'  => '',
				'destacadas' => '',
			),
			$atts
		);
		$args = array(
			'posts_per_page' => min( 50, absint( $atts['cantidad'] ) ),
			'destacadas'     => (bool) $atts['destacadas'],
		);
		$tax  = array();
		if ( $atts['categoria'] ) {
			$tax[] = array( 'taxonomy' => 'categoria_empleo', 'field' => 'slug', 'terms' => sanitize_title( $atts['categoria'] ) );
		}
		if ( $atts['provincia'] ) {
			$tax[] = array( 'taxonomy' => 'provincia', 'field' => 'slug', 'terms' => sanitize_title( $atts['provincia'] ) );
		}
		if ( $tax ) {
			$args['tax_query'] = $tax; // phpcs:ignore WordPress.DB.SlowDBQuery
		}
		$jobs = vpty_get_jobs( $args );
		$html = '<div class="vpty-list">';
		while ( $jobs->have_posts() ) {
			$jobs->the_post();
			$html .= vpty_job_card();
		}
		wp_reset_postdata();
		return $html . '</div>';
	}
);

/**
 * Net salary calculator for Panama (CSS 9.75%, Seguro Educativo 1.25%, ISR brackets).
 */
add_shortcode(
	'vpty_calculadora',
	function () {
		wp_enqueue_script( 'vpty-calculadora', VPTY_URL . 'assets/calculadora.js', array(), VPTY_VERSION, true );
		ob_start();
		?>
		<div class="vpty-calc" data-vpty-calc>
			<div class="vpty-calc__tabs" role="tablist">
				<button type="button" class="is-active" data-tab="neto"><?php esc_html_e( 'Salario neto', 'vacantespty' ); ?></button>
				<button type="button" data-tab="decimo"><?php esc_html_e( 'Décimo tercer mes', 'vacantespty' ); ?></button>
			</div>

			<div class="vpty-calc__panel is-active" data-panel="neto">
				<label><?php esc_html_e( 'Salario bruto mensual (B/.)', 'vacantespty' ); ?>
					<input type="number" min="0" step="0.01" inputmode="decimal" data-in="bruto" placeholder="1000">
				</label>
				<table class="vpty-calc__result">
					<tr><td><?php esc_html_e( 'Seguro Social (9.75%)', 'vacantespty' ); ?></td><td data-out="css">—</td></tr>
					<tr><td><?php esc_html_e( 'Seguro Educativo (1.25%)', 'vacantespty' ); ?></td><td data-out="se">—</td></tr>
					<tr><td><?php esc_html_e( 'Impuesto sobre la renta (ISR)', 'vacantespty' ); ?></td><td data-out="isr">—</td></tr>
					<tr class="vpty-calc__total"><td><?php esc_html_e( 'Salario neto mensual', 'vacantespty' ); ?></td><td data-out="neto">—</td></tr>
					<tr><td><?php esc_html_e( 'Neto por quincena', 'vacantespty' ); ?></td><td data-out="quincena">—</td></tr>
				</table>
			</div>

			<div class="vpty-calc__panel" data-panel="decimo">
				<label><?php esc_html_e( 'Total ganado en el período de 4 meses (B/.)', 'vacantespty' ); ?>
					<input type="number" min="0" step="0.01" inputmode="decimal" data-in="periodo" placeholder="4000">
				</label>
				<table class="vpty-calc__result">
					<tr><td><?php esc_html_e( 'Décimo bruto (1/12)', 'vacantespty' ); ?></td><td data-out="dbruto">—</td></tr>
					<tr><td><?php esc_html_e( 'Seguro Social (7.25%)', 'vacantespty' ); ?></td><td data-out="dcss">—</td></tr>
					<tr class="vpty-calc__total"><td><?php esc_html_e( 'Décimo neto aproximado', 'vacantespty' ); ?></td><td data-out="dneto">—</td></tr>
				</table>
				<p class="vpty-calc__note"><?php esc_html_e( 'Se paga en tres partidas: 15 de abril, 15 de agosto y 15 de diciembre.', 'vacantespty' ); ?></p>
			</div>

			<p class="vpty-calc__note"><?php esc_html_e( 'Cálculo estimado con las tasas vigentes. Tu planilla puede variar por otros descuentos (préstamos, sindicato, dependientes).', 'vacantespty' ); ?></p>
		</div>
		<?php
		return ob_get_clean();
	}
);

/** Call to action for companies: they reach the brand by Instagram direct message. */
add_shortcode(
	'vpty_publicar',
	function () {
		return '<p><a class="vpty-btn vpty-btn--accent" href="' . esc_url( vpty_instagram_dm_url( __( 'Hola, quiero publicar una vacante en Vacantes PTY.', 'vacantespty' ) ) ) . '" target="_blank" rel="noopener" data-vpty-track="publicar_click" data-vpty-point="publicar">' . esc_html__( 'Escríbenos por Instagram para publicar', 'vacantespty' ) . '</a></p>';
	}
);
