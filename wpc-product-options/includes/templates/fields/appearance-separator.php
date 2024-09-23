<?php
/**
 * @var $this
 * @var $type
 */

defined( 'ABSPATH' ) || exit;
?>
<input type="hidden" name="<?php echo esc_attr( 'wpcpo-fields[' . $this->field_id . '][type]' ); ?>" value="<?php echo esc_attr( $type ); ?>"/>
<div class="wpcpo-item-line">
    <label><strong><?php esc_html_e( 'Width', 'wpc-product-options' ); ?> *</strong>
        <input type="number" class="wpcpo-input-not-empty" min="1" max="100" name="<?php echo esc_attr( 'wpcpo-fields[' . $this->field_id . '][width]' ); ?>" value="<?php echo esc_attr( $this->get_field_value( 'width', '100' ) ); ?>"/> %
    </label>
</div>
