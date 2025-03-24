<?php
/**
 * GeneratePress child theme functions and definitions
 */
// Enqueue parent and child theme styles
function generatepress_child_enqueue_styles() {
    // Enqueue parent theme's style
    wp_enqueue_style('generatepress-style', get_template_directory_uri() . '/style.css');
    // Enqueue child theme's style
    wp_enqueue_style('generatepress-child-style',
        get_stylesheet_directory_uri() . '/style.css',
        array('generatepress-style'),
        wp_get_theme()->get('Version')
    );
}
add_action('wp_enqueue_scripts', 'generatepress_child_enqueue_styles');

/**
 * Include TOC feature files
 */
require_once get_stylesheet_directory() . '/inc/toc/class-toc.php';
require_once get_stylesheet_directory() . '/inc/toc/class-toc-widget.php';
require_once get_stylesheet_directory() . '/inc/toc/class-toc-shortcode.php';

// Only load block integration if Gutenberg is active
if (function_exists('register_block_type')) {
    require_once get_stylesheet_directory() . '/inc/toc/class-toc-block.php';
}

/**
 * Include Schema feature files
 */
require_once get_stylesheet_directory() . '/inc/schema/class-schema.php';

/**
 * Include SEO feature files
 */
require_once get_stylesheet_directory() . '/inc/seo/class-seo.php';
require_once get_stylesheet_directory() . '/inc/seo/class-sitemap.php';

/**
 * Only load TOC assets on singular pages where they might be used
 */
function gp_child_toc_conditionally_load_assets() {
    if (is_singular() && !is_admin() || is_active_widget(false, false, 'gp_child_toc_widget', true)) {
        // Enqueue CSS and JS only on pages where TOC might be used
        wp_enqueue_style('gp-child-toc',
            get_stylesheet_directory_uri() . '/assets/css/toc.css',
            array(),
            wp_get_theme()->get('Version')
        );
        wp_enqueue_script('gp-child-toc',
            get_stylesheet_directory_uri() . '/assets/js/toc.js',
            array('jquery'),
            wp_get_theme()->get('Version'),
            true
        );
    }
}
add_action('wp_enqueue_scripts', 'gp_child_toc_conditionally_load_assets', 20);

/**
 * Load SEO assets in admin
 */
function gp_child_seo_admin_assets() {
    $screen = get_current_screen();
    
    // Only load on post edit screens
    if ($screen && ($screen->base === 'post' || $screen->post_type === 'post' || $screen->post_type === 'page')) {
        wp_enqueue_style('gp-child-seo', 
            get_stylesheet_directory_uri() . '/assets/css/seo.css',
            array(),
            wp_get_theme()->get('Version')
        );
        
        wp_enqueue_script('gp-child-seo', 
            get_stylesheet_directory_uri() . '/assets/js/seo.js', 
            array('jquery', 'wp-media-upload'), 
            wp_get_theme()->get('Version'), 
            true
        );
    }
}
add_action('admin_enqueue_scripts', 'gp_child_seo_admin_assets');

/**
 * Cache TOC output
 */
function gp_child_cached_toc_output($content, $args) {
    global $post;
    if (!$post) return '';
    
    // Generate a cache key based on content and args
    $cache_key = 'gp_toc_' . $post->ID . '_' . md5(serialize($args));
    
    // Try to get cached output
    $cached_output = get_transient($cache_key);
    if ($cached_output !== false) {
        return $cached_output;
    }
    
    // Generate TOC if not cached
    $toc_instance = GP_Child_TOC::get_instance();
    $toc = $toc_instance->generate_toc($content, $args);
    
    // Cache for 1 week, or until post is updated
    set_transient($cache_key, $toc, WEEK_IN_SECONDS);
    
    return $toc;
}

/**
 * Flush TOC cache when post is updated
 */
function gp_child_flush_toc_cache($post_id) {
    global $wpdb;
    $wpdb->query("DELETE FROM $wpdb->options WHERE option_name LIKE '%gp_toc_{$post_id}_%'");
}
add_action('save_post', 'gp_child_flush_toc_cache');