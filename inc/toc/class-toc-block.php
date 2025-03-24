<?php
/**
 * TOC Block Class
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

class GP_Child_TOC_Block {
    /**
     * Constructor
     */
    public function __construct() {
        add_action('init', array($this, 'register_block'));
        add_action('enqueue_block_editor_assets', array($this, 'enqueue_block_editor_assets'));
    }

    /**
     * Register block
     */
    public function register_block() {
        register_block_type('generatepress-child/toc', array(
            'editor_script' => 'gp-child-toc-block',
            'render_callback' => array($this, 'render_block'),
            'attributes' => array(
                'style' => array(
                    'type' => 'string',
                    'default' => 'toc-1',
                ),
                'title' => array(
                    'type' => 'string',
                    'default' => __('Table of Contents', 'generatepress-child'),
                ),
                'minHeaders' => array(
                    'type' => 'number',
                    'default' => 3,
                ),
                'maxDepth' => array(
                    'type' => 'number',
                    'default' => 3,
                ),
                'numbering' => array(
                    'type' => 'boolean',
                    'default' => true,
                ),
                'toggle' => array(
                    'type' => 'boolean',
                    'default' => true,
                ),
            ),
        ));
    }

    /**
     * Enqueue block editor assets
     */
    public function enqueue_block_editor_assets() {
        wp_enqueue_script(
            'gp-child-toc-block',
            get_stylesheet_directory_uri() . '/assets/js/toc-block.js',
            array('wp-blocks', 'wp-element', 'wp-editor', 'wp-components', 'wp-i18n'),
            wp_get_theme()->get('Version'),
            true
        );
        
        wp_enqueue_style(
            'gp-child-toc-editor-styles',
            get_stylesheet_directory_uri() . '/assets/css/editor.css',
            array(),
            wp_get_theme()->get('Version')
        );
    }

    /**
     * Render block
     */
    public function render_block($attributes, $content) {
        // Don't show in editor
        if (defined('REST_REQUEST') && REST_REQUEST) {
            return '<div class="gp-toc-placeholder">' . __('Table of Contents will appear here', 'generatepress-child') . '</div>';
        }
        
        // Get post content
        global $post;
        if (!$post || !is_singular()) {
            return '';
        }
        
        $content = $post->post_content;
        
        // Ensure heading IDs are added
        $content = GP_Child_TOC::get_instance()->ensure_heading_ids($content);
        
        // Prepare TOC args
        $args = array(
            'style'       => $attributes['style'] ?? 'toc-1',
            'title'       => $attributes['title'] ?? __('Table of Contents', 'generatepress-child'),
            'min_headers' => $attributes['minHeaders'] ?? 3,
            'max_depth'   => $attributes['maxDepth'] ?? 3,
            'numbering'   => $attributes['numbering'] ?? true,
            'toggle'      => $attributes['toggle'] ?? true,
        );
        
        // Generate TOC
        return gp_child_cached_toc_output($content, $args);
    }
}

// Initialize TOC Block
new GP_Child_TOC_Block();