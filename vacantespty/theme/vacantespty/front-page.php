<?php
get_header();

$archive_url = vpty_core_active() ? get_post_type_archive_link( 'vacante' ) : home_url( '/' );
?>

<section class="hero">
	<div class="container hero__inner">
		<p class="hero__eyebrow">🇵🇦 <?php esc_html_e( 'El portal de empleo de Panamá', 'vacantespty' ); ?></p>
		<h1 class="hero__title"><?php echo vpty_highlight_last_word( vpty_theme_setting( 'hero_titulo', get_bloginfo( 'name' ) ) ); // phpcs:ignore WordPress.Security.EscapeOutput -- escaped in helper. ?></h1>
		<p class="hero__subtitle"><?php echo esc_html( vpty_theme_setting( 'hero_subtitulo', get_bloginfo( 'description' ) ) ); ?></p>

		<?php if ( vpty_core_active() ) : ?>
			<?php echo vpty_search_form(); // phpcs:ignore WordPress.Security.EscapeOutput ?>

			<div class="chips">
				<span><?php esc_html_e( 'Popular:', 'vacantespty' ); ?></span>
				<a href="<?php echo esc_url( home_url( '/empleos-de/sin-experiencia-primer-empleo/' ) ); ?>">🚀 <?php esc_html_e( 'Sin experiencia', 'vacantespty' ); ?></a>
				<a href="<?php echo esc_url( add_query_arg( 'modalidad', 'remoto', $archive_url ) ); ?>">🏠 <?php esc_html_e( 'Remoto', 'vacantespty' ); ?></a>
				<a href="<?php echo esc_url( add_query_arg( 'tipo', 'PART_TIME', $archive_url ) ); ?>">🕒 <?php esc_html_e( 'Medio tiempo', 'vacantespty' ); ?></a>
				<a href="<?php echo esc_url( home_url( '/empleos-de/atencion-al-cliente-y-call-center/' ) ); ?>">🎧 <?php esc_html_e( 'Call center', 'vacantespty' ); ?></a>
				<a href="<?php echo esc_url( home_url( '/empleos-en/colon/' ) ); ?>">🚢 <?php esc_html_e( 'Colón', 'vacantespty' ); ?></a>
			</div>

			<ul class="hero__stats">
				<li><strong><?php echo esc_html( number_format_i18n( vpty_active_job_count() ) ); ?></strong> <?php esc_html_e( 'vacantes activas', 'vacantespty' ); ?></li>
				<li><strong>10</strong> <?php esc_html_e( 'provincias', 'vacantespty' ); ?></li>
				<li><strong>B/. 0</strong> <?php esc_html_e( 'por aplicar, siempre', 'vacantespty' ); ?></li>
			</ul>
		<?php endif; ?>
	</div>
</section>

<?php if ( vpty_core_active() ) : ?>

<div class="container section-ad"><?php vpty_theme_banner( 'home-top' ); ?></div>

<section class="section">
	<div class="container">
		<?php
		$destacadas = vpty_get_jobs( array( 'posts_per_page' => 4, 'destacadas' => true ) );
		if ( $destacadas->have_posts() ) :
			?>
			<div class="section__head">
				<h2>⭐ <?php esc_html_e( 'Vacantes destacadas', 'vacantespty' ); ?></h2>
			</div>
			<div class="vpty-list vpty-list--grid">
				<?php
				while ( $destacadas->have_posts() ) {
					$destacadas->the_post();
					echo vpty_job_card(); // phpcs:ignore WordPress.Security.EscapeOutput
				}
				wp_reset_postdata();
				?>
			</div>
		<?php endif; ?>

		<div class="section__head">
			<h2><?php esc_html_e( 'Empleos nuevos hoy', 'vacantespty' ); ?></h2>
			<a class="link-arrow" href="<?php echo esc_url( $archive_url ); ?>"><?php esc_html_e( 'Ver todas las vacantes', 'vacantespty' ); ?> →</a>
		</div>
		<div class="vpty-list">
			<?php
			$recientes = vpty_get_jobs( array( 'posts_per_page' => 8 ) );
			if ( $recientes->have_posts() ) {
				while ( $recientes->have_posts() ) {
					$recientes->the_post();
					echo vpty_job_card(); // phpcs:ignore WordPress.Security.EscapeOutput
				}
				wp_reset_postdata();
			} else {
				echo '<p class="empty">' . esc_html__( 'Muy pronto publicaremos las primeras vacantes.', 'vacantespty' ) . '</p>';
			}
			?>
		</div>
	</div>
</section>

<section class="section section--soft">
	<div class="container">
		<div class="section__head">
			<h2><?php esc_html_e( 'Explora por categoría', 'vacantespty' ); ?></h2>
		</div>
		<div class="cat-grid">
			<?php
			$cats = get_terms( array( 'taxonomy' => 'categoria_empleo', 'hide_empty' => false, 'parent' => 0 ) );
			foreach ( (array) $cats as $term ) :
				?>
				<a class="cat-card" href="<?php echo esc_url( get_term_link( $term ) ); ?>">
					<span class="cat-card__icon"><?php echo esc_html( vpty_category_icon( $term->slug ) ); ?></span>
					<span class="cat-card__name"><?php echo esc_html( $term->name ); ?></span>
					<span class="cat-card__count">
						<?php
						/* translators: %s: number of vacancies */
						echo esc_html( sprintf( _n( '%s vacante', '%s vacantes', $term->count, 'vacantespty' ), number_format_i18n( $term->count ) ) );
						?>
					</span>
				</a>
			<?php endforeach; ?>
		</div>
	</div>
</section>

<section class="section">
	<div class="container promise">
		<div class="promise__item">
			<span class="promise__icon">💵</span>
			<h3><?php esc_html_e( 'Salario visible', 'vacantespty' ); ?></h3>
			<p><?php esc_html_e( 'Sabes cuánto pagan antes de aplicar. Sin sorpresas en la entrevista.', 'vacantespty' ); ?></p>
		</div>
		<div class="promise__item">
			<span class="promise__icon">✅</span>
			<h3><?php esc_html_e( 'Empresas verificadas', 'vacantespty' ); ?></h3>
			<p><?php esc_html_e( 'Revisamos a las empresas para protegerte de estafas y ofertas falsas.', 'vacantespty' ); ?></p>
		</div>
		<div class="promise__item">
			<span class="promise__icon">🎯</span>
			<h3><?php esc_html_e( 'Aplica directo con la empresa', 'vacantespty' ); ?></h3>
			<p><?php esc_html_e( 'Sin registros ni intermediarios: te llevamos al canal oficial de postulación.', 'vacantespty' ); ?></p>
		</div>
	</div>
</section>

<section class="section section--tight">
	<div class="container duo">
		<a class="duo__card duo__card--accent" href="<?php echo esc_url( home_url( '/calculadora-salario-neto-panama/' ) ); ?>">
			<span class="duo__kicker"><?php esc_html_e( 'Herramienta gratis', 'vacantespty' ); ?></span>
			<h3><?php esc_html_e( '¿Cuánto recibirás realmente?', 'vacantespty' ); ?></h3>
			<p><?php esc_html_e( 'Calcula tu salario neto, Seguro Social, ISR y décimo tercer mes en segundos.', 'vacantespty' ); ?></p>
			<span class="vpty-btn vpty-btn--primary"><?php esc_html_e( 'Abrir calculadora', 'vacantespty' ); ?></span>
		</a>
		<div class="duo__card duo__card--dark">
			<span class="duo__kicker"><?php esc_html_e( 'Comunidad de +2,000 personas', 'vacantespty' ); ?></span>
			<h3><?php esc_html_e( 'Únete a la comunidad Vacantes PTY', 'vacantespty' ); ?></h3>
			<p><?php esc_html_e( 'Vacantes nuevas todos los días en nuestro canal de WhatsApp. Los primeros en aplicar son los primeros en ser llamados.', 'vacantespty' ); ?></p>
			<?php
			if ( function_exists( 'vpty_channel_link' ) ) {
				echo vpty_channel_link( 'inicio', __( 'Seguir el canal de WhatsApp', 'vacantespty' ) ); // phpcs:ignore WordPress.Security.EscapeOutput
			}
			?>
		</div>
	</div>
</section>

<div class="container section-ad"><?php vpty_theme_banner( 'home-middle' ); ?></div>

<section class="section">
	<div class="container">
		<div class="section__head">
			<h2><?php esc_html_e( 'Empleos por provincia', 'vacantespty' ); ?></h2>
		</div>
		<div class="prov-grid">
			<?php
			$provs = get_terms( array( 'taxonomy' => 'provincia', 'hide_empty' => false, 'parent' => 0 ) );
			foreach ( (array) $provs as $term ) :
				?>
				<a href="<?php echo esc_url( get_term_link( $term ) ); ?>">
					<strong><?php echo esc_html( $term->name ); ?></strong>
					<span><?php echo esc_html( number_format_i18n( $term->count ) ); ?></span>
				</a>
			<?php endforeach; ?>
		</div>
	</div>
</section>

<?php endif; ?>

<?php
$blog_posts = new WP_Query( array( 'posts_per_page' => 3, 'ignore_sticky_posts' => true ) );
if ( $blog_posts->have_posts() ) :
	?>
	<section class="section section--soft">
		<div class="container">
			<div class="section__head">
				<h2><?php esc_html_e( 'Consejos para conseguir empleo', 'vacantespty' ); ?></h2>
				<?php if ( get_option( 'page_for_posts' ) ) : ?>
					<a class="link-arrow" href="<?php echo esc_url( get_permalink( get_option( 'page_for_posts' ) ) ); ?>"><?php esc_html_e( 'Ir al blog', 'vacantespty' ); ?> →</a>
				<?php endif; ?>
			</div>
			<div class="post-grid">
				<?php
				while ( $blog_posts->have_posts() ) {
					$blog_posts->the_post();
					get_template_part( 'template-parts/card-post' );
				}
				wp_reset_postdata();
				?>
			</div>
		</div>
	</section>
<?php endif; ?>

<section class="section">
	<div class="container cta-empresas">
		<div>
			<h2><?php esc_html_e( '¿Tu empresa está contratando?', 'vacantespty' ); ?></h2>
			<p><?php esc_html_e( 'Publica tu vacante y aparece en Google Empleos, en nuestras redes y en el canal de alertas. Llega al talento correcto en horas, no en semanas.', 'vacantespty' ); ?></p>
		</div>
		<a class="vpty-btn vpty-btn--accent vpty-btn--lg" href="<?php echo esc_url( home_url( '/publicar-vacante/' ) ); ?>"><?php esc_html_e( 'Publicar vacante', 'vacantespty' ); ?></a>
	</div>
</section>

<?php
get_footer();
