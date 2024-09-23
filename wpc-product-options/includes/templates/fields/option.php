<?php
/**
 * @var $this
 * @var $option
 * @var $option_id
 * @var $type
 */

defined( 'ABSPATH' ) || exit;

$price_type = $this->get_option_value( $option, 'price_type', 'flat' );
?>
<div class="inner-option">
    <div class="inner-option-move"></div>

	<?php if ( $type === 'image-radio' || $type === 'image-checkbox' ) {
		$image_id = $this->get_option_value( $option, 'image', 0 );
		echo '<div class="inner-option-image wpcpo-image-selector">';
		echo '<input type="hidden" class="wpcpo-image-id" name="wpcpo-fields[' . $this->field_id . '][options][' . $option_id . '][image]" value="' . esc_attr( $image_id ) . '">';

		if ( $image_id ) {
			echo '<span class="wpcpo-image-preview">' . wp_get_attachment_image( $image_id ) . '</span>';
		} else {
			echo '<span class="wpcpo-image-preview"></span>';
		}

		echo '<a class="wpcpo-image-remove" href="#" ' . ( ! $image_id ? 'style="display:none;"' : '' ) . '>&times</a>';
		echo '</div>';
	} ?>

    <div class="inner-option-name">
        <input type="text" class="option-name" name="<?php echo esc_attr( 'wpcpo-fields[' . $this->field_id . '][options][' . $option_id . '][name]' ); ?>" value="<?php echo esc_attr( $this->get_option_value( $option, 'name', '' ) ); ?>"/>
    </div>

    <div class="inner-option-value">
        <input type="text" class="option-value wpcpo-input-not-empty" name="<?php echo esc_attr( 'wpcpo-fields[' . $this->field_id . '][options][' . $option_id . '][value]' ); ?>" value="<?php echo esc_attr( $this->get_option_value( $option, 'value', '' ) ); ?>"/>
    </div>

    <div class="inner-option-price">
        <select class="option-type <?php echo esc_attr( 'type-' . $price_type ); ?>" name="<?php echo esc_attr( 'wpcpo-fields[' . $this->field_id . '][options][' . $option_id . '][price_type]' ); ?>">
            <option value="flat" <?php selected( $price_type, 'flat' ); ?>><?php esc_html_e( 'Flat Fee', 'wpc-product-options' ); ?></option>
            <option value="qty" <?php selected( $price_type, 'qty' ); ?>><?php esc_html_e( 'Quantity Synced', 'wpc-product-options' ); ?></option>
            <option value="custom" <?php selected( $price_type, 'custom' ); ?>><?php esc_html_e( 'Custom Formula', 'wpc-product-options' ); ?></option>
        </select> <span>—</span>
        <span class="wpcpo-price-wrapper hint--left" aria-label="<?php esc_html_e( 'Set a price using a number (eg. "10") or percentage (eg. "10%" of product price)', 'wpc-product-options' ); ?>">
            <input type="text" class="option-number wpcpo-price" name="<?php echo esc_attr( 'wpcpo-fields[' . $this->field_id . '][options][' . $option_id . '][price]' ); ?>" value="<?php echo esc_attr( $this->get_option_value( $option, 'price', '' ) ); ?>"/>
        </span>
        <span class="wpcpo-price-custom-wrapper hint--left" aria-label="<?php esc_html_e( 'You can use: p (product price); q (quantity); l (string length); w (words count); v (value) in the formula, e.g: (p+2)*q/2', 'wpc-product-options' ); ?>">
            <input type="hidden" class="wpcpo-price-custom" name="<?php echo esc_attr( 'wpcpo-fields[' . $this->field_id . '][options][' . $option_id . '][custom_price]' ); ?>" value="<?php echo esc_attr( $this->get_field_value( 'custom_price' ) ); ?>"/> This feature is only available on the premium version. Click <a href="https://wpclever.net/downloads/product-options/" target="_blank">here</a> to buy it for just $29!
        </span>
    </div>

    <div class="inner-option-remove">
        <button type="button" class="button">&times;</button>
    </div>
</div>
