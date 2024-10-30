// assets/js/public.js

jQuery(document).ready(function($) {
    // Back to Top functionality
    $('.woopop-back-to-top').on('click', function() {
        $('html, body').animate({ scrollTop: 0 }, 'slow');
    });
});
