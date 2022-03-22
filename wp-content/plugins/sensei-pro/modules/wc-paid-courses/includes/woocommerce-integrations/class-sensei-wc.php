<?php
/**
 * Sensei WooCommerce Integration
 *
 * @package sensei-wc-paid-courses
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Sensei_WC_Paid_Courses\Course_Enrolment_Providers;
use Sensei_WC_Paid_Courses\Course_Enrolment_Providers\WooCommerce_Subscriptions;
use Sensei_Pro_Course_Expiration\Course_Expiration;
use Sensei_WC_Paid_Courses\Courses;

// @phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedClassFound -- Legacy classname.

/**
 * Sensei WooCommerce class
 *
 * All functions needed to integrate Sensei and WooCommerce
 *
 * @package Access-Management
 * @author Automattic
 * @since Sensei 1.9.0
 */
class Sensei_WC {

	/**
	 * Load the files needed for the woocommerce integration.
	 *
	 * @since Sensei 1.9.0
	 */
	public static function load_woocommerce_integration_hooks() {

		if ( ! self::is_woocommerce_active() ) {
			return;
		}

		$woocommerce_hooks_file_path = dirname( __FILE__ ) . '/hooks.php';
		require_once $woocommerce_hooks_file_path;

	}
	/**
	 * Check if WooCommerce plugin is loaded and allowed by Sensei.
	 *
	 * @since Sensei 1.9.0
	 * @return bool
	 */
	public static function is_woocommerce_active() {
		return self::is_woocommerce_present();
	}

	/**
	 * Checks if the WooCommerce plugin is installed and activation.
	 *
	 * If you need to check if WooCommerce is activated use Sensei_Utils::is_woocommerce_active().
	 * This function does nott check to see if the Sensei setting for WooCommerce is enabled.
	 *
	 * @since Sensei 1.9.0
	 *
	 * @return bool
	 */
	public static function is_woocommerce_present() {
		return Sensei_Utils::is_plugin_present_and_activated( 'Woocommerce', 'woocommerce/woocommerce.php' );
	}

	/**
	 * Find the order active number (completed or processing ) for a given user on a course. It will return the latest order.
	 *
	 * If multiple exist we will return the latest order unless `$get_all` is `true`.
	 *
	 * @deprecated 2.0.0 Only used for legacy enrolment handling.
	 *
	 * @param int  $user_id               User ID.
	 * @param int  $course_id             Course ID.
	 * @param bool $check_parent_products Check Parent Products.
	 * @param bool $get_all               If `true`, return all order IDs found.
	 *
	 * @return int|bool|array The latest order ID or `false` if there aren't any. If `$get_all` is `true`, returns an array.
	 */
	public static function get_learner_course_active_order_id( $user_id, $course_id, $check_parent_products = false, $get_all = false ) {
		_deprecated_function( __METHOD__, '2.0.0' );

		$course_product_ids = self::get_course_product_ids( $course_id );
		$orders_query       = new WP_Query(
			[
				'post_type'      => 'shop_order',
				'posts_per_page' => -1,
				'post_status'    => [ 'wc-processing', 'wc-completed' ],
				'meta_key'       => '_customer_user',
				'meta_value'     => $user_id,
			]
		);

		if ( 0 === $orders_query->post_count ) {
			return $get_all ? [] : false;
		}

		$order_ids = [];
		foreach ( $orders_query->get_posts() as $order ) {
			$order = wc_get_order( $order->ID );

			if ( false === $order ) {
				continue;
			}

			$items = $order->get_items();

			foreach ( $items as $item ) {
				// If the product id on the order and the one given to this function
				// this order has been placed by the given user on the given course.
				$item_product_id   = Sensei_WC_Utils::get_item_id_from_item( $item );
				$parent_product_id = Sensei_WC_Utils::get_item_id_from_item( $item, true );

				if (
					in_array( $item_product_id, $course_product_ids, true ) ||
					$check_parent_products && in_array( $parent_product_id, $course_product_ids, true )
				) {

					$order_id = $order->get_id();
					if ( $get_all ) {
						$order_ids[] = $order_id;
					} else {
						return $order->get_id();
					}
				}
			}
		}

		return $get_all ? $order_ids : false;
	}

	/**
	 * Output WooCommerce specific course filters
	 * Removing the paged argument
	 *
	 * @since Sensei 1.9.0
	 * @param array $filter_links The incoming filter links.
	 * @return mixed
	 */
	public static function add_course_archive_wc_filter_links( $filter_links ) {

		$free_courses = self::get_free_courses();
		$paid_courses = self::get_paid_courses();

		if ( empty( $free_courses ) || empty( $paid_courses ) ) {
			// do not show any WooCommerce filters if all courses are
			// free or if all courses are paid.
			return $filter_links;

		}

		$filter_links[] = [
			'id'    => 'paid',
			'url'   => add_query_arg(
				[
					'course_filter' => 'paid',
				],
				Sensei_Course::get_courses_page_url()
			),
			'title' => __( 'Paid', 'sensei-pro' ),
		];

		$filter_links[] = [
			'id'    => 'free',
			'url'   => add_query_arg(
				[
					'course_filter' => 'free',
				],
				Sensei_Course::get_courses_page_url()
			),
			'title' => __( 'Free', 'sensei-pro' ),
		];

		return $filter_links;

	}

	/**
	 * Apply the free filter the the course query getting all course with no products or products with zero price.
	 *
	 * Hooked into `pre_get_posts`.
	 *
	 * @since Sensei 1.9.0
	 * @param WP_Query $query The WP_Query object to modify.
	 * @return WP_Query
	 */
	public static function course_archive_wc_filter_free( $query ) {

		// phpcs:ignore WordPress.Security.NonceVerification
		if ( isset( $_GET['course_filter'] ) && 'free' === $_GET['course_filter']
			&& 'course' === $query->get( 'post_type' ) && $query->is_main_query() ) {

			// Get the free course IDs.
			$free_course_ids = self::get_free_courses( [ 'fields' => 'ids' ] );

			if ( ! empty( $free_course_ids ) ) {
				// manipulate the query to return free courses.
				$query->set( 'post__in', $free_course_ids );
			} else {
				// Ensure the query returns nothing.
				$query->set( 'post__in', [ -1 ] );
			}
		}

		return $query;

	}

	/**
	 * Apply the paid filter to the course query on the courses page
	 * will include all course with a product attached with a price
	 * more than 0.
	 *
	 * Hooked into `pre_get_posts`.
	 *
	 * @since Sensei 1.9.0
	 * @param WP_Query $query The WP_Query to modify.
	 * @return WP_Query $query
	 */
	public static function course_archive_wc_filter_paid( $query ) {

		// phpcs:ignore WordPress.Security.NonceVerification
		if ( isset( $_GET['course_filter'] ) && 'paid' === $_GET['course_filter']
			&& 'course' === $query->get( 'post_type' ) && $query->is_main_query() ) {

			// Get the paid course IDs.
			$paid_course_ids = self::get_paid_courses( [ 'fields' => 'ids' ] );

			if ( ! empty( $paid_course_ids ) ) {
				// manipulate the query to return paid courses.
				$query->set( 'post__in', $paid_course_ids );
			} else {
				// Ensure the query returns nothing.
				$query->set( 'post__in', [ -1 ] );
			}
		}

		return $query;

	}

	/**
	 * Load the WooCommerce single product actions above
	 * single courses if woocommerce is active allowing purchase
	 * information and actions to be hooked from WooCommerce.
	 *
	 * Only triggers on single courses when there is a product associated with them.
	 * Sets the product global to the course product when empty.
	 */
	public static function do_single_course_wc_single_product_action() {
		global $wp_query, $product;

		if ( false === self::is_woocommerce_active() ) {
			return;
		}

		if ( empty( $wp_query ) || false === $wp_query->is_single() ) {
			return;
		}

		$course = $wp_query->get_queried_object();
		if ( empty( $course ) || 'course' !== $course->post_type ) {
			return;
		}

		$course_product_id = self::get_course_product_id( absint( $course->ID ) );

		if ( empty( $course_product_id ) ) {
			// no need to proceed, as no product is related to this course.
			return;
		}

		if ( empty( $product ) ) {
			// Product is not defined, set it to be the course product to mitigate fatals from wc hooks triggered
			// expecting it to be set.
			// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Overriding WooCommerce global.
			$product = wc_get_product( absint( $course_product_id ) );
		}

		// This hooks is documented within the WooCommerce plugin.
		// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- Using WooCommerce hook.
		do_action( 'woocommerce_before_single_product' );

	}

	/**
	 * Hooking into the single lesson page to alter the
	 * user access permissions based on if they have purchased the
	 * course the lesson belongs to.
	 *
	 * This function will only return false or the passed in user_access value.
	 * It doesn't return true in order to avoid altering other options.
	 *
	 * @since Sensei 1.9.0
	 *
	 * @deprecated 2.0.0 Only used for legacy enrolment handling.
	 *
	 * @param bool $can_user_view_lesson Whether the user can view this lesson.
	 * @param int  $lesson_id            The lesson ID.
	 * @param int  $user_id              The user ID.
	 * @return bool
	 */
	public static function alter_can_user_view_lesson( $can_user_view_lesson, $lesson_id, $user_id ) {
		_deprecated_function( __METHOD__, '2.0.0' );

		// do not override access to admins.
		$course_id = Sensei()->lesson->get_course_id( $lesson_id );

		if (
			sensei_all_access()
			|| Sensei_Utils::is_preview_lesson( $lesson_id )
			|| Course_Enrolment_Providers::is_user_enrolled( $course_id, $user_id )
		) {
			return $can_user_view_lesson;
		}

		$course_product_ids = self::get_course_product_ids( $course_id );

		if ( empty( $course_product_ids ) ) {
			return $can_user_view_lesson;
		}

		$order_id = self::get_learner_course_active_order_id( $user_id, $course_id );

		// product has a successful order so this user may access the content
		// this function may only return false or the default
		// returning true may override other negatives which we don't want.
		if ( ! $order_id ) {
			return false;
		}

		// return the passed in value.
		return $can_user_view_lesson;
	}

	/**
	 * Require login for paid courses.
	 *
	 * @since 2.1.1
	 *
	 * @hooked into sensei_is_login_required.
	 *
	 * @param boolean  $login_required Current filter vaue.
	 * @param int|null $course_id      Course ID.
	 *
	 * @return boolean Whether login is required to access course.
	 */
	public static function require_login_for_paid_courses( $login_required, $course_id ) {
		if (
			empty( $course_id )
			|| ! Course_Enrolment_Providers::instance()->handles_enrolment( $course_id )
		) {
			return $login_required;
		}

		return true;
	}

	/**
	 * Assign user to unassigned purchased courses.
	 *
	 * Note: this method seems to be dead code and not hooked to anything, best remove it.
	 *
	 * @param WP_Query $query The current query.
	 *
	 * @deprecated 1.1.0
	 */
	public static function assign_user_to_unassigned_purchased_courses( $query ) {
		_deprecated_function( __METHOD__, '1.1.0' );

		if ( is_admin() || false === self::is_woocommerce_active() || ! $query->is_main_query() ) {
			return;
		}

		$in_my_courses      = self::is_my_courses_page( $query );
		$in_learner_profile = isset( $query->query_vars ) && isset( $query->query_vars['learner_profile'] );

		if ( ! $in_learner_profile && ! $in_my_courses ) {
			return;
		}

		$user_id = $in_learner_profile ? self::user_id_from_query( $query ) : ( $in_my_courses ? self::current_user_id() : null );

		if ( ! $user_id ) {
			return;
		}

		remove_action( 'pre_get_posts', [ __CLASS__, __FUNCTION__ ] );

		self::start_purchased_courses_for_user( $user_id );
	}

	/**
	 * Detect whether this page is the My Courses page.
	 *
	 * @deprecated 2.0.0
	 * @param WP_Query $query The current query.
	 * @return bool
	 */
	public static function is_my_courses_page( $query ) {

		_deprecated_function( __METHOD__, '2.0.0' );

		if ( ! $query->is_page() ) {
			return false;
		}

		$queried_object = $query->get_queried_object();

		if ( ! $queried_object ) {
			return false;
		}

		$object_id       = absint( $queried_object->ID );
		$my_courses_page = Sensei()->settings->get( 'my_course_page' );
		if ( false === $my_courses_page ) {
			return false;
		}
		$my_courses_page_id = absint( $my_courses_page );

		if ( $object_id !== $my_courses_page_id ) {
			return false;
		}

		return true;
	}

	/**
	 * Get the user id from the given query.
	 *
	 * @param WP_Query $query The query.
	 * @return int
	 */
	private static function user_id_from_query( $query ) {
		$user = get_user_by( 'login', esc_html( $query->query_vars['learner_profile'] ) );
		if ( ! $user ) {
			return false;
		}
		return $user->ID;
	}

	/**
	 * Get the ID for the current user.
	 *
	 * @deprecated 2.0.0
	 * @return int|bool The user ID or false for the anonymous user.
	 */
	private static function current_user_id() {

		$current_user = wp_get_current_user();

		if ( ! ( $current_user instanceof WP_User ) || intval( $current_user->ID ) === 0 ) {
			// return in case of anonymous user or no user.
			return false;
		}

		return $current_user->ID;

	}

	/**
	 * Add course link to order thank you and details pages.
	 *
	 * @since  Sensei 1.4.5
	 * @access public
	 * @deprecated 2.3.0
	 *
	 * @return void
	 */
	public static function course_link_from_order() {
		_deprecated_function( __METHOD__, '2.3.0' );

		if ( ! is_order_received_page() ) {
			return;
		}

		$order_id = get_query_var( 'order-received' );
		$order    = wc_get_order( $order_id );

		$courses = self::get_order_courses( $order );

		if ( ! empty( $courses ) ) {
			self::generate_order_notice( $courses );
		}
	}

	/**
	 * Display courses section in order details.
	 *
	 * @param WC_Order $order The order.
	 */
	public static function order_details_display_courses( $order ) {
		$courses = self::get_order_courses( $order );

		if ( empty( $courses ) ) {
			return;
		}

		$course_links = [];

		foreach ( $courses as $course ) {
			$title           = $course->post_title;
			$permalink       = get_permalink( $course->ID );
			$course_links[] .= '<li><a href="' . esc_url( $permalink ) . '" >' . $title . '</a></li>';
		}

		echo wp_kses_post(
			'<section class="woocommerce-order-sensei-courses"><h2>' . esc_html__( 'Courses', 'sensei-pro' ) . '</h2>
			<ul>' . join( '', $course_links ) . '</ul></section>'
		);

	}

	/**
	 * Get courses provided by the products in an order.
	 *
	 * @param WC_Order $order The order.
	 *
	 * @return array|void
	 */
	private static function get_order_courses( WC_Order $order ) {

		if ( false === $order ) {
			return;
		}

		$status = Sensei_WC_Utils::get_order_status( $order );

		// exit early if not wc-completed or wc-processing.
		if ( ! in_array( $status, [ 'wc-completed', 'wc-processing' ], true ) ) {
			return;
		}

		$product_ids = [];

		foreach ( $order->get_items() as $item ) {
			$item_id = Sensei_WC_Utils::get_item_id_from_item( $item );

			$user_id = get_post_meta( $order->get_id(), '_customer_user', true );

			if ( $user_id ) {
				$product_ids[] = $item_id;
			}
		}

		$courses = [];

		if ( ! empty( $product_ids ) ) {

			$courses = Courses::get_product_courses( $product_ids );
		}

		return $courses;
	}

	/**
	 * Helper method to generate the order's information notice.
	 *
	 * @deprecated 2.3.0
	 * @param array $courses Courses in the order.
	 */
	private static function generate_order_notice( array $courses ) {
		_deprecated_function( __METHOD__, '2.3.0' );

		$course_links = [];

		// Generate the link elements.
		if ( $courses && count( $courses ) > 0 ) {

			foreach ( $courses as $course ) {

				$title           = $course->post_title;
				$permalink       = get_permalink( $course->ID );
				$course_links[] .= '<a href="' . esc_url( $permalink ) . '" >' . $title . '</a> ';

			}
		}

		// add the courses to the WooCommerce notice.
		if ( ! empty( $course_links ) ) {

			$courses_html = _nx(
				'You have purchased the following course:',
				'You have purchased the following courses:',
				count( $course_links ),
				'Purchase thank you note on Checkout page. The course link(s) will be shown',
				'sensei-pro'
			);

			$courses_html .= ' <ul>';

			foreach ( $course_links as $link ) {
				$courses_html .= '<li>' . $link . '</li>';
			}

			$courses_html .= ' </ul>';

			Sensei()->notices->add_notice( $courses_html, 'info' );

			// Ensure Sensei notices are output on this non-Sensei page.
			add_filter( 'the_content', [ 'Sensei_WC', 'prepend_sensei_notices' ] );
		}
	}

	/**
	 * Prepend Sensei notices to the content, within a wrapper. Used in
	 * `the_content` filter.
	 *
	 * @since 1.0.1
	 * @access private
	 *
	 * @param string $content The post content.
	 * @return string
	 */
	public static function prepend_sensei_notices( $content ) {
		ob_start();

		echo '<div class="sensei">';
		// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- Using Senseei core hook.
		do_action( 'sensei_frontend_messages' );
		echo '</div>';

		return ob_get_clean() . $content;
	}

	/**
	 * Show the message that a user should complete
	 * their purchase if the course is in the cart
	 *
	 * This should be used within the course loop or single course page
	 *
	 * @since Sensei 1.9.0
	 */
	public static function course_in_cart_message() {
		global $post;

		// Don't show the notice if the course has just been added to the cart.
		if ( isset( $_REQUEST['add-to-cart'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Safe use of input.
			return;
		}

		if ( self::is_course_in_cart( $post->ID ) ) { ?>

			<div class="sensei-message info">
				<?php
				$checkout_url = wc_get_checkout_url();
				$cart_link    = '<a class="cart-complete" href="' . esc_url( $checkout_url )
							. '" title="' . esc_attr__( 'complete purchase', 'sensei-pro' ) . '">'
							. esc_html__( 'complete the purchase', 'sensei-pro' ) . '</a>';

				// translators: Placeholder is a link to the cart.
				echo wp_kses_post( sprintf( __( 'You have already added this Course to your cart. Please %1$s to access the course.', 'sensei-pro' ), $cart_link ) );

				?>
			</div>
			<?php
		}

	}

	/**
	 * Checks the cart to see if a course is in the cart.
	 *
	 * @param int $course_id The Course ID.
	 * @return bool
	 */
	public static function is_course_in_cart( $course_id ) {
		$course_product_ids = self::get_course_product_ids( $course_id );
		$is_user_enrolled   = Course_Enrolment_Providers::is_user_enrolled( $course_id, get_current_user_id() );

		if ( ! empty( $course_product_ids ) && ! $is_user_enrolled ) {
			foreach ( $course_product_ids as $course_product_id ) {

				if ( self::is_product_in_cart( $course_product_id ) ) {
					return true;
				}
			}
		}

		return false;

	}

	/**
	 * Check the cart to see if the product is in the cart.
	 *
	 * @param int $product_id Product or product variation ID.
	 * @return bool
	 */
	public static function is_product_in_cart( $product_id ) {
		if ( ( false === Sensei_Utils::is_request( 'frontend' ) ) || ! WC()->cart ) {
			// WC Cart is not loaded when we are on Admin or doing a Cronjob.
			// see https://github.com/Automattic/sensei/issues/1622.
			return false;
		}

		if ( ! $product_id ) {
			return false;
		}

		$product = wc_get_product( $product_id );

		if ( ! is_object( $product ) ) {
			return false;
		}

		foreach ( WC()->cart->get_cart() as $cart_item ) {
			// Check if the product IDs match.
			$cart_product_id = $cart_item['product_id'];

			if ( $product_id === $cart_product_id ) {
				return true;
			}

			// The product ID could be for a variation.
			if ( isset( $cart_item['variation_id'] ) && ( 0 < $cart_item['variation_id'] ) ) {
				$cart_variation_id = $cart_item['variation_id'];

				if ( $product_id === $cart_variation_id ) {
					return true;
				}
			}
		}

		return false;
	}

	/**
	 * Add Courses menu item to My Account page.
	 *
	 * @access private
	 * @since  2.3.0
	 *
	 * @param array $items My Account menu items.
	 *
	 * @return array
	 */
	public static function add_my_account_courses_menu( $items ) {

		if ( empty( Sensei()->settings->get( 'my_course_page' ) ) ) {
			return $items;
		}

		/**
		 * Position of Courses item in the My Account menu.
		 *
		 * @since 2.3.0
		 * @hook sensei_wc_paid_courses_my_account_menu_courses_position
		 *
		 * @param {int} $position Menu position.
		 */
		$position = apply_filters( 'sensei_wc_paid_courses_my_account_menu_courses_position', 2 );

		$courses_item = [ 'courses' => __( 'Courses', 'sensei-pro' ) ];

		return array_slice( $items, 0, $position ) + $courses_item + array_slice( $items, $position );
	}

	/**
	 * Point the Courses item in My Account menu to the My Courses page.
	 *
	 * @hooked woocommerce_get_endpoint_url
	 * @since  2.3.0
	 *
	 * @param string $url       Endpoint URL.
	 * @param string $endpoint  Endpoint slug.
	 * @param string $value     Query param value.
	 * @param string $permalink Permalink.
	 *
	 * @return string
	 */
	public static function my_account_courses_menu_link( $url, $endpoint, $value, $permalink ) {

		if ( 'courses' === $endpoint && wc_get_page_permalink( 'myaccount' ) === $permalink ) {
			$my_courses_page = Sensei()->settings->get( 'my_course_page' );
			if ( ! empty( $my_courses_page ) ) {
				return get_permalink( intval( $my_courses_page ) );
			}
		}

		return $url;
	}

	/**
	 * Get query args for free products.
	 *
	 * @return array
	 */
	private static function get_free_products_query_args() {
		return [
			'post_type'      => 'product',
			'posts_per_page' => -1,
			'fields'         => 'ids',
			'meta_query'     => [
				'relation' => 'AND',
				[
					'relation' => 'OR',
					[
						'key'   => '_price',
						'value' => 0,
					],
					[
						'key'   => '_regular_price',
						'value' => 0,
					],
					[
						'key'   => '_sale_price',
						'value' => 0,
					],
				],
				[
					'relation' => 'OR',
					[
						'key'     => '_subscription_sign_up_fee',
						'value'   => [ 0, '' ],
						'compare' => 'IN',
					],
					[
						'key'     => '_subscription_sign_up_fee',
						'compare' => 'NOT EXISTS',
					],
				],
			],
		];
	}

	/**
	 * Get all free WooCommerce products.
	 *
	 * @since Sensei 1.9.0
	 *
	 * @return array $free_products{
	 *  @type int $wp_post_id
	 * }
	 */
	public static function get_free_product_ids() {
		$base_query_args = self::get_free_products_query_args();

		// Get products, variations, and variation parents.
		$free_product_ids                  = get_posts( $base_query_args );
		$free_product_variation_parent_ids = get_posts(
			array_merge(
				$base_query_args,
				[
					'post_type' => 'product_variation',
					'fields'    => 'id=>parent',
				]
			)
		);

		return array_merge(
			$free_product_ids,
			array_keys( $free_product_variation_parent_ids ),
			array_values( $free_product_variation_parent_ids )
		);

	}

	/**
	 * The meta query for courses that are free.
	 *
	 * @since Sensei 1.9.0
	 * @return array $wp_meta_query_param
	 */
	public static function get_free_courses_meta_query_args() {
		// To be removed in 3.0.0.
		_deprecated_function( __METHOD__, '1.1.0' );

		return [
			'relation' => 'OR',
			[
				'key'     => '_course_woocommerce_product',
				'compare' => 'NOT EXISTS',
			],
			[
				'key'     => '_course_woocommerce_product',
				'value'   => '-',
				'compare' => '=',
			],
			[
				'key'     => '_course_woocommerce_product',
				'value'   => self::get_paid_product_ids(),
				'compare' => 'NOT IN',
			],
		];

	}

	/**
	 * The metat query for courses that are free
	 *
	 * @since Sensei 1.9.0
	 * @return array $wp_query_meta_query_args_param
	 */
	public static function get_paid_courses_meta_query_args() {
		// To be removed in 3.0.0.
		_deprecated_function( __METHOD__, '1.1.0' );

		$paid_product_ids = self::get_paid_product_ids();

		return [
			[
				'key'     => '_course_woocommerce_product',
				// when empty we give a false post_id to ensure the caller doesn't get any courses for their
				// query.
				'value'   => empty( $paid_product_ids ) ? '-1000' : $paid_product_ids,
				'compare' => 'IN',
			],
		];

	}

	/**
	 * The WordPress Query args
	 * for paid products on sale
	 *
	 * @since Sensei 1.9.0
	 * @deprecated 1.0.0
	 *
	 * @return array $product_query_args
	 */
	public static function get_paid_products_on_sale_query_args() {
		_deprecated_function( __METHOD__, '1.0.0' );

		$args               = self::get_paid_products_query_args();
		$args['meta_query'] = [
			'relation' => 'AND',
			[
				'key'     => '_regular_price',
				'compare' => '>',
				'value'   => 0,
			],
			[
				'key'     => '_sale_price',
				'compare' => '>',
				'value'   => 0,
			],
		];

		return $args;

	} // get_paid_products_on_sale_query_args

	/**
	 * Return the WordPress query args for
	 * products not on sale but that is not a free
	 *
	 * @since Sensei 1.9.0
	 * @deprecated 1.0.0  This method may produce an unreliable query with WooCommerce 3.6.0+.
	 *
	 * @return array
	 */
	public static function get_paid_products_not_on_sale_query_args() {
		_deprecated_function( __METHOD__, '1.0.0' );

		$args               = self::get_paid_products_query_args();
		$args['meta_query'] = [
			'relation' => 'AND',
			[
				'key'     => '_regular_price',
				'compare' => '>',
				'value'   => 0,
			],
			[
				'key'     => '_sale_price',
				'compare' => '=',
				'value'   => '',
			],
		];

		return $args;

	} // get_paid_courses_meta_query

	/**
	 * Get query args for paid products.
	 *
	 * @return array
	 */
	private static function get_paid_products_query_args() {
		return [
			'post_type'      => 'product',
			'posts_per_page' => -1,
			'fields'         => 'ids',
			'meta_query'     => [
				'relation' => 'OR',
				[
					'key'     => '_price',
					'compare' => '>',
					'value'   => 0,
				],
				[
					'key'     => '_subscription_sign_up_fee',
					'value'   => 0,
					'compare' => '>',
				],
			],
		];
	}

	/**
	 * Get all WooCommerce non-free product id's
	 *
	 * @since Sensei 1.9.0
	 *
	 * @return array $woocommerce_paid_product_ids
	 */
	public static function get_paid_product_ids() {
		$base_query_args = self::get_paid_products_query_args();

		// Get products, variations, and variation parents.
		$paid_product_ids                  = get_posts( $base_query_args );
		$paid_product_variation_parent_ids = get_posts(
			array_merge(
				$base_query_args,
				[
					'post_type' => 'product_variation',
					'fields'    => 'id=>parent',
				]
			)
		);

		return array_merge(
			$paid_product_ids,
			array_keys( $paid_product_variation_parent_ids ),
			array_values( $paid_product_variation_parent_ids )
		);

	}

	/**
	 * Detect whether WC Subscriptions is active.
	 *
	 * @deprecated 2.0.0
	 *
	 * @return bool
	 */
	public static function is_wc_subscriptions_active() {
		_deprecated_function( __METHOD__, '2.0.0' );

		return Sensei_WC_Subscriptions::is_wc_subscriptions_active();
	}

	/**
	 * Get IDs of courses attached to a product whose ID is in the given array.
	 *
	 * @access private
	 * @since 1.1.0
	 *
	 * @param array $product_ids The array of product IDs.
	 * @return array
	 */
	private static function get_course_ids_with_product( $product_ids ) {
		return wp_list_pluck( Courses::get_product_courses( $product_ids ), 'ID' );
	}

	/**
	 * Get IDs of courses that are attached to at least one free product.
	 *
	 * @access private
	 * @since 1.1.0
	 *
	 * @return array
	 */
	private static function get_course_ids_with_free_product() {
		return self::get_course_ids_with_product( self::get_free_product_ids() );
	}

	/**
	 * Get IDs of courses that are attached to at least one paid product.
	 *
	 * @access private
	 * @since 1.1.0
	 *
	 * @return array
	 */
	private static function get_course_ids_with_paid_product() {
		return self::get_course_ids_with_product( self::get_paid_product_ids() );
	}

	/**
	 * Get IDs of all paid courses. Paid courses are defined as any course that
	 * is attached to a paid product and not attached to any free product.
	 *
	 * @return array
	 */
	private static function get_only_paid_course_ids() {
		return array_diff(
			self::get_course_ids_with_paid_product(),
			self::get_course_ids_with_free_product()
		);
	}

	/**
	 * Get all free courses. This includes courses with at least one free
	 * product attached, and courses with no products attached.
	 *
	 * @since Sensei 1.9.0
	 *
	 * @param array $args The additional query args.
	 * @return array
	 */
	public static function get_free_courses( $args = [] ) {

		$free_course_query_args = Sensei_Course::get_default_query_args();
		$only_paid_course_ids   = self::get_only_paid_course_ids();

		if ( ! empty( $only_paid_course_ids ) ) {
			$free_course_query_args['post__not_in'] = $only_paid_course_ids;
		}

		if ( ! empty( $args ) ) {
			$free_course_query_args = wp_parse_args( $args, $free_course_query_args );
		}

		return get_posts( $free_course_query_args );

	}

	/**
	 * Get all paid courses. This includes courses with at least one paid
	 * product attached.
	 *
	 * @since Sensei 1.9.0
	 * @param array $args override default arg values.
	 *
	 * @return array
	 */
	public static function get_paid_courses( $args = [] ) {

		$paid_course_query_args = Sensei_Course::get_default_query_args();
		$paid_course_ids        = self::get_course_ids_with_paid_product();

		if ( empty( $paid_course_ids ) ) {
			return [];
		} else {
			$paid_course_query_args['post__in'] = $paid_course_ids;
		}

		if ( ! empty( $args ) ) {
			$paid_course_query_args = wp_parse_args( $args, $paid_course_query_args );
		}

		return get_posts( $paid_course_query_args );
	}

	/**
	 * The text for the Purchase Course button.
	 *
	 * @param WC_Product $product The product to be purchased.
	 * @param string     $default_text Optional. Default button text. Default null.
	 * @return string
	 */
	public static function purchase_course_default_button_text( $product, $default_text = null ) {
		if ( $default_text ) {
			return $default_text;
		}

		$price           = $product->get_price();
		$product_is_free = wc_price( $price ) === wc_price( 0 );

		if ( $product_is_free ) {
			$button_text = __( 'Register for this Course', 'sensei-pro' );
		} else {
			$button_text = __( 'Purchase this Course', 'sensei-pro' );
		}

		return $product->get_price_html() . ' - ' . $button_text;
	}

	/**
	 * Get the purchasable products for a particular course.
	 *
	 * A product is considered to be purchasable if:
	 * The product is not already in the cart.
	 * The product is considered purchasable by WooCommerce.
	 * The product is in stock.
	 *
	 * @since 1.1.0
	 *
	 * @param int $course_id The course ID.
	 * @return array An array of products that can be purchased by the user.
	 */
	public static function get_purchasable_products( $course_id ) {
		$purchasable_products = [];

		// Course prerequisite not met or course is already in cart.
		if ( ! Sensei_Course::is_prerequisite_complete( $course_id ) || self::is_course_in_cart( $course_id ) ) {
			return $purchasable_products;
		}

		$product_ids = self::get_course_product_ids( $course_id );

		if ( ! $product_ids ) {
			return $purchasable_products;
		}

		foreach ( $product_ids as $product_id ) {
			$product = self::get_product_object( $product_id );

			if ( ! ( $product instanceof \WC_Product ) ) {
				continue;
			}

			// User has already added this product to their cart.
			if ( self::is_product_in_cart( $product_id ) ) {
				continue;
			}

			// Product is purchasable and in stock. Still need to check the rest of the products
			// as one of them could already have been purchased or added to the cart.
			if ( $product->is_purchasable() && $product->is_in_stock() ) {
				$purchasable_products[] = $product;
			}
		}

		return $purchasable_products;
	}

	/**
	 * Show purchase button for a particular course.
	 *
	 * @since Sensei 1.9.0
	 * @param int                   $course_id Course ID.
	 * @param WC_Product|null|false $product Optional. Product to purchase. Default null.
	 * @param string                $default_text Optional. Default button text. Default null.
	 * @return string Empty string.
	 */
	public static function the_add_to_cart_button_html( $course_id, $product = null, $default_text = null ) {
		// $product will be null when there is a single product attached to the course.
		if ( ! $product ) {
			$product_id = self::get_course_product_id( $course_id );
			$product    = self::get_product_object( $product_id );

			if ( ! ( $product instanceof \WC_Product ) ) {
				return '';
			}
		}
		?>

		<form action="<?php echo esc_url( $product->add_to_cart_url() ); ?>"
			class="cart"
			method="post"
			enctype="multipart/form-data">

			<input type="hidden" name="product_id" value="<?php echo esc_attr( Sensei_WC_Utils::get_product_id( $product ) ); ?>" />

			<input type="hidden" name="quantity" value="1" />

			<?php
			if ( $product->is_type( 'variation' ) ) {
				$variation_data = Sensei_WC_Utils::get_variation_data( $product );
				?>

				<input type="hidden" name="variation_id" value="<?php echo esc_attr( Sensei_WC_Utils::get_product_variation_id( $product ) ); ?>" />
				<?php if ( is_array( $variation_data ) && count( $variation_data ) > 0 ) { ?>

					<?php foreach ( $variation_data as $att => $val ) { ?>

						<input type="hidden" name="<?php echo esc_attr( $att ); ?>" id="<?php echo esc_attr( str_replace( 'attribute_', '', $att ) ); ?>" value="<?php echo esc_attr( $val ); ?>" />

					<?php } ?>

				<?php } ?>

			<?php } ?>

			<button type="submit" class="single_add_to_cart_button button alt">
				<?php $button_text = self::purchase_course_default_button_text( $product, $default_text ); ?>

				<?php
				/**
				 * Filter Add to Cart button text.
				 *
				 * @since 1.0.0
				 *
				 * @param string $button_text Text to use for Add to Cart button.
				 */
				echo wp_kses_post( apply_filters( 'sensei_wc_paid_courses_add_to_cart_button_text', $button_text ) );
				?>
			</button>

		</form>

		<?php

		return '';
	}

	/**
	 * Alter the no permissions message on the single course page
	 * Changes the message to a WooCommerce specific message.
	 *
	 * @since Sensei 1.9.0
	 *
	 * @param string $message The message to alter.
	 * @param int    $post_id The ID of the course.
	 *
	 * @return string $message
	 */
	public static function alter_no_permissions_message( $message, $post_id ) {

		if ( empty( $post_id ) || 'course' !== get_post_type( $post_id ) ) {
			return $message;
		}

		$product_ids = self::get_course_product_ids( $post_id );

		if ( empty( $product_ids ) ) {

			return $message;

		}

		$no_course_product_purchases = true;

		foreach ( $product_ids as $product_id ) {

			if ( self::has_customer_bought_product( get_current_user_id(), $product_id ) ) {

				$no_course_product_purchases = false;
				break;

			}
		}

		if ( $no_course_product_purchases ) {

			return $message;

		}

		ob_start();
		self::the_course_no_permissions_message( $post_id );
		$woocommerce_course_no_permissions_message = ob_get_clean();

		return $woocommerce_course_no_permissions_message;

	}

	/**
	 * Show the no permissions message when a user is logged in
	 * and have not yet purchased the current course
	 *
	 * @param int $course_id The ID of the current course.
	 *
	 * @since Sensei 1.9.0
	 */
	public static function the_course_no_permissions_message( $course_id ) {

		// login link.
		$my_courses_page_id = intval( Sensei()->settings->settings['my_course_page'] );
		$login_link         = '<a href="' . esc_url( get_permalink( $my_courses_page_id ) ) . '">' .
			esc_html__( 'log in', 'sensei-pro' ) . '</a>';

		$course_product_ids         = self::get_course_product_ids( $course_id );
		$any_course_product_in_cart = false;

		foreach ( $course_product_ids as $course_product_id ) {
			if ( self::is_product_in_cart( $course_product_id ) ) {
				$any_course_product_in_cart = true;
				break;
			}
		}

		if ( $any_course_product_in_cart ) {

			$cart_link = '<a href="' . esc_url( wc_get_checkout_url() ) . '" title="' .
				esc_attr__( 'Checkout', 'sensei-pro' ) . '">' . esc_html__( 'checkout', 'sensei-pro' ) .
				'</a>';

			// translators: Placeholder is a link to the cart.
			$message = sprintf( __( 'This course is already in your cart, please proceed to %1$s, to gain access.', 'sensei-pro' ), $cart_link );
			?>
			<span class="add-to-cart-login">
					<?php echo wp_kses_post( $message ); ?>
				</span>

			<?php

		} elseif ( is_user_logged_in() ) {

			?>
			<style>
				.sensei-message.alert {
					display: none;
				}
			</style>

			<?php

		} else {
			// translators: Placeholder is a link to log in.
			$message = sprintf( __( 'Or %1$s to access your purchased courses', 'sensei-pro' ), $login_link );
			?>
				<span class="add-to-cart-login">
					<?php echo wp_kses_post( $message ); ?>
				</span>

			<?php
		}
	}

	/**
	 * Checks if a user has bought a product item.
	 *
	 * @since  Sensei 1.9.0
	 *
	 * @param int $user_id    The user ID.
	 * @param int $product_id The product ID.
	 *
	 * @return bool
	 */
	public static function has_customer_bought_product( $user_id, $product_id ) {

		$product = wc_get_product( $product_id );

		if ( ! ( $product instanceof \WC_Product ) ) {
			return false;
		}

		// get variations parent.
		if ( $product->is_type( 'variation' ) ) {
			$product_id = Sensei_WC_Utils::get_product_id( $product );
		}

		$orders = self::get_user_product_orders( $user_id, $product_id );

		foreach ( $orders as $order_id ) {

			$order = wc_get_order( $order_id->ID );
			if ( false === $order ) {
				continue;
			}

			// wc-active is the subscriptions complete status.
			$status = 'wc-' . $order->get_status();
			if ( ! in_array( $status, [ 'wc-processing', 'wc-completed' ], true )
				|| ! ( 0 < count( $order->get_items() ) ) ) {

				continue;

			}

			foreach ( $order->get_items() as $item ) {

				// Check if user has bought product.
				if ( Sensei_WC_Utils::has_user_bought_product( $product_id, $item ) ) {

					// Check if user has an active subscription for product.
					if ( function_exists( 'wcs_user_has_subscription' ) && function_exists( 'wcs_get_subscription' ) ) {
						$user_bought_subscription_but_cancelled = wcs_user_has_subscription( $user_id, $product_id, 'cancelled' );
						if ( $user_bought_subscription_but_cancelled ) {
							// assume the user was refunded, so technically it is ok to display a buy product.
							return false;
						}
						$sub_key = wcs_get_subscription( $order );
						if ( $sub_key ) {
							$sub = wcs_get_subscription( $sub_key );
							if ( $sub && isset( $sub['status'] ) ) {
								if ( 'active' === $sub['status'] ) {
									return true;
								} else {
									return false;
								}
							}
						}
					}

					// Customer has bought product.
					return true;
				}
			}
		}

		// default is no order.
		return false;

	}

	/**
	 * Return the product id for the given course.
	 *
	 * @since Sensei 1.9.0
	 * @deprecated 1.1.0
	 *
	 * @param int  $course_id The ID of the course.
	 * @param bool $include_memberships Optional. Whether to include course memberships. Default true.
	 *
	 * @return string|bool $woocommerce_product_id or false if none exist
	 */
	public static function get_course_product_id( $course_id, $include_memberships = true ) {
		$product_ids = self::get_course_product_ids( $course_id, $include_memberships );

		if ( ! $product_ids || ! isset( $product_ids[0] ) ) {
			return false;
		}

		return $product_ids[0];
	}

	/**
	 * Return the product ids for the given course.
	 *
	 * @since 1.1.0
	 * @todo Deprecate this in favor of simple \Sensei_WC_Paid_Courses\Courses::get_course_products.
	 *
	 * @param int  $course_id The ID of the course.
	 * @param bool $include_memberships Optional. Whether to include course memberships. Default true.
	 *
	 * @return int[] Array of product IDs.
	 */
	public static function get_course_product_ids( $course_id, $include_memberships = true ) {
		$product_ids     = [];
		$product_ids_raw = [];

		// Get products attached to the course and products in a course membership.
		if ( $course_id ) {
			if ( $include_memberships ) {
				$product_ids_raw = array_unique(
					array_merge(
						get_post_meta( $course_id, '_course_woocommerce_product', false ),
						Sensei_WC_Memberships::get_course_membership_product_ids( $course_id )
					)
				);
			} else { // Just get products attached to the course.
				$product_ids_raw = get_post_meta( $course_id, '_course_woocommerce_product', false );
			}
		}

		if ( ! empty( $product_ids_raw ) ) {
			// Verify products still exist and get variation product ID if applicable.
			foreach ( $product_ids_raw as $product_id ) {
				$product = wc_get_product( $product_id );

				if ( ! ( $product instanceof \WC_Product ) ) {
					continue;
				}

				if ( $product->is_type( 'variation' ) ) {
					$product_ids[] = Sensei_WC_Utils::get_product_variation_id( $product );
				} else {
					$product_ids[] = Sensei_WC_Utils::get_product_id( $product );
				}
			}
		}

		/**
		 * Filter the product IDs for a course.
		 *
		 * @since 1.1.0
		 *
		 * @param int[] $product_ids List of product IDs associated with a course.
		 * @param int   $course_id   Course ID.
		 */
		$product_ids = apply_filters( 'sensei_wc_paid_courses_course_product_ids', array_unique( $product_ids ), $course_id );

		return $product_ids;
	}

	/**
	 * Adds woocommerce to the body class.
	 *
	 * @param array $classes The classes to alter.
	 * @return array
	 */
	public static function add_woocommerce_body_class( $classes ) {
		if ( ! in_array( 'woocommerce', $classes, true ) && is_singular( 'course' ) ) {
			$classes[] = 'woocommerce';
		}

		return $classes;
	}

	/**
	 * Responds to when a subscription product is purchased
	 *
	 * @deprecated 2.0.0
	 *
	 * @since Sensei 1.2.0
	 * @since Sensei 1.9.0 move to class Sensei_WC
	 *
	 * @param WC_Order $order The WooCommerce order.
	 *
	 * @return void
	 */
	public static function activate_subscription( $order ) {
		_deprecated_function( __METHOD__, '2.0.0' );

		Sensei_WC_Subscriptions::activate_subscription( $order );
	}

	/**
	 * Adds detail to to the WooCommerce order
	 *
	 * @since Sensei 1.4.5
	 * @since Sensei 1.9.0 function moved to class Sensei_WC and renamed from sensei_woocommerce_email_course_details to email_course_details
	 *
	 * @param WC_Order $order The WooCommerce order.
	 *
	 * @return void
	 */
	public static function email_course_details( $order ) {

		$post_status = Sensei_WC_Utils::get_order_status( $order );

		// exit early if not wc-completed or wc-processing.
		if ( 'wc-completed' !== $post_status
			&& 'wc-processing' !== $post_status ) {
			return;
		}

		$order_items = $order->get_items();
		$order_id    = $order->get_id();
		$user_id     = get_post_meta( $order_id, '_customer_user', true );

		if ( ! $user_id ) {
			return;
		}

		// If object have items go through them all to find course.
		if ( 0 < count( $order_items ) ) {

			$course_details_html    = '<h2>' . esc_html__( 'Course details', 'sensei-pro' ) . '</h2>';
			$order_contains_courses = false;
			$product_ids            = [];

			foreach ( $order_items as $item ) {
				$product_ids[] = Sensei_WC_Utils::get_item_id_from_item( $item );
			}

			$courses = Courses::get_product_courses( $product_ids );

			if ( $courses && count( $courses ) > 0 ) {

				foreach ( $courses as $course ) {

					$title                  = $course->post_title;
					$permalink              = get_permalink( $course->ID );
					$order_contains_courses = true;
					// translators: Placeholder is a link to the course.
					$course_details_html .= '<p><strong>' . sprintf( __( 'View course: %1$s', 'sensei-pro' ), '</strong><a href="' . esc_url( $permalink ) . '">' . esc_html( $title ) . '</a>' ) . '</p>';
				}
			}

			// Output Course details.
			if ( $order_contains_courses ) {

				echo wp_kses_post( $course_details_html );

			}
		}

	}

	/**
	 * Completes an order with WCPC and give access to paid courses.
	 *
	 * @since  Sensei 1.0.3
	 * @deprecated 2.0.0 Only used for legacy enrolment handling.
	 * @access public
	 *
	 * @param int $order_id WC order ID.
	 * @return void
	 */
	public static function complete_order( $order_id = 0 ) {
		_deprecated_function( __METHOD__, '2.0.0' );

		$order_user = [];

		// Check for WooCommerce.
		if ( ! self::is_woocommerce_active() || empty( $order_id ) || ! is_numeric( $order_id ) ) {
			return;
		}

		// Get order object.
		$order = wc_get_order( $order_id );
		if ( false === $order ) {
			return;
		}
		$order_status = Sensei_WC_Utils::get_order_status( $order );

		/**
		 * Allow order statuses to be filtered.
		 *
		 * @since 1.0.0
		 *
		 * @param array $order_statuses Currently accepted order statuses.
		 */
		if ( ! in_array( $order_status, apply_filters( 'sensei_wc_paid_courses_order_statuses', [ 'wc-completed', 'wc-processing' ] ), true ) ) {
			return;
		}
		$user = get_user_by( 'id', $order->get_user_id() );

		if ( $user ) {
			$order_user['ID']         = $user->ID;
			$order_user['user_login'] = $user->user_login;
			$order_user['user_email'] = $user->user_email;
			$order_user['user_url']   = $user->user_url;
		}

		if ( 0 === count( $order->get_items() ) ) {
			return;
		}

		Sensei_WC_Utils::log( 'Sensei_WC::complete_order: order_id = ' . $order_id );
		$order_contains_courses = false;

		// Run through each product ordered.
		foreach ( $order->get_items() as $item ) {

			$product_type = '';
			if ( Sensei_WC_Utils::is_wc_item_variation( $item ) ) {
				$product_type = 'variation';
			}

			$item_id = Sensei_WC_Utils::get_item_id_from_item( $item );

			$_product = self::get_product_object( $item_id, $product_type );

			if ( ! ( $_product instanceof \WC_Product ) ) {
				continue;
			}

			$_product_id = $_product->is_type( 'variation' ) ?
				Sensei_WC_Utils::get_product_variation_id( $_product ) :
				Sensei_WC_Utils::get_product_id( $_product );

			// Get courses that use the WC product.
			$courses = Courses::get_product_courses( $_product_id );
			Sensei_WC_Utils::log( 'Sensei_WC::complete_order: Got (' . count( $courses ) . ') course(s), order_id ' . $order_id . ', product_id ' . $_product_id );

			if ( count( $order_user ) > 0 ) {
				// Loop and update those courses.
				foreach ( $courses as $course_item ) {
					Sensei_WC_Utils::log( 'Sensei_WC::complete_order: Update course_id ' . $course_item->ID . ' for user_id ' . $order_user['ID'] );
					$update_course = self::course_update( $course_item->ID, $order_user, $order );
					if ( false === $update_course ) {
						Sensei_WC_Utils::log( 'Sensei_WC::complete_order: FAILED course_update course_id ' . $course_item->ID . ' for user_id ' . $order_user['ID'] );
					}
				}
			}

			if ( count( $courses ) > 0 ) {
				$order_contains_courses = true;
			}
		}

		if ( $order_contains_courses ) {
			// Add meta to indicate that payment has been completed successfully.
			update_post_meta( $order_id, 'sensei_payment_complete', '1' );
		}

	}

	/**
	 * Responds to when an order is cancelled.
	 *
	 * @since Sensei 1.2.0
	 * @since Sensei 1.9.0 Move function to the Sensei_WC class
	 * @deprecated 2.0.0 Only used for legacy enrolment handling.
	 *
	 * @param integer|WC_Order $order_id The order ID.
	 * @return void
	 */
	public static function cancel_order( $order_id ) {
		_deprecated_function( __METHOD__, '2.0.0', 'Method no longer needed when used with Sensei 3' );

		// Get order object.
		if ( is_object( $order_id ) ) {

			$order = $order_id;

		} else {

			$order = wc_get_order( $order_id );
			if ( false === $order ) {
				return;
			}
		}

		if ( ! in_array( $order->get_status(), [ 'cancelled', 'refunded' ], true ) ) {

			return;

		}

		// Run through each product ordered.
		if ( 0 < count( $order->get_items() ) ) {

			// Get order user.
			$user_id = $order->get_customer_id();

			foreach ( $order->get_items() as $item ) {

				$item_id = Sensei_WC_Utils::get_item_id_from_item( $item );

				if ( self::has_customer_bought_product( $user_id, $item_id ) ) {

					// Get courses that use the WC product.
					$courses = Courses::_back_compat_get_product_courses( $item_id );

					// Loop and update those courses.
					foreach ( $courses as $course_item ) {
						// Check and Remove course from courses user meta.
						Sensei_Utils::sensei_remove_user_from_course( $course_item->ID, $user_id );

					}
				}
			}
		}

	}

	/**
	 * Returns the WooCommerce Product Object
	 *
	 * The code caters for pre and post WooCommerce 2.2 installations.
	 *
	 * @since Sensei 1.1.1
	 *
	 * @access public
	 *
	 * @param integer $wc_product_id Product ID or Variation ID.
	 * @param string  $product_type  '' or 'variation'.
	 * @return WC_Product|null|false
	 */
	public static function get_product_object( $wc_product_id = 0, $product_type = '' ) {

		$wc_product_object = false;
		if ( 0 < intval( $wc_product_id ) ) {

			// Get the product.
			if ( function_exists( 'wc_get_product' ) ) {

				$wc_product_object = wc_get_product( $wc_product_id ); // Post WC 2.3x.

			} elseif ( function_exists( 'get_product' ) ) {

				$wc_product_object = get_product( $wc_product_id ); // Post WC 2.0.

			} else {

				// Pre WC 2.0.
				if ( 'variation' === $product_type || 'subscription_variation' === $product_type ) {

					$wc_product_object = new WC_Product_Variation( $wc_product_id );

				} else {

					$wc_product_object = new WC_Product( $wc_product_id );

				}
			}
		}

		return $wc_product_object;

	}

	/**
	 * If customer has purchased the course, update Sensei to indicate that they are taking the course.
	 *
	 * @since  1.0.0
	 * @since Sensei 1.9.0 move to class Sensei_WC
	 * @deprecated 2.0.0
	 *
	 * @param  int           $course_id  The course ID (default: 0).
	 * @param  array|Object  $order_user Specific user's data (default: array()).
	 * @param  WC_Order|null $order      The Order.
	 *
	 * @return bool|int
	 */
	public static function course_update( $course_id = 0, $order_user = [], $order = null ) {
		_deprecated_function( __METHOD__, '2.0.0' );

		global $current_user;
		$has_valid_user_object = isset( $current_user->ID ) || isset( $order_user['ID'] );
		if ( ! $has_valid_user_object ) {
			return false;
		}

		$has_valid_user_id = ! empty( $current_user->ID ) || ! empty( $order_user['ID'] );
		if ( ! $has_valid_user_id ) {
			return false;
		}

		// setup user data.
		if ( is_admin() ) {
			$user_id = $order_user['ID'];
		} else {
			$user_id = empty( $current_user->ID ) ? $order_user['ID'] : $current_user->ID;
			$user    = get_user_by( 'id', $user_id );
			if ( ! $user ) {
				return false;
			}
		}

		Sensei_WC_Utils::log( 'Sensei_WC::course_update: course_id ' . $course_id . ', user_id ' . $user_id );

		// Get the product ID.
		$wc_post_id = get_post_meta( intval( $course_id ), '_course_woocommerce_product', true );
		Sensei_WC_Utils::log( 'Sensei_WC::course_update: product_id ' . $wc_post_id );

		// This doesn't appear to be purely WooCommerce related. Should it be in a separate function?
		$course_prerequisite_id = (int) get_post_meta( $course_id, '_course_prerequisite', true );

		if ( 0 < absint( $course_prerequisite_id ) ) {
			Sensei_WC_Utils::log( 'Sensei_WC::course_update: course_prerequisite_id ' . $course_prerequisite_id );
			$prereq_course_complete = Sensei_Utils::user_completed_course( $course_prerequisite_id, intval( $user_id ) );
			if ( ! $prereq_course_complete ) {
				// Remove all course user meta.
				return Sensei_Utils::sensei_remove_user_from_course( $course_id, $user_id );

			}
		}

		/*
		 * If we're processing a WooCommerce Checkout, check for the payment
		 * method.
		 *
		 * Note that we can ignore these the nonce verification inside the check
		 * for WOOCOMMERCE_CHECKOUT. If we're in the WooCommerce checkout
		 * process, we can trust that nonce verification has already been done.
		 */
		if ( defined( 'WOOCOMMERCE_CHECKOUT' ) && WOOCOMMERCE_CHECKOUT ) {
			$has_payment_method = isset( $_POST['payment_method'] ); // phpcs:ignore WordPress.Security.NonceVerification
			$payment_method     = $has_payment_method ? sanitize_text_field( wp_unslash( $_POST['payment_method'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
		} else {
			$has_payment_method = false;
			$payment_method     = '';
		}
		$is_user_taking_course = Course_Enrolment_Providers::is_user_enrolled( intval( $course_id ), intval( $user_id ) );
		Sensei_WC_Utils::log( 'Sensei_WC::course_update: user_taking_course: ' . ( $is_user_taking_course ? 'yes' : 'no' ) );

		if ( false !== $is_user_taking_course ) {
			return $is_user_taking_course;
		}

		// Get the product IDs.
		$course_product_ids = self::get_course_product_ids( $course_id );
		Sensei_WC_Utils::log( 'Sensei_WC::course_update: course_product_ids ' . implode( ', ', $course_product_ids ) );

		if ( empty( $course_product_ids ) ) {
			return $is_user_taking_course;
		}

		$currently_purchasing_this_course                = false;
		$has_customer_purchased_product_linked_to_course = false;

		if ( null !== $order && is_a( $order, 'WC_Order' ) ) {
			foreach ( $order->get_items() as $item ) {
				if ( isset( $item['variation_id'] ) && ( 0 < $item['variation_id'] ) ) {
					// If item has variation_id then its a variation of the product.
					$item_id = $item['variation_id'];
				} else {
					// Than its real product set it's id to item_id.
					$item_id = $item['product_id'];
				}

				if ( 0 === $item_id ) {
					continue;
				}

				$product = self::get_product_object( $item_id );

				if ( ! is_object( $product ) ) {
					continue;
				}

				$product_courses = Courses::_back_compat_get_product_courses( $product->get_id() );

				if ( $product_courses && count( $product_courses ) > 0 ) {
					foreach ( $product_courses as $course ) {

						if ( $course_id === $course->ID ) {
							$currently_purchasing_this_course = true;
							break 2;
						}
					}
				}
			}
		}

		foreach ( $course_product_ids as $course_product_id ) {
			if ( self::has_customer_bought_product( $user_id, $course_product_id ) ) {
				$has_customer_purchased_product_linked_to_course = true;
				break;
			}
		}

		$currently_purchasing_course = $has_payment_method || $currently_purchasing_this_course;

		if ( $has_payment_method ) {
			Sensei_WC_Utils::log( 'Sensei_WC::course_update: user purchasing course via ' . $payment_method );
		}

		if ( $has_customer_purchased_product_linked_to_course || $currently_purchasing_course ) {

			$activity_logged = Sensei_Utils::user_start_course( intval( $user_id ), intval( $course_id ) );
			Sensei_WC_Utils::log( 'Sensei_WC::course_update: activity_logged: ' . $activity_logged );
			$is_user_taking_course = Course_Enrolment_Providers::is_user_enrolled( $user_id, $course_id );
		}

		Sensei_WC_Utils::log( 'Sensei_WC::course_update: user taking course after update: ' . ( $is_user_taking_course ? 'yes' : 'NO' ) );

		return $is_user_taking_course;

	}

	/**
	 * Disable guest checkout if a course product is in the cart
	 *
	 * @since Sensei 1.1.0
	 * @since Sensei 1.9.0 move to class Sensei_WC
	 *
	 * @param  boolean $guest_checkout Current guest checkout setting.
	 *
	 * @return boolean                 Modified guest checkout setting
	 */
	public static function disable_guest_checkout( $guest_checkout ) {

		if ( ! is_admin() || ( defined( 'DOING_AJAX' ) && DOING_AJAX ) ) {

			if ( isset( WC()->cart->cart_contents ) && count( WC()->cart->cart_contents ) > 0 ) {

				foreach ( WC()->cart->cart_contents as $cart_key => $product ) {
					if ( isset( $product['product_id'] ) ) {

						$args = [
							'posts_per_page' => -1,
							'post_type'      => 'course',
							'meta_query'     => [
								[
									'key'   => '_course_woocommerce_product',
									'value' => $product['product_id'],
								],
							],
						];

						$posts = get_posts( $args );

						if ( $posts && count( $posts ) > 0 ) {

							foreach ( $posts as $course ) {
								$guest_checkout = '';
								break;

							}
						}
					}
				}
			}
		}

		return $guest_checkout;

	}

	/**
	 * Change order status with virtual products to completed
	 *
	 * @since  Sensei 1.1.0
	 * @since Sensei 1.9.0 move to class Sensei_WC
	 *
	 * @param string $order_status Order Status.
	 * @param int    $order_id Order ID.
	 *
	 * @return string
	 **/
	public static function virtual_order_payment_complete( $order_status, $order_id ) {

		$order = wc_get_order( $order_id );

		if ( ! isset( $order ) || false === $order ) {
			return '';
		}

		$status = Sensei_WC_Utils::get_order_status( $order );

		if ( in_array( $order_status, [ 'wc-processing', 'processing' ], true ) &&
			in_array( $status, [ 'wc-on-hold', 'wc-pending', 'wc-failed' ], true ) ) {
			$virtual_order = true;

			if ( count( $order->get_items() ) > 0 ) {

				foreach ( $order->get_items() as $item ) {
					$_product = $item->get_product();
					if ( false === $_product ) {
						continue;
					}

					if ( ! $_product->is_virtual() ) {
						$virtual_order = false;
						break;
					}
				}
			}

			// virtual order, mark as completed.
			if ( $virtual_order ) {

				return 'completed';

			}
		}

		return $order_status;

	}

	/**
	 * Determine if the user has and active subscription to give them access
	 * to the requested resource.
	 *
	 * @since Sensei 1.9.0
	 *
	 * @param  bool $user_access_permission Access Permission.
	 * @param  int  $user_id User ID.
	 *
	 * @return bool $user_access_permission
	 */
	public static function get_subscription_permission( $user_access_permission, $user_id ) {
		_deprecated_function( __FUNCTION__, esc_html( Sensei()->version ), 'Sensei_WC_Subscriptions::get_subscription_permission' );
		return Sensei_WC_Subscriptions::get_subscription_permission( $user_access_permission, $user_id );
	}

	/**
	 * Get_subscription_user_started_course
	 *
	 * @since Sensei 1.9.0
	 *
	 * @param bool $has_user_started_course Has Started.
	 * @param int  $course_id Course ID.
	 * @param int  $user_id User ID.
	 *
	 * @return bool $has_user_started_course
	 */
	public static function get_subscription_user_started_course( $has_user_started_course, $course_id, $user_id ) {
		_deprecated_function( __FUNCTION__, esc_html( Sensei()->version ), 'Sensei_WC_Subscriptions::get_subscription_user_started_course' );
		return Sensei_WC_Subscriptions::get_subscription_user_started_course( $has_user_started_course, $course_id, $user_id );
	}

	/**
	 * Compare the user's subscriptions end date with the date
	 * the user was added to the course. If the user was added after
	 * the subscription ended they were manually added and this will return
	 * true.
	 *
	 * Important to note that all subscriptions for the user is compared.
	 *
	 * @deprecated Sensei 1.9.12
	 * @since Sensei 1.9.0
	 *
	 * @param int $user_id User ID.
	 * @param int $product_id Product ID.
	 * @param int $course_id Course ID.
	 *
	 * @return bool
	 */
	public static function was_user_added_without_subscription( $user_id, $product_id, $course_id ) {
		_deprecated_function( __FUNCTION__, esc_html( Sensei()->version ), 'Sensei_WC_Subscriptions::was_user_added_without_subscription' );
		return Sensei_WC_Subscriptions::was_user_added_without_subscription( $user_id, $product_id, $course_id );
	}

	/**
	 * Get all the orders for a specific user and product combination
	 *
	 * @param int $user_id The user id.
	 * @param int $product_id The product id.
	 *
	 * @return array $orders
	 */
	public static function get_user_product_orders( $user_id = 0, $product_id ) {

		if ( empty( $user_id ) ) {
			return [];
		}

		$args = [
			'posts_per_page' => -1,
			'post_type'      => 'shop_order',
			'meta_key'       => '_customer_user',
			'meta_value'     => intval( $user_id ),
			'post_status'    => [ 'wc-completed', 'wc-processing' ],
		];

		return get_posts( $args );

	}

	/**
	 * Determine if a course can be purchased. Purchasable
	 * courses have valid products attached. These can also be products
	 * with price of Zero.
	 *
	 * @since Sensei 1.9.0
	 *
	 * @param int  $course_id           Course ID.
	 * @param bool $include_memberships Optional. Whether to include course memberships. Default false.
	 *
	 * @return bool
	 */
	public static function is_course_purchasable( $course_id = 0, $include_memberships = false ) {
		if ( ! self::is_woocommerce_active() ) {
			return false;
		}

		if ( empty( $course_id ) || Course_Expiration::instance()->is_access_expired( get_current_user_id(), $course_id ) ) {
			return false;
		}

		// First check if the course is attached to a product.
		$course_product_ids = self::get_course_product_ids( $course_id, $include_memberships );

		if ( empty( $course_product_ids ) ) {
			return false;
		}

		foreach ( $course_product_ids as $course_product_id ) {
			$course_product = wc_get_product( $course_product_id );

			if ( ! ( $course_product instanceof \WC_Product ) ) {
				continue;
			}

			// Check that the product is published.
			if ( 'publish' === $course_product->get_status() ) {
				return true;
			}

			// Check that the product is otherwise purchasable.
			if ( $course_product->is_purchasable() ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Do not add course to cart if Learner is already taking it
	 *
	 * @since Sensei 1.9.16
	 *
	 * @hooked into woocommerce_add_to_cart
	 *
	 * @param mixed $cart_item_key Cart Item Key.
	 * @param int   $product_id Product ID.
	 * @param mixed $quantity Quantity.
	 * @param int   $variation_id Variation ID.
	 * @param mixed $variation Variation.
	 * @param mixed $cart_item_data Cart Item Data.
	 *
	 * @throws Exception When learner already taking course.
	 */
	public static function do_not_add_course_to_cart_if_user_taking_course( $cart_item_key, $product_id, $quantity, $variation_id, $variation, $cart_item_data ) {
		if ( ! self::is_woocommerce_active() ) {
			return;
		}

		$user_id = get_current_user_id();
		$product = self::get_product_object( absint( $product_id ) );

		if ( ! ( $product instanceof \WC_Product ) || ! $user_id ) {
			return 0;
		}

		$courses              = Courses::get_product_courses( $product_id );
		$product_course_count = count( $courses );
		$courses_started      = 0;
		$has_expired_courses  = false;

		// Do not do anything if the product is not linked to any courses.
		if ( 0 === $product_course_count ) {
			return;
		}

		foreach ( $courses as $course ) {
			if ( Course_Enrolment_Providers::is_user_enrolled( $course->ID, $user_id ) ) {
				$courses_started++;
			} elseif ( Course_Expiration::instance()->is_access_expired( $user_id, $course->ID ) ) {
				$has_expired_courses = true;
			}
		}

		if ( $courses_started === $product_course_count ) {
			// Taking all courses. Prevent adding to the cart.
			throw new Exception( __( 'You are already taking all the courses associated with this product.', 'sensei-pro' ) );
		} elseif ( $has_expired_courses ) {
			// Has expired courses.
			wc_add_notice( __( 'The product you are buying contains a course where your access has expired. Buying it will not enroll you again to expired course.', 'sensei-pro' ), 'notice' );
		} elseif ( $courses_started > 0 ) {
			// Taking some courses.
			wc_add_notice( __( 'The product you are buying contains a course you are already taking.', 'sensei-pro' ), 'notice' );
		}
	}

	/**
	 * Check if learner is already taking courses with a product.
	 *
	 * @deprecated 2.6.0
	 *
	 * @since 2.2.0
	 *
	 * @param int $product_id Product ID.
	 *
	 * @return int 0 if user is not taking any course with the product.
	 *             1 if user is taking courses with the product.
	 *             2 if user is taking all courses with the product.
	 */
	public static function get_enrollment_status_for_product_courses( $product_id ) {
		_deprecated_function( __METHOD__, '2.6.0' );

		$user_id = get_current_user_id();
		$product = self::get_product_object( absint( $product_id ) );

		if ( ! ( $product instanceof \WC_Product ) || ! $user_id ) {
			return 0;
		}

		$courses              = Courses::get_product_courses( $product_id );
		$product_course_count = count( $courses );
		$courses_started      = 0;

		foreach ( $courses as $course ) {
			if ( Course_Enrolment_Providers::is_user_enrolled( $course->ID, $user_id ) ) {
				$courses_started++;
			}
		}

		if ( 0 === $courses_started ) {
			return 0;
		}

		return $courses_started === $product_course_count ? 2 : 1;
	}

	/**
	 * Get_courses_from_product_id
	 *
	 * @param int $item_id Item id.
	 * @return array
	 */
	private static function get_courses_from_product_id( $item_id ) {
		$product = self::get_product_object( $item_id );
		if ( ! is_object( $product ) ) {
			return [];
		}

		$product_courses = Courses::_back_compat_get_product_courses( $product->get_id() );
		return $product_courses;
	}

	/**
	 * WC start_purchased_courses_for_user
	 *
	 * @deprecated 1.1.0
	 *
	 * @param int $user_id The user ID.
	 */
	private static function start_purchased_courses_for_user( $user_id ) {
		_deprecated_function( __METHOD__, '1.1.0' );

		// get current user's active courses.
		$active_courses = Sensei_Utils::sensei_check_for_activity(
			[
				'user_id' => $user_id,
				'type'    => 'sensei_course_status',
			],
			true
		);

		if ( empty( $active_courses ) ) {
			$active_courses = [];
		}

		if ( ! is_array( $active_courses ) ) {
			$active_courses = [ $active_courses ];
		}

		$active_course_ids = [];

		foreach ( $active_courses as $c ) {
			$active_course_ids[] = $c->comment_post_ID;
		}

		$orders_query = new WP_Query(
			[
				'post_type'      => 'shop_order',
				'posts_per_page' => -1,
				'post_status'    => [ 'wc-processing', 'wc-completed' ],
				'meta_key'       => '_customer_user',
				'meta_value'     => $user_id,
				'fields'         => 'ids',
			]
		);

		// get user's processing and completed orders.
		$user_order_ids = $orders_query->get_posts();

		if ( empty( $user_order_ids ) ) {
			$user_order_ids = [];
		}

		if ( ! is_array( $user_order_ids ) ) {
			$user_order_ids = [ $user_order_ids ];
		}

		$user_orders = [];

		foreach ( $user_order_ids as $order_data ) {
			$user_order = wc_get_order( $order_data );
			if ( false === $user_order ) {
				continue;
			}
			$user_orders[] = $user_order;
		}

		foreach ( $user_orders as $user_order ) {
			foreach ( $user_order->get_items() as $item ) {
				$item_id = Sensei_WC_Utils::get_item_id_from_item( $item );

				$product_courses                    = self::get_courses_from_product_id( $item_id );
				$is_variation                       = Sensei_WC_Utils::is_wc_item_variation( $item );
				$is_course_linked_to_parent_product = false;

				if ( empty( $product_courses ) && $is_variation ) {
					// if we get no products from a variable sub course.
					// check if there are any courses linked to the parent product id.
					$item_id                            = Sensei_WC_Utils::get_item_id_from_item( $item, true );
					$product_courses                    = self::get_courses_from_product_id( $item_id );
					$is_course_linked_to_parent_product = ! empty( $product_courses );
				}

				foreach ( $product_courses as $course ) {
					$course_id = $course->ID;
					$order_id  = self::get_learner_course_active_order_id( $user_id, $course_id, $is_course_linked_to_parent_product );

					if ( in_array( $order_id, $user_order_ids, true ) &&
						! in_array( $course_id, $active_course_ids, true )
					) {
						if ( Sensei_WC_Subscriptions::has_user_bought_subscription_but_cancelled( $course_id, $user_id ) ) {
							continue;
						}
						// user ordered a course and not assigned to it. Fix this by assigning them now.
						Sensei_Utils::start_user_on_course( $user_id, $course_id );
					}
				}
			}
		}
	}

	/**
	 * Get purchased course data of a user.
	 *
	 * Returns an array containing arrays of the following shape:
	 *
	 * [ 'course_id' => $course_id, 'product_id' => $product id, 'order_id' => $order_id ]
	 *
	 * @deprecated 2.6.4
	 *
	 * @param int $user_id User ID.
	 *
	 * @return array
	 */
	public static function get_purchased_course_data_for_user( $user_id ) {
		_deprecated_function( __METHOD__, '2.6.4' );

		$purchased_course_data = [];
		$user_orders           = Sensei_WC_Utils::get_user_orders( $user_id );

		foreach ( $user_orders as $user_order ) {
			$user_order_purchase_data = self::get_purchased_course_data_for_order( $user_order );

			foreach ( $user_order_purchase_data as $data ) {
				$purchased_course_data[] = $data;
			}
		}

		return $purchased_course_data;
	}

	/**
	 * Gets course and product data from a user order.
	 *
	 * @deprecated 2.6.4
	 *
	 * @param WC_Order $user_order The user order.
	 *
	 * @return array
	 */
	private static function get_purchased_course_data_for_order( $user_order ) {
		_deprecated_function( __METHOD__, '2.6.4' );

		$purchased_course_data = [];
		$user_order_items      = Sensei_WC_Utils::get_items_from_user_order( $user_order );

		foreach ( $user_order_items as $item_data ) {
			$item            = $item_data['item'];
			$order           = $item_data['order'];
			$item_id         = Sensei_WC_Utils::get_item_id_from_item( $item );
			$product_courses = Courses::get_product_courses( $item_id );

			foreach ( $product_courses as $course ) {
				$course_id = absint( $course->ID );

				if ( in_array( $course_id, $purchased_course_data, true ) ) {
					continue;
				}

				$purchased_course_data[] = [
					'course_id'  => $course_id,
					'order_id'   => $order->get_id(),
					'product_id' => $item_id,
				];
			}
		}

		return $purchased_course_data;
	}

}
