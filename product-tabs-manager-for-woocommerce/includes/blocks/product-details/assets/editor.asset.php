<?php
return array(
    'dependencies' => array(
        'wp-block-editor',
        'wp-blocks',
        'wp-components',
        'wp-element',
        'wp-i18n',
        'wp-server-side-render',
    ),
    'version'      => ( defined( 'BeRocket_tab_manager_version' ) ? BeRocket_tab_manager_version . '-' : '' ) . filemtime( __DIR__ . '/editor.js' ),
);
