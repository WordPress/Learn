<?php
/**
 * @package  Polylang-Pro
 */

namespace WP_Syntex\Polylang_Pro\Integrations\ACF\Strategy;

/**
 * This class is part of the ACF compatibility.
 * This strategy copies all fields (even the ignored ones), for instance during duplication.
 *
 * @since 3.7
 */
class Copy_All extends Copy {

	/**
	 * Recursively checks if a field can be copied.
	 * Always returns true to copy all fields.
	 *
	 * @since 3.7
	 *
	 * @param array $field Custom field definition.
	 * @return true
	 */
	protected function can_execute_recursive( array $field ): bool { // phpcs:ignore VariableAnalysis.CodeAnalysis.VariableAnalysis.UnusedVariable
		return true;
	}
}
