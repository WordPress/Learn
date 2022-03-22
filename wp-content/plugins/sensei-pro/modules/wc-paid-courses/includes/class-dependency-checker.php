<?php
/**
 * File containing the class \Sensei_WC_Paid_Courses\Dependency_Checker.
 *
 * @package sensei-wc-paid-courses
 * @since   1.0.0
 */

namespace Sensei_WC_Paid_Courses;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Sensei Pro Dependencies Check
 *
 * @since 1.0.0
 */
class Dependency_Checker {
	const MINIMUM_WOOCOMMERCE_VERSION = '4.0.0';

	/**
	 * Check if WooCommerce is activated.
	 *
	 * @return bool
	 */
	public static function woocommerce_dependency_is_met() {
		if ( ! class_exists( 'WooCommerce' ) ) {
			return false;
		}

		return version_compare( self::MINIMUM_WOOCOMMERCE_VERSION, get_option( 'woocommerce_db_version' ), '<=' );
	}
}
