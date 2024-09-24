<?php
define( "BeRocket_tab_manager_domain", 'product-tabs-manager-for-woocommerce' );
define( "tab_manager_TEMPLATE_PATH", plugin_dir_path( __FILE__ ) . "templates/" );
load_plugin_textdomain('product-tabs-manager-for-woocommerce', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/');
require_once( plugin_dir_path( __FILE__ ) . 'berocket/framework.php' );
foreach ( glob( __DIR__ . "/includes/*.php") as $filename ) {
    include_once( $filename );
}
foreach ( glob( plugin_dir_path( __FILE__ ) . "includes/compatibility/*.php" ) as $filename ) {
    include_once( $filename );
}

class BeRocket_tab_manager extends BeRocket_Framework {
    public static $settings_name = 'br-tab_manager-options';
    protected static $instance;
    public $info, $defaults, $values;
    protected $disable_settings_for_admin = array(
        array('script', 'js_page_load'),
    );

    function __construct () {
        $this->info = array(
            'id'          => 10,
            'lic_id'      => 19,
            'version'     => BeRocket_tab_manager_version,
            'plugin'      => '',
            'slug'        => '',
            'key'         => '',
            'name'        => '',
            'plugin_name' => 'tab_manager',
            'full_name'   => 'WooCommerce Product Tab Manager',
            'norm_name'   => 'Tab Manager',
            'price'       => '24',
            'domain'      => 'product-tabs-manager-for-woocommerce',
            'templates'   => tab_manager_TEMPLATE_PATH,
            'plugin_file' => BeRocket_tab_manager_file,
            'plugin_dir'  => __DIR__,
        );

        $this->defaults = array(
            'custom_css'        => '',
            'script'            => array(
                'js_page_load'      => '',
            ),
            'use_cat_tabs'      => array(),
            'styles'            => array(
                'question_size'     => '',
                'answer_size'       => '',
                'border_color'      => 'aaaaaa',
                'question_color'    => 'eeeeee',
                'q_opened_color'    => '',
            ),
            'fontawesome_frontend_disable'    => '',
            'fontawesome_frontend_version'    => '',
        );

        $this->values = array(
            'settings_name' => 'br-tab_manager-options',
            'option_page'   => 'br_tab_manager',
            'premium_slug'  => 'woocommerce-product-tabs-manager',
            'free_slug'     => 'product-tabs-manager-for-woocommerce',
            'hpos_comp'     => true
        );

        if ( $this->init_validation() ) {
            new BeRocket_tab_manager_product_tab();
            new BeRocket_tab_manager_custom_post();
        }
        $this->framework_data['fontawesome_frontend'] = true;
        parent::__construct( $this );
        if( $this->init_validation() ) {
            add_action('wp_enqueue_scripts', array( $this, 'enqueue_scripts' ));
            add_filter ( 'BeRocket_updater_menu_order_custom_post', array( $this, 'menu_order_custom_post' ) );
            add_action('berocket_tabs_cron_func', array($this, 'cron_func'));
        }
    }

    public function init_validation() {
        return ( ( is_plugin_active( 'woocommerce/woocommerce.php' ) || is_plugin_active_for_network( 'woocommerce/woocommerce.php' ) ) &&
                 br_get_woocommerce_version() >= 2.1 );
    }

    public function page_load_script() {
        $options = $this->get_option();
        if( ! empty($options['script']['js_page_load']) ) {
            echo '<script>jQuery(document).ready(function(){', $options['script']['js_page_load'], '});</script>';
        }
    }

    public function enqueue_scripts() {
        if ( is_product() ) {
            wp_register_style('berocket_tab_manager_frontend_style', plugins_url( 'css/frontend.css', __FILE__ ), "",
                $this->info[ 'version' ]
            );
            wp_enqueue_style( 'berocket_tab_manager_frontend_style' );
            add_action('wp_footer', array($this, 'page_load_script'));
        }
    }

    public function menu_order_custom_post($compatibility) {
        $compatibility['br_product_tab']   = 'br_tab_manager';
        $compatibility['br_tabs_location'] = 'br_tab_manager';
        return $compatibility;
    }

    public function admin_init() {
        parent::admin_init();
        wp_enqueue_script( 'berocket_tab_manager', plugins_url( 'js/admin.js', __FILE__ ), array( 'jquery' ), BeRocket_tab_manager_version );
        wp_register_style( 'berocket_tab_manager_admin_style', plugins_url( 'css/admin.css', __FILE__ ), "", BeRocket_tab_manager_version );
        wp_enqueue_style( 'berocket_tab_manager_admin_style' );
        $this->update_from_older();
    }
    public function update_from_older() {
        $BeRocket_tab_manager_custom_post   = BeRocket_tab_manager_custom_post::getInstance();
        $options                            = $this->get_option();
        $sortable                           = br_get_value_from_array($options, 'sortable');
        $sortable_name                      = br_get_value_from_array($options, 'sortable_name');
        $has_prev_version = false;
        if( is_array($sortable) && count($sortable) ) {
            if( ! is_array($sortable_name) ) {
                $sortable_name = array();
            }
            $create_new_post_id = $BeRocket_tab_manager_custom_post->create_new_post(
                array('post_title' => 'Global'), 
                array(
                    'data'          => array(),
                    'sortable'      => $sortable,
                    'sortable_name' => $sortable_name
                )
            );
            update_post_meta($create_new_post_id, 'berocket_post_order', 10);
            $has_prev_version = true;
        }
        if( ! empty($options['use_cat_tabs']) && is_array($options['use_cat_tabs']) ) {
            foreach($options['use_cat_tabs'] as $cat_tab => $is_used_tab) {
                if( $is_used_tab ) {
                    $sortable = br_get_value_from_array($options, $cat_tab);
                    $sortable_name = br_get_value_from_array($options, $cat_tab);
                    if( is_array($sortable) && count($sortable) ) {
                        if( ! is_array($sortable_name) ) {
                            $sortable_name = array();
                        }
                        $cat_id = intval(str_replace('sortable_', '', $cat_tab));
                        $cat_term = get_term($cat_id, 'product_cat');
                        $BeRocket_tab_manager_custom_post->create_new_post(
                            array('post_title' => 'Category: '.$cat_term->name), 
                            array(
                                'data'          => array(
                                    1 => array(
                                        1 => array(
                                            'type'  => 'category',
                                            'equal' => 'equal',
                                            'category' => array(
                                                $cat_id
                                            )
                                        )
                                    )
                                ),
                                'sortable'      => $sortable,
                                'sortable_name' => $sortable_name
                            )
                        );
                    }
                }
            }
            $has_prev_version = true;
        }
        if( isset($options['sortable']) ) {
            unset($options['sortable']);
        }
        if( isset($options['use_cat_tabs']) ) {
            unset($options['use_cat_tabs']);
        }
        update_option($this->values[ 'settings_name' ], $options);
        $iscron = get_option('berocket_tabs_cron_enabled');
        if( ! $iscron || $iscron == 1 ) {
            $posts = get_posts(array(
                'post_type'         => 'product',
                'fields'            => 'ids',
                'posts_per_page'    => 10,
                'meta_query'        => array(
                    array(
                        'key'     => 'br_use_specific_tabs',
                        'value'   => array( '1' ),
                        'compare' => 'IN',
                    ),
                )
            ));
            if( ! $iscron ) {
                if( is_array($posts) && count($posts) ) {
                    $has_prev_version = true;
                    update_option('berocket_tabs_cron_enabled', 1);
                    wp_schedule_single_event( time(), 'berocket_tabs_cron_func', array(time()) );
                } else {
                    update_option('berocket_tabs_cron_enabled', 2);
                }
            } elseif( $iscron == 1 && is_array($posts) && count($posts) ) {
                add_filter( 'berocket_display_additional_notices', array(
                    $this,
                    'cron_is_running'
                ) );
            }
        }
        $BeRocket_tab_manager_product_tab = BeRocket_tab_manager_product_tab::getInstance();
        $posts = $BeRocket_tab_manager_product_tab->update_from_older();
    }
    function cron_is_running($notices) {
        $notices[] = array(
            'start'         => 0,
            'end'           => 0,
            'name'          => $this->info[ 'plugin_name' ].'_cron_run',
            'html'          => __('<strong>WooCommerce Product Tab Manager plugin replacing tabs inside products with new tab locations</strong>', 'product-tabs-manager-for-woocommerce'),
            'righthtml'     => '',
            'rightwidth'    => 0,
            'nothankswidth' => 0,
            'contentwidth'  => 1600,
            'subscribe'     => false,
            'priority'      => 10,
            'height'        => 50,
            'repeat'        => false,
            'repeatcount'   => 1,
            'image'         => array(
                'local'  => '',
                'width'  => 0,
                'height' => 0,
                'scale'  => 1,
            )
        );
        return $notices;
    }
    public function cron_func() {
        $BeRocket_tab_manager_custom_post = BeRocket_tab_manager_custom_post::getInstance();
        $posts = get_posts(array(
            'post_type'         => 'product',
            'fields'            => 'ids',
            'posts_per_page'    => 10,
            'meta_query'        => array(
                array(
                    'key'     => 'br_use_specific_tabs',
                    'value'   => array( '1' ),
                    'compare' => 'IN',
                ),
            )
        ));
        if( is_array($posts) && count($posts) ) {
            foreach($posts as $post_id) {
                $br_use_specific_tabs = get_post_meta($post_id, 'br_use_specific_tabs', true);
                if( ! empty($br_use_specific_tabs) ) {
                    $post_data = get_post_meta($post_id, 'br-tab_manager-options', true);
                    $same_posts = get_posts(array(
                        'post_type' => 'product',
                        'fields'    => 'ids',
                        'meta_query'=> array(
                            array(
                                'key'     => 'br_use_specific_tabs',
                                'value'   => array( '1' ),
                                'compare' => 'IN',
                            ),
                            array(
                                'key'     => 'br-tab_manager-options',
                                'value'   => array( maybe_serialize($post_data) ),
                                'compare' => 'IN',
                            ),
                        )
                    ));
                    if( is_array($post_data) && ! empty($post_data['sortable']) && is_array($post_data['sortable']) ) {
                        $post_data['data'] = array(
                            1 => array(
                                1 => array(
                                    'type'  => 'product',
                                    'equal' => 'equal',
                                    'product' => $same_posts
                                )
                            )
                        );
                        $BeRocket_tab_manager_custom_post->create_new_post(
                            array('post_title' => 'Product: '.implode(', ', $same_posts)), 
                            $post_data
                        );
                    }
                    foreach($same_posts as $same_post) {
                        delete_post_meta($same_post, 'br_use_specific_tabs');
                        delete_post_meta($same_post, 'br-tab_manager-options');
                    }
                }
            }
            wp_schedule_single_event( time(), 'berocket_tabs_cron_func', array(time()) );
        } else {
            update_option('berocket_tabs_cron_enabled', 2);
        }
    }
    public function admin_settings( $tabs_info = array(), $data = array() ) {
        parent::admin_settings(
            array(
                'General' => array(
                    'icon' => 'cog',
                    'name' => __('General', 'product-tabs-manager-for-woocommerce'),
                ),
                'CSS/JavaScript'     => array(
                    'icon' => 'css3',
                    'name' => __('CSS/JavaScript', 'product-tabs-manager-for-woocommerce'),
                ),
                'Tabs' => array(
                    'icon' => 'plus-square',
                    'link' => admin_url( 'edit.php?post_type=br_product_tab' ),
                    'name' => __('Tabs', 'product-tabs-manager-for-woocommerce'),
                ),
                'Locations' => array(
                    'icon' => 'plus-square',
                    'link' => admin_url( 'edit.php?post_type=br_tabs_location' ),
                    'name' => __('Locations', 'product-tabs-manager-for-woocommerce'),
                ),
                'License' => array(
                    'icon' => 'unlock-alt',
                    'link' => admin_url( 'admin.php?page=berocket_account' ),
                    'name' => __('License', 'product-tabs-manager-for-woocommerce'),
                ),
            ),
            array(
                'General'  => array(
                    'explanation' => array(
                        "section"  => "explanation",
                    ),
                ),
                'CSS/JavaScript'     => array(
                    'global_font_awesome_disable' => array(
                        "label"     => __( 'Disable Font Awesome', "product-tabs-manager-for-woocommerce" ),
                        "type"      => "checkbox",
                        "name"      => "fontawesome_frontend_disable",
                        "value"     => '1',
                        'label_for' => __('Don\'t loading css file for Font Awesome on site front end. Use it only if you doesn\'t uses Font Awesome icons in widgets or you have Font Awesome in your theme.', 'product-tabs-manager-for-woocommerce'),
                    ),
                    'global_fontawesome_version' => array(
                        "label"    => __( 'Font Awesome Version', "product-tabs-manager-for-woocommerce" ),
                        "name"     => "fontawesome_frontend_version",
                        "type"     => "selectbox",
                        "options"  => array(
                            array('value' => '', 'text' => __('Font Awesome 4', 'product-tabs-manager-for-woocommerce')),
                            array('value' => 'fontawesome5', 'text' => __('Font Awesome 5', 'product-tabs-manager-for-woocommerce')),
                        ),
                        "value"    => '',
                        "label_for" => __('Version of Font Awesome that will be used on front end. Please select version that you have in your theme', 'product-tabs-manager-for-woocommerce'),
                    ),
                    array(
                        "type"  => "textarea",
                        "label" => __('Custom CSS', 'product-tabs-manager-for-woocommerce'),
                        "name"  => "custom_css",
                        "class" => "berocket_custom_css",
                    ),
                    array(
                        "type"      => "textarea",
                        "label"     => __('JavaScript On Page Load', 'product-tabs-manager-for-woocommerce'),
                        "name"      => array("script", "js_page_load"),
                        "value"     => "",
                        "class"     => "berocket_custom_javascript",
                    ),
                ),
            )
        );
    }
    function section_explanation() {
        $html ='<td colspan="2">';
        $html .= '<ol>';
        $html .= '<li>
        Create custom tabs that you need for use it in products
        <p><video autoplay muted loop>
            <source src="'.plugins_url( 'guide/tabs.mp4', __FILE__ ).'">
        </video></p>
        <br><a class="button" href="'.admin_url( 'edit.php?post_type=br_product_tab' ).'">Custom Tabs</a>
        </li>';
        $html .= '<li>
        Create some location for tabs with tab list that you need
        <p><video autoplay muted loop>
            <source src="'.plugins_url( 'guide/location.mp4', __FILE__ ).'">
        </video></p>
        <br><a class="button" href="'.admin_url( 'edit.php?post_type=br_tabs_location' ).'">Tabs Locations</a>
        </li>';
        $html .= '</ol>';
        $html .= '</td>';
        return $html;
    }
}

new BeRocket_tab_manager;
