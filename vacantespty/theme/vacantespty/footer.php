</main>

<?php if ( function_exists( 'vpty_channel_link' ) ) : ?>
	<section class="footer-community">
		<div class="container footer-community__inner">
			<div>
				<strong><?php esc_html_e( 'Únete a la comunidad Vacantes PTY', 'vacantespty' ); ?></strong>
				<span><?php esc_html_e( 'Más de 2,000 personas ya reciben vacantes diarias en WhatsApp.', 'vacantespty' ); ?></span>
			</div>
			<?php echo vpty_channel_link( 'footer', __( 'Seguir el canal', 'vacantespty' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
		</div>
	</section>
<?php endif; ?>

<footer class="site-footer">
	<div class="container site-footer__grid">
		<div class="site-footer__about">
			<a class="brand brand--light" href="<?php echo esc_url( home_url( '/' ) ); ?>">
				<img class="brand__mark brand__mark--img" src="<?php echo esc_url( get_template_directory_uri() . '/assets/img/logo-mark.png' ); ?>" width="44" height="44" alt="">
				<span class="brand__text">vacantes<b>pty</b></span>
			</a>
			<p><?php esc_html_e( 'El portal de empleo de Panamá con salarios visibles, empresas verificadas y aplicación directa con la empresa.', 'vacantespty' ); ?></p>
			<?php echo vpty_social_links(); // phpcs:ignore WordPress.Security.EscapeOutput -- escaped in helper. ?>
		</div>

		<?php if ( taxonomy_exists( 'categoria_empleo' ) ) : ?>
			<div>
				<h4><?php esc_html_e( 'Empleos por categoría', 'vacantespty' ); ?></h4>
				<ul>
					<?php
					$cats = get_terms( array( 'taxonomy' => 'categoria_empleo', 'hide_empty' => false, 'number' => 8, 'orderby' => 'count', 'order' => 'DESC' ) );
					foreach ( (array) $cats as $term ) {
						echo '<li><a href="' . esc_url( get_term_link( $term ) ) . '">' . esc_html( $term->name ) . '</a></li>';
					}
					?>
				</ul>
			</div>
			<div>
				<h4><?php esc_html_e( 'Empleos por provincia', 'vacantespty' ); ?></h4>
				<ul>
					<?php
					$provs = get_terms( array( 'taxonomy' => 'provincia', 'hide_empty' => false, 'number' => 10 ) );
					foreach ( (array) $provs as $term ) {
						/* translators: %s: province */
						echo '<li><a href="' . esc_url( get_term_link( $term ) ) . '">' . esc_html( sprintf( __( 'Empleos en %s', 'vacantespty' ), $term->name ) ) . '</a></li>';
					}
					?>
				</ul>
			</div>
		<?php endif; ?>

		<div>
			<h4><?php esc_html_e( 'Vacantes PTY', 'vacantespty' ); ?></h4>
			<?php
			wp_nav_menu(
				array(
					'theme_location' => 'footer',
					'container'      => false,
					'depth'          => 1,
					'fallback_cb'    => 'vpty_default_menu',
				)
			);
			?>
		</div>
	</div>
	<div class="container site-footer__legal">
		<a href="<?php echo esc_url( home_url( '/politica-de-privacidad/' ) ); ?>"><?php esc_html_e( 'Política de Privacidad', 'vacantespty' ); ?></a>
		<a href="<?php echo esc_url( home_url( '/divulgacion-de-afiliados/' ) ); ?>"><?php esc_html_e( 'Divulgación de afiliados', 'vacantespty' ); ?></a>
		<a href="<?php echo esc_url( home_url( '/alertas-de-vacantes/' ) ); ?>"><?php esc_html_e( 'Alertas de vacantes', 'vacantespty' ); ?></a>
		<a href="<?php echo esc_url( home_url( '/reto/' ) ); ?>"><?php esc_html_e( 'Reto diario', 'vacantespty' ); ?></a>
		<p><?php esc_html_e( 'Algunos enlaces del sitio son de afiliado: si compras a través de ellos podemos recibir una comisión, sin costo extra para ti.', 'vacantespty' ); ?></p>
	</div>
	<div class="container site-footer__bottom">
		<span>© <?php echo esc_html( gmdate( 'Y' ) ); ?> <?php bloginfo( 'name' ); ?>. <?php esc_html_e( 'Hecho en Panamá 🇵🇦', 'vacantespty' ); ?></span>
		<span><?php esc_html_e( 'Vacantes PTY nunca te cobrará por aplicar a un empleo.', 'vacantespty' ); ?></span>
	</div>
</footer>

<?php wp_footer(); ?>
</body>
</html>
