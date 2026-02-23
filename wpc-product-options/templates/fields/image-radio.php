<?php
/**
 * @var $field
 * @var $key
 */

defined( 'ABSPATH' ) || exit;

if ( empty( $field['options'] ) || ! is_array( $field['options'] ) ) {
    return;
}

$default_value      = $_GET[ $key ] ?? '';
$tooltip_library    = Wpcpo_Backend::get_setting( 'tooltip_library', 'none' );
$tooltip_position   = Wpcpo_Backend::get_setting( 'tooltip_position', 'top' );
$tooltip_image_size = Wpcpo_Backend::get_setting( 'tooltip_image_size', 'woocommerce_thumbnail' );

switch ( $tooltip_library ) {
    case 'tippy':
        $tooltip_class = 'wpcpo-tippy-tooltip tippy--' . $tooltip_position;
        break;
    case 'hint':
        $tooltip_class = 'wpcpo-hint-tooltip hint--' . $tooltip_position;
        break;
    default:
        $tooltip_class = '';
}

foreach ( $field['options'] as $option_key => $option ) {
    if ( isset( $option['value'] ) && $option['value'] !== '' && ! empty( $option['image'] ) ) {
        $option_label = isset( $option['name'] ) && $option['name'] !== '' ? $option['name'] : $option['value'];

        switch ( $tooltip_library ) {
            case 'tippy':
                $tooltip_content = 'data-tippy-content="' . esc_attr( htmlentities( '<span class="wpcpo-tippy wpcpo-tippy-' . esc_attr( $option_key ) . '"><span class="wpcpo-tippy-inner"><span class="wpcpo-tippy-image">' . wp_get_attachment_image( $option['image'], $tooltip_image_size ) . '</span><span class="wpcpo-tippy-label">' . esc_html( $option_label ) . '</span></span></span>' ) ) . '"';
                break;
            case 'hint':
                $tooltip_content = 'aria-label="' . esc_attr( $option_label ) . '"';
                break;
            default:
                $tooltip_content = '';
        }
        ?>
        <input class="wpcpo-option-field field-radio" type="radio" name="<?php echo esc_attr( $key . '[value]' ); ?>"
               id="<?php echo esc_attr( $option_key ); ?>" data-label="<?php echo esc_attr( $option_label ); ?>"
               data-title="<?php echo esc_attr( $field['title'] ); ?>" data-enable-price="1"
               data-price-type="<?php echo esc_attr( $option['price_type'] ); ?>"
               data-price="<?php echo esc_attr( $option['price'] ); ?>"
               data-price-custom="<?php echo esc_attr( $option['custom_price'] ); ?>"
               value="<?php echo esc_attr( $option['value'] ); ?>"
               data-image="<?php echo esc_attr( $option['image'] ); ?>" <?php echo esc_attr( ( $field['default_value'] && ( $field['value'] === $option['value'] ) ) || $default_value === $option['value'] ? 'checked' : '' ); ?>>
        <?php
        switch ( $tooltip_library ) {
            case 'tippy':
                echo '<label for="' . esc_attr( $option_key ) . '" class="' . esc_attr( $tooltip_class ) . '" ' . $tooltip_content . '>';
                break;
            case 'hint':
                echo '<label for="' . esc_attr( $option_key ) . '">';
                echo '<span class="label-inner ' . esc_attr( $tooltip_class ) . '" ' . $tooltip_content . '>';
                break;
            default:
                echo '<label for="' . esc_attr( $option_key ) . '">';
        }

        do_action( 'wpcpo_image_radio_option_before', $option, $field );

        echo wp_get_attachment_image( $option['image'] );

        if ( isset( $option['name'] ) && $option['name'] !== '' ) {
            echo '<span class="label-name">' . esc_html( $option['name'] ) . '</span>';
        }

        echo Wpcpo_Frontend::get_label_price( $option, 'option' );

        do_action( 'wpcpo_image_radio_option_after', $option, $field );

        switch ( $tooltip_library ) {
            case 'tippy':
                echo '</label>';
                break;
            case 'hint':
                echo '</span></label>';
                break;
            default:
                echo '</label>';
        }
    }
}
?>
<input type="hidden" name="<?php echo esc_attr( $key . '[label]' ); ?>" value=""/>
<input type="hidden" name="<?php echo esc_attr( $key . '[price_type]' ); ?>" value=""/>
<input type="hidden" name="<?php echo esc_attr( $key . '[price]' ); ?>" value=""/>
<input type="hidden" name="<?php echo esc_attr( $key . '[custom_price]' ); ?>" value=""/>
<input type="hidden" name="<?php echo esc_attr( $key . '[type]' ); ?>" value="image-radio"/>
<input type="hidden" name="<?php echo esc_attr( $key . '[image]' ); ?>" value=""/>
