<?php
/**
 * @package Polylang-Pro
 */

namespace WP_Syntex\Polylang_Pro\Modules\Capabilities\Mapper;

use WP_Syntex\Polylang\Capabilities\User\User_Interface;

/**
 * Class to manage our custom capabilities that are mapped to `manage_options`.
 *
 * @since 3.8
 */
class Internal extends Abstract_Mapper {
	/**
	 * The capability mapped to `manage_options`.
	 *
	 * @var string
	 */
	private $capability;

	/**
	 * Constructor.
	 *
	 * @since 3.8
	 *
	 * @param string $capability The capability mapped to `manage_options`.
	 */
	public function __construct( string $capability ) {
		$this->capability = $capability;
	}
	/**
	 * Maps a capability to the primitive capabilities required of the given user.
	 *
	 * @since 3.8
	 *
	 * @param string[]       $caps Primitive capabilities required of the user.
	 * @param string         $cap  Capability being checked.
	 * @param User_Interface $user The user object.
	 * @param array          $args Adds context to the capability check, typically starting with an object ID.
	 * @return string[] Updated primitive capabilities required of the user.
	 */
	public function map( array $caps, string $cap, User_Interface $user, array $args ): array {
		if ( $this->capability !== $cap ) {
			return parent::map( $caps, $cap, $user, $args );
		}

		if ( $user->has_cap( 'manage_options' ) ) {
			// Polylang already replaced the custom capability by `manage_options`.
			return $caps;
		}

		// Non-administrators must have the custom capability.
		$caps   = array_diff( $caps, array( 'manage_options', $this->capability ) );
		$caps[] = $this->capability;

		return $caps;
	}
}
