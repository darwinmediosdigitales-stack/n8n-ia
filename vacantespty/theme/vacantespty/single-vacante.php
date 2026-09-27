<?php
get_header();

while ( have_posts() ) :
	the_post();

	$empresa   = vpty_get( 'empresa' );
	$salario   = vpty_salary_text();
	$provincia = vpty_first_term( 'provincia' );
	$categoria = vpty_first_term( 'categoria_empleo' );
	$modalidad = vpty_option_label( vpty_modalidades(), vpty_get( 'modalidad' ) );
	$tipo      = vpty_option_label( vpty_tipos_contrato(), vpty_get( 'tipo' ) );
	$apply     = vpty_apply_action();
	$vence     = vpty_get( 'vence' );
	$expired   = vpty_is_expired();
	?>

	<section class="job-hero">
		<div class="container">
			<nav class="breadcrumbs" aria-label="<?php esc_attr_e( 'Ruta', 'vacantespty' ); ?>">
				<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Inicio', 'vacantespty' ); ?></a> ›
				<a href="<?php echo esc_url( get_post_type_archive_link( 'vacante' ) ); ?>"><?php esc_html_e( 'Empleos', 'vacantespty' ); ?></a>
				<?php if ( $categoria ) : ?>
					› <a href="<?php echo esc_url( get_term_link( $categoria ) ); ?>"><?php echo esc_html( $categoria->name ); ?></a>
				<?php endif; ?>
			</nav>

			<div class="job-hero__head">
				<div class="vpty-card__logo vpty-card__logo--lg">
					<?php
					if ( has_post_thumbnail() ) {
						the_post_thumbnail( 'thumbnail', array( 'alt' => esc_attr( $empresa ) ) );
					} else {
						echo '<span>' . esc_html( vpty_initials( $empresa ? $empresa : get_the_title() ) ) . '</span>';
					}
					?>
				</div>
				<div>
					<h1><?php the_title(); ?></h1>
					<p class="job-hero__company">
						<?php echo esc_html( $empresa ? $empresa : __( 'Empresa confidencial', 'vacantespty' ) ); ?>
						<?php if ( vpty_get( 'verificada' ) ) : ?>
							<span class="vpty-badge vpty-badge--verified">✔ <?php esc_html_e( 'Verificada', 'vacantespty' ); ?></span>
						<?php endif; ?>
					</p>
				</div>
			</div>

			<ul class="job-facts">
				<li><small><?php esc_html_e( 'Salario', 'vacantespty' ); ?></small><strong><?php echo esc_html( $salario ? $salario : __( 'A convenir', 'vacantespty' ) ); ?></strong></li>
				<?php if ( $provincia || vpty_get( 'ciudad' ) ) : ?>
					<li><small><?php esc_html_e( 'Ubicación', 'vacantespty' ); ?></small><strong><?php echo esc_html( vpty_get( 'ciudad' ) ? vpty_get( 'ciudad' ) : $provincia->name ); ?></strong></li>
				<?php endif; ?>
				<?php if ( $modalidad ) : ?>
					<li><small><?php esc_html_e( 'Modalidad', 'vacantespty' ); ?></small><strong><?php echo esc_html( $modalidad ); ?></strong></li>
				<?php endif; ?>
				<?php if ( $tipo ) : ?>
					<li><small><?php esc_html_e( 'Contrato', 'vacantespty' ); ?></small><strong><?php echo esc_html( $tipo ); ?></strong></li>
				<?php endif; ?>
				<?php if ( vpty_get( 'experiencia' ) ) : ?>
					<li><small><?php esc_html_e( 'Experiencia', 'vacantespty' ); ?></small><strong><?php echo esc_html( vpty_get( 'experiencia' ) ); ?></strong></li>
				<?php endif; ?>
			</ul>
		</div>
	</section>

	<div class="container layout">
		<article class="layout__main">
			<?php if ( $expired ) : ?>
				<div class="notice-box notice-box--warn"><?php esc_html_e( 'Esta vacante ya cerró. Mira las vacantes similares más abajo.', 'vacantespty' ); ?></div>
			<?php endif; ?>

			<div class="entry-content">
				<?php the_content(); ?>
			</div>

			<div class="notice-box">
				🛡️ <?php esc_html_e( 'Ninguna empresa legítima te pedirá dinero para contratarte. Si te piden pagos, repórtalo.', 'vacantespty' ); ?>
			</div>

			<?php
			if ( function_exists( 'vpty_share_buttons' ) ) {
				echo vpty_share_buttons(); // phpcs:ignore WordPress.Security.EscapeOutput
			}
			if ( function_exists( 'vpty_profile_block' ) ) {
				echo vpty_profile_block( get_the_ID() ); // phpcs:ignore WordPress.Security.EscapeOutput
			}
			vpty_theme_banner( 'job-bottom' );
			?>
		</article>

		<aside class="layout__side">
			<div class="apply-box">
				<?php if ( $expired ) : ?>
					<p class="apply-box__title"><?php esc_html_e( 'Vacante cerrada', 'vacantespty' ); ?></p>
				<?php elseif ( $apply ) : ?>
					<p class="apply-box__title"><?php esc_html_e( '¿Te interesa? Aplica directo con la empresa', 'vacantespty' ); ?></p>
					<a class="vpty-btn vpty-btn--primary vpty-btn--lg vpty-btn--block" href="<?php echo esc_url( $apply['url'] ); ?>" <?php echo 'email' === $apply['type'] ? '' : 'target="_blank" rel="noopener nofollow"'; ?> data-vpty-track="apply_click" data-vpty-point="<?php echo esc_attr( $apply['type'] ); ?>"><?php echo esc_html( $apply['label'] ); ?></a>
					<p class="apply-box__meta"><?php esc_html_e( 'Postulas directamente con la empresa. Vacantes PTY no pide datos para aplicar.', 'vacantespty' ); ?></p>
				<?php else : ?>
					<p class="apply-box__title"><?php esc_html_e( 'Cómo aplicar', 'vacantespty' ); ?></p>
					<p class="apply-box__meta"><?php esc_html_e( 'Revisa las instrucciones de postulación en la descripción.', 'vacantespty' ); ?></p>
				<?php endif; ?>
				<?php if ( $vence && ! $expired ) : ?>
					<p class="apply-box__meta">
						<?php
						/* translators: %s: closing date */
						echo esc_html( sprintf( __( 'Cierra el %s', 'vacantespty' ), date_i18n( get_option( 'date_format' ), strtotime( $vence ) ) ) );
						?>
					</p>
				<?php endif; ?>
				<p class="apply-box__meta">
					<?php
					/* translators: %s: date */
					echo esc_html( sprintf( __( 'Publicada el %s', 'vacantespty' ), get_the_date() ) );
					?>
				</p>
			</div>

			<?php if ( function_exists( 'vpty_lead_form' ) ) : ?>
				<details class="lead-box">
					<summary>
						<span class="lead-box__title" data-ab-a="<?php esc_attr_e( '🔔 Recibe vacantes como esta en tu WhatsApp o correo', 'vacantespty' ); ?>" data-ab-b="<?php esc_attr_e( '🔔 ¿Quieres que te avisemos de más vacantes así?', 'vacantespty' ); ?>"><?php esc_html_e( '🔔 Recibe vacantes como esta en tu WhatsApp o correo', 'vacantespty' ); ?></span>
						<span class="lead-box__sub"><?php esc_html_e( 'Gratis. Toca para activar la alerta.', 'vacantespty' ); ?></span>
					</summary>
					<?php echo vpty_lead_form( array( 'punto' => 'vacante', 'categoria' => vpty_lead_category_for_job( get_the_ID() ), 'compact' => true, 'boton' => __( 'Activar alerta', 'vacantespty' ) ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
				</details>
				<p class="lead-box__alt"><?php echo vpty_channel_link( 'vacante', __( 'o sigue el canal de Vacantes PTY en WhatsApp', 'vacantespty' ), 'vpty-link' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></p>
			<?php endif; ?>
			<?php vpty_theme_banner( 'sidebar' ); ?>
		</aside>
	</div>

	<?php
	$related_args = array(
		'posts_per_page' => 4,
		'post__not_in'   => array( get_the_ID() ),
	);
	if ( $categoria ) {
		$related_args['tax_query'] = array( array( 'taxonomy' => 'categoria_empleo', 'terms' => $categoria->term_id ) ); // phpcs:ignore WordPress.DB.SlowDBQuery
	}
	$related = vpty_get_jobs( $related_args );
	if ( $related->have_posts() ) :
		?>
		<section class="section section--soft">
			<div class="container">
				<div class="section__head"><h2><?php esc_html_e( 'Vacantes similares', 'vacantespty' ); ?></h2></div>
				<div class="vpty-list">
					<?php
					while ( $related->have_posts() ) {
						$related->the_post();
						echo vpty_job_card(); // phpcs:ignore WordPress.Security.EscapeOutput
					}
					wp_reset_postdata();
					?>
				</div>
			</div>
		</section>
		<?php
	endif;

	if ( ! $expired && $apply ) :
		?>
		<a class="apply-sticky vpty-btn vpty-btn--primary" href="<?php echo esc_url( $apply['url'] ); ?>" <?php echo 'email' === $apply['type'] ? '' : 'target="_blank" rel="noopener nofollow"'; ?> data-vpty-track="apply_click" data-vpty-point="sticky"><?php echo esc_html( $apply['label'] ); ?></a>
		<?php
	endif;
endwhile;

get_footer();
