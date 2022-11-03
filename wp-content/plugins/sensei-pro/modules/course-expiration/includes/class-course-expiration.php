<?php
/**
 * File containing the class \Sensei_Pro_Course_Expiration\Course_Expiration.
 *
 * @package sensei-pro
 * @since   1.0.1
 */

namespace Sensei_Pro_Course_Expiration;

use Exception;
use Sensei_Pro\Course_Helper;
use Sensei_Pro_Course_Expiration\Background_Jobs\Course_Expiration_Job;
use Sensei_Pro_Course_Expiration\Background_Jobs\Course_Expiration_Recurring_Job;
use Sensei_Pro_Course_Expiration\Background_Jobs\Course_Expiration_Notification_Job;
use Sensei_Pro_Course_Expiration\Background_Jobs\Course_Expiration_Notification_Recurring_Job;
use Sensei_WC_Paid_Courses\Sensei_WC_Paid_Courses;
use DateInterval;
use DateTimeImmutable;
use Sensei_Course;
use Sensei_Course_Enrolment;
use Sensei_Learner;
use WP_Query;
use DateTime;

/**
 * Course Expiration class.
 *
 * @since 1.0.1
 */
class Course_Expiration {
	const MODULE_NAME = 'course-expiration';

	const EXPIRATION_TIMESTAMP_COURSE_META_PREFIX = '_sensei_course_expiration_';
	const START_TIMESTAMP_COURSE_META_PREFIX      = '_sensei_course_start_';

	const EXPIRED_TIMESTAMP_COURSE_META_PREFIX = '_sensei_course_expired_';

	const EXPIRATION_TYPE = '_course_expiration_type';
	const START_TYPE      = '_course_start_type';

	const EXPIRATION_LENGTH = '_course_expiration_length';
	const EXPIRATION_PERIOD = '_course_expiration_period';

	const EXPIRATION_DATE = '_course_expires_on_date';
	const START_DATE      = '_course_starts_on_date';

	/**
	 * Instance of class.
	 *
	 * @var self
	 */
	private static $instance;

	/**
	 * Module directory.
	 *
	 * @var string
	 */
	private $module_dir;

	/**
	 * Script and stylesheet loading.
	 *
	 * @var \Sensei_Assets
	 */
	public $assets;

	/**
	 * Course_Expiration constructor. Prevents other instances from being created outside `Course_Expiration::instance()`.
	 */
	private function __construct() {
		$this->module_dir = dirname( __DIR__ );
		$this->assets     = \Sensei_Pro\Modules\assets_loader( self::MODULE_NAME );
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
	 * Initializes the class and adds all filters and actions.
	 */
	public static function init() {
		$instance = self::instance();
		$instance->include_dependencies();

		add_action( 'enqueue_block_editor_assets', [ $instance, 'enqueue_block_editor_assets' ] );
		add_filter( 'sensei_default_feature_flag_settings', [ $instance, 'add_feature_flags' ] );

		add_action( 'init', [ $instance, 'register_course_expiration_post_meta' ] );
		add_action( 'init', [ $instance, 'register_course_start_post_meta' ] );

		add_action( 'sensei_course_enrolment_status_changed', [ $instance, 'set_expiration_date' ], 10, 3 );
		add_action( 'sensei_course_enrolment_status_changed', [ $instance, 'set_access_period_start_date' ], 10, 3 );

		add_filter( 'sensei_is_enrolled', [ $instance, 'check_expiration' ], 10, 3 );
		add_filter( 'sensei_can_user_manually_enrol', [ $instance, 'can_user_enroll' ], 10, 2 );

		// Expand learner management.
		add_action( 'sensei_admin_enrol_user', [ $instance, 'handle_manual_enrolment' ], 10, 2 );
		add_filter( 'sensei_learners_default_columns', [ $instance, 'add_learner_management_expiration_columns' ], 10, 2 );
		add_filter( 'sensei_learners_default_columns', [ $instance, 'add_learner_management_starts_access_columns' ], 10, 2 );
		add_filter( 'sensei_learners_main_column_data', [ $instance, 'enrich_learner_management_data' ], 10, 4 );

		add_filter( 'sensei_learners_learner_updated', [ $instance, 'update_learner_expiry_date' ], 10, 3 );
		add_filter( 'sensei_learners_learner_updated', [ $instance, 'update_learner_start_date' ], 10, 3 );

		// Show buttons for expired users.
		add_filter( 'sensei_render_view_results_block', [ $instance, 'maybe_render_view_results_block' ], 10, 3 );
		add_filter( 'sensei_display_course_enrollment_actions', [ $instance, 'maybe_display_course_enrollment_actions' ], 10, 4 );

		// Extend user courses page.
		add_filter( 'sensei_user_courses_query', [ $instance, 'extend_user_courses_query' ], 10, 4 );
		add_filter( 'sensei_user_courses_filter_options', [ $instance, 'extend_user_courses_filter_options' ] );

		add_action( 'sensei_course_content_inside_after', [ $instance, 'add_learner_course_expiration_message' ], 25 );
		add_action( 'sensei_course_content_inside_after', [ $instance, 'add_learner_course_not_started_message' ], 25 );

		// Update notifications.
		add_filter( 'sensei_lesson_show_course_signup_notice', [ $instance, 'hide_signup_notice' ], 10, 2 );
		add_filter( 'sensei_module_show_course_signup_notice', [ $instance, 'hide_signup_notice' ], 10, 2 );

		// Add notification if the user is logged in.
		if ( is_user_logged_in() ) {
			add_action( 'template_redirect', [ $instance, 'add_expiry_notification' ], 5 );
			add_action( 'template_redirect', [ $instance, 'add_not_started_notification' ], 5 );
		}
		add_filter( 'sensei_user_quiz_status', [ $instance, 'update_quiz_signup_notice' ], 10, 3 );

		// Init expiration background jobs.
		Course_Expiration_Job::init();
		Course_Expiration_Recurring_Job::init();
		Course_Expiration_Notification_Job::init();
		Course_Expiration_Notification_Recurring_Job::init();
	}

	/**
	 * Include dependencies.
	 *
	 * @access private
	 */
	private function include_dependencies() {
		// Emails.
		include_once $this->module_dir . '/includes/emails/class-course-expiration-email.php';

		// Background jobs.
		include_once $this->module_dir . '/includes/background-jobs/class-course-expiration-job.php';
		include_once $this->module_dir . '/includes/background-jobs/class-course-expiration-recurring-job.php';
		include_once $this->module_dir . '/includes/background-jobs/class-course-expiration-notification-job.php';
		include_once $this->module_dir . '/includes/background-jobs/class-course-expiration-notification-recurring-job.php';
	}

	/**
	 * Enqueues assets for the block editor for the Course and Lesson editors.
	 *
	 * @access private
	 * @since 1.1.0
	 */
	public function enqueue_block_editor_assets() {
		$screen    = get_current_screen();
		$post_type = $screen->id;

		if ( 'course' === $post_type ) {
			$this->assets->enqueue( 'sensei-pro-course-expiration', 'block-editor/course-expiration.js' );
			$this->assets->enqueue( 'sensei-pro-course-expiration-sidebar', 'block-editor/course-expiration-sidebar.css' );
		}
	}

	/**
	 * Add feature flags for Course Expiration.
	 *
	 * @access private
	 *
	 * @param array $default_feature_flag_settings Default feature flags.
	 *
	 * @return array Default feature flags.
	 */
	public function add_feature_flags( $default_feature_flag_settings ) {
		return $default_feature_flag_settings;
	}

	/**
	 * Register post meta needed for course expiration.
	 *
	 * @access private
	 */
	public function register_course_expiration_post_meta() {
		register_post_meta(
			'course',
			self::EXPIRATION_TYPE,
			[
				'show_in_rest'  => true,
				'single'        => true,
				'type'          => 'string',
				'default'       => 'no-expiration',
				'auth_callback' => [ $this, 'course_expiration_post_meta_auth_callback' ],
			]
		);

		register_post_meta(
			'course',
			self::EXPIRATION_LENGTH,
			[
				'show_in_rest'      => true,
				'single'            => true,
				'type'              => 'integer',
				'default'           => 1,
				'sanitize_callback' => function( $value ) {
					return max( 1, absint( $value ) );
				},
				'auth_callback'     => [ $this, 'course_expiration_post_meta_auth_callback' ],
			]
		);

		register_post_meta(
			'course',
			self::EXPIRATION_PERIOD,
			[
				'show_in_rest'  => true,
				'single'        => true,
				'type'          => 'string',
				'default'       => 'month',
				'auth_callback' => [ $this, 'course_expiration_post_meta_auth_callback' ],
			]
		);

		register_post_meta(
			'course',
			self::EXPIRATION_DATE,
			[
				'show_in_rest'  => true,
				'single'        => true,
				'type'          => 'string',
				'auth_callback' => [ $this, 'course_expiration_post_meta_auth_callback' ],
			]
		);
	}

	/**
	 * Register post meta needed for course start access period.
	 *
	 * @since 1.6.0
	 * @access private
	 */
	public function register_course_start_post_meta() {
		register_post_meta(
			'course',
			self::START_TYPE,
			[
				'show_in_rest'  => true,
				'single'        => true,
				'type'          => 'string',
				'default'       => 'immediately',
				'auth_callback' => [ $this, 'course_expiration_post_meta_auth_callback' ],
			]
		);

		register_post_meta(
			'course',
			self::START_DATE,
			[
				'show_in_rest'  => true,
				'single'        => true,
				'type'          => 'string',
				'auth_callback' => [ $this, 'course_expiration_post_meta_auth_callback' ],
			]
		);
	}

	/**
	 * Post meta auth callback.
	 *
	 * @access private
	 *
	 * @param bool   $allowed True if allowed to view the meta field by default, false otherwise.
	 * @param string $meta_key Meta key.
	 * @param int    $post_id  Lesson ID.
	 *
	 * @return bool Whether the user can edit the post meta.
	 */
	public function course_expiration_post_meta_auth_callback( $allowed, $meta_key, $post_id ) {
		return current_user_can( 'edit_post', $post_id );
	}

	/**
	 * Set access expiration date.
	 *
	 * @access private
	 *
	 * @param int  $user_id     User ID.
	 * @param int  $course_id   Course post ID.
	 * @param bool $is_enrolled New enrolment status.
	 */
	public function set_expiration_date( $user_id, $course_id, $is_enrolled ) {

		$course_expiration_type = get_post_meta( $course_id, self::EXPIRATION_TYPE, true );
		$expiration_post_meta   = self::EXPIRATION_TIMESTAMP_COURSE_META_PREFIX . $user_id;
		$expired_course_meta    = self::EXPIRED_TIMESTAMP_COURSE_META_PREFIX . $user_id;

		// If user is being enrolled, we want to remove their already expired access.
		if ( $is_enrolled ) {
			delete_post_meta( $course_id, $expired_course_meta );
		}

		// Remove expiration if learner was removed or if it's being enrolled with no expiration.
		// The second case will probably not have the meta, but it's there for any special case.
		if ( ! $is_enrolled || empty( $course_expiration_type ) || 'no-expiration' === $course_expiration_type ) {
			delete_post_meta( $course_id, $expiration_post_meta );
			return;
		}

		if ( 'expires-on' === $course_expiration_type ) {
			$course_expiration_date = get_post_meta( $course_id, self::EXPIRATION_DATE, true );
			$expiration_date        = new DateTime( $course_expiration_date );
			update_post_meta(
				$course_id,
				$expiration_post_meta,
				$expiration_date->getTimestamp()
			);
			return;
		}

		update_post_meta(
			$course_id,
			$expiration_post_meta,
			$this->calculate_expiration_date_timestamp( $user_id, $course_id, current_datetime() )
		);
	}


	/**
	 * Set access period start date.
	 *
	 * @since 1.6.0
	 * @access private
	 *
	 * @param int  $user_id User ID.
	 * @param int  $course_id Course post ID.
	 * @param bool $is_enrolled New enrolment status.
	 * @throws Exception Exception.
	 */
	public function set_access_period_start_date( $user_id, $course_id, $is_enrolled ) {
		$course_start_type = get_post_meta( $course_id, self::START_TYPE, true );
		$start_post_meta   = self::START_TIMESTAMP_COURSE_META_PREFIX . $user_id;

		// Remove start access period if learner was removed or if it's being enrolled with no start date.
		if ( ! $is_enrolled || empty( $course_start_type ) || 'immediately' === $course_start_type ) {
			delete_post_meta( $course_id, $start_post_meta );
			return;
		}

		if ( 'starts-on' === $course_start_type ) {
			$course_start_date = get_post_meta( $course_id, self::START_DATE, true );
			$start_date        = new DateTime( $course_start_date );

			update_post_meta(
				$course_id,
				$start_post_meta,
				$start_date->getTimestamp()
			);
		}
	}
	/**
	 * Get the interval until course expiry to be used in notifications.
	 *
	 * @param int $user_id   The user id.
	 * @param int $course_id The course id.
	 *
	 * @return DateInterval|null The expiration interval from now.
	 */
	private function get_notification_interval( int $user_id, int $course_id ) {
		$expiration_datetime = $this->get_user_expiration_datetime( $user_id, $course_id );

		if ( empty( $expiration_datetime ) ) {
			return null;
		}

		$current_datetime = current_datetime();

		return $current_datetime->diff( $expiration_datetime );
	}

	/**
	 * Get the days remaining before expiration.
	 *
	 * @param int $user_id   The user id.
	 * @param int $course_id The course id.
	 *
	 * @return int|null The days remaining.
	 */
	private function get_days_remaining( int $user_id, int $course_id ) {
		$notification_interval = $this->get_notification_interval( $user_id, $course_id );
		$days_remaining        = null !== $notification_interval ? $notification_interval->days : null;

		return $days_remaining;
	}

	/**
	 * Adds the course expiry notification.
	 *
	 * @access private
	 */
	public function add_expiry_notification() {

		$course_id = Course_Helper::get_course_id_for_current_page();

		if ( ! $course_id ) {
			return;
		}

		$user_id = get_current_user_id();

		// Don't show access period expired message if not needed.
		$display_access_expired_message = $this->should_display_expiration_message( $course_id, $user_id );
		if ( ! $display_access_expired_message ) {
			return;
		}

		$days_remaining = $this->get_days_remaining( $user_id, $course_id ) ?? 0;

		list( $message ) = $this->get_expiration_message(
			$user_id,
			$course_id,
			// translators: Placeholder is the expiration date.
			__( 'Your access expired on %s.', 'sensei-pro' ),
			__( 'Your access expires today.', 'sensei-pro' ),
			// translators: Placeholder is the number of days.
			_n( 'Your access expires in %d day.', 'Your access expires in %d days.', $days_remaining, 'sensei-pro' )
		);

		if ( $message ) {
			Sensei()->notices->add_notice( $message, 'clock', 'sensei-course-expiry-interval' );
		}
	}

	/**
	 * Adds the course not started notification.
	 *
	 * @access private
	 */
	public function add_not_started_notification() {

		$course_id = Course_Helper::get_course_id_for_current_page();

		if ( ! $course_id ) {
			return;
		}

		$user_id = get_current_user_id();

		// Don't show start access period message if it shouldn't be displayed.
		$display_access_period_start_message = $this->should_display_start_access_message( $course_id, $user_id );
		if ( ! $display_access_period_start_message ) {
			return;
		}

		list( $message ) = $this->get_not_started_message(
			$user_id,
			$course_id,
			// translators: Placeholder is the expiration date.
			__( 'Your access will start on %s.', 'sensei-pro' )
		);

		if ( $message ) {
			Sensei()->notices->add_notice( $message, 'clock', 'sensei-course-expiry-interval' );
		}
	}

	/**
	 * Get expiration message using format strings. If a format is not sent, it will return
	 * `null` if the result matches that.
	 *
	 * @param int         $user_id            User ID.
	 * @param int         $course_id          Course ID.
	 * @param string      $expired_format     Expired format string.
	 * @param string|null $today_format       Today format string.
	 * @param string|null $countdown_format   Countdown format string.
	 * @param string|null $future_date_format Future date format string.
	 *
	 * @return array|null Tuple containing the expiration message and the type.
	 */
	private function get_expiration_message( int $user_id, int $course_id, string $expired_format, string $today_format = null, string $countdown_format = null, string $future_date_format = null ) {
		if ( $this->is_access_expired( $user_id, $course_id ) ) {
			$expiration_datetime = $this->get_user_expiration_datetime( $user_id, $course_id );

			if ( null !== $expiration_datetime ) {
				return [ sprintf( $expired_format, $expiration_datetime->format( __( 'M d, Y', 'sensei-pro' ) ) ), 'expired' ];
			}
		}

		if ( ! Sensei_Course::is_user_enrolled( $course_id ) ) {
			return null;
		}

		$notification_interval = $this->get_notification_interval( $user_id, $course_id );

		if ( null === $notification_interval ) {
			return null;
		}

		$days_remaining = $notification_interval->days;

		if ( 0 === $days_remaining ) {
			return null === $today_format ? null : [ $today_format, 'today' ];
		}

		$expiration_datetime = $this->get_user_expiration_datetime( $user_id, $course_id );

		/**
		 * During this interval before the course expiry date, notices will be displayed in days.
		 *
		 * @since 2.6.0
		 * @hook sensei_wc_paid_courses_expiration_countdown_notice_threshold
		 *
		 * @param {int} $interval The default interval.
		 *
		 * @return {int} $interval The modified interval.
		 */
		$expiration_threshold = apply_filters( 'sensei_wc_paid_courses_expiration_countdown_notice_threshold', 7 );

		if ( $expiration_threshold >= $days_remaining ) {
			return null === $countdown_format
				? null
				: [ sprintf( $countdown_format, $days_remaining ), 'countdown' ];
		}

		return null === $future_date_format
			? null
			: [ sprintf( $future_date_format, $expiration_datetime->format( __( 'M d, Y', 'sensei-pro' ) ) ), 'future' ];
	}

	/**
	 * Get not started message using format strings. If a format is not sent, it will return
	 * `null` if the result matches that.
	 *
	 * @param int    $user_id            User ID.
	 * @param int    $course_id          Course ID.
	 * @param string $message           Not started message string.
	 *
	 * @return array|null Tuple containing the expiration message and the type.
	 */
	private function get_not_started_message( int $user_id, int $course_id, string $message ) {
		$start_date = $this->get_user_start_datetime( $user_id, $course_id );

		return $start_date ? [ sprintf( $message, $start_date->format( __( 'M d, Y', 'sensei-pro' ) ) ), 'expired' ] : null;
	}

	/**
	 * Get expiration date timestamp calculated based on the course settings or base on the length and period arguments.
	 *
	 * @param int               $user_id           User ID.
	 * @param int               $course_id         Course ID.
	 * @param DateTimeImmutable $start_date        Start date.
	 *
	 * @return int Calculated expiration date timestamp.
	 */
	private function calculate_expiration_date_timestamp( int $user_id, int $course_id, DateTimeImmutable $start_date ) : int {
		$course_expiration_length = get_post_meta( $course_id, self::EXPIRATION_LENGTH, true );
		$course_expiration_period = get_post_meta( $course_id, self::EXPIRATION_PERIOD, true );
		$expiration_date          = $start_date->setTime( 0, 0, 0 )->modify( "+{$course_expiration_length} {$course_expiration_period}" );

		// Solution for month issue inspired by https://www.php.net/manual/en/datetime.add.php#118342.
		// See issue in the above link in the "Example #3 Beware when adding months".
		if ( 'month' === $course_expiration_period ) {
			$start_day      = $start_date->format( 'd' );
			$expiration_day = $expiration_date->format( 'd' );

			if ( $start_day !== $expiration_day ) {
				$expiration_date = $expiration_date->modify( "-{$expiration_day} day" );
			}
		}

		// Expires in the last second of the previous day.
		$expiration_date = $expiration_date->modify( '-1 second' );

		/**
		 * Expiration date for a learner access.
		 *
		 * @since 2.6.0
		 * @hook sensei_wc_paid_courses_learner_access_expiration_date
		 *
		 * @param {DateTimeImmutable} $expiration_date Expiration date.
		 * @param {DateTimeImmutable} $start_date      Start date.
		 * @param {int}               $user_id         User ID.
		 * @param {int}               $course_id       Course ID.
		 *
		 * @return {DateTimeImmutable} Expiration date.
		 */
		$expiration_date = apply_filters(
			'sensei_wc_paid_courses_learner_access_expiration_date',
			$expiration_date,
			$start_date,
			$user_id,
			$course_id
		);

		return $expiration_date->getTimestamp();
	}

	/**
	 * Check whether learner access is expired.
	 * It filters the `is_enrolled` value, returning false in case the access is expired.
	 *
	 * @access private
	 *
	 * @param bool|null $is_enrolled If a boolean, that value will be used. Null values will keep default behavior.
	 * @param int       $user_id     User ID.
	 * @param int       $course_id   Course post ID.
	 *
	 * @return false|null False if it's expired or not started. Original `$is_enrolled` if not expired.
	 */
	public function check_expiration( $is_enrolled, int $user_id, int $course_id ) {

		if ( $this->is_access_expired_or_not_started( $user_id, $course_id ) ) {
			return false;
		}

		return $is_enrolled;
	}

	/**
	 * Check whether learner access is expired or not started.
	 *
	 * @access public
	 * @since 1.6.0
	 *
	 * @param int $user_id     User ID.
	 * @param int $course_id   Course post ID.
	 *
	 * @return bool Whether the access has expired or not started.
	 */
	public function is_access_expired_or_not_started( int $user_id, int $course_id ) : bool {
		$is_expired     = $this->is_access_expired( $user_id, $course_id );
		$is_not_started = $this->is_access_not_started( $user_id, $course_id );
		return $is_expired || $is_not_started;
	}

	/**
	 * Check whether learner access is expired.
	 *
	 * @param int $user_id     User ID.
	 * @param int $course_id   Course post ID.
	 *
	 * @return bool Whether the access has expired.
	 */
	public function is_access_expired( int $user_id, int $course_id ) : bool {
		$expiration_timestamp = $this->get_user_expiration_timestamp( $user_id, $course_id );
		return null !== $expiration_timestamp && current_datetime()->getTimestamp() >= $expiration_timestamp;

	}

	/**
	 * Check whether learner access is not started.
	 *
	 * @acces private.
	 *
	 * @param int $user_id     User ID.
	 * @param int $course_id   Course post ID.
	 *
	 * @return bool Whether the access is not started.
	 */
	public function is_access_not_started( int $user_id, int $course_id ): bool {
		$start_timestamp = $this->get_user_start_timestamp( $user_id, $course_id );
		return null !== $start_timestamp && current_datetime()->getTimestamp() <= $start_timestamp;
	}

	/**
	 * Get the user expiration timestamp for a course.
	 *
	 * @param int $user_id   The user id.
	 * @param int $course_id The course id.
	 *
	 * @return int|null The expiration timestamp.
	 */
	private function get_user_expiration_timestamp( int $user_id, int $course_id ) {
		$expired_post_meta = self::EXPIRED_TIMESTAMP_COURSE_META_PREFIX . $user_id;
		$expired_timestamp = get_post_meta( $course_id, $expired_post_meta, true );

		if ( ! empty( $expired_timestamp ) ) {
			return $expired_timestamp;
		}

		$expiration_post_meta = self::EXPIRATION_TIMESTAMP_COURSE_META_PREFIX . $user_id;
		$expiration_timestamp = get_post_meta( $course_id, $expiration_post_meta, true );

		return empty( $expiration_timestamp ) ? null : $expiration_timestamp;
	}

	/**
	 * Get the user start timestamp for a course.
	 *
	 * @param int $user_id   The user id.
	 * @param int $course_id The course id.
	 *
	 * @return int|null The start timestamp.
	 */
	private function get_user_start_timestamp( int $user_id, int $course_id ) {
		$start_post_meta = self::START_TIMESTAMP_COURSE_META_PREFIX . $user_id;
		$start_timestamp = get_post_meta( $course_id, $start_post_meta, true );

		return empty( $start_timestamp ) ? null : $start_timestamp;
	}

	/**
	 * Get the user expiration datetime for a course.
	 *
	 * @param int $user_id   The user id.
	 * @param int $course_id The course id.
	 *
	 * @return DateTimeImmutable|null The expiration datetime.
	 */
	private function get_user_expiration_datetime( int $user_id, int $course_id ) {
		$expiration_timestamp = $this->get_user_expiration_timestamp( $user_id, $course_id );

		if ( null !== $expiration_timestamp ) {
			$expiration_datetime = current_datetime()->setTimestamp( $expiration_timestamp );
			return $expiration_datetime;
		}

		return null;
	}

	/**
	 * Get the user expiration datetime for a course.
	 *
	 * @param int $user_id   The user id.
	 * @param int $course_id The course id.
	 *
	 * @return DateTimeImmutable|null The expiration datetime.
	 */
	private function get_user_start_datetime( int $user_id, int $course_id ) {
		$start_timestamp = $this->get_user_start_timestamp( $user_id, $course_id );

		if ( null !== $start_timestamp ) {
			return current_datetime()->setTimestamp( $start_timestamp );
		}

		return null;
	}

	/**
	 * Handler method for sensei_can_user_manually_enrol filter. Responsible for blocking a new enrolment for users that
	 * their enrolment expired.
	 *
	 * @access private
	 *
	 * @param bool $can_user_manually_enrol Default value of the flag.
	 * @param int  $course_id               Course post ID.
	 *
	 * @return bool False if it's expired. Original `$can_user_manually_enrol` if not expired.
	 */
	public function can_user_enroll( bool $can_user_manually_enrol, int $course_id ) : bool {
		if ( $this->is_access_expired_or_not_started( get_current_user_id(), $course_id ) ) {
			return false;
		}

		return $can_user_manually_enrol;
	}

	/**
	 * Expire learner access.
	 *
	 * @param int $user_id   User ID.
	 * @param int $course_id Course post ID.
	 */
	public function expire_access( int $user_id, int $course_id ) {
		$expiration_post_meta = self::EXPIRATION_TIMESTAMP_COURSE_META_PREFIX . $user_id;
		$expiration_timestamp = get_post_meta( $course_id, $expiration_post_meta, true );
		$expired_post_meta    = self::EXPIRED_TIMESTAMP_COURSE_META_PREFIX . $user_id;
		$course_enrolment     = Sensei_Course_Enrolment::get_course_instance( $course_id );

		add_post_meta( $course_id, $expired_post_meta, $expiration_timestamp, true );
		delete_post_meta( $course_id, $expiration_post_meta );

		$course_enrolment->save_enrolment( $user_id, false );
	}

	/**
	 * Render view results block if learner access has expired.
	 *
	 * @param bool $render Whether render the view results block.
	 *
	 * @return bool Whether render the view results block.
	 */
	public function maybe_render_view_results_block( bool $render ) : bool {
		$user_id = get_current_user_id();
		$course  = get_post();

		if ( empty( $user_id ) || empty( $course ) ) {
			return $render;
		}

		$course_id = $course->ID;

		if ( $this->is_access_expired_or_not_started( $user_id, $course_id ) ) {
			return true;
		}

		return $render;
	}

	/**
	 * Display course enrollment actions if learner access has expired.
	 *
	 * @param bool $display_actions  Whether display the actions.
	 * @param int  $course_id        Course ID.
	 * @param int  $user_id          User ID.
	 * @param bool $completed_course Whether user completed the course.
	 *
	 * @return bool Whether display course enrollment actions.
	 */
	public function maybe_display_course_enrollment_actions( bool $display_actions, int $course_id, int $user_id, bool $completed_course ) : bool {
		if ( empty( $user_id ) || empty( $course_id ) ) {
			return $display_actions;
		}

		if ( $completed_course && $this->is_access_expired_or_not_started( $user_id, $course_id ) ) {
			return true;
		}

		return $display_actions;
	}

	/**
	 * Extend user courses query to support course expiration.
	 *
	 * @access private
	 *
	 * @param null   $query
	 * @param int    $user_id         The user id.
	 * @param string $status          Status of query to run.
	 * @param array  $base_query_args Base query args.
	 *
	 * @return WP_Query The query.
	 */
	public function extend_user_courses_query( $query, int $user_id, string $status, array $base_query_args ) {
		$learner_manager = Sensei_Learner::instance();

		if ( 'all' === $status || 'complete' === $status ) {
			$only_ids_args = [
				'fields'         => 'ids',
				'posts_per_page' => -1,
			];

			// Get enrolled course ids.
			$enrolled_course_ids_query = $learner_manager->get_enrolled_courses_query( $user_id, $only_ids_args );
			$enrolled_course_ids       = $enrolled_course_ids_query->posts;

			add_filter( 'sensei_learner_enrolled_courses_args', [ $this, 'remove_tax_query_from_course_query' ] );

			// Get expired course ids.
			$expired_course_ids_query = $this->get_user_expired_courses_query( $user_id, $only_ids_args );
			$expired_course_ids       = $expired_course_ids_query->posts;

			// If there are neither enrolled nor expired courses, we set the courses to -1 otherwise post__in argument of
			// WP_Query will be ignored.
			$merged_courses = array_merge( $enrolled_course_ids, $expired_course_ids );

			if ( empty( $merged_courses ) ) {
				$merged_courses = [ -1 ];
			}
		}

		if ( 'all' === $status ) {
			// Return enrolled courses query, including expired courses.
			return $learner_manager->get_enrolled_courses_query(
				$user_id,
				wp_parse_args(
					[ 'post__in' => $merged_courses ],
					$base_query_args
				)
			);
		} elseif ( 'complete' === $status ) {
			// Return completed courses query, including enrolled and expired courses.
			return $learner_manager->get_enrolled_completed_courses_query(
				$user_id,
				wp_parse_args(
					[ 'post__in' => $merged_courses ],
					$base_query_args
				)
			);
		} elseif ( 'expired' === $status ) {
			add_filter( 'sensei_learner_enrolled_courses_args', [ $this, 'remove_tax_query_from_course_query' ] );

			return $this->get_user_expired_courses_query(
				$user_id,
				$learner_manager->get_enrolled_courses_query_args( $user_id, $base_query_args )
			);
		}

		remove_filter( 'sensei_learner_enrolled_courses_args', [ $this, 'remove_tax_query_from_course_query' ] );

		return $query;
	}

	/**
	 * Get query to fetch a learner's expired courses.
	 *
	 * @param int   $user_id         User ID.
	 * @param array $base_query_args Base query arguments.
	 *
	 * @return WP_Query The query.
	 */
	private function get_user_expired_courses_query( $user_id, $base_query_args = [] ) {
		$query_args = $this->get_user_expired_courses_query_args( $user_id, $base_query_args );

		return new WP_Query( $query_args );
	}

	/**
	 * Get the arguments to pass to WP_Query to fetch a learner's expired courses.
	 *
	 * @param int   $user_id         User ID.
	 * @param array $base_query_args Base query arguments.
	 *
	 * @return array The query args.
	 */
	private function get_user_expired_courses_query_args( $user_id, $base_query_args = [] ) {
		return wp_parse_args(
			[
				'post_status'  => 'publish',
				'post_type'    => 'course',
				'meta_key'     => "_sensei_course_expired_{$user_id}",
				'meta_compare' => 'IN',
			],
			$base_query_args
		);
	}

	/**
	 * It removes the tax query from course query. It's needed to
	 * list the user courses including the expired courses.
	 *
	 * @access private
	 *
	 * @param array $query_args The query args.
	 *
	 * @return array Query arguments.
	 */
	public function remove_tax_query_from_course_query( $query_args ) {
		unset( $query_args['tax_query'] );

		return $query_args;
	}

	/**
	 * It adds the expired filter option to the user courses page.
	 *
	 * @access private
	 *
	 * @param array $filter_options The filter options.
	 *
	 * @return array The filter options.
	 */
	public function extend_user_courses_filter_options( $filter_options ) {
		$expired_courses_query = $this->get_user_expired_courses_query( get_current_user_id() );

		if ( $expired_courses_query->found_posts > 0 ) {
			$filter_options['expired'] = __( 'Expired', 'sensei-pro' );
		}

		return $filter_options;
	}

	/**
	 * Removes the users course expiry.
	 *
	 * @param int $course_id Course post ID.
	 * @param int $user_id   User which was manually enrolled.
	 *
	 * @return bool If removal was successful.
	 */
	private function remove_user_expiry( int $course_id, int $user_id ) : bool {
		$deleted_expiry     = delete_post_meta( $course_id, self::EXPIRED_TIMESTAMP_COURSE_META_PREFIX . $user_id );
		$deleted_expiration = delete_post_meta( $course_id, self::EXPIRATION_TIMESTAMP_COURSE_META_PREFIX . $user_id );

		return $deleted_expiration || $deleted_expiry;
	}


	/**
	 * Removes the users course started.
	 *
	 * @param int $course_id Course post ID.
	 * @param int $user_id   User which was manually enrolled.
	 *
	 * @return bool If removal was successful.
	 */
	private function remove_user_started( int $course_id, int $user_id ) : bool {
		return delete_post_meta( $course_id, self::START_TIMESTAMP_COURSE_META_PREFIX . $user_id );
	}

	/**
	 * Handle manual enrolment of learner.
	 *
	 * @access private
	 *
	 * @param int $course_id Course post ID.
	 * @param int $user_id   User which was manually enrolled.
	 */
	public function handle_manual_enrolment( int $course_id, int $user_id ) {
		// There is a window in which although the access has expired, the job hasn't run yet to update the enrolment
		// term. This can cause the term value to be out of sync with the actual enrolment. The lines below make the
		// 2 values in sync again.
		$course_enrolment = Sensei_Course_Enrolment::get_course_instance( $course_id );
		$is_enrolled      = $course_enrolment->is_enrolled( $user_id );
		$course_enrolment->save_enrolment( $user_id, $is_enrolled );

		$this->remove_user_expiry( $course_id, $user_id );
		$this->remove_user_started( $course_id, $user_id );
	}

	/**
	 * Adds the 'Access Expiration' column in learner management.
	 *
	 * @access private
	 *
	 * @param array                 $columns           The default columns.
	 * @param \Sensei_Learners_Main $learners_instance The learners instance.
	 *
	 * @returns array The modified columns.
	 */
	public function add_learner_management_expiration_columns( array $columns, $learners_instance ) : array {
		if ( 'learners' !== $learners_instance->get_view() ) {
			return $columns;
		}

		Sensei_WC_Paid_Courses::instance()->assets->enqueue_style( Sensei_WC_Paid_Courses::STYLE_LEARNER_MANAGEMENT );

		$first_two_columns = array_slice( $columns, 0, 2, true );
		$rest_of_columns   = array_slice( $columns, 2, null, true );
		$expiry            = [ 'expiry_date' => __( 'Access Expiration', 'sensei-pro' ) ];

		return array_merge_recursive( $first_two_columns, $expiry, $rest_of_columns );
	}

	/**
	 * Adds the 'Start Access' column in student management.
	 *
	 * @access private
	 *
	 * @param array                 $columns           The default columns.
	 * @param \Sensei_Learners_Main $learners_instance The learners instance.
	 *
	 * @returns array The modified columns.
	 */
	public function add_learner_management_starts_access_columns( array $columns, $learners_instance ) : array {
		if ( 'learners' !== $learners_instance->get_view() ) {
			return $columns;
		}

		Sensei_WC_Paid_Courses::instance()->assets->enqueue_style( Sensei_WC_Paid_Courses::STYLE_LEARNER_MANAGEMENT );

		$first_two_columns = array_slice( $columns, 0, 2, true );
		$rest_of_columns   = array_slice( $columns, 2, null, true );
		$start             = [ 'start_access_date' => __( 'Start Access', 'sensei-pro' ) ];

		return array_merge_recursive( $first_two_columns, $start, $rest_of_columns );
	}

	/**
	 * Generates the data for the 'Access Expiration' column in learner management.
	 *
	 * @access private
	 *
	 * @param array       $columns   The column values.
	 * @param \WP_Comment $comment   Learner progress comment.
	 * @param ?int        $post_id   The post id.
	 * @param ?string     $post_type The post type.
	 */
	public function enrich_learner_management_data( array $columns, $comment, int $post_id = null, string $post_type = null ) : array {
		if ( empty( $post_id ) || empty( $post_type ) ) {
			return $columns;
		}

		$course_id           = 'course' === $post_type ? $post_id : Sensei()->lesson->get_course_id( $post_id );
		$expiration_datetime = $this->get_user_expiration_datetime( $comment->user_id, $course_id );
		$expiry_date         = null === $expiration_datetime ? '' : $expiration_datetime->format( 'Y-m-d' );

		$is_expired = $this->is_access_expired( $comment->user_id, $course_id );

		$columns['expiry_date'] = $this->get_expiry_date_column( $expiry_date, $is_expired );

		if ( null !== $expiration_datetime && $is_expired ) {
			if ( ! in_array( $comment->comment_approved, [ 'complete', 'graded', 'passed' ], true ) ) {
				$columns['user_status'] = '<span class="incomplete">' . esc_html__( 'Incomplete', 'sensei-pro' ) . '</span>';
			}
		}
		$start_datetime               = $this->get_user_start_datetime( $comment->user_id, $course_id );
		$start_date                   = null === $start_datetime ? '' : $start_datetime->format( 'Y-m-d' );
		$columns['start_access_date'] = $this->get_start_date_column( $start_date );

		return $columns;
	}

	/**
	 * Get the contents of the Access Expiration column.
	 *
	 * @param string $expiry_date The expiry date.
	 *
	 * @return string
	 */
	private function get_expiry_date_column( string $expiry_date ) : string {
		return '<input class="edit-date-date-picker access-expiration" data-name="expiration-date" type="text" value="' . esc_attr( $expiry_date ) . '">';
	}

	/**
	 * Get the contents of the Access Start column.
	 *
	 * @param string $start_date The start date.
	 *
	 * @return string
	 */
	private function get_start_date_column( string $start_date ) : string {
		return '<input class="edit-date-date-picker access-expiration" data-name="start-access-date" type="text" value="' . esc_attr( $start_date ) . '">';
	}

	/**
	 * Updates the learner expiry date from the input in 'Access Expiration' column in learner management.
	 *
	 * @access private
	 *
	 * @param bool $updated    Initial value from the filter.
	 * @param int  $post_id    The lesson or course id.
	 * @param int  $comment_id The comment id of the lesson or course progress.
	 *
	 * @return bool Whether an update happened or not.
	 */
	public function update_learner_expiry_date( bool $updated, int $post_id, int $comment_id ) : bool {
		$comment = get_comment( $comment_id );

		$post_type = get_post_type( $post_id );

		if ( 'course' === $post_type ) {
			$course_id = $post_id;
		} else {
			$course_id = Sensei()->lesson->get_course_id( $post_id );
		}

		if ( false === $course_id || empty( $comment ) ) {
			return $updated;
		}

		// phpcs:disable WordPress.Security.NonceVerification.Missing -- Nonce is checked in main Sensei.
		if ( ! empty( $_POST['data']['new_dates']['expiration-date'] ) ) {
			$date_string = sanitize_text_field( wp_unslash( $_POST['data']['new_dates']['expiration-date'] ) );
		}

		// phpcs:enable
		if ( empty( $date_string ) ) {
			$result = $this->remove_user_expiry( $course_id, $comment->user_id );
			return true === $result ? true : $updated;
		}

		$date = DateTimeImmutable::createFromFormat( 'Y-m-d', $date_string, wp_timezone() );

		$input_expiration = $date->setTime( 23, 59, 59 )->getTimestamp();

		// If the expiration date didn't change don't update it.
		$current_expiration = $this->get_user_expiration_timestamp( $comment->user_id, $course_id );
		if ( $current_expiration === $input_expiration ) {
			return $updated;
		}

		if ( false === $date ) {
			return $updated;
		}

		$current_expiration = $this->get_user_expiration_timestamp( $comment->user_id, $course_id );

		if ( $input_expiration === $current_expiration ) {
			return $updated;
		}

		$this->remove_user_expiry( $course_id, $comment->user_id );
		$current_timestamp = current_datetime()->getTimestamp();

		if ( $current_timestamp < $input_expiration ) {
			$meta_key = self::EXPIRATION_TIMESTAMP_COURSE_META_PREFIX . $comment->user_id;
		} else {
			$meta_key = self::EXPIRED_TIMESTAMP_COURSE_META_PREFIX . $comment->user_id;
		}

		update_post_meta(
			$course_id,
			$meta_key,
			$input_expiration
		);

		return true;
	}

	/**
	 * Updates the student access period start date from the input in 'Access Start' column in student management.
	 *
	 * @access private
	 *
	 * @param bool $updated    Initial value from the filter.
	 * @param int  $post_id    The lesson or course id.
	 * @param int  $comment_id The comment id of the lesson or course progress.
	 *
	 * @return bool Whether an update happened or not.
	 */
	public function update_learner_start_date( bool $updated, int $post_id, int $comment_id ) : bool {
		$post_type = get_post_type( $post_id );

		if ( 'course' === $post_type ) {
			$course_id = $post_id;
		} else {
			$course_id = Sensei()->lesson->get_course_id( $post_id );
		}

		$comment = get_comment( $comment_id );

		if ( false === $course_id || empty( $comment ) ) {
			return $updated;
		}

		// phpcs:disable WordPress.Security.NonceVerification.Missing -- Nonce is checked in main Sensei.
		if ( ! empty( $_POST['data']['new_dates']['start-access-date'] ) ) {
			$date_string = sanitize_text_field( wp_unslash( $_POST['data']['new_dates']['start-access-date'] ) );
		}

		// phpcs:enable
		if ( empty( $date_string ) ) {
			$result = $this->remove_user_started( $course_id, $comment->user_id );

			return $result ? true : $updated;
		}

		$date = DateTimeImmutable::createFromFormat( 'Y-m-d', $date_string, wp_timezone() );

		if ( false === $date ) {
			return $updated;
		}

		$input_start_date   = $date->setTime( 23, 59, 59 )->getTimestamp();
		$current_start_date = $this->get_user_start_timestamp( $comment->user_id, $course_id );

		if ( $input_start_date === $current_start_date ) {
			return $updated;
		}

		$this->remove_user_started( $course_id, $comment->user_id );

		$start_post_meta = self::START_TIMESTAMP_COURSE_META_PREFIX . $comment->user_id;

		update_post_meta(
			$course_id,
			$start_post_meta,
			$input_start_date
		);
		return true;
	}

	/**
	 * Adds the expiration message in the learner courses list.
	 *
	 * @access private
	 *
	 * @param int $course_id Course ID.
	 */
	public function add_learner_course_expiration_message( $course_id ) {
		$user_id = get_current_user_id();

		// Don't show access period expired message if not needed.
		$display_access_expired_message = $this->should_display_expiration_message( $course_id, $user_id );
		if ( ! $display_access_expired_message ) {
			return;
		}

		$days_remaining = $this->get_days_remaining( $user_id, $course_id ) ?? 0;

		list( $message, $type ) = $this->get_expiration_message(
			$user_id,
			$course_id,
			// translators: Placeholder is the expiration date.
			__( 'Expired on %s', 'sensei-pro' ),
			__( 'Expires today', 'sensei-pro' ),
			// translators: Placeholder is the number of days.
			_n( 'Expires in %d day', 'Expires in %d days', $days_remaining, 'sensei-pro' ),
			// translators: Placeholder is the date.
			__( 'Expires on %s', 'sensei-pro' )
		);

		if ( $message ) {
			echo wp_kses_post( "<div class=\"course-expiration-message course-expiration-message--{$type}\">{$message}</div>" );
		}
	}

	/**
	 * Adds the not started message in the student courses list.
	 *
	 * @access private
	 *
	 * @param int $course_id Course ID.
	 */
	public function add_learner_course_not_started_message( $course_id ) {
		$user_id = get_current_user_id();

		// Don't display start access period message if it shouldn't be displayed.
		$display_access_period_start_message = $this->should_display_start_access_message( $course_id, $user_id );
		if ( ! $display_access_period_start_message ) {
			return;
		}

		list( $message, $type ) = $this->get_not_started_message(
			$user_id,
			$course_id,
			// translators: Placeholder is the expiration date.
			__( 'Starts on %s', 'sensei-pro' )
		);

		if ( $message ) {
			echo wp_kses_post( "<div class=\"course-expiration-message course-expiration-message--{$type}\">{$message}</div>" );
		}
	}

	/**
	 * Get if the access period expired message should be shown to the user.
	 *
	 * @access private
	 *
	 * @param int $course_id Course ID.
	 * @param int $user_id User id.
	 * @return bool
	 */
	private function should_display_expiration_message( $course_id, $user_id ) {

		// If the start access period has not started and the expiration is NOT older than start access period, don't show the message.
		$is_access_not_started = $this->is_access_not_started( $user_id, (int) $course_id );
		if ( $is_access_not_started && ! $this->is_expiration_older_than_start_date( $course_id, $user_id ) ) {
			return false;
		}
		return true;
	}

	/**
	 * Get if the access period not started message should be shown to the user.
	 *
	 * @access private
	 *
	 * @param int $course_id Course ID.
	 * @param int $user_id User id.
	 * @return bool
	 */
	private function should_display_start_access_message( $course_id, $user_id ) {
		$is_access_not_started = $this->is_access_not_started( $user_id, (int) $course_id );

		// If access has started don't show access period not started message.
		if ( ! $is_access_not_started ) {
			return false;
		}

		// If access expired and expired date is older than start access date, don't show start access not started message.
		$is_access_expired = $this->is_access_expired( $user_id, $course_id );
		if ( $is_access_expired && $this->is_expiration_older_than_start_date( $course_id, $user_id ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Check if the expiration timestamp is older than start date timestamp.
	 *
	 * @access private
	 *
	 * @param int $course_id Course ID.
	 * @param int $user_id User id.
	 * @return bool
	 */
	private function is_expiration_older_than_start_date( $course_id, $user_id ): bool {
		$start_date_timestamp = $this->get_user_start_timestamp( $user_id, $course_id );
		$expired_timestamp    = $this->get_user_expiration_timestamp( $user_id, $course_id );
		return $expired_timestamp < $start_date_timestamp;
	}

	/**
	 * Hides the signup notices when access is expired.
	 *
	 * @access private
	 *
	 * @param bool   $default_value Default value of the filter.
	 * @param string $course_id     Course ID.
	 *
	 * @returns bool False if the notice shouldn't be displayed.
	 */
	public function hide_signup_notice( $default_value, $course_id ) {
		$user_id = get_current_user_id();

		if ( 0 === $user_id ) {
			return $default_value;
		}
		if ( $this->is_access_expired_or_not_started( $user_id, (int) $course_id ) ) {
			return false;
		}

		return $default_value;
	}

	/**
	 * Hides the signup notice on the quiz page when access is expired.
	 *
	 * @access private
	 *
	 * @param array $quiz_message_args Default value of the filter.
	 * @param int   $lesson_id         Lesson ID.
	 * @param int   $user_id           User ID.
	 *
	 * @returns array The quiz message arguments.
	 */
	public function update_quiz_signup_notice( $quiz_message_args, $lesson_id, $user_id ) : array {
		$course_id = Sensei()->lesson->get_course_id( $lesson_id );
		if ( empty( $course_id ) || empty( $user_id ) ) {
			return $quiz_message_args;
		}

		if ( $this->is_access_expired( $user_id, (int) $course_id ) ) {
			list( $message ) = $this->get_expiration_message(
				get_current_user_id(),
				$course_id,
				// translators: Placeholder is the expiration date.
				__( 'Your access expired on %s.', 'sensei-pro' )
			);
			$quiz_message_args['message']   = $message;
			$quiz_message_args['box_class'] = 'clock';
		}

		return $quiz_message_args;
	}
}
