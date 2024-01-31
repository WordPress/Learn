<?php
/**
 * File containing the Group_Settings class.
 *
 * @package student-groups
 */

namespace Sensei_Pro_Student_Groups\Settings;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Add group settings.
 *
 * @since 1.18.0
 */
class Group_Settings {
	const GROUP_SIGNUP_PAGE_SETTING = 'group_signup_page';

	/**
	 * Group_Settings instance.
	 *
	 * @var Group_Settings
	 */
	private static $instance;

	/**
	 * Creates instance of Group_Settings.
	 *
	 * @return Group_Settings
	 */
	public static function instance() {
		if ( ! self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Group_Settings constructor.
	 */
	private function __construct() {}

	/**
	 * Initialize hooks.
	 */
	public function init() {
		if ( is_admin() ) {
			add_filter( 'sensei_settings_fields', [ $this, 'register_settings_fields' ] );
		}
	}

	/**
	 * Includes the Group settings fields.
	 *
	 * @param array $fields The original fields.
	 *
	 * @return array The fields with the Group settings fields.
	 */
	public function register_settings_fields( $fields ) {
		$pages       = $this->get_pages();
		$placeholder = [ 0 => __( 'Select a Page:', 'sensei-pro' ) ];
		$options     = $placeholder + $pages;

		$new_fields = [
			self::GROUP_SIGNUP_PAGE_SETTING => [
				'name'        => __( 'Group Signup Page', 'sensei-pro' ),
				'description' => __( 'The page for users to sign up for a group.', 'sensei-pro' ),
				'type'        => 'select',
				'default'     => 0,
				'section'     => 'default-settings',
				'required'    => 0,
				'options'     => $options,
			],
		];

		return $this->array_insert_after_key( $fields, $new_fields, 'course_completed_page' );
	}

	/**
	 * Insert new items after a specific key in an array.
	 *
	 * @param array $original_array   Original array.
	 * @param array $new_items        New items to be inserted.
	 * @param array $insert_after_key Array key after which the new items should be inserted.
	 *
	 * @return array Array with the new items.
	 */
	private function array_insert_after_key( $original_array, $new_items, $insert_after_key ) {
		// Get index.
		$keys               = array_keys( $original_array );
		$insert_after_index = array_search( $insert_after_key, $keys, true );

		if ( false !== $insert_after_index ) {
			// Split the original array into two parts.
			$first_part  = array_slice( $original_array, 0, $insert_after_index + 1, true );
			$second_part = array_slice( $original_array, $insert_after_index + 1, null, true );

			// Merge the three arrays to add the new item.
			$updated_array = $first_part + $new_items + $second_part;
		} else {
			// If the key is not found, you can add the new item to the end of the array.
			$updated_array = $original_array + $new_items;
		}

		return $updated_array;
	}

	/**
	 * Get published pages.
	 *
	 * @return array Published pages.
	 */
	private function get_pages() {
		$pages = get_pages( [ 'hierarchical' => true ] );
		$pages = wp_list_pluck( $pages, 'post_title', 'ID' );

		return $pages;
	}
}
