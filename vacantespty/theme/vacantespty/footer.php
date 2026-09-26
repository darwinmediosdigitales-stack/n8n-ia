</main>

<footer class="site-footer">
	<div class="container site-footer__grid">
		<div class="site-footer__about">
			<a class="brand brand--light" href="<?php echo esc_url( home_url( '/' ) ); ?>">
				<span class="brand__mark">VP</span>
				<span class="brand__text">vacantes<b>pty</b></span>
			</a>
			<p><?php esc_html_e( 'El portal de empleo de Panamá con salarios visibles, empresas verificadas y aplicación directa por WhatsApp.', 'vacantespty' ); ?></p>
			<div class="social"><?php echo vpty_social_links(); // phpcs:ignore WordPress.Security.EscapeOutput -- escaped in helper. ?></div>
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
	<div class="container site-footer__bottom">
		<span>© <?php echo esc_html( gmdate( 'Y' ) ); ?> <?php bloginfo( 'name' ); ?>. <?php esc_html_e( 'Hecho en Panamá 🇵🇦', 'vacantespty' ); ?></span>
		<span><?php esc_html_e( 'Vacantes PTY nunca te cobrará por aplicar a un empleo.', 'vacantespty' ); ?></span>
	</div>
</footer>

<?php wp_footer(); ?>
</body>
</html>
