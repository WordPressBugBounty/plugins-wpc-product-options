<?php
/**
 * @var $this
 * @var $options
 * @var $type
 */

defined( 'ABSPATH' ) || exit;
?>
<div class="wpcpo-inner-options">
    <div class="inner-header">
        <?php
        if ( $type === 'image-radio' || $type === 'image-checkbox' ) {
            echo '<span class="inner-header-image">' . esc_html__( 'Image', 'wpc-product-options' ) . '</span>';
        }

        if ( $type === 'color-radio' || $type === 'color-checkbox' ) {
            echo '<span class="inner-header-color">' . esc_html__( 'Color', 'wpc-product-options' ) . '</span>';
        }
        ?>
        <span class="inner-header-name"><?php esc_html_e( 'Label', 'wpc-product-options' ); ?></span>
        <span class="inner-header-value"><?php esc_html_e( 'Value *', 'wpc-product-options' ); ?></span>
        <span class="inner-header-price"><?php esc_html_e( 'Price', 'wpc-product-options' ); ?></span>
    </div>
    <div class="inner-content">
        <?php
        foreach ( $options as $k => $option ) {
            $this->get_option( $option, $type );
        }
        ?>
    </div>
    <div class="inner-footer">
        <button type="button" class="button wpcpo-add-new-option"
                data-id="<?php echo esc_attr( $this->field_id ); ?>"><?php esc_html_e( 'Add option', 'wpc-product-options' ); ?></button>
    </div>
</div>
