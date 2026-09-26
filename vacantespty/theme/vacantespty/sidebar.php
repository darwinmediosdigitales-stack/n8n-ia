<aside class="layout__side">
	<?php vpty_theme_banner( 'sidebar' ); ?>
	<?php if ( is_active_sidebar( 'blog' ) ) : ?>
		<?php dynamic_sidebar( 'blog' ); ?>
	<?php elseif ( vpty_core_active() ) : ?>
		<div class="side-box side-box--cta">
			<h3><?php esc_html_e( '¿Buscas empleo?', 'vacantespty' ); ?></h3>
			<p><?php esc_html_e( 'Mira las vacantes publicadas hoy en todo Panamá.', 'vacantespty' ); ?></p>
			<a class="vpty-btn vpty-btn--accent vpty-btn--block" href="<?php echo esc_url( get_post_type_archive_link( 'vacante' ) ); ?>"><?php esc_html_e( 'Ver vacantes', 'vacantespty' ); ?></a>
		</div>
	<?php endif; ?>
</aside>
