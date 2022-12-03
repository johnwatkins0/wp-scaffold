<?php
/**
 * FrontendAssets class
 * 
 * @package TenUpPlugin
 */

namespace TenUpPlugin\PluginAssets;

use TenUpPlugin\Infrastructure\{Module, Conditional, Registerable, Shared};
use TenUpPlugin\Plugin;

final class FrontendAssets implements Conditional, Registerable, Shared, Module {
	/**
	 * Plugin instance.
	 * 
	 * @var Plugin
	 */
	private $plugin;

	/**
	 * PluginAssets instance.
	 * 
	 * @var PluginAssets
	 */
	private $plugin_assets;

	/**
	 * Returns whether the service should be registered.
	 * 
	 * @return bool
	 */
	public static function is_needed() : bool {
		return ! is_admin();
	}


	/**
	 * Constructor.
	 * 
	 * @param Plugin $plugin Plugin instance.
	 * @param PluginAssets $plugin_assets PluginAssets instance.
	 */
	public function __construct( Plugin $plugin, PluginAssets $plugin_assets ) {
		$this->plugin = $plugin;
		$this->plugin_assets = $plugin_assets;
	}
	
	/**
	 * Registers the module.
	 */
	public function register() {
		add_action( 'wp_enqueue_scripts', [ $this, 'scripts' ] );
		add_action( 'wp_enqueue_scripts', [ $this, 'styles' ] );
	}

	/**
	 * Enqueue scripts for front-end.
	 *
	 * @return void
	 */
	public function scripts() {

		wp_enqueue_script(
			'tenup_plugin_shared',
			$this->plugin_assets->script_url( 'shared', 'shared' ),
			$this->plugin_assets->get_asset_info( 'shared', 'dependencies' ),
			$this->plugin->get_version(),
			true
		);

		wp_enqueue_script(
			'tenup_plugin_frontend',
			$this->plugin_assets->script_url( 'frontend', 'frontend' ),
			$this->plugin_assets->get_asset_info( 'frontend', 'dependencies' ),
			$this->plugin->get_version(),
			true
		);

	}

	/**
	 * Enqueue styles for front-end.
	 *
	 * @return void
	 */
	public function styles() {

		wp_enqueue_style(
			'tenup_plugin_shared',
			$this->plugin_assets->style_url( 'shared', 'shared' ),
			[],
			$this->plugin->get_version()
		);

		
		wp_enqueue_style(
			'tenup_plugin_frontend',
			$this->plugin_assets->style_url( 'frontend', 'frontend' ),
			[],
			$this->plugin->get_version()
		);
	}
}