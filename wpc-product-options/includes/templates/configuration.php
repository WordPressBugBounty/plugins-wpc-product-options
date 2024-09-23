<?php
defined( 'ABSPATH' ) || exit;

$apply_for = get_post_meta( get_the_ID(), 'wpcpo-apply-for', true );
$terms     = get_post_meta( get_the_ID(), 'wpcpo-apply', true );
$terms     = ( is_array( $terms ) ) ? $terms : [];
?>
<table class="form-table">
    <tr>
        <th style="width: 80px"><?php esc_html_e( 'Apply', 'wpc-product-options' ); ?></th>
        <td>
            <select class="wpcpo-apply-for" name="wpcpo-apply-for">
                <option value="none" <?php selected( $apply_for, 'none' ); ?>><?php esc_html_e( 'None', 'woo-product-timer' ); ?></option>
                <option value="all" <?php selected( $apply_for, 'all' ); ?>><?php esc_html_e( 'All products', 'woo-product-timer' ); ?></option>
				<?php
				$taxonomies = get_object_taxonomies( 'product', 'objects' ); //$taxonomies = get_taxonomies( [ 'object_type' => [ 'product' ] ], 'objects' );

				foreach ( $taxonomies as $taxonomy ) {
					echo '<option value="' . esc_attr( $taxonomy->name ) . '" ' . selected( $apply_for, $taxonomy->name, false ) . '>' . esc_html( $taxonomy->label ) . '</option>';
				}
				?>
            </select>
            <div class="wpcpo-apply-val" style="margin-top: 10px">
                <select class="wpcpo-apply" name="wpcpo-apply[]" multiple="multiple" data-<?php echo esc_attr( $apply_for ); ?>="<?php echo esc_attr( implode( ',', $terms ) ); ?>">
					<?php
					if ( ! empty( $terms ) ) {
						foreach ( $terms as $t ) {
							if ( $term = get_term_by( 'slug', $t, $apply_for ) ) {
								echo '<option value="' . esc_attr( $t ) . '" selected>' . esc_html( $term->name ) . '</option>';
							}
						}
					}
					?>
                </select>
            </div>
            <p class="description"><?php esc_html_e( 'Select which products that you want to apply these options. You still can specify options on a product basis.', 'wpc-product-options' ); ?></p>
        </td>
    </tr>
</table>
