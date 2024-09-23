<?php
defined( 'ABSPATH' ) || exit;

$display = get_post_meta( get_the_ID(), 'wpcpo-display', true ) ?: 'global';
?>
<div id='wpcpo_settings' class='panel woocommerce_options_panel wpcpo_settings'>
    <div class="options_group">
        <p class="form-field">
            <label for="wpcpo-select-display"><?php esc_html_e( 'Fields', 'wpc-product-options' ); ?></label>
            <select name="wpcpo-display" id="wpcpo-select-display" class="select short">
                <option value="global" <?php selected( $display, 'global' ); ?>><?php esc_html_e( 'Global', 'wpc-product-options' ); ?></option>
                <option value="disable" <?php selected( $display, 'disable' ); ?>><?php esc_html_e( 'Disable', 'wpc-product-options' ); ?></option>
                <option value="override" <?php selected( $display, 'override' ); ?>><?php esc_html_e( 'Override', 'wpc-product-options' ); ?></option>
                <option value="prepend" <?php selected( $display, 'prepend' ); ?>><?php esc_html_e( 'Prepend', 'wpc-product-options' ); ?></option>
                <option value="append" <?php selected( $display, 'append' ); ?>><?php esc_html_e( 'Append', 'wpc-product-options' ); ?></option>
            </select>
        </p>
    </div>
    <div class="options_group wpcpo-fields-single-product" style="display: none;">
		<?php $this->fields_meta(); ?>
    </div>
</div>
