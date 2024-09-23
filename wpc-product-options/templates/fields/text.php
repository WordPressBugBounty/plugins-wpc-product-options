<?php
/**
 * @var $field
 * @var $key
 */

defined( 'ABSPATH' ) || exit;
?>
<input type="text" class="wpcpo-option-field" name="<?php echo esc_attr( $key . '[value]' ); ?>" id="<?php echo esc_attr( $key ); ?>" value="<?php echo esc_attr( $field['default_value'] ? $field['value'] : '' ); ?>" data-title="<?php echo esc_attr( $field['title'] ); ?>" data-enable-price="<?php echo esc_attr( $field['enable_price'] ); ?>" data-price-type="<?php echo esc_attr( $field['price_type'] ); ?>" data-price="<?php echo esc_attr( $field['price'] ); ?>" data-price-custom="<?php echo esc_attr( $field['custom_price'] ); ?>"
	<?php echo Wpcpo_Frontend::get_min_max_attr( $field ); ?>
	<?php echo esc_attr( $field['required'] ? 'required' : '' ); ?>/>
<?php if ( $field['enable_price'] ) { ?>
    <input type="hidden" name="<?php echo esc_attr( $key . '[price_type]' ); ?>" value="<?php echo esc_attr( $field['price_type'] ); ?>"/>
    <input type="hidden" name="<?php echo esc_attr( $key . '[price]' ); ?>" value="<?php echo esc_attr( $field['price'] ); ?>"/>
    <input type="hidden" name="<?php echo esc_attr( $key . '[custom_price]' ); ?>" value="<?php echo esc_attr( $field['custom_price'] ); ?>"/>
<?php } ?>
<input type="hidden" name="<?php echo esc_attr( $key . '[type]' ); ?>" value="text"/>
