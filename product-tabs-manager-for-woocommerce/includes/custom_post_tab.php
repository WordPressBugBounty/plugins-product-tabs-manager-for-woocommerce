<?php

class BeRocket_tab_manager_product_tab extends BeRocket_custom_post_class {
    public $hook_name = 'berocket_tab_manager_tab_editor';
    public $import_export = array(
        'products' => array(
            'export_type' => 'post',
            'post_type' => array('product', 'product_variation'),
            'multiple' => true,
            'on_missing' => 'remove',
        ),
        'additional_product' => array(
            'products' => array(
                'export_type' => 'post',
                'post_type' => array('product', 'product_variation'),
                'multiple' => true,
                'on_missing' => 'remove',
            ),
            'category' => array(
                'export_type' => 'taxonomy',
                'taxonomy' => 'product_cat',
                'passthrough' => array(''),
                'on_missing' => 'remove',
            ),
        ),
    );
    public $conditions;
    protected static $instance;
    protected $rendering_tabs = array();
    public $post_type_parameters = array(
        'sortable' => false,
        'can_be_disabled' => true
    );
    function __construct() {
        $this->post_name = 'br_product_tab';
        $this->post_settings = array(
            'label' => 'Tabs',
            'labels' => array(
                'name'               => 'Tabs',
                'singular_name'      => 'Tab',
                'menu_name'          => 'Tabs',
                'add_new'            => 'Add Tab',
                'add_new_item'       => 'Add New Tab',
                'edit'               => 'Edit',
                'edit_item'          => 'Edit Tab',
                'new_item'           => 'New Tab',
                'view'               => 'View Tabs',
                'view_item'          => 'View Tab',
                'search_items'       => 'Search Tabs',
                'not_found'          => 'No Tabs found',
                'not_found_in_trash' => 'No Tabs found in trash',
            ),
            'description'     => 'This is where you can add new tabs that you can add to products.',
            'public'          => false,
            'show_ui'         => true,
            'map_meta_cap'    => true,
            'capability_type' => 'product',
            'publicly_queryable'  => false,
            'exclude_from_search' => true,
            'show_in_menu'        => 'berocket_account',
            'hierarchical'        => false,
            'rewrite'             => false,
            'query_var'           => false,
            'supports'            => array( 'title', 'editor' ),
            'show_in_nav_menus'   => false,
        );

        $this->default_settings = array(
            'admin_name'       => '',
            'table_style'      => '',
            'additional'        => '',
            'additional_faq'    => array(),
            'additional_product'=> array(
                'type'              => 'products',
                'products'          => array(),
                'category'          => '',
                'count'             => '4',
            ),
            'condition'     => array(),
            'condition_mode'=> '',
            'products'      => array(),
        );

        add_filter('berocket_custom_post_'.$this->post_name.'_manage_edit_columns', array($this, 'additional_manage_edit_columns'), 1);
        parent::__construct();
    }

    function init_translation() {
        $this->post_settings['label'] = __( 'Tabs', 'product-tabs-manager-for-woocommerce' );
        $this->post_settings['labels'] = array(
            'name'               => __( 'Tabs', 'product-tabs-manager-for-woocommerce' ),
            'singular_name'      => __( 'Tab', 'product-tabs-manager-for-woocommerce' ),
            'menu_name'          => _x( 'Tabs', 'Admin menu name', 'product-tabs-manager-for-woocommerce' ),
            'add_new'            => __( 'Add Tab', 'product-tabs-manager-for-woocommerce' ),
            'add_new_item'       => __( 'Add New Tab', 'product-tabs-manager-for-woocommerce' ),
            'edit'               => __( 'Edit', 'product-tabs-manager-for-woocommerce' ),
            'edit_item'          => __( 'Edit Tab', 'product-tabs-manager-for-woocommerce' ),
            'new_item'           => __( 'New Tab', 'product-tabs-manager-for-woocommerce' ),
            'view'               => __( 'View Tabs', 'product-tabs-manager-for-woocommerce' ),
            'view_item'          => __( 'View Tab', 'product-tabs-manager-for-woocommerce' ),
            'search_items'       => __( 'Search Tabs', 'product-tabs-manager-for-woocommerce' ),
            'not_found'          => __( 'No Tabs found', 'product-tabs-manager-for-woocommerce' ),
            'not_found_in_trash' => __( 'No Tabs found in trash', 'product-tabs-manager-for-woocommerce' ),
        );
        $this->post_settings['description'] = __( 'This is where you can add new tabs that you can add to products.', 'product-tabs-manager-for-woocommerce' );
        $this->add_meta_box('meta_box_settings', __( 'Tab settings', 'product-tabs-manager-for-woocommerce' ));
    }

    public function meta_box_settings( $post ) {
        set_query_var( 'meta_post', $post );
        wp_nonce_field('berocket_product_tab', 'berocket_tab_edit');

        $options              = $this->get_option( $post->ID );
        $BeRocket_tab_manager = BeRocket_tab_manager::getInstance();

        echo '<div class="br_framework_settings br_tab_manager_settings">';
        $BeRocket_tab_manager->display_admin_settings(
            array(
                'General' => array(
                    'icon' => 'inbox',
                ),
            ),
            array(
                'General' => array(
                    'admin_name' => array(
                        "label"    => __('Admin name', 'product-tabs-manager-for-woocommerce'),
                        "name"     => "admin_name",
                        "type"     => "text",
                        "value"    => '',
                    ),
                    'table_style' => array(
                        'label'     => __('Table style', 'product-tabs-manager-for-woocommerce'),
                        'name'      => 'table_style',
                        'type'      => 'checkbox',
                        'tr_class'  => 'br-tab-table-style-option',
                        'value'     => '1',
                        'label_for' => __('Use striped rows, highlighted headers and horizontal scrolling for tables in this tab.', 'product-tabs-manager-for-woocommerce'),
                        'text_after' => '<button type="button" class="button tiny-button br-tab-insert-table" data-table-template="'
                            . esc_attr(wp_json_encode($this->sample_table(), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT)) . '" data-success-message="'
                            . esc_attr__('Sample table added. Edit the example values in the content editor, then save the tab.', 'product-tabs-manager-for-woocommerce') . '">'
                            . esc_html__('Insert sample table', 'product-tabs-manager-for-woocommerce') . '</button>'
                            . '<span class="br-tab-table-status" role="status" aria-live="polite"></span>',
                    ),
                ),
            ),
            array(
                'name_for_filters' => $this->hook_name,
                'hide_header' => true,
                'hide_form' => true,
                'hide_additional_blocks' => true,
                'hide_save_button' => true,
                'settings_name' => $this->post_name,
                'options' => $options
            )
        );
        echo '</div>';
    }

    public function sample_table() {
        return '<table><caption>' . esc_html__('Size guide', 'product-tabs-manager-for-woocommerce') . '</caption>'
            . '<thead><tr><th scope="col">' . esc_html__('Size', 'product-tabs-manager-for-woocommerce') . '</th>'
            . '<th scope="col">' . esc_html__('Chest (cm)', 'product-tabs-manager-for-woocommerce') . '</th>'
            . '<th scope="col">' . esc_html__('Waist (cm)', 'product-tabs-manager-for-woocommerce') . '</th></tr></thead>'
            . '<tbody><tr><th scope="row">S</th><td>90</td><td>76</td></tr>'
            . '<tr><th scope="row">M</th><td>94</td><td>80</td></tr>'
            . '<tr><th scope="row">L</th><td>98</td><td>84</td></tr></tbody></table>';
    }

    public function wc_save_product_without_check( $post_id, $post ) {
        if ( isset( $_POST['br_product_tab'] ) ) {
            $br_product_tab = is_array($_POST['br_product_tab'])
                ? wp_unslash($_POST['br_product_tab'])
                : array();
            $br_product_tab['table_style'] = isset($br_product_tab['table_style']) && '1' === $br_product_tab['table_style']
                ? '1'
                : '';
            if( isset($br_product_tab['admin_name']) ) {
                $br_product_tab['admin_name'] = is_scalar($br_product_tab['admin_name'])
                    ? sanitize_text_field($br_product_tab['admin_name'])
                    : '';
            }
            if( isset($br_product_tab['additional_faq']) && is_array($br_product_tab['additional_faq']) ) {
                foreach($br_product_tab['additional_faq'] as $i => $qa) {
                    $question = isset($qa['q']) && is_scalar($qa['q'])
                        ? sanitize_text_field($qa['q'])
                        : '';
                    $answer = isset($qa['a']) && is_scalar($qa['a'])
                        ? wp_kses_post($qa['a'])
                        : '';
                    if ( '' === $question || '' === $answer ) {
                        unset($br_product_tab['additional_faq'][$i]);
                    } else {
                        $br_product_tab['additional_faq'][$i]['q'] = $question;
                        $br_product_tab['additional_faq'][$i]['a'] = nl2br($answer);
                    }
                }
            }
            if( isset($br_product_tab['additional']) ) {
                $additional = is_string($br_product_tab['additional'])
                    ? sanitize_key($br_product_tab['additional'])
                    : '';
                $br_product_tab['additional'] = in_array($additional, array('', 'faq', 'product_list'), true)
                    ? $additional
                    : '';
            }
            if( isset($br_product_tab['additional_product']) && is_array($br_product_tab['additional_product']) ) {
                $product_settings = $br_product_tab['additional_product'];
                $type = isset($product_settings['type']) && is_string($product_settings['type'])
                    ? sanitize_key($product_settings['type'])
                    : 'products';
                $product_settings['type'] = in_array($type, array('products', 'category'), true)
                    ? $type
                    : 'products';
                $product_settings['products'] = isset($product_settings['products']) && is_array($product_settings['products'])
                    ? array_values(array_unique(array_filter(array_map('absint', array_filter($product_settings['products'], 'is_scalar')))))
                    : array();
                $product_settings['category'] = isset($product_settings['category']) && is_scalar($product_settings['category'])
                    ? absint($product_settings['category'])
                    : 0;
                $product_settings['count'] = isset($product_settings['count']) && is_scalar($product_settings['count'])
                    ? min(100, max(1, absint($product_settings['count'])))
                    : 4;
                $br_product_tab['additional_product'] = $product_settings;
            }
            $_POST['br_product_tab'] = $br_product_tab;
        }
        if( isset($_POST[$this->post_name]) && is_array($_POST[$this->post_name]) ) {
            $BeRocket_tab_manager = BeRocket_tab_manager::getInstance();
            $_POST[$this->post_name] = $BeRocket_tab_manager->recursive_array_set($this->default_settings, $_POST[$this->post_name]);
        }
        parent::wc_save_product_without_check($post_id, $post);
    }
    public function wc_save_check($post_id, $post) {
        if( ! $post || $this->post_name !== $post->post_type ) {
            return false;
        }
        if( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) {
            return false;
        }
        if( ! current_user_can('edit_post', $post_id) ) {
            return false;
        }
        $nonce_name = $this->post_name . '_nonce';
        $nonce = isset($_POST[$nonce_name]) && is_string($_POST[$nonce_name])
            ? sanitize_text_field(wp_unslash($_POST[$nonce_name]))
            : '';
        return wp_verify_nonce($nonce, $this->post_name . '_check');
    }
    public function description($post) {
        ?>
        <p><?php _e('Tab without any condition will be displayed on all products', 'product-tabs-manager-for-woocommerce'); ?></p>
        <p><?php _e('Connection between condition can be AND and OR', 'product-tabs-manager-for-woocommerce'); ?></p>
        <p><strong>AND</strong> <?php _e('is used between condition in one section', 'product-tabs-manager-for-woocommerce'); ?></p>
        <p><strong>OR</strong> <?php _e('is used between different sections with conditions', 'product-tabs-manager-for-woocommerce'); ?></p>
        <?php
    }

    public function get_custom_tab( $id, $tab = array() ) {
        if( is_array($tab) && isset($tab['id']) && is_scalar($tab['id']) ) {
            $id = $tab['id'];
        }
        $id = absint($id);
        if( ! $id || 'br_product_tab' !== get_post_type($id) || 'publish' !== get_post_status($id) ) {
            return;
        }
        $post = get_post( $id );
        if ( $post && ! isset($this->rendering_tabs[$id]) ) {
            $this->rendering_tabs[$id] = true;
            try {
                $this->render_custom_tab($post);
            } finally {
                unset($this->rendering_tabs[$id]);
            }
        }
    }

    protected function render_custom_tab( $post ) {
        global $wp_embed;
        $id = $post->ID;
        $builder_content = '';
        if ( class_exists('\\Elementor\\Plugin') ) {
            $elementor = \Elementor\Plugin::instance();
            if ( $elementor->db->is_built_with_elementor($id) ) {
                // Render this tab's document, without filtering the enclosing product content.
                $builder_content = $elementor->frontend->get_builder_content_for_display($id, true);
            }
        }
        if ( '' !== $builder_content ) {
            $post_content = $builder_content;
        } else {
            $post_content = do_blocks($post->post_content);
            $post_content = $wp_embed->run_shortcode($post_content);
            $post_content = do_shortcode($post_content);
            $post_content = $wp_embed->autoembed($post_content);
            $post_content = wptexturize($post_content);
            $post_content = wpautop($post_content);
            $post_content = shortcode_unautop($post_content);
            $post_content = prepend_attachment($post_content);
            $wp_filter_content_tags = function_exists('wp_filter_content_tags') ? 'wp_filter_content_tags' : 'wp_make_content_images_responsive';
            $post_content = $wp_filter_content_tags($post_content);
            $post_content = convert_smilies($post_content);
        }

        $echo_content = '';
        if ( strpos( $post_content, '\<\!\-\-more\-\-\>' ) !== false ) {
            list( $first_content, $second_content ) = explode( '<!--more-->', $post_content, 2 );
            if ( $second_content = preg_replace("#^\s*\<\/p\>#", "", $second_content) ) {
                $echo_content .= preg_replace("#\<p\>\s*$#", "", $first_content);
                $echo_content .= '<a href="#" class="br_more_content_button" >' . __( 'Read More...', 'product-tabs-manager-for-woocommerce' ) . '</a>';
                $echo_content .= '<div style="display:none;">' . $second_content . '</div>';
                $echo_content .= '
                <script>
                    jQuery(document).on( "click", ".br_more_content_button", function (e) {
                        e.preventDefault();
                        jQuery(this).next().show(0);
                        jQuery(this).hide(0);
                    });
                </script>';
            } else {
                $echo_content .= $post_content;
            }
        } else {
            $echo_content .= $post_content;
        }
        $options = $this->get_option($id);
        if( '1' === $options['table_style'] ) {
            wp_enqueue_script(
                'berocket_tab_manager_table_style',
                plugins_url('js/table-style.js', BeRocket_tab_manager_file),
                array(),
                BeRocket_tab_manager_version,
                true
            );
            $echo_content = '<div class="br-tab-table-style" data-scroll-label="'
                . esc_attr__('Scrollable table', 'product-tabs-manager-for-woocommerce') . '">'
                . $echo_content . '</div>';
        }
        $echo_content = apply_filters('berocket_get_custom_tab_echo_content', $echo_content, $id);
        echo $echo_content;
    }
    public function additional_manage_edit_columns ( $columns ) {
        $columns["admin_name"] = __( "Admin name", 'product-tabs-manager-for-woocommerce' );
        return $columns;
    }
    public function columns_replace ( $column ) {
        global $post;
        $options = $this->get_option($post->ID);
        switch ( $column ) {
            case "admin_name":

                $edit_link = get_edit_post_link( $post->ID );
                $title = '<a class="row-title" href="' . esc_url($edit_link) . '">' . esc_html(br_get_value_from_array($options, array('admin_name'))) . '</a>';

                echo '<strong>' . $title . '</strong>';

                break;
            default:
                break;
        }
        parent::columns_replace($column);
    }
    public function update_from_older() {
        $posts = $this->get_custom_posts(array('meta_query' => array(
            array(
                'key'     => 'br_tabs',
                'compare' => 'EXISTS',
            ),
        )));
        foreach($posts as $post) {
            $options     = $this->get_option($post);
            $options_old = get_post_meta( $post, 'br_tabs', true );
            if( ! empty($options_old) && is_array($options_old) ) {
                $options = array_merge($options, $options_old);
                $options = BeRocket_Framework::recursive_array_set($this->default_settings, $options);
                update_post_meta( $post, $this->post_name, $options );
                delete_post_meta($post, 'br_tabs');
            }
        }
    }
}
