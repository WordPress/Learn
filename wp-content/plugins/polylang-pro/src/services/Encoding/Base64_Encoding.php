<?php
/**
 * @package Polylang-Pro
 */

namespace WP_Syntex\Polylang_Pro\Services\Encoding;

use WP_Error;

/**
 * Allows to decode and encode data in base64 format.
 *
 * @since 3.8
 */
class Base64_Encoding implements Data_Encoder_Interface {
	/**
	 * Name of the format.
	 *
	 * @since 3.8
	 */
	public const NAME = 'base64';

	/**
	 * Decodes the given base64 data.
	 *
	 * @since 3.8
	 *
	 * @param string $data Data.
	 * @return mixed|WP_Error Decoded data. A `WP_Error` object upon decoding failure.
	 */
	public function decode( string $data ) {
		$decoded = base64_decode( $data, true );

		if ( false === $decoded ) {
			return new WP_Error(
				'pll-base64-decoding-error',
				sprintf(
					/* translators: %s is a format. */
					__( 'Error while decoding from format "%s".', 'polylang-pro' ),
					self::NAME
				)
			);
		}

		return $decoded;
	}

	/**
	 * Encodes the given data to base64.
	 *
	 * @since 3.8
	 *
	 * @param mixed $data Data.
	 * @return string|WP_Error Encoded data. A `WP_Error` object upon decoding failure.
	 */
	public function encode( $data ) {
		if ( ! is_string( $data ) ) {
			return new WP_Error(
				'pll-base64-encoding-not-a-string',
				sprintf(
					/* translators: %s is a format. */
					__( 'Error while encoding to format "%s".', 'polylang-pro' ),
					self::NAME
				)
			);
		}

		return base64_encode( $data );
	}
}
