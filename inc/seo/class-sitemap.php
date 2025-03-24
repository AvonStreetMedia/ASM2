<?php
/**
 * GeneratePress Child Theme Sitemap Generator
 * 
 * Generates XML sitemaps for better SEO
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

class GP_Child_Sitemap {
    /**
     * Instance of this class
     */
    private static $instance = null;

    /**
     * Get instance of this class
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor
     */
    public function __construct() {
        // Add rewrite rules
        add_action('init', array($this, 'add_rewrite_rules'));
        
        // Handle sitemap requests
        add_action('template_redirect', array($this, 'handle_sitemap_requests'));
        
        // Update sitemap when post is published or updated
        add_action('save_post', array($this, 'update_sitemap'), 10, 3);
        
        // Add settings to Reading page
        add_action('admin_init', array($this, 'add_settings'));
    }

    /**
     * Add rewrite rules for sitemaps
     */
    public function add_rewrite_rules() {
        add_rewrite_rule('^sitemap\.xml$', 'index.php?gp_sitemap=index', 'top');
        add_rewrite_rule('^sitemap-([a-zA-Z0-9_-]+)\.xml$', 'index.php?gp_sitemap=$matches[1]', 'top');
        
        add_rewrite_tag('%gp_sitemap%', '([a-zA-Z0-9_-]+)');
    }

    /**
     * Handle sitemap requests
     */
    public function handle_sitemap_requests() {
        global $wp_query;
        
        if (!isset($wp_query->query_vars['gp_sitemap'])) {
            return;
        }
        
        $sitemap_type = $wp_query->query_vars['gp_sitemap'];
        
        // Set headers
        header('Content-Type: application/xml; charset=UTF-8');
        
        // Disable caching
        header('Cache-Control: no-cache, no-store, must-revalidate');
        header('Pragma: no-cache');
        header('Expires: 0');
        
        // Generate the appropriate sitemap
        if ($sitemap_type === 'index') {
            $this->generate_index_sitemap();
        } else {
            $this->generate_specific_sitemap($sitemap_type);
        }
        
        exit;
    }

    /**
     * Generate index sitemap
     */
    private function generate_index_sitemap() {
        $sitemap_content = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $sitemap_content .= '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
        
        // Add post types sitemaps
        $post_types = get_post_types(array('public' => true));
        
        foreach ($post_types as $post_type) {
            // Skip attachments
            if ($post_type === 'attachment') {
                continue;
            }
            
            // Get the latest post of this type
            $latest_post = get_posts(array(
                'post_type' => $post_type,
                'posts_per_page' => 1,
                'orderby' => 'modified',
                'order' => 'DESC',
            ));
            
            $lastmod = '';
            if (!empty($latest_post)) {
                $lastmod = '<lastmod>' . date('c', strtotime($latest_post[0]->post_modified_gmt)) . '</lastmod>';
            }
            
            $sitemap_content .= "\t" . '<sitemap>' . "\n";
            $sitemap_content .= "\t\t" . '<loc>' . home_url('/sitemap-' . $post_type . '.xml') . '</loc>' . "\n";
            if (!empty($lastmod)) {
                $sitemap_content .= "\t\t" . $lastmod . "\n";
            }
            $sitemap_content .= "\t" . '</sitemap>' . "\n";
        }
        
        // Add taxonomy sitemaps
        $taxonomies = get_taxonomies(array('public' => true));
        
        foreach ($taxonomies as $taxonomy) {
            // Skip post formats
            if ($taxonomy === 'post_format') {
                continue;
            }
            
            $sitemap_content .= "\t" . '<sitemap>' . "\n";
            $sitemap_content .= "\t\t" . '<loc>' . home_url('/sitemap-' . $taxonomy . '.xml') . '</loc>' . "\n";
            $sitemap_content .= "\t" . '</sitemap>' . "\n";
        }
        
        $sitemap_content .= '</sitemapindex>';
        
        echo $sitemap_content;
    }

    /**
     * Generate a specific sitemap
     */
    private function generate_specific_sitemap($type) {
        // Check if it's a post type sitemap
        $post_types = get_post_types(array('public' => true));
        $taxonomies = get_taxonomies(array('public' => true));
        
        $sitemap_content = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $sitemap_content .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd">' . "\n";
        
        if (in_array($type, $post_types)) {
            // Post type sitemap
            $posts = get_posts(array(
                'post_type' => $type,
                'posts_per_page' => 1000,
                'post_status' => 'publish',
            ));
            
            foreach ($posts as $post) {
                // Skip if noindex is enabled for this post
                $robots = get_post_meta($post->ID, '_gp_seo_robots', true);
                if (strpos($robots, 'noindex') !== false) {
                    continue;
                }
                
                $sitemap_content .= "\t" . '<url>' . "\n";
                $sitemap_content .= "\t\t" . '<loc>' . get_permalink($post) . '</loc>' . "\n";
                $sitemap_content .= "\t\t" . '<lastmod>' . date('c', strtotime($post->post_modified_gmt)) . '</lastmod>' . "\n";
                
                // Set change frequency and priority based on post type
                $change_freq = 'weekly';
                $priority = '0.7';
                
                if ($type === 'page') {
                    $change_freq = 'monthly';
                    $priority = '0.8';
                } elseif ($post->post_date > date('Y-m-d H:i:s', strtotime('-1 week'))) {
                    $change_freq = 'daily';
                    $priority = '0.9';
                }
                
                $sitemap_content .= "\t\t" . '<changefreq>' . $change_freq . '</changefreq>' . "\n";
                $sitemap_content .= "\t\t" . '<priority>' . $priority . '</priority>' . "\n";
                $sitemap_content .= "\t" . '</url>' . "\n";
            }
        } elseif (in_array($type, $taxonomies)) {
            // Taxonomy sitemap
            $terms = get_terms(array(
                'taxonomy' => $type,
                'hide_empty' => true,
            ));
            
            foreach ($terms as $term) {
                $sitemap_content .= "\t" . '<url>' . "\n";
                $sitemap_content .= "\t\t" . '<loc>' . get_term_link($term) . '</loc>' . "\n";
                $sitemap_content .= "\t\t" . '<changefreq>monthly</changefreq>' . "\n";
                $sitemap_content .= "\t\t" . '<priority>0.6</priority>' . "\n";
                $sitemap_content .= "\t" . '</url>' . "\n";
            }
        }
        
        $sitemap_content .= '</urlset>';
        
        echo $sitemap_content;
    }

    /**
     * Update sitemap when post is saved
     */
    public function update_sitemap($post_id, $post, $update) {
        // Skip if post is not published
        if ($post->post_status !== 'publish') {
            return;
        }
        
        // Skip revisions and auto-saves
        if (wp_is_post_revision($post_id) || wp_is_post_autosave($post_id)) {
            return;
        }
        
        // Ping search engines
        $sitemap_url = home_url('/sitemap.xml');
        
        // Only ping if setting is enabled
        if (get_option('gp_sitemap_ping_engines', 1)) {
            // Ping Google
            wp_remote_get('https://www.google.com/ping?sitemap=' . urlencode($sitemap_url));
            
            // Ping Bing
            wp_remote_get('https://www.bing.com/ping?sitemap=' . urlencode($sitemap_url));
        }
    }

    /**
     * Add settings to Reading page
     */
    public function add_settings() {
        add_settings_section(
            'gp_sitemap_settings',
            __('Sitemap Settings', 'generatepress-child'),
            array($this, 'sitemap_settings_section_callback'),
            'reading'
        );
        
        add_settings_field(
            'gp_sitemap_ping_engines',
            __('Ping Search Engines', 'generatepress-child'),
            array($this, 'ping_engines_callback'),
            'reading',
            'gp_sitemap_settings'
        );
        
        register_setting('reading', 'gp_sitemap_ping_engines', 'intval');
    }

    /**
     * Settings section callback
     */
    public function sitemap_settings_section_callback() {
        echo '<p>' . __('Settings for XML sitemaps generated by the theme.', 'generatepress-child') . '</p>';
        echo '<p>' . __('Sitemap URL:', 'generatepress-child') . ' <a href="' . home_url('/sitemap.xml') . '" target="_blank">' . home_url('/sitemap.xml') . '</a></p>';
    }

    /**
     * Ping engines callback
     */
    public function ping_engines_callback() {
        $value = get_option('gp_sitemap_ping_engines', 1);
        echo '<input type="checkbox" name="gp_sitemap_ping_engines" value="1" ' . checked(1, $value, false) . ' />';
        echo '<p class="description">' . __('Automatically notify Google and Bing when your sitemap is updated.', 'generatepress-child') . '</p>';
    }
}

// Initialize Sitemap class
GP_Child_Sitemap::get_instance();