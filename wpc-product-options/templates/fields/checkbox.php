<?php
/**
 * @var $field
 * @var $key
 */

defined( 'ABSPATH' ) || exit;

if ( ! empty( $field['options'] ) ) {
	foreach ( $field['options'] as $option_key => $option ) {
		if ( isset( $option['value'] ) && $option['value'] !== '' ) {
			$option_label = isset( $option['name'] ) && $option['name'] !== '' ? $option['name'] : $option['value'];
			?>
            <label>
                <input class="wpcpo-option-field" type="checkbox" name="<?php echo esc_attr( $option_key . '[value]' ); ?>"
					<?php echo esc_attr( $field['required'] ? 'required' : '' ); ?> data-label="<?php echo esc_attr( $option_label ); ?>" data-title="<?php echo esc_attr( $field['title'] ); ?>" data-enable-price="1" data-price-type="<?php echo esc_attr( $option['price_type'] ); ?>" data-price="<?php echo esc_attr( $option['price'] ); ?>" data-price-custom="<?php echo esc_attr( $option['custom_price'] ); ?>" value="<?php echo esc_attr( $option['value'] ); ?>" <?php echo esc_attr( $field['default_value'] && ( $field['value'] === $option['value'] ) ? 'checked' : '' ); ?>>
				<?php echo esc_html( $option_label ); ?>
                <span><?php echo Wpcpo_Frontend::get_label_price( $option, 'option' ); ?></span>
                <input type="hidden" name="<?php echo esc_attr( $option_key . '[label]' ); ?>" value="<?php echo esc_attr( $option_label ); ?>"/>
                <input type="hidden" name="<?php echo esc_attr( $option_key . '[price_type]' ); ?>" value="<?php echo esc_attr( $option['price_type'] ); ?>"/>
                <input type="hidden" name="<?php echo esc_attr( $option_key . '[price]' ); ?>" value="<?php echo esc_attr( $option['price'] ); ?>"/>
                <input type="hidden" name="<?php echo esc_attr( $option_key . '[custom_price]' ); ?>" value="<?php echo esc_attr( $option['custom_price'] ); ?>"/>
                <input type="hidden" name="<?php echo esc_attr( $option_key . '[title]' ); ?>" value="<?php echo esc_attr( $field['title'] ); ?>"/>
                <input type="hidden" name="<?php echo esc_attr( $option_key . '[type]' ); ?>" value="checkbox"/>
            </label>
		<?php }
	}
}
