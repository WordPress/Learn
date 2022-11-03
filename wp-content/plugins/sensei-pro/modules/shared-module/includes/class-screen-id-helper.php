<?php
/**
 * File containing the class \Sensei_Pro\Screen_ID_Helper.
 *
 * @package sensei-pro
 * @since $$next-version$$
 */

namespace Sensei_Pro;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class that provides helper methods related to Screen IDs.
 *
 * @internal
 */
class Screen_ID_Helper {

	/**
	 * Returns the Sensei Home screen ID depending on the current environment.
	 *
	 * @internal
	 *
	 * @return string|null Sensei Home ID. Null in case we can't tell.
	 */
	public static function get_sensei_home_screen_id() {
		if ( class_exists( 'Sensei_Home' ) ) {
			return \Sensei_Home::SCREEN_ID;
		}
		if ( class_exists( '\Sensei_Interactive_Blocks_Sensei_Home\Sensei_Home' ) ) {
			return \Sensei_Interactive_Blocks_Sensei_Home\Sensei_Home::SCREEN_ID;
		}
		return null;
	}

}
