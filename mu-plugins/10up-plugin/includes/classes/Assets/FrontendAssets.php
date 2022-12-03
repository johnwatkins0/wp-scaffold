<?php
/**
 * FrontendAssets class
 * 
 * @package TenUpPlugin
 */

namespace TenUpPlugin\Assets;

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
	 * Assets instance.
	 * 
	 * @var Assets
	 */
	private $assets;

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
	 * @param Assets $assets Assets instance.
	 */
	public function __construct( Plugin $plugin, Assets $assets ) {
		$this->plugin = $plugin;
		$this->assets = $assets;
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
			$this->assets->script_url( 'shared', 'shared' ),
			$this->assets->get_asset_info( 'shared', 'dependencies' ),
			$this->plugin->get_version(),
			true
		);

		wp_enqueue_script(
			'tenup_plugin_frontend',
			$this->assets->script_url( 'frontend', 'frontend' ),
			$this->assets->get_asset_info( 'frontend', 'dependencies' ),
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
			$this->assets->style_url( 'shared', 'shared' ),
			[],
			$this->plugin->get_version()
		);

		
		wp_enqueue_style(
			'tenup_plugin_frontend',
			$this->assets->style_url( 'frontend', 'frontend' ),
			[],
			$this->plugin->get_version()
		);
	}
}