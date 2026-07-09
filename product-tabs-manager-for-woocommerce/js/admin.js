var br_saved_timeout;
var br_savin_ajax = false;
(function ($){
    $(document).ready( function () {
        $('.colorpicker_field_tabs').each(function (i,o){
            $(o).css('backgroundColor', '#'+$(o).data('color'));
            $(o).colpick({
                layout: 'hex',
                submit: 0,
                color: '#'+$(o).data('color'),
                onChange: function(hsb,hex,rgb,el,bySetColor) {
                    $(el).css('backgroundColor', '#'+hex).next().val(hex).trigger('change');
                }
            })
        });
        $('.theme_default_tabs').click(function() {
            var $parent = $(this).parents('td').first();
            $parent.find('.colorpicker_field_tabs').css('backgroundColor', '#000000').colpickSetColor('#000000');
            $parent.find('.colorpicker_input_tabs').val('');
        });
        $(document).on('click', '.br-tab_manager-header', function (event) {
            var $block = $(this).parents('.br-tab_manager-element').find('.br_hidden');
            var $caret = $(this).find('.br-show_next_hidden .fa');
            if ( $block.is('.br_display_none') ) {
                $block.removeClass('br_display_none');
                $caret.removeClass('fa-caret-down').addClass('fa-caret-up');
            } else {
                $block.addClass('br_display_none');
                $caret.removeClass('fa-caret-up').addClass('fa-caret-down');
            }
        });
        $(document).on('click', '.br-remove_tab', function (event) {
            event.stopPropagation();
            $(this).parents('.br-tab_manager-element').remove();
        });
        $('.br-add-tab').click( function() {
            var $parent = $(this).parents('.br_tab_manager_tab_editor');
            var tab_html_current = window['$tab_html'+$(this).data('randid')];
             if( $parent.find('.br-element-'+$parent.find('.br-add-tab-select').val()).length == 0 ) {
                $parent.find('.br-tab_manager-sortable').append( $( tab_html_current[$parent.find('.br-add-tab-select').val()] ) );
                $parent.find('.br-tab_manager-sortable div input[type=hidden]').each(function(i, o) {
                    jQuery(o).val(i);
                });
             }
        });
        $(document).on('change', '.berocket_change_tabs_options', function() {
            $('.berocket_tabs_for_cat').hide();
            $('.berocket_tabs_for_cat_'+$(this).val()).show();
        });
    });
})(jQuery);
