<!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="theme-color" content="#0A2463">
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<a class="skip-link screen-reader-text" href="#contenido"><?php esc_html_e( 'Saltar al contenido', 'vacantespty' ); ?></a>

<header class="site-header">
	<div class="container site-header__inner">
		<a class="brand" href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home">
			<?php if ( has_custom_logo() ) : ?>
				<?php echo wp_get_attachment_image( get_theme_mod( 'custom_logo' ), 'full', false, array( 'class' => 'brand__logo', 'alt' => get_bloginfo( 'name' ) ) ); ?>
			<?php else : ?>
				<img class="brand__mark brand__mark--img" src="<?php echo esc_url( get_template_directory_uri() . '/assets/img/logo-mark.png' ); ?>" width="44" height="44" alt="">
				<span class="brand__text">vacantes<b>pty</b></span>
			<?php endif; ?>
		</a>

		<button class="nav-toggle" aria-expanded="false" aria-controls="site-nav">
			<span></span><span></span><span></span>
			<span class="screen-reader-text"><?php esc_html_e( 'Menú', 'vacantespty' ); ?></span>
		</button>

		<nav id="site-nav" class="site-nav" aria-label="<?php esc_attr_e( 'Principal', 'vacantespty' ); ?>">
			<?php
			wp_nav_menu(
				array(
					'theme_location' => 'principal',
					'container'      => false,
					'fallback_cb'    => 'vpty_default_menu',
					'depth'          => 2,
				)
			);
			?>
			<a class="vpty-btn vpty-btn--accent site-nav__cta" href="<?php echo esc_url( home_url( '/publicar-vacante/' ) ); ?>"><?php esc_html_e( 'Publicar vacante', 'vacantespty' ); ?></a>
		</nav>
	</div>
</header>

<main id="contenido" class="site-main">
