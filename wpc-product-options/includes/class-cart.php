<?php
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Wpcpo_Cart' ) ) {
	class Wpcpo_Cart {
		protected static $instance = null;

		public static function instance() {
			if ( is_null( self::$instance ) ) {
				self::$instance = new self();
			}

			return self::$instance;
		}

		public function __construct() {
			// Load cart data per page load
			add_filter( 'woocommerce_get_cart_item_from_session', [ $this, 'get_cart_item_from_session' ], 20, 2 );

			// Validation
			add_filter( 'woocommerce_add_to_cart_validation', [ $this, 'add_to_cart_validation' ], 10, 2 );

			// Add item data to the cart
			add_filter( 'woocommerce_add_cart_item_data', [ $this, 'add_cart_item_data' ], 11, 2 );

			// Get item data to display
			add_filter( 'woocommerce_get_item_data', [ $this, 'get_item_data' ], 10, 2 );

			// Add meta to order
			add_filter( 'woocommerce_checkout_create_order_line_item', [ $this, 'order_line_item' ], 10, 3 );

			// Before calculate totals
			add_action( 'woocommerce_before_mini_cart_contents', [ $this, 'before_mini_cart_contents' ], 999999 );
			add_action( 'woocommerce_before_calculate_totals', [ $this, 'before_calculate_totals' ], 999999 );

			// Cart item price & subtotal
			add_filter( 'woocommerce_cart_item_price', [ $this, 'cart_item_price' ], 999999, 2 );
			add_filter( 'woocommerce_cart_item_subtotal', [ $this, 'cart_item_subtotal' ], 999999, 2 );
		}

		private function clean_custom_price( $custom_price ) {
			return preg_replace( '/[^0-9\+\-\*\/\(\)\.vpqlws]/', '', $custom_price );
		}

		private function word_count( $string ) {
			$formatted_string = preg_replace( '/\s+/', ' ', trim( wp_strip_all_tags( $string ) ) );
			$words            = explode( ' ', $formatted_string );

			return apply_filters( 'wpcpo_word_count', count( $words ), $string );
		}

		private function get_custom_price( $custom_price, $quantity, $product_price, $value, $total = 0 ) {
			return 0;
		}

		public function add_to_cart_validation( $passed, $product_id ) {
			if ( isset( $_REQUEST['order_again'] ) ) {
				return $passed;
			}

			$_product = wc_get_product( $product_id );

			// check required
			if ( ( $fields = Wpcpo_Frontend::get_required_fields( $_product ) ) && ! empty( $fields ) ) {
				$post_data           = $_REQUEST;
				$has_required_fields = true;

				foreach ( $fields as $key => $field ) {
					if ( ! empty( $field['options'] ) ) {
						$has_required_options = false;

						foreach ( $field['options'] as $option_key => $option ) {
							if ( isset( $post_data[ $option_key ]['value'] ) && $post_data[ $option_key ]['value'] != '' ) {
								$has_required_options = true;
								break;
							}
						}

						if ( isset( $post_data[ $key ]['value'] ) && $post_data[ $key ]['value'] != '' ) {
							$has_required_options = true;
						}

						if ( ! $has_required_options ) {
							$has_required_fields = false;
							break;
						}
					} else {
						if ( ! isset( $post_data[ $key ] ) || ! isset( $post_data[ $key ]['value'] ) || $post_data[ $key ]['value'] == '' ) {
							$has_required_fields = false;
							break;
						}
					}
				}

				if ( ! $has_required_fields ) {
					wc_add_notice( esc_html__( 'You cannot add this product to the cart.', 'wpc-product-options' ), 'error' );

					return false;
				}
			}

			// check file
			if ( ( $fields = Wpcpo_Frontend::get_file_fields( $_product ) ) && ! empty( $fields ) ) {
				$post_data = $_REQUEST;

				foreach ( $post_data as $key => $data ) {
					if ( str_starts_with( $key, 'wpcpo-' ) && isset( $data['type'] ) && $data['type'] === 'file' && isset( $data['value'] ) && $data['value'] !== '' ) {
						// check file
						if ( ! empty( $_FILES[ $key ] ) && ! empty( $_FILES[ $key ]['name'] ) && isset( $fields[ $key ] ) ) {
							$exts     = array_map( 'trim', explode( ',', Wpcpo_Backend::upload_filetypes( $fields[ $key ]['filetypes'] ) ) );
							$ext      = '.' . strtolower( pathinfo( $_FILES[ $key ]['name'], PATHINFO_EXTENSION ) );
							$size_min = absint( $fields[ $key ]['size_min'] ?? 0 );
							$size_max = absint( $fields[ $key ]['size_max'] ?? wp_max_upload_size() );

							if ( ! in_array( $ext, $exts ) ) {
								wc_add_notice( esc_html__( 'You cannot add this product to the cart.', 'wpc-product-options' ), 'error' );
								wc_add_notice( esc_html__( 'The uploaded file has an incorrect extension.', 'wpc-product-options' ), 'error' );

								return false;
							}

							if ( $size_min && ( $size_min > absint( $_FILES[ $key ]['size'] ) ) ) {
								wc_add_notice( esc_html__( 'You cannot add this product to the cart.', 'wpc-product-options' ), 'error' );
								wc_add_notice( sprintf( /* translators: file size */ esc_html__( 'The uploaded file must be larger than %s in size.', 'wpc-product-options' ), size_format( $size_min ) ), 'error' );

								return false;
							}

							if ( $size_max && ( $size_max < absint( $_FILES[ $key ]['size'] ) ) ) {
								wc_add_notice( esc_html__( 'You cannot add this product to the cart.', 'wpc-product-options' ), 'error' );
								wc_add_notice( sprintf( /* translators: file size */ esc_html__( 'The uploaded file exceeds %s in size.', 'wpc-product-options' ), size_format( $size_max ) ), 'error' );

								return false;
							}
						}
					}
				}
			}

			return $passed;
		}

		public function add_cart_item_data( $cart_item_data, $product_id ) {
			if ( ! isset( $_REQUEST ) || empty( $product_id ) || ! empty( $cart_item_data['wpcpo-options'] ) ) {
				return $cart_item_data;
			}

			$post_data    = $_REQUEST;
			$post_options = [];

			foreach ( $post_data as $key => $data ) {
				if ( str_starts_with( $key, 'wpcpo-' ) && isset( $data['value'] ) && $data['value'] !== '' ) {
					// file upload
					if ( isset( $data['type'] ) && $data['type'] === 'file' ) {
						if ( ! empty( $_FILES[ $key ] ) && ! empty( $_FILES[ $key ]['name'] ) ) {
							$upload           = $this->handle_upload( $_FILES[ $key ] );
							$data['value']    = basename( wc_clean( $upload['url'] ) );
							$data['file_url'] = wc_clean( $upload['url'] );
							$post_options[]   = $data;
						}
					} else {
						$post_options[] = $data;
					}

					if ( apply_filters( 'wpcpo_clear_request_data', true, $cart_item_data, $product_id ) ) {
						unset( $_REQUEST[ $key ] );
					}
				}
			}

			if ( ! empty( $post_options ) ) {
				$cart_item_data['wpcpo-options'] = $post_options;
			}

			return $cart_item_data;
		}

		public function handle_upload( $file ) {
			include_once( ABSPATH . 'wp-admin/includes/file.php' );
			include_once( ABSPATH . 'wp-admin/includes/media.php' );

			add_filter( 'upload_dir', [ $this, 'upload_dir' ] );

			$upload = wp_handle_upload( $file, [ 'test_form' => false ] );

			remove_filter( 'upload_dir', [ $this, 'upload_dir' ] );

			return $upload;
		}

		public function upload_dir( $path_data ) {
			global $woocommerce;

			$date_str = date( 'Ymd' );
			$user_str = md5( $woocommerce->session->get_customer_id() );
			$folder   = trim( apply_filters( 'wpcpo_upload_folder', $date_str . '/' . $user_str ), '/' );

			if ( empty( $path_data['subdir'] ) ) {
				$path_data['path']   = $path_data['path'] . '/wpcpo_uploads/' . $folder;
				$path_data['url']    = $path_data['url'] . '/wpcpo_uploads/' . $folder;
				$path_data['subdir'] = '/wpcpo_uploads/' . $folder;
			} else {
				$subdir              = '/wpcpo_uploads/' . $folder;
				$path_data['path']   = str_replace( $path_data['subdir'], $subdir, $path_data['path'] );
				$path_data['url']    = str_replace( $path_data['subdir'], $subdir, $path_data['url'] );
				$path_data['subdir'] = str_replace( $path_data['subdir'], $subdir, $path_data['subdir'] );
			}

			return apply_filters( 'wpcpo_upload_dir', $path_data );
		}

		public function get_cart_item_from_session( $cart_item, $session_values ) {
			if ( ! empty( $session_values['wpcpo-options'] ) ) {
				$cart_item['wpcpo-options'] = $session_values['wpcpo-options'];
			}

			return $cart_item;
		}

		public function get_item_data( $other_data, $cart_item ) {
			if ( ! empty( $cart_item['wpcpo-options'] ) ) {
				foreach ( $cart_item['wpcpo-options'] as $option ) {
					if ( isset( $option['value'] ) && ( $option['value'] !== '' ) ) {
						$data = [
							'name'    => $option['title'],
							'value'   => '<span class="' . esc_attr( 'wpcpo-item-data-value wpcpo-item-data-' . ( $option['type'] ?? 'default' ) ) . '">' . ( isset( $option['label'] ) && $option['label'] !== '' ? $option['label'] : $option['value'] ) . '</span>',
							'display' => '',
						];

						if ( ! empty( $option['type'] ) ) {
							if ( ( $option['type'] === 'color-picker' ) && apply_filters( 'wpcpo_cart_item_data_makeup', true, 'color-picker' ) ) {
								$data['value'] = '<span class="wpcpo-item-data-color box-color-picker" style="background: ' . $option['value'] . '"></span> ' . $option['value'];
							}

							if ( ( $option['type'] === 'image-radio' ) && ! empty( $option['image'] ) && apply_filters( 'wpcpo_cart_item_data_makeup', true, 'image-radio' ) ) {
								$data['value'] = '<span class="wpcpo-item-data-image box-image-radio">' . wp_get_attachment_image( $option['image'] ) . '</span>';
							}

							if ( ( $option['type'] === 'image-checkbox' ) && ! empty( $option['image'] ) && apply_filters( 'wpcpo_cart_item_data_makeup', true, 'image-checkbox' ) ) {
								$data['value'] = '<span class="wpcpo-item-data-image box-image-checkbox">' . wp_get_attachment_image( $option['image'] ) . '</span>';
							}

							if ( ( $option['type'] === 'file' ) && ! empty( $option['file_url'] ) && apply_filters( 'wpcpo_cart_item_data_makeup', true, 'file' ) ) {
								$data['value'] = '<span class="wpcpo-item-data-file"><a target="_blank" href="' . esc_url( $option['file_url'] ) . '">' . $option['value'] . '</a></span>';
							}
						}

						if ( ! empty( $option['display_price'] ) ) {
							$data['display'] = '<span class="' . esc_attr( 'wpcpo-item-data-display wpcpo-item-data-' . ( $option['type'] ?? 'default' ) ) . '">' . $data['value'] . ' <span class="wpcpo-item-data-price">(' . wc_price( $option['display_price'] ) . ')</span></span>';
						}

						$other_data[] = $data;
					}
				}
			}

			return $other_data;
		}

		public function order_line_item( $item, $cart_item_key, $values ) {
			if ( ! empty( $values['wpcpo-options'] ) ) {
				$options = [];

				foreach ( $values['wpcpo-options'] as $option ) {
					if ( isset( $option['value'] ) && ( $option['value'] !== '' ) ) {
						$option_key   = sanitize_title( $option['title'] );
						$option_value = isset( $option['label'] ) && ( $option['label'] !== '' ) ? $option['label'] : $option['value'];

						if ( ( $option['type'] === 'file' ) && ! empty( $option['file_url'] ) ) {
							$option_value = '<a target="_blank" href="' . esc_url( $option['file_url'] ) . '">' . $option['value'] . '</a>';
						}

						$item->add_meta_data( $option['title'], $option_value );
						$options[ $option_key ] = [
							'title' => $option['title'],
							'label' => $option['label'] ?? '',
							'value' => $option['value'],
						];
					}
				}

				if ( ! empty( $options ) ) {
					$item->add_meta_data( '_wpcpo_options', $options );
				}
			}
		}

		public function before_mini_cart_contents() {
			WC()->cart->calculate_totals();
		}

		public function before_calculate_totals( $cart_object ) {
			if ( ! defined( 'DOING_AJAX' ) && is_admin() ) {
				// This is necessary for WC 3.0+
				return;
			}

			foreach ( $cart_object->cart_contents as $cart_item_key => $cart_item ) {
				if ( ! empty( $cart_item['wpcpo-options'] ) ) {
					if ( apply_filters( 'wpcpo_ignore_recalculate_price', false, $cart_item_key, $cart_item ) ) {
						continue;
					}

					$quantity         = (float) apply_filters( 'wpcpo_cart_item_qty', $cart_item['quantity'], $cart_item );
					$price_bc         = $price = $cart_item['wpcpo_price_before_calc'] ?? (float) apply_filters( 'wpcpo_cart_item_price', $cart_item['data']->get_price(), $cart_item );
					$regular_price_bc = $regular_price = $cart_item['wpcpo_regular_price_before_calc'] ?? (float) apply_filters( 'wpcpo_cart_item_regular_price', $cart_item['data']->get_regular_price(), $cart_item );
					$sale_price_bc    = $sale_price = $cart_item['wpcpo_sale_price_before_calc'] ?? (float) apply_filters( 'wpcpo_cart_item_sale_price', $cart_item['data']->get_sale_price(), $cart_item );
					$is_on_sale       = apply_filters( 'wpcpo_cart_item_is_on_sale', $cart_item['data']->is_on_sale(), $cart_item );
					$total            = $price * $quantity; // calculate total for 's'

					if ( isset( WC()->cart->cart_contents[ $cart_item_key ]['woosb_price'] ) ) {
						$price_bc = $regular_price_bc = $sale_price_bc = (float) WC()->cart->cart_contents[ $cart_item_key ]['woosb_price'];
					}

					if ( isset( WC()->cart->cart_contents[ $cart_item_key ]['wooco_price'] ) ) {
						$price_bc = $regular_price_bc = $sale_price_bc = (float) WC()->cart->cart_contents[ $cart_item_key ]['wooco_price'];
					}

					// Save the price before price type calculations to be used later
					$cart_item['wpcpo_price_before_calc']         = $cart_item['wpcpo_price_before_calc'] ?? apply_filters( 'wpcpo_price_before_calc', $price_bc, $cart_item );
					$cart_item['wpcpo_regular_price_before_calc'] = $cart_item['wpcpo_regular_price_before_calc'] ?? apply_filters( 'wpcpo_regular_price_before_calc', $regular_price_bc, $cart_item );
					$cart_item['wpcpo_sale_price_before_calc']    = $cart_item['wpcpo_sale_price_before_calc'] ?? apply_filters( 'wpcpo_sale_price_before_calc', $sale_price_bc, $cart_item );

					foreach ( $cart_item['wpcpo-options'] as $key => $field ) {
						$price_type = ! empty( $field['price_type'] ) ? $field['price_type'] : '';
						$price_val  = ! empty( $field['price'] ) ? $field['price'] : 0;

						switch ( $price_type ) {
							case 'flat':
								if ( strpos( $price_val, '%' ) !== false ) {
									$calc_price    = $price_bc * (float) $price_val / 100;
									$price         += $calc_price / $quantity;
									$regular_price += $calc_price / $quantity;
									$sale_price    += $calc_price / $quantity;

									$cart_item['wpcpo-options'][ $key ]['display_price'] = $calc_price;
									$total                                               += $calc_price;
								} else {
									$price         += (float) $price_val / $quantity;
									$regular_price += (float) $price_val / $quantity;
									$sale_price    += (float) $price_val / $quantity;

									$cart_item['wpcpo-options'][ $key ]['display_price'] = (float) $price_val;
									$total                                               += (float) $price_val;
								}

								break;
							case 'custom':
								$calc_price    = $this->get_custom_price( $field['custom_price'], $quantity, $price_bc, $field['value'], $total );
								$price         += $calc_price / $quantity;
								$regular_price += $calc_price / $quantity;
								$sale_price    += $calc_price / $quantity;

								$cart_item['wpcpo-options'][ $key ]['display_price'] = $calc_price;
								$total                                               += $calc_price;

								break;
							default:
								// qty
								if ( strpos( $price_val, '%' ) !== false ) {
									$calc_price    = $price_bc * (float) $price_val / 100;
									$price         += $calc_price;
									$regular_price += $calc_price;
									$sale_price    += $calc_price;

									$cart_item['wpcpo-options'][ $key ]['display_price'] = $calc_price * $quantity;
									$total                                               += $calc_price * $quantity;
								} else {
									$price         += (float) $price_val;
									$regular_price += (float) $price_val;
									$sale_price    += (float) $price_val;

									$cart_item['wpcpo-options'][ $key ]['display_price'] = (float) $price_val * $quantity;
									$total                                               += (float) $price_val * $quantity;
								}

								break;
						}
					}

					$cart_item['wpcpo_price'] = $price;
					$cart_item['data']->set_price( $price );
					$cart_item['data']->set_regular_price( $regular_price );

					if ( $is_on_sale ) {
						$cart_item['data']->set_sale_price( $sale_price );
					}

					// save $cart_item
					WC()->cart->cart_contents[ $cart_item_key ] = $cart_item;
				}
			}
		}

		public function cart_item_price( $price, $cart_item ) {
			if ( ! empty( $cart_item['wpcpo_price'] ) ) {
				$price = (float) $cart_item['wpcpo_price'];

				if ( ! empty( $cart_item['woosb_price'] ) ) {
					$price += (float) $cart_item['woosb_price'];
				}

				if ( ! empty( $cart_item['woosb_discount_amount'] ) ) {
					$price += (float) $cart_item['woosb_discount_amount'];
				}

				if ( ! empty( $cart_item['wooco_price'] ) ) {
					$price += (float) $cart_item['wooco_price'];
				}

				return apply_filters( 'wpcpo_cart_item_price', wc_price( $price ), $cart_item );
			}

			return $price;
		}

		public function cart_item_subtotal( $subtotal, $cart_item = null ) {
			if ( ! empty( $cart_item['wpcpo_price'] ) ) {
				$price = (float) $cart_item['wpcpo_price'];

				if ( ! empty( $cart_item['woosb_price'] ) ) {
					$price += (float) $cart_item['woosb_price'];
				}

				if ( ! empty( $cart_item['woosb_discount_amount'] ) ) {
					$price += (float) $cart_item['woosb_discount_amount'];
				}

				if ( ! empty( $cart_item['wooco_price'] ) ) {
					$price += (float) $cart_item['wooco_price'];
				}

				$subtotal = wc_price( $price * (float) $cart_item['quantity'] );

				if ( wc_tax_enabled() && WC()->cart->display_prices_including_tax() && ! wc_prices_include_tax() ) {
					$subtotal .= ' <small class="tax_label">' . WC()->countries->inc_tax_or_vat() . '</small>';
				}

				return apply_filters( 'wpcpo_cart_item_subtotal', $subtotal, $cart_item );
			}

			return $subtotal;
		}
	}
}

return Wpcpo_Cart::instance();
