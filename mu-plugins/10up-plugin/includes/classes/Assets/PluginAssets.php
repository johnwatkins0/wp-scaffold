<?php
/**
 * PluginAssets class
 * 
 * @package TenUpPlugin
 */

namespace TenUpPlugin\PluginAssets;

use TenUpPlugin\Infrastructure\{Module, Registerable, Shared};
use TenUpPlugin\Plugin;
use WP_Error;

final class PluginAssets implements Registerable, Shared, Module {
	/**
	 * Plugin instance.
	 * 
	 * @var Plugin
	 */
	private $plugin;

	/**
	 * Constructor.
	 * 
	 * @param Plugin $plugin Plugin instance.
	 */
	public function __construct( Plugin $plugin ) {
		$this->plugin = $plugin;
	}

	/**
	 * Registers the module.
	 */
	public function register() {
		// Hook to allow async or defer on asset loading.
		add_filter( 'script_loader_tag', [ $this, 'script_loader_tag' ], 10, 2 );
	}


	/**
	 * The list of knows contexts for enqueuing scripts/styles.
	 *
	 * @return array
	 */
	private function get_enqueue_contexts() {
		return [ 'admin', 'frontend', 'shared' ];
	}

	/**
	 * Generate an URL to a script, taking into account whether SCRIPT_DEBUG is enabled.
	 *
	 * @param string $script Script file name (no .js extension)
	 * @param string $context Context for the script ('admin', 'frontend', or 'shared')
	 *
	 * @return string|WP_Error URL
	 */
	public function script_url( $script, $context ) {

		if ( ! in_array( $context, $this->get_enqueue_contexts(), true ) ) {
			return new WP_Error( 'invalid_enqueue_context', 'Invalid $context specified in TenUpPlugin script loader.' );
		}

		return $this->plugin->get_url() . "dist/js/${script}.js";

	}

	/**
	 * Generate an URL to a stylesheet, taking into account whether SCRIPT_DEBUG is enabled.
	 *
	 * @param string $stylesheet Stylesheet file name (no .css extension)
	 * @param string $context Context for the script ('admin', 'frontend', or 'shared')
	 *
	 * @return string URL
	 */
	public function style_url( $stylesheet, $context ) {

		if ( ! in_array( $context, $this->get_enqueue_contexts(), true ) ) {
			return new WP_Error( 'invalid_enqueue_context', 'Invalid $context specified in TenUpPlugin stylesheet loader.' );
		}

		return $this->plugin->get_url() . "dist/css/${stylesheet}.css";

	}

	/**
	 * Get asset info from extracted asset files
	 *
	 * @param string $slug Asset slug as defined in build/webpack configuration
	 * @param string $attribute Optional attribute to get. Can be version or dependencies
	 * @return string|array
	 */
	public function get_asset_info( $slug, $attribute = null ) {
		$plugin_path = $this->plugin->get_path();

		if ( file_exists( $plugin_path . 'dist/js/' . $slug . '.asset.php' ) ) {
			$asset = require $plugin_path . 'dist/js/' . $slug . '.asset.php';
		} elseif ( file_exists( $plugin_path . 'dist/css/' . $slug . '.asset.php' ) ) {
			$asset = require $plugin_path . 'dist/css/' . $slug . '.asset.php';
		} else {
			return null;
		}

		if ( ! empty( $attribute ) && isset( $asset[ $attribute ] ) ) {
			return $asset[ $attribute ];
		}

		return $asset;
	}

	/**
	 * Add async/defer attributes to enqueued scripts that have the specified script_execution flag.
	 *
	 * @link https://core.trac.wordpress.org/ticket/12009
	 * @param string $tag    The script tag.
	 * @param string $handle The script handle.
	 * @return string
	 */
	public function script_loader_tag( $tag, $handle ) {
		$script_execution = wp_scripts()->get_data( $handle, 'script_execution' );

		if ( ! $script_execution ) {
			return $tag;
		}

		if ( 'async' !== $script_execution && 'defer' !== $script_execution ) {
			return $tag; // _doing_it_wrong()?
		}

		// Abort adding async/defer for scripts that have this script as a dependency. _doing_it_wrong()?
		foreach ( wp_scripts()->registered as $script ) {
			if ( in_array( $handle, $script->deps, true ) ) {
				return $tag;
			}
		}

		// Add the attribute if it hasn't already been added.
		if ( ! preg_match( ":\s$script_execution(=|>|\s):", $tag ) ) {
			$tag = preg_replace( ':(?=></script>):', " $script_execution", $tag, 1 );
		}

		return $tag;
	}
}