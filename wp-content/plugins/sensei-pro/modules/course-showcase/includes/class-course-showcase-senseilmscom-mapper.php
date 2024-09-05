<?php
/**
 * File containing the Course_Showcase_Mapper class.
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
 * Class responsible for mapping and validating data received from and sent to SenseiLMS.com.
 *
 * @since 1.12.0
 */
class Course_Showcase_SenseiLMSCom_Mapper {
	const REMOTE_STATUS_PUBLISH      = 'publish';
	const REMOTE_STATUS_PENDING      = 'pending';
	const REMOTE_STATUS_INACCESSIBLE = 'inaccessible';
	const REMOTE_STATUS_REJECTED     = 'rejected';

	const NOT_SUBMITTED_STATUS = [
		'status'      => 'not-submitted',
		'status_code' => null,
	];

	/**
	 * Encode a course showcase listing to the format accepted by SenseiLMS.com.
	 *
	 * @param int|\WP_Post|null $post The course showcase listing to be encoded. If null, the current post will be used.
	 * @internal
	 * @return array An array with the data to be submitted to the SenseiLMS.com API.
	 */
	public function map_listing( $post ) {
		$post = get_post( $post );
		if ( ! $post ) {
			return [
				'remote_course_id'   => -1,
				'title'              => '',
				'description'        => '',
				'is_paid'            => false,
				'category'           => '',
				'image_url'          => null,
				'locale'             => '',
				'students_number'    => 0,
				'first_publish_date' => null,
				'url'                => '',
				'site_name'          => '',
				'site_icon_url'      => '',
			];
		}
		$course_id     = get_post_meta( $post->ID, '_course', true );
		$course        = get_post( $course_id );
		$media         = get_post_meta( $post->ID, '_media', true );
		$site_icon_url = get_site_icon_url( 192 );

		return [
			'remote_course_id'   => $course_id,
			'title'              => get_post_meta( $post->ID, '_title', true ),
			'description'        => get_post_meta( $post->ID, '_excerpt', true ),
			'is_paid'            => (bool) get_post_meta( $post->ID, '_is_paid', true ),
			'category'           => get_post_meta( $post->ID, '_category', true ),
			'image_url'          => $media ? $media['src'] : null,
			'locale'             => get_post_meta( $post->ID, '_language', true ),
			'students_number'    => $this->get_students_number( $post ),
			'first_publish_date' => null !== $course ? $course->post_date_gmt : null,
			'url'                => null !== $course ? get_post_permalink( $course ) : null,
			'site_name'          => get_bloginfo( 'name' ),
			// TODO Replace with null when supported in API.
			'site_icon_url'      => $site_icon_url ? $site_icon_url : 'https://upload.wikimedia.org/wikipedia/commons/thumb/9/98/WordPress_blue_logo.svg/2048px-WordPress_blue_logo.svg.png',
		];
	}

	/**
	 * Get the check-in fields to send to SenseiLMS.com.
	 *
	 * @param \WP_Post $post The course showcase listing.
	 *
	 * @return array The check-in fields.
	 */
	public function get_check_in_fields( $post ) {
		return [
			'students_number' => $this->get_students_number( $post ),
		];
	}

	/**
	 * Get the number of students for the course associated with a course showcase listing.
	 *
	 * @param \WP_Post $post The course showcase listing.
	 *
	 * @return int The number of students.
	 */
	private function get_students_number( $post ) {
		$course_id = get_post_meta( $post->ID, '_course', true );

		if ( ! $course_id ) {
			return 0;
		}

		return \Sensei_Utils::sensei_check_for_activity(
			[
				'post_id' => $course_id,
				'type'    => 'sensei_course_status',
				'status'  => 'any',
			]
		);
	}

	/**
	 * Validates whether the data is valid to be submitted to the API.
	 *
	 * @param array $listing_array The data to be submitted.
	 * @internal
	 * @return \WP_Error|null The error if any, or null if the data is valid.
	 */
	public function validate_listing( $listing_array ) {
		if ( ! array_key_exists( 'remote_course_id', $listing_array ) || $listing_array['remote_course_id'] <= 0 ) {
			return $this->create_validation_error( __( 'The submission must be associated to a course.', 'sensei-pro' ) );
		}
		if ( ! array_key_exists( 'title', $listing_array ) || empty( $listing_array['title'] ) ) {
			return $this->create_validation_error( __( 'Title is required', 'sensei-pro' ) );
		}
		if ( ! array_key_exists( 'description', $listing_array ) || empty( $listing_array['description'] ) ) {
			return $this->create_validation_error( __( 'Description is required', 'sensei-pro' ) );
		}
		if ( ! array_key_exists( 'category', $listing_array ) || empty( $listing_array['category'] ) ) {
			return $this->create_validation_error( __( 'Category is required', 'sensei-pro' ) );
		}
		if ( ! array_key_exists( 'locale', $listing_array ) || empty( $listing_array['locale'] ) ) {
			return $this->create_validation_error( __( 'Language is required', 'sensei-pro' ) );
		}
		if ( ! array_key_exists( 'image_url', $listing_array ) || empty( $listing_array['image_url'] ) ) {
			return $this->create_validation_error( __( 'Image is required', 'sensei-pro' ) );
		}
		return null;
	}

	/**
	 * Create a validation error.
	 *
	 * @param string $message The message for the validation error.
	 * @internal
	 * @return \WP_Error The error to be returned.
	 */
	private function create_validation_error( $message ) {
		return new \WP_Error( 'sensei_validation_error', $message );
	}

	/**
	 * Decodes the response from the SenseiLMS.com API.
	 *
	 * @param array|\WP_Error|\Requests_Response|\WpOrg\Requests\Response $response The response returned by the SenseiLMS.com API.
	 * @internal
	 * @return array|\WP_Error The decoded response, or an error if the response is not valid.
	 */
	public function decode_response( $response ) {
		if ( is_wp_error( $response ) ) {
			return $response;
		}

		// Standardize the response to be the array output from a WP_HTTP_Requests_Response.
		if ( $response instanceof \Requests_Response || $response instanceof \WpOrg\Requests\Response ) {
			$response = ( new \WP_HTTP_Requests_Response( $response ) )->to_array();
		}

		$body          = wp_remote_retrieve_body( $response );
		$json_response = \json_decode( $body, true );
		$is_valid      = is_array( $json_response );
		if ( 200 !== wp_remote_retrieve_response_code( $response ) || ! $is_valid ) {
			$code    = 'senseilmscom_error';
			$message = 'Unexpected Internal Server Error';
			if ( $is_valid && array_key_exists( 'code', $json_response ) && array_key_exists( 'message', $json_response ) ) {
				$code    = $json_response['code'];
				$message = $this->get_status_code_label( $code, esc_html( $json_response['message'] ) );
			}
			return new \WP_Error( $code, $message );
		}
		return $json_response;
	}

	/**
	 * Returns the fields to be saved in the post meta related to the listing.
	 *
	 * @param array $json_response The array containing all the fields in the response.
	 * @internal
	 * @return array The new array to be saved in the post meta.
	 */
	public function decode_listing_response( $json_response ) {
		return [
			'id'               => $json_response['listing']['id'],
			'secret_key'       => $json_response['listing']['secret_key'],
			'verification_key' => $json_response['listing']['verification_key'],
		];
	}

	/**
	 * Returns the fields to be cached related to the listing status.
	 *
	 * @internal
	 *
	 * @param array $json_response The array containing all the fields in the response.
	 *
	 * @return array The array of status related fields to cache.
	 */
	public function map_listing_status( $json_response ) {
		return [
			'status'      => $json_response['listing']['status'] ?? null,
			'status_code' => $json_response['listing']['status_code'] ?? null,
		];
	}

	/**
	 * Returns the license parameter for the SenseiLMS.com API.
	 *
	 * @internal
	 * @return array|null The license parameter to pass to the API, or null if no license data was found.
	 */
	public function get_license_parameter() {
		if ( defined( 'SENSEI_COMPAT_PLUGIN' ) && SENSEI_COMPAT_PLUGIN ) {
			$license = $this->get_woothemes_sensei_license();
		} else {
			$license = $this->get_sensei_pro_license();
		}
		return $license;
	}

	/**
	 * Get license data associated with sensei-pro.
	 *
	 * @return array|null The license data if any is available, or null if no data was found.
	 */
	private function get_sensei_pro_license() {
		$license = License_Manager::get_license_status( 'sensei-pro' );
		if ( ! $license['is_valid'] ) {
			// License data is not valid, return early.
			return null;
		}

		return [
			'type'     => 'senseilmscom',
			'key'      => $license['license_key'],
			'site_url' => network_site_url(),
		];
	}

	/**
	 * Get license data associated with woothemes-sensei using WooCommerce's API.
	 *
	 * @return array|null The license data if any is available, or null if no data was found.
	 */
	private function get_woothemes_sensei_license() {
		if ( ! \Sensei_Utils::is_woocommerce_active() || ! class_exists( '\Sensei_Compat_Admin' ) ) {
			// WooCommerce is not active, or this isn't woothemes-sensei, return early.
			return null;
		}
		$subscriptions = \WC_Helper::get_subscriptions();
		$auth          = \WC_Helper_Options::get( 'auth' );
		if ( ! $auth || empty( $subscriptions ) ) {
			return null;
		}

		$site_id_str = $auth['site_id'];
		$site_id_int = absint( $site_id_str );
		foreach ( $subscriptions as $subscription ) {
			$connections = array_key_exists( 'connections', $subscription ) ? $subscription['connections'] : [];
			$in_site     = in_array( $site_id_str, $connections, true );
			$in_site     = $in_site || in_array( $site_id_int, $connections, true );
			if ( \Sensei_Compat_Admin::WOO_PRODUCT_ID === $subscription['product_id'] && $in_site ) {
				// subscription for woothemes-sensei found, return the license data.
				return [
					'type'     => 'wccom',
					'key'      => $subscription['product_key'],
					'site_url' => get_home_url(),
				];
			}
		}
		return null;
	}

	/**
	 * Get the status label.
	 *
	 * @param string $status The status slug.
	 *
	 * @return string
	 */
	public function get_listing_status_label( $status ): string {
		$labels = [
			self::NOT_SUBMITTED_STATUS['status'] => __( 'Not Submitted', 'sensei-pro' ),
			self::REMOTE_STATUS_PENDING          => __( 'Review Pending', 'sensei-pro' ),
			self::REMOTE_STATUS_PUBLISH          => __( 'Approved', 'sensei-pro' ),
			self::REMOTE_STATUS_REJECTED         => __( 'Rejected', 'sensei-pro' ),
		];

		return $labels[ $status ] ?? __( 'Unknown', 'sensei-pro' );
	}

	/**
	 * Get the error and status code label.
	 *
	 * @param string  $status_code   The status code slug.
	 * @param ?string $default_label The default label to return.
	 *
	 * @return string
	 */
	public function get_status_code_label( $status_code, $default_label = null ) {
		$labels = [
			// System errors.
			'failed-verification'         => __( 'The course could not be verified.', 'sensei-pro' ),
			'not-reachable'               => __( 'The course was not accessible from SenseiLMS.com\'s server.', 'sensei-pro' ),

			// Moderation errors.
			'not-relevant'                => __( 'The course was found to not be relevant to Sensei Showcase.', 'sensei-pro' ),
			'not-unique'                  => __( 'The course (or one very similar) was already submitted to Sensei Showcase.', 'sensei-pro' ),
			'tos-violation'               => __( 'The course is in violation of SenseiLMS.com\'s Terms of Service.', 'sensei-pro' ),
			'over-usage-limit'            => __( 'This site has submitted too many listings to the Sensei Showcase. Please delete some and try again.', 'sensei-pro' ),
			'other'                       => __( 'The listing was rejected from the Sensei Showcase.', 'sensei-pro' ),

			// Endpoint status errors.
			'invalid_listing_id'          => __( 'The remote listing was not found.', 'sensei-pro' ),
			'too_many_requests'           => __( 'Too many requests have been made to SenseiLMS.com. Please wait and try again later.', 'sensei-pro' ),
			'temporarily_not_available'   => __( 'Sensei Showcase submissions are temporarily not available. Please try again later.', 'sensei-pro' ),
			'course_image_download_error' => __( 'The course image could not be downloaded. Is it publicly available?', 'sensei-pro' ),
			'site_icon_download_error'    => __( 'The site icon could not be downloaded. Is it publicly available?', 'sensei-pro' ),
		];

		if ( null === $default_label ) {
			$default_label = __( 'Unknown', 'sensei-pro' );
		}

		return $labels[ $status_code ] ?? $default_label;
	}
}
