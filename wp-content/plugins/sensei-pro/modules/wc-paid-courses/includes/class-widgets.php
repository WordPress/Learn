<?php
/**
 * File containing the class \Sensei_WC_Paid_Courses\Widgets.
 *
 * @package sensei-wc-paid-courses
 * @since   1.0.0
 */

namespace Sensei_WC_Paid_Courses;

use Sensei_WC;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class for functionality related to widgets.
 *
 * @class Sensei_WC_Paid_Courses\Widgets
 */
final class Widgets {
	/**
	 * Instance of class.
	 *
	 * @var self
	 */
	private static $instance;

	/**
	 * Widgets constructor. Prevents other instances from being created outside of `Widgets::instance()`.
	 */
	private function __construct() {}

	/**
	 * Initializes the class and adds all filters and actions.
	 *
	 * @since 1.0.0
	 */
	public function init() {
		add_filter( 'sensei_widget_course_component_components_list', [ $this, 'course_component_add_components' ] );
		add_filter( 'sensei_widget_course_component_get_courses_freecourses', [ $this, 'course_component_get_free_courses' ], 10, 2 );
		add_filter( 'sensei_widget_course_component_get_courses_paidcourses', [ $this, 'course_component_get_paid_courses' ], 10, 2 );
	}

	/**
	 * Add WC related components to the course component widget.
	 *
	 * @since 1.0.0
	 *
	 * @param array $components_list {
	 *     Array of course components to allow in the widget.
	 *
	 *     @type string ${$component_name} Label for the component.
	 * }
	 * @return array
	 */
	public function course_component_add_components( $components_list ) {
		$components_list['freecourses'] = __( 'Free Courses', 'sensei-pro' );
		$components_list['paidcourses'] = __( 'Paid Courses', 'sensei-pro' );

		return $components_list;
	}

	/**
	 * Get the free courses for the `freecourses` component of the course component widget.
	 *
	 * @since 1.0.0
	 *
	 * @param \WP_Post[] $courses  List of course post objects.
	 * @param array      $instance Widget instance arguments.
	 * @return array
	 */
	public function course_component_get_free_courses( $courses, $instance ) {
		$args = [ 'posts_per_page' => intval( $instance['limit'] ) ];
		return Sensei_WC::get_free_courses( $args );
	}

	/**
	 * Get the paid courses for the `paidcourses` component of the course component widget.
	 *
	 * @since 1.0.0
	 *
	 * @param \WP_Post[] $courses  List of course post objects.
	 * @param array      $instance Widget instance arguments.
	 * @return array
	 */
	public function course_component_get_paid_courses( $courses, $instance ) {
		$args = [ 'posts_per_page' => intval( $instance['limit'] ) ];
		return Sensei_WC::get_paid_courses( $args );
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
}
