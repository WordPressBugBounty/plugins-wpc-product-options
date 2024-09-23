<?php
/**
 * @var $field
 * @var $key
 */

defined( 'ABSPATH' ) || exit;

echo wp_kses_post( do_shortcode( $field['shortcode'] ) );
