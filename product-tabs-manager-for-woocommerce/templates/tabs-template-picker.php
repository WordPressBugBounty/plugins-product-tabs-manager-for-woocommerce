<?php
if ( ! defined('ABSPATH') ) {
    exit;
}
?>
<td colspan="2">
    <fieldset class="br-tabs-template-picker">
        <legend><?php esc_html_e('Product tabs template', 'product-tabs-manager-for-woocommerce'); ?></legend>
        <p class="description"><?php esc_html_e('Choose a layout for the tabs and their content on product pages.', 'product-tabs-manager-for-woocommerce'); ?></p>
        <label class="br-tabs-template-default">
            <input type="radio" name="<?php echo esc_attr(self::$settings_name); ?>[tabs_template]" value="theme" <?php checked($selected_template, 'theme'); ?>>
            <?php echo esc_html($templates['theme']); ?>
        </label>
        <div class="br-tabs-template-options">
            <?php foreach ( $templates as $template => $label ) : ?>
                <?php if ( 'theme' === $template ) { continue; } ?>
                <label class="br-tabs-template-option">
                    <input type="radio" name="<?php echo esc_attr(self::$settings_name); ?>[tabs_template]" value="<?php echo esc_attr($template); ?>" <?php checked($selected_template, $template); ?>>
                    <span><?php echo esc_html($label); ?></span>
                    <span aria-hidden="true" class="br-tabs-template-preview br-product-tabs-template br-product-tabs-<?php echo esc_attr($template); ?>">
                        <span class="woocommerce-tabs wc-tabs-wrapper">
                            <span class="wc-tabs">
                                <span class="active"><span>FAQ</span></span>
                                <span><span><?php esc_html_e('Sizes', 'product-tabs-manager-for-woocommerce'); ?></span></span>
                                <span><span><?php esc_html_e('Returns', 'product-tabs-manager-for-woocommerce'); ?></span></span>
                            </span>
                            <span class="wc-tab">
                                <span class="br-tabs-preview-lines"></span>
                                <span class="br-tabs-preview-question"></span>
                                <span class="br-tabs-preview-question"></span>
                            </span>
                        </span>
                    </span>
                </label>
            <?php endforeach; ?>
        </div>
        <?php if ( ! class_exists('BeRocket_tab_manager_Paid') ) : ?>
            <a class="br-tabs-template-upsell" href="https://berocket.com/woocommerce-product-tabs-manager/" target="_blank" rel="noopener noreferrer">
                <?php esc_html_e('More templates in Pro version', 'product-tabs-manager-for-woocommerce'); ?>
            </a>
        <?php endif; ?>
    </fieldset>
</td>
