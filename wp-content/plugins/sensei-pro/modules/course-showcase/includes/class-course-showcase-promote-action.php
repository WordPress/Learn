<?php
/**
 * File containing the Course_Showcase_Promote_Action class.
 *
 * @package sensei-pro
 * @since   1.12.0
 */

namespace Sensei_Pro\Course_Showcase;

use Sensei_WC_Paid_Courses\Courses;
use Sensei_WC_Paid_Courses\Dependency_Checker;
use SenseiLMS_Licensing\License_Manager;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class responsible for managing the course promotion action.
 *
 * Promoting a course will create a listing for it in the Course Showcase.
 *
 * @since 1.12.0
 */
class Course_Showcase_Promote_Action {
	/**
	 * The admin action name for promoting a course.
	 * Take into account that this is used in the related `admin_action_{$action}` hook.
	 */
	private const PROMOTE_COURSE_ACTION = 'promote_course';

	/**
	 * Singleton instance.
	 *
	 * @var Course_Showcase_Promote_Action
	 */
	private static $instance;

	/**
	 * The feature availability helper for Course Showcase.
	 *
	 * @var Course_Showcase_Feature_Availability
	 */
	private $feature_availability;

	/**
	 * The listing CPT.
	 *
	 * @var Course_Showcase_Listing
	 */
	private $listing_cpt;

	/**
	 * Constructor.
	 *
	 * @param Course_Showcase_Listing              $listing_cpt
	 * @param Course_Showcase_Feature_Availability $feature_availability
	 */
	private function __construct(
		Course_Showcase_Listing $listing_cpt,
		Course_Showcase_Feature_Availability $feature_availability
	) {
		$this->listing_cpt          = $listing_cpt;
		$this->feature_availability = $feature_availability;
	}

	/**
	 * Fetch an instance of the class.
	 *
	 * @return Course_Showcase_Promote_Action
	 */
	public static function instance(): Course_Showcase_Promote_Action {
		if ( ! self::$instance ) {
			self::$instance = new self(
				Course_Showcase_Listing::instance(),
				Course_Showcase_Feature_Availability::instance()
			);
		}

		return self::$instance;
	}

	/**
	 * Initializes the class.
	 */
	public static function init(): void {
		$instance = self::instance();
		add_filter( 'post_row_actions', [ $instance, 'add_promote_action_link' ], 10, 2 );
		add_action( 'admin_action_promote_course', [ $instance, 'promote_course_action' ] );
	}

	/**
	 * Adds link for promoting a course.
	 *
	 * @param array    $actions Default actions.
	 * @param \WP_Post $post    Current post.
	 *
	 * @return array Modified actions
	 */
	public function add_promote_action_link( $actions, $post ): array {
		if ( 'course' === $post->post_type ) {
			$course_id = $post->ID;
			if ( $this->is_course_valid_for_promote_link( $course_id ) ) {
				$text = __( 'Edit promotion', 'sensei-pro' );
				if ( is_null( $this->listing_cpt->get_listing( $course_id ) ) ) {
					$text = __( 'Promote', 'sensei-pro' );
				}
				$actions['promote'] = '<a href="' . $this->get_promote_url( $course_id ) . '" title="' . esc_attr( __( 'Promote this course in Sensei\'s Showcase', 'sensei-pro' ) ) . '">'
					. $text . '</a>';
			}
		}

		return $actions;
	}

	/**
	 * Generates the promote action URL.
	 *
	 * @param int $course_id The course ID (post ID).
	 *
	 * @return string Promote action URL.
	 */
	private function get_promote_url( int $course_id ): string {

		$bare_url = admin_url( 'admin.php?action=' . self::PROMOTE_COURSE_ACTION . '&course_id=' . $course_id );
		$url      = wp_nonce_url( $bare_url, self::PROMOTE_COURSE_ACTION );

		/**
		 * Allows customizing the URL for the course promotion action.
		 *
		 * @since  1.12.0
		 * @hook   sensei_promote_course_action_url
		 *
		 * @param  {string} $url The action url.
		 * @param  {string} $post_id The course ID.
		 *
		 * @return {string} The promote course action url.
		 */
		return apply_filters( 'sensei_promote_course_action_url', $url, $course_id );
	}

	/**
	 * Promote course action.
	 *
	 * This will optionally create a listing if it does not exist and redirect to its edit page.
	 *
	 * @return void
	 */
	public function promote_course_action(): void {
		check_admin_referer( self::PROMOTE_COURSE_ACTION );
		if ( ! isset( $_GET['course_id'] ) || ! is_numeric( $_GET['course_id'] ) ) {
			wp_die( esc_html( __( 'Please supply a valid course ID.', 'sensei-pro' ) ) );
		}
		$course_id = intval( $_GET['course_id'] );

		// If the course shouldn't have had the link, redirect to courses listing screen.
		if ( ! $this->is_course_valid_for_promote_link( $course_id ) ) {
			wp_safe_redirect( admin_url( 'edit.php?post_type=course' ) );
			exit;
		}

		// Get or create listing.
		$listing = $this->listing_cpt->get_listing( $course_id );
		if ( is_null( $listing ) ) {
			// Create listing if not found.
			$listing_id = $this->listing_cpt->create_listing(
				$this->calculate_metas_from_course( $course_id )
			);

			if ( is_wp_error( $listing_id ) ) {
				wp_die( esc_html( __( 'Could not create listing.', 'sensei-pro' ) ) );
			}
		} else {
			$listing_id = $listing->ID;
		}

		// Redirect to listing's edition page.
		wp_safe_redirect( admin_url( 'post.php?post=' . $listing_id . '&action=edit' ) );
		exit;
	}

	/**
	 * Check if a given course ID is valid in order to show the promote link.
	 *
	 * @param int $course_id The course ID.
	 *
	 * @return bool
	 */
	private function is_course_valid_for_promote_link( int $course_id ): bool {

		// Check feature is available.
		if ( ! $this->feature_availability->is_available() ) {
			return false;
		}

		// If course already has a listing, show the link.
		if ( ! is_null( $this->listing_cpt->get_listing( $course_id ) ) ) {
			return true;
		}

		$course = get_post( $course_id );
		// Only published courses are allowed to be promoted.
		if ( 'publish' !== $course->post_status ) {
			return false;
		}

		return $this->feature_availability->is_course_eligible( $course_id );
	}

	/**
	 * Calculates meta fields for a listing given a course ID.
	 *
	 * @param int $course_id
	 *
	 * @return array
	 */
	private function calculate_metas_from_course( int $course_id ): array {
		$is_paid = false;
		if ( Dependency_Checker::woocommerce_dependency_is_met() ) {
			$products_with_price = array_filter(
				Courses::get_course_products( $course_id ),
				function ( $product ) {
					return ! empty( $product->get_price() );
				}
			);
			$is_paid             = ! empty( $products_with_price );
		}
		$media     = [];
		$media_id  = get_post_thumbnail_id( $course_id );
		$media_url = get_the_post_thumbnail_url( $course_id, 'full' );
		if ( false !== $media_id && false !== $media_url ) {
			$media['id']  = $media_id;
			$media['src'] = $media_url;
		}
		return [
			'_course'   => $course_id,
			'_is_paid'  => $is_paid,
			'_title'    => get_the_title( $course_id ),
			'_excerpt'  => get_the_excerpt( $course_id ),
			'_category' => '',
			'_language' => get_locale(), // Get site locale by default.
			'_media'    => $media,
		];
	}
}
