<?php
/**
 * Hooks linking WooCommerce functionality into Sensei
 *
 * @package sensei-wc-paid-courses
 */

/**
 * Show the WooCommerce course filter links above the courses.
 *
 * @since Sensei 1.9.0
 */
add_filter( 'sensei_archive_course_filter_by_options', [ 'Sensei_WC', 'add_course_archive_wc_filter_links' ] );

/**
 * Filter the queries for paid and free course based on the users selection.
 *
 * @since Sensei 1.9.0
 */
add_filter( 'pre_get_posts', [ 'Sensei_WC', 'course_archive_wc_filter_free' ] );
add_filter( 'pre_get_posts', [ 'Sensei_WC', 'course_archive_wc_filter_paid' ] );

/**
 * Add woocommerce action above single course the action
 *
 * @since Sensei 1.9.0
 */
add_action( 'sensei_before_main_content', [ 'Sensei_WC', 'do_single_course_wc_single_product_action' ], 50 );


/******************************
 *
 * Single Lesson Hooks
 */
if ( \Sensei_WC_Paid_Courses\Course_Enrolment_Providers::use_legacy_enrolment_method() ) {
	add_filter( 'sensei_can_user_view_lesson', [ 'Sensei_WC', 'alter_can_user_view_lesson' ], 20, 3 );
}

/******************************
 *
 * Login required to access content.
 */
add_filter( 'sensei_is_login_required', [ 'Sensei_WC', 'require_login_for_paid_courses' ], 10, 2 );

/******************************
 *
 * Single Course.
 */
// @since Sensei 1.9.0
// show a notice if the user has already added the current course to their cart.
add_action( 'sensei_single_course_content_inside_before', [ 'Sensei_WC', 'course_in_cart_message' ], 20 );

/******************************
 *
 * No Permissions Template
 */
// @since Sensei 1.9.0
// alter the no permissions message to show the woocommerce message instead
add_filter( 'sensei_the_no_permissions_message', [ 'Sensei_WC', 'alter_no_permissions_message' ], 20, 2 );

// @since Sensei 1.9.0
// add  woocommerce class to the the no permission body class to ensure WooCommerce elements are styled
add_filter( 'body_class', [ 'Sensei_WC', 'add_woocommerce_body_class' ], 20, 1 );


/************************************
 *
 * Emails
 */
// Add Email link to course orders.
add_action( 'woocommerce_email_after_order_table', [ 'Sensei_WC', 'email_course_details' ], 10, 1 );

/************************************
 *
 * Checkout
 */
if ( \Sensei_WC_Paid_Courses\Course_Enrolment_Providers::use_legacy_enrolment_method() ) {
	add_action( 'woocommerce_order_status_completed', [ 'Sensei_WC', 'complete_order' ] );
	add_action( 'woocommerce_order_status_processing', [ 'Sensei_WC', 'complete_order' ] );
	add_action( 'woocommerce_order_status_cancelled', [ 'Sensei_WC', 'cancel_order' ] );
}

// Disable guest checkout if a course is in the cart as we need a valid user to store data for.
add_filter( 'pre_option_woocommerce_enable_guest_checkout', [ 'Sensei_WC', 'disable_guest_checkout' ] );
// Mark orders with virtual products as complete rather then stay processing.
add_filter( 'woocommerce_payment_complete_order_status', [ 'Sensei_WC', 'virtual_order_payment_complete' ], 10, 2 );

/************************************
 *
 * Order details
 */
add_action( 'woocommerce_after_order_details', [ 'Sensei_WC', 'order_details_display_courses' ] );

/************************************
 *
 * Add To Cart
 */
// fail to add to cart if user already taking course.
add_action( 'woocommerce_add_to_cart', [ 'Sensei_WC', 'do_not_add_course_to_cart_if_user_taking_course' ], 10, 6 );

/************************************
 *
 * My Account
 */
add_filter( 'woocommerce_account_menu_items', [ 'Sensei_WC', 'add_my_account_courses_menu' ], 10, 1 );
add_filter( 'woocommerce_get_endpoint_url', [ 'Sensei_WC', 'my_account_courses_menu_link' ], 10, 4 );
