<?php

if ( ! function_exists( 'br_generate_product_selector' ) ){
    function br_generate_product_selector($options) {
        $default_options = array(
            'option' => array(),
            'block_name' => '',
            'name' => '',
            'return' => false,
            'action' => 'woocommerce_json_search_products_and_variations',
        );
        $options = array_merge($default_options, $options);
        $html = '<div class="br_search_box" data-function="' . esc_attr($options['block_name']) . '" data-name="' . esc_attr($options['name']) . '">
            <ul class="br_products_search">';
        if( isset($options['option']) && is_array($options['option']) )
            foreach ($options['option'] as $post) {
                $post = absint($post);
                if( ! $post ) {
                    continue;
                }
                $SKU = get_post_meta($post, '_sku', true);
                if (isset($SKU) && $SKU) $SKU = ' (SKU: ' . $SKU . ')';
                $found_products = '#' . $post . $SKU . ' – ' . get_the_title( $post );
                $html .= '<li class="br_products_suggest button"><input data-name="' . esc_attr($options['name']) . '" name="' . esc_attr($options['name']) . '" type="hidden" value="' . esc_attr($post) . '"><i class="fa fa-times"></i>' . esc_html($found_products) . '</li>';
            }
        $html .= '<li class="br_products_suggest_search"><input type="text" data-action="' . esc_attr($options['action']) . '" class="br_search_input" placeholder="Enter 3 or more characters"></li>
            </ul>
        </div>';
        if( $options['return'] ) {
            return $html;
        } else {
            echo $html;
        }
    }
}
