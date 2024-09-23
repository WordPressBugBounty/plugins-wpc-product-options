<?php
/**
 * @var $field
 * @var $key
 */

defined( 'ABSPATH' ) || exit;

if ( isset( $field['block'] ) && $field['block'] ) {
	$selected_dates = [];

	if ( in_array( 'selected', $field['block_dates'] ) && ! empty( $field['selected_dates'] ) ) {
		$selected_dates = array_map( 'trim', explode( ',', $field['selected_dates'] ) );
	}

	$block_dates = implode( ',', array_merge( $field['block_dates'], $selected_dates ) );
} else {
	$block_dates = '';
}
?>
<input type="text" class="wpcpo-option-field wpcpo-date-picker" name="<?php echo esc_attr( $key . '[value]' ); ?>" id="<?php echo esc_attr( $key ); ?>" data-block="<?php echo esc_attr( $block_dates ); ?>"
	<?php echo esc_attr( $field['required'] ? 'required' : '' ); ?> data-format="<?php echo esc_attr( Wpcpo_Frontend::js_datetime_format( apply_filters( 'wpcpo_date_format', $field['format'], $field ) ) ); ?>" data-title="<?php echo esc_attr( $field['title'] ); ?>" data-enable-price="<?php echo esc_attr( $field['enable_price'] ); ?>" data-price-type="<?php echo esc_attr( $field['price_type'] ); ?>" data-price-custom="<?php echo esc_attr( $field['custom_price'] ); ?>" data-price="<?php echo esc_attr( $field['price'] ); ?>"/>
<?php if ( $field['enable_price'] ) { ?>
    <input type="hidden" name="<?php echo esc_attr( $key . '[price_type]' ); ?>" value="<?php echo esc_attr( $field['price_type'] ); ?>"/>
    <input type="hidden" name="<?php echo esc_attr( $key . '[price]' ); ?>" value="<?php echo esc_attr( $field['price'] ); ?>"/>
    <input type="hidden" name="<?php echo esc_attr( $key . '[custom_price]' ); ?>" value="<?php echo esc_attr( $field['custom_price'] ); ?>"/>
<?php } ?>
<input type="hidden" name="<?php echo esc_attr( $key . '[type]' ); ?>" value="date-picker"/>
