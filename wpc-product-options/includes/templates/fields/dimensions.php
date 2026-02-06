<?php
/**
 * @var $this
 * @var $type
 */

defined( 'ABSPATH' ) || exit;

$price_type = $this->get_field_value( 'price_type', 'flat' );
?>
    <input type="hidden" name="<?php echo esc_attr( 'wpcpo-fields[' . $this->field_id . '][type]' ); ?>"
           class="wpcpo-type-val" value="<?php echo esc_attr( $type ); ?>"/>
    <div class="wpcpo-item-line">
        <label><strong><?php esc_html_e( 'Title', 'wpc-product-options' ); ?> *</strong>
            <input type="text" class="input-block sync-label field-logic wpcpo-input-not-empty"
                   name="<?php echo esc_attr( 'wpcpo-fields[' . $this->field_id . '][title]' ); ?>"
                   value="<?php echo esc_attr( $this->get_field_value( 'title', ucwords( str_replace( '-', ' ', $type ) ) ) ); ?>">
        </label>
    </div>
    <div class="wpcpo-item-line">
        <label>
            <input type="checkbox" value="1"
                   name="<?php echo esc_attr( 'wpcpo-fields[' . $this->field_id . '][hide_title]' ); ?>" <?php checked( $this->get_field_value( 'hide_title' ), '1' ); ?>> <?php esc_html_e( 'Hide title', 'wpc-product-options' ); ?>
        </label>
    </div>
    <div class="wpcpo-item-line">
        <label>
            <input type="checkbox" value="1"
                   name="<?php echo esc_attr( 'wpcpo-fields[' . $this->field_id . '][show_desc]' ); ?>" <?php checked( $this->get_field_value( 'show_desc' ), '1' ); ?>> <?php esc_html_e( 'Add description', 'wpc-product-options' ); ?>
            <textarea class="input-block checkbox-show"
                      name="<?php echo esc_attr( 'wpcpo-fields[' . $this->field_id . '][desc]' ); ?>"><?php echo esc_textarea( $this->get_field_value( 'desc' ) ); ?></textarea>
        </label>
    </div>
    <div class="wpcpo-item-line">
        <label>
            <input type="checkbox" value="1" class="checkbox-required"
                   name="<?php echo esc_attr( 'wpcpo-fields[' . $this->field_id . '][required]' ); ?>" <?php checked( $this->get_field_value( 'required' ), '1' ); ?>> <?php esc_html_e( 'Required', 'wpc-product-options' ); ?>
        </label>
    </div>
    <div class="wpcpo-item-line">
        <div class="wpcpo-inner-options">
            <div class="inner-header">
                <span class="inner-header-name"><?php esc_html_e( 'Label', 'wpc-product-options' ); ?></span>
                <span class="inner-header-default"><?php esc_html_e( 'Default value', 'wpc-product-options' ); ?></span>
                <span class="inner-header-min"><?php esc_html_e( 'Min', 'wpc-product-options' ); ?></span>
                <span class="inner-header-max"><?php esc_html_e( 'Max', 'wpc-product-options' ); ?></span>
                <span class="inner-header-step"><?php esc_html_e( 'Step', 'wpc-product-options' ); ?></span>
            </div>
            <div class="inner-content">
                <?php
                $dimensions = $this->get_field_value( 'dimensions', [] );

                foreach ( $dimensions as $k => $option ) {
                    $this->get_dimension( $option, $type );
                }
                ?>
            </div>
            <div class="inner-footer">
                <button type="button" class="button wpcpo-add-new-dimension"
                        data-id="<?php echo esc_attr( $this->field_id ); ?>"><?php esc_html_e( 'Add dimension', 'wpc-product-options' ); ?></button>
            </div>
        </div>
    </div>
    <div class="wpcpo-item-line">
        <label>
            <input type="checkbox" value="1"
                   name="<?php echo esc_attr( 'wpcpo-fields[' . $this->field_id . '][enable_price]' ); ?>" <?php checked( $this->get_field_value( 'enable_price', '0' ), '1' ); ?>> <?php esc_html_e( 'Adjust price', 'wpc-product-options' ); ?>
            <div class="checkbox-show">
                <p class="description"><?php esc_html_e( 'In the custom formula, you can use d1, d2, d3, etc., which correspond to the values of the above dimensions. For example: d1*d2*10. If you use a value that does not exist, it will be 0.', 'wpc-product-options' ); ?></p>
                <select class="option-type <?php echo esc_attr( 'type-' . $price_type ); ?>"
                        name="<?php echo esc_attr( 'wpcpo-fields[' . $this->field_id . '][price_type]' ); ?>">
                    <option value="flat" <?php selected( $price_type, 'flat' ); ?>><?php esc_html_e( 'Flat Fee', 'wpc-product-options' ); ?></option>
                    <option value="qty" <?php selected( $price_type, 'qty' ); ?>><?php esc_html_e( 'Quantity Synced', 'wpc-product-options' ); ?></option>
                    <option value="custom" <?php selected( $price_type, 'custom' ); ?>><?php esc_html_e( 'Custom Formula', 'wpc-product-options' ); ?></option>
                </select> <span>—</span>
                <span class="wpcpo-price-wrapper hint--right"
                      aria-label="<?php esc_attr_e( 'Set a price using a number (eg. "10") or percentage (eg. "10%" of product price)', 'wpc-product-options' ); ?>">
                <input type="text" class="wpcpo-price"
                       name="<?php echo esc_attr( 'wpcpo-fields[' . $this->field_id . '][price]' ); ?>"
                       value="<?php echo esc_attr( $this->get_field_value( 'price' ) ); ?>"/>
            </span>
                <span class="wpcpo-price-custom-wrapper hint--right"
                      aria-label="<?php esc_attr_e( 'You can use: p (product price); q (quantity); l (string length); w (words count); v (value) in the formula, e.g: (p+2)*q/2', 'wpc-product-options' ); ?>">
                <input type="text" class="wpcpo-price-custom"
                       name="<?php echo esc_attr( 'wpcpo-fields[' . $this->field_id . '][custom_price]' ); ?>"
                       value="<?php echo esc_attr( $this->get_field_value( 'custom_price' ) ); ?>" readonly/> This feature is only available on the premium version. Click <a href="https://wpclever.net/downloads/product-options/" target="_blank">here</a> to buy it for just $29!
            </span>
            </div>
        </label>
    </div>
<?php $this->get_conditions(); ?>