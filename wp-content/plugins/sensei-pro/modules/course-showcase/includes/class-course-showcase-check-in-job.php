<?php
/**
 * File containing the Course_Showcase_Check_In_Job class.
 *
 * @package sensei-pro
 * @since   1.12.0
 */

namespace Sensei_Pro\Course_Showcase;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class responsible for managing the check-in jobs.
 *
 * @since 1.12.0
 */
class Course_Showcase_Check_In_Job {
	const CHECK_IN_JOB_HOOK = 'sensei_pro_course_showcase_check_in_job';

	/**
	 * Singleton instance.
	 *
	 * @var self
	 */
	private static $instance;

	/**
	 * The course showcase availability helper.
	 *
	 * @var Course_Showcase_Feature_Availability
	 */
	private $feature_availability;

	/**
	 * The course showcase listing CPT.
	 *
	 * @var Course_Showcase_Listing
	 */
	private $listing_cpt;

	/**
	 * Course_Showcase_Check_In_Job constructor.
	 *
	 * @param Course_Showcase_Feature_Availability $feature_availability The feature availability helper.
	 * @param Course_Showcase_Listing              $listing_cpt          The listing CPT.
	 */
	private function __construct(
		Course_Showcase_Feature_Availability $feature_availability,
		Course_Showcase_Listing $listing_cpt
	) {
		$this->feature_availability = $feature_availability;
		$this->listing_cpt          = $listing_cpt;
	}

	/**
	 * Fetch an instance of the class.
	 *
	 * @return self
	 */
	public static function instance(): self {
		if ( ! self::$instance ) {
			self::$instance = new self(
				Course_Showcase_Feature_Availability::instance(),
				Course_Showcase_Listing::instance()
			);
		}

		return self::$instance;
	}

	/**
	 * Register the hooks.
	 */
	public static function init() {
		$instance = self::instance();
		add_action( 'sensei_pro_course_showcase_module_setup', [ $instance, 'ensure_job_check_in' ], 100 );
		add_action( 'sensei_pro_course_showcase_schedule_check_ins', [ $instance, 'schedule_check_ins' ] );
		add_action( self::CHECK_IN_JOB_HOOK, [ $instance, 'check_in' ] );
		add_action( 'sensei_pro_course_showcase_submitted', [ $instance, 'schedule_check_in' ], 10, 2 );
	}

	/**
	 * Ensure that the job is scheduled.
	 */
	public function ensure_job_check_in(): void {
		if ( ! wp_next_scheduled( 'sensei_pro_course_showcase_schedule_check_ins' ) ) {
			wp_schedule_event( time(), 'daily', 'sensei_pro_course_showcase_schedule_check_ins' );
		}
	}

	/**
	 * Check to make sure each listing has a job scheduled.
	 */
	public function schedule_check_ins(): void {
		if ( ! $this->feature_availability->is_available() ) {
			return;
		}

		$listing_ids = get_posts(
			[
				'post_type'      => Course_Showcase_Listing::POST_TYPE,
				'post_status'    => 'pending',
				'posts_per_page' => -1,
				'fields'         => 'ids',
				'meta_query'     => [
					[
						'key'     => '_senseilmscom_response',
						'compare' => 'EXISTS',
					],
				],
			]
		);

		foreach ( $listing_ids as $listing_id ) {
			$args = [ $listing_id ];
			if ( ! wp_next_scheduled( self::CHECK_IN_JOB_HOOK, $args ) ) {
				// Vary the start time by up to an hour to avoid all jobs running at the same time.
				$start_time = time() + wp_rand( 0, 3600 );
				wp_schedule_event( $start_time, 'daily', self::CHECK_IN_JOB_HOOK, $args );
			}
		}
	}

	/**
	 * Schedule an individual listing check-in.
	 *
	 * @param int  $listing_id The listing ID.
	 * @param bool $is_update  Whether this is an update.
	 */
	public function schedule_check_in( int $listing_id, bool $is_update ): void {
		$args           = [ $listing_id ];
		$next_scheduled = wp_next_scheduled( self::CHECK_IN_JOB_HOOK, $args );

		if ( $next_scheduled && $is_update ) {
			// If this is an update, we don't need to schedule a new job.
			return;
		}

		// Schedule the event and unschedule any old events.
		if ( $next_scheduled ) {
			wp_unschedule_event( $next_scheduled, self::CHECK_IN_JOB_HOOK, $args );
		}

		wp_schedule_event( time(), 'daily', self::CHECK_IN_JOB_HOOK, $args );
	}

	/**
	 * Check in on a listing.
	 *
	 * @param int $listing_id The listing ID.
	 */
	public function check_in( $listing_id ): void {
		if (
			! $listing_id
			|| ! $this->listing_cpt->has_been_submitted( $listing_id )
		) {
			$args = [ $listing_id ];

			// Clean-up this invalid or old job.
			$next_scheduled = wp_next_scheduled( self::CHECK_IN_JOB_HOOK, $args );
			if ( $next_scheduled ) {
				wp_unschedule_event( $next_scheduled, self::CHECK_IN_JOB_HOOK, $args );
			}
			return;
		}

		// Check to make sure the associating course is still published.
		$course_id = get_post_meta( $listing_id, '_course', true );
		$course    = get_post( $course_id );
		if (
			! $course
			|| 'publish' !== $course->post_status
			|| 'course' !== $course->post_type
		) {
			return;
		}

		/**
		 * Check in on the listing. Ignore the response as the errors will eventually user-facing errors will
		 * show up from the status check the next time the user visits the showcase listing CPT list.
		 */
		$this->listing_cpt->listing_check_in( $listing_id );
	}
}
