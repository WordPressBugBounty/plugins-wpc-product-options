<?php
/**
 * @var $field
 * @var $key
 */

defined( 'ABSPATH' ) || exit;
?>
<select class="wpcpo-option-field field-select" data-enable-price="1" name="<?php echo esc_attr( $key . '[value]' ); ?>" id="<?php echo esc_attr( $key ); ?>" data-title="<?php echo esc_attr( $field['title'] ); ?>" <?php echo esc_attr( ! empty( $field['required'] ) ? 'required' : '' ); ?>>
    <option value=""><?php esc_html_e( 'Select an option...', 'wpc-product-options' ); ?></option>
	<?php if ( ! empty( $field['options'] ) ) {
		foreach ( $field['options'] as $option_key => $option ) {
			if ( isset( $option['value'] ) && $option['value'] !== '' ) {
				$option_label = isset( $option['name'] ) && $option['name'] !== '' ? $option['name'] : $option['value'];
				?>
                <option data-title="<?php echo esc_attr( $field['title'] ); ?>" data-label="<?php echo esc_attr( $option_label ); ?>" data-enable-price="1" data-price-type="<?php echo esc_attr( $option['price_type'] ); ?>" data-price="<?php echo esc_attr( $option['price'] ); ?>" data-price-custom="<?php echo esc_attr( $option['custom_price'] ); ?>" value="<?php echo esc_attr( $option['value'] ); ?>" <?php echo esc_attr( $field['default_value'] && ( $field['value'] === $option['value'] ) ? 'selected' : '' ); ?>>
					<?php echo esc_html( $option_label ) . ' ' . Wpcpo_Frontend::get_label_price( $option, 'option' ); ?>
                </option>
			<?php }
		}
	} ?>
</select><input type="hidden" name="<?php echo esc_attr( $key . '[label]' ); ?>" value=""/>
<input type="hidden" name="<?php echo esc_attr( $key . '[price_type]' ); ?>" value=""/>
<input type="hidden" name="<?php echo esc_attr( $key . '[price]' ); ?>" value=""/>
<input type="hidden" name="<?php echo esc_attr( $key . '[custom_price]' ); ?>" value=""/>
<input type="hidden" name="<?php echo esc_attr( $key . '[type]' ); ?>" value="select"/>
