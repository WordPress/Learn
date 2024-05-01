<?php
/**
 * \Sensei_Pro_Block_Visibility\Visibility_Options class.
 *
 * @package sensei-pro
 */

namespace Sensei_Pro_Block_Visibility;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once __DIR__ . '/visibility-types/class-type.php';
require_once __DIR__ . '/visibility-types/class-everyone.php';
require_once __DIR__ . '/visibility-types/class-no-one.php';
require_once __DIR__ . '/visibility-types/class-enrolled-to-course.php';
require_once __DIR__ . '/visibility-types/class-not-enrolled-to-course.php';
require_once __DIR__ . '/visibility-types/class-completed-course.php';
require_once __DIR__ . '/visibility-types/class-not-completed-course.php';
require_once __DIR__ . '/visibility-types/class-enrolled-to-any-course.php';
require_once __DIR__ . '/visibility-types/class-not-enrolled-to-any-course.php';
require_once __DIR__ . '/visibility-types/class-completed-any-course.php';
require_once __DIR__ . '/visibility-types/class-not-completed-any-course.php';
require_once __DIR__ . '/visibility-types/class-completed-lesson.php';
require_once __DIR__ . '/visibility-types/class-not-completed-lesson.php';
require_once __DIR__ . '/visibility-types/class-logged-in.php';
require_once __DIR__ . '/visibility-types/class-logged-out.php';
require_once __DIR__ . '/visibility-types/class-groups.php';
require_once __DIR__ . '/visibility-types/class-schedule.php';

/**
 * Visibility_Options class.
 */
class Visibility_Options {
	/**
	 * Visibility types.
	 *
	 * @var \Sensei_Pro_Block_Visibility\Types\Type[]
	 */
	private $visibility_types = [];

	/**
	 * Visibility type that is equivalent to empty value.
	 *
	 * @var \Sensei_Pro_Block_Visibility\Types\Everyone
	 */
	private $empty_type;

	/**
	 * Class instance.
	 *
	 * @var Visibility_Options
	 */
	private static $instance;

	/**
	 * Interactive blocks instance.
	 */
	public static function instance(): Visibility_Options {
		if ( ! self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Initialize the class singleton.
	 */
	private function __construct() {
		$this->empty_type = new Types\Everyone();

		$visibility_types = [
			$this->empty_type,
			new Types\No_One(),
			new Types\Enrolled_To_Course(),
			new Types\Not_Enrolled_To_Course(),
			new Types\Completed_Course(),
			new Types\Not_Completed_Course(),
			new Types\Completed_Lesson(),
			new Types\Not_Completed_Lesson(),
			new Types\Logged_In(),
			new Types\Logged_Out(),
			new Types\Groups(),
			new Types\Schedule(),
			new Types\Enrolled_To_Any_Course(),
			new Types\Not_Enrolled_To_Any_Course(),
			new Types\Completed_Any_Course(),
			new Types\Not_Completed_Any_Course(),
		];

		foreach ( $visibility_types as $visibility_type ) {
			$this->visibility_types[ $visibility_type->name() ] = $visibility_type;
		}
	}

	/**
	 * Retrieves a visibility type instance by name.
	 *
	 * @param string $name The name of the visibility type.
	 * @return null|\Sensei_Pro_Block_Visibility\Types\Type
	 */
	public function get_visibility_type( string $name ) {
		if ( ! isset( $this->visibility_types[ $name ] ) ) {
			return null;
		}
		return $this->visibility_types[ $name ];
	}

	/**
	 * Retrieves the visibility type that is equivalent
	 * to empty value.
	 *
	 * @return \Sensei_Pro_Block_Visibility\Types\Type
	 */
	public function get_empty_type() {
		return $this->empty_type;
	}

	/**
	 * Initializes the class and adds all filters and actions.
	 */
	public static function init() {
		$instance = self::instance();
		return $instance;
	}

	/**
	 * Returns the visibility options with values and i18n labels.
	 *
	 * @return Array
	 */
	public function get_visibility_options() {
		return array_map(
			function ( $type ) {
				return $type->get_option();
			},
			array_values( $this->visibility_types )
		);
	}

	/**
	 * Inlines the visibility options data for js.
	 */
	public function enqueue_inline_scripts() {
		$data = wp_json_encode(
			[
				'options'   => $this->get_visibility_options(),
				'emptyType' => $this->empty_type->name(),
				'screenId'  => get_current_screen()->id,
			]
		);

		$script = "window.sensei = window.sensei || {}; window.sensei.blockVisibility=$data;";
		wp_add_inline_script(
			'sensei-block-visibility-script',
			$script,
			'before'
		);
	}
}
