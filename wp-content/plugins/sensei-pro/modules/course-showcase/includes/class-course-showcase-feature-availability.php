<?php
/**
 * File containing the Course_Showcase_Feature_Availability class.
 *
 * @package sensei-pro
 * @since   1.12.0
 */

namespace Sensei_Pro\Course_Showcase;

use SenseiLMS_Licensing\License_Manager;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class responsible for contacting SenseiLMS.com and checking if the feature is enabled, and what the rules for its
 * usages are.
 *
 * @since 1.12.0
 */
class Course_Showcase_Feature_Availability {
	/**
	 * The transient name.
	 *
	 * @var string
	 */
	private const TRANSIENT_NAME = 'sensei_pro_course_showcase_feature_availability';

	/**
	 * Time until expiration of the transient in seconds.
	 *
	 * @var int
	 */
	private const TRANSIENT_EXPIRATION = DAY_IN_SECONDS;

	/**
	 * Time until expiration of the transient in seconds in case of an error.
	 */
	private const TRANSIENT_EXPIRATION_ERROR = MINUTE_IN_SECONDS;

	/**
	 * Singleton instance.
	 *
	 * @var Course_Showcase_Feature_Availability
	 */
	private static $instance;

	/**
	 * Mapper for the SenseiLMS.com API.
	 *
	 * @var Course_Showcase_SenseiLMSCom_Mapper
	 */
	private $mapper;

	/**
	 * Error code representing that the tab for the feature should be hidden.
	 */
	public const ERROR_HIDE = 'HIDE';

	/**
	 * Error code representing that the feature is temporarily unavailable.
	 */
	public const ERROR_TEMPORARY = 'TEMPORARY_ERROR';

	/**
	 * Error code representing that a license is required to access the feature.
	 */
	public const ERROR_LICENSE_REQUIRED = 'LICENSE_REQUIRED';

	/**
	 * Error code representing that the license provided is invalid, and hence the feature cannot be accessed.
	 */
	public const ERROR_LICENSE_INVALID = 'LICENSE_INVALID';

	/**
	 * Error code representing that a specific version of the API is required to access the feature,
	 * and the current version being used is not compatible.
	 */
	public const ERROR_VERSION_REQUIRED = 'VERSION_REQUIRED';

	/**
	 * Error code representing that the feature is not available because an update is required,
	 * and the current version being used is outdated.
	 */
	public const ERROR_UPDATE_REQUIRED = 'UPDATE_REQUIRED';

	/**
	 * Constructor of the class.
	 */
	private function __construct() {
		$this->mapper = new Course_Showcase_SenseiLMSCom_Mapper();
	}

	/**
	 * Fetch an instance of the class.
	 *
	 * @return Course_Showcase_Feature_Availability
	 */
	public static function instance(): Course_Showcase_Feature_Availability {
		if ( ! self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Returns if the feature is available, or an error if the request failed.
	 *
	 * @return bool|\WP_Error
	 */
	public function is_available() {
		$response = $this->load();
		if ( is_wp_error( $response ) ) {
			return $response;
		}
		return $response['available'] ?? false;
	}

	/**
	 * Returns if a given course is eligible for the Showcase feature.
	 *
	 * Eligible means checking if the feature is generally available and, additionally, if the course passes the rules.
	 *
	 * @param int $course_id The course ID.
	 *
	 * @return bool
	 */
	public function is_course_eligible( int $course_id ) {

		// Check general availability of the feature.
		if ( ! $this->is_available() ) {
			return false;
		}

		// Check rules.
		$rules = $this->get_rule_arguments();
		if ( is_wp_error( $rules ) ) {
			// Swallowing error.
			return false;
		}
		foreach ( $rules as $rule_name => $rule_value ) {
			if ( ! $this->check_rule( $rule_name, $rule_value, $course_id ) ) {
				return false;
			}
		}

		// All rules, if any,  were successful.
		return true;
	}

	/**
	 * Returns the reason for the unavailability of the feature, or an error if the request failed.
	 *
	 * @return string|null|\WP_Error
	 */
	public function get_unavailability_reason() {
		$response = $this->load();
		if ( is_wp_error( $response ) ) {
			return $response;
		}
		return $response['unavailability_reason'] ?? self::ERROR_TEMPORARY;
	}

	/**
	 * Return the rules arguments for the feature, as defined in SenseiLMS.com API, or an error if the request failed.
	 * If no rules are found an empty array will be returned.
	 *
	 * @return array|\WP_Error
	 */
	private function get_rule_arguments() {
		$response = $this->load();
		if ( is_wp_error( $response ) ) {
			return $response;
		}
		return $response['rule_args'] ?? [];
	}

	/**
	 * Returns the response from the availability data, or a WP_Error if the request somehow failed.
	 * This method caches the data in a transient.
	 *
	 * @return array|\WP_Error
	 */
	private function load() {
		$transient = get_transient( self::TRANSIENT_NAME );
		if ( false === $transient ) {
			$transient = $this->request_cached();
			if ( is_wp_error( $transient ) ) {
				return $transient;
			}
			$expiration = self::TRANSIENT_EXPIRATION;
			if ( ! $transient['available'] ) {
				$expiration = self::TRANSIENT_EXPIRATION_ERROR;
			}
			set_transient( self::TRANSIENT_NAME, $transient, $expiration );
		}
		return $transient;
	}

	/**
	 * Retrieves the cached response for a request, if available. Otherwise, retrieves the response from the API,
	 * caches for 15 seconds, then returns.
	 *
	 * @return array|\WP_Error Array containing the response data or an error if the request failed.
	 */
	private function request_cached() {
		// Attempt to retrieve the cached response.
		$response = wp_cache_get( self::TRANSIENT_NAME, 'sensei/temporary' );

		// If the cached response is not found, make the request and cache the response.
		if ( false === $response ) {
			$response = $this->request();

			// Cache the response for 15 seconds.
			wp_cache_set( self::TRANSIENT_NAME, $response, 'sensei/temporary', 15 );
		}

		return $response;
	}

	/**
	 * Requests the SenseiLMS.com API for the feature availability data. This method doesn't cache data.
	 *
	 * @return array|\WP_Error Array containing the response data or an error if the request failed.
	 */
	private function request() {
		$license = $this->mapper->get_license_parameter();
		if ( null === $license ) {
			// Return early if there's no license.
			return [
				'available'             => false,
				'unavailability_reason' => self::ERROR_LICENSE_REQUIRED,
				'rule_args'             => [],
			];
		}
		$url      = License_Manager::get_api_url() . '/course-showcase/v1/availability?';
		$url     .= http_build_query(
			[
				'license'            => $license,
				'sensei_pro_version' => SENSEI_PRO_VERSION,
			]
		);
		$response = wp_safe_remote_get(
			$url,
			[
				'timeout' => 15,
			]
		);
		return $this->mapper->decode_response( $response );
	}

	/**
	 * Check for a specific rule on a specific course.
	 *
	 * @param string $rule_name The rule name.
	 * @param mixed  $rule_arg  The rule argument.
	 * @param int    $course_id
	 *
	 * @return bool Whether the rule is satisfied or not.
	 */
	private function check_rule( string $rule_name, $rule_arg, int $course_id ): bool {
		switch ( $rule_name ) {
			case 'min_students':
				// $rule_arg is the minimum number of students.
				$students = \Sensei_Utils::sensei_check_for_activity(
					[
						'post_id' => $course_id,
						'type'    => 'sensei_course_status',
						'status'  => 'any',
					]
				);
				return $students >= $rule_arg;
			case 'last_activity':
				// $rule_arg is a relative date like "-30 days" following the `strtotime()` format.
				$recent_activity = \Sensei_Utils::sensei_check_for_activity(
					[
						'post_id'    => $course_id,
						'type'       => 'all',
						'status'     => 'any',
						'number'     => 1,
						'date_query' => [
							[
								'after'     => $rule_arg,
								'inclusive' => true,
							],
						],
					]
				);
				return ! empty( $recent_activity );
		}

		return false;
	}
}
