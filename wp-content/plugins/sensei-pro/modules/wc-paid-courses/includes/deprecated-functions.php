<?php
/**
 * Contains deprecated functions.
 *
 * @package sensei-wc-paid-courses
 */

add_action( 'plugins_loaded', 'sensei_wc_paid_courses_declare_is_woocommerce_active', 5 );

/**
 * Late declaration of `is_woocommerce_active()`. Allows for other plugins/sources of this function to declare first.
 *
 * @since 1.0.0
 * @access private
 */
function sensei_wc_paid_courses_declare_is_woocommerce_active() {
	if ( ! function_exists( 'is_woocommerce_active' ) ) {
		/**
		 * WC Detection for backwards compatibility.
		 *
		 * @deprecated since 1.0.0 Plugins and themes should use their own helper for determining if WooCommerce is active.
		 */
		function is_woocommerce_active() { // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedFunctionFound -- For deprecated usage.
			_deprecated_function( __FUNCTION__, '1.0.0' );

			return class_exists( 'WooCommerce' );
		}
	}
}
