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
			add_action( 'wp', [ $this, 'process_file_link' ] );

			// Load cart data per page load
			add_filter( 'woocommerce_get_cart_item_from_session', [ $this, 'get_cart_item_from_session' ], 20, 2 );

			// Validation
			add_filter( 'woocommerce_add_to_cart_validation', [ $this, 'add_to_cart_validation' ], 10, 2 );

			// Add item data to the cart
			add_filter( 'woocommerce_add_cart_item_data', [ $this, 'add_cart_item_data' ], 11, 2 );

			// Get item data to display
			add_filter( 'woocommerce_get_item_data', [ $this, 'get_item_data' ], 10, 2 );

			// Add meta to order
			add_action( 'woocommerce_checkout_create_order_line_item', [ $this, 'order_line_item' ], 10, 3 );
			add_action( 'woocommerce_order_item_meta_start', [ $this, 'order_item_meta' ], 10, 2 );

			// Admin order item meta
			add_action( 'woocommerce_before_order_itemmeta', [ $this, 'order_item_meta' ], 10, 2 );

			// Before calculate totals
			add_action( 'woocommerce_before_mini_cart_contents', [ $this, 'before_mini_cart_contents' ], 999999 );
			add_action( 'woocommerce_before_calculate_totals', [ $this, 'before_calculate_totals' ], 999999 );

			// Cart item price & subtotal
			add_filter( 'woocommerce_cart_item_price', [ $this, 'cart_item_price' ], 999999, 2 );
			add_filter( 'woocommerce_cart_item_subtotal', [ $this, 'cart_item_subtotal' ], 999999, 2 );

			// Cart item link
			add_filter( 'woocommerce_cart_item_permalink', [ $this, 'cart_item_permalink' ], 10, 2 );
		}

		function process_file_link() {
			// cart file download
			if ( isset( $_GET['wpcpo-key'], $_GET['wpcpo-cart-item'] ) && isset( $_GET['_wpnonce'] ) && ( false !== wp_verify_nonce( $_GET['_wpnonce'], 'wpcpo_cart_file' ) ) ) {
				$file_key      = sanitize_text_field( wp_unslash( $_GET['wpcpo-key'] ) );
				$cart_item_key = sanitize_text_field( wp_unslash( $_GET['wpcpo-cart-item'] ) );

				if ( ! str_starts_with( $file_key, 'wpcpo-' ) ) {
					$file_key = 'wpcpo-' . $file_key;
				}

				if ( isset( WC()->cart->cart_contents[ $cart_item_key ]['wpcpo-options'][ $file_key ]['url'] ) ) {
					$file_name     = WC()->cart->cart_contents[ $cart_item_key ]['wpcpo-options'][ $file_key ]['value'];
					$tmp_file_name = WC()->cart->cart_contents[ $cart_item_key ]['wpcpo-options'][ $file_key ]['file'];
					$mime_type     = false;

					if ( function_exists( 'finfo_open' ) ) {
						$finfo     = finfo_open( FILEINFO_MIME_TYPE );
						$mime_type = finfo_file( $finfo, $tmp_file_name );
						finfo_close( $finfo );
					} elseif ( function_exists( 'mime_content_type' ) ) {
						$mime_type = mime_content_type( $tmp_file_name );
					}

					// clean all levels of output buffering
					// this is needed in case some 3rd-party plugin interferes with the output buffer
					while ( ob_get_level() ) {
						ob_end_clean();
					}

					header( "Expires: 0" );
					header( "Cache-Control: must-revalidate, post-check=0, pre-check=0" );
					header( "Cache-Control: private", false );

					if ( isset( $_GET['force_download'] ) && $_GET['force_download'] === '1' ) {
						header( 'Content-disposition: attachment; filename="' . $file_name . '"' );
					}

					if ( $mime_type ) {
						header( "Content-type: $mime_type" );
					}

					header( "Content-Transfer-Encoding: binary" );
					header( "Content-Length: " . filesize( $tmp_file_name ) );
					readfile( $tmp_file_name );

					exit();
				}
			}

			// order file download
			if ( isset( $_GET['wpcpo-key'], $_GET['wpcpo-order-item'] ) && isset( $_GET['_wpnonce'] ) ) {
				$file_key      = sanitize_text_field( wp_unslash( $_GET['wpcpo-key'] ) );
				$order_item_id = sanitize_text_field( wp_unslash( $_GET['wpcpo-order-item'] ) );

				if ( ! str_starts_with( $file_key, 'wpcpo-' ) ) {
					$file_key = 'wpcpo-' . $file_key;
				}

				if ( ( $options = wc_get_order_item_meta( $order_item_id, '_wpcpo_options_v2' ) ) && isset( $options[ $file_key ] ) ) {
					$file_name     = $options[ $file_key ]['value'];
					$tmp_file_name = $options[ $file_key ]['file'];
					$mime_type     = false;

					if ( function_exists( 'finfo_open' ) ) {
						$finfo     = finfo_open( FILEINFO_MIME_TYPE );
						$mime_type = finfo_file( $finfo, $tmp_file_name );
						finfo_close( $finfo );
					} elseif ( function_exists( 'mime_content_type' ) ) {
						$mime_type = mime_content_type( $tmp_file_name );
					}

					// clean all levels of output buffering
					// this is needed in case some 3rd-party plugin interferes with the output buffer
					while ( ob_get_level() ) {
						ob_end_clean();
					}

					header( "Expires: 0" );
					header( "Cache-Control: must-revalidate, post-check=0, pre-check=0" );
					header( "Cache-Control: private", false );

					if ( isset( $_GET['force_download'] ) && $_GET['force_download'] === '1' ) {
						header( 'Content-disposition: attachment; filename="' . $file_name . '"' );
					}

					if ( $mime_type ) {
						header( "Content-type: $mime_type" );
					}

					header( "Content-Transfer-Encoding: binary" );
					header( "Content-Length: " . filesize( $tmp_file_name ) );
					readfile( $tmp_file_name );

					exit();
				}
			}
		}

		private function clean_custom_price( $custom_price ) {
			return preg_replace( '/[^0-9\+\-\*\/\(\)\.vdpqlws]/', '', $custom_price );
		}

		private function word_count( $string ) {
			$formatted_string = preg_replace( '/\s+/', ' ', trim( wp_strip_all_tags( $string ) ) );
			$words            = explode( ' ', $formatted_string );

			return apply_filters( 'wpcpo_word_count', count( $words ), $string );
		}

		private function get_custom_price( $custom_price, $quantity, $product_price, $value, $total = 0 ) {
			return 0;
		}

		private function get_custom_price_dimensions( $dimensions, $custom_price, $quantity, $product_price, $value, $total = 0 ) {
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
							$upload = $this->handle_upload( $_FILES[ $key ] );

							if ( $upload && empty( $upload['error'] ) ) {
								$data['value']        = basename( wc_clean( $upload['url'] ) );
								$data['file']         = wc_clean( $upload['file'] );
								$data['url']          = wc_clean( $upload['url'] );
								$post_options[ $key ] = $data;
							}
						}
					} else {
						$post_options[ $key ] = $data;
					}

					if ( apply_filters( 'wpcpo_clear_request_data', true, $cart_item_data, $product_id ) ) {
						unset( $_REQUEST[ $key ] );
					}
				}
			}

			if ( ! empty( $post_options ) ) {
				$cart_item_data['wpcpo-options'] = $post_options;
			}

			if ( ! empty( $post_data['wpcpo_url'] ) ) {
				$cart_item_data['wpcpo-url'] = $post_data['wpcpo_url'];
			}

			return $cart_item_data;
		}

		public function handle_upload( $file ) {
			include_once( ABSPATH . 'wp-admin/includes/file.php' );
			include_once( ABSPATH . 'wp-admin/includes/media.php' );

			add_filter( 'upload_dir', [ $this, 'upload_dir' ] );

			$upload = wp_handle_upload( $file, [
				'test_form'                => false,
				'unique_filename_callback' => [ $this, 'unique_filename' ]
			] );

			remove_filter( 'upload_dir', [ $this, 'upload_dir' ] );

			return $upload;
		}

		public function unique_filename( $dir, $name, $ext ) {
			return apply_filters( 'wpcpo_unique_filename', uniqid() . $ext, $dir, $name, $ext );
		}

		public function upload_dir( $path_data ) {
			global $woocommerce;

			$date_str = date( 'Ymd' );
			$user_str = md5( $woocommerce->session->get_customer_id() );
			$folder   = trim( apply_filters( 'wpcpo_upload_folder', $date_str . '/' . $user_str ), '/' );

			if ( empty( $path_data['subdir'] ) ) {
				$path_data['path']   = $path_data['path'] . '/woocommerce_uploads/wpcpo_uploads/' . $folder;
				$path_data['url']    = $path_data['url'] . '/woocommerce_uploads/wpcpo_uploads/' . $folder;
				$path_data['subdir'] = '/woocommerce_uploads/wpcpo_uploads/' . $folder;
			} else {
				$subdir              = '/woocommerce_uploads/wpcpo_uploads/' . $folder;
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

			if ( ! empty( $session_values['wpcpo-url'] ) ) {
				$cart_item['wpcpo-url'] = $session_values['wpcpo-url'];
			}

			return $cart_item;
		}

		public function get_item_data( $item_data, $cart_item ) {
			if ( empty( $cart_item['wpcpo-options'] ) || ! is_array( $cart_item['wpcpo-options'] ) ) {
				return $item_data;
			}

			foreach ( $cart_item['wpcpo-options'] as $option_key => $option ) {
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

						if ( ( $option['type'] === 'file' ) && ( isset( $option['url'] ) || isset( $option['file_url'] ) ) && apply_filters( 'wpcpo_cart_item_data_makeup', true, 'file' ) ) {
							$data['value'] = '<span class="wpcpo-item-data-file"><a target="_blank" href="' . esc_url( $option['file_url'] ?? self::get_cart_file_link( $option_key, $cart_item['key'] ) ) . '">' . $option['value'] . '</a></span>';
						}
					}

					if ( ! empty( $option['display_price'] ) ) {
						$data['display'] = '<span class="' . esc_attr( 'wpcpo-item-data-display wpcpo-item-data-' . ( $option['type'] ?? 'default' ) ) . '">' . $data['value'] . ' <span class="wpcpo-item-data-price">(' . wc_price( $option['display_price'] ) . ')</span></span>';
					}

					$item_data[] = apply_filters( 'wpcpo_cart_item_data', $data, $option, $cart_item );
				}
			}

			return $item_data;
		}

		public function order_line_item( $item, $cart_item_key, $values ) {
			if ( isset( $values['wpcpo-options'] ) ) {
				$item->update_meta_data( '_wpcpo_options_v2', $values['wpcpo-options'] );
			}
		}

		function order_item_meta( $item_id, $item ) {
			if ( $options = $item->get_meta( '_wpcpo_options_v2' ) ) {
				$meta = '<ul class="wpcpo-order-item-options">';

				foreach ( $options as $option_key => $option ) {
					if ( $option['type'] === 'file' ) {
						$meta .= '<li><strong>' . esc_html( $option['title'] ) . ':</strong> <a target="_blank" href="' . self::get_order_file_link( $option_key, $item ) . '">' . $option['value'] . '</a> (<a target="_blank" href="' . self::get_order_file_link( $option_key, $item, true ) . '">' . esc_html__( 'download', 'wpc-product-options' ) . '</a>)</li>';
					} else {
						$meta .= '<li><strong>' . esc_html( $option['title'] ) . ':</strong> ' . esc_html( isset( $option['label'] ) && $option['label'] !== '' ? $option['label'] : $option['value'] ) . '</li>';
					}
				}

				$meta .= '</ul>';

				echo apply_filters( 'wpcpo_order_item_meta', $meta, $item );
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
				if ( empty( $cart_item['wpcpo-options'] ) || apply_filters( 'wpcpo_ignore_recalculate_price', false, $cart_item_key, $cart_item ) ) {
					continue;
				}

				$product_id    = $cart_item['data']->get_id();
				$ori_product   = apply_filters( 'wpcpo_cart_item_product', wc_get_product( $product_id ), $cart_item );
				$is_on_sale    = apply_filters( 'wpcpo_cart_item_is_on_sale', $ori_product->is_on_sale(), $cart_item );
				$price         = (float) apply_filters( 'wpcpo_cart_item_price', $ori_product->get_price(), $cart_item );
				$regular_price = (float) apply_filters( 'wpcpo_cart_item_regular_price', $ori_product->get_regular_price(), $cart_item );
				$quantity      = (float) apply_filters( 'wpcpo_cart_item_qty', $cart_item['quantity'], $cart_item );

				// calculate options price
				$options_price = 0; // options price

				if ( isset( $cart_item['woosb_price'] ) ) {
					$price = (float) $cart_item['woosb_price'];
				}

				/*
				if ( isset( $cart_item['wooco_price'] ) ) {
					$price = (float) $cart_item['wooco_price'];
				}
				*/

				if ( isset( $cart_item['wpcpq_price'] ) ) {
					$price = (float) $cart_item['wpcpq_price'];
				}

				$total = $price * $quantity; // calculate total for 's'

				foreach ( $cart_item['wpcpo-options'] as $key => $field ) {
					$price_type = ! empty( $field['price_type'] ) ? $field['price_type'] : '';
					$price_val  = ! empty( $field['price'] ) ? $field['price'] : 0;

					switch ( $price_type ) {
						case 'flat':
							if ( str_contains( $price_val, '%' ) ) {
								$calc_price = $price * (float) $price_val / 100;
							} else {
								$calc_price = (float) $price_val;
							}

							$options_price += $calc_price / $quantity;
							$total         += $calc_price;

							$cart_item['wpcpo-options'][ $key ]['display_price'] = $calc_price;

							break;
						case 'custom':
							if ( $field['type'] === 'dimensions' ) {
								$calc_price = $this->get_custom_price_dimensions( $field['dimensions'] ?? [], $field['custom_price'], $quantity, $price, $field['value'], $total );
							} else {
								$calc_price = $this->get_custom_price( $field['custom_price'], $quantity, $price, $field['value'], $total );
							}

							$options_price += $calc_price / $quantity;
							$total         += $calc_price;

							$cart_item['wpcpo-options'][ $key ]['display_price'] = $calc_price;

							break;
						default:
							// qty
							if ( str_contains( $price_val, '%' ) ) {
								$calc_price = $price * (float) $price_val / 100;
							} else {
								$calc_price = (float) $price_val;
							}

							$options_price += $calc_price;
							$total         += $calc_price * $quantity;

							$cart_item['wpcpo-options'][ $key ]['display_price'] = $calc_price * $quantity;

							break;
					}
				}

				$cart_item['wpcpo_price'] = $options_price; // store options price only

				if ( $options_price != 0 ) {
					$cart_item['data']->set_regular_price( $regular_price + $options_price );
					$cart_item['data']->set_price( $price + $options_price );

					if ( $is_on_sale ) {
						$sale_price = (float) apply_filters( 'wpcpo_cart_item_sale_price', $ori_product->get_sale_price(), $cart_item );
						$cart_item['data']->set_sale_price( $sale_price + $options_price );
					}
				}

				// save $cart_item
				WC()->cart->cart_contents[ $cart_item_key ] = $cart_item;
			}
		}

		public function cart_item_price( $price, $cart_item ) {
			if ( empty( $cart_item['wpcpo-options'] ) ) {
				return $price;
			}

			if ( ! empty( $cart_item['wpcpo_price'] ) && ( ! empty( $cart_item['wooco_price'] ) || ! empty( $cart_item['woosb_price'] ) ) ) {
				$calc_price = (float) $cart_item['wpcpo_price'];

				if ( ! empty( $cart_item['wooco_price'] ) ) {
					$calc_price += (float) $cart_item['wooco_price'];
				}

				if ( ! empty( $cart_item['woosb_price'] ) ) {
					$calc_price += (float) $cart_item['woosb_price'];

					if ( ! empty( $cart_item['woosb_discount_amount'] ) ) {
						$calc_price += (float) $cart_item['woosb_discount_amount'];
					}
				}

				$price = wc_price( $calc_price );
			}

			return apply_filters( 'wpcpo_cart_item_price_html', $price, $cart_item );
		}

		public function cart_item_subtotal( $subtotal, $cart_item = null ) {
			if ( empty( $cart_item['wpcpo-options'] ) ) {
				return $subtotal;
			}

			if ( ! empty( $cart_item['wpcpo_price'] ) && ( ! empty( $cart_item['wooco_price'] ) || ! empty( $cart_item['woosb_price'] ) ) ) {
				$price = (float) $cart_item['wpcpo_price'];

				if ( ! empty( $cart_item['wooco_price'] ) ) {
					$price += (float) $cart_item['wooco_price'];
				}

				if ( ! empty( $cart_item['woosb_price'] ) ) {
					$price += (float) $cart_item['woosb_price'];

					if ( ! empty( $cart_item['woosb_discount_amount'] ) ) {
						$price += (float) $cart_item['woosb_discount_amount'];
					}
				}

				$subtotal = wc_price( $price * (float) $cart_item['quantity'] );

				if ( wc_tax_enabled() && WC()->cart->display_prices_including_tax() && ! wc_prices_include_tax() ) {
					$subtotal .= ' <small class="tax_label">' . WC()->countries->inc_tax_or_vat() . '</small>';
				}
			}

			return apply_filters( 'wpcpo_cart_item_subtotal_html', $subtotal, $cart_item );
		}

		public function cart_item_permalink( $permalink, $cart_item ) {
			if ( ! apply_filters( 'wpcpo_change_url', false ) || empty( $cart_item['wpcpo-url'] ) ) {
				return $permalink;
			}

			if ( str_contains( $permalink, '?' ) ) {
				$permalink .= '&' . $cart_item['wpcpo-url'];
			} else {
				$permalink .= '?' . $cart_item['wpcpo-url'];
			}

			return $permalink;
		}

		public function get_cart_file_link( $file_key, $cart_item_key ) {
			$args = [
				'_wpnonce'        => wp_create_nonce( 'wpcpo_cart_file' ),
				'wpcpo-key'       => str_replace( 'wpcpo-', '', $file_key ),
				'wpcpo-cart-item' => $cart_item_key,
			];

			return add_query_arg( $args, home_url() );
		}

		public function get_order_file_link( $file_key, $order_item, $download = false ) {
			$args = [
				'_wpnonce'         => wp_create_nonce( 'wpcpo_order_file' ),
				'wpcpo-key'        => str_replace( 'wpcpo-', '', $file_key ),
				'wpcpo-order-item' => $order_item->get_id(),
			];

			if ( $download ) {
				$args['force_download'] = 1;
			}

			return add_query_arg( $args, home_url() );
		}
	}
}

return Wpcpo_Cart::instance();
