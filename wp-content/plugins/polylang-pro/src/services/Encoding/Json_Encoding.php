<?php
/**
 * @package Polylang-Pro
 */

namespace WP_Syntex\Polylang_Pro\Services\Encoding;

use WP_Error;

/**
 * Allows to decode and encode data in JSON format.
 *
 * @since 3.8
 */
class Json_Encoding implements Data_Encoder_Interface {
	/**
	 * Name of the format.
	 *
	 * @since 3.8
	 */
	public const NAME = 'json';

	/**
	 * Decodes the given JSON data.
	 *
	 * @since 3.8
	 *
	 * @param string $data Data.
	 * @return mixed|WP_Error Decoded data. A `WP_Error` object upon decoding failure.
	 */
	public function decode( string $data ) {
		$decoded = json_decode( $data, true );

		if ( json_last_error() !== \JSON_ERROR_NONE ) {
			return new WP_Error(
				'pll-json-decoding-error',
				sprintf(
					/* translators: %1$s is a format, %2$s is an error message. */
					__( 'Error while decoding from format "%1$s": %2$s.', 'polylang-pro' ),
					self::NAME,
					json_last_error_msg()
				)
			);
		}

		return $decoded;
	}

	/**
	 * Encodes the given data to JSON.
	 *
	 * @since 3.8
	 *
	 * @param mixed $data Data.
	 * @return string|WP_Error Encoded data. A `WP_Error` object upon decoding failure.
	 */
	public function encode( $data ) {
		$encoded = wp_json_encode( $data, \JSON_PRESERVE_ZERO_FRACTION ); // Cannot trigger an Exception since we use the default value for `$depth`.

		if ( ! is_string( $encoded ) ) {
			return new WP_Error(
				'pll-json-encoding-error',
				sprintf(
					/* translators: %1$s is a format, %2$s is an error message. */
					__( 'Error while encoding to format "%1$s": %2$s.', 'polylang-pro' ),
					self::NAME,
					json_last_error_msg()
				)
			);
		}

		return $encoded;
	}
}
