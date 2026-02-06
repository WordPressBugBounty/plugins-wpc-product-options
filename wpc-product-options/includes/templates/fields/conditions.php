<?php
/**
 * @var $this
 * @var $conditions
 */

defined( 'ABSPATH' ) || exit;

$logic_display = $this->get_field_value( 'logic_display', 'show' );
?>
<div class="wpcpo-item-line">
    <input type="checkbox" value="1" id="<?php echo esc_attr( 'wpcpo_' . $this->field_id . '_enable_logic' ); ?>"
           class="checkbox-logic"
           name="<?php echo esc_attr( 'wpcpo-fields[' . $this->field_id . '][enable_logic]' ); ?>" <?php checked( $this->get_field_value( 'enable_logic', '0' ), '1' ); ?>>
    <label for="<?php echo esc_attr( 'wpcpo_' . $this->field_id . '_enable_logic' ); ?>"><?php esc_html_e( 'Conditional logic', 'wpc-product-options' ); ?></label>
    <div class="checkbox-show">
        This feature is only available on the premium version. Click <a
                href="https://wpclever.net/downloads/product-options/" target="_blank">here</a> to buy it for just $29!
    </div>
</div>