<?php
// uninstall.php

if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// Delete plugin options
$options = array(
    'woopop_product_links',
    'woopop_media_links',
    'woopop_custom_links',
    'woopop_affiliate_links',
    'woopop_amazon_links',
    'woopop_bg_color',
    'woopop_text_color',
    'woopop_button_color',
    'woopop_button_hover_color',
    'woopop_profile_url',
    'woopop_bio_image',
    'woopop_profile_name',
    'woopop_profile_description',
    'woopop_shopify_store',
    'woopop_email',
    'woopop_facebook',
    'woopop_instagram',
    'woopop_twitter',
    'woopop_youtube',
    'woopop_snapchat',
    'woopop_tiktok',
    'woopop_pinterest',
    'woopop_patreon',
    'woopop_linkedin',
    'woopop_seo_description',
);

foreach ($options as $option) {
    delete_option($option);
}
