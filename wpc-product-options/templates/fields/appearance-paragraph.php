<?php
/**
 * @var $field
 * @var $key
 */

defined( 'ABSPATH' ) || exit;

echo wp_kses_post( wpautop( $field['paragraph'] ) );
