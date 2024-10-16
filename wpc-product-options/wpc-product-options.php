<?php
/*
Plugin Name: WPC Product Options for WooCommerce
Plugin URI: https://wpclever.net/
Description: WPC Product Options brings about the power of adjusting prices with highly customizable additional fields for products.
Version: 1.7.6
Author: WPClever
Author URI: https://wpclever.net
Text Domain: wpc-product-options
Domain Path: /languages/
Requires Plugins: woocommerce
Requires at least: 4.0
Tested up to: 6.6
WC requires at least: 3.0
WC tested up to: 9.3
*/

! defined( 'WPCPO_VERSION' ) && define( 'WPCPO_VERSION', '1.7.6' );
! defined( 'WPCPO_LITE' ) && define( 'WPCPO_LITE', __FILE__ );
! defined( 'WPCPO_FILE' ) && define( 'WPCPO_FILE', __FILE__ );
! defined( 'WPCPO_DIR' ) && define( 'WPCPO_DIR', plugin_dir_path( __FILE__ ) );
! defined( 'WPCPO_URI' ) && define( 'WPCPO_URI', plugin_dir_url( __FILE__ ) );
! defined( 'WPCPO_SUPPORT' ) && define( 'WPCPO_SUPPORT', 'https://wpclever.net/support?utm_source=support&utm_medium=wpcpo&utm_campaign=wporg' );
! defined( 'WPCPO_REVIEWS' ) && define( 'WPCPO_REVIEWS', 'https://wordpress.org/support/plugin/wpc-product-options/reviews/?filter=5' );
! defined( 'WPCPO_CHANGELOG' ) && define( 'WPCPO_CHANGELOG', 'https://wordpress.org/plugins/wpc-product-options/#developers' );
! defined( 'WPCPO_DISCUSSION' ) && define( 'WPCPO_DISCUSSION', 'https://wordpress.org/support/plugin/wpc-product-options' );
! defined( 'WPC_URI' ) && define( 'WPC_URI', WPCPO_URI );

include 'includes/dashboard/wpc-dashboard.php';
include 'includes/kit/wpc-kit.php';
include 'includes/hpos.php';

if ( ! function_exists( 'wpcpo_init' ) ) {
	add_action( 'plugins_loaded', 'wpcpo_init', 11 );

	function wpcpo_init() {
		load_plugin_textdomain( 'wpc-product-options', false, basename( __DIR__ ) . '/languages/' );

		if ( ! function_exists( 'WC' ) || ! version_compare( WC()->version, '3.0', '>=' ) ) {
			add_action( 'admin_notices', 'wpcpo_notice_wc' );

			return null;
		}

		if ( ! class_exists( 'WPCleverWpcpo' ) && class_exists( 'WC_Product' ) ) {
			class WPCleverWpcpo {
				public function __construct() {
					require_once WPCPO_DIR . 'includes/class-backend.php';
					require_once WPCPO_DIR . 'includes/class-frontend.php';
					require_once WPCPO_DIR . 'includes/class-cart.php';
				}
			}

			new WPCleverWpcpo();
		}
	}
}

if ( ! function_exists( 'wpcpo_notice_wc' ) ) {
	function wpcpo_notice_wc() {
		?>
        <div class="error">
            <p><strong>WPC Product Options</strong> requires WooCommerce version 3.0 or greater.</p>
        </div>
		<?php
	}
}
