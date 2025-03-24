<?php
/**
 * TOC Widget Class
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

class GP_Child_TOC_Widget extends WP_Widget {
    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct(
            'gp_child_toc_widget',
            __('TOC Widget', 'generatepress-child'),
            array(
                'description' => __('Adds a Table of Contents widget', 'generatepress-child'),
            )
        );
    }

    /**
     * Front-end display
     */
    public function widget($args, $instance) {
        // Only show on singular posts, pages, or custom post types
        if (!is_singular()) {
            return;
        }
        
        global $post;
        $content = $post->post_content;
        
        echo $args['before_widget'];
        
        if (!empty($instance['title'])) {
            echo $args['before_title'] . apply_filters('widget_title', $instance['title']) . $args['after_title'];
        }
        
        // Ensure heading IDs
        $content = GP_Child_TOC::get_instance()->ensure_heading_ids($content);
        
        // Generate TOC
        $toc_args = array(
            'style'       => !empty($instance['style']) ? $instance['style'] : 'toc-1',
            'title'       => !empty($instance['toc_title']) ? $instance['toc_title'] : __('Table of Contents', 'generatepress-child'),
            'min_headers' => !empty($instance['min_headers']) ? $instance['min_headers'] : 3,
            'max_depth'   => !empty($instance['max_depth']) ? $instance['max_depth'] : 3,
            'numbering'   => !empty($instance['numbering']),
            'toggle'      => !empty($instance['toggle']),
        );
        
        // Use cached output
        echo gp_child_cached_toc_output($content, $toc_args);
        
        echo $args['after_widget'];
    }

    /**
     * Back-end widget form
     */
    public function form($instance) {
        // Default values
        $title = !empty($instance['title']) ? $instance['title'] : '';
        $toc_title = !empty($instance['toc_title']) ? $instance['toc_title'] : 'Table of Contents';
        $style = !empty($instance['style']) ? $instance['style'] : 'toc-1';
        ?>
        
        <!-- Widget Title -->
        <p>
            <label for="<?php echo $this->get_field_id('title'); ?>">Widget Title:</label>
            <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>">
        </p>
        
        <!-- TOC Title -->
        <p>
            <label for="<?php echo $this->get_field_id('toc_title'); ?>">TOC Title:</label>
            <input class="widefat" id="<?php echo $this->get_field_id('toc_title'); ?>" name="<?php echo $this->get_field_name('toc_title'); ?>" type="text" value="<?php echo esc_attr($toc_title); ?>">
        </p>
        
        <!-- Style Selection -->
        <p>
            <label for="<?php echo $this->get_field_id('style'); ?>">Style:</label>
            <select class="widefat" id="<?php echo $this->get_field_id('style'); ?>" name="<?php echo $this->get_field_name('style'); ?>">
                <option value="toc-1" <?php selected($style, 'toc-1'); ?>>Style 1 (Clean)</option>
                <option value="toc-2" <?php selected($style, 'toc-2'); ?>>Style 2 (Boxed)</option>
                <option value="toc-3" <?php selected($style, 'toc-3'); ?>>Style 3 (Minimal)</option>
            </select>
        </p>
        <?php
    }

    /**
     * Sanitize widget form values
     */
    public function update($new_instance, $old_instance) {
        $instance = array();
        $instance['title'] = (!empty($new_instance['title'])) ? sanitize_text_field($new_instance['title']) : '';
        $instance['toc_title'] = (!empty($new_instance['toc_title'])) ? sanitize_text_field($new_instance['toc_title']) : '';
        $instance['style'] = (!empty($new_instance['style'])) ? sanitize_text_field($new_instance['style']) : 'toc-1';
        $instance['min_headers'] = 3;
        $instance['max_depth'] = 3;
        $instance['numbering'] = true;
        $instance['toggle'] = true;

        return $instance;
    }
}

// Register the widget
function gp_child_register_toc_widget() {
    register_widget('GP_Child_TOC_Widget');
}
add_action('widgets_init', 'gp_child_register_toc_widget');