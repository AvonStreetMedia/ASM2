<?php
/**
 * GeneratePress Child Theme SEO Module
 * 
 * Adds SEO functionality to the GeneratePress child theme
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

class GP_Child_SEO {
    /**
     * Instance of this class
     */
    private static $instance = null;

    /**
     * SEO meta keys
     */
    private $meta_keys = array(
        'title' => '_gp_seo_title',
        'description' => '_gp_seo_description',
        'keywords' => '_gp_seo_keywords',
        'canonical' => '_gp_seo_canonical',
        'robots' => '_gp_seo_robots',
        'og_title' => '_gp_seo_og_title',
        'og_description' => '_gp_seo_og_description',
        'og_image' => '_gp_seo_og_image',
        'twitter_title' => '_gp_seo_twitter_title',
        'twitter_description' => '_gp_seo_twitter_description',
        'twitter_image' => '_gp_seo_twitter_image',
        'twitter_card' => '_gp_seo_twitter_card'
    );

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
        // Add meta box to post editor
        add_action('add_meta_boxes', array($this, 'add_meta_box'));
        
        // Save meta box data
        add_action('save_post', array($this, 'save_meta_box'));
        
        // Output SEO tags in head
        add_action('wp_head', array($this, 'output_seo_tags'), 1);
        
        // Prevent Yoast SEO from outputting meta tags if installed
        if (defined('WPSEO_VERSION')) {
            add_filter('wpseo_canonical', '__return_false');
            add_filter('wpseo_metadesc', '__return_false');
            add_filter('wpseo_title', '__return_false');
            add_filter('wpseo_opengraph', '__return_false');
            add_filter('wpseo_twitter', '__return_false');
        }
    }

    /**
     * Add meta box to post editor
     */
    public function add_meta_box() {
        $post_types = apply_filters('gp_child_seo_post_types', array('post', 'page'));
        
        foreach ($post_types as $post_type) {
            add_meta_box(
                'gp_child_seo_meta_box',
                __('SEO Settings', 'generatepress-child'),
                array($this, 'render_meta_box'),
                $post_type,
                'normal',
                'high'
            );
        }
    }

    /**
     * Render meta box content
     */
    public function render_meta_box($post) {
        // Add nonce for security
        wp_nonce_field('gp_child_seo_meta_box', 'gp_child_seo_meta_box_nonce');
        
        // Get saved values
        $seo_title = get_post_meta($post->ID, $this->meta_keys['title'], true);
        $seo_description = get_post_meta($post->ID, $this->meta_keys['description'], true);
        $seo_keywords = get_post_meta($post->ID, $this->meta_keys['keywords'], true);
        $seo_canonical = get_post_meta($post->ID, $this->meta_keys['canonical'], true);
        $seo_robots = get_post_meta($post->ID, $this->meta_keys['robots'], true);
        $og_title = get_post_meta($post->ID, $this->meta_keys['og_title'], true);
        $og_description = get_post_meta($post->ID, $this->meta_keys['og_description'], true);
        $og_image = get_post_meta($post->ID, $this->meta_keys['og_image'], true);
        $twitter_title = get_post_meta($post->ID, $this->meta_keys['twitter_title'], true);
        $twitter_description = get_post_meta($post->ID, $this->meta_keys['twitter_description'], true);
        $twitter_image = get_post_meta($post->ID, $this->meta_keys['twitter_image'], true);
        $twitter_card = get_post_meta($post->ID, $this->meta_keys['twitter_card'], true);
        
        // Default values
        if (empty($twitter_card)) {
            $twitter_card = 'summary_large_image';
        }
        
        // Get preview data
        $preview_title = !empty($seo_title) ? $seo_title : get_the_title($post->ID);
        $preview_url = get_permalink($post->ID);
        
        // Output the fields
        ?>
        <div class="gp-seo-tabs">
            <div class="gp-seo-tabs-nav">
                <button type="button" class="gp-seo-tab-button active" data-tab="general"><?php _e('General', 'generatepress-child'); ?></button>
                <button type="button" class="gp-seo-tab-button" data-tab="social"><?php _e('Social Media', 'generatepress-child'); ?></button>
                <button type="button" class="gp-seo-tab-button" data-tab="advanced"><?php _e('Advanced', 'generatepress-child'); ?></button>
            </div>
            
            <div class="gp-seo-tab-content active" data-tab="general">
                <div class="gp-seo-preview">
                    <h4><?php _e('Search Preview', 'generatepress-child'); ?></h4>
                    <div class="gp-seo-preview-title"><?php echo esc_html($preview_title); ?></div>
                    <div class="gp-seo-preview-url"><?php echo esc_url($preview_url); ?></div>
                    <div class="gp-seo-preview-description"><?php echo esc_html($seo_description); ?></div>
                </div>
                
                <div class="gp-seo-field">
                    <label for="gp_seo_title"><?php _e('SEO Title', 'generatepress-child'); ?></label>
                    <input type="text" id="gp_seo_title" name="gp_seo_title" value="<?php echo esc_attr($seo_title); ?>" class="widefat" />
                    <p class="description"><?php _e('If empty, the post title will be used.', 'generatepress-child'); ?></p>
                </div>
                
                <div class="gp-seo-field">
                    <label for="gp_seo_description"><?php _e('Meta Description', 'generatepress-child'); ?></label>
                    <textarea id="gp_seo_description" name="gp_seo_description" rows="3" class="widefat"><?php echo esc_textarea($seo_description); ?></textarea>
                    <p class="description"><?php _e('Recommended length: 150-160 characters.', 'generatepress-child'); ?></p>
                </div>
                
                <div class="gp-seo-field">
                    <label for="gp_seo_keywords"><?php _e('Keywords', 'generatepress-child'); ?></label>
                    <input type="text" id="gp_seo_keywords" name="gp_seo_keywords" value="<?php echo esc_attr($seo_keywords); ?>" class="widefat" />
                    <p class="description"><?php _e('Comma-separated keywords.', 'generatepress-child'); ?></p>
                </div>
            </div>
            
            <div class="gp-seo-tab-content" data-tab="social">
                <h4><?php _e('Open Graph (Facebook, LinkedIn)', 'generatepress-child'); ?></h4>
                <div class="gp-seo-field">
                    <label for="gp_seo_og_title"><?php _e('OG Title', 'generatepress-child'); ?></label>
                    <input type="text" id="gp_seo_og_title" name="gp_seo_og_title" value="<?php echo esc_attr($og_title); ?>" class="widefat" />
                    <p class="description"><?php _e('If empty, the SEO Title will be used.', 'generatepress-child'); ?></p>
                </div>
                
                <div class="gp-seo-field">
                    <label for="gp_seo_og_description"><?php _e('OG Description', 'generatepress-child'); ?></label>
                    <textarea id="gp_seo_og_description" name="gp_seo_og_description" rows="3" class="widefat"><?php echo esc_textarea($og_description); ?></textarea>
                    <p class="description"><?php _e('If empty, the Meta Description will be used.', 'generatepress-child'); ?></p>
                </div>
                
                <div class="gp-seo-field">
                    <label for="gp_seo_og_image"><?php _e('OG Image', 'generatepress-child'); ?></label>
                    <div class="gp-seo-image-upload">
                        <input type="text" id="gp_seo_og_image" name="gp_seo_og_image" value="<?php echo esc_attr($og_image); ?>" class="widefat" />
                        <button type="button" class="button gp-seo-upload-button"><?php _e('Select Image', 'generatepress-child'); ?></button>
                    </div>
                    <p class="description"><?php _e('Recommended size: 1200x630 pixels.', 'generatepress-child'); ?></p>
                </div>
                
                <h4 class="gp-seo-divider"><?php _e('Twitter Cards', 'generatepress-child'); ?></h4>
                
                <div class="gp-seo-field">
                    <label for="gp_seo_twitter_title"><?php _e('Twitter Title', 'generatepress-child'); ?></label>
                    <input type="text" id="gp_seo_twitter_title" name="gp_seo_twitter_title" value="<?php echo esc_attr($twitter_title); ?>" class="widefat" />
                    <p class="description"><?php _e('If empty, the OG Title will be used.', 'generatepress-child'); ?></p>
                </div>
                
                <div class="gp-seo-field">
                    <label for="gp_seo_twitter_description"><?php _e('Twitter Description', 'generatepress-child'); ?></label>
                    <textarea id="gp_seo_twitter_description" name="gp_seo_twitter_description" rows="3" class="widefat"><?php echo esc_textarea($twitter_description); ?></textarea>
                    <p class="description"><?php _e('If empty, the OG Description will be used.', 'generatepress-child'); ?></p>
                </div>
                
                <div class="gp-seo-field">
                    <label for="gp_seo_twitter_image"><?php _e('Twitter Image', 'generatepress-child'); ?></label>
                    <div class="gp-seo-image-upload">
                        <input type="text" id="gp_seo_twitter_image" name="gp_seo_twitter_image" value="<?php echo esc_attr($twitter_image); ?>" class="widefat" />
                        <button type="button" class="button gp-seo-upload-button"><?php _e('Select Image', 'generatepress-child'); ?></button>
                    </div>
                    <p class="description"><?php _e('If empty, the OG Image will be used.', 'generatepress-child'); ?></p>
                </div>
                
                <div class="gp-seo-field">
                    <label for="gp_seo_twitter_card"><?php _e('Twitter Card Type', 'generatepress-child'); ?></label>
                    <select id="gp_seo_twitter_card" name="gp_seo_twitter_card" class="widefat">
                        <option value="summary_large_image" <?php selected($twitter_card, 'summary_large_image'); ?>><?php _e('Summary with Large Image', 'generatepress-child'); ?></option>
                        <option value="summary" <?php selected($twitter_card, 'summary'); ?>><?php _e('Summary', 'generatepress-child'); ?></option>
                    </select>
                </div>
            </div>
            
            <div class="gp-seo-tab-content" data-tab="advanced">
                <div class="gp-seo-field">
                    <label for="gp_seo_canonical"><?php _e('Canonical URL', 'generatepress-child'); ?></label>
                    <input type="text" id="gp_seo_canonical" name="gp_seo_canonical" value="<?php echo esc_attr($seo_canonical); ?>" class="widefat" />
                    <p class="description"><?php _e('If empty, the post permalink will be used.', 'generatepress-child'); ?></p>
                </div>
                
                <div class="gp-seo-field">
                    <label for="gp_seo_robots"><?php _e('Robots Meta', 'generatepress-child'); ?></label>
                    <select id="gp_seo_robots" name="gp_seo_robots" class="widefat">
                        <option value="" <?php selected($seo_robots, ''); ?>><?php _e('Default (index, follow)', 'generatepress-child'); ?></option>
                        <option value="noindex,follow" <?php selected($seo_robots, 'noindex,follow'); ?>><?php _e('noindex, follow', 'generatepress-child'); ?></option>
                        <option value="index,nofollow" <?php selected($seo_robots, 'index,nofollow'); ?>><?php _e('index, nofollow', 'generatepress-child'); ?></option>
                        <option value="noindex,nofollow" <?php selected($seo_robots, 'noindex,nofollow'); ?>><?php _e('noindex, nofollow', 'generatepress-child'); ?></option>
                    </select>
                </div>
            </div>
        </div>
        <?php
    }

    /**
     * Save meta box data
     */
    public function save_meta_box($post_id) {
        // Check if nonce is set
        if (!isset($_POST['gp_child_seo_meta_box_nonce'])) {
            return;
        }
        
        // Verify nonce
        if (!wp_verify_nonce($_POST['gp_child_seo_meta_box_nonce'], 'gp_child_seo_meta_box')) {
            return;
        }
        
        // Check if autosave
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }
        
        // Check permissions
        if ('page' === $_POST['post_type']) {
            if (!current_user_can('edit_page', $post_id)) {
                return;
            }
        } else {
            if (!current_user_can('edit_post', $post_id)) {
                return;
            }
        }
        
        // Save meta fields
        $fields = array(
            'gp_seo_title' => 'title',
            'gp_seo_description' => 'description',
            'gp_seo_keywords' => 'keywords',
            'gp_seo_canonical' => 'canonical',
            'gp_seo_robots' => 'robots',
            'gp_seo_og_title' => 'og_title',
            'gp_seo_og_description' => 'og_description',
            'gp_seo_og_image' => 'og_image',
            'gp_seo_twitter_title' => 'twitter_title',
            'gp_seo_twitter_description' => 'twitter_description',
            'gp_seo_twitter_image' => 'twitter_image',
            'gp_seo_twitter_card' => 'twitter_card'
        );
        
        foreach ($fields as $field => $meta_key) {
            if (isset($_POST[$field])) {
                $value = sanitize_text_field($_POST[$field]);
                update_post_meta($post_id, $this->meta_keys[$meta_key], $value);
            }
        }
    }

    /**
     * Output SEO tags in head
     */
    public function output_seo_tags() {
        // Only output on singular pages
        if (!is_singular()) {
            return;
        }
        
        global $post;
        
        // Get saved SEO data
        $seo_title = get_post_meta($post->ID, $this->meta_keys['title'], true);
        $seo_description = get_post_meta($post->ID, $this->meta_keys['description'], true);
        $seo_keywords = get_post_meta($post->ID, $this->meta_keys['keywords'], true);
        $seo_canonical = get_post_meta($post->ID, $this->meta_keys['canonical'], true);
        $seo_robots = get_post_meta($post->ID, $this->meta_keys['robots'], true);
        $og_title = get_post_meta($post->ID, $this->meta_keys['og_title'], true);
        $og_description = get_post_meta($post->ID, $this->meta_keys['og_description'], true);
        $og_image = get_post_meta($post->ID, $this->meta_keys['og_image'], true);
        $twitter_title = get_post_meta($post->ID, $this->meta_keys['twitter_title'], true);
        $twitter_description = get_post_meta($post->ID, $this->meta_keys['twitter_description'], true);
        $twitter_image = get_post_meta($post->ID, $this->meta_keys['twitter_image'], true);
        $twitter_card = get_post_meta($post->ID, $this->meta_keys['twitter_card'], true);
        
        // Set defaults if empty
        if (empty($seo_title)) {
            $seo_title = get_the_title($post->ID);
        }
        
        if (empty($seo_canonical)) {
            $seo_canonical = get_permalink($post->ID);
        }
        
        if (empty($og_title)) {
            $og_title = $seo_title;
        }
        
        if (empty($og_description)) {
            $og_description = $seo_description;
        }
        
        if (empty($twitter_title)) {
            $twitter_title = $og_title;
        }
        
        if (empty($twitter_description)) {
            $twitter_description = $og_description;
        }
        
        if (empty($twitter_image)) {
            $twitter_image = $og_image;
        }
        
        if (empty($twitter_card)) {
            $twitter_card = 'summary_large_image';
        }
        
        // Output meta tags
        if (!empty($seo_title)) {
            echo '<title>' . esc_html($seo_title) . '</title>' . "\n";
        }
        
        if (!empty($seo_description)) {
            echo '<meta name="description" content="' . esc_attr($seo_description) . '" />' . "\n";
        }
        
        if (!empty($seo_keywords)) {
            echo '<meta name="keywords" content="' . esc_attr($seo_keywords) . '" />' . "\n";
        }
        
        if (!empty($seo_canonical)) {
            echo '<link rel="canonical" href="' . esc_url($seo_canonical) . '" />' . "\n";
        }
        
        if (!empty($seo_robots)) {
            echo '<meta name="robots" content="' . esc_attr($seo_robots) . '" />' . "\n";
        }
        
        // Open Graph tags
        echo '<meta property="og:type" content="article" />' . "\n";
        echo '<meta property="og:url" content="' . esc_url(get_permalink($post->ID)) . '" />' . "\n";
        
        if (!empty($og_title)) {
            echo '<meta property="og:title" content="' . esc_attr($og_title) . '" />' . "\n";
        }
        
        if (!empty($og_description)) {
            echo '<meta property="og:description" content="' . esc_attr($og_description) . '" />' . "\n";
        }
        
        if (!empty($og_image)) {
            echo '<meta property="og:image" content="' . esc_url($og_image) . '" />' . "\n";
        } elseif (has_post_thumbnail($post->ID)) {
            $thumbnail = wp_get_attachment_image_src(get_post_thumbnail_id($post->ID), 'full');
            echo '<meta property="og:image" content="' . esc_url($thumbnail[0]) . '" />' . "\n";
        }
        
        // Twitter Card tags
        echo '<meta name="twitter:card" content="' . esc_attr($twitter_card) . '" />' . "\n";
        
        if (!empty($twitter_title)) {
            echo '<meta name="twitter:title" content="' . esc_attr($twitter_title) . '" />' . "\n";
        }
        
        if (!empty($twitter_description)) {
            echo '<meta name="twitter:description" content="' . esc_attr($twitter_description) . '" />' . "\n";
        }
        
        if (!empty($twitter_image)) {
            echo '<meta name="twitter:image" content="' . esc_url($twitter_image) . '" />' . "\n";
        } elseif (has_post_thumbnail($post->ID)) {
            $thumbnail = wp_get_attachment_image_src(get_post_thumbnail_id($post->ID), 'full');
            echo '<meta name="twitter:image" content="' . esc_url($thumbnail[0]) . '" />' . "\n";
        }
    }
}

// Initialize SEO class
GP_Child_SEO::get_instance();