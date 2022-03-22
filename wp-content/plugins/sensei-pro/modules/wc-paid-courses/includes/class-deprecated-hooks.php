<?php
/**
 * File containing the class \Sensei_WC_Paid_Courses\Deprecated_Hooks.
 *
 * @package sensei-wc-paid-courses
 * @since   1.0.0
 */

namespace Sensei_WC_Paid_Courses;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class containing all deprecated hook handling.
 *
 * @class Sensei_WC_Paid_Courses\Deprecated_Hooks
 */
class Deprecated_Hooks {
	const TYPE_FILTER = 'filter';
	const TYPE_ACTION = 'action';

	/**
	 * Priority to call deprecated hooks in the replacement hook.
	 *
	 * @var int
	 */
	protected static $legacy_hook_priority = -1000;

	/**
	 * Max number of arguments ever called in a deprecated hook.
	 *
	 * @var int
	 */
	protected static $max_arguments = 1;

	/**
	 * List of deprecated hooks.
	 *
	 * @var array {
	 *    string $old_key => {
	 *       @type string $type        Either `self::TYPE_FILTER` or `self::TYPE_ACTION`
	 *       @type string $version     Version the hook was deprecated.
	 *       @type string $replacement (Optional) New hook that replaces the old hook.
	 *       @type string $message     (Optional) Message to show along with the deprecation notice.
	 *    }
	 * }
	 */
	protected static $deprecated_hooks = [
		'sensei_merge_courses_with_parent_product'       => [
			'type'        => self::TYPE_FILTER,
			'version'     => '1.0.0',
			'replacement' => 'sensei_wc_paid_courses_merge_courses_with_parent_product',
		],
		'sensei_wc_subscriptions_get_subscription_types' => [
			'type'        => self::TYPE_FILTER,
			'version'     => '1.0.0',
			'replacement' => 'sensei_wc_paid_courses_subscriptions_get_subscription_types',
		],
		'sensei_wc_memberships_auto_start_course'        => [
			'type'    => self::TYPE_FILTER,
			'version' => '1.0.0',
		],
		'sensei_wc_memberships_auto_start_courses'       => [
			'type'    => self::TYPE_FILTER,
			'version' => '1.0.0',
		],
		'sensei_wc_paid_courses_memberships_auto_start_course' => [
			'type'    => self::TYPE_FILTER,
			'version' => '2.0.0',
		],
		'sensei_wc_paid_courses_memberships_auto_start_courses' => [
			'type'        => self::TYPE_FILTER,
			'version'     => '2.0.0',
			'replacement' => 'sensei_wc_paid_courses_memberships_auto_enrol_courses',
		],
		'sensei_wc_single_add_to_cart_button_text'       => [
			'type'        => self::TYPE_FILTER,
			'version'     => '1.0.0',
			'replacement' => 'sensei_wc_paid_courses_add_to_cart_button_text',
		],
		'sensei_wc_order_statuses'                       => [
			'type'        => self::TYPE_FILTER,
			'version'     => '1.0.0',
			'replacement' => 'sensei_wc_paid_courses_order_statuses',
		],
	];

	/**
	 * Deprecated_Hooks constructor. Prevents instances from being created.
	 */
	private function __construct() {}

	/**
	 * Initializes the handling of deprecated hooks.
	 */
	public static function init() {
		add_action( 'init', [ static::class, 'check_hooks' ] );

		static::legacy_runner();
	}

	/**
	 * Adds backwards compatibility with the legacy hooks.
	 */
	private static function legacy_runner() {
		foreach ( static::$deprecated_hooks as $old_hook => $settings ) {
			if ( empty( $settings['replacement'] ) || empty( $settings['type'] ) ) {
				continue;
			}

			$new_hook = $settings['replacement'];
			if ( self::TYPE_ACTION === $settings['type'] ) {
				static::hook_into_new_action( $old_hook, $new_hook );
			} elseif ( self::TYPE_FILTER === $settings['type'] ) {
				static::hook_into_new_filter( $old_hook, $new_hook );
			}
		}
	}

	/**
	 * Set up the firing of the deprecated action on the new action.
	 *
	 * @param string $old_hook Name of deprecated hook.
	 * @param string $new_hook Name of replacement hook.
	 */
	private static function hook_into_new_action( $old_hook, $new_hook ) {
		add_action(
			$new_hook,
			function() use ( $old_hook, $new_hook ) {
				$callback_args = func_get_args();
				if ( has_action( $old_hook ) ) {
					static::trigger_deprecation_notice( $old_hook, $new_hook );
					static::trigger_action( $old_hook, $callback_args );
				}
			},
			static::$legacy_hook_priority,
			static::$max_arguments
		);
	}

	/**
	 * Set up the firing of the deprecated filter on the new filter.
	 *
	 * @param string $old_hook Name of deprecated hook.
	 * @param string $new_hook Name of replacement hook.
	 */
	private static function hook_into_new_filter( $old_hook, $new_hook ) {
		add_filter(
			$new_hook,
			function() use ( $old_hook, $new_hook ) {
				$callback_args = func_get_args();
				$return_value  = $callback_args[0];

				if ( has_filter( $old_hook ) ) {
					static::trigger_deprecation_notice( $old_hook, $new_hook );
					$return_value = static::trigger_filter( $old_hook, $callback_args );
				}
				return $return_value;
			},
			static::$legacy_hook_priority,
			static::$max_arguments
		);
	}

	/**
	 * Fires the deprecated action.
	 *
	 * @param string $old_hook      Name of deprecated action.
	 * @param array  $callback_args Arguments passed to the action.
	 */
	protected static function trigger_action( $old_hook, $callback_args ) {
		// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.DynamicHooknameFound -- Used for deprecations.
		do_action_ref_array( $old_hook, $callback_args );
	}

	/**
	 * Fires the deprecated filter.
	 *
	 * @param string $old_hook      Name of deprecated filter.
	 * @param array  $callback_args Arguments passed to the filter.
	 * @return mixed
	 */
	protected static function trigger_filter( $old_hook, $callback_args ) {
		// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.DynamicHooknameFound -- Used for deprecations.
		return apply_filters_ref_array( $old_hook, $callback_args );
	}

	/**
	 * Triggers deprecation notices on hooks without replacements.
	 */
	public static function check_hooks() {
		foreach ( static::$deprecated_hooks as $old_hook => $settings ) {
			if ( ! empty( $settings['replacement'] ) ) {
				// Hooks with replacements have deprecation notice logged the new hook is called.
				continue;
			}
			if (
				( self::TYPE_ACTION === $settings['type'] && has_action( $old_hook ) )
				|| ( self::TYPE_FILTER === $settings['type'] && has_filter( $old_hook ) )
			) {
				static::trigger_deprecation_notice( $old_hook );
			}
		}
	}

	/**
	 * Trigger the deprecation notice for the old hook.
	 *
	 * @param string      $old_hook Name of the deprecated hook.
	 * @param string|null $new_hook Name of the replacement hook (null if none).
	 */
	protected static function trigger_deprecation_notice( $old_hook, $new_hook = null ) {
		$version = isset( static::$deprecated_hooks[ $old_hook ]['version'] ) ? static::$deprecated_hooks[ $old_hook ]['version'] : '0.0.0';
		$message = isset( static::$deprecated_hooks[ $old_hook ]['message'] ) ? static::$deprecated_hooks[ $old_hook ]['message'] : null;

		_deprecated_hook( esc_html( $old_hook ), esc_html( $version ), esc_html( $new_hook ), esc_html( $message ) );
	}

}
