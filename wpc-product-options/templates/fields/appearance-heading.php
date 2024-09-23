<?php
/**
 * @var $field
 * @var $key
 */

defined( 'ABSPATH' ) || exit;

echo '<' . $field['level'] . '>' . esc_html( $field['heading'] ) . '</' . $field['level'] . '>';
