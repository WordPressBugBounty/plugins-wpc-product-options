<?php
/**
 * @var $this
 * @var $condition
 * @var $condition_id
 */

defined( 'ABSPATH' ) || exit;

$field   = $this->get_option_value( $condition, 'f', '' );
$compare = $this->get_option_value( $condition, 'c', 'is' );
$value   = $this->get_option_value( $condition, 'v', '' );
?>
<div class="inner-option">
    <div class="inner-option-move"></div>

    <div class="inner-option-field">
        <select class="wpcpo-condition-fields" data-val="<?php echo esc_attr( $field ); ?>"
                data-id="<?php echo esc_attr( $this->field_id ); ?>"
                name="<?php echo esc_attr( 'wpcpo-fields[' . $this->field_id . '][logic_conditions][' . $condition_id . '][f]' ); ?>">
        </select>
    </div>

    <div class="inner-option-compare">
        <select name="<?php echo esc_attr( 'wpcpo-fields[' . $this->field_id . '][logic_conditions][' . $condition_id . '][c]' ); ?>">
            <option value="is" <?php selected( $compare, 'is' ); ?>><?php esc_html_e( 'is (equal)', 'wpc-product-options' ); ?></option>
            <option value="is_not" <?php selected( $compare, 'is_not' ); ?>><?php esc_html_e( 'is not (not equal)', 'wpc-product-options' ); ?></option>
            <option value="less" <?php selected( $compare, 'less' ); ?>><?php esc_html_e( 'less than', 'wpc-product-options' ); ?></option>
            <option value="greater" <?php selected( $compare, 'greater' ); ?>><?php esc_html_e( 'greater than', 'wpc-product-options' ); ?></option>
        </select>
    </div>

    <div class="inner-option-value">
        <input type="text" class="option-value"
               name="<?php echo esc_attr( 'wpcpo-fields[' . $this->field_id . '][logic_conditions][' . $condition_id . '][v]' ); ?>"
               value="<?php echo esc_attr( $value ); ?>"/>
    </div>

    <div class="inner-option-remove">
        <button type="button" class="button">&times;</button>
    </div>
</div>
