<?php
// includes/admin/class-woopop-settings.php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

class Woopop_Settings {
    private static $instance = null;

    /**
     * Private constructor to enforce Singleton pattern.
     */
    private function __construct() {
        add_action( 'admin_menu', [ $this, 'add_admin_menu' ] );
        add_action( 'admin_init', [ $this, 'register_settings' ] );
        add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_admin_assets' ] );

        // Handle creation and updating of mobile link pages
        add_action( 'update_option_woopop_mobile_links', [ $this, 'update_mobile_link_pages' ], 10, 2 );
    }

    /**
     * Get the Singleton instance.
     *
     * @return Woopop_Settings
     */
    public static function get_instance() {
        if ( self::$instance === null ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Add admin menu.
     */
    public function add_admin_menu() {
        add_menu_page(
            __( 'WooPop Settings', 'woopop' ),
            __( 'WooPop', 'woopop' ),
            'manage_options',
            'woopop',
            [ $this, 'woopop_settings_page' ],
            'dashicons-admin-generic',
            20
        );
    }

    /**
     * Render the WooPop settings page.
     */
    public function woopop_settings_page() {
        include_once WOOP_POP_PLUGIN_DIR . 'includes/admin/settings-page.php';
    }

    /**
     * Register settings, sections, and fields.
     */
    public function register_settings() {
        $this->register_links_settings();
        $this->register_style_settings();
        $this->register_bio_settings();
        $this->register_mobile_links_settings();
    }

    /**
     * Enqueue admin-specific scripts and styles.
     *
     * @param string $hook The current admin page.
     */
    public function enqueue_admin_assets( $hook ) {
        if ( $hook !== 'toplevel_page_woopop' ) {
            return;
        }

        wp_enqueue_style( 'woopop-admin-css', WOOP_POP_PLUGIN_URL . 'assets/css/admin.css', [], WOOP_POP_VERSION );
        wp_enqueue_script( 'woopop-admin-js', WOOP_POP_PLUGIN_URL . 'assets/js/admin.js', [ 'jquery', 'jquery-ui-sortable' ], WOOP_POP_VERSION, true );
        wp_enqueue_style( 'font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css', [], '6.4.0' );
        wp_enqueue_media(); // Enqueue media scripts

        wp_localize_script( 'woopop-admin-js', 'woopopData', [
            'ajax_url'             => admin_url( 'admin-ajax.php' ),
            'noLinksText'          => __( 'No links added yet.', 'woopop' ),
            'placeholderImage'     => WOOP_POP_PLUGIN_URL . 'assets/images/placeholder.png',
            'slug_placeholder'     => __( 'custom-slug', 'woopop' ),
            'title_placeholder'    => __( 'Page Title', 'woopop' ),
            'remove_button_text'   => __( 'Remove', 'woopop' ),
            'site_url'             => home_url(),
        ] );
    }

    /**
     * Register Links settings.
     */
    private function register_links_settings() {
        // Register settings for Links
        register_setting( 'woopop_links_group', 'woopop_links', [ $this, 'sanitize_links' ] );

        // Add settings sections for Links
        add_settings_section(
            'woopop_links_section',
            __( 'Manage Your Links', 'woopop' ),
            [ $this, 'links_section_callback' ],
            'woopop_links'
        );

        // Add fields to Links Section
        add_settings_field(
            'woopop_links_field',
            __( 'Links', 'woopop' ),
            [ $this, 'woopop_render_links_field' ],
            'woopop_links',
            'woopop_links_section'
        );
    }

    /**
     * Register Style settings.
     */
    private function register_style_settings() {
        // Register settings for Style
        register_setting( 'woopop_style_group', 'woopop_style_options', [ $this, 'sanitize_style_options' ] );

        // Add settings sections for Style
        add_settings_section(
            'woopop_style_section',
            __( 'Customize Style', 'woopop' ),
            [ $this, 'style_section_callback' ],
            'woopop_style'
        );

        // Add fields to Style Section
        $this->add_style_fields();
    }

    /**
     * Register Bio settings.
     */
    private function register_bio_settings() {
        // Register settings for Bio
        register_setting( 'woopop_bio_group', 'woopop_bio_options', [ $this, 'sanitize_bio_options' ] );

        // Add settings sections for Bio
        add_settings_section(
            'woopop_bio_section',
            __( 'Your Bio', 'woopop' ),
            [ $this, 'bio_section_callback' ],
            'woopop_bio'
        );

        // Add fields to Bio Section
        $this->add_bio_fields();
    }

    /**
     * Register Mobile Links settings.
     */
    private function register_mobile_links_settings() {
        // Register settings for Mobile Links
        register_setting( 'woopop_mobile_links_group', 'woopop_mobile_links', [ $this, 'sanitize_mobile_links' ] );

        // Add settings sections for Mobile Links
        add_settings_section(
            'woopop_mobile_links_section',
            __( 'Mobile Links', 'woopop' ),
            [ $this, 'mobile_links_section_callback' ],
            'woopop_mobile_links'
        );

        // Add fields to Mobile Links Section
        add_settings_field(
            'woopop_mobile_links_field',
            __( 'Mobile Links', 'woopop' ),
            [ $this, 'woopop_render_mobile_links_field' ],
            'woopop_mobile_links',
            'woopop_mobile_links_section'
        );
    }

    /**
     * Add fields to Style Section.
     */
    private function add_style_fields() {
        $fields = [
            'woopop_bg_color'           => __( 'Background Color', 'woopop' ),
            'woopop_text_color'         => __( 'Text Color', 'woopop' ),
            'woopop_button_color'       => __( 'Button Color', 'woopop' ),
            'woopop_button_hover_color' => __( 'Button Hover Color', 'woopop' ),
            'woopop_card_bg_color'      => __( 'Card Background Color', 'woopop' ),
            'woopop_card_text_color'    => __( 'Card Text Color', 'woopop' ),
            'woopop_font_family'        => __( 'Font Family', 'woopop' ),
            'woopop_text_hover_color'   => __( 'Hover Text Color', 'woopop' ),
            'woopop_show_back_to_top'   => __( 'Show Back to Top Button', 'woopop' ),
            'woopop_show_social_icons'  => __( 'Show Social Icons', 'woopop' ),
        ];

        foreach ( $fields as $field_id => $label ) {
            add_settings_field(
                $field_id,
                $label,
                [ $this, "{$field_id}_callback" ],
                'woopop_style',
                'woopop_style_section'
            );
        }
    }

    /**
     * Add fields to Bio Section.
     */
    private function add_bio_fields() {
        $fields = [
            'woopop_bio_image'           => __( 'Profile Photo', 'woopop' ),
            'woopop_profile_name'        => __( 'Profile Name', 'woopop' ),
            'woopop_profile_description' => __( 'Profile Description', 'woopop' ),
            'woopop_social_links'        => __( 'Social Media Handles (Optional)', 'woopop' ),
        ];

        foreach ( $fields as $field_id => $label ) {
            add_settings_field(
                $field_id,
                $label,
                [ $this, "{$field_id}_callback" ],
                'woopop_bio',
                'woopop_bio_section'
            );
        }
    }

    /**
     * Callback for Links Section description.
     */
    public function links_section_callback() {
        echo '<p>' . esc_html__( 'Manage your links with different types and rearrange them as needed.', 'woopop' ) . '</p>';
    }

    /**
     * Callback for Style Section description.
     */
    public function style_section_callback() {
        echo '<p>' . esc_html__( 'Customize the appearance of your WooPop page.', 'woopop' ) . '</p>';
    }

    /**
     * Callback for Bio Section description.
     */
    public function bio_section_callback() {
        echo '<p>' . esc_html__( 'Update your bio details, including profile image and bio text.', 'woopop' ) . '</p>';
    }

    /**
     * Callback for Mobile Links Section description.
     */
    public function mobile_links_section_callback() {
        echo '<p>' . esc_html__( 'Set up custom URLs that display mobile-only pages.', 'woopop' ) . '</p>';
    }

    /**
     * Callback for Profile Photo field.
     */
    public function woopop_bio_image_callback() {
        $options   = get_option( 'woopop_bio_options' );
        $image_id  = isset( $options['woopop_bio_image_id'] ) ? $options['woopop_bio_image_id'] : 0;
        $image_url = $image_id ? wp_get_attachment_image_url( $image_id, 'thumbnail' ) : WOOP_POP_PLUGIN_URL . 'assets/images/placeholder.png';
        ?>
        <div class="woopop-image-preview-wrapper">
            <img id="woopop-bio-image-preview" src="<?php echo esc_url( $image_url ); ?>" style="max-width: 150px; max-height: 150px;">
        </div>
        <input type="hidden" name="woopop_bio_options[woopop_bio_image_id]" id="woopop-bio-image-id" value="<?php echo esc_attr( $image_id ); ?>">
        <button type="button" class="button" id="woopop-bio-image-upload"><?php esc_html_e( 'Select Image', 'woopop' ); ?></button>
        <button type="button" class="button" id="woopop-bio-image-remove"><?php esc_html_e( 'Remove Image', 'woopop' ); ?></button>
        <?php
    }

    /**
     * Callback for Profile Name field.
     */
    public function woopop_profile_name_callback() {
        $options      = get_option( 'woopop_bio_options' );
        $profile_name = isset( $options['woopop_profile_name'] ) ? esc_attr( $options['woopop_profile_name'] ) : '';
        echo '<input type="text" name="woopop_bio_options[woopop_profile_name]" value="' . $profile_name . '" placeholder="' . esc_attr__( 'Your Name', 'woopop' ) . '" class="regular-text">';
    }

    /**
     * Callback for Profile Description field.
     */
    public function woopop_profile_description_callback() {
        $options              = get_option( 'woopop_bio_options' );
        $profile_description  = isset( $options['woopop_profile_description'] ) ? esc_textarea( $options['woopop_profile_description'] ) : '';
        echo '<textarea name="woopop_bio_options[woopop_profile_description]" rows="5" class="regular-text" placeholder="' . esc_attr__( 'Your bio goes here.', 'woopop' ) . '">' . $profile_description . '</textarea>';
    }

    /**
     * Callback for Social Media Handles field.
     */
    public function woopop_social_links_callback() {
        $options       = get_option( 'woopop_bio_options' );
        $social_links  = isset( $options['woopop_social_links'] ) ? $options['woopop_social_links'] : [];
        $social_media  = [
            'facebook'  => 'Facebook',
            'instagram' => 'Instagram',
            'twitter'   => 'Twitter',
            'youtube'   => 'YouTube',
            'snapchat'  => 'Snapchat',
            'tiktok'    => 'TikTok',
            'pinterest' => 'Pinterest',
            'patreon'   => 'Patreon',
            'linkedin'  => 'LinkedIn',
        ];

        echo '<table class="form-table woopop-social-media-table">';
        foreach ( $social_media as $key => $label ) {
            $value = isset( $social_links[ $key ] ) ? esc_url( $social_links[ $key ] ) : '';
            echo '<tr valign="top">
                    <th scope="row">' . esc_html__( $label, 'woopop' ) . '</th>
                    <td>
                        <input type="url" name="woopop_bio_options[woopop_social_links][' . $key . ']" value="' . $value . '" placeholder="' . esc_attr__( 'https://', 'woopop' ) . strtolower( $label ) . '.com/yourprofile" class="regular-text woopop-social-input">
                    </td>
                  </tr>';
        }
        echo '</table>';
    }

    /**
     * Callback for Background Color field.
     */
    public function woopop_bg_color_callback() {
        $options  = get_option( 'woopop_style_options' );
        $bg_color = isset( $options['woopop_bg_color'] ) ? esc_attr( $options['woopop_bg_color'] ) : '#ffffff';
        echo '<input type="color" name="woopop_style_options[woopop_bg_color]" value="' . $bg_color . '" />';
    }

    /**
     * Callback for Text Color field.
     */
    public function woopop_text_color_callback() {
        $options     = get_option( 'woopop_style_options' );
        $text_color  = isset( $options['woopop_text_color'] ) ? esc_attr( $options['woopop_text_color'] ) : '#333333';
        echo '<input type="color" name="woopop_style_options[woopop_text_color]" value="' . $text_color . '" />';
    }

    /**
     * Callback for Button Color field.
     */
    public function woopop_button_color_callback() {
        $options       = get_option( 'woopop_style_options' );
        $button_color  = isset( $options['woopop_button_color'] ) ? esc_attr( $options['woopop_button_color'] ) : '#0073aa';
        echo '<input type="color" name="woopop_style_options[woopop_button_color]" value="' . $button_color . '" />';
    }

    /**
     * Callback for Button Hover Color field.
     */
    public function woopop_button_hover_color_callback() {
        $options             = get_option( 'woopop_style_options' );
        $button_hover_color  = isset( $options['woopop_button_hover_color'] ) ? esc_attr( $options['woopop_button_hover_color'] ) : '#005a87';
        echo '<input type="color" name="woopop_style_options[woopop_button_hover_color]" value="' . $button_hover_color . '" />';
    }

    /**
     * Callback for Card Background Color field.
     */
    public function woopop_card_bg_color_callback() {
        $options        = get_option( 'woopop_style_options' );
        $card_bg_color  = isset( $options['woopop_card_bg_color'] ) ? esc_attr( $options['woopop_card_bg_color'] ) : '#f9f9f9';
        echo '<input type="color" name="woopop_style_options[woopop_card_bg_color]" value="' . $card_bg_color . '" />';
    }

    /**
     * Callback for Card Text Color field.
     */
    public function woopop_card_text_color_callback() {
        $options         = get_option( 'woopop_style_options' );
        $card_text_color = isset( $options['woopop_card_text_color'] ) ? esc_attr( $options['woopop_card_text_color'] ) : '#333333';
        echo '<input type="color" name="woopop_style_options[woopop_card_text_color]" value="' . $card_text_color . '" />';
    }

    /**
     * Callback for Font Family field.
     */
    public function woopop_font_family_callback() {
        $options     = get_option( 'woopop_style_options' );
        $font_family = isset( $options['woopop_font_family'] ) ? esc_attr( $options['woopop_font_family'] ) : 'Arial, sans-serif';
        $fonts       = [
            'Arial, sans-serif'                => 'Arial',
            'Helvetica, sans-serif'            => 'Helvetica',
            '"Times New Roman", serif'         => 'Times New Roman',
            'Georgia, serif'                   => 'Georgia',
            '"Courier New", monospace'         => 'Courier New',
            'Verdana, sans-serif'              => 'Verdana',
            'Tahoma, sans-serif'               => 'Tahoma',
            '"Trebuchet MS", sans-serif'       => 'Trebuchet MS',
            '"Lucida Sans Unicode", sans-serif' => 'Lucida Sans Unicode',
        ];
        echo '<select name="woopop_style_options[woopop_font_family]">';
        foreach ( $fonts as $font_value => $font_name ) {
            echo '<option value="' . esc_attr( $font_value ) . '" ' . selected( $font_family, $font_value, false ) . '>' . esc_html( $font_name ) . '</option>';
        }
        echo '</select>';
    }

    /**
     * Callback for Hover Text Color field.
     */
    public function woopop_text_hover_color_callback() {
        $options          = get_option( 'woopop_style_options' );
        $text_hover_color = isset( $options['woopop_text_hover_color'] ) ? esc_attr( $options['woopop_text_hover_color'] ) : '#ffffff';
        echo '<input type="color" name="woopop_style_options[woopop_text_hover_color]" value="' . $text_hover_color . '" />';
    }

    /**
     * Callback for Show Back to Top Button field.
     */
    public function woopop_show_back_to_top_callback() {
        $options          = get_option( 'woopop_style_options' );
        $show_back_to_top = isset( $options['woopop_show_back_to_top'] ) ? $options['woopop_show_back_to_top'] : 1;
        echo '<input type="checkbox" name="woopop_style_options[woopop_show_back_to_top]" value="1"' . checked( 1, $show_back_to_top, false ) . ' />';
        echo ' ' . __( 'Enable', 'woopop' );
    }

    /**
     * Callback for Show Social Icons field.
     */
    public function woopop_show_social_icons_callback() {
        $options           = get_option( 'woopop_style_options' );
        $show_social_icons = isset( $options['woopop_show_social_icons'] ) ? $options['woopop_show_social_icons'] : 1;
        echo '<input type="checkbox" name="woopop_style_options[woopop_show_social_icons]" value="1"' . checked( 1, $show_social_icons, false ) . ' />';
        echo ' ' . __( 'Enable', 'woopop' );
    }

    /**
     * Callback for Links Repeater Field.
     */
    public function woopop_render_links_field() {
        $links = get_option( 'woopop_links', [] );

        ?>
        <div class="woopop-repeater">
            <div class="woopop-repeater-items">
                <?php
                if ( ! empty( $links ) ) {
                    foreach ( $links as $link ) {
                        $this->woopop_render_repeater_item( $link );
                    }
                }
                ?>
            </div>
            <!-- Hidden Template -->
            <div class="woopop-repeater-item-template" style="display:none;">
                <?php $this->woopop_render_repeater_item(); ?>
            </div>
            <button type="button" class="button woopop-add-link"><?php esc_html_e( 'Add Link', 'woopop' ); ?></button>
        </div>
        <?php
    }

    /**
     * Callback for Mobile Links Repeater Field.
     */
    public function woopop_render_mobile_links_field() {
        $mobile_links = get_option( 'woopop_mobile_links', [] );
        $site_url     = home_url();
        ?>
        <div class="woopop-mobile-links-repeater">
            <table class="woopop-mobile-links-table">
                <thead>
                    <tr>
                        <th><?php esc_html_e( 'Generated URL', 'woopop' ); ?></th>
                        <th><?php esc_html_e( 'Page Title', 'woopop' ); ?></th>
                        <th><?php esc_html_e( 'Actions', 'woopop' ); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ( ! empty( $mobile_links ) ) : ?>
                        <?php foreach ( $mobile_links as $index => $link ) : ?>
                            <tr>
                                <td>
                                    <div class="woopop-url-input">
                                        <span class="woopop-base-url"><?php echo esc_html( $site_url ); ?>/</span>
                                        <input type="text" name="woopop_mobile_links[<?php echo esc_attr( $index ); ?>][slug]" value="<?php echo esc_attr( $link['slug'] ); ?>" placeholder="<?php esc_attr_e( 'custom-slug', 'woopop' ); ?>" class="regular-text slug-input" required>
                                    </div>
                                    <div class="woopop-generated-url">
                                        <?php echo esc_html( $site_url ); ?>/<span class="generated-slug"><?php echo esc_html( $link['slug'] ); ?></span>
                                    </div>
                                </td>
                                <td>
                                    <input type="text" name="woopop_mobile_links[<?php echo esc_attr( $index ); ?>][title]" value="<?php echo esc_attr( $link['title'] ); ?>" placeholder="<?php esc_attr_e( 'Page Title', 'woopop' ); ?>" class="regular-text" required>
                                </td>
                                <td>
                                    <button type="button" class="button woopop-remove-mobile-link"><?php esc_html_e( 'Remove', 'woopop' ); ?></button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else : ?>
                        <tr>
                            <td colspan="3"><?php esc_html_e( 'No mobile links added yet.', 'woopop' ); ?></td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
            <button type="button" class="button woopop-add-mobile-link"><?php esc_html_e( 'Add Mobile Link', 'woopop' ); ?></button>
        </div>
        <?php
    }

    /**
     * Render individual repeater item.
     *
     * @param array $link The link data.
     */
    private function woopop_render_repeater_item( $link = [] ) {
        $name        = isset( $link['name'] ) ? esc_attr( $link['name'] ) : '';
        $type        = isset( $link['type'] ) ? esc_attr( $link['type'] ) : 'custom';
        $url         = isset( $link['url'] ) ? esc_url( $link['url'] ) : '';
        $product_id  = isset( $link['product_id'] ) ? intval( $link['product_id'] ) : '';
        $media_url   = isset( $link['media_url'] ) ? esc_url( $link['media_url'] ) : '';
        $form_value  = isset( $link['form'] ) ? esc_attr( $link['form'] ) : '';
        $video_url   = isset( $link['video_url'] ) ? esc_url( $link['video_url'] ) : '';
        $video_file  = isset( $link['video_file'] ) ? esc_url( $link['video_file'] ) : '';
        ?>
        <div class="woopop-repeater-item">
            <span class="dashicons dashicons-move woopop-repeater-handle"></span>
            <select name="woopop_links[woopop_links_type][]" class="woopop-link-type">
                <option value="custom" <?php selected( $type, 'custom' ); ?>><?php esc_html_e( 'Custom URL', 'woopop' ); ?></option>
                <option value="product" <?php selected( $type, 'product' ); ?>><?php esc_html_e( 'Product', 'woopop' ); ?></option>
                <option value="media" <?php selected( $type, 'media' ); ?>><?php esc_html_e( 'Media', 'woopop' ); ?></option>
                <option value="form" <?php selected( $type, 'form' ); ?>><?php esc_html_e( 'Form', 'woopop' ); ?></option>
                <option value="video" <?php selected( $type, 'video' ); ?>><?php esc_html_e( 'Video', 'woopop' ); ?></option>
            </select>
            <?php if ( $type !== 'product' ) : ?>
                <input type="text" name="woopop_links[woopop_links_name][]" value="<?php echo $name; ?>" placeholder="<?php esc_attr_e( 'Link Name', 'woopop' ); ?>" class="regular-text">
            <?php else : ?>
                <input type="hidden" name="woopop_links[woopop_links_name][]" value="">
            <?php endif; ?>
            <!-- Custom URL Field -->
            <div class="woopop-link-field custom" style="<?php echo in_array( $type, [ 'custom' ] ) ? '' : 'display:none;'; ?>">
                <input type="url" name="woopop_links[woopop_links_url][]" value="<?php echo $url; ?>" placeholder="<?php esc_attr_e( 'https://example.com', 'woopop' ); ?>" class="regular-text">
            </div>
            <!-- Product Selection Field -->
            <div class="woopop-link-field product" style="<?php echo ( $type === 'product' ) ? '' : 'display:none;'; ?>">
                <select name="woopop_links[woopop_links_product][]" class="regular-text">
                    <option value=""><?php esc_html_e( 'Select a Product', 'woopop' ); ?></option>
                    <?php
                    if ( class_exists( 'WooCommerce' ) ) {
                        $products = wc_get_products( [ 'limit' => -1 ] );
                        foreach ( $products as $product ) {
                            $selected            = ( $product_id == $product->get_id() ) ? 'selected' : '';
                            $product_image       = wp_get_attachment_image_url( $product->get_image_id(), 'medium' );
                            $product_description = wp_strip_all_tags( $product->get_short_description() );
                            echo '<option value="' . esc_attr( $product->get_id() ) . '" ' . $selected . ' data-url="' . esc_url( get_permalink( $product->get_id() ) ) . '" data-image="' . esc_url( $product_image ) . '" data-description="' . esc_attr( $product_description ) . '">' . esc_html( $product->get_name() ) . '</option>';
                        }
                    } else {
                        echo '<option value="">' . esc_html__( 'WooCommerce is not active.', 'woopop' ) . '</option>';
                    }
                    ?>
                </select>
            </div>
            <!-- Media Selection Field -->
            <div class="woopop-link-field media" style="<?php echo ( $type === 'media' ) ? '' : 'display:none;'; ?>">
                <select name="woopop_links[woopop_links_media][]" class="regular-text">
                    <option value=""><?php esc_html_e( 'Select Media', 'woopop' ); ?></option>
                    <?php
                    $media_items = get_posts( [
                        'post_type'      => 'attachment',
                        'post_status'    => 'inherit',
                        'posts_per_page' => -1,
                        'post_mime_type' => 'image',
                    ] );
                    foreach ( $media_items as $file ) {
                        $file_url = wp_get_attachment_url( $file->ID );
                        $selected = ( $media_url == $file_url ) ? 'selected' : '';
                        echo '<option value="' . esc_url( $file_url ) . '" ' . $selected . '>' . esc_html( $file->post_title ) . '</option>';
                    }
                    ?>
                </select>
            </div>
            <!-- Form Selection Field -->
            <div class="woopop-link-field form" style="<?php echo ( $type === 'form' ) ? '' : 'display:none;'; ?>">
                <label><?php esc_html_e( 'Select Form:', 'woopop' ); ?></label>
                <select name="woopop_links[woopop_links_form][]">
                    <?php
                    // Retrieve forms from supported form plugins
                    $forms = [];

                    // Contact Form 7
                    if ( class_exists( 'WPCF7' ) ) {
                        $cf7_forms = get_posts( [ 'post_type' => 'wpcf7_contact_form', 'numberposts' => -1 ] );
                        foreach ( $cf7_forms as $form ) {
                            $forms[] = [
                                'id'    => $form->ID,
                                'title' => $form->post_title,
                                'type'  => 'cf7'
                            ];
                        }
                    }

                    // Gravity Forms
                    if ( class_exists( 'GFAPI' ) ) {
                        $gf_forms = GFAPI::get_forms();
                        foreach ( $gf_forms as $form ) {
                            $forms[] = [
                                'id'    => $form['id'],
                                'title' => $form['title'],
                                'type'  => 'gf'
                            ];
                        }
                    }

                    // WPForms
                    if ( class_exists( 'WPForms' ) ) {
                        $wpforms_forms = wpforms()->form->get();
                        foreach ( $wpforms_forms as $form ) {
                            $forms[] = [
                                'id'    => $form->ID,
                                'title' => $form->post_title,
                                'type'  => 'wpforms'
                            ];
                        }
                    }

                    // Output form options
                    if ( ! empty( $forms ) ) {
                        foreach ( $forms as $form ) {
                            $value    = $form['type'] . ':' . $form['id'];
                            $selected = ( $form_value == $value ) ? 'selected' : '';
                            echo '<option value="' . esc_attr( $value ) . '" ' . $selected . '>' . esc_html( $form['title'] ) . ' (' . esc_html( strtoupper( $form['type'] ) ) . ')</option>';
                        }
                    } else {
                        echo '<option value="">' . esc_html__( 'No forms available.', 'woopop' ) . '</option>';
                    }
                    ?>
                </select>
            </div>
            <!-- Video Fields -->
            <div class="woopop-link-field video" style="<?php echo ( $type === 'video' ) ? '' : 'display:none;'; ?>">
                <label><?php esc_html_e( 'Video URL:', 'woopop' ); ?></label>
                <input type="text" name="woopop_links[woopop_links_video_url][]" value="<?php echo $video_url; ?>" placeholder="<?php esc_attr_e( 'https://www.youtube.com/watch?v=...', 'woopop' ); ?>" class="regular-text">
                <p class="description"><?php esc_html_e( 'Enter a video URL from YouTube, Vimeo, etc.', 'woopop' ); ?></p>
                <label><?php esc_html_e( 'Or Upload Video:', 'woopop' ); ?></label>
                <input type="text" name="woopop_links[woopop_links_video_file][]" value="<?php echo $video_file; ?>" class="woopop-video-file regular-text">
                <button type="button" class="button woopop-video-upload-button"><?php esc_html_e( 'Upload Video', 'woopop' ); ?></button>
            </div>
            <button type="button" class="button woopop-remove-link"><?php esc_html_e( 'Remove', 'woopop' ); ?></button>
        </div>
        <?php
    }

    /**
     * Sanitize links input.
     *
     * @param array $input
     * @return array
     */
    public function sanitize_links( $input ) {
        $sanitized = [];

        if ( ! empty( $input ) && is_array( $input ) ) {
            $types        = isset( $input['woopop_links_type'] ) ? $input['woopop_links_type'] : [];
            $names        = isset( $input['woopop_links_name'] ) ? $input['woopop_links_name'] : [];
            $urls         = isset( $input['woopop_links_url'] ) ? $input['woopop_links_url'] : [];
            $products     = isset( $input['woopop_links_product'] ) ? $input['woopop_links_product'] : [];
            $media        = isset( $input['woopop_links_media'] ) ? $input['woopop_links_media'] : [];
            $forms        = isset( $input['woopop_links_form'] ) ? $input['woopop_links_form'] : [];
            $video_urls   = isset( $input['woopop_links_video_url'] ) ? $input['woopop_links_video_url'] : [];
            $video_files  = isset( $input['woopop_links_video_file'] ) ? $input['woopop_links_video_file'] : [];

            $count = count( $types );

            for ( $i = 0; $i < $count; $i++ ) {
                $type        = sanitize_text_field( $types[ $i ] );
                $name        = isset( $names[ $i ] ) ? sanitize_text_field( $names[ $i ] ) : '';
                $url         = isset( $urls[ $i ] ) ? esc_url_raw( $urls[ $i ] ) : '';
                $product_id  = isset( $products[ $i ] ) ? intval( $products[ $i ] ) : '';
                $media_url   = isset( $media[ $i ] ) ? esc_url_raw( $media[ $i ] ) : '';
                $form_value  = isset( $forms[ $i ] ) ? sanitize_text_field( $forms[ $i ] ) : '';
                $video_url   = isset( $video_urls[ $i ] ) ? esc_url_raw( $video_urls[ $i ] ) : '';
                $video_file  = isset( $video_files[ $i ] ) ? esc_url_raw( $video_files[ $i ] ) : '';

                $sanitized_item = [
                    'type' => $type,
                    'name' => $name,
                ];

                if ( $type === 'product' ) {
                    $sanitized_item['product_id'] = $product_id;
                } elseif ( $type === 'media' ) {
                    $sanitized_item['media_url'] = $media_url;
                } elseif ( $type === 'form' ) {
                    $sanitized_item['form'] = $form_value;
                } elseif ( $type === 'video' ) {
                    $sanitized_item['video_url']  = $video_url;
                    $sanitized_item['video_file'] = $video_file;
                } else {
                    $sanitized_item['url'] = $url;
                }

                $sanitized[] = $sanitized_item;
            }
        }

        return $sanitized;
    }

    /**
     * Sanitize style options input.
     *
     * @param array $input
     * @return array
     */
    public function sanitize_style_options( $input ) {
        $sanitized = [];

        if ( is_array( $input ) ) {
            $sanitized['woopop_bg_color']            = sanitize_hex_color( $input['woopop_bg_color'] ?? '#ffffff' );
            $sanitized['woopop_text_color']          = sanitize_hex_color( $input['woopop_text_color'] ?? '#333333' );
            $sanitized['woopop_button_color']        = sanitize_hex_color( $input['woopop_button_color'] ?? '#0073aa' );
            $sanitized['woopop_button_hover_color']  = sanitize_hex_color( $input['woopop_button_hover_color'] ?? '#005a87' );
            $sanitized['woopop_card_bg_color']       = sanitize_hex_color( $input['woopop_card_bg_color'] ?? '#f9f9f9' );
            $sanitized['woopop_card_text_color']     = sanitize_hex_color( $input['woopop_card_text_color'] ?? '#333333' );
            $sanitized['woopop_font_family']         = sanitize_text_field( $input['woopop_font_family'] ?? 'Arial, sans-serif' );
            $sanitized['woopop_text_hover_color']    = sanitize_hex_color( $input['woopop_text_hover_color'] ?? '#ffffff' );
            $sanitized['woopop_show_back_to_top']    = isset( $input['woopop_show_back_to_top'] ) ? 1 : 0;
            $sanitized['woopop_show_social_icons']   = isset( $input['woopop_show_social_icons'] ) ? 1 : 0;
        }

        return $sanitized;
    }

    /**
     * Sanitize bio options input.
     *
     * @param array $input
     * @return array
     */
    public function sanitize_bio_options( $input ) {
        $sanitized = [];

        if ( is_array( $input ) ) {
            $sanitized['woopop_bio_image_id']        = intval( $input['woopop_bio_image_id'] ?? 0 );
            $sanitized['woopop_profile_name']        = sanitize_text_field( $input['woopop_profile_name'] ?? '' );
            $sanitized['woopop_profile_description'] = sanitize_textarea_field( $input['woopop_profile_description'] ?? '' );

            if ( isset( $input['woopop_social_links'] ) && is_array( $input['woopop_social_links'] ) ) {
                foreach ( $input['woopop_social_links'] as $key => $url ) {
                    $sanitized['woopop_social_links'][ $key ] = esc_url_raw( $url );
                }
            }
        }

        return $sanitized;
    }

    /**
     * Sanitize mobile links input.
     *
     * @param array $input
     * @return array
     */
    public function sanitize_mobile_links( $input ) {
        $sanitized      = [];
        $existing_slugs = [];

        if ( is_array( $input ) ) {
            foreach ( $input as $index => $link ) {
                if ( isset( $link['slug'], $link['title'] ) ) {
                    $slug  = sanitize_title( $link['slug'] );
                    $title = sanitize_text_field( $link['title'] );

                    // Check for duplicates and conflicts as before

                    $sanitized[ $index ] = [
                        'slug'  => $slug,
                        'title' => $title,
                    ];
                }
            }
        }

        return $sanitized;
    }

    /**
     * Check if the slug conflicts with existing pages, posts, or custom post types.
     *
     * @param string $slug
     * @return bool
     */
    private function is_slug_conflicting( $slug ) {
        // Check if a page with the same slug exists
        if ( get_page_by_path( $slug ) ) {
            return true;
        }

        // Check if a post with the same slug exists
        if ( get_post( get_page_by_path( $slug, OBJECT, 'post' ) ) ) {
            return true;
        }

        // Check for custom post types if any
        $custom_post_types = get_post_types( [ '_builtin' => false ], 'names' );
        foreach ( $custom_post_types as $post_type ) {
            if ( get_page_by_path( $slug, OBJECT, $post_type ) ) {
                return true;
            }
        }

        return false;
    }

    /**
     * Handle creation and updating of mobile link pages.
     *
     * @param array $old_value The old value of the option.
     * @param array $new_value The new value of the option.
     */
    public function update_mobile_link_pages( $old_value, $new_value ) {
        // Get existing pages with the meta key '_woopop_mobile_link'
        $existing_pages = get_posts( [
            'post_type'   => 'page',
            'post_status' => 'publish',
            'meta_key'    => '_woopop_mobile_link',
            'meta_value'  => '1',
            'numberposts' => -1,
        ] );

        // Map existing slugs to page IDs
        $existing_pages_slugs = [];
        foreach ( $existing_pages as $page ) {
            $existing_pages_slugs[ $page->post_name ] = $page->ID;
        }

        // Collect new slugs
        $new_slugs = [];
        foreach ( $new_value as $link ) {
            $new_slugs[] = sanitize_title( $link['slug'] );
        }

        // Delete pages that are no longer needed
        foreach ( $existing_pages_slugs as $slug => $page_id ) {
            if ( ! in_array( $slug, $new_slugs ) ) {
                wp_delete_post( $page_id, true ); // Permanently delete
            }
        }

        // Create or update pages
        foreach ( $new_value as $link ) {
            $slug    = sanitize_title( $link['slug'] );
            $title   = sanitize_text_field( $link['title'] );
            $content = '[woopop]';

            $page_args = [
                'post_title'     => $title,
                'post_content'   => $content,
                'post_status'    => 'publish',
                'post_type'      => 'page',
                'post_name'      => $slug,
                'meta_input'     => [
                    '_woopop_mobile_link' => '1',
                    '_wp_page_template'   => 'page-woopop-template.php', // Assign our custom template if needed
                ],
            ];

            if ( isset( $existing_pages_slugs[ $slug ] ) ) {
                // Update existing page
                $page_args['ID'] = $existing_pages_slugs[ $slug ];
                wp_update_post( $page_args );
            } else {
                // Create new page
                wp_insert_post( $page_args );
            }
        }

        // Flush rewrite rules to ensure new pages are accessible
        flush_rewrite_rules();
    }
}

// Initialize the Woopop_Settings class
Woopop_Settings::get_instance();
