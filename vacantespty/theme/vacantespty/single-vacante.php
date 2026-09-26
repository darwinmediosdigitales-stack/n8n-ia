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
	$wa        = vpty_apply_whatsapp_url();
	$email     = vpty_get( 'email' );
	$url       = vpty_get( 'url' );
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

			<?php vpty_theme_banner( 'job-bottom' ); ?>
		</article>

		<aside class="layout__side">
			<div class="apply-box">
				<?php if ( $expired ) : ?>
					<p class="apply-box__title"><?php esc_html_e( 'Vacante cerrada', 'vacantespty' ); ?></p>
				<?php else : ?>
					<p class="apply-box__title"><?php esc_html_e( '¿Te interesa? Aplica ahora', 'vacantespty' ); ?></p>
					<?php if ( $wa ) : ?>
						<a class="vpty-btn vpty-btn--whatsapp vpty-btn--block" href="<?php echo esc_url( $wa ); ?>" target="_blank" rel="noopener"><?php esc_html_e( 'Aplicar por WhatsApp', 'vacantespty' ); ?></a>
					<?php endif; ?>
					<?php if ( $email ) : ?>
						<a class="vpty-btn vpty-btn--primary vpty-btn--block" href="<?php echo esc_url( 'mailto:' . $email . '?subject=' . rawurlencode( get_the_title() ) ); ?>"><?php esc_html_e( 'Enviar hoja de vida', 'vacantespty' ); ?></a>
					<?php endif; ?>
					<?php if ( $url ) : ?>
						<a class="vpty-btn vpty-btn--ghost vpty-btn--block" href="<?php echo esc_url( $url ); ?>" target="_blank" rel="noopener nofollow"><?php esc_html_e( 'Aplicar en el sitio de la empresa', 'vacantespty' ); ?></a>
					<?php endif; ?>
					<?php if ( $vence ) : ?>
						<p class="apply-box__meta">
							<?php
							/* translators: %s: closing date */
							echo esc_html( sprintf( __( 'Cierra el %s', 'vacantespty' ), date_i18n( get_option( 'date_format' ), strtotime( $vence ) ) ) );
							?>
						</p>
					<?php endif; ?>
				<?php endif; ?>
				<p class="apply-box__meta">
					<?php
					/* translators: %s: date */
					echo esc_html( sprintf( __( 'Publicada el %s', 'vacantespty' ), get_the_date() ) );
					?>
				</p>
				<div class="share">
					<span><?php esc_html_e( 'Compartir:', 'vacantespty' ); ?></span>
					<a href="<?php echo esc_url( 'https://wa.me/?text=' . rawurlencode( get_the_title() . ' ' . get_permalink() ) ); ?>" target="_blank" rel="noopener">WhatsApp</a>
					<a href="<?php echo esc_url( 'https://www.facebook.com/sharer/sharer.php?u=' . rawurlencode( get_permalink() ) ); ?>" target="_blank" rel="noopener">Facebook</a>
					<a href="<?php echo esc_url( 'https://www.linkedin.com/sharing/share-offsite/?url=' . rawurlencode( get_permalink() ) ); ?>" target="_blank" rel="noopener">LinkedIn</a>
				</div>
			</div>
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

	if ( ! $expired && $wa ) :
		?>
		<a class="apply-sticky vpty-btn vpty-btn--whatsapp" href="<?php echo esc_url( $wa ); ?>" target="_blank" rel="noopener"><?php esc_html_e( 'Aplicar por WhatsApp', 'vacantespty' ); ?></a>
		<?php
	endif;
endwhile;

get_footer();
