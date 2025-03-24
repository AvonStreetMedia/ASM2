/**
 * TOC Frontend JavaScript
 */
(function($) {
    'use strict';
    
    // Initialize TOC functionality when document is ready
    $(document).ready(function() {
        // Toggle functionality
        $('.gp-toc-toggle').on('click', function() {
            var $toc = $(this).closest('.gp-toc');
            $toc.toggleClass('toc-collapsed');
        });
        
        // Smooth scroll to headings
        $('.gp-toc-list a').on('click', function(e) {
            e.preventDefault();
            
            var targetId = $(this).attr('href');
            var $target = $(targetId);
            
            if ($target.length) {
                // Scroll to the target with offset for fixed headers
                var offset = 100; // Adjust based on your theme's header height
                var targetPosition = $target.offset().top - offset;
                
                $('html, body').animate({
                    scrollTop: targetPosition
                }, 500);
                
                // Add a temporary highlight effect
                $target.addClass('toc-highlight');
                
                setTimeout(function() {
                    $target.removeClass('toc-highlight');
                }, 1500);
            }
        });
        
        // Highlight current section while scrolling
        $(window).on('scroll', function() {
            var scrollPosition = $(window).scrollTop();
            
            // Find all headings with IDs
            $('h1[id], h2[id], h3[id], h4[id], h5[id], h6[id]').each(function() {
                var currentHeading = $(this);
                var headingPosition = currentHeading.offset().top;
                
        // Add some offset to account for fixed headers
var offset = 120;
                
if (scrollPosition + offset >= headingPosition) {
    // Remove active class from all TOC links
    $('.gp-toc-list a').removeClass('toc-active');
    
    // Add active class to the corresponding TOC link
    var headingId = currentHeading.attr('id');
    $('.gp-toc-list a[href="#' + headingId + '"]').addClass('toc-active');
}
});
});
});
})(jQuery);