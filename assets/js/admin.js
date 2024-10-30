/* assets/js/admin.js */

jQuery(document).ready(function($) {
    // Ensure the code only runs on the WooPop settings page
    if ($('#woopop-settings-page').length === 0) {
        return;
    }

    // Media uploader for Profile Photo
    var file_frame;
    $('#woopop-bio-image-upload').on('click', function(event) {
        event.preventDefault();

        // If the media frame already exists, reopen it.
        if (file_frame) {
            file_frame.open();
            return;
        }

        // Create the media frame.
        file_frame = wp.media.frames.file_frame = wp.media({
            title: 'Select Profile Photo',
            button: {
                text: 'Use this image',
            },
            multiple: false
        });

        // When an image is selected, run a callback.
        file_frame.on('select', function() {
            var attachment = file_frame.state().get('selection').first().toJSON();
            $('#woopop-bio-image-id').val(attachment.id);
            $('#woopop-bio-image-preview').attr('src', attachment.url);
            updateMobilePreview();
        });

        // Open the modal
        file_frame.open();
    });

    // Remove Profile Photo
    $('#woopop-bio-image-remove').on('click', function(event) {
        event.preventDefault();
        $('#woopop-bio-image-id').val('');
        $('#woopop-bio-image-preview').attr('src', woopopData.placeholderImage);
        updateMobilePreview();
    });

    // Add Link
    $('.woopop-add-link').on('click', function() {
        var repeater = $(this).closest('.woopop-repeater');
        var itemsContainer = repeater.find('.woopop-repeater-items');
        var template = repeater.find('.woopop-repeater-item-template').html();
        var newItem = $(template);

        // Reset input/select values
        newItem.find('input, select').val('');
        newItem.find('.woopop-link-field').hide();
        newItem.find('.woopop-link-field.custom').show(); // Default to custom link
        newItem.find('input[name^="woopop_links[woopop_links_name]"]').prop('disabled', false);

        itemsContainer.append(newItem);

        // Re-initialize events for new items
        updateMobilePreview();
    });

    // Remove Link
    $(document).on('click', '.woopop-remove-link', function() {
        $(this).closest('.woopop-repeater-item').remove();
        updateMobilePreview();
    });

    // Change Link Type
    $(document).on('change', '.woopop-link-type', function() {
        var linkType = $(this).val();
        var repeaterItem = $(this).closest('.woopop-repeater-item');
        repeaterItem.find('.woopop-link-field').hide();
        repeaterItem.find('.woopop-link-field.' + linkType).show();

        // If Product type, disable Name input
        if (linkType === 'product') {
            repeaterItem.find('input[name^="woopop_links[woopop_links_name]"]').prop('disabled', true).val('');
        } else {
            repeaterItem.find('input[name^="woopop_links[woopop_links_name]"]').prop('disabled', false);
        }
        updateMobilePreview();
    });

    // Video Upload
    $(document).on('click', '.woopop-video-upload-button', function(e) {
        e.preventDefault();
        var button = $(this);
        var input = button.prev('.woopop-video-file');
        var file_frame = wp.media({
            title: 'Select or Upload Video',
            button: {
                text: 'Use this video',
            },
            multiple: false,
            library: {
                type: 'video'
            }
        });
        file_frame.on('select', function() {
            var attachment = file_frame.state().get('selection').first().toJSON();
            input.val(attachment.url);
            updateMobilePreview();
        });
        file_frame.open();
    });

    // Update Mobile Preview on Form Changes
    $(document).on('input change', '.woopop-settings-form input, .woopop-settings-form select, .woopop-settings-form textarea', function() {
        updateMobilePreview();
    });

    // Tab Navigation
    $('.woopop-tab-navigation .nav-tab').on('click', function(e) {
        e.preventDefault();
        var target = $(this).attr('href');

        // Remove active class from all tabs and contents
        $('.woopop-tab-navigation .nav-tab').removeClass('nav-tab-active');
        $('.woopop-tab-content').removeClass('active').hide();

        // Add active class to the selected tab and show the content
        $(this).addClass('nav-tab-active');
        $(target).addClass('active').show();

        // Save active tab to localStorage
        localStorage.setItem('woopopActiveTab', target);
    });

    // Initialize the first tab as active
    var activeTab = localStorage.getItem('woopopActiveTab') || '#woopop_bio';
    $('.woopop-tab-navigation .nav-tab[href="' + activeTab + '"]').addClass('nav-tab-active');
    $(activeTab).addClass('active').show();

    // Initialize sortable (drag-and-drop)
    $('.woopop-repeater-items').sortable({
        handle: '.woopop-repeater-handle',
        update: function(event, ui) {
            updateMobilePreview();
        }
    });

    // Mobile Links Repeater
    $('.woopop-add-mobile-link').on('click', function() {
        var tableBody = $('.woopop-mobile-links-table tbody');
        var rowCount = tableBody.find('tr').length;
        var newRow = '<tr>' +
            '<td>' +
                '<div class="woopop-url-input">' +
                    '<span class="woopop-base-url">' + woopopData.site_url + '/</span>' +
                    '<input type="text" name="woopop_mobile_links[' + rowCount + '][slug]" value="" placeholder="' + woopopData.slug_placeholder + '" class="regular-text slug-input" required>' +
                '</div>' +
                '<div class="woopop-generated-url">' + woopopData.site_url + '/<span class="generated-slug"></span></div>' +
            '</td>' +
            '<td>' +
                '<input type="text" name="woopop_mobile_links[' + rowCount + '][title]" value="" placeholder="' + woopopData.title_placeholder + '" class="regular-text" required>' +
            '</td>' +
            '<td>' +
                '<button type="button" class="button woopop-remove-mobile-link">' + woopopData.remove_button_text + '</button>' +
            '</td>' +
            '</tr>';
        tableBody.append(newRow);
    });

    // Remove Mobile Link
    $(document).on('click', '.woopop-remove-mobile-link', function() {
        $(this).closest('tr').remove();
        updateMobilePreview();
    });

    /**
     * Function to update the mobile preview mockup.
     */
    function updateMobilePreview() {
        // Update Profile Image
        var imageId = $('#woopop-bio-image-id').val();
        var profileImage = imageId ? $('#woopop-bio-image-preview').attr('src') : woopopData.placeholderImage;
        $('#woopop-preview-image').attr('src', profileImage);

        // Update Profile Name
        var profileName = $('input[name="woopop_bio_options[woopop_profile_name]"]').val() || 'Your Name';
        $('#woopop-preview-title').text(profileName);

        // Update Profile Description
        var profileDescription = $('textarea[name="woopop_bio_options[woopop_profile_description]"]').val() || 'Your bio goes here.';
        $('#woopop-preview-description').text(profileDescription);

        // Update Style Variables
        var bgColor = $('input[name="woopop_style_options[woopop_bg_color]"]').val() || '#ffffff';
        var textColor = $('input[name="woopop_style_options[woopop_text_color]"]').val() || '#333333';
        var buttonColor = $('input[name="woopop_style_options[woopop_button_color]"]').val() || '#0073aa';
        var buttonHoverColor = $('input[name="woopop_style_options[woopop_button_hover_color]"]').val() || '#005a87';
        var cardBgColor = $('input[name="woopop_style_options[woopop_card_bg_color]"]').val() || '#f9f9f9';
        var cardTextColor = $('input[name="woopop_style_options[woopop_card_text_color]"]').val() || '#333333';
        var fontFamily = $('select[name="woopop_style_options[woopop_font_family]"]').val() || 'Arial, sans-serif';
        var hoverTextColor = $('input[name="woopop_style_options[woopop_text_hover_color]"]').val() || '#ffffff';

        // Update Links
        var linksHtml = '';
        $('.woopop-repeater-item').each(function() {
            var linkType = $(this).find('.woopop-link-type').val();
            var linkUrl = '';
            var linkName = '';
            var linkImage = '';

            if (linkType === 'product') {
                var productId = $(this).find('select[name^="woopop_links[woopop_links_product]"]').val();
                if (productId) {
                    linkUrl = $(this).find('select[name^="woopop_links[woopop_links_product]"] option:selected').data('url');
                    linkName = $(this).find('select[name^="woopop_links[woopop_links_product]"] option:selected').text();
                    linkImage = $(this).find('select[name^="woopop_links[woopop_links_product]"] option:selected').data('image');

                    linksHtml += '<div class="woopop-card" style="background-color: ' + cardBgColor + '; color: ' + cardTextColor + ';">';
                    linksHtml += '<a href="' + linkUrl + '" target="_blank" rel="noopener noreferrer" class="woopop-link-image">';
                    linksHtml += '<img src="' + linkImage + '" alt="' + linkName + '">';
                    linksHtml += '<span class="woopop-link-name">' + linkName + '</span>';
                    linksHtml += '</a>';
                    linksHtml += '</div>';
                }
            } else if (linkType === 'media') {
                linkUrl = $(this).find('select[name^="woopop_links[woopop_links_media]"]').val();
                linkName = $(this).find('input[name^="woopop_links[woopop_links_name]"]').val() || 'Media Link';
                if (linkUrl) {
                    linkImage = linkUrl;

                    linksHtml += '<div class="woopop-card" style="background-color: ' + cardBgColor + '; color: ' + cardTextColor + ';">';
                    linksHtml += '<a href="' + linkUrl + '" target="_blank" rel="noopener noreferrer" class="woopop-link-image">';
                    linksHtml += '<img src="' + linkImage + '" alt="' + linkName + '">';
                    linksHtml += '<span class="woopop-link-name">' + linkName + '</span>';
                    linksHtml += '</a>';
                    linksHtml += '</div>';
                }
            } else if (linkType === 'form') {
                var formValue = $(this).find('select[name^="woopop_links[woopop_links_form]"]').val();
                var linkName = $(this).find('input[name^="woopop_links[woopop_links_name]"]').val() || 'Form';
                if (formValue) {
                    linksHtml += '<div class="woopop-card">';
                    linksHtml += '<div class="woopop-form-preview">' + linkName + ' (Form Preview)</div>'; // Simplified preview
                    linksHtml += '</div>';
                }
            } else if (linkType === 'video') {
                var videoUrl = $(this).find('input[name^="woopop_links[woopop_links_video_url]"]').val();
                var videoFile = $(this).find('input[name^="woopop_links[woopop_links_video_file]"]').val();
                var linkName = $(this).find('input[name^="woopop_links[woopop_links_name]"]').val() || 'Video';
            
                if (videoUrl || videoFile) {
                    linksHtml += '<div class="woopop-card">';
                    linksHtml += '<div class="woopop-video-container">';
            
                    if (videoUrl) {
                        // For video URLs like YouTube or Vimeo, embed using iframe
                        linksHtml += '<iframe src="' + videoUrl + '" frameborder="0" allowfullscreen style="width:100%; height:auto;"></iframe>';
                    } else if (videoFile) {
                        // For uploaded video files, use video tag
                        linksHtml += '<video controls style="width:100%;">';
                        linksHtml += '<source src="' + videoFile + '" type="video/mp4">';
                        linksHtml += 'Your browser does not support the video tag.';
                        linksHtml += '</video>';
                    }
            
                    linksHtml += '</div>';
                    linksHtml += '</div>';
                }
            } else if (linkType === 'form') {
                var formValue = $(this).find('select[name^="woopop_links[woopop_links_form]"]').val();
                var linkName = $(this).find('input[name^="woopop_links[woopop_links_name]"]').val() || 'Form';
                if (formValue) {
                    linksHtml += '<div class="woopop-card">';
                    linksHtml += '<div class="woopop-form-preview">';
                    linksHtml += '<p>' + linkName + '</p>';
                    linksHtml += '<p>(Form will be displayed here)</p>';
                    linksHtml += '</div>';
                    linksHtml += '</div>';
                }
            } else {
                linkUrl = $(this).find('input[name^="woopop_links[woopop_links_url]"]').val();
                linkName = $(this).find('input[name^="woopop_links[woopop_links_name]"]').val() || 'Custom Link';

                if (linkUrl) {
                    linksHtml += '<a href="' + linkUrl + '" target="_blank" rel="noopener noreferrer" class="woopop-link" style="background-color: ' + buttonColor + '; color: #fff;" data-hover-color="' + hoverTextColor + '" data-hover-bg-color="' + buttonHoverColor + '">' + linkName + '</a>';
                }
            }
        });
        $('#woopop-preview-links').html(linksHtml);

        // Update Styles
        $('.woopop-mobile-screen').css({
            'background-color': bgColor,
            'color': textColor,
            'font-family': fontFamily
        });

        // Apply hover effects in preview
        $('#woopop-preview-links .woopop-link').hover(
            function() {
                $(this).css({
                    'background-color': $(this).attr('data-hover-bg-color'),
                    'color': $(this).attr('data-hover-color')
                });
            },
            function() {
                $(this).css({
                    'background-color': buttonColor,
                    'color': '#fff'
                });
            }
        );

        // Update Generated URLs
        $('.woopop-mobile-links-table tbody tr').each(function() {
            var baseUrl = $(this).find('.woopop-base-url').text();
            var slug = $(this).find('.slug-input').val();
            $(this).find('.generated-slug').text(slug);
        });

        // Update Social Icons
        var showSocialIcons = $('input[name="woopop_style_options[woopop_show_social_icons]"]').is(':checked');
        if (showSocialIcons) {
            var socialIconsHtml = '';
            $('.woopop-social-media-table input').each(function() {
                var url = $(this).val();
                if (url) {
                    var nameAttr = $(this).attr('name');
                    var keyMatch = nameAttr.match(/\[woopop_social_links\]\[(\w+)\]$/);
                    if (keyMatch) {
                        var key = keyMatch[1];
                        var faClass = getFaClassForSocialNetwork(key);
                        socialIconsHtml += '<a href="' + url + '" target="_blank" rel="noopener noreferrer" class="woopop-social-icon"><i class="' + faClass + '"></i></a>';
                    }
                }
            });
            $('#woopop-preview-social-links').html('<div class="woopop-social-links">' + socialIconsHtml + '</div>');
        } else {
            $('#woopop-preview-social-links').html('');
        }

        // Update Back to Top Button
        var showBackToTop = $('input[name="woopop_style_options[woopop_show_back_to_top]"]').is(':checked');
        if (showBackToTop) {
            if ($('#woopop-preview-back-to-top').length === 0) {
                $('.woopop-mobile-screen').append('<div id="woopop-preview-back-to-top"><i class="fas fa-arrow-up"></i></div>');
            }
        } else {
            $('#woopop-preview-back-to-top').remove();
        }

        // Center Align Profile Image and Text
        $('.woopop-mobile-screen .woopop-profile, #woopop-preview-title, #woopop-preview-description').css('text-align', 'center');
        $('#woopop-preview-image').css('display', 'block').css('margin', '0 auto 15px');
    }

    // Helper function to get Font Awesome class
    function getFaClassForSocialNetwork(network) {
        var faClasses = {
            'facebook': 'fab fa-facebook-f',
            'instagram': 'fab fa-instagram',
            'twitter': 'fab fa-twitter',
            'youtube': 'fab fa-youtube',
            'snapchat': 'fab fa-snapchat-ghost',
            'tiktok': 'fab fa-tiktok',
            'pinterest': 'fab fa-pinterest-p',
            'patreon': 'fab fa-patreon',
            'linkedin': 'fab fa-linkedin-in',
        };
        return faClasses[network] || 'fas fa-globe';
    }

    // Initialize the preview on page load
    updateMobilePreview();

    // Event delegation for slug inputs to update generated URLs in real-time
    $(document).on('input', '.slug-input', function() {
        var slug = $(this).val();
        $(this).closest('tr').find('.generated-slug').text(slug);
    });

    // Back to Top functionality in preview
    $(document).on('click', '#woopop-preview-back-to-top', function() {
        $('.woopop-mobile-screen').animate({ scrollTop: 0 }, 'slow');
    });

    // Show/hide back to top button in preview
    $('.woopop-mobile-screen').scroll(function() {
        if ($(this).scrollTop() > 100) {
            $('#woopop-preview-back-to-top').fadeIn();
        } else {
            $('#woopop-preview-back-to-top').fadeOut();
        }
    });

    // Save the active tab before form submission
    $('.woopop-settings-form').on('submit', function() {
        localStorage.setItem('woopopActiveTab', $('.woopop-tab-navigation .nav-tab-active').attr('href'));
    });

    // Restore the active tab after page reload
    $(document).ready(function() {
        var activeTab = localStorage.getItem('woopopActiveTab') || '#woopop_bio';
        $('.woopop-tab-navigation .nav-tab').removeClass('nav-tab-active');
        $('.woopop-tab-content').removeClass('active').hide();

        $('.woopop-tab-navigation .nav-tab[href="' + activeTab + '"]').addClass('nav-tab-active');
        $(activeTab).addClass('active').show();
    });
});
