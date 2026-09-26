<?php
get_header();
?>
<section class="page-hero">
	<div class="container container--narrow">
		<h1><?php esc_html_e( 'Esta página no existe', 'vacantespty' ); ?></h1>
		<p><?php esc_html_e( 'Puede que la vacante haya cerrado. Busca otras oportunidades:', 'vacantespty' ); ?></p>
		<?php
		if ( vpty_core_active() ) {
			echo vpty_search_form(); // phpcs:ignore WordPress.Security.EscapeOutput
		}
		?>
	</div>
</section>
<?php
get_footer();
