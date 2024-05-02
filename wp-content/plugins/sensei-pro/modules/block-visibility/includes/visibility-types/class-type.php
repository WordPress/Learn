<?php
/**
 * File for \Sensei_Pro_Block_Visibility\Types\Type abstract class.
 *
 * @package sensei-pro
 * @since 1.5.0
 */

namespace Sensei_Pro_Block_Visibility\Types;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Basis for visibility type classes.
 */
abstract class Type {
	/**
	 * Retrieves the name.
	 */
	abstract public function name(): string;

	/**
	 * Retrieves the label.
	 */
	abstract public function label(): string;

	/**
	 * Retrieves the visibility badge label.
	 */
	abstract public function badge_label(): string;

	/**
	 * Retrieves the description of the visibility type.
	 */
	abstract public function description(): string;

	/**
	 * Returns the select option data for this visibility type.
	 *
	 * @return array
	 */
	public function get_option(): array {
		return [
			'value'       => $this->name(),
			'label'       => $this->label(),
			'badge_label' => $this->badge_label(),
			'description' => $this->description(),
		];
	}

	/**
	 * Given the sensei visibility settings for the block,
	 * tells if the block is visible or not.
	 *
	 * @param array $visibility_settings The sensei visibility settings for the block.
	 */
	public function is_visible( array $visibility_settings ): bool { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.Found -- This is an abstract class, so the method signature is important.
		return false;
	}
}
