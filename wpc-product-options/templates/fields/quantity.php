<?php
/**
 * @var $field
 * @var $key
 */

defined( 'ABSPATH' ) || exit;

global $product;

if ( is_a( $product, 'WC_Product' ) ) {
	$qty_args = apply_filters( 'wpcpo_quantity_input_args', [
		'input_id'   => $key,
		'input_name' => 'quantity'
	], $product );
	woocommerce_quantity_input( $qty_args, $product );
}
