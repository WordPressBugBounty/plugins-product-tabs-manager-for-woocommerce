<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class BeRocket_Product_Details_Block {
    private static $rendering = false;

    public static function register() {
        if ( ! function_exists( 'register_block_type_from_metadata' ) ) {
            return;
        }

        $block_type = register_block_type_from_metadata( __DIR__ );

        if ( $block_type && function_exists( 'wp_set_script_translations' ) ) {
            foreach ( $block_type->editor_script_handles as $script_handle ) {
                wp_set_script_translations(
                    $script_handle,
                    'product-tabs-manager-for-woocommerce',
                    dirname( BeRocket_tab_manager_file ) . '/languages'
                );
            }
        }
    }

    public static function render( $attributes, $content, $block ) {
        if ( self::$rendering || ! function_exists( 'wc_get_product' ) ) {
            return '';
        }

        $product = self::resolve_product( $block );
        if ( ! $product ) {
            return '';
        }

        $previous_product = isset( $GLOBALS['product'] ) ? $GLOBALS['product'] : null;
        $previous_post    = isset( $GLOBALS['post'] ) ? $GLOBALS['post'] : null;
        $product_post     = get_post( $product->get_id() );

        self::$rendering   = true;
        $GLOBALS['product'] = $product;
        if ( $product_post ) {
            $GLOBALS['post'] = $product_post;
        }

        try {
            $tabs = apply_filters( 'woocommerce_product_tabs', array() );
            if ( ! is_array( $tabs ) ) {
                $tabs = array();
            }

            /**
             * Filters the tabs rendered by the BeRocket Product Details block.
             *
             * @param array      $tabs       Product tabs after WooCommerce compatibility filters.
             * @param WC_Product $product    Product rendered by the block.
             * @param array      $attributes Block attributes.
             * @param WP_Block   $block      Block instance.
             */
            $tabs = apply_filters( 'berocket_product_details_block_tabs', $tabs, $product, $attributes, $block );
            if ( ! is_array( $tabs ) || empty( $tabs ) ) {
                return '';
            }

            $rendered_tabs = self::render_tab_contents( $tabs, ! empty( $attributes['hideTabTitle'] ) );
            if ( empty( $rendered_tabs ) ) {
                return '';
            }

            ob_start();
            do_action( 'woocommerce_product_after_tabs' );
            $after_tabs = ob_get_clean();

            $wrapper_class = 'wp-block-woocommerce-product-details wp-block-berocket-product-details berocket-product-details is-style-minimal';
            $use_accordion = 'accordion' === ( $attributes['displayStyle'] ?? '' ) && self::has_core_accordion();
            if ( ! $use_accordion ) {
                $manager = BeRocket_tab_manager::getInstance();
                $template_class = $manager->get_tabs_template_class();
                if ( $template_class ) {
                    $manager->enqueue_tabs_template_styles();
                    $wrapper_class .= ' ' . $template_class;
                }
            }
            $wrapper_attributes = get_block_wrapper_attributes( array( 'class' => $wrapper_class ) );
            $details = $use_accordion ? self::render_accordion( $rendered_tabs, $attributes ) : self::render_classic_tabs( $rendered_tabs );

            return sprintf(
                '<div %1$s>%2$s%3$s</div>',
                $wrapper_attributes,
                $details,
                $after_tabs
            );
        } finally {
            self::$rendering = false;

            if ( null === $previous_product ) {
                unset( $GLOBALS['product'] );
            } else {
                $GLOBALS['product'] = $previous_product;
            }

            if ( null === $previous_post ) {
                unset( $GLOBALS['post'] );
            } else {
                $GLOBALS['post'] = $previous_post;
            }
        }
    }

    private static function resolve_product( $block ) {
        $product_id = 0;

        if (
            $block instanceof WP_Block
            && ! empty( $block->context['postId'] )
            && ( empty( $block->context['postType'] ) || 'product' === $block->context['postType'] )
        ) {
            $product_id = absint( $block->context['postId'] );
        }

        if ( ! $product_id && isset( $GLOBALS['product'] ) && $GLOBALS['product'] instanceof WC_Product ) {
            return $GLOBALS['product'];
        }

        if ( ! $product_id ) {
            $current_post_id = get_the_ID();
            if ( $current_post_id && 'product' === get_post_type( $current_post_id ) ) {
                $product_id = absint( $current_post_id );
            }
        }

        return $product_id ? wc_get_product( $product_id ) : false;
    }

    private static function render_tab_contents( $tabs, $hide_tab_title = false ) {
        $rendered_tabs = array();

        foreach ( $tabs as $key => $tab ) {
            if ( ! is_array( $tab ) || ! isset( $tab['title'] ) ) {
                continue;
            }

            $tab_key = (string) $key;
            $title   = apply_filters(
                'woocommerce_product_' . $tab_key . '_tab_title',
                $tab['title'],
                $tab_key
            );

            ob_start();
            if ( isset( $tab['callback'] ) && is_callable( $tab['callback'] ) ) {
                call_user_func( $tab['callback'], $tab_key, $tab );
            }
            $tab_content = ob_get_clean();

            if ( $hide_tab_title && class_exists( 'WP_HTML_Tag_Processor' ) ) {
                $tab_html = new WP_HTML_Tag_Processor( $tab_content );
                if ( $tab_html->next_tag( 'H2' ) ) {
                    $tab_html->set_attribute( 'hidden', 'true' );
                    $tab_content = $tab_html->get_updated_html();
                }
            }

            $rendered_tab = array(
                'key'      => $tab_key,
                'title'    => $title,
                'content'  => $tab_content,
                'priority' => isset( $tab['priority'] ) ? intval( $tab['priority'] ) : 10,
                'source'   => $tab,
            );

            /**
             * Filters one tab before the BeRocket Product Details block renders it.
             *
             * Return a non-array value to omit the tab.
             */
            $rendered_tab = apply_filters( 'berocket_product_details_block_tab', $rendered_tab, $tab_key, $tab );
            if ( is_array( $rendered_tab ) ) {
                $rendered_tabs[] = $rendered_tab;
            }
        }

        return $rendered_tabs;
    }

    private static function has_core_accordion() {
        return class_exists( 'WP_Block_Type_Registry' )
            && WP_Block_Type_Registry::get_instance()->is_registered( 'core/accordion' );
    }

    private static function render_accordion( $tabs, $attributes ) {
        $items = '';

        $show_icon     = ! isset( $attributes['showIcon'] ) || (bool) $attributes['showIcon'];
        $icon_position = isset( $attributes['iconPosition'] ) && 'left' === $attributes['iconPosition'] ? 'left' : 'right';
        $heading_level = isset( $attributes['headingLevel'] ) ? intval( $attributes['headingLevel'] ) : 3;
        $heading_level = min( 6, max( 2, $heading_level ) );
        $heading_tag   = 'h' . $heading_level;

        $accordion_attributes = array(
            'metadata'     => array(
                'isDescendantOfProductDetails' => true,
                'berocketProductDetails'       => true,
            ),
            'autoclose'    => ! empty( $attributes['autoclose'] ),
            'showIcon'     => $show_icon,
            'iconPosition' => $icon_position,
            'headingLevel' => $heading_level,
        );

        foreach ( $tabs as $index => $tab ) {
            $item_attributes = array( 'metadata' => array( 'berocketProductDetailsTab' => $tab['key'] ) );
            if ( 0 === $index ) {
                $item_attributes['openByDefault'] = true;
            }

            $title          = '<span class="wp-block-accordion-heading__toggle-title">' . wp_kses_post( $tab['title'] ) . '</span>';
            $icon           = $show_icon ? '<span class="wp-block-accordion-heading__toggle-icon" aria-hidden="true">+</span>' : '';
            $toggle_content = 'left' === $icon_position ? $icon . $title : $title . $icon;

            $items .= sprintf(
                '<!-- wp:accordion-item %1$s -->
                <div class="wp-block-accordion-item">
                    <!-- wp:accordion-heading -->
                    <%2$s class="wp-block-accordion-heading">
                        <button class="wp-block-accordion-heading__toggle">%3$s</button>
                    </%2$s>
                    <!-- /wp:accordion-heading -->

                    <!-- wp:accordion-panel -->
                    <div class="wp-block-accordion-panel">
                        <!-- wp:html -->%4$s<!-- /wp:html -->
                    </div>
                    <!-- /wp:accordion-panel -->
                </div>
                <!-- /wp:accordion-item -->',
                wp_json_encode( $item_attributes ),
                $heading_tag,
                $toggle_content,
                $tab['content']
            );
        }

        $markup = sprintf(
            '<!-- wp:accordion %1$s -->
            <div class="wp-block-accordion">%2$s</div>
            <!-- /wp:accordion -->',
            wp_json_encode( $accordion_attributes ),
            $items
        );

        return do_blocks( $markup );
    }

    private static function render_classic_tabs( $tabs ) {
        if ( function_exists( 'wp_enqueue_script' ) ) {
            wp_enqueue_script( 'wc-single-product' );
        }

        ob_start();
        ?>
        <div class="woocommerce-tabs wc-tabs-wrapper">
            <ul class="tabs wc-tabs" role="tablist">
                <?php foreach ( $tabs as $tab ) :
                    $tab_id = sanitize_title( $tab['key'] );
                    ?>
                    <li role="presentation" class="<?php echo esc_attr( $tab_id ); ?>_tab" id="tab-title-<?php echo esc_attr( $tab_id ); ?>">
                        <a href="#tab-<?php echo esc_attr( $tab_id ); ?>" role="tab" aria-controls="tab-<?php echo esc_attr( $tab_id ); ?>">
                            <?php echo wp_kses_post( $tab['title'] ); ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
            <?php foreach ( $tabs as $tab ) :
                $tab_id = sanitize_title( $tab['key'] );
                ?>
                <div class="woocommerce-Tabs-panel woocommerce-Tabs-panel--<?php echo esc_attr( $tab_id ); ?> panel entry-content wc-tab" id="tab-<?php echo esc_attr( $tab_id ); ?>" role="tabpanel" aria-labelledby="tab-title-<?php echo esc_attr( $tab_id ); ?>">
                    <?php echo $tab['content']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                </div>
            <?php endforeach; ?>
        </div>
        <?php
        return ob_get_clean();
    }
}
