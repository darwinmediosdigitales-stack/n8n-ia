<?php
/**
 * "Reto diario" in app mode: minimal chrome so the game fills the screen.
 */
?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
	<meta name="theme-color" content="#0A2463">
	<?php wp_head(); ?>
</head>
<body <?php body_class( 'vpty-app' ); ?>>
<?php wp_body_open(); ?>
<header class="app-bar">
	<a class="brand" href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home">
		<img class="brand__mark brand__mark--img" src="<?php echo esc_url( get_template_directory_uri() . '/assets/img/logo-mark.png' ); ?>" width="40" height="40" alt="">
		<span class="brand__text">vacantes<b>pty</b></span>
	</a>
	<a class="vpty-btn vpty-btn--accent app-bar__cta" href="<?php echo esc_url( get_post_type_archive_link( 'vacante' ) ? get_post_type_archive_link( 'vacante' ) : home_url( '/' ) ); ?>" data-vpty-track="reto_to_jobs" data-vpty-point="app-bar"><?php esc_html_e( 'Ver vacantes', 'vacantespty' ); ?></a>
</header>

<main id="contenido" class="app-main">
	<?php
	while ( have_posts() ) {
		the_post();
		the_content();
	}
	?>
</main>

<footer class="app-foot">
	<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Inicio', 'vacantespty' ); ?></a>
	<a href="<?php echo esc_url( home_url( '/curriculum-profesional/' ) ); ?>"><?php esc_html_e( 'Tu CV profesional', 'vacantespty' ); ?></a>
	<a href="<?php echo esc_url( home_url( '/politica-de-privacidad/' ) ); ?>"><?php esc_html_e( 'Privacidad', 'vacantespty' ); ?></a>
</footer>
<?php wp_footer(); ?>
</body>
</html>
