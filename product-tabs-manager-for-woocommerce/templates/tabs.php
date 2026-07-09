<div id="br_tab_manager" class="panel wc-metaboxes-wrapper br_specific_tabs_div">
    <div style="padding:1em;">
        <?php
        if( !isset($fields_name) ) {
            $fields_name = 'sortable';
        }
        $randid = rand();
        $product_tabs = false;
        $sortable = ( empty($options[$fields_name]) ? NULL : $options[$fields_name] );
        $sortable_name = ( empty($options[$fields_name.'_name']) ? NULL : $options[$fields_name.'_name'] );
        ?>
        <div class="br_tab_manager_tab_editor">
            <div id="br_tab_manager_sortable-<?php echo $randid; ?>" class="br-tab_manager-sortable">
                <?php
                $tab_html = array();
                foreach( $tabs as $tab => $tabs_data ) {
                    $edit = '';
                    if( $tabs_data['type'] == 'global' ) {
                        $edit_link = get_edit_post_link( $tabs_data['id'] );
                        $edit = '<div><a class="button tiny-button" target="_blank" href="' . $edit_link . '">' . __('Edit', 'woocommerce') . '</a></div>';
                    }
                    $tab_html[$tab] = '<div class="br-tab_manager-element br-element-'. $tab. '">
                        <input type="hidden" name="br_tabs_location['.$fields_name.']['. $tab. ']" value="">
                        <div class="br-tab_manager-header">
                            <h3>'. (empty($tabs_data['admin_name']) ? $tabs_data['title'] : $tabs_data['admin_name']). '</h3>
                            <span class="br-show_next_hidden"><i class="fa fa-caret-down"></i></span>
                            <span class="br-remove_tab button tiny-button">'. __('Remove', 'product-tabs-manager-for-woocommerce'). '</span>
                        </div>
                        <div class="br_hidden br_display_none">
                        <h2>Title: '. ( $tabs_data['type'] == 'core' ? '<input name="br_tabs_location['.$fields_name.'_name]['. $tab. ']" type="text" value="'. ( ( isset($sortable_name[$tab]) && $sortable_name[$tab] != '' ) ? $sortable_name[$tab] : (empty($tabs_data['admin_name']) ? $tabs_data['title'] : $tabs_data['admin_name']) ). '">' : (empty($tabs_data['admin_name']) ? $tabs_data['title'] : $tabs_data['admin_name']) ). '</h2>
                        ' . $edit . '
                        <div>'.( isset($tabs_data['description']) ? $tabs_data['description'] : '' ).'</div>
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
                        echo '<option value="', $tab, '">', (empty($tab_data['admin_name']) ? $tab_data['title'] : $tab_data['admin_name']), '</option>';
                    }
                    ?>
                </select>
                <script>var $tab_html<?php echo $randid;?> = <?php echo json_encode($tab_html); ?>;</script>
                <button type="button" class="button button-primary br-add-tab tiny-button" data-randid="<?php echo $randid; ?>">Add Tab</button>
                <a class="button tiny-button" href="<?=admin_url( 'post-new.php?post_type=br_product_tab' )?>">
                    <?php _e('Create new tab', 'product-tabs-manager-for-woocommerce') ?>
                </a>
            </div>
        </div>
        <script>
            jQuery(function() {
                jQuery( "#br_tab_manager_sortable-<?php echo $randid; ?>" ).sortable({
                    axis: "y",
                    helper: "clone",
                    opacity: 0.5,
                    handle: ".br-tab_manager-header h3",
                    stop: function( event, ui ) {
                        jQuery('#br_tab_manager_sortable-<?php echo $randid; ?> div input[type=hidden]').each(function(i, o) {
                            jQuery(o).val(i);
                        });
                    }
                });
                jQuery('#br_tab_manager_sortable-<?php echo $randid; ?> div input[type=hidden]').each(function(i, o) {
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
