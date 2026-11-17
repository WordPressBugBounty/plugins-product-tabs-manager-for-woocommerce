<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

echo BeRocket_Product_Details_Block::render( $attributes, $content, $block ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
