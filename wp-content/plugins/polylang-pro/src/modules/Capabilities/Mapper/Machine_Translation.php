<?php
/**
 * @package Polylang-Pro
 */

namespace WP_Syntex\Polylang_Pro\Modules\Capabilities\Mapper;

use WP_Syntex\Polylang\Capabilities\User\User_Interface;

/**
 * Class to map machine translation capabilities.
 *
 * @since 3.8
 */
class Machine_Translation extends Abstract_Mapper {
	/**
	 * The capability allowing to use machine translation.
	 *
	 * @since 3.8
	 *
	 * @var string
	 */
	public const CAPABILITY = 'machine_translate';

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
		if ( self::CAPABILITY !== $cap ) {
			return parent::map( $caps, $cap, $user, $args );
		}

		if ( ! pll_get_constant( 'PLL_ENABLE_MACHINE_TRANSLATION_CAPABILITIES' ) ) {
			// Not enabled: remove `machine_translate`. The list should be empty.
			return array_diff( $caps, array( self::CAPABILITY ) );
		}

		if ( $user->has_cap( 'manage_options' ) ) {
			// Enabled, but administrators are not required to have this capability.
			return array_diff( $caps, array( self::CAPABILITY ) );
		}

		return $caps;
	}
}
