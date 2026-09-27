<?php
/**
 * Plugin Name:       Vacantes PTY Core
 * Description:       Vacantes, categorías, provincias, banners de marcas, calculadora de salario, alertas de empleo (leads), planes de currículum con Yappy, sección Capacítate y datos estructurados para Google for Jobs.
 * Version:           1.1.0
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * Author:            Vacantes PTY
 * Text Domain:       vacantespty
 * License:           GPL-2.0-or-later
 */

defined( 'ABSPATH' ) || exit;

define( 'VPTY_VERSION', '1.1.0' );
define( 'VPTY_FILE', __FILE__ );
define( 'VPTY_DIR', plugin_dir_path( __FILE__ ) );
define( 'VPTY_URL', plugin_dir_url( __FILE__ ) );

require_once VPTY_DIR . 'includes/helpers.php';
require_once VPTY_DIR . 'includes/post-types.php';
require_once VPTY_DIR . 'includes/meta.php';
require_once VPTY_DIR . 'includes/query.php';
require_once VPTY_DIR . 'includes/schema.php';
require_once VPTY_DIR . 'includes/banners.php';
require_once VPTY_DIR . 'includes/shortcodes.php';
require_once VPTY_DIR . 'includes/install.php';
require_once VPTY_DIR . 'includes/settings.php';
require_once VPTY_DIR . 'includes/growth.php';
require_once VPTY_DIR . 'includes/leads.php';
require_once VPTY_DIR . 'includes/cv-plans.php';
require_once VPTY_DIR . 'includes/recursos.php';
require_once VPTY_DIR . 'includes/upgrade.php';

register_activation_hook( __FILE__, 'vpty_activate' );
register_deactivation_hook( __FILE__, 'flush_rewrite_rules' );
