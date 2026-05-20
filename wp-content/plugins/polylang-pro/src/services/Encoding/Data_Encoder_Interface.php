<?php
/**
 * @package Polylang-Pro
 */

namespace WP_Syntex\Polylang_Pro\Services\Encoding;

use WP_Error;

/**
 * Allows to decode and encode data in a particular format.
 *
 * @since 3.8
 */
interface Data_Encoder_Interface {
	/**
	 * Decodes the given data.
	 *
	 * @since 3.8
	 *
	 * @param string $data Data.
	 * @return mixed|WP_Error Decoded data. A `WP_Error` object upon decoding failure.
	 */
	public function decode( string $data );

	/**
	 * Encodes the given data.
	 *
	 * @since 3.8
	 *
	 * @param mixed $data Data.
	 * @return string|WP_Error Encoded data. A `WP_Error` object upon encoding failure.
	 */
	public function encode( $data );
}
