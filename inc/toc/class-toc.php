<?php
/**
 * TOC Main Class
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

class GP_Child_TOC {
    /**
     * Instance
     */
    private static $instance;

    /**
     * Get instance
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
        // Nothing to do here as we're loading files from functions.php
    }

    /**
     * Generate TOC HTML
     */
    public function generate_toc($content, $args = array()) {
        // Default arguments
        $defaults = array(
            'style'       => 'toc-1',
            'title'       => __('Table of Contents', 'generatepress-child'),
            'min_headers' => 3,
            'max_depth'   => 3,
            'numbering'   => true,
            'toggle'      => true,
        );

        $args = wp_parse_args($args, $defaults);
        
        // Extract headers from content
        $headers = $this->extract_headers($content, $args['max_depth']);
        
        // Don't display TOC if not enough headers
        if (count($headers) < $args['min_headers']) {
            return '';
        }
        
        // Build TOC HTML
        $toc_html = $this->build_toc_html($headers, $args);
        
        return $toc_html;
    }

    /**
     * Extract headers from content
     */
    private function extract_headers($content, $max_depth) {
        $headers = array();
        
        // Match h1 to h6 tags based on max_depth
        $pattern = '/<h([1-' . $max_depth . ']).*?id=["\'](.*?)["\'].*?>(.*?)<\/h\1>/i';
        
        if (preg_match_all($pattern, $content, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $level = $match[1];
                $id = $match[2];
                $title = strip_tags($match[3]);
                
                $headers[] = array(
                    'level' => $level,
                    'id'    => $id,
                    'title' => $title,
                );
            }
        }
        
        return $headers;
    }

    /**
     * Build TOC HTML
     */
    private function build_toc_html($headers, $args) {
        $style_class = esc_attr($args['style']);
        $title = esc_html($args['title']);
        $toggle_class = $args['toggle'] ? 'toc-toggle' : '';
        
        $output = '<div class="gp-toc ' . $style_class . ' ' . $toggle_class . '">';
        $output .= '<div class="gp-toc-header">';
        $output .= '<h4 class="gp-toc-title">' . $title . '</h4>';
        
        if ($args['toggle']) {
            $output .= '<span class="gp-toc-toggle">[';
            $output .= '<span class="gp-toc-toggle-show">' . __('hide', 'generatepress-child') . '</span>';
            $output .= '<span class="gp-toc-toggle-hide">' . __('show', 'generatepress-child') . '</span>';
            $output .= ']</span>';
        }
        
        $output .= '</div>'; // End header
        
        $output .= '<div class="gp-toc-content">';
        $output .= '<ol class="gp-toc-list">';
        
        // Current level for hierarchical list
        $current_level = 1;
        $numbering_counters = array(0, 0, 0, 0, 0, 0);
        
        foreach ($headers as $index => $header) {
            $header_level = (int) $header['level'];
            
            // Open new sublists as needed
            while ($current_level < $header_level) {
                $output .= '<ol class="gp-toc-sublist">';
                $current_level++;
            }
            
            // Close sublists as needed
            while ($current_level > $header_level) {
                $output .= '</ol></li>';
                $current_level--;
            }
            
            // Reset lower-level counters when moving to a higher level
            if ($args['numbering']) {
                $numbering_counters[$header_level - 1]++;
                for ($i = $header_level; $i < 6; $i++) {
                    $numbering_counters[$i] = 0;
                }
            }
            
            // Item number display
            $number = '';
            if ($args['numbering']) {
                $number_parts = array();
                for ($i = 0; $i < $header_level; $i++) {
                    $number_parts[] = $numbering_counters[$i];
                }
                $number = implode('.', $number_parts) . '. ';
            }
            
            $output .= '<li class="gp-toc-item gp-toc-level-' . $header_level . '">';
            $output .= '<a href="#' . esc_attr($header['id']) . '">';
            $output .= $number . esc_html($header['title']);
            $output .= '</a>';
            
            // Don't close the list item yet if we're not at the last header
            // and the next header is a subheader
            if (isset($headers[$index + 1]) && $headers[$index + 1]['level'] > $header_level) {
                // Next item will be a subitem
            } else {
                $output .= '</li>';
            }
        }
        
        // Close any remaining open lists
        while ($current_level > 1) {
            $output .= '</ol></li>';
            $current_level--;
        }
        
        $output .= '</ol>';
        $output .= '</div>'; // End content
        $output .= '</div>'; // End TOC container
        
        return $output;
    }

    /**
     * Ensure heading IDs are added
     */
    public function ensure_heading_ids($content) {
        // Add IDs to headings if they don't already have them
        $pattern = '/<h([1-6])(?![^>]*id=["|\'])([^>]*)>(.*?)<\/h\1>/i';
        $replacement = function($matches) {
            $level = $matches[1];
            $attrs = $matches[2];
            $title = $matches[3];
            
            // Generate ID from title
            $id = sanitize_title($title);
            
            return '<h' . $level . $attrs . ' id="' . $id . '">' . $title . '</h' . $level . '>';
        };
        
        return preg_replace_callback($pattern, $replacement, $content);
    }
}

// Initialize TOC
GP_Child_TOC::get_instance();