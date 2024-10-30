<?php
// includes/admin/settings-page.php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

/**
 * Render the WooPop settings page with tabs and mobile mockup.
 */

// Fetch saved options
$bio_options = get_option( 'woopop_bio_options', [] );
$profile_name = isset( $bio_options['woopop_profile_name'] ) ? esc_attr( $bio_options['woopop_profile_name'] ) : 'Your Name';
$profile_description = isset( $bio_options['woopop_profile_description'] ) ? esc_textarea( $bio_options['woopop_profile_description'] ) : 'Your bio goes here.';
$bio_image_id = isset( $bio_options['woopop_bio_image_id'] ) ? $bio_options['woopop_bio_image_id'] : 0;
$bio_image = $bio_image_id ? wp_get_attachment_image_url( $bio_image_id, 'thumbnail' ) : WOOP_POP_PLUGIN_URL . 'assets/images/placeholder.png';

$links = get_option( 'woopop_links', [] );

$style_options = get_option( 'woopop_style_options', [] );
$bg_color = isset( $style_options['woopop_bg_color'] ) ? esc_attr( $style_options['woopop_bg_color'] ) : '#ffffff';
$text_color = isset( $style_options['woopop_text_color'] ) ? esc_attr( $style_options['woopop_text_color'] ) : '#333333';
$button_color = isset( $style_options['woopop_button_color'] ) ? esc_attr( $style_options['woopop_button_color'] ) : '#0073aa';
$button_hover_color = isset( $style_options['woopop_button_hover_color'] ) ? esc_attr( $style_options['woopop_button_hover_color'] ) : '#005a87';

$mobile_links = get_option( 'woopop_mobile_links', [] );
?>
<div class="wrap woopop-settings-page" id="woopop-settings-page">
    <h1><?php esc_html_e( 'WooPop Settings', 'woopop' ); ?></h1>

    <div class="woopop-settings-container">
        <!-- Mobile Preview Mockup -->
        <div class="woopop-mobile-preview">
            <div class="woopop-mobile-frame">
                <div class="woopop-mobile-screen" style="background-color: <?php echo $bg_color; ?>; color: <?php echo $text_color; ?>; overflow-y: auto;">
                    <img src="<?php echo esc_url( $bio_image ); ?>" alt="<?php esc_attr_e( 'Mobile Preview', 'woopop' ); ?>" id="woopop-preview-image" class="woopop-profile-image">
                    <h3 id="woopop-preview-title"><?php echo $profile_name; ?></h3>
                    <p id="woopop-preview-description"><?php echo $profile_description; ?></p>
                    <div id="woopop-preview-links" class="woopop-preview-links">
                        <!-- Links will be dynamically inserted here -->
                    </div>
                </div>
            </div>
        </div>

        <!-- Tab Navigation and Forms -->
        <div class="woopop-settings-form">
            <!-- Tab Navigation -->
            <h2 class="nav-tab-wrapper woopop-tab-navigation">
                <a href="#links-tab" class="nav-tab nav-tab-active"><?php esc_html_e( 'Links', 'woopop' ); ?></a>
                <a href="#style-tab" class="nav-tab"><?php esc_html_e( 'Style', 'woopop' ); ?></a>
                <a href="#bio-tab" class="nav-tab"><?php esc_html_e( 'Bio', 'woopop' ); ?></a>
                <a href="#mobile-links-tab" class="nav-tab"><?php esc_html_e( 'Mobile Links', 'woopop' ); ?></a>
            </h2>

            <!-- Links Tab Content -->
            <div class="woopop-tab-content active" id="links-tab">
                <form method="post" action="options.php">
                    <?php
                    settings_fields( 'woopop_links_group' );
                    do_settings_sections( 'woopop_links' );
                    submit_button();
                    ?>
                </form>
            </div>

            <!-- Style Tab Content -->
            <div class="woopop-tab-content" id="style-tab" style="display:none;">
                <form method="post" action="options.php">
                    <?php
                    settings_fields( 'woopop_style_group' );
                    do_settings_sections( 'woopop_style' );
                    submit_button();
                    ?>
                </form>
            </div>

            <!-- Bio Tab Content -->
            <div class="woopop-tab-content" id="bio-tab" style="display:none;">
                <form method="post" action="options.php">
                    <?php
                    settings_fields( 'woopop_bio_group' );
                    do_settings_sections( 'woopop_bio' );
                    submit_button();
                    ?>
                </form>
            </div>

            <!-- Mobile Links Tab Content -->
            <div class="woopop-tab-content" id="mobile-links-tab" style="display:none;">
                <form method="post" action="options.php">
                    <?php
                    settings_fields( 'woopop_mobile_links_group' );
                    do_settings_sections( 'woopop_mobile_links' );
                    submit_button();
                    ?>
                </form>
            </div>
        </div>
    </div>
</div>
