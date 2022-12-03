<?php
/**
 * Auto-initialize all Module based clases in the plugin.
 *
 * @package TenUpPlugin
 */

namespace TenUpPlugin\Infrastructure;

use HaydenPierce\ClassFinder\ClassFinder;
use ReflectionClass;

/**
 * Provider class.
 *
 * @package TenUpPlugin
 */
abstract class Provider implements Shared, Registerable, Module {

	/**
	 * All classes in the namespace.
	 * 
	 * @var array
	 */
	private $classes;

	/**
	 * Associative array of shared module instances.
	 *
	 * @var array
	 */
	private $shared_module_instances;

	/**
	 * Class constructor.
	 */
	public function __construct() {
		$this->shared_module_instances = [ static::class => $this ];
	}

	/**
	 * Get all the TenUpPlugin plugin classes.
	 *
	 * @return array
	 */
	protected function get_classes() {
		if ( is_null( $this->classes ) ) {
			$this->classes = 
			array_filter(
				ClassFinder::getClassesInNamespace( 'TenUpPlugin', ClassFinder::RECURSIVE_MODE ),
				static function( $class ) {
					$reflection = new ReflectionClass( $class );
					return $reflection->isInstantiable();
				}
			);
		}

		return $this->classes;
	}

	/**
	 * Get all the TenUpPlugin plugin classes that implement the Module interface.
	 *
	 * @return array
	 */
	protected function get_module_classes() {
		return array_filter(
			$this->get_classes(),
			static function ( $class ) {
				return in_array( Module::class, class_implements( $class ), true );
			}
		);
	}

	/**
	 * Get all the TenUpPlugin plugin classes that implement the Shared interface.
	 *
	 * @return array
	 */
	protected function get_shared_modules() {
		return array_filter(
			$this->get_module_classes(),
			static function ( $class ) {
				return in_array( Shared::class, class_implements( $class ), true );
			}
		);
	}

	/**
	 * Get all the plugin modules that are not shared.
	 * 
	 * @return array
	 */
	protected function get_non_shared_modules() {
		return array_filter(
			$this->get_module_classes(),
			static function ( $class ) {
				return ! in_array( Shared::class, class_implements( $class ), true );
			}
		);
	}

	/**
	 * Performs setup functions.
	 */
	public function register() {
		foreach ( $this->get_shared_modules() as $module ) {
			$implements = class_implements( $module );
			if ( is_array( $implements ) && in_array( Conditional::class, $implements, true ) && ! $module::is_needed() ) {
				continue;
			}

			$this->get_module( $module );
		}

		foreach ( $this->shared_module_instances as $module ) {
			if ( $module !== $this && is_a( $module, Registerable::class ) ) {
				$module->register();
			}
		}

		foreach ( $this->get_non_shared_modules() as $module ) {
			if ( ! is_a( $module, Registerable::class ) ) {
				continue;
			}

			$implements = class_implements( $module );
			if ( is_array( $implements ) && in_array( Conditional::class, $implements, true ) && ! $module::is_needed() ) {
				continue;
			}

			$this->get_module( $module, false );
			$module->register();
		}
	}

	/**
	 * Sets up a shared module.
	 *
	 * @param string $class Module class.
	 * @param bool   $is_shared True if the module is shared.
	 * @return object
	 *
	 * @throws Exception If an unknown module is encountered.
	 */
	public function get_module( $class, $is_shared = true ) {
		if ( $is_shared && isset( $this->shared_module_instances[ $class ] ) ) {
			return $this->shared_module_instances[ $class ];
		}

		$module_classes                = $this->get_module_classes();
		$dependency_instances    = [];

		$reflection  = new ReflectionClass( $class );
		$constructor = $reflection->getConstructor();

		if ( ! is_null( $constructor ) ) {
			foreach ( $constructor->getParameters() as $parameter ) {

				/**
				 * Reflection type.
				 *
				 * @var ReflectionType
				 */
				$type = $parameter->getType();

				if ( is_null( $type ) || ! method_exists( $type, 'getName' ) ) {
					continue;
				}

				$dependency_class = $type->getName();

				if ( ! class_exists( $dependency_class ) ) {
					throw new Exception( __( "Unknown class {$dependency_class} encountered.", 'tenup-plugin' ) );
				}

				if ( static::class === $dependency_class ) {
					$dependency_instances[] = $this;
					continue;
				}

				if ( in_array( $dependency_class, $module_classes, true ) ) {
					$dependency_instances[] = $this->get_module( $dependency_class );
				} else {
					throw new Exception( __( "Unknown module {$dependency_class} encountered.", 'tenup-plugin' ) );
				}
			}
		}

		if ( $is_shared && isset( $this->shared_module_instances[ $class ] ) ) {
			return $this->shared_module_instances[ $class ];
		}

		$instance = new $class( ...$dependency_instances );

		if ( $is_shared ) {
			$this->shared_module_instances[ $class ] = $instance;
		}

		return $instance;
	}
}