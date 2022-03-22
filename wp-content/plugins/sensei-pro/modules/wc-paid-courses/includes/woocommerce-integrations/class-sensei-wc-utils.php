<?php
/**
 * WooCommerce Utility/Compatibility.
 *
 * @package sensei-wc-paid-courses
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// @phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedClassFound -- Legacy classname.

/**
 * Class Sensei_WC_Utils.
 */
class Sensei_WC_Utils {

	/**
	 * Logger object.
	 *
	 * @var WC_Logger
	 */
	private static $logger = null;

	/**
	 * Gets the order status prefixed with `wc-`
	 *
	 * @param WC_Order $order Order object.
	 * @return string
	 */
	public static function get_order_status( $order ) {
		return 'wc-' . $order->get_status();
	}

	/**
	 * Checks WooCommerce version is less than $str.
	 *
	 * @deprecated 1.0.0 Call version_compare( WC()->version, $str, '<' ) directly.
	 *
	 * @param string $str Version String.
	 * @return mixed
	 */
	public static function wc_version_less_than( $str ) {
		_deprecated_function( __METHOD__, '1.0.0', 'version_compare( WC()->version, $str, \'<\' )' );
		return version_compare( WC()->version, $str, '<' );
	}

	/**
	 * Checks if a line item contains a certain product.
	 *
	 * @param int                   $product_id Product post ID.
	 * @param WC_Order_Item_Product $item       Order line item.
	 * @return bool
	 */
	public static function has_user_bought_product( $product_id, $item ) {
		$product_id = absint( $product_id );
		return $product_id === $item->get_variation_id() || $product_id === $item->get_product_id();
	}

	/**
	 * Checks if a line item is a product variation.
	 *
	 * @param array|WC_Order_Item_Product $item Order line item.
	 * @return bool
	 */
	public static function is_wc_item_variation( $item ) {
		if ( is_a( $item, 'WC_Order_Item_Product' ) ) {
			return $item->get_variation_id() ? true : false;
		}
		return isset( $item['variation_id'] ) && ! empty( $item['variation_id'] );
	}

	/**
	 * Checks if a product is a variation product.
	 *
	 * @deprecated 1.0.0 Call method `is_type` on object.
	 *
	 * @param WC_Product $product Product.
	 * @return bool
	 */
	public static function is_product_variation( $product ) {
		_deprecated_function( __METHOD__, '1.0.0', '$product->is_type( \'variation\' )' );

		return $product->is_type( 'variation' );
	}

	/**
	 * Get the order ID from an order object.
	 *
	 * @deprecated 1.0.0 Use `$order->get_id()` directly.
	 *
	 * @param WC_Order $order Order object.
	 * @return mixed
	 */
	public static function get_order_id( $order ) {
		_deprecated_function( __METHOD__, '1.0.0', '$order->get_id()' );

		return $order->get_id();
	}

	/**
	 * Get the product id. Always return parent id in variations.
	 *
	 * @param WC_Product $product The product object.
	 * @return int
	 */
	public static function get_product_id( $product ) {
		return $product->is_type( 'variation' ) ? $product->get_parent_id() : $product->get_id();
	}

	/**
	 * Get the product variation ID from a product object.
	 *
	 * @param WC_Product $product Product object.
	 * @return int|null
	 */
	public static function get_product_variation_id( $product ) {
		if ( ! $product->is_type( 'variation' ) ) {
			return null;
		}
		return $product->get_id();
	}

	/**
	 * Get the product ID or variation ID from a order line item.
	 *
	 * @param WC_Order_Item_Product|WC_Bundled_Item $item Order line item object.
	 * @param bool                                  $always_return_parent_product_id True if we should return the parent id for variations.
	 * @return mixed
	 */
	public static function get_item_id_from_item( $item, $always_return_parent_product_id = false ) {
		if ( method_exists( $item, 'get_variation_id' ) ) {
			$variation_id = $item->get_variation_id();

			if ( false === $always_return_parent_product_id && $variation_id && 0 < $variation_id ) {
				return $variation_id;
			}
		}

		return $item->get_product_id();
	}

	/**
	 * Get product from ID.
	 *
	 * @deprecated 1.0.0 Use wc_get_product instead.
	 *
	 * @param WP_Post|int $post_or_id Post Or ID.
	 * @return null|WC_Product
	 */
	public static function get_product( $post_or_id ) {
		_deprecated_function( __METHOD__, '1.0.0', 'wc_get_product' );

		return wc_get_product( $post_or_id );
	}

	/**
	 * Get the product object.
	 *
	 * @deprecated 1.0.0 Use wc_get_product()
	 *
	 * @param WC_Product $product Product.
	 * @return null|WC_Product
	 */
	public static function get_parent_product( $product ) {
		_deprecated_function( __METHOD__, '1.0.0', 'wc_get_product' );

		return wc_get_product( self::get_product_id( $product ) );
	}

	/**
	 * Get variation attributes if it is a variation product.
	 *
	 * @param WC_Product $product The product object.
	 * @return array
	 */
	public static function get_variation_data( $product ) {
		return $product->is_type( 'variation' ) ? wc_get_product_variation_attributes( $product->get_id() ) : [];
	}

	/**
	 * Gets a formatted version of variation data or item meta.
	 *
	 * @deprecated 1.0.0 Use wc_get_formatted_variation() directly.
	 *
	 * @param mixed $variation Variation object.
	 * @param bool  $flat      Should this be a flat list or HTML list? (default: false).
	 * @return string
	 */
	public static function get_formatted_variation( $variation = '', $flat = false ) {
		_deprecated_function( __METHOD__, '1.0.0', 'wc_get_formatted_variation' );

		return wc_get_formatted_variation( $variation, $flat );
	}

	/**
	 * Get variation attributes if it is a variation product.
	 *
	 * @param WC_Product $product The product object.
	 * @return array
	 */
	public static function get_product_variation_data( $product ) {
		_deprecated_function( __METHOD__, '1.0.0', '\Sensei_WC_Utils::get_variation_data' );

		return $product->is_type( 'variation' ) ? wc_get_product_variation_attributes( $product->get_id() ) : [];
	}

	/**
	 * Lazy-load our logger.
	 *
	 * @return WC_Logger
	 */
	private static function get_logger() {
		if ( null === self::$logger ) {
			self::$logger = new WC_Logger();
		}

		return self::$logger;
	}

	/**
	 * Log errors using WooCommerce's logger.
	 *
	 * @param string $message Message to log.
	 */
	public static function log( $message ) {
		$debugging_enabled = (bool) Sensei()->settings->get( 'woocommerce_enable_sensei_debugging' );
		if ( ! $debugging_enabled ) {
			return;
		}
		self::get_logger()->log(
			'notice',
			$message,
			[
				'source' => 'woothemes_sensei_core',
			]
		);
	}

	/**
	 * Get Product From item.
	 *
	 * @deprecated 1.0.0 $item->get_product()
	 *
	 * @param array|WC_Order_Item_Product $item The item.
	 * @param WC_Order                    $order The order.
	 *
	 * @return bool|WC_Product
	 */
	public static function get_product_from_item( $item, $order ) {
		_deprecated_function( __METHOD__, '1.0.0', '$item->get_product()' );

		return $item->get_product();
	}

	/**
	 * Get Checkout URL.
	 *
	 * @deprecated 1.0.0 Use `wc_get_checkout_url`
	 *
	 * @return string
	 */
	public static function get_checkout_url() {
		_deprecated_function( __METHOD__, '1.0.0', 'wc_get_checkout_url' );

		return wc_get_checkout_url();
	}

	/**
	 * Get all of a user's orders.
	 *
	 * @deprecated 2.6.4
	 *
	 * @param int        $user_id The user id.
	 * @param null|array $status_filter Filter for these statuses (will only get completed and processing by default).
	 *
	 * @return array|int[]|\WP_Post[]
	 */
	public static function get_user_orders( $user_id, $status_filter = null ) {
		_deprecated_function( __METHOD__, '2.6.4' );

		if ( ! isset( $user_id ) || ! is_numeric( $user_id ) ) {
			return [];
		}

		$user_id = absint( $user_id );

		if ( null === $status_filter ) {
			$status_filter = [ 'wc-processing', 'wc-completed' ];
		}

		// Get all user's orders.
		$order_args = [
			'post_type'      => 'shop_order',
			'posts_per_page' => -1,
			'post_status'    => $status_filter,
			'meta_query'     => [
				[
					'key'   => '_customer_user',
					'value' => $user_id,
				],
			],
			'fields'         => 'ids',
			'order_by'       => 'ID',
		];

		$order_ids = get_posts( $order_args );

		if ( empty( $order_ids ) ) {
			return [];
		}

		return self::get_orders_from_order_ids( $order_ids );
	}

	/**
	 * Get all WC_Order_Item items from a user order.
	 *
	 * @deprecated 2.6.4
	 *
	 * @param WC_Order $user_order The user order.
	 * @return array
	 */
	public static function get_items_from_user_order( $user_order ) {
		_deprecated_function( __METHOD__, '2.6.4' );

		$items            = [];
		$user_order_items = $user_order->get_items();

		foreach ( $user_order_items as $item ) {
			$product_id = self::get_item_id_from_item( $item );
			$product    = wc_get_product( $product_id );

			if ( ! is_object( $product ) ) {
				continue;
			}

			if ( $product->is_type( 'bundle' ) ) {
				$bundled_product = new WC_Product_Bundle( self::get_product_id( $product ) );
				$bundled_items   = $bundled_product->get_bundled_items();

				foreach ( $bundled_items as $bundled_item ) {
					$items[] = [
						'order' => $user_order,
						'item'  => $bundled_item,
					];
				}
			} else {
				$items[] = [
					'order' => $user_order,
					'item'  => $item,
				];
			}
		}

		return $items;
	}

	/**
	 * Get orders from order ids.
	 *
	 * @deprecated 2.6.4
	 *
	 * @param array $user_order_ids The user order ids.
	 *
	 * @return array
	 */
	private static function get_orders_from_order_ids( $user_order_ids ) {
		_deprecated_function( __METHOD__, '2.6.4' );

		$user_orders = [];

		foreach ( $user_order_ids as $order_id ) {
			$user_order = wc_get_order( $order_id );

			if ( ! $user_order ) {
				continue;
			}

			$user_orders[] = $user_order;
		}

		return $user_orders;
	}

	/**
	 * Check if User started or completed a course.
	 *
	 * @param int $user_id The user id.
	 * @param int $course_id The course id.
	 *
	 * @return bool
	 */
	public static function has_user_started_or_completed_course( $user_id, $course_id ) {
		$user_course_status = Sensei_Utils::user_course_status( intval( $course_id ), $user_id );

		// Ignore course if already started or completed.
		if ( $user_course_status || Sensei_Utils::user_completed_course( $user_course_status ) ) {
			return true;
		}

		return false;
	}
}
