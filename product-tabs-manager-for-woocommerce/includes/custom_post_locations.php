<?php
class BeRocket_conditions_tab_manager extends BeRocket_conditions {
}
class BeRocket_tab_manager_custom_post extends BeRocket_custom_post_class {
    public $hook_name = 'berocket_tab_manager_location_editor';
    public $conditions;
    protected static $instance;
    public static $core_tabs;
    public $post_type_parameters = array(
        'sortable' => true,
        'can_be_disabled' => true
    );
    function __construct() {
        add_action('tab_manager_framework_construct', array($this, 'init_conditions'));
        $this->post_name = 'br_tabs_location';
        $this->post_settings = array(
            'label' => __( 'Locations', 'product-tabs-manager-for-woocommerce' ),
            'labels' => array(
                'name'               => __( 'Locations', 'product-tabs-manager-for-woocommerce' ),
                'singular_name'      => __( 'Location', 'product-tabs-manager-for-woocommerce' ),
                'menu_name'          => _x( 'Locations', 'Admin menu name', 'product-tabs-manager-for-woocommerce' ),
                'add_new'            => __( 'Add Location', 'product-tabs-manager-for-woocommerce' ),
                'add_new_item'       => __( 'Add New Location', 'product-tabs-manager-for-woocommerce' ),
                'edit'               => __( 'Edit', 'product-tabs-manager-for-woocommerce' ),
                'edit_item'          => __( 'Edit Location', 'product-tabs-manager-for-woocommerce' ),
                'new_item'           => __( 'New Location', 'product-tabs-manager-for-woocommerce' ),
                'view'               => __( 'View Locations', 'product-tabs-manager-for-woocommerce' ),
                'view_item'          => __( 'View Location', 'product-tabs-manager-for-woocommerce' ),
                'search_items'       => __( 'Search Locations', 'product-tabs-manager-for-woocommerce' ),
                'not_found'          => __( 'No Locations found', 'product-tabs-manager-for-woocommerce' ),
                'not_found_in_trash' => __( 'No Locations found in trash', 'product-tabs-manager-for-woocommerce' ),
            ),
            'description'     => __( 'This is where you can add tabs to the products/categories/etc.', 'product-tabs-manager-for-woocommerce' ),
            'public'          => true,
            'show_ui'         => true,
            'map_meta_cap'    => true,
            'capability_type' => 'product',
            'publicly_queryable'  => false,
            'exclude_from_search' => true,
            'show_in_menu'        => 'berocket_account',
            'hierarchical'        => false,
            'rewrite'             => false,
            'query_var'           => false,
            'supports'            => array( 'title' ),
            'show_in_nav_menus'   => false,
        );
        $this->default_settings = array(
            "data"     => array(),
            "sortable" => array(
                "description"            => 0,
                "additional_information" => 1,
                "reviews"                => 2
            ),
            'sortable_name'     => array(),
        );

        self::$core_tabs = array(
            'description'            => array( 'id' => 'description',            'type' => 'core', 'title' => __( 'Description', 'product-tabs-manager-for-woocommerce' ) ),
            'additional_information' => array( 'id' => 'additional_information', 'type' => 'core', 'title' => __( 'Additional Information', 'product-tabs-manager-for-woocommerce' ) ),
            'reviews'                => array( 'id' => 'reviews',                'type' => 'core', 'title' => __( 'Reviews (%d)', 'product-tabs-manager-for-woocommerce' ), 'description' => __( 'Use %d in the Title to substitute the number of reviews for the product.', 'product-tabs-manager-for-woocommerce' ) )
        );

        $this->add_meta_box('conditions', __( 'Conditions', 'product-tabs-manager-for-woocommerce' ));
        $this->add_meta_box('settings', __( 'Tabs Manager Settings', 'product-tabs-manager-for-woocommerce' ));
        $this->add_meta_box('description', __( 'Description', 'product-tabs-manager-for-woocommerce' ), false, 'side');
        add_filter('brfr_berocket_tab_manager_location_editor_cp_locations', array($this, 'section_cp_locations'), 10, 4);
        add_filter( 'woocommerce_product_tabs', array( $this, 'woocommerce_product_tabs' ), 29999 );
        remove_filter( 'woocommerce_product_tabs', 'woocommerce_sort_product_tabs', 99 );
        add_action( 'berocket_custom_post_'.$this->post_name.'_wc_save_product_without_check_after', array($this, 'save_product_without_check_after'), 10, 1);
        parent::__construct();
        //Add to product
        add_action( 'add_meta_boxes_product', array($this, 'add_meta_box_product_page'), 1, 1 );
    }
    public function init_conditions() {
        $this->conditions = new BeRocket_conditions_tab_manager($this->post_name.'[data]', $this->hook_name, array(
            'condition_product',
            'condition_product_sale',
            'condition_product_bestsellers',
            'condition_product_totalsales',
        ));
    }
    public function conditions($post) {
        $options = $this->get_option( $post->ID );
        if( berocket_isset($post, 'post_parent') || (! empty($_GET['parent_product']) && intval($_GET['parent_product']) ) ) {
            $parent = (berocket_isset($post, 'post_parent') ? berocket_isset($post, 'post_parent') : intval($_GET['parent_product']));
            $product = wc_get_product($parent);
            if( ! empty($product) && is_a($product, 'WC_Product') ) {
                echo '<h3>
                ' . __('This location added to single product', 'product-tabs-manager-for-woocommerce') . '
                <a target="_blank" href="'.admin_url( 'post.php?post=' . $parent . '&action=edit' ).'">' . get_the_title($parent) . '</a>
                </h3>';
                echo '<input type="hidden" name="post_parent" value="'.$parent.'">';
            } else {
                echo '<h3>';
                _e('This location added to single product, but it seems that your site do not have product with such ID or it is not product', 'product-tabs-manager-for-woocommerce');
                echo '</h3>';
            }
        } else {
            echo $this->conditions->build($options['data']);
        }
    }
    public function settings($post) {
        $options = $this->get_option( $post->ID );
        $BeRocket_tab_manager_var = BeRocket_tab_manager::getInstance();
        echo '<div class="br_framework_settings br_tabs_settings">';
        $BeRocket_tab_manager_var->display_admin_settings(
            array(
                'Locations' => array(
                    'icon' => 'cog',
                ),
            ),
            array(
                'Locations' => array(
                    array(
                        'section' => 'cp_locations'
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

    public function get_custom_posts_frontend($args = array(), $additional = array()) {
        $args = array_merge(array(
            'post_parent' => 0
        ), $args);
        return parent::get_custom_posts_frontend($args, $additional);
    }
    public function wc_save_product_without_check( $post_id, $post ) {
        if( isset($_POST[$this->post_name]) && is_array($_POST[$this->post_name]) ) {
            $BeRocket_tab_manager = BeRocket_tab_manager::getInstance();
            $_POST[$this->post_name] = $BeRocket_tab_manager->recursive_array_set($this->default_settings, $_POST[$this->post_name]);
        }
        if( isset($_POST[$this->post_name][ 'sortable' ]) && is_array($_POST[$this->post_name][ 'sortable' ]) ) {
            asort($_POST[$this->post_name][ 'sortable' ], SORT_NUMERIC);
        }
        parent::wc_save_product_without_check($post_id, $post);
    }
    public function section_cp_locations($html, $item, $options, $name) {
        global $post;
        $html    = '';
        $tabs    = $this->get_all_tabs();
        set_query_var( 'tabs', $tabs );
        set_query_var( 'options', $options );

        ob_start();
        include tab_manager_TEMPLATE_PATH . "tabs.php";
        $html .= ob_get_clean();

        return $html;
    }
    public function get_all_tabs() {
        global $post;

        $tabs           = self::$core_tabs;
        $post_parent_in = array(0);
        $old_post       = $post;

        $BeRocket_tab_manager_product_tab = BeRocket_tab_manager_product_tab::getInstance();
        $br_product_tabs = $BeRocket_tab_manager_product_tab->get_custom_posts();

        foreach($br_product_tabs as $post_slug) {
            $tab_setting = get_post_meta( $post_slug, 'br_product_tab', true );
            $tabs[$post_slug] = array( 'id' => $post_slug, 'type' => 'global', 'title' => get_the_title($post_slug), 'description' => '', 'admin_name' => (empty($tab_setting['admin_name']) ? '' : $tab_setting['admin_name']) );
        }
        if ( is_admin() ) {
            remove_filter( 'woocommerce_product_tabs', 'woocommerce_default_product_tabs' );
            remove_filter( 'woocommerce_product_tabs', 'woocommerce_sort_product_tabs', 99 );
        }
        $products = get_posts(array(
            'post_type' => 'product',
            'posts_per_page' => 1,
            'suppress_filters' => true,
            'fields' => 'ids'
        ));
        if( ! empty($products) && is_array($products) && count($products) > 0 ) {
            $product_id = array_pop($products);
            $product_id = intval($product_id);
            if( $product_id ) {
                $product_element = wc_get_product($product_id);
                $post_element    = get_post($product_id);
                global $product, $post;
                $product         = $product_element;
                $post            = $post_element;
            }
        }
        remove_filter( 'woocommerce_product_tabs', array( $this, 'woocommerce_product_tabs' ), 29999 );
        $third_party_tabs = apply_filters( 'woocommerce_product_tabs', array() );
        add_filter( 'woocommerce_product_tabs', array( $this, 'woocommerce_product_tabs' ), 29999 );
        $third_party_tab_names = array_keys($third_party_tabs);
        update_option('berocket_tab_manager_third_party_tabs', $third_party_tab_names);
        if( is_array($third_party_tabs) ) {
            foreach( $third_party_tabs as $tab => $tab_data ) {
                if( ! isset( $tabs[$tab] ) ) {
                    $tabs[$tab] = array( 'id' => $tab, 'type' => '3party', 'title' => $tab_data['title'], 'description' => '' );
                }
            }
        }
        $post = $old_post;
        return $tabs;
    }

    public function get_default_tabs() {
        $third_party_tabs = get_option('berocket_tab_manager_third_party_tabs');
        if( ! is_array($third_party_tabs) ) {
            $third_party_tabs = array();
        }
        return array_unique(array_merge($third_party_tabs, array( 'description', 'additional_information', 'reviews' )));
    }

    public function woocommerce_product_tabs( $tabs ) {
        $default_tabs = $this->get_default_tabs();
        global $product;
        $product_id = br_wc_get_product_id($product);
        if ( isset($product_id) ) {
            $BeRocket_tab_manager_product_tab = BeRocket_tab_manager_product_tab::getInstance();
            $new_tabs = $this->get_product_tabs( $product_id );
            if( $new_tabs === false ) return $tabs;
            $priority = 10;
            foreach ( $default_tabs as $default_tab_name ) {
                if ( empty( $new_tabs[ $default_tab_name ] ) ) {
                    unset( $tabs[ $default_tab_name ] );
                }
            }

            foreach ( $new_tabs as $tab => &$tab_name ) {
                if ( $tab == 'reviews' ) {
                    $tab_name['title'] = str_replace( '%d', $product->get_review_count(), $tab_name['title'] );
                }

                if ( isset( $tabs[ $tab ] ) ) {
                    $tabs[ $tab ]["priority"] = $priority;
                    $tabs[ $tab ]["title"] = ( empty($tab_name['title']) ? $tabs[ $tab ]["title"] : $tab_name['title'] );
                } else {
                    $tabs[ $tab ] = array(
                        "title"    => $tab_name['title'],
                        "priority" => $priority,
                        "callback" => array( $BeRocket_tab_manager_product_tab, 'get_custom_tab'),
                        "id"       => $tab
                    );
                }

                $priority += 10;
            }
        }

        if ( ! function_exists( '_sort_priority_callback' ) ) {
            function _sort_priority_callback( $a, $b ) {
                if ( $a['priority'] == $b['priority'] )
                    return 0;
                return ( $a['priority'] < $b['priority'] ) ? -1 : 1;
            }
        }

        uasort( $tabs, '_sort_priority_callback' );
        return $tabs;
    }

    public function get_product_tabs( $product_id = 0 ) {
        global $product;

        if ( ! $product_id ) $product_id = $product->get_product_id();

        $posts_array = $this->get_custom_posts_frontend(array('post_parent' => intval($product_id)));

        if( count($posts_array) > 0 ) {
            foreach ( $posts_array as $location ) break;
            return $this->get_tabs_from_location($location);
        }

        $posts_array = $this->get_custom_posts_frontend();
        $tabs        = false;

        foreach ( $posts_array as $location ) {
            $br_location = $this->get_option( $location );

            if ( isset( $br_location[ 'sortable' ] ) and ( ! isset( $br_location[ 'data' ] ) || $this->check_location_in_post( $location, $br_location[ 'data' ], $product ) ) ) {
                $tabs = $this->get_tabs_from_location($location);
                break;
            }
        }

        return $tabs;
    }

    public function get_tabs_from_location($location) {
        $br_location = $this->get_option( $location );
        asort($br_location[ 'sortable' ], SORT_NUMERIC);
        $tabs = array();
        $default_tabs = $this->get_default_tabs();
        foreach ( $br_location[ 'sortable' ] as $tab_id => $tab ) {
            if( ($tab === '' || get_post_status(intval($tab_id)) != 'publish' || has_term('isdisabled', 'berocket_taxonomy_data', intval($tab_id))) && (! in_array($tab_id, $default_tabs) || $tab === '') ) continue;
            $post_slug          = $location;
            $tabs[ (is_numeric($tab_id) ? $tab_id.'_tab' : $tab_id) ] = array(
                'id'          => $post_slug,
                'type'        => 'global',
                'title'       => ( isset( $br_location[ 'sortable_name' ][ $tab_id ] ) ) ? $br_location[ 'sortable_name' ][ $tab_id ] : get_the_title( $tab_id ),
                'description' => '',
            );
        }
        return $tabs;
    }

    public function check_location_in_post( $location_id, $location_data, $product ) {
        $product_id      = br_wc_get_product_id( $product );
        $needed_location = wp_cache_get( 'WC_Product_' . $product_id, 'brptb_' . $location_id );
        if ( $needed_location === false ) {
            $needed_location = BeRocket_conditions_tab_manager::check( $location_data, 'berocket_tab_manager_location_editor', array(
                'product'      => $product,
                'product_id'   => $product_id,
                'product_post' => br_wc_get_product_post( $product ),
                'post_id'      => $product_id
            ) );
            wp_cache_set( 'WC_Product_' . $product_id, ( $needed_location ? 1 : - 1 ), 'brptb_' . $location_id, 60 * 60 );
        } else {
            $needed_location = ( $needed_location == 1 ? true : false );
        }

        return $needed_location;
    }
    public function description($post) {
        ?>
        <p><?php _e('Tab without any condition will be displayed on all products', 'product-tabs-manager-for-woocommerce'); ?></p>
        <p><?php _e('Connection between condition can be AND and OR', 'product-tabs-manager-for-woocommerce'); ?></p>
        <p><strong>AND</strong> <?php _e('is used between condition in one section', 'product-tabs-manager-for-woocommerce'); ?></p>
        <p><strong>OR</strong> <?php _e('is used between different sections with conditions', 'product-tabs-manager-for-woocommerce'); ?></p>
        <?php
    }
    public function save_product_without_check_after($post_id) {
        $post_data = berocket_sanitize_array($_POST[$this->post_name]);
        $options = $this->get_option($post_id);
        if( ! isset($post_data['sortable']) || ! is_array($post_data['sortable']) || ! count($post_data['sortable']) ) {
            $options['sortable'] = array();
            update_post_meta( $post_id, $this->post_name, $options );
        }
    }
    public function manage_edit_columns ( $columns ) {
        $columns = parent::manage_edit_columns($columns);
        $columns = berocket_insert_to_array($columns, 'name', array(
            'parent' => __( "Products", 'product-tabs-manager-for-woocommerce' )
        ));
        return $columns;
    }
    public function columns_replace ( $column ) {
        parent::columns_replace($column);
        global $post;
        $filter = $this->get_option($post->ID);
        switch ( $column ) {
            case "parent":
                if( $parent = berocket_isset($post, 'post_parent') ) {
                    _e('Specific tab for:', 'product-tabs-manager-for-woocommerce');
                    $product = wc_get_product($parent);
                    if( ! empty($product) && is_a($product, 'WC_Product') ) {
                        echo '<br><a target="_blank" href="'.admin_url( 'post.php?post=' . $parent . '&action=edit' ).'">' . get_the_title($parent) . '</a>';
                    } else {
                        echo '<br><strong style="color:red;">' . __('INCORRECT PRODUCT', 'product-tabs-manager-for-woocommerce') . '</strong>';
                    }
                } else {
                    _e('By Conditions', 'product-tabs-manager-for-woocommerce');
                }
                break;
            default:
                break;
        }
    }
    public function add_meta_box_product_page($post) {
        add_meta_box( 'berocket-tab-child', __('Specific Tabs', 'product-tabs-manager-for-woocommerce'), array($this, 'render_meta_box_product_page'), 'product', 'side', 'core' );
    }
    function render_meta_box_product_page($post){
        global $pagenow;
        if( in_array( $pagenow, array( 'post-new.php' ) ) ) {
            _e('Specific Tabs can be added only to created products. Please save products before add it', 'product-tabs-manager-for-woocommerce');
        } else {
            $posts_array = $this->get_custom_posts_frontend(array('post_parent' => intval($post->ID)));
            if( count($posts_array) > 0 ) {
                _e('This product already has specific tabs', 'product-tabs-manager-for-woocommerce');
                foreach($posts_array as $location) {
                    echo '<p><a href="'.admin_url( 'post.php?post='.$location.'&action=edit' ).'">(ID: ' . $location . ') ' . get_the_title($location) . '</a></p>';
                }
            } else {
                _e('This product do not have specific tabs', 'product-tabs-manager-for-woocommerce');
                echo '<p><a href="'.admin_url( 'post-new.php?post_type=br_tabs_location&parent_product=' . $post->ID ).'">'.__('Create Specific Tabs', 'product-tabs-manager-for-woocommerce').'</a></p>';
            }
        }
    }
}
