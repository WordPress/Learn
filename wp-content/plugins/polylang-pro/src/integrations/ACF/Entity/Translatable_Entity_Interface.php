<?php
/**
 * @package Polylang Pro
 */

namespace WP_Syntex\Polylang_Pro\Integrations\ACF\Entity;

use WP_Syntex\Polylang_Pro\Integrations\ACF\Strategy\Abstract_Strategy;

/**
 * Interface for applying a strategy to all fields of the current object.
 */
interface Translatable_Entity_Interface {
	/**
	 * Applies a strategy to all fields of the current object.
	 *
	 * @since 3.7
	 *
	 * @param Abstract_Strategy $strategy Strategy to execute.
	 * @param int               $to       ID of the target object.
	 * @param array             $args     Array of arguments.
	 * @return void
	 */
	public function apply_to_all_fields( Abstract_Strategy $strategy, int $to = 0, array $args = array() );
}
