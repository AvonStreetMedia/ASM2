/**
 * TOC Block for Gutenberg
 */
(function(blocks, element, blockEditor, components, i18n) {
    var el = element.createElement;
    var __ = i18n.__;
    var InspectorControls = blockEditor.InspectorControls;
    var PanelBody = components.PanelBody;
    var TextControl = components.TextControl;
    var SelectControl = components.SelectControl;
    var RangeControl = components.RangeControl;
    var ToggleControl = components.ToggleControl;
    
    blocks.registerBlockType('generatepress-child/toc', {
        title: __('Table of Contents', 'generatepress-child'),
        icon: 'list-view',
        category: 'widgets',
        
        attributes: {
            style: {
                type: 'string',
                default: 'toc-1',
            },
            title: {
                type: 'string',
                default: __('Table of Contents', 'generatepress-child'),
            },
            minHeaders: {
                type: 'number',
                default: 3,
            },
            maxDepth: {
                type: 'number',
                default: 3,
            },
            numbering: {
                type: 'boolean',
                default: true,
            },
            toggle: {
                type: 'boolean',
                default: true,
            },
        },
        
        edit: function(props) {
            var attributes = props.attributes;
            
            function updateAttribute(key) {
                return function(value) {
                    var newAttributes = {};
                    newAttributes[key] = value;
                    props.setAttributes(newAttributes);
                };
            }
            
            return [
                el(
                    InspectorControls,
                    { key: 'inspector' },
                    el(
                        PanelBody,
                        {
                            title: __('TOC Settings', 'generatepress-child'),
                            initialOpen: true,
                        },
                        el(
                            TextControl,
                            {
                                label: __('TOC Title', 'generatepress-child'),
                                value: attributes.title,
                                onChange: updateAttribute('title'),
                            }
                        ),
                        el(
                            SelectControl,
                            {
                                label: __('Style', 'generatepress-child'),
                                value: attributes.style,
                                options: [
                                    { label: __('Style 1 (Clean)', 'generatepress-child'), value: 'toc-1' },
                                    { label: __('Style 2 (Boxed)', 'generatepress-child'), value: 'toc-2' },
                                    { label: __('Style 3 (Minimal)', 'generatepress-child'), value: 'toc-3' },
                                ],
                                onChange: updateAttribute('style'),
                            }
                        ),
                        el(
                            RangeControl,
                            {
                                label: __('Minimum Headers Required', 'generatepress-child'),
                                value: attributes.minHeaders,
                                min: 1,
                                max: 10,
                                onChange: updateAttribute('minHeaders'),
                            }
                        ),
                        el(
                            RangeControl,
                            {
                                label: __('Maximum Header Depth', 'generatepress-child'),
                                value: attributes.maxDepth,
                                min: 1,
                                max: 6,
                                onChange: updateAttribute('maxDepth'),
                            }
                        ),
                        el(
                            ToggleControl,
                            {
                                label: __('Show Numbering', 'generatepress-child'),
                                checked: attributes.numbering,
                                onChange: updateAttribute('numbering'),
                            }
                        ),
                        el(
                            ToggleControl,
                            {
                                label: __('Allow Toggle (Show/Hide)', 'generatepress-child'),
                                checked: attributes.toggle,
                                onChange: updateAttribute('toggle'),
                            }
                        )
                    )
                ),
                el(
                    'div',
                    { className: 'gp-toc-preview' },
                    el(
                        'div',
                        { className: 'gp-toc-preview-header' },
                        __('Table of Contents', 'generatepress-child'),
                        ' (',
                        attributes.style,
                        ')'
                    ),
                    el(
                        'div',
                        { className: 'gp-toc-preview-settings' },
                        el('p', {}, __('Minimum Headers:', 'generatepress-child') + ' ' + attributes.minHeaders),
                        el('p', {}, __('Maximum Depth:', 'generatepress-child') + ' ' + attributes.maxDepth),
                        el('p', {}, __('Numbering:', 'generatepress-child') + ' ' + (attributes.numbering ? __('Yes', 'generatepress-child') : __('No', 'generatepress-child'))),
                        el('p', {}, __('Toggle:', 'generatepress-child') + ' ' + (attributes.toggle ? __('Yes', 'generatepress-child') : __('No', 'generatepress-child')))
                    ),
                    el(
                        'p',
                        { className: 'gp-toc-preview-note' },
                        __('Table of Contents will be generated automatically based on the headers in your content.', 'generatepress-child')
                    )
                )
            ];
        },
        
        save: function() {
            // Dynamic block, render callback used on server
            return null;
        },
    });
})(
    window.wp.blocks,
    window.wp.element,
    window.wp.blockEditor,
    window.wp.components,
    window.wp.i18n
);