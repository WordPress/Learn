<?php
/**
 * File containing the class \Sensei_Pro_Student_Groups\Enrolment\Providers.
 *
 * @package student-groups
 */

namespace Sensei_Pro_Student_Groups\Enrolment;

use Sensei_Course_Enrolment_Manager;
use Sensei_Course_Manual_Enrolment_Provider;

/**
 * Class Providers.
 *
 * @since 1.4.0
 */
class Providers {
	/**
	 * Instance of class.
	 *
	 * @var self
	 */
	private static $instance;

	/**
	 * Provider's constructor.
	 *
	 * Prevents other instances from being created outside of `Providers::instance()`.
	 */
	private function __construct() {}

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
	 * Initializes the class and adds all filters and actions.
	 */
	public function init() {
		add_filter( 'sensei_course_enrolment_providers', [ $this, 'register_course_enrolment_providers' ] );
		remove_filter( 'sensei_can_user_manually_enrol', [ Sensei_Course_Enrolment_Manager::instance(), 'maybe_prevent_frontend_manual_enrol' ], 10, 2 );
		add_filter( 'sensei_can_user_manually_enrol', [ $this, 'maybe_allow_frontend_manual_enrol' ], 10, 2 );
	}

	/**
	 * Registers course enrolment providers.
	 *
	 * @access private
	 *
	 * @param \Sensei_Course_Enrolment_Provider_Interface[] $course_enrolment_providers Array of current providers.
	 * @return \Sensei_Course_Enrolment_Provider_Interface[]
	 */
	public function register_course_enrolment_providers( $course_enrolment_providers ): array {
		$course_enrolment_providers[] = Groups_Provider::instance();

		return $course_enrolment_providers;
	}

	/**
	 * Allows manual enrolment for frontend courses.
	 *
	 * @access private
	 *
	 * @param bool $can_user_manually_enrol Whether the user can manually enrol.
	 * @param int  $course_id Course ID.
	 * @return bool
	 */
	public function maybe_allow_frontend_manual_enrol( $can_user_manually_enrol, $course_id ) {
		$enrolment_manager = Sensei_Course_Enrolment_Manager::instance();
		$all_providers     = $enrolment_manager->get_all_enrolment_providers();

		// If the manual provider has been filtered out, do not allow frontend enrolment.
		if ( ! isset( $all_providers[ Sensei_Course_Manual_Enrolment_Provider::instance()->get_id() ] ) ) {
			return false;
		}

		unset(
			$all_providers[ Sensei_Course_Manual_Enrolment_Provider::instance()->get_id() ],
			$all_providers[ Groups_Provider::instance()->get_id() ]
		);

		foreach ( $all_providers as $provider ) {
			if ( $provider->handles_enrolment( $course_id ) ) {
				// One of the other providers handles enrolment. Prevent enrolment on the frontend form.
				return false;
			}
		}

		if ( Groups_Provider::instance()->is_enrolled( get_current_user_id(), $course_id ) ) {
			return false;
		}

		return $can_user_manually_enrol;
	}
}
