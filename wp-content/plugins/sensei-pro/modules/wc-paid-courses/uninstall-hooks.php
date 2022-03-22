<?php
/**
 * Sensei WooCommerce Paid Courses Uninstall Hooks
 *
 * Adds the associated data to be deleted when uninstalled.
 * This includes options, post_meta, user_meta, transients and custom database tables.
 *
 * @package sensei-pro
 * @since 1.0.0
 */

add_filter(
	'sensei_pro_data_cleaner_post_meta',
	function( $post_meta ) {
		return array_merge(
			$post_meta,
			[
				'_course_woocommerce_product',
				'sensei_wc_paid_courses_calculation_version',
			]
		);
	}
);

add_filter(
	'sensei_pro_data_cleaner_options',
	function( $options ) {
		return array_merge(
			$options,
			[
				'sensei-wc-paid-courses-memberships-cancelled-orders',
			]
		);
	}
);

add_filter(
	'sensei_pro_data_cleaner_user_meta',
	function( $user_meta ) {
		return array_merge(
			$user_meta,
			[
				'sensei_wcpc_modal_confirmation_date',
			]
		);
	}
);

add_filter(
	'sensei_pro_data_cleaner_transients',
	function( $transients ) {
		return array_merge(
			$transients,
			[
				'sensei-wc-paid-courses-translations-.*',
				'sensei_language_packs_.*',
			]
		);
	}
);
