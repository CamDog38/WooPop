<?php
// includes/public/class-woopop-public.php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

class Woopop_Public {
    private static $instance = null;

    /**
     * Private constructor to enforce Singleton pattern.
     */
    private function __construct() {
        // Conditionally enqueue public scripts and styles.
        add_action( 'wp_enqueue_scripts', [ $this, 'maybe_enqueue_public_assets' ] );

        // Register shortcode.
        add_shortcode( 'woopop', [ $this, 'render_woopop_shortcode' ] );

        // Back to top script
        add_action( 'wp_footer', [ $this, 'add_back_to_top_script' ] );
    }

    /**
     * Get the singleton instance.
     *
     * @return Woopop_Public
     */
    public static function get_instance() {
        if ( self::$instance === null ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Conditionally enqueue public-facing assets only when shortcode is present.
     */
    public function maybe_enqueue_public_assets() {
        global $post;
        if ( is_a( $post, 'WP_Post' ) && has_shortcode( $post->post_content, 'woopop' ) ) {
            $this->enqueue_public_assets();
        }
    }

    /**
     * Enqueue public-facing assets.
     */
    public function enqueue_public_assets() {
        wp_enqueue_style( 'woopop-public-css', WOOP_POP_PLUGIN_URL . 'assets/css/public.css', [], WOOP_POP_VERSION );
        wp_enqueue_script( 'woopop-public-js', WOOP_POP_PLUGIN_URL . 'assets/js/public.js', [ 'jquery' ], WOOP_POP_VERSION, true );
        wp_enqueue_style( 'font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css', [], '6.4.0' );
    }

    /**
     * Add Back to Top script.
     */
    public function add_back_to_top_script() {
        $style_options    = get_option( 'woopop_style_options', [] );
        $show_back_to_top = isset( $style_options['woopop_show_back_to_top'] ) ? $style_options['woopop_show_back_to_top'] : false;

        if ( $show_back_to_top ) {
            ?>
            <script type="text/javascript">
            jQuery(document).ready(function($) {
                $(window).scroll(function() {
                    if ($(this).scrollTop() > 100) {
                        $('.woopop-back-to-top').fadeIn();
                    } else {
                        $('.woopop-back-to-top').fadeOut();
                    }
                });
                $('.woopop-back-to-top').click(function() {
                    $('html, body').animate({ scrollTop: 0 }, 600);
                    return false;
                });
            });
            </script>
            <?php
        }
    }

    /**
     * Render WooPop shortcode.
     *
     * @param array $atts
     * @return string
     */
    public function render_woopop_shortcode( $atts ) {
        // Accept 'title' and 'content' as optional attributes
        $atts = shortcode_atts( [
            'title'   => '',
            'content' => '',
        ], $atts, 'woopop' );

        // Fetch options
        $bio_options   = get_option( 'woopop_bio_options', [] );
        $style_options = get_option( 'woopop_style_options', [] );
        $links         = get_option( 'woopop_links', [] );

        // Use data from options or defaults
        $profile_name        = $bio_options['woopop_profile_name'] ?? 'Your Name';
        $profile_description = $bio_options['woopop_profile_description'] ?? 'Your bio goes here.';
        $bio_image_id        = $bio_options['woopop_bio_image_id'] ?? 0;
        $profile_image       = $bio_image_id ? wp_get_attachment_image_url( $bio_image_id, 'full' ) : WOOP_POP_PLUGIN_URL . 'assets/images/placeholder.png';

        $bg_color           = $style_options['woopop_bg_color'] ?? '#ffffff';
        $text_color         = $style_options['woopop_text_color'] ?? '#333333';
        $button_color       = $style_options['woopop_button_color'] ?? '#0073aa';
        $button_hover_color = $style_options['woopop_button_hover_color'] ?? '#005a87';
        $card_bg_color      = $style_options['woopop_card_bg_color'] ?? '#f9f9f9';
        $card_text_color    = $style_options['woopop_card_text_color'] ?? '#333333';
        $font_family        = $style_options['woopop_font_family'] ?? 'Arial, sans-serif';
        $hover_text_color   = $style_options['woopop_text_hover_color'] ?? '#ffffff';
        $show_back_to_top   = isset( $style_options['woopop_show_back_to_top'] ) ? $style_options['woopop_show_back_to_top'] : false;
        $show_social_icons  = isset( $style_options['woopop_show_social_icons'] ) ? $style_options['woopop_show_social_icons'] : false;

        // Use shortcode attributes if provided
        $title   = ! empty( $atts['title'] ) ? sanitize_text_field( $atts['title'] ) : $profile_name;
        $content = ! empty( $atts['content'] ) ? sanitize_textarea_field( $atts['content'] ) : $profile_description;

        // Output the content
        ob_start();
        ?>
        <div class="woopop-container" style="background-color: <?php echo esc_attr( $bg_color ); ?>; color: <?php echo esc_attr( $text_color ); ?>; font-family: <?php echo esc_attr( $font_family ); ?>;">
            <!-- Set CSS variables for styles -->
            <style>
                .woopop-container {
                    --button-color: <?php echo esc_attr( $button_color ); ?>;
                    --button-hover-color: <?php echo esc_attr( $button_hover_color ); ?>;
                    --button-text-color: #ffffff;
                    --hover-text-color: <?php echo esc_attr( $hover_text_color ); ?>;
                    --card-bg-color: <?php echo esc_attr( $card_bg_color ); ?>;
                    --card-text-color: <?php echo esc_attr( $card_text_color ); ?>;
                }
            </style>

            <div class="woopop-profile">
                <img src="<?php echo esc_url( $profile_image ); ?>" alt="<?php echo esc_attr( $title ); ?>" class="woopop-profile-image">
                <h3 class="woopop-profile-name"><?php echo esc_html( $title ); ?></h3>
                <p class="woopop-profile-description"><?php echo nl2br( esc_html( $content ) ); ?></p>
            </div>
            <div class="woopop-links">
                <?php
                if ( ! empty( $links ) ) {
                    foreach ( $links as $link ) {
                        $link_name  = ! empty( $link['name'] ) ? esc_html( $link['name'] ) : __( 'Link', 'woopop' );
                        $link_url   = '';
                        $link_image = '';

                        if ( $link['type'] === 'product' && ! empty( $link['product_id'] ) ) {
                            $product_id  = $link['product_id'];
                            $link_url    = get_permalink( $product_id );
                            $link_name   = get_the_title( $product_id );
                            $link_image  = get_the_post_thumbnail_url( $product_id, 'medium' );
                        } elseif ( $link['type'] === 'media' && ! empty( $link['media_url'] ) ) {
                            $link_url   = esc_url( $link['media_url'] );
                            $link_image = $link_url;
                        } elseif ( $link['type'] === 'form' && ! empty( $link['form'] ) ) {
                            // Handle Form Link Type
                            $form_data = explode( ':', $link['form'] );
                            $form_type = $form_data[0];
                            $form_id   = $form_data[1];

                            echo '<div class="woopop-card">';
                            echo '<div class="woopop-form-container">';
                            if ( $form_type === 'cf7' && class_exists( 'WPCF7' ) ) {
                                echo do_shortcode( '[contact-form-7 id="' . esc_attr( $form_id ) . '"]' );
                            } elseif ( $form_type === 'gf' && class_exists( 'GFAPI' ) ) {
                                echo do_shortcode( '[gravityform id="' . esc_attr( $form_id ) . '" title="false" description="false" ajax="true"]' );
                            } elseif ( $form_type === 'wpforms' && class_exists( 'WPForms' ) ) {
                                echo do_shortcode( '[wpforms id="' . esc_attr( $form_id ) . '" title="false" description="false"]' );
                            } else {
                                echo '<p>' . esc_html__( 'Form plugin not found or inactive.', 'woopop' ) . '</p>';
                            }
                            echo '</div>';
                            echo '</div>';
                            continue;
                        } elseif ( $link['type'] === 'video' ) {
                        // Handle Video Link Type
                        $video_url      = $link['video_url'] ?? '';
                        $video_file     = $link['video_file'] ?? '';
                        $video_autoplay = !empty( $link['video_autoplay'] ) ? '1' : '0';
                        $video_muted    = !empty( $link['video_mute'] ) ? '1' : '0';
                    
                        echo '<div class="woopop-card">';
                        echo '<div class="woopop-video-container">';
                    
                        if ( ! empty( $video_url ) ) {
                            // For video URLs (e.g., YouTube or Vimeo), append autoplay and mute parameters
                            $autoplay_param = $video_autoplay === '1' ? '1' : '0';
                            $mute_param     = $video_muted === '1' ? '1' : '0';
                    
                            // Parse video URL to modify query parameters
                            $parsed_url = parse_url( $video_url );
                    
                            // Handle YouTube URLs
                            if ( strpos( $parsed_url['host'], 'youtube.com' ) !== false || strpos( $parsed_url['host'], 'youtu.be' ) !== false ) {
                                parse_str( $parsed_url['query'] ?? '', $query_params );
                                $query_params['autoplay'] = $autoplay_param;
                                $query_params['mute']     = $mute_param;
                    
                                // Extract video ID
                                if ( isset( $query_params['v'] ) ) {
                                    $video_id = $query_params['v'];
                                } else {
                                    // For youtu.be URLs
                                    $video_id = trim( $parsed_url['path'], '/' );
                                }
                    
                                $embed_url = 'https://www.youtube.com/embed/' . $video_id . '?' . http_build_query( $query_params );
                            }
                            // Handle Vimeo URLs
                            elseif ( strpos( $parsed_url['host'], 'vimeo.com' ) !== false ) {
                                $video_id = trim( $parsed_url['path'], '/' );
                                $query_params = [
                                    'autoplay' => $autoplay_param,
                                    'muted'    => $mute_param,
                                ];
                                $embed_url = 'https://player.vimeo.com/video/' . $video_id . '?' . http_build_query( $query_params );
                            }
                            else {
                                // Other video URLs
                                $embed_url = $video_url;
                            }
                    
                            // Embed video using iframe
                            echo '<iframe src="' . esc_url( $embed_url ) . '" frameborder="0" allow="autoplay; encrypted-media" allowfullscreen style="width:100%; height:auto;"></iframe>';
                        } elseif ( ! empty( $video_file ) ) {
                            // For uploaded videos, use video tag with controls
                            $autoplay_attr = $video_autoplay === '1' ? ' autoplay' : '';
                            $muted_attr    = $video_muted === '1' ? ' muted' : '';
                            echo '<video controls' . $autoplay_attr . $muted_attr . ' style="width:100%;">';
                            echo '<source src="' . esc_url( $video_file ) . '" type="video/mp4">';
                            echo esc_html__( 'Your browser does not support the video tag.', 'woopop' );
                            echo '</video>';
                        } else {
                            echo '<p>' . esc_html__( 'No video available.', 'woopop' ) . '</p>';
                        }
                            echo '</div>';
                            echo '</div>';
                            continue;
                        } elseif ( ! empty( $link['url'] ) ) {
                            $link_url = esc_url( $link['url'] );
                        }

                        if ( $link_url ) {
                            // Wrap image-based links in a card
                            if ( $link_image ) {
                                echo '<div class="woopop-card">';
                                echo '<a href="' . esc_url( $link_url ) . '" class="woopop-link-image" target="_blank" rel="noopener noreferrer">';
                                echo '<img src="' . esc_url( $link_image ) . '" alt="' . esc_attr( $link_name ) . '">';
                                echo '<span class="woopop-link-name">' . esc_html( $link_name ) . '</span>';
                                echo '</a>';
                                echo '</div>';
                            } else {
                                // Non-image links remain as buttons
                                echo '<a href="' . esc_url( $link_url ) . '" class="woopop-link" target="_blank" rel="noopener noreferrer">' . esc_html( $link_name ) . '</a>';
                            }
                        }
                    }
                } else {
                    echo '<p>' . esc_html__( 'No links added yet.', 'woopop' ) . '</p>';
                }
                ?>
            </div>
            <?php
            // Add social media icons using Font Awesome
            if ( $show_social_icons && ! empty( $bio_options['woopop_social_links'] ) ) {
                echo '<div class="woopop-social-links">';
                foreach ( $bio_options['woopop_social_links'] as $key => $url ) {
                    if ( ! empty( $url ) ) {
                        // Map social network keys to Font Awesome classes
                        $fa_classes = [
                            'facebook'  => 'fab fa-facebook-f',
                            'instagram' => 'fab fa-instagram',
                            'twitter'   => 'fab fa-twitter',
                            'youtube'   => 'fab fa-youtube',
                            'snapchat'  => 'fab fa-snapchat-ghost',
                            'tiktok'    => 'fab fa-tiktok',
                            'pinterest' => 'fab fa-pinterest-p',
                            'patreon'   => 'fab fa-patreon',
                            'linkedin'  => 'fab fa-linkedin-in',
                        ];
                        $fa_class = isset( $fa_classes[ $key ] ) ? $fa_classes[ $key ] : 'fas fa-globe';
                        echo '<a href="' . esc_url( $url ) . '" target="_blank" rel="noopener noreferrer" class="woopop-social-icon"><i class="' . esc_attr( $fa_class ) . '"></i></a>';
                    }
                }
                echo '</div>';
            }

            // Back to Top icon
            if ( $show_back_to_top ) {
                echo '<div class="woopop-back-to-top" style="display: none;"><i class="fas fa-arrow-up"></i></div>';
            }
            ?>
        </div>
        <?php
        return ob_get_clean();
    }
}

// Initialize the Woopop_Public class
Woopop_Public::get_instance();
