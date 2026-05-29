( function ( blocks, element, blockEditor, components ) {
    var el            = element.createElement;
    var registerBlock = blocks.registerBlockType;
    var useBlockProps = blockEditor.useBlockProps;
    var InspectorControls = blockEditor.InspectorControls;
    var PanelBody     = components.PanelBody;
    var TextControl   = components.TextControl;
    var ToggleControl = components.ToggleControl;

    registerBlock( 'claude-ai-search/search-block', {
        title:       'Claude AI Search',
        description: 'Let visitors ask questions answered from your posts and pages via Claude AI.',
        icon:        'search',
        category:    'widgets',
        keywords:    [ 'ai', 'search', 'claude', 'chat', 'ask' ],

        attributes: {
            placeholder: { type: 'string', default: 'Ask a question about our content...' },
            buttonText:  { type: 'string', default: 'Search' },
            showSources: { type: 'boolean', default: true },
        },

        edit: function ( props ) {
            var attrs      = props.attributes;
            var setAttr    = props.setAttributes;
            var blockProps = useBlockProps( { className: 'claude-ai-search-widget is-editor-preview' } );

            return [
                el( InspectorControls, { key: 'inspector' },
                    el( PanelBody, { title: 'Search Settings', initialOpen: true },
                        el( TextControl, {
                            label:    'Placeholder text',
                            value:    attrs.placeholder,
                            onChange: function ( v ) { setAttr( { placeholder: v } ); },
                        } ),
                        el( TextControl, {
                            label:    'Button label',
                            value:    attrs.buttonText,
                            onChange: function ( v ) { setAttr( { buttonText: v } ); },
                        } ),
                        el( ToggleControl, {
                            label:    'Show source articles below answer',
                            checked:  attrs.showSources,
                            onChange: function ( v ) { setAttr( { showSources: v } ); },
                        } )
                    )
                ),
                el( 'div', blockProps,
                    el( 'div', { className: 'cas-input-row' },
                        el( 'input', {
                            type:        'text',
                            className:   'cas-input',
                            placeholder: attrs.placeholder,
                            disabled:    true,
                        } ),
                        el( 'button', { className: 'cas-button', disabled: true }, attrs.buttonText )
                    ),
                    el( 'p', { className: 'cas-editor-note' },
                        '🤖 Claude AI Search — visitors can ask questions answered from your site content.'
                    )
                )
            ];
        },

        // Server-side rendered — no save needed
        save: function () { return null; },
    } );

} (
    window.wp.blocks,
    window.wp.element,
    window.wp.blockEditor,
    window.wp.components
) );
