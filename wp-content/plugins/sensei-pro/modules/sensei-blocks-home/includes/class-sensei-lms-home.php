<?php
/**
 * File containing the class Sensei_Interactive_Blocks_Sensei_Home\Sensei_LMS_Home.
 *
 * @package sensei-blocks-home
 * @since   1.8.0
 */

namespace Sensei_Interactive_Blocks_Sensei_Home;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Handles interactions with Sensei Home when loaded from Sensei LMS.
 *
 * @since 1.8.0
 */
class Sensei_LMS_Home {
	/**
	 * Instance of class.
	 *
	 * @var self
	 */
	private static $instance;

	/**
	 * Initialize the singleton instance.
	 *
	 * @since 1.8.0
	 */
	private function __construct() {
		// Silence is golden.
	}

	/**
	 * Fetches an instance of the class.
	 *
	 * @return self
	 */
	public static function instance() {
		if ( ! self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Initialize the hooks.
	 */
	public function init() {
		add_filter( 'sensei_home_remote_data_other_plugins', [ $this, 'add_blocks_other_plugin' ] );
	}

	/**
	 * Add Sensei Blocks to the list of other plugins.
	 *
	 * @param array $other_plugins The other plugins.
	 *
	 * @return array
	 */
	public function add_blocks_other_plugin( $other_plugins ) {
		$other_plugins[] = 'sensei-interactive-blocks';

		return $other_plugins;
	}
}
