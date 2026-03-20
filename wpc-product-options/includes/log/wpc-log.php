<?php
defined( 'ABSPATH' ) || exit;

register_activation_hook( defined( 'WPCPO_LITE' ) ? WPCPO_LITE : WPCPO_FILE, 'wpcpo_activate' );
register_deactivation_hook( defined( 'WPCPO_LITE' ) ? WPCPO_LITE : WPCPO_FILE, 'wpcpo_deactivate' );
add_action( 'admin_init', 'wpcpo_check_version' );

function wpcpo_check_version() {
	if ( ! empty( get_option( 'wpcpo_version' ) ) && ( get_option( 'wpcpo_version' ) < WPCPO_VERSION ) ) {
		wpc_log( 'wpcpo', 'upgraded' );
		update_option( 'wpcpo_version', WPCPO_VERSION, false );
	}
}

function wpcpo_activate() {
	wpc_log( 'wpcpo', 'installed' );
	update_option( 'wpcpo_version', WPCPO_VERSION, false );
}

function wpcpo_deactivate() {
	wpc_log( 'wpcpo', 'deactivated' );
}

if ( ! function_exists( 'wpc_log' ) ) {
	function wpc_log( $prefix, $action ) {
		$logs = get_option( 'wpc_logs', [] );
		$user = wp_get_current_user();

		if ( ! isset( $logs[ $prefix ] ) ) {
			$logs[ $prefix ] = [];
		}

		$logs[ $prefix ][] = [
			'time'   => current_time( 'mysql' ),
			'user'   => $user->display_name . ' (ID: ' . $user->ID . ')',
			'action' => $action
		];

		update_option( 'wpc_logs', $logs, false );
	}
}