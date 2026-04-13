<?php
/**
 * Plugin Name: WP Health Check
 * Plugin URI: https://github.com/HectorRiquelme/wp-health-check
 * Description: Diagnostica problemas comunes de WordPress automáticamente con reportes de semáforo y correcciones automáticas.
 * Version: 1.0.0
 * Author: Hector Riquelme
 * Author URI: https://github.com/HectorRiquelme
 * Text Domain: wp-health-check
 * Domain Path: /languages
 * Requires at least: 5.6
 * Requires PHP: 7.4
 * License: GPL v2 or later
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'WPHC_VERSION', '1.0.0' );
define( 'WPHC_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'WPHC_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'WPHC_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

// Cargar traducciones.
add_action( 'init', function () {
    load_plugin_textdomain( 'wp-health-check', false, dirname( WPHC_PLUGIN_BASENAME ) . '/languages' );
} );

// Incluir archivos.
require_once WPHC_PLUGIN_DIR . 'includes/class-health-check-runner.php';
require_once WPHC_PLUGIN_DIR . 'includes/class-health-check-fixer.php';
require_once WPHC_PLUGIN_DIR . 'includes/class-health-check-admin.php';

// Checks individuales.
require_once WPHC_PLUGIN_DIR . 'includes/checks/class-check-jquery.php';
require_once WPHC_PLUGIN_DIR . 'includes/checks/class-check-scripts.php';
require_once WPHC_PLUGIN_DIR . 'includes/checks/class-check-images.php';
require_once WPHC_PLUGIN_DIR . 'includes/checks/class-check-responsive.php';
require_once WPHC_PLUGIN_DIR . 'includes/checks/class-check-security.php';
require_once WPHC_PLUGIN_DIR . 'includes/checks/class-check-updates.php';

// Inicializar plugin.
add_action( 'plugins_loaded', function () {
    WPHC_Health_Check_Admin::get_instance();
} );

// Activación.
register_activation_hook( __FILE__, function () {
    if ( ! get_option( 'wphc_check_history' ) ) {
        update_option( 'wphc_check_history', array() );
    }
} );

// Desactivación.
register_deactivation_hook( __FILE__, function () {
    // No eliminamos datos por si el usuario reactiva.
} );
