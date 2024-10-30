<?php
// includes/class-woopop-template-loader.php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

class Woopop_Template_Loader {
    private static $instance = null;

    private function __construct() {
        // Hook into template loading.
        add_filter( 'template_include', [ $this, 'load_custom_template' ] );
        add_filter( 'theme_page_templates', [ $this, 'add_custom_template_to_dropdown' ] );
    }

    public static function get_instance() {
        if ( self::$instance === null ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function add_custom_template_to_dropdown( $templates ) {
        $templates['page-woopop-template.php'] = __( 'WooPop Template', 'woopop' );
        return $templates;
    }

    public function load_custom_template( $template ) {
        if ( is_page() ) {
            global $post;

            // Check if the page uses our custom template.
            $template_slug = get_page_template_slug( $post->ID );
            if ( $template_slug === 'page-woopop-template.php' ) {
                $custom_template = WOOP_POP_PLUGIN_DIR . 'templates/page-woopop-template.php';
                if ( file_exists( $custom_template ) ) {
                    return $custom_template;
                }
            }
        }
        return $template;
    }
}

// Initialize the Woopop_Template_Loader class.
Woopop_Template_Loader::get_instance();
