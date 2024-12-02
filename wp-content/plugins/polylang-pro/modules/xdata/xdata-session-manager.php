<?php
/**
 * @package Polylang-Pro
 */

/**
 * A class to store cross domain data
 *
 * @since 2.0
 */
class PLL_Xdata_Session_Manager {
	/**
	 * Writes cross domain data to the session
	 *
	 * @since 2.0
	 *
	 * @param string $key     A unique hash key.
	 * @param array  $data    Data to store in the session.
	 * @param int    $user_id Optional, user id.
	 * @return void
	 */
	public function set( $key, $data, $user_id = 0 ) {
		if ( empty( $user_id ) ) {
			$user_id = get_current_user_id();
		}

		if ( empty( $user_id ) ) {
			update_option( 'pll_xdata_' . $key, $data );
		} else {
			update_user_meta( $user_id, wp_slash( 'pll_xdata_' . $key ), wp_slash( $data ) );
		}
	}

	/**
	 * Reads cross domain data in the session
	 * And deletes the session to avoid a replay
	 *
	 * @since 2.0
	 *
	 * @param string $key The session key.
	 * @return array
	 */
	public function get( $key ) {
		$key = 'pll_xdata_' . $key;

		$users = get_users( array( 'meta_key' => $key, 'meta_compare' => 'EXISTS' ) );

		if ( ! empty( $users ) ) {
			$user = reset( $users );
			$data = get_user_meta( $user->ID, $key, true );
			delete_user_meta( $user->ID, wp_slash( $key ) ); // No replay.
			$data['user_id'] = $user->ID;
			return $data;
		}

		$data = get_option( $key, array() );

		if ( ! empty( $data ) ) {
			delete_option( $key ); // No replay.
			return $data;
		}

		wp_die( esc_html__( 'An error has occurred.', 'polylang-pro' ) );
	}
}
