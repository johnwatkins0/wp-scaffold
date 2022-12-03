<?php
/**
 * AdminAssets class
 * 
 * @package TenUpPlugin
 */

namespace TenUpPlugin\Assets;

use TenUpPlugin\Infrastructure\{Module, Conditional, Registerable, Shared};
use TenUpPlugin\Plugin;

final class AdminAssets implements Conditional, Registerable, Shared, Module {
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
		return is_admin();
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
		add_action( 'admin_enqueue_scripts', [ $this, 'admin_scripts' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'admin_styles' ] );

		// Editor styles. add_editor_style() doesn't work outside of a theme.
		add_filter( 'mce_css', [ $this, 'mce_css' ] );
	}

	/**
	 * Enqueue scripts for admin.
	 *
	 * @return void
	 */
	public function admin_scripts() {

		wp_enqueue_script(
			'tenup_plugin_shared',
			$this->assets->script_url( 'shared', 'shared' ),
			$this->assets->get_asset_info( 'shared', 'dependencies' ),
			$this->plugin->get_version(),
			true
		);

		wp_enqueue_script(
			'tenup_plugin_admin',
			$this->assets->script_url( 'admin', 'admin' ),
			$this->assets->get_asset_info( 'admin', 'dependencies' ),
			$this->plugin->get_version(),
			true
		);

	}

	/**
	 * Enqueue styles for admin.
	 *
	 * @return void
	 */
	public function admin_styles() {

		wp_enqueue_style(
			'tenup_plugin_shared',
			$this->assets->style_url( 'shared', 'shared' ),
			[],
			$this->plugin->get_version()
		);

		wp_enqueue_style(
			'tenup_plugin_admin',
			$this->assets->style_url( 'admin', 'admin' ),
			[],
			$this->plugin->get_version()
		);
	}

	/**
	 * Enqueue editor styles. Filters the comma-delimited list of stylesheets to load in TinyMCE.
	 *
	 * @param string $stylesheets Comma-delimited list of stylesheets.
	 * @return string
	 */
	public function mce_css( $stylesheets ) {
		if ( ! empty( $stylesheets ) ) {
			$stylesheets .= ',';
		}

		return $stylesheets . $this->plugin->get_url() . ( ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ?
				'assets/css/frontend/editor-style.css' :
				'dist/css/editor-style.min.css' );
	}
	
}