<?php
/**
 * @package Polylang-Pro
 */

namespace WP_Syntex\Polylang_Pro\Modules\Capabilities\Mapper;

use WP_Syntex\Polylang\Capabilities\User\User_Interface;

/**
 * Abstract class for capabilities mappers implementing the Chain of Responsibility pattern.
 *
 * @since 3.8
 */
abstract class Abstract_Mapper {
	/**
	 * @var Abstract_Mapper|null
	 */
	protected $next;

	/**
	 * Maps a capability to the primitive capabilities required of the given user.
	 *
	 * @since 3.8
	 *
	 * @param string[]       $caps    Primitive capabilities required of the user.
	 * @param string         $cap     Capability being checked.
	 * @param User_Interface $user    The user object.
	 * @param array          $args    Adds context to the capability check, typically starting with an object ID.
	 * @return string[] Updated primitive capabilities required of the user.
	 */
	public function map( array $caps, string $cap, User_Interface $user, array $args ): array {
		if ( in_array( 'do_not_allow', $caps, true ) ) {
			return $caps;
		}

		if ( $this->next ) {
			return $this->next->map( $caps, $cap, $user, $args );
		}

		return $caps;
	}

	/**
	 * Sets the next mapper to be used.
	 *
	 * @since 3.8
	 *
	 * @param Abstract_Mapper $next_mapper The next mapper to be used.
	 * @return Abstract_Mapper The next mapper to be used.
	 */
	public function set_next( Abstract_Mapper $next_mapper ): Abstract_Mapper {
		$this->next = $next_mapper;

		return $next_mapper;
	}
}
