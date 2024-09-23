<?php
/**
 * @var $this
 * @var $type
 */

defined( 'ABSPATH' ) || exit;
?>
<input type="hidden" name="<?php echo esc_attr( 'wpcpo-fields[' . $this->field_id . '][type]' ); ?>" value="<?php echo esc_attr( $type ); ?>"/>
<div class="wpcpo-item-line">
    <label><strong><?php esc_html_e( 'Shortcode', 'wpc-product-options' ); ?> *</strong>
        <input type="text" class="input-block wpcpo-input-not-empty" name="<?php echo esc_attr( 'wpcpo-fields[' . $this->field_id . '][shortcode]' ); ?>" value="<?php echo esc_attr( $this->get_field_value( 'shortcode' ) ); ?>"/>
    </label>
</div>
