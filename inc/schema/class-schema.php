<?php
/**
 * AI-Basics.com Schema Manager
 * 
 * Adds structured data schema to posts and pages with special handling for 
 * AI tutorials, comparisons, and learning resources
 */

/**
 * Add schema type meta box
 */
function gp_child_add_schema_meta_box() {
    add_meta_box(
        'gp_child_schema_type',
        'AI Content Schema',
        'gp_child_schema_meta_box_callback',
        ['post', 'page'],
        'normal',
        'high'
    );
}
add_action('add_meta_boxes', 'gp_child_add_schema_meta_box');

/**
 * Meta box callback function
 */
function gp_child_schema_meta_box_callback($post) {
    // Add nonce for security
    wp_nonce_field('gp_child_schema_save', 'gp_child_schema_nonce');
    
    // Get current values
    $schema_type = get_post_meta($post->ID, '_gp_schema_type', true);
    $schema_options = get_post_meta($post->ID, '_gp_schema_options', true);
    
    if (empty($schema_type)) {
        $schema_type = 'Article'; // Default type
    }
    
    if (empty($schema_options) || !is_array($schema_options)) {
        $schema_options = [];
    }
    
    // Schema type options with AI-Basics focus
    $schema_types = [
        'Article' => 'Article (Default)',
        'TechArticle' => 'Tech Article',
        'LearningResource' => 'Learning Resource/Hub',
        'AIComparison' => 'AI Tool Comparison',
        'SoftwareApplication' => 'AI Software/Tool',
        'HowTo' => 'AI Tutorial/Guide',
        'FAQPage' => 'AI FAQ Page',
        'Course' => 'AI Course',
        'Review' => 'AI Tool Review',
        'WebPage' => 'General Web Page',
        'None' => 'No Schema (Disable)'
    ];
    
    // CSS for the meta box
    ?>
    <style>
        .ai-schema-panel {
            background: #fff;
            padding: 10px 15px;
            border: 1px solid #e2e8f0;
            border-radius: 4px;
        }
        
        .ai-schema-section {
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 1px solid #e2e8f0;
        }
        
        .ai-schema-section:last-child {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }
        
        .ai-schema-field {
            margin-bottom: 10px;
        }
        
        .ai-schema-field label {
            font-weight: 500;
        }
        
        .ai-schema-field input[type="text"],
        .ai-schema-field select,
        .ai-schema-field textarea {
            margin-top: 5px;
        }
        
        .ai-schema-field .description {
            color: #718096;
            font-style: italic;
            font-size: 12px;
            margin-top: 3px;
        }
        
        .ai-schema-comparison-tools {
            background: #f8fafc;
            padding: 10px;
            border-radius: 4px;
            margin-top: 10px;
        }
        
        .ai-schema-comparison-tool {
            background: #fff;
            padding: 10px;
            border: 1px solid #e2e8f0;
            border-radius: 4px;
            margin-bottom: 10px;
            position: relative;
        }
        
        .ai-schema-comparison-tool:last-child {
            margin-bottom: 0;
        }
        
        .ai-schema-remove-tool {
            position: absolute;
            right: 10px;
            top: 10px;
            color: #e53e3e;
            cursor: pointer;
        }
        
        .ai-schema-add-tool {
            background: #4299e1;
            color: white;
            border: none;
            padding: 5px 10px;
            border-radius: 4px;
            cursor: pointer;
            margin-top: 10px;
        }
    </style>
    <?php
    
    // Start meta box output
    echo '<div class="ai-schema-panel">';
    
    // Schema type selector
    echo '<div class="ai-schema-section">';
    echo '<label for="gp_schema_type"><strong>Content Type:</strong></label><br>';
    echo '<select name="gp_schema_type" id="gp_schema_type" style="width:300px; margin-top:5px;">';
    foreach ($schema_types as $value => $label) {
        echo '<option value="' . esc_attr($value) . '" ' . selected($schema_type, $value, false) . '>' . esc_html($label) . '</option>';
    }
    echo '</select>';
    echo '<p class="description">Select the content type that best represents this page or post.</p>';
    echo '</div>';
    
    // Basic options
    echo '<div class="ai-schema-section">';
    echo '<h3>Basic Properties</h3>';
    
    // Description
    $description = isset($schema_options['description']) ? $schema_options['description'] : '';
    echo '<div class="ai-schema-field">';
    echo '<label for="gp_schema_description"><strong>Description:</strong></label><br>';
    echo '<textarea name="gp_schema_options[description]" id="gp_schema_description" style="width:100%; height:60px;">' . esc_textarea($description) . '</textarea>';
    echo '<p class="description">Custom description for search results (defaults to excerpt if left blank)</p>';
    echo '</div>';
    
    // Keywords
    $keywords = isset($schema_options['keywords']) ? $schema_options['keywords'] : '';
    echo '<div class="ai-schema-field">';
    echo '<label for="gp_schema_keywords"><strong>Keywords:</strong></label><br>';
    echo '<input type="text" name="gp_schema_options[keywords]" id="gp_schema_keywords" value="' . esc_attr($keywords) . '" style="width:100%">';
    echo '<p class="description">Comma-separated keywords (e.g., "ChatGPT, AI assistant, language model")</p>';
    echo '</div>';
    
    echo '</div>'; // End basic section
    
    // Type-specific fields
    echo '<div id="ai-schema-type-fields">';
    
    // LearningResource fields
    echo '<div class="ai-schema-type-fields" id="ai-schema-LearningResource-fields" style="display:none;">';
    echo '<div class="ai-schema-section">';
    echo '<h3>Learning Resource Properties</h3>';
    
    // Resource Type
    $learning_type = isset($schema_options['learning_type']) ? $schema_options['learning_type'] : '';
    echo '<div class="ai-schema-field">';
    echo '<label for="gp_schema_learning_type"><strong>Resource Type:</strong></label><br>';
    echo '<select name="gp_schema_options[learning_type]" id="gp_schema_learning_type" style="width:300px;">';
    echo '<option value="" ' . selected($learning_type, '', false) . '>-- Select --</option>';
    echo '<option value="Hub" ' . selected($learning_type, 'Hub', false) . '>Learning Hub</option>';
    echo '<option value="Guide" ' . selected($learning_type, 'Guide', false) . '>Guide</option>';
    echo '<option value="Tutorial" ' . selected($learning_type, 'Tutorial', false) . '>Tutorial</option>';
    echo '<option value="Reference" ' . selected($learning_type, 'Reference', false) . '>Reference</option>';
    echo '</select>';
    echo '</div>';
    
    // Educational Level
    $edu_level = isset($schema_options['educational_level']) ? $schema_options['educational_level'] : '';
    echo '<div class="ai-schema-field">';
    echo '<label for="gp_schema_edu_level"><strong>Educational Level:</strong></label><br>';
    echo '<select name="gp_schema_options[educational_level]" id="gp_schema_edu_level" style="width:300px;">';
    echo '<option value="" ' . selected($edu_level, '', false) . '>-- Select --</option>';
    echo '<option value="Beginner" ' . selected($edu_level, 'Beginner', false) . '>Beginner</option>';
    echo '<option value="Intermediate" ' . selected($edu_level, 'Intermediate', false) . '>Intermediate</option>';
    echo '<option value="Advanced" ' . selected($edu_level, 'Advanced', false) . '>Advanced</option>';
    echo '<option value="All Levels" ' . selected($edu_level, 'All Levels', false) . '>All Levels</option>';
    echo '</select>';
    echo '</div>';
    
    // Learning Time
    $learning_time = isset($schema_options['learning_time']) ? $schema_options['learning_time'] : '';
    echo '<div class="ai-schema-field">';
    echo '<label for="gp_schema_learning_time"><strong>Learning Time (minutes):</strong></label><br>';
    echo '<input type="number" name="gp_schema_options[learning_time]" id="gp_schema_learning_time" value="' . esc_attr($learning_time) . '" min="0" style="width:100px;">';
    echo '<p class="description">Estimated time to complete (in minutes)</p>';
    echo '</div>';
    
    echo '</div>'; // End section
    echo '</div>'; // End LearningResource fields
    
    // AI Comparison fields
    echo '<div class="ai-schema-type-fields" id="ai-schema-AIComparison-fields" style="display:none;">';
    echo '<div class="ai-schema-section">';
    echo '<h3>AI Tool Comparison Properties</h3>';
    
    // Comparison tools container
    echo '<div class="ai-schema-field">';
    echo '<label><strong>Tools Being Compared:</strong></label>';
    echo '<div class="ai-schema-comparison-tools" id="ai-schema-tools-container">';
    
    // Get existing tools
    $comparison_tools = isset($schema_options['comparison_tools']) ? $schema_options['comparison_tools'] : [];
    
    // If no tools yet, add empty one
    if (empty($comparison_tools)) {
        $comparison_tools = [['name' => '', 'url' => '', 'rating' => '', 'description' => '']];
    }
    
    // Tool fields
    foreach ($comparison_tools as $index => $tool) {
        $tool_name = isset($tool['name']) ? $tool['name'] : '';
        $tool_url = isset($tool['url']) ? $tool['url'] : '';
        $tool_rating = isset($tool['rating']) ? $tool['rating'] : '';
        $tool_desc = isset($tool['description']) ? $tool['description'] : '';
        
        echo '<div class="ai-schema-comparison-tool">';
        echo '<span class="ai-schema-remove-tool dashicons dashicons-dismiss" title="Remove"></span>';
        
        echo '<div class="ai-schema-field">';
        echo '<label><strong>Tool Name:</strong></label><br>';
        echo '<input type="text" name="gp_schema_options[comparison_tools][' . $index . '][name]" value="' . esc_attr($tool_name) . '" style="width:100%;">';
        echo '</div>';
        
        echo '<div class="ai-schema-field">';
        echo '<label><strong>URL:</strong></label><br>';
        echo '<input type="url" name="gp_schema_options[comparison_tools][' . $index . '][url]" value="' . esc_attr($tool_url) . '" style="width:100%;">';
        echo '</div>';
        
        echo '<div class="ai-schema-field">';
        echo '<label><strong>Rating (1-5):</strong></label><br>';
        echo '<input type="number" name="gp_schema_options[comparison_tools][' . $index . '][rating]" value="' . esc_attr($tool_rating) . '" min="1" max="5" step="0.1" style="width:100px;">';
        echo '</div>';
        
        echo '<div class="ai-schema-field">';
        echo '<label><strong>Brief Description:</strong></label><br>';
        echo '<textarea name="gp_schema_options[comparison_tools][' . $index . '][description]" style="width:100%; height:40px;">' . esc_textarea($tool_desc) . '</textarea>';
        echo '</div>';
        
        echo '</div>'; // End comparison tool
    }
    
    echo '</div>'; // End tools container
    
    // Add tool button
    echo '<button type="button" class="ai-schema-add-tool" id="ai-schema-add-tool">Add Another Tool</button>';
    echo '</div>'; // End field
    
    echo '</div>'; // End section
    echo '</div>'; // End AIComparison fields
    
    // SoftwareApplication fields
    echo '<div class="ai-schema-type-fields" id="ai-schema-SoftwareApplication-fields" style="display:none;">';
    echo '<div class="ai-schema-section">';
    echo '<h3>AI Software/Tool Properties</h3>';
    
    // Software Category
    $app_category = isset($schema_options['application_category']) ? $schema_options['application_category'] : '';
    echo '<div class="ai-schema-field">';
    echo '<label for="gp_schema_app_category"><strong>Software Category:</strong></label><br>';
    echo '<select name="gp_schema_options[application_category]" id="gp_schema_app_category" style="width:300px;">';
    echo '<option value="" ' . selected($app_category, '', false) . '>-- Select --</option>';
    echo '<option value="ChatApplication" ' . selected($app_category, 'ChatApplication', false) . '>Chat Application</option>';
    echo '<option value="AIContentCreation" ' . selected($app_category, 'AIContentCreation', false) . '>Content Creation</option>';
    echo '<option value="ImageGeneration" ' . selected($app_category, 'ImageGeneration', false) . '>Image Generation</option>';
    echo '<option value="VoiceAssistant" ' . selected($app_category, 'VoiceAssistant', false) . '>Voice Assistant</option>';
    echo '<option value="DataAnalysis" ' . selected($app_category, 'DataAnalysis', false) . '>Data Analysis</option>';
    echo '<option value="DeveloperTool" ' . selected($app_category, 'DeveloperTool', false) . '>Developer Tool</option>';
    echo '<option value="BusinessApplication" ' . selected($app_category, 'BusinessApplication', false) . '>Business Application</option>';
    echo '</select>';
    echo '</div>';
    
    // Application URL
    $app_url = isset($schema_options['application_url']) ? $schema_options['application_url'] : '';
    echo '<div class="ai-schema-field">';
    echo '<label for="gp_schema_app_url"><strong>Application URL:</strong></label><br>';
    echo '<input type="url" name="gp_schema_options[application_url]" id="gp_schema_app_url" value="' . esc_attr($app_url) . '" style="width:100%">';
    echo '</div>';
    
    // Operating System
    $operating_system = isset($schema_options['operating_system']) ? $schema_options['operating_system'] : '';
    echo '<div class="ai-schema-field">';
    echo '<label for="gp_schema_os"><strong>Operating System:</strong></label><br>';
    echo '<input type="text" name="gp_schema_options[operating_system]" id="gp_schema_os" value="' . esc_attr($operating_system) . '" style="width:100%">';
    echo '<p class="description">E.g., "Web-based, Windows, macOS, iOS, Android"</p>';
    echo '</div>';
    
    // Price
    $price = isset($schema_options['price']) ? $schema_options['price'] : '';
    $price_currency = isset($schema_options['price_currency']) ? $schema_options['price_currency'] : 'USD';
    $price_type = isset($schema_options['price_type']) ? $schema_options['price_type'] : '';
    
    echo '<div class="ai-schema-field">';
    echo '<label for="gp_schema_price_type"><strong>Pricing Model:</strong></label><br>';
    echo '<select name="gp_schema_options[price_type]" id="gp_schema_price_type" style="width:300px;">';
    echo '<option value="" ' . selected($price_type, '', false) . '>-- Select --</option>';
    echo '<option value="Free" ' . selected($price_type, 'Free', false) . '>Free</option>';
    echo '<option value="Freemium" ' . selected($price_type, 'Freemium', false) . '>Freemium</option>';
    echo '<option value="Subscription" ' . selected($price_type, 'Subscription', false) . '>Subscription</option>';
    echo '<option value="One-time Purchase" ' . selected($price_type, 'One-time Purchase', false) . '>One-time Purchase</option>';
    echo '</select>';
    echo '</div>';
    
    echo '<div class="ai-schema-field">';
    echo '<label for="gp_schema_price"><strong>Starting Price:</strong></label><br>';
    echo '<input type="text" name="gp_schema_options[price]" id="gp_schema_price" value="' . esc_attr($price) . '" style="width:100px;">';
    echo '<select name="gp_schema_options[price_currency]" style="width:80px; margin-left:5px;">';
    echo '<option value="USD" ' . selected($price_currency, 'USD', false) . '>USD</option>';
    echo '<option value="EUR" ' . selected($price_currency, 'EUR', false) . '>EUR</option>';
    echo '<option value="GBP" ' . selected($price_currency, 'GBP', false) . '>GBP</option>';
    echo '</select>';
    echo '<p class="description">Leave blank for free tools</p>';
    echo '</div>';
    
    // Rating
    $app_rating = isset($schema_options['app_rating']) ? $schema_options['app_rating'] : '';
    echo '<div class="ai-schema-field">';
    echo '<label for="gp_schema_app_rating"><strong>Rating (1-5):</strong></label><br>';
    echo '<input type="number" name="gp_schema_options[app_rating]" id="gp_schema_app_rating" value="' . esc_attr($app_rating) . '" min="1" max="5" step="0.1" style="width:100px;">';
    echo '</div>';
    
    echo '</div>'; // End section
    echo '</div>'; // End SoftwareApplication fields
    
    // TOC Integration option
    echo '<div class="ai-schema-section" id="ai-schema-toc-section">';
    echo '<h3>Table of Contents Integration</h3>';
    
    $include_toc = isset($schema_options['include_toc']) ? $schema_options['include_toc'] : 'auto';
    echo '<div class="ai-schema-field">';
    echo '<label><input type="radio" name="gp_schema_options[include_toc]" value="auto" ' . checked($include_toc, 'auto', false) . '> Automatically detect and include TOC</label><br>';
    echo '<label><input type="radio" name="gp_schema_options[include_toc]" value="always" ' . checked($include_toc, 'always', false) . '> Always include TOC (extract from headings)</label><br>';
    echo '<label><input type="radio" name="gp_schema_options[include_toc]" value="never" ' . checked($include_toc, 'never', false) . '> Never include TOC</label>';
    echo '</div>';
    
    echo '</div>'; // End TOC section
    
    echo '</div>'; // End type-specific fields
    
    echo '</div>'; // End panel
    
    // JavaScript for dynamic form behavior
    ?>
    <script type="text/javascript">
    jQuery(document).ready(function($) {
        // Function to show/hide fields based on schema type
        function toggleSchemaFields() {
            var selectedType = $('#gp_schema_type').val();
            
            // Hide all type-specific fields first
            $('.ai-schema-type-fields').hide();
            
            // Show fields for selected type
            $('#ai-schema-' + selectedType + '-fields').show();
            
            // Special case for TOC section
            if (selectedType === 'Article' || selectedType === 'TechArticle' || 
                selectedType === 'LearningResource' || selectedType === 'HowTo') {
                $('#ai-schema-toc-section').show();
            } else {
                $('#ai-schema-toc-section').hide();
            }
        }
        
        // Handle add new tool button
        $('#ai-schema-add-tool').on('click', function() {
            var toolCount = $('.ai-schema-comparison-tool').length;
            var newTool = $('<div class="ai-schema-comparison-tool"></div>');
            
            newTool.append('<span class="ai-schema-remove-tool dashicons dashicons-dismiss" title="Remove"></span>');
            
            // Tool name
            newTool.append(
                '<div class="ai-schema-field">' +
                '<label><strong>Tool Name:</strong></label><br>' +
                '<input type="text" name="gp_schema_options[comparison_tools][' + toolCount + '][name]" value="" style="width:100%;">' +
                '</div>'
            );
            
            // URL
            newTool.append(
                '<div class="ai-schema-field">' +
                '<label><strong>URL:</strong></label><br>' +
                '<input type="url" name="gp_schema_options[comparison_tools][' + toolCount + '][url]" value="" style="width:100%;">' +
                '</div>'
            );
            
            // Rating
            newTool.append(
                '<div class="ai-schema-field">' +
                '<label><strong>Rating (1-5):</strong></label><br>' +
                '<input type="number" name="gp_schema_options[comparison_tools][' + toolCount + '][rating]" value="" min="1" max="5" step="0.1" style="width:100px;">' +
                '</div>'
            );
            
            // Description
            newTool.append(
                '<div class="ai-schema-field">' +
                '<label><strong>Brief Description:</strong></label><br>' +
                '<textarea name="gp_schema_options[comparison_tools][' + toolCount + '][description]" style="width:100%; height:40px;"></textarea>' +
                '</div>'
            );
            
            $('#ai-schema-tools-container').append(newTool);
        });
        
        // Handle remove tool button
        $(document).on('click', '.ai-schema-remove-tool', function() {
            if ($('.ai-schema-comparison-tool').length > 1) {
                $(this).closest('.ai-schema-comparison-tool').remove();
                
                // Renumber tools
                $('.ai-schema-comparison-tool').each(function(index) {
                    $(this).find('input, textarea').each(function() {
                        var name = $(this).attr('name');
                        name = name.replace(/\[comparison_tools\]\[\d+\]/, '[comparison_tools][' + index + ']');
                        $(this).attr('name', name);
                    });
                });
            } else {
                alert('You need at least one tool for comparison');
            }
        });
        
        // Initial toggle
        toggleSchemaFields();
        
        // Toggle when schema type changes
        $('#gp_schema_type').on('change', toggleSchemaFields);
    });
    </script>
    <?php
}

/**
 * Save meta box data
 */
function gp_child_save_schema_meta($post_id) {
    // Security checks
    if (!isset($_POST['gp_child_schema_nonce']) || 
        !wp_verify_nonce($_POST['gp_child_schema_nonce'], 'gp_child_schema_save') ||
        (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) ||
        !(current_user_can('edit_post', $post_id))) {
        return;
    }
    
    // Save schema type
    if (isset($_POST['gp_schema_type'])) {
        update_post_meta($post_id, '_gp_schema_type', sanitize_text_field($_POST['gp_schema_type']));
    }
    
    // Save schema options
    if (isset($_POST['gp_schema_options']) && is_array($_POST['gp_schema_options'])) {
        $schema_options = [];
        
        // Process basic fields
        foreach ($_POST['gp_schema_options'] as $key => $value) {
            if ($key === 'description') {
                $schema_options[$key] = wp_kses_post($value);
            } elseif ($key === 'comparison_tools' && is_array($value)) {
                // Handle comparison tools array
                foreach ($value as $tool_index => $tool) {
                    if (is_array($tool)) {
                        foreach ($tool as $tool_key => $tool_value) {
                            if ($tool_key === 'description') {
                                $schema_options[$key][$tool_index][$tool_key] = wp_kses_post($tool_value);
                            } else {
                                $schema_options[$key][$tool_index][$tool_key] = sanitize_text_field($tool_value);
                            }
                        }
                    }
                }
            } else {
                // Regular fields
                $schema_options[$key] = sanitize_text_field($value);
            }
        }
        
        update_post_meta($post_id, '_gp_schema_options', $schema_options);
    }
}
add_action('save_post', 'gp_child_save_schema_meta');

/**
 * Generate and output schema markup
 */
function gp_child_apply_schema() {
    // Only apply schema on singular posts and pages
    if (!is_singular(['post', 'page'])) {
        return;
    }
    
    global $post;
    
    // Get schema type and options
    $schema_type = get_post_meta($post->ID, '_gp_schema_type', true);
    $schema_options = get_post_meta($post->ID, '_gp_schema_options', true);
    
    if (empty($schema_type)) {
        $schema_type = 'Article';
    }
    
    if (empty($schema_options) || !is_array($schema_options)) {
        $schema_options = [];
    }
    
    // If schema is disabled, return
    if ($schema_type === 'None') {
        return;
    }
    
    // Get content
    $content = $post->post_content;
    
    // Basic schema data
    $schema = [
        '@context' => 'https://schema.org',
        '@type' => $schema_type,
        'name' => get_the_title(),
        'headline' => get_the_title(),
        'datePublished' => get_the_date('c'),
        'dateModified' => get_the_modified_date('c'),
        'author' => [
            '@type' => 'Person',
            'name' => get_the_author_meta('display_name', $post->post_author)
        ],
        'publisher' => [
            '@type' => 'Organization',
            'name' => get_bloginfo('name'),
            'logo' => [
                '@type' => 'ImageObject',
                'url' => get_site_icon_url()
            ]
        ],
        'mainEntityOfPage' => [
            '@type' => 'WebPage',
            '@id' => get_permalink()
        ]
    ];
    
    // Add description
    if (!empty($schema_options['description'])) {
        $schema['description'] = $schema_options['description'];
    } elseif (has_excerpt()) {
        $schema['description'] = get_the_excerpt();
    }
    
    // Add keywords
    if (!empty($schema_options['keywords'])) {
        $schema['keywords'] = $schema_options['keywords'];
    }
    
    // Add featured image
    if (has_post_thumbnail()) {
        $schema['image'] = get_the_post_thumbnail_url(null, 'full');
    }
    
    // Handle custom schema type: AIComparison
    if ($schema_type === 'AIComparison') {
        // Use Review schema type for comparisons
        $schema['@type'] = 'Review';
        $schema['itemReviewed'] = [
            '@type' => 'SoftwareApplication',
            'applicationCategory' => 'AIApplication',
            'name' => 'AI Tools Comparison'
        ];
        
        // Add compared tools as review items
        if (!empty($schema_options['comparison_tools']) && is_array($schema_options['comparison_tools'])) {
            $schema['reviewedItems'] = [];
            
            foreach ($schema_options['comparison_tools'] as $tool) {
                if (!empty($tool['name'])) {
                    $tool_item = [
                        '@type' => 'SoftwareApplication',
                        'name' => $tool['name']
                    ];
                    
                    if (!empty($tool['url'])) {
                        $tool_item['url'] = $tool['url'];
                    }
                    
                    if (!empty($tool['description'])) {
                        $tool_item['description'] = $tool['description'];
                    }
                    
                    if (!empty($tool['rating'])) {
                        $tool_item['reviewRating'] = [
                            '@type' => 'Rating',
                            'ratingValue' => floatval($tool['rating']),
                            'bestRating' => 5
                        ];
                    }
                    
                    $schema['reviewedItems'][] = $tool_item;
                }
            }
        }
    } elseif ($schema_type === 'LearningResource') {
        // Learning Resource specific schema
        $schema['@type'] = 'LearningResource';
        
        if (!empty($schema_options['learning_type'])) {
            $schema['learningResourceType'] = $schema_options['learning_type'];
        }
        
        if (!empty($schema_options['educational_level'])) {
            $schema['educationalLevel'] = $schema_options['educational_level'];
        }
        
        if (!empty($schema_options['learning_time'])) {
            $schema['timeRequired'] = 'PT' . intval($schema_options['learning_time']) . 'M';
        }
        
        // Add topics covered as keywords if not already set
        if (empty($schema['keywords']) && !empty($schema_options['keywords'])) {
            $schema['keywords'] = $schema_options['keywords'];
        }
        
        } elseif ($schema_type === 'SoftwareApplication') {
            // Software application specifics
            if (!empty($schema_options['application_category'])) {
                $schema['applicationCategory'] = $schema_options['application_category'];
            }
            
            if (!empty($schema_options['operating_system'])) {
                $schema['operatingSystem'] = $schema_options['operating_system'];
            }
            
            if (!empty($schema_options['application_url'])) {
                $schema['url'] = $schema_options['application_url'];
            }
            
            // Add price information
            if (!empty($schema_options['price_type'])) {
                if ($schema_options['price_type'] === 'Free') {
                    $schema['offers'] = [
                        '@type' => 'Offer',
                        'price' => '0',
                        'priceCurrency' => 'USD',
                        'availability' => 'https://schema.org/InStock'
                    ];
                } else {
                    $schema['offers'] = [
                        '@type' => 'Offer',
                        'price' => !empty($schema_options['price']) ? $schema_options['price'] : '0',
                        'priceCurrency' => !empty($schema_options['price_currency']) ? $schema_options['price_currency'] : 'USD',
                        'availability' => 'https://schema.org/InStock'
                    ];
                    
                    // Add pricing model as description
                    if (!empty($schema_options['price_type'])) {
                        $schema['offers']['description'] = $schema_options['price_type'] . ' pricing model';
                    }
                }
            }
            
            // Add rating if provided
            if (!empty($schema_options['app_rating'])) {
                $schema['aggregateRating'] = [
                    '@type' => 'AggregateRating',
                    'ratingValue' => floatval($schema_options['app_rating']),
                    'bestRating' => 5,
                    'worstRating' => 1,
                    'ratingCount' => 1
                ];
            }
        } elseif ($schema_type === 'FAQPage') {
            // FAQPage schema
            $schema = [
                '@context' => 'https://schema.org',
                '@type' => 'FAQPage',
                'mainEntity' => []
            ];
            
            // Extract questions and answers from content
            preg_match_all('/<h([3-4])[^>]*>(.*?)<\/h\1>(.*?)(?=<h[3-4]|$)/is', $content, $matches, PREG_SET_ORDER);
            
            foreach ($matches as $match) {
                $question = strip_tags($match[2]);
                $answer = trim(strip_tags($match[3]));
                
                $schema['mainEntity'][] = [
                    '@type' => 'Question',
                    'name' => $question,
                    'acceptedAnswer' => [
                        '@type' => 'Answer',
                        'text' => $answer
                    ]
                ];
            }
        } elseif ($schema_type === 'HowTo') {
            // HowTo schema
            $schema['step'] = [];
            
            // Extract steps from content
            preg_match_all('/<h([3-4])[^>]*>(.*?)<\/h\1>(.*?)(?=<h[3-4]|$)/is', $content, $matches, PREG_SET_ORDER);
            
            foreach ($matches as $index => $match) {
                $step_name = strip_tags($match[2]);
                $step_text = trim(strip_tags($match[3]));
                
                $schema['step'][] = [
                    '@type' => 'HowToStep',
                    'position' => $index + 1,
                    'name' => $step_name,
                    'text' => $step_text
                ];
            }
        }
        
        // Handle TOC for Article-type schemas
        $article_types = ['Article', 'TechArticle', 'WebPage', 'LearningResource', 'HowTo'];
        $include_toc = isset($schema_options['include_toc']) ? $schema_options['include_toc'] : 'auto';
        
        if (in_array($schema_type, $article_types) && $include_toc !== 'never') {
            $has_toc = false;
            
            // For 'auto' setting, check if TOC is present
            if ($include_toc === 'auto') {
                // Check for TOC block
                if (function_exists('has_block') && has_block('generatepress-child/toc', $content)) {
                    $has_toc = true;
                }
                
                // Check for TOC shortcode
                if (!$has_toc && strpos($content, '[gp_toc') !== false) {
                    $has_toc = true;
                }
                
                // Check for TOC widget
                if (!$has_toc && is_active_widget(false, false, 'gp_child_toc_widget', true)) {
                    $has_toc = true;
                }
            } else {
                // For 'always' setting, always include TOC
                $has_toc = true;
            }
            
            // If TOC should be included, extract headings and add to schema
            if ($has_toc) {
                // Get headings from content
                preg_match_all('/<h([1-6])[^>]*>(.*?)<\/h\1>/i', $content, $matches, PREG_SET_ORDER);
                
                if (!empty($matches)) {
                    $toc_items = [];
                    
                    foreach ($matches as $match) {
                        $heading_text = strip_tags($match[2]);
                        $toc_items[] = $heading_text;
                    }
                    
                    // Add TOC to schema
                    $schema['tableOfContents'] = implode(', ', $toc_items);
                }
            }
        }
        
        // Output schema as JSON-LD
        echo '<script type="application/ld+json">' . wp_json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . '</script>';
        }
        add_action('wp_head', 'gp_child_apply_schema', 10);