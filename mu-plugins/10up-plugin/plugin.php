<?php

use TenUpPlugin\Plugin;

$tenup_plugin_is_local_env = in_array( wp_get_environment_type(), [ 'local', 'development' ], true );
$tenup_plugin_is_local_url = strpos( home_url(), '.test' ) || strpos( home_url(), '.local' );
$tenup_plugin_is_local     = $tenup_plugin_is_local_env || $tenup_plugin_is_local_url;

if ( $tenup_plugin_is_local && file_exists( __DIR__ . '/dist/fast-refresh.php' ) ) {
	require_once __DIR__ . '/dist/fast-refresh.php';

	if ( function_exists( 'TenUpToolkit\\set_dist_url_path')) {
		\TenUpToolkit\set_dist_url_path( basename( __DIR__ ), TENUP_THEME_DIST_URL, TENUP_THEME_DIST_PATH );
	}
}

// Require Composer autoloader if it exists.
$teup_plugin_autoload_path = __DIR__ . '/vendor/autoload.php';	
if ( file_exists( $teup_plugin_autoload_path ) ) {
	require_once $teup_plugin_autoload_path;
}

/**
 * Provides the Plugin instance.
 *
 * @return Plugin
 */
function tenup_plugin() : Plugin {
	static $plugin;

	if ( ! $plugin ) {
		do_action( 'tenup_plugin_before_init' );

		$plugin = new Plugin( '0.1.0', __FILE__ );
		$plugin->register();

		do_action( 'tenup_plugin_init' );
	}

	return $plugin;
}

add_action( 'init', 'tenup_plugin', apply_filters( 'tenup_plugin_init_priority', 8 ) );

do_action( 'tenup_plugin_loaded' );
