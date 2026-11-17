<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

require_once __DIR__ . '/class-product-details-block.php';

add_action( 'init', array( 'BeRocket_Product_Details_Block', 'register' ) );
