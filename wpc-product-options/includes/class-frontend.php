<?php
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Wpcpo_Frontend' ) ) {
	class Wpcpo_Frontend {
		private static $apply = [];
		protected static $instance = null;

		public static function instance() {
			if ( is_null( self::$instance ) ) {
				self::$instance = new self();
			}

			return self::$instance;
		}

		public function __construct() {
			add_action( 'init', [ $this, 'init' ] );
			add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_scripts' ], 99 );
			add_filter( 'woocommerce_loop_add_to_cart_link', [ $this, 'add_to_cart_link' ], 10, 2 );

			if ( Wpcpo_Backend::get_setting( 'position', 'above_atc' ) === 'above_atc' ) {
				add_action( 'woocommerce_before_add_to_cart_button', [ $this, 'display' ], 10 );
				add_action( 'woocommerce_before_variations_form', [ $this, 'display_variable_before' ] );
			}

			if ( Wpcpo_Backend::get_setting( 'position', 'above_atc' ) === 'below_atc' ) {
				add_action( 'woocommerce_after_add_to_cart_button', [ $this, 'display' ], 10 );
				add_action( 'woocommerce_after_variations_form', [ $this, 'display_variable_after' ] );
			}
		}

		public function init() {
			$args = [
				'fields'         => 'ids',
				'posts_per_page' => 500,
				'post_type'      => 'wpc_product_option',
			];

			$posts = get_posts( $args );

			if ( ! empty( $posts ) ) {
				foreach ( $posts as $post_id ) {
					$apply_for = get_post_meta( $post_id, 'wpcpo-apply-for', true ) ?: 'none';
					$apply     = (array) get_post_meta( $post_id, 'wpcpo-apply', true ) ?: [];

					self::$apply[ $post_id ]['apply_for'] = $apply_for;
					self::$apply[ $post_id ]['apply']     = $apply;
				}
			}

			add_shortcode( 'wpcpo', [ $this, 'shortcode' ] );
		}

		public static function add_to_cart_link( $link, $product ) {
			if ( ( $fields = self::get_required_fields( $product ) ) && ! empty( $fields ) ) {
				$link = sprintf(
					'<a href="%s" class="button">%s</a>',
					esc_url( $product->get_permalink() ),
					esc_html__( 'Select options', 'wpc-product-options' )
				);
			}

			return $link;
		}

		public function enqueue_scripts() {
			if ( is_admin() ) {
				return;
			}

			// wpcdpk
			wp_enqueue_style( 'wpcdpk', WPCPO_URI . 'assets/libs/wpcdpk/css/datepicker.css' );
			wp_enqueue_script( 'wpcdpk', WPCPO_URI . 'assets/libs/wpcdpk/js/datepicker.js', [ 'jquery' ], WPCPO_VERSION, true );

			wp_enqueue_style( 'wpcpo-frontend', WPCPO_URI . 'assets/css/frontend.css' );

			// Enqueuing CSS stylesheet for Iris (the easy part)
			wp_enqueue_style( 'wp-color-picker' );

			// Manually enqueing Iris itself by linking directly to it and naming its dependencies
			wp_enqueue_script(
				'iris',
				admin_url( 'js/iris.min.js' ),
				[
					'jquery-ui-draggable',
					'jquery-ui-slider',
					'jquery-touch-punch',
					'wp-i18n'
				],
				false,
				1
			);

			// Now we can enqueue the color-picker script itself, naming iris.js as its dependency
			wp_enqueue_script(
				'wp-color-picker',
				admin_url( 'js/color-picker.min.js' ),
				[ 'iris' ],
				false,
				1
			);

			// Manually passing text strings to the JavaScript
			$colorpicker_l10n = [
				'clear'         => esc_html__( 'Clear', 'wpc-product-options' ),
				'defaultString' => esc_html__( 'Default', 'wpc-product-options' ),
				'pick'          => esc_html__( 'Select Color', 'wpc-product-options' ),
				'current'       => esc_html__( 'Current Color', 'wpc-product-options' ),
			];
			wp_localize_script(
				'wp-color-picker',
				'wpColorPickerL10n',
				$colorpicker_l10n
			);

			wp_enqueue_script( 'wpcpo-frontend', WPCPO_URI . 'assets/js/frontend.js', [ 'jquery' ], WPCPO_VERSION, true );
			wp_localize_script( 'wpcpo-frontend', 'wpcpo_vars', [
				'i18n_addon_total'         => esc_html__( 'Options total:', 'wpc-product-options' ),
				'i18n_subtotal'            => esc_html__( 'Subtotal:', 'wpc-product-options' ),
				'i18n_remaining'           => esc_html__( 'characters remaining', 'wpc-product-options' ),
				'price_decimals'           => wc_get_price_decimals(),
				'currency_symbol'          => get_woocommerce_currency_symbol(),
				'price_format'             => get_woocommerce_price_format(),
				'price_decimal_separator'  => wc_get_price_decimal_separator(),
				'price_thousand_separator' => wc_get_price_thousand_separator(),
				'trim_zeros'               => apply_filters( 'woocommerce_price_trim_zeros', false ),
				'quantity_symbol'          => '&times;',
				'is_rtl'                   => is_rtl(),
			] );
		}

		public function shortcode() {
			ob_start();
			self::display();

			return ob_get_clean();
		}

		public function display() {
			global $product;

			if ( ! $product || 'grouped' === $product->get_type() || 'external' === $product->get_type() ) {
				return;
			}

			$fields = self::get_fields( $product );

			if ( is_array( $fields ) && count( $fields ) <= 0 ) {
				return;
			}

			wc_get_template(
				'options.php',
				[
					'frontend'   => $this,
					'fields'     => $fields,
					'product_id' => $product->get_id()
				],
				'wpc-product-options',
				WPCPO_DIR . 'templates/'
			);
		}

		public static function get_fields( $product ) {
			if ( is_numeric( $product ) ) {
				$product_id = $product;
			} else {
				$product_id = $product->get_id();
			}

			if ( ! $product_id ) {
				return [];
			}

			$fields          = [];
			$product_display = get_post_meta( $product_id, 'wpcpo-display', true );

			if ( $product_display === 'override' ) {
				$fields = self::get_fields_in_product( $product_id );

				if ( is_array( $fields ) && count( $fields ) <= 0 ) {
					return [];
				}
			} elseif ( $product_display === 'disable' ) {
				return [];
			} else {
				// global
				foreach ( self::$apply as $_id => $item ) {
					if ( $item['apply_for'] !== 'none' ) {
						if ( $item['apply_for'] === 'all' ) {
							$fields = array_merge( $fields, self::get_fields_in_product( $_id ) );
						} else {
							if ( has_term( $item['apply'], $item['apply_for'], $product_id ) ) {
								$fields = array_merge( $fields, self::get_fields_in_product( $_id ) );
							}
						}
					}
				}

				// prepend or append
				if ( $product_display === 'prepend' || $product_display === 'append' ) {
					$product_fields = self::get_fields_in_product( $product_id );

					if ( $product_display === 'prepend' ) {
						// prepend
						$fields = array_merge( $product_fields, $fields );
					} else {
						// append
						$fields = array_merge( $fields, $product_fields );
					}
				}
			}

			return $fields;
		}

		public static function get_required_fields( $product ) {
			$fields = self::get_fields( $product );

			if ( empty( $fields ) ) {
				return [];
			}

			foreach ( $fields as $key => $field ) {
				if ( empty( $field['required'] ) ) {
					unset( $fields[ $key ] );
				}
			}

			return $fields;
		}

		public static function get_file_fields( $product ) {
			$fields = self::get_fields( $product );

			if ( empty( $fields ) ) {
				return [];
			}

			foreach ( $fields as $key => $field ) {
				if ( empty( $field['type'] ) || ( $field['type'] !== 'file-upload' ) ) {
					unset( $fields[ $key ] );
				}
			}

			return $fields;
		}

		public static function get_fields_in_product( $post_id ) {
			$fields = get_post_meta( $post_id, 'wpcpo-fields', true );

			if ( ! $fields ) {
				$fields = [];
			}

			return $fields;
		}

		public function total_price_settings() {
			global $product;
			$post_id = get_the_ID();

			if ( ! isset( $product ) || $product->get_id() != $post_id ) {
				$the_product = wc_get_product( $post_id );
			} else {
				$the_product = $product;
			}

			if ( is_object( $the_product ) ) {
				$tax_display_mode = get_option( 'woocommerce_tax_display_shop' );
				$display_price    = 'incl' === $tax_display_mode ? wc_get_price_including_tax( $the_product ) : wc_get_price_excluding_tax( $the_product );
			} else {
				$display_price = '';
			}

			echo '<div class="wpcpo-total" data-product-name="' . esc_attr( get_the_title() ) . '" data-type="' . esc_attr( $the_product->get_type() ) . '" data-o_price="' . esc_attr( $display_price ) . '" data-price="' . esc_attr( $display_price ) . '" data-product-id="' . esc_attr( $post_id ) . '"></div>';
		}

		public static function get_label_price( $field, $type = '' ) {
			$label = '';

			if ( ! empty( $field['required'] ) ) {
				$label .= '* ';
			}

			$label_class = 'label-price';

			if ( ! empty( $field['id'] ) ) {
				$label_class .= ' label-price-' . $field['id'];
			}

			if ( $field['price_type'] === 'custom' ) {
				$label_class .= ' label-price-custom';
			}

			$label .= '<span class="' . esc_attr( $label_class ) . '">';

			if ( ! empty( $field['enable_price'] ) || ( ! empty( $field['price'] ) && $type == 'option' ) ) {
				if ( $field['price'] == '' || $field['price'] == '0' ) {
					$label .= '';
				} elseif ( in_array( $field['price_type'], [ 'qty', 'flat' ] ) ) {
					if ( ! str_contains( $field['price'], '%' ) ) {
						if ( ! str_contains( $field['price'], '-' ) ) {
							$label .= '(+' . wc_price( $field['price'] ) . ')';
						} else {
							$label .= '(' . wc_price( $field['price'] ) . ')';
						}
					}
				}
			}

			$label .= '</span>';

			return apply_filters( 'wpcpo_get_label_price', $label, $field, $type );
		}

		public static function get_min_max_attr( $field ) {
			$attr = '';

			if ( ! empty( $field['enable_limit'] ) ) {
				if ( ! empty( $field['min'] ) ) {
					$attr .= ' minlength="' . esc_attr( $field['min'] ) . '"';
				}

				if ( ! empty( $field['max'] ) ) {
					$attr .= ' maxlength="' . esc_attr( $field['max'] ) . '"';
				}
			}

			return $attr;
		}

		public static function get_min_max_step_attr( $field ) {
			$attr = '';

			if ( ! empty( $field['enable_limit'] ) ) {
				if ( ! empty( $field['min'] ) ) {
					$attr .= ' min="' . esc_attr( $field['min'] ) . '"';
				}

				if ( ! empty( $field['step'] ) ) {
					$attr .= ' step="' . esc_attr( $field['step'] ) . '"';
				}

				if ( ! empty( $field['max'] ) ) {
					$attr .= ' max="' . esc_attr( $field['max'] ) . '"';
				}
			}

			return $attr;
		}

		public function display_variable_before() {
			remove_action( 'woocommerce_before_add_to_cart_button', [ $this, 'display' ], 10 );
			add_action( 'woocommerce_single_variation', [ $this, 'display' ], 15 );
		}

		public function display_variable_after() {
			remove_action( 'woocommerce_after_add_to_cart_button', [ $this, 'display' ], 10 );
			add_action( 'woocommerce_single_variation', [ $this, 'display' ], 25 );
		}

		public static function js_datetime_format( $datetime_format ) {
			$replace = [
				// Day
				'd' => 'dd',
				'D' => 'D',
				'j' => 'd',
				'l' => 'DD',
				'z' => 'o',
				// Month
				'F' => 'MM',
				'm' => 'mm',
				'n' => 'm',
				// Year
				'y' => 'yy',
				'Y' => 'yyyy',
				// Time
				'a' => 'aa',
				'A' => 'AA',
				'g' => 'hh',
				'H' => 'hh',
				'i' => 'ii',
			];

			return str_replace( array_keys( $replace ), array_values( $replace ), $datetime_format );
		}
	}
}

return Wpcpo_Frontend::instance();
