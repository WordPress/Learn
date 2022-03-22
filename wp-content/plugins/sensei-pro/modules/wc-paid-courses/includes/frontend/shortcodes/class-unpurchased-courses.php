<?php
/**
 * File containing the class \Sensei_WC_Paid_Courses\Frontend\Shortcodes\Unpurchased_Courses.
 *
 * @package sensei-wc-paid-courses
 * @since   1.0.0
 */

namespace Sensei_WC_Paid_Courses\Frontend\Shortcodes;

use Sensei;
use Sensei_Shortcode_Interface;
use Sensei_Templates;
use Sensei_WC;
use Sensei_WC_Paid_Courses\Course_Enrolment_Providers;
use WP_Query;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Renders the `[sensei_unpurchased_courses]` shortcode when a user is logged in. If the user is not logged in
 * it will show all courses.
 *
 * @class \Sensei_WC_Paid_Courses\Frontend\Shortcodes\Unpurchased_Courses
 * @since 1.0.0
 */
class Unpurchased_Courses implements Sensei_Shortcode_Interface {

	/**
	 * Help setup the query needed by the render method.
	 *
	 * @var \WP_Query
	 */
	protected $query;

	/**
	 * Number of items to show on the current page (Default: `all`).
	 *
	 * @var string|int
	 */
	protected $number;

	/**
	 * Value of `orderby` argument in query (Default: `date`).
	 *
	 * @var string
	 */
	protected $orderby;

	/**
	 * Value of `order` argument in query (`ASC` or `DESC`; Default: `DESC`).
	 *
	 * @var string
	 */
	protected $order;

	/**
	 * Setup the shortcode object.
	 *
	 * @since 1.0.0
	 *
	 * @param array  $attributes Attributes called with the shortcode.
	 * @param string $content    Content of the post.
	 * @param string $shortcode  Shortcode that was called for this instance.
	 */
	public function __construct( $attributes, $content, $shortcode ) {

		// Set up all argument need for constructing the course query.
		$this->number  = isset( $attributes['number'] ) ? $attributes['number'] : '10';
		$this->orderby = isset( $attributes['orderby'] ) ? $attributes['orderby'] : 'title';

		if ( 'menu_order' === $this->orderby && ! isset( $attributes['order'] ) ) {
			// set the default for menu_order to be ASC.
			$this->order = 'ASC';
		} else {
			// For everything else use the value passed or the default DESC.
			$this->order = isset( $attributes['order'] ) ? $attributes['order'] : 'DESC';
		}

		// Setup the course query that will be used when rendering.
		$this->setup_course_query();
	}

	/**
	 * Sets up the object course query
	 * that will be used int he render method.
	 *
	 * @since Sensei 1.9.0
	 */
	protected function setup_course_query() {

		// Course query parameters to be used for all courses.
		$query_args = [
			'post_type'      => 'course',
			'post_status'    => 'publish',
			// The number specified by the user will be used later in this function.
			'posts_per_page' => -1,
			'orderby'        => $this->orderby,
			'order'          => $this->order,
		];

		// Get all the courses that has a product attached.
		$all_courses_query = new WP_Query( $query_args );

		$paid_courses_not_taken = [];

		foreach ( $all_courses_query->posts as $course ) {
			// Check that this is a paid course.
			if ( ! Sensei_WC::is_course_purchasable( $course->ID, true ) ) {
				continue;
			}

			// Only include courses that the user is not already enrolled in.
			if ( ! Course_Enrolment_Providers::is_user_enrolled( $course->ID, get_current_user_id() ) ) {
				$paid_courses_not_taken[] = $course->ID;
			}
		}

		// Setup the course query again and only use the course the user has not purchased.
		// This query will be loaded into the global WP_Query in the render function.
		$query_args['post__in']       = $paid_courses_not_taken;
		$query_args['posts_per_page'] = $this->number;

		$this->query = new WP_Query( $query_args );

	}

	/**
	 * Rendering the shortcode this class is responsible for.
	 *
	 * @return string
	 */
	public function render() {
		global $wp_query;

		if ( ! is_user_logged_in() ) {
			$anchor_before = '<a href="' . esc_url( sensei_user_login_url() ) . '" >';
			$anchor_after  = '</a>';
			$notice        = sprintf(
				// translators: Placeholders are an opening and closing <a> tag linking to the login URL.
				__( 'You must be logged in to view the non-purchased courses. Click here to %1$slogin%2$s.', 'sensei-pro' ),
				$anchor_before,
				$anchor_after
			);

			Sensei()->notices->add_notice( $notice, 'info' );
			Sensei()->notices->maybe_print_notices();

			return '';
		}

		// Keep a reference to old query.
		$current_global_query = $wp_query;

		// Assign the query setup in $this-> setup_course_query.
		// phpcs:ignore WordPress.WP.GlobalVariablesOverride
		$wp_query = $this->query;

		ob_start();
		Sensei()->notices->maybe_print_notices();
		Sensei_Templates::get_template( 'loop-course.php' );
		$shortcode_output = ob_get_clean();

		// Restore old query.
		// phpcs:ignore WordPress.WP.GlobalVariablesOverride
		$wp_query = $current_global_query;

		return $shortcode_output;
	}
}
