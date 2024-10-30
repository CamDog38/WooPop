<?php
// includes/common/functions.php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

/**
 * Helper function to sanitize links array.
 *
 * @param array $input
 * @return array
 */
function woopop_sanitize_links_array($input) {
    if (!is_array($input)) {
        return array();
    }

    return array_map('esc_url_raw', $input);
}
