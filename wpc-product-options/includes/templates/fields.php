<?php
/**
 * @var $this
 * @var $fields []
 */

defined( 'ABSPATH' ) || exit;
?>
<div class="wpcpo-items-wrapper">
    <div class="wpcpo-items">
		<?php foreach ( $fields as $key => $field ) {
			$this->get_field( $field['type'], $key, $field );
		} ?>
    </div>
    <div class="wpcpo-items-new">
        <select id="wpcpo-item-type">
            <option value="text"><?php esc_html_e( 'Text', 'wpc-product-options' ); ?></option>
            <option value="number"><?php esc_html_e( 'Number', 'wpc-product-options' ); ?></option>
            <option value="email"><?php esc_html_e( 'Email', 'wpc-product-options' ); ?></option>
            <option value="textarea"><?php esc_html_e( 'Textarea', 'wpc-product-options' ); ?></option>
            <option value="select"><?php esc_html_e( 'Select', 'wpc-product-options' ); ?></option>
            <option value="radio"><?php esc_html_e( 'Radio', 'wpc-product-options' ); ?></option>
            <option value="image-radio"><?php esc_html_e( 'Image Radio', 'wpc-product-options' ); ?></option>
            <option value="checkbox"><?php esc_html_e( 'Checkbox', 'wpc-product-options' ); ?></option>
            <option value="image-checkbox"><?php esc_html_e( 'Image Checkbox', 'wpc-product-options' ); ?></option>
            <option value="date-picker"><?php esc_html_e( 'Date picker', 'wpc-product-options' ); ?></option>
            <option value="time-picker"><?php esc_html_e( 'Time picker', 'wpc-product-options' ); ?></option>
            <option value="date-time-picker"><?php esc_html_e( 'Date time picker', 'wpc-product-options' ); ?></option>
            <option value="date-range-picker"><?php esc_html_e( 'Date range picker', 'wpc-product-options' ); ?></option>
            <option value="color-picker"><?php esc_html_e( 'Color picker', 'wpc-product-options' ); ?></option>
            <option value="file-upload"><?php esc_html_e( 'File upload', 'wpc-product-options' ); ?></option>
            <optgroup label="<?php esc_attr_e( 'Appearance', 'wpc-product-options' ); ?>">
                <option value="appearance-heading"><?php esc_html_e( 'Heading', 'wpc-product-options' ); ?></option>
                <option value="appearance-paragraph"><?php esc_html_e( 'Paragraph', 'wpc-product-options' ); ?></option>
                <option value="appearance-spacer"><?php esc_html_e( 'Spacer', 'wpc-product-options' ); ?></option>
                <option value="appearance-separator"><?php esc_html_e( 'Separator', 'wpc-product-options' ); ?></option>
                <option value="appearance-shortcode"><?php esc_html_e( 'Shortcode', 'wpc-product-options' ); ?></option>
            </optgroup>
        </select>
        <input type="button" class="button wpcpo-item-new" value="<?php esc_attr_e( '+ Add new field', 'wpc-product-options' ); ?>">
    </div>
</div>
