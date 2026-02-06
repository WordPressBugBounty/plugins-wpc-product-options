<?php
/**
 * @var $field
 * @var $key
 */

defined( 'ABSPATH' ) || exit;

if ( empty( $field['dimensions'] ) || ! is_array( $field['dimensions'] ) ) {
    return;
}

echo '<div class="wpcpo-dimensions">';

$d = 1;

foreach ( $field['dimensions'] as $option_key => $option ) {
    $option_label = $option['name'] ?? '';
    $option_class = 'wpcpo-option-field-dimension wpcpo-option-field-dimension-d' . $d;
    $option_min   = (float) $option['min'] ?? 0;
    $option_max   = (float) $option['max'] ?? 0;
    $option_step  = (float) $option['step'] ?? 1;
    $option_value = (float) $option['default'] ?? 0;

    if ( $option_min < 0 ) {
        $option_min = 0;
    }

    if ( $option_max < $option_min ) {
        $option_max = $option_min;
    }

    if ( $option_value < $option_min ) {
        $option_value = $option_min;
    }

    if ( $option_max > 0 && ( $option_value > $option_max ) ) {
        $option_value = $option_max;
    }
    ?>
    <div class="wpcpo-dimension">
        <label for="<?php echo esc_attr( $option_key ); ?>"><?php echo esc_html( $option_label ); ?></label>
        <input type="number" class="<?php echo esc_attr( $option_class ); ?>"
               id="<?php echo esc_attr( $option_key ); ?>"
               value="<?php echo esc_attr( $option['default'] !== '' ? $option_value : '' ); ?>"
               min="<?php echo esc_attr( $option_min ); ?>"
               step="<?php echo esc_attr( $option_step ); ?>"
               max="<?php echo esc_attr( $option['max'] !== '' ? $option_max : '' ); ?>"
               name="<?php echo esc_attr( $key . '[dimensions][]' ); ?>"
                <?php echo esc_attr( $field['required'] ? 'required' : '' ); ?>/>
    </div>
    <?php
    $d ++;
}

echo '</div>';

if ( $field['enable_price'] ) { ?>
    <input type="hidden" class="wpcpo-option-field field-dimensions"
           name="<?php echo esc_attr( $key . '[value]' ); ?>"
           data-title="<?php echo esc_attr( $field['title'] ); ?>"
           data-enable-price="<?php echo esc_attr( $field['enable_price'] ); ?>"
           data-price-type="<?php echo esc_attr( $field['price_type'] ); ?>"
           data-price-custom="<?php echo esc_attr( $field['custom_price'] ); ?>"
           data-price="<?php echo esc_attr( $field['price'] ); ?>">
    <input type="hidden" name="<?php echo esc_attr( $key . '[price_type]' ); ?>"
           value="<?php echo esc_attr( $field['price_type'] ); ?>"/>
    <input type="hidden" name="<?php echo esc_attr( $key . '[price]' ); ?>"
           value="<?php echo esc_attr( $field['price'] ); ?>"/>
    <input type="hidden" name="<?php echo esc_attr( $key . '[custom_price]' ); ?>"
           value="<?php echo esc_attr( $field['custom_price'] ); ?>"/>
<?php } ?>

<input type="hidden" name="<?php echo esc_attr( $key . '[type]' ); ?>" value="dimensions"/>