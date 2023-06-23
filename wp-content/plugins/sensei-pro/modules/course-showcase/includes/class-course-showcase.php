<?php
/**
 * File containing the Course_Showcase class.
 *
 * @package sensei-pro
 * @since   1.12.0
 */

namespace Sensei_Pro\Course_Showcase;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Main class for the 'course-showcase' module.
 *
 * @since 1.12.0
 */
class Course_Showcase {

	/**
	 * Singleton instance.
	 *
	 * @var Course_Showcase
	 */
	private static $instance;

	/**
	 * Course_Showcase constructor.
	 */
	private function __construct() {
		// Silence is golden.
	}

	/**
	 * Fetch an instance of the class.
	 *
	 * @return Course_Showcase
	 */
	public static function instance(): Course_Showcase {
		if ( ! self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Initialize the class.
	 */
	public static function init(): void {
		require_once dirname( __FILE__ ) . '/class-course-showcase-senseilmscom-mapper.php';
		require_once dirname( __FILE__ ) . '/class-course-showcase-promote-action.php';
		require_once dirname( __FILE__ ) . '/class-course-showcase-feature-availability.php';
		require_once dirname( __FILE__ ) . '/class-course-showcase-listing.php';
		require_once dirname( __FILE__ ) . '/class-course-showcase-check-in-job.php';
		Course_Showcase_Listing::init();
		Course_Showcase_Check_In_Job::init();

		add_action( 'init', [ __CLASS__, 'setup_module' ] );
	}

	/**
	 * Set up the module on new versions of Sensei Pro.
	 *
	 * @internal
	 */
	public static function setup_module() {
		if ( version_compare( get_option( 'sensei_pro_course_showcase_version' ), SENSEI_PRO_VERSION, '>=' ) ) {
			return;
		}

		/**
		 * Action triggered when the 'course-showcase' module is set up on a new version.
		 *
		 * @since 1.13.0
		 *
		 * @hook sensei_pro_course_showcase_module_setup
		 */
		do_action( 'sensei_pro_course_showcase_module_setup' );
		update_option( 'sensei_pro_course_showcase_version', SENSEI_PRO_VERSION );
	}
}


