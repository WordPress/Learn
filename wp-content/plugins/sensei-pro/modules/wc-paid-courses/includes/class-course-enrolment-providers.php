<?php
/**
 * File containing the class \Sensei_WC_Paid_Courses\Course_Enrolment_Providers.
 *
 * @package sensei-wc-paid-courses
 * @since   2.0.0
 */

namespace Sensei_WC_Paid_Courses;

use Sensei_Course;
use Sensei_Utils;
use Sensei_WC_Paid_Courses\Course_Enrolment_Providers\WooCommerce_Memberships;
use Sensei_WC_Paid_Courses\Course_Enrolment_Providers\WooCommerce_Simple;
use Sensei_WC_Paid_Courses\Course_Enrolment_Providers\WooCommerce_Subscriptions;

/**
 * Registers the course enrolment providers.
 *
 * @since 2.0.0
 */
class Course_Enrolment_Providers {
	/**
	 * Instance of class.
	 *
	 * @var self
	 */
	private static $instance;

	/**
	 * Course_Enrolment_Providers constructor. Prevents other instances from being created outside of `Course_Enrolment_Providers::instance()`.
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
	 *
	 * @since 2.0.0
	 */
	public function init() {
		if ( self::use_legacy_enrolment_method() ) {
			return;
		}

		add_filter( 'sensei_course_enrolment_providers', [ $this, 'register_course_enrolment_providers' ] );

		include_once __DIR__ . '/course-enrolment-providers/class-woocommerce-simple.php';
		include_once __DIR__ . '/course-enrolment-providers/class-woocommerce-subscriptions.php';
		include_once __DIR__ . '/course-enrolment-providers/class-woocommerce-memberships.php';

		WooCommerce_Simple::instance()->init();

		if ( WooCommerce_Subscriptions::is_active() ) {
			WooCommerce_Subscriptions::instance()->init();
		}

		if ( WooCommerce_Memberships::is_active() ) {
			WooCommerce_Memberships::instance()->init();
		}
	}

	/**
	 * Registers course enrolment providers.
	 *
	 * @access private
	 *
	 * @param \Sensei_Course_Enrolment_Provider_Interface[] $course_enrolment_providers Array of current providers.
	 * @return \Sensei_Course_Enrolment_Provider_Interface[]
	 */
	public function register_course_enrolment_providers( $course_enrolment_providers ) {
		foreach ( $this->get_provider_classes() as $provider_class ) {
			$course_enrolment_providers[] = $provider_class::instance();
		}

		return $course_enrolment_providers;
	}

	/**
	 * Get list of all of our provider classes.
	 *
	 * @return array
	 */
	private function get_provider_classes() {
		$provider_classes   = [];
		$provider_classes[] = WooCommerce_Simple::class;

		if ( WooCommerce_Subscriptions::is_active() ) {
			$provider_classes[] = WooCommerce_Subscriptions::class;
		}

		if ( WooCommerce_Memberships::is_active() ) {
			$provider_classes[] = WooCommerce_Memberships::class;
		}

		return $provider_classes;
	}

	/**
	 * Checks if any of our providers handle enrolment for a course.
	 *
	 * @param int $course_id Course post ID.
	 *
	 * @return bool
	 */
	public function handles_enrolment( $course_id ) {
		$enrolment_manager = \Sensei_Course_Enrolment_Manager::instance();

		foreach ( $this->get_provider_classes() as $provider_class ) {
			$provider = $enrolment_manager->get_enrolment_provider_by_id( $provider_class::instance()->get_id() );
			if ( $provider && $provider->handles_enrolment( $course_id ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Temporary method while we still support Sensei 2.x.
	 *
	 * Do not use outside of WooCommerce Paid Courses.
	 *
	 * @access private
	 *
	 * @param int $course_id Course post ID.
	 * @param int $user_id   User ID.
	 *
	 * @return bool
	 */
	public static function is_user_enrolled( $course_id, $user_id ) {
		if ( ! $user_id ) {
			return false;
		}

		if ( self::use_legacy_enrolment_method() ) {
			// We're using a legacy version of Sensei (2.x or below).
			return false !== Sensei_Utils::user_started_course( $course_id, $user_id );
		}

		return Sensei_Course::is_user_enrolled( $course_id, $user_id );
	}

	/**
	 * Attempt to recalculate course enrolment when it might have changed.
	 *
	 * @param int $user_id   User ID.
	 * @param int $course_id Course ID.
	 * @return bool False if we should use legacy procedures to update course enrolment,
	 *              true if we triggered a course enrolment check.
	 */
	public static function trigger_course_enrolment_check( $user_id, $course_id ) {
		if ( self::use_legacy_enrolment_method() || ! class_exists( '\Sensei_Course_Enrolment_Manager' ) || ! method_exists( '\Sensei_Course_Enrolment_Manager', 'trigger_course_enrolment_check' ) ) {
			return false;
		}

		\Sensei_Course_Enrolment_Manager::trigger_course_enrolment_check( $user_id, $course_id );

		return true;
	}

	/**
	 * If the new enrolment provider method is not available, return true.
	 *
	 * @return bool
	 */
	public static function use_legacy_enrolment_method() {
		if ( ! interface_exists( '\Sensei_Course_Enrolment_Provider_Interface' ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Returns true of learner removal is enabled.
	 *
	 * @return bool
	 */
	public static function is_learner_removal_enabled() {
		return method_exists( 'Sensei_Course_Enrolment', 'remove_learner' );
	}
}
