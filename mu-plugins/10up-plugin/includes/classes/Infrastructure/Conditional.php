<?php
/**
 * Conditional interface
 *
 * @package TenUpPlugin
 */

namespace TenUpPlugin\Infrastructure;

interface Conditional {

	/**
	 * Returns whether the service should be registered.
	 */
	public static function is_needed() : bool;
}
