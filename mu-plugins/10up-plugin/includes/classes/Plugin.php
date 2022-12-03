<?php
/**
 * Plugin class
 * 
 * @package TenUpPlugin
 */

namespace TenUpPlugin;

use TenUpPlugin\Infrastructure\Provider;

final class Plugin extends Provider {
	/**
	 * Main plugin file.
	 * 
	 * @var string
	 */
	private $file;

	/**
	 * Plugin version.
	 * 
	 * @var string
	 */
	private $version;

	/**
	 * Plugin constructor.
	 * 
	 * @param string $file    Main plugin file.
	 * @param string $version Plugin version.
	 */
	public function __construct( string $file, string $version ) {
		$this->file    = $file;
		$this->version = $version;

		parent::__construct();
	}

	/**
	 * Registers the module.
	 */
	public function register() {
		parent::register();

		add_action( 'init', [ $this, 'i18n' ] );
	}

	/**
	 * Get the main plugin file.
	 * 
	 * @return string
	 */
	public function get_file() : string {
		return $this->file;
	}

	/**
	 * Get the plugin version.
	 * 
	 * @return string
	 */
	public function get_version() : string {
		return $this->version;
	}

	/**
	 * Get the plugin basename.
	 * 
	 * @return string
	 */
	public function get_basename() : string {
		return plugin_basename( $this->file );
	}

	/**
	 * Get the plugin directory path.
	 * 
	 * @return string
	 */
	public function get_path() : string {
		return trailingslashit( plugin_dir_path( $this->file ) );
	}

	/**
	 * Get the plugin directory URL.
	 * 
	 * @return string
	 */
	public function get_url() : string {
		return trailingslashit( plugin_dir_url( $this->file ) );
	}

	/**
	 * Registers the default textdomain.
	 *
	 * @return void
	 */
	public function i18n() {
		$locale = apply_filters( 'plugin_locale', get_locale(), 'tenup-plugin' );
		load_textdomain( 'tenup-plugin', WP_LANG_DIR . '/tenup-plugin/tenup-plugin-' . $locale . '.mo' );
		load_plugin_textdomain( 'tenup-plugin', false, tenup_plugin()->get_basename() . '/languages/' );
	}

}