<?php
/**
 * @var $field
 * @var $key
 */

defined( 'ABSPATH' ) || exit;

$default_value = $_GET[ $key ] ?? '';

if ( empty( $field['options'] ) || ! is_array( $field['options'] ) ) {
    return;
}

foreach ( $field['options'] as $option_key => $option ) {
    if ( isset( $option['value'] ) && $option['value'] !== '' && ! empty( $option['color'] ) ) {
        $option_label = isset( $option['name'] ) && $option['name'] !== '' ? $option['name'] : $option['value'];
        ?>
        <input class="wpcpo-option-field field-radio" type="radio" name="<?php echo esc_attr( $key . '[value]' ); ?>"
               id="<?php echo esc_attr( $option_key ); ?>" data-label="<?php echo esc_attr( $option_label ); ?>"
               data-title="<?php echo esc_attr( $field['title'] ); ?>" data-enable-price="1"
               data-price-type="<?php echo esc_attr( $option['price_type'] ); ?>"
               data-price="<?php echo esc_attr( $option['price'] ); ?>"
               data-price-custom="<?php echo esc_attr( $option['custom_price'] ); ?>"
               value="<?php echo esc_attr( $option['value'] ); ?>"
               data-color="<?php echo esc_attr( $option['color'] ); ?>" <?php echo esc_attr( ( $field['default_value'] && ( $field['value'] === $option['value'] ) ) || $default_value === $option['value'] ? 'checked' : '' ); ?>>
        <label for="<?php echo esc_attr( $option_key ); ?>">
            <?php
            do_action( 'wpcpo_color_radio_option_before', $option, $field );

            echo '<span class="label-color" style="background-color: ' . esc_attr( sanitize_hex_color( $option['color'] ) ) . '"></span>';

            if ( isset( $option['name'] ) && $option['name'] !== '' ) {
                echo '<span class="label-name">' . esc_html( $option['name'] ) . '</span>';
            }

            echo Wpcpo_Frontend::get_label_price( $option, 'option' );

            do_action( 'wpcpo_color_radio_option_after', $option, $field );
            ?>
        </label>
    <?php }
}
?>
<input type="hidden" name="<?php echo esc_attr( $key . '[label]' ); ?>" value=""/>
<input type="hidden" name="<?php echo esc_attr( $key . '[price_type]' ); ?>" value=""/>
<input type="hidden" name="<?php echo esc_attr( $key . '[price]' ); ?>" value=""/>
<input type="hidden" name="<?php echo esc_attr( $key . '[custom_price]' ); ?>" value=""/>
<input type="hidden" name="<?php echo esc_attr( $key . '[type]' ); ?>" value="color-radio"/>
<input type="hidden" name="<?php echo esc_attr( $key . '[color]' ); ?>" value=""/>
