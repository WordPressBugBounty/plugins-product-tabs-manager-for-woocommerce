( function ( blocks, element, i18n, blockEditor, components, serverSideRenderPackage ) {
    'use strict';

    var el = element.createElement;
    var __ = i18n.__;
    var useBlockProps = blockEditor.useBlockProps;
    var InspectorControls = blockEditor.InspectorControls;
    var PanelBody = components.PanelBody;
    var ToggleControl = components.ToggleControl;
    var SelectControl = components.SelectControl;
    var Disabled = components.Disabled;
    var ServerSideRender = serverSideRenderPackage && ( serverSideRenderPackage.default || serverSideRenderPackage );

    function placeholderTab( id, title, isActive ) {
        return el(
            'li',
            {
                className: id + '_tab' + ( isActive ? ' active' : '' ),
                id: 'tab-title-' + id,
                key: id,
                role: 'tab',
                'aria-controls': 'tab-' + id,
            },
            el( 'a', { href: '#tab-' + id }, title )
        );
    }

    function placeholderPanel( id, content ) {
        return el(
            'div',
            {
                className: id + '_tab',
                id: 'tab-' + id,
                key: id,
                role: 'tabpanel',
                'aria-labelledby': 'tab-title-' + id,
            },
            content
        );
    }

    function TabsPlaceholder( props ) {
        var tabs = [
            { id: 'description', title: __( 'Description', 'product-tabs-manager-for-woocommerce' ) },
            { id: 'additional_information', title: __( 'Additional Information', 'product-tabs-manager-for-woocommerce' ) },
            { id: 'reviews', title: __( 'Reviews', 'product-tabs-manager-for-woocommerce' ) },
        ];
        var description = el(
            element.Fragment,
            null,
            props.hideTabTitle ? null : el( 'h2', null, __( 'Description', 'product-tabs-manager-for-woocommerce' ) ),
            el(
                'p',
                null,
                __( 'This block lists description, attributes and reviews for a single product.', 'product-tabs-manager-for-woocommerce' )
            )
        );

        return el(
            Disabled,
            null,
            el(
                element.Fragment,
                null,
                el(
                    'ul',
                    { className: 'wc-tabs tabs', role: 'tablist' },
                    tabs.map( function ( tab, index ) {
                        return placeholderTab( tab.id, tab.title, 0 === index );
                    } )
                ),
                tabs.map( function ( tab, index ) {
                    return placeholderPanel( tab.id, 0 === index ? description : null );
                } )
            )
        );
    }

    function accordionPlaceholderItem( props ) {
        var title = el( 'span', { className: 'wp-block-accordion-heading__toggle-title', key: 'title' }, props.title );
        var icon = props.showIcon
            ? el( 'span', { className: 'wp-block-accordion-heading__toggle-icon', key: 'icon', 'aria-hidden': 'true' }, '+' )
            : null;
        var toggleChildren = 'left' === props.iconPosition ? [ icon, title ] : [ title, icon ];
        var panelContent = props.isOpen
            ? el(
                element.Fragment,
                null,
                props.hideTabTitle ? null : el( 'h2', null, props.title ),
                el(
                    'p',
                    null,
                    __( 'This block lists description, attributes and reviews for a single product.', 'product-tabs-manager-for-woocommerce' )
                )
            )
            : null;

        return el(
            'div',
            { className: 'wp-block-accordion-item' + ( props.isOpen ? ' is-open' : '' ), key: props.title },
            el(
                'h' + props.headingLevel,
                { className: 'wp-block-accordion-heading' },
                el( 'button', { className: 'wp-block-accordion-heading__toggle', type: 'button' }, toggleChildren )
            ),
            el( 'div', { className: 'wp-block-accordion-panel' }, panelContent )
        );
    }

    function AccordionPlaceholder( props ) {
        var titles = [
            __( 'Description', 'product-tabs-manager-for-woocommerce' ),
            __( 'Additional Information', 'product-tabs-manager-for-woocommerce' ),
            __( 'Reviews', 'product-tabs-manager-for-woocommerce' ),
        ];

        return el(
            Disabled,
            null,
            el(
                'div',
                { className: 'wp-block-accordion' },
                titles.map( function ( title, index ) {
                    return accordionPlaceholderItem( {
                        title: title,
                        isOpen: 0 === index,
                        hideTabTitle: props.hideTabTitle,
                        showIcon: props.showIcon,
                        iconPosition: props.iconPosition,
                        headingLevel: props.headingLevel,
                    } );
                } )
            )
        );
    }

    function Settings( props ) {
        var attributes = props.attributes;
        var setAttributes = props.setAttributes;

        return el(
            InspectorControls,
            null,
            el(
                PanelBody,
                { title: __( 'Settings', 'product-tabs-manager-for-woocommerce' ) },
                el( SelectControl, {
                    label: __( 'Display style', 'product-tabs-manager-for-woocommerce' ),
                    value: attributes.displayStyle,
                    options: [
                        { label: __( 'Accordion (standard)', 'product-tabs-manager-for-woocommerce' ), value: 'accordion' },
                        { label: __( 'Tabs (classic)', 'product-tabs-manager-for-woocommerce' ), value: 'tabs' },
                    ],
                    onChange: function ( value ) {
                        setAttributes( { displayStyle: value } );
                    },
                } ),
                el( ToggleControl, {
                    label: __( 'Show tab title in content', 'product-tabs-manager-for-woocommerce' ),
                    checked: ! attributes.hideTabTitle,
                    onChange: function ( value ) {
                        setAttributes( { hideTabTitle: ! value } );
                    },
                } )
            ),
            'accordion' === attributes.displayStyle ? el(
                PanelBody,
                { title: __( 'Accordion settings', 'product-tabs-manager-for-woocommerce' ), initialOpen: false },
                el( ToggleControl, {
                    label: __( 'Auto-close', 'product-tabs-manager-for-woocommerce' ),
                    checked: !! attributes.autoclose,
                    help: __( 'Automatically close an open section when another section is opened.', 'product-tabs-manager-for-woocommerce' ),
                    onChange: function ( value ) {
                        setAttributes( { autoclose: value } );
                    },
                } ),
                el( ToggleControl, {
                    label: __( 'Show icon', 'product-tabs-manager-for-woocommerce' ),
                    checked: attributes.showIcon,
                    onChange: function ( value ) {
                        setAttributes( { showIcon: value } );
                    },
                } ),
                attributes.showIcon
                    ? el( SelectControl, {
                        label: __( 'Icon position', 'product-tabs-manager-for-woocommerce' ),
                        value: attributes.iconPosition,
                        options: [
                            { label: __( 'Left', 'product-tabs-manager-for-woocommerce' ), value: 'left' },
                            { label: __( 'Right', 'product-tabs-manager-for-woocommerce' ), value: 'right' },
                        ],
                        onChange: function ( value ) {
                            setAttributes( { iconPosition: value } );
                        },
                    } )
                    : null,
                el( SelectControl, {
                    label: __( 'Heading level', 'product-tabs-manager-for-woocommerce' ),
                    value: String( attributes.headingLevel ),
                    options: [ 2, 3, 4, 5, 6 ].map( function ( level ) {
                        return { label: 'H' + level, value: String( level ) };
                    } ),
                    onChange: function ( value ) {
                        setAttributes( { headingLevel: parseInt( value, 10 ) } );
                    },
                } )
            ) : null
        );
    }

    var blockSupports = {
        align: [ 'wide', 'full' ],
        color: {
            background: true,
            gradients: true
        },
        __experimentalBorder: {
            color: true,
            radius: true,
            style: true,
            width: true
        },
        spacing: {
            padding: true,
            margin: [ 'top', 'bottom' ]
        },
        typography: {
            fontSize: true,
            lineHeight: true
        },
        shadow: true,
        html: false,
        multiple: false
    };

    var blockAttributes = {
        align: {
            type: 'string',
            default: 'wide'
        },
        displayStyle: {
            type: 'string',
            default: 'tabs'
        },
        hideTabTitle: {
            type: 'boolean',
            default: false
        },
        autoclose: {
            type: 'boolean',
            default: false
        },
        showIcon: {
            type: 'boolean',
            default: true
        },
        iconPosition: {
            type: 'string',
            default: 'right'
        },
        headingLevel: {
            type: 'number',
            default: 3
        }
    };

    blocks.registerBlockType( 'berocket/product-details', {
        title: __( 'BeRocket Product Details', 'product-tabs-manager-for-woocommerce' ),
        description: __( 'Display product details and custom tabs managed by BeRocket Product Tab Manager.', 'product-tabs-manager-for-woocommerce' ),
        icon: 'index-card',
        category: 'woocommerce',
        attributes: blockAttributes,
        supports: blockSupports,
        usesContext: [ 'postId', 'postType' ],
        edit: function ( props ) {
            var context = props.context || {};
            var blockProps = useBlockProps( {
                className: 'wp-block-woocommerce-product-details berocket-product-details-editor is-style-minimal',
            } );
            var hasProductContext = 'product' === context.postType && !! context.postId;
            var preview = hasProductContext && ServerSideRender
                ? el( ServerSideRender, {
                    block: 'berocket/product-details',
                    attributes: props.attributes,
                    urlQueryArgs: { post_id: context.postId },
                } )
                : 'accordion' === props.attributes.displayStyle
                    ? el( AccordionPlaceholder, {
                        hideTabTitle: props.attributes.hideTabTitle,
                        showIcon: props.attributes.showIcon,
                        iconPosition: props.attributes.iconPosition,
                        headingLevel: props.attributes.headingLevel,
                    } )
                    : el( TabsPlaceholder, { hideTabTitle: props.attributes.hideTabTitle } );

            return el(
                element.Fragment,
                null,
                el( Settings, props ),
                el( 'div', blockProps, preview )
            );
        },
        save: function () {
            return null;
        },
    } );
} )(
    window.wp.blocks,
    window.wp.element,
    window.wp.i18n,
    window.wp.blockEditor,
    window.wp.components,
    window.wp.serverSideRender
);
