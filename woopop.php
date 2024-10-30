<?php
/**
 * Plugin Name: WooPop
 * Description: A powerful plugin to manage and display your links with customizable settings.
 * Version: 1.0.0
 * Author: Your Name
 * Text Domain: woopop
 * Domain Path: /languages
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Define plugin constants.
define( 'WOOP_POP_VERSION', '1.0.0' );
define( 'WOOP_POP_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'WOOP_POP_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

// Include the Template Loader class.
include_once WOOP_POP_PLUGIN_DIR . 'includes/class-woopop-template-loader.php';

// Include common functions.
if ( file_exists( WOOP_POP_PLUGIN_DIR . 'includes/common/functions.php' ) ) {
    require_once WOOP_POP_PLUGIN_DIR . 'includes/common/functions.php';
}

// Initialize the plugin.
function woopop_init() {
    // Load text domain for translations.
    load_plugin_textdomain( 'woopop', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );

    // Include admin-specific functionality and initialize if in admin.
    if ( is_admin() ) {
        if ( file_exists( WOOP_POP_PLUGIN_DIR . 'includes/admin/class-woopop-settings.php' ) ) {
            require_once WOOP_POP_PLUGIN_DIR . 'includes/admin/class-woopop-settings.php';
            Woopop_Settings::get_instance();
        }
    }

    // Include public-facing functionality and initialize.
    if ( file_exists( WOOP_POP_PLUGIN_DIR . 'includes/public/class-woopop-public.php' ) ) {
        require_once WOOP_POP_PLUGIN_DIR . 'includes/public/class-woopop-public.php';
        Woopop_Public::get_instance();
    }
}
add_action( 'plugins_loaded', 'woopop_init' );
