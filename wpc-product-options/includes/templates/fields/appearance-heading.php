<?php
/**
 * @var $this
 * @var $type
 */

defined( 'ABSPATH' ) || exit;

$level = $this->get_field_value( 'level', 'h3' );
?>
<input type="hidden" name="<?php echo esc_attr( 'wpcpo-fields[' . $this->field_id . '][type]' ); ?>" value="<?php echo esc_attr( $type ); ?>"/>
<div class="wpcpo-item-line">
    <label><strong><?php esc_html_e( 'Heading', 'wpc-product-options' ); ?> *</strong>
        <input type="text" class="input-block wpcpo-input-not-empty" name="<?php echo esc_attr( 'wpcpo-fields[' . $this->field_id . '][heading]' ); ?>" value="<?php echo esc_attr( $this->get_field_value( 'heading', ucwords( str_replace( '-', ' ', $type ) ) ) ); ?>">
    </label>
</div>
<div class="wpcpo-item-line">
    <label><strong><?php esc_html_e( 'Level', 'wpc-product-options' ); ?></strong>
        <select name="<?php echo esc_attr( 'wpcpo-fields[' . $this->field_id . '][level]' ); ?>">
            <option value="h1" <?php selected( $level, 'h1' ); ?>>H1</option>
            <option value="h2" <?php selected( $level, 'h2' ); ?>>H2</option>
            <option value="h3" <?php selected( $level, 'h3' ); ?>>H3</option>
            <option value="h4" <?php selected( $level, 'h4' ); ?>>H4</option>
            <option value="h5" <?php selected( $level, 'h5' ); ?>>H5</option>
            <option value="h6" <?php selected( $level, 'h6' ); ?>>H6</option>
        </select> </label>
</div>
