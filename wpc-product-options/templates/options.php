<?php
/**
 * @var $frontend
 * @var $fields
 * @var $product_id
 */

defined( 'ABSPATH' ) || exit;
?>
<div class="<?php echo esc_attr( apply_filters( 'wpcpo_wrap_class', 'wpcpo-wrapper wpcpo-wrap wpcpo-wrap-' . $product_id, $product_id ) ); ?>">
	<?php do_action( 'wpcpo_wrap_before', $fields, $product_id ); ?>
    <div class="wpcpo-options">
		<?php do_action( 'wpcpo_options_before', $fields, $product_id ); ?>
		<?php foreach ( $fields as $key => $field ) {
			$field = wp_parse_args( $field, [
				'type'          => '',
				'required'      => '',
				'enable_price'  => '',
				'price_type'    => '',
				'price'         => '',
				'custom_price'  => '',
				'hide_title'    => '',
				'show_desc'     => '',
				'desc'          => '',
				'enable_limit'  => '',
				'min'           => '',
				'step'          => '',
				'max'           => '',
				'default_value' => '',
				'value'         => '',
				'limit'         => '',
				'id'            => $key,
			] );

			$field_class = 'wpcpo-option wpcpo-option-' . $field['type'];
			$field_class .= $field['required'] ? ' wpcpo-required' : '';
			?>
            <div class="<?php echo esc_attr( $field_class ); ?>" <?php echo( ( $field['type'] === 'checkbox' || $field['type'] === 'image-checkbox' ) && ! empty( $field['limit'] ) ? 'data-limit="' . esc_attr( $field['limit'] ) . '"' : '' ); ?>>
				<?php if ( strpos( $field['type'], 'appearance-' ) !== false ) {
					wc_get_template(
						'fields/' . $field['type'] . '.php',
						[
							'field' => $field,
							'key'   => $key,
						],
						'wpc-product-options',
						WPCPO_DIR . 'templates/'
					);
				} else {
					if ( ! $field['hide_title'] ) { ?>
                        <label class="wpcpo-option-name" for="<?php echo esc_attr( $key ); ?>">
                            <strong><?php echo esc_html( $field['title'] ); ?></strong>
                            <span><?php echo Wpcpo_Frontend::get_label_price( $field ); ?></span> </label>
						<?php
					}
					if ( $field['show_desc'] && $field['desc'] !== '' ) { ?>
                        <div class="wpcpo-option-description">
							<?php echo wp_kses_post( wpautop( $field['desc'] ) ); ?>
                        </div>
					<?php } ?>
                    <div class="wpcpo-option-form">
                        <p class="form-row">
							<?php
							wc_get_template(
								'fields/' . $field['type'] . '.php',
								[
									'field' => $field,
									'key'   => $key,
								],
								'wpc-product-options',
								WPCPO_DIR . 'templates/'
							);
							?>
                            <input type="hidden" name="<?php echo esc_attr( $key . '[title]' ); ?>" value="<?php echo esc_attr( $field['title'] ); ?>"/>
                        </p>
                    </div>
				<?php } ?>
            </div>
		<?php } ?>
		<?php do_action( 'wpcpo_options_after', $fields, $product_id ); ?>
    </div>
	<?php $frontend->total_price_settings(); ?>
	<?php do_action( 'wpcpo_wrap_after', $fields, $product_id ); ?>
</div>
