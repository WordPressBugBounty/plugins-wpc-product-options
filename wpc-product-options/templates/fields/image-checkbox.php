<?php
/**
 * @var $field
 * @var $option_key
 */

defined( 'ABSPATH' ) || exit;

if ( ! empty( $field['options'] ) ) {
	foreach ( $field['options'] as $option_key => $option ) {
		if ( isset( $option['value'] ) && $option['value'] !== '' && ! empty( $option['image'] ) ) {
			$option_label = isset( $option['name'] ) && $option['name'] !== '' ? $option['name'] : $option['value'];
			?>
            <input class="wpcpo-option-field field-checkbox" type="checkbox" name="<?php echo esc_attr( $option_key . '[value]' ); ?>" id="<?php echo esc_attr( $option_key ); ?>" data-label="<?php echo esc_attr( $option_label ); ?>" data-title="<?php echo esc_attr( $field['title'] ); ?>" data-enable-price="1" data-price-type="<?php echo esc_attr( $option['price_type'] ); ?>" data-price="<?php echo esc_attr( $option['price'] ); ?>" data-price-custom="<?php echo esc_attr( $option['custom_price'] ); ?>" value="<?php echo esc_attr( $option['value'] ); ?>" data-image="<?php echo esc_attr( $option['image'] ); ?>" <?php echo esc_attr( $field['default_value'] && ( $field['value'] === $option['value'] ) ? 'checked' : '' ); ?>>
            <label for="<?php echo esc_attr( $option_key ); ?>">
				<?php
				do_action( 'wpcpo_image_checkbox_option_before', $option, $field );

				echo wp_get_attachment_image( $option['image'] );

				if ( isset( $option['name'] ) && $option['name'] !== '' ) {
					echo '<span class="label-name">' . esc_html( $option['name'] ) . '</span>';
				}

				echo Wpcpo_Frontend::get_label_price( $option, 'option' );

				do_action( 'wpcpo_image_checkbox_option_after', $option, $field );
				?>
            </label>
            <input type="hidden" name="<?php echo esc_attr( $option_key . '[label]' ); ?>" value="<?php echo esc_attr( $option_label ); ?>"/>
            <input type="hidden" name="<?php echo esc_attr( $option_key . '[price_type]' ); ?>" value="<?php echo esc_attr( $option['price_type'] ); ?>"/>
            <input type="hidden" name="<?php echo esc_attr( $option_key . '[price]' ); ?>" value="<?php echo esc_attr( $option['price'] ); ?>"/>
            <input type="hidden" name="<?php echo esc_attr( $option_key . '[custom_price]' ); ?>" value="<?php echo esc_attr( $option['custom_price'] ); ?>"/>
            <input type="hidden" name="<?php echo esc_attr( $option_key . '[title]' ); ?>" value="<?php echo esc_attr( $field['title'] ); ?>"/>
            <input type="hidden" name="<?php echo esc_attr( $option_key . '[type]' ); ?>" value="image-checkbox"/>
            <input type="hidden" name="<?php echo esc_attr( $option_key . '[image]' ); ?>" value="<?php echo esc_attr( $option['image'] ); ?>"/>
		<?php }
	}
}
