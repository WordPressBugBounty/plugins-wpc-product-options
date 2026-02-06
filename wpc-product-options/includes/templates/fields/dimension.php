<?php
/**
 * @var $this
 * @var $dimension
 * @var $dimension_id
 */

defined( 'ABSPATH' ) || exit;
?>
<div class="inner-option">
    <div class="inner-option-move"></div>

    <div class="inner-option-name">
        <input type="text" class="option-name"
               name="<?php echo esc_attr( 'wpcpo-fields[' . $this->field_id . '][dimensions][' . $dimension_id . '][name]' ); ?>"
               value="<?php echo esc_attr( $this->get_option_value( $dimension, 'name', '' ) ); ?>"/>
    </div>

    <div class="inner-option-default">
        <input type="number" step="any" class="option-value"
               name="<?php echo esc_attr( 'wpcpo-fields[' . $this->field_id . '][dimensions][' . $dimension_id . '][default]' ); ?>"
               value="<?php echo esc_attr( $this->get_option_value( $dimension, 'default', '' ) ); ?>"/>
    </div>

    <div class="inner-option-min">
        <input type="number" step="any" class="option-value wpcpo-input-not-empty"
               name="<?php echo esc_attr( 'wpcpo-fields[' . $this->field_id . '][dimensions][' . $dimension_id . '][min]' ); ?>"
               value="<?php echo esc_attr( $this->get_option_value( $dimension, 'min', '' ) ); ?>"/>
    </div>

    <div class="inner-option-max">
        <input type="number" step="any" class="option-value wpcpo-input-not-empty"
               name="<?php echo esc_attr( 'wpcpo-fields[' . $this->field_id . '][dimensions][' . $dimension_id . '][max]' ); ?>"
               value="<?php echo esc_attr( $this->get_option_value( $dimension, 'max', '' ) ); ?>"/>
    </div>

    <div class="inner-option-step">
        <input type="number" step="any" class="option-value wpcpo-input-not-empty"
               name="<?php echo esc_attr( 'wpcpo-fields[' . $this->field_id . '][dimensions][' . $dimension_id . '][step]' ); ?>"
               value="<?php echo esc_attr( $this->get_option_value( $dimension, 'step', '' ) ); ?>"/>
    </div>

    <div class="inner-option-remove">
        <button type="button" class="button">&times;</button>
    </div>
</div>
