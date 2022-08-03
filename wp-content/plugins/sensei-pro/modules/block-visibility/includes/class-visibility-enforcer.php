<?php
/**
 * \Sensei_Pro_Block_Visibility\Visibility_Enforcer class.
 *
 * @package sensei-pro
 */

namespace Sensei_Pro_Block_Visibility;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Visibility_Enforcer class.
 */
class Visibility_Enforcer {
	/**
	 * Sensei blocks visibility options.
	 *
	 * @var Visibility_Options
	 */
	private $visibility_options;

	/**
	 * Class instance.
	 *
	 * @var Visibility_Enforcer
	 */
	private static $instance;

	/**
	 * Interactive blocks instance.
	 */
	public static function instance() : Visibility_Enforcer {
		if ( ! self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Initialize the class singleton.
	 */
	private function __construct() {}

	/**
	 * Initializes the class and adds all filters and actions.
	 *
	 * @param Visibility_Options $visibility_options Sensei blocks visibility options.
	 */
	public static function init( Visibility_Options $visibility_options ) {
		$instance                     = self::instance();
		$instance->visibility_options = $visibility_options;
		add_filter( 'render_block', [ $instance, 'enforce_sensei_visibility' ], 10, 2 );
		return $instance;
	}

	/**
	 * Enforces the sensei visiblity settings.
	 *
	 * @param string $block_content The block content markup.
	 * @param array  $block         The block metadata.
	 */
	public function enforce_sensei_visibility( string $block_content, array $block ) {
		if ( ! isset( $block['attrs'] ) || ! isset( $block['attrs']['senseiVisibility'] ) ) {
			return $block_content;
		}

		$sensei_visibility = $block['attrs']['senseiVisibility'];
		foreach ( $sensei_visibility as $visibility_type_name => $visibility_settings ) {
			$visibility_type = $this->visibility_options->get_visibility_type( $visibility_type_name );
			if ( $visibility_type && ! $visibility_type->is_visible( $visibility_settings ) ) {
				return '';
			}
		}

		return $block_content;
	}
}
