/**
 * SEO Module JavaScript
 */
(function($) {
    'use strict';
    
    $(document).ready(function() {
        // Tab functionality
        $('.gp-seo-tab-button').on('click', function() {
            var tabId = $(this).data('tab');
            
            // Remove active class from all tabs
            $('.gp-seo-tab-button').removeClass('active');
            $('.gp-seo-tab-content').removeClass('active');
            
            // Add active class to current tab
            $(this).addClass('active');
            $('.gp-seo-tab-content[data-tab="' + tabId + '"]').addClass('active');
        });
        
        // Image upload functionality
        $('.gp-seo-upload-button').on('click', function(e) {
            e.preventDefault();
            
            var button = $(this);
            var inputField = button.prev('input');
            
            // Create media frame
            var frame = wp.media({
                title: 'Select or Upload an Image',
                button: {
                    text: 'Use this image'
                },
                multiple: false
            });
            
            // When image is selected
            frame.on('select', function() {
                var attachment = frame.state().get('selection').first().toJSON();
                inputField.val(attachment.url);
                
                // Update preview if it's an OG or Twitter image
                if (inputField.attr('id') === 'gp_seo_og_image') {
                    // You could add preview functionality here
                }
            });
            
            // Open media library
            frame.open();
        });
        
        // Character counting and preview updates
        var titleField = $('#gp_seo_title');
        var descriptionField = $('#gp_seo_description');
        var previewTitle = $('.gp-seo-preview-title');
        var previewDescription = $('.gp-seo-preview-description');
        
        // Add character count elements
        titleField.after('<span class="gp-seo-char-count" id="seo-title-count">0 characters</span>');
        descriptionField.after('<span class="gp-seo-char-count" id="seo-description-count">0 characters</span>');
        
        var titleCount = $('#seo-title-count');
        var descriptionCount = $('#seo-description-count');
        
        // Update title preview and character count
        titleField.on('input', function() {
            var val = $(this).val();
            var len = val.length;
            
            // Update preview
            if (val) {
                previewTitle.text(val);
            } else {
                // Use post title as fallback
                previewTitle.text($('#title').val());
            }
            
            // Update character count
            titleCount.text(len + ' characters');
            
            // Add warning or error class based on length
            titleCount.removeClass('warning error');
            if (len > 60 && len <= 70) {
                titleCount.addClass('warning');
            } else if (len > 70) {
                titleCount.addClass('error');
            }
        });
        
        // Update description preview and character count
        descriptionField.on('input', function() {
            var val = $(this).val();
            var len = val.length;
            
            // Update preview
            if (val) {
                previewDescription.text(val);
            } else {
                previewDescription.text('');
            }
            
            // Update character count
            descriptionCount.text(len + ' characters');
            
            // Add warning or error class based on length
            descriptionCount.removeClass('warning error');
            if (len > 150 && len <= 160) {
                descriptionCount.addClass('warning');
            } else if (len > 160) {
                descriptionCount.addClass('error');
            }
        });
        
        // Trigger initial update
        titleField.trigger('input');
        descriptionField.trigger('input');
        
        // Fill social media fields with general SEO data if empty
        $('#gp_seo_og_title, #gp_seo_twitter_title').on('focus', function() {
            if (!$(this).val() && titleField.val()) {
                $(this).val(titleField.val());
            }
        });
        
        $('#gp_seo_og_description, #gp_seo_twitter_description').on('focus', function() {
            if (!$(this).val() && descriptionField.val()) {
                $(this).val(descriptionField.val());
            }
        });
    });
})(jQuery);