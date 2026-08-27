<div id="br_tab_manager" class="panel wc-metaboxes-wrapper br_specific_tabs_div">
    <div style="padding:1em;">
        <?php
        if( !isset($fields_name) ) {
            $fields_name = 'sortable';
        }
        $randid = wp_rand();
        $product_tabs = false;
        $sortable = ( empty($options[$fields_name]) ? NULL : $options[$fields_name] );
        $sortable_name = ( empty($options[$fields_name.'_name']) ? NULL : $options[$fields_name.'_name'] );
        ?>
        <div class="br_tab_manager_tab_editor">
            <div id="br_tab_manager_sortable-<?php echo esc_attr($randid); ?>" class="br-tab_manager-sortable">
                <?php
                $tab_html = array();
                foreach( $tabs as $tab => $tabs_data ) {
                    $tab = (string) $tab;
                    $tab_title = empty($tabs_data['admin_name']) ? $tabs_data['title'] : $tabs_data['admin_name'];
                    $edit = '';
                    if( $tabs_data['type'] == 'global' ) {
                        $edit_link = get_edit_post_link( $tabs_data['id'] );
                        $edit = '<div><a class="button tiny-button" target="_blank" href="' . esc_url($edit_link) . '">' . esc_html__('Edit', 'woocommerce') . '</a></div>';
                    }
                    $tab_name = 'br_tabs_location[' . $fields_name . '][' . $tab . ']';
                    $tab_title_name = 'br_tabs_location[' . $fields_name . '_name][' . $tab . ']';
                    $custom_title = isset($sortable_name[$tab]) && '' !== $sortable_name[$tab]
                        ? $sortable_name[$tab]
                        : $tab_title;
                    $tab_html[$tab] = '<div class="br-tab_manager-element br-element-' . esc_attr($tab) . '">
                        <input type="hidden" name="' . esc_attr($tab_name) . '" value="">
                        <div class="br-tab_manager-header">
                            <h3>' . esc_html($tab_title) . '</h3>
                            <span class="br-show_next_hidden"><i class="fa fa-caret-down"></i></span>
                            <span class="br-remove_tab button tiny-button">' . esc_html__('Remove', 'product-tabs-manager-for-woocommerce') . '</span>
                        </div>
                        <div class="br_hidden br_display_none">
                        <h2>' . esc_html__('Title:', 'product-tabs-manager-for-woocommerce') . ' ' . ( $tabs_data['type'] == 'core' ? '<input name="' . esc_attr($tab_title_name) . '" type="text" value="' . esc_attr($custom_title) . '">' : esc_html($tab_title) ) . '</h2>
                        ' . $edit . '
                        <div>' . ( isset($tabs_data['description']) ? wp_kses_post($tabs_data['description']) : '' ) . '</div>
                        </div>
                    </div>';
                }
                if( ! empty($sortable) && is_array($sortable) ) {
                    asort($sortable, SORT_NUMERIC);
                    foreach( $sortable as $tab => $position ) {
                        if( isset($tabs[$tab]) && is_numeric($position) ) {
                            echo $tab_html[$tab];
                        }
                    }
                }
                ?>
            </div>
            <div>
                <select class="br-add-tab-select">
                    <?php
                    foreach ($tabs as $tab => $tab_data) {
                        echo '<option value="', esc_attr($tab), '">', esc_html(empty($tab_data['admin_name']) ? $tab_data['title'] : $tab_data['admin_name']), '</option>';
                    }
                    ?>
                </select>
                <script>var $tab_html<?php echo absint($randid); ?> = <?php echo wp_json_encode($tab_html, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;</script>
                <button type="button" class="button button-primary br-add-tab tiny-button" data-randid="<?php echo esc_attr($randid); ?>"><?php esc_html_e('Add Tab', 'product-tabs-manager-for-woocommerce'); ?></button>
                <a class="button tiny-button" href="<?php echo esc_url(admin_url('post-new.php?post_type=br_product_tab')); ?>">
                    <?php esc_html_e('Create new tab', 'product-tabs-manager-for-woocommerce'); ?>
                </a>
            </div>
        </div>
        <script>
            jQuery(function() {
            jQuery( "#br_tab_manager_sortable-<?php echo absint($randid); ?>" ).sortable({
                    axis: "y",
                    helper: "clone",
                    opacity: 0.5,
                    handle: ".br-tab_manager-header h3",
                    stop: function( event, ui ) {
                        jQuery('#br_tab_manager_sortable-<?php echo absint($randid); ?> div input[type=hidden]').each(function(i, o) {
                            jQuery(o).val(i);
                        });
                    }
                });
                jQuery('#br_tab_manager_sortable-<?php echo absint($randid); ?> div input[type=hidden]').each(function(i, o) {
                    jQuery(o).val(i);
                });
            });
        </script>
    </div>
</div>
<?php
$fields_name = 'sortable';
set_query_var( 'fields_name', $fields_name );
?>
