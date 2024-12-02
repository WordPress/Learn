<?php
/**
 * @package Polylang-Pro
 */

/**
 * Allows to decode and encode data in a given format.
 *
 * @since 3.6
 *
 * @phpstan-type Formats ''|'serialize'|'json'
 */
class PLL_Data_Encoding {
	/**
	 * Encoding format.
	 *
	 * @var string
	 */
	private $format;

	/**
	 * Constructor.
	 *
	 * @since 3.6
	 *
	 * @param string $format Encoding format. Possible values are:
	 *    - An empty string: to (un)serialize.
	 *    - `serialize`.
	 *    - `json`.
	 *    Default is an empty string.
	 */
	public function __construct( string $format = '' ) {
		$this->format = ! empty( $format ) ? $format : 'serialize';
	}

	/**
	 * Tells if the current format is `serialize`, which is what WP uses by default for the metas.
	 *
	 * @since 3.6
	 *
	 * @return bool
	 */
	public function use_serialize(): bool {
		return 'serialize' === $this->format;
	}

	/**
	 * Decodes the given data.
	 * Returns a `WP_Error` object upon decoding failure.
	 *
	 * @since 3.6
	 *
	 * @param mixed $data Data.
	 * @return mixed|WP_Error Decoded data. A `WP_Error` object upon decoding failure.
	 */
	public function decode( $data ) {
		switch ( $this->format ) {
			case 'json':
				return $this->decode_from_json( $data );

			case 'serialize':
				return maybe_unserialize( $data );

			default:
				return new WP_Error( 'pll-decode-unknown-format', 'Unknown format.' );
		}
	}

	/**
	 * Decodes the given data.
	 * The data is passed by reference and the method returns a `WP_Error` object upon decoding failure.
	 *
	 * @since 3.6
	 *
	 * @param mixed $data Data, passed by reference.
	 * @return WP_Error
	 */
	public function decode_reference( &$data ): WP_Error {
		$value = $this->decode( $data );

		if ( is_wp_error( $value ) ) {
			return $value;
		}

		$data = $value;
		return new WP_Error();
	}

	/**
	 * Encodes the given data.
	 * Returns a `WP_Error` object upon encoding failure.
	 *
	 * @since 3.6
	 *
	 * @param mixed $data Data.
	 * @return mixed Decoded data. A `WP_Error` object upon encoding failure.
	 */
	public function encode( $data ) {
		switch ( $this->format ) {
			case 'json':
				return $this->encode_to_json( $data );

			case 'serialize':
				return maybe_serialize( $data );

			default:
				return new WP_Error( 'pll-encode-unknown-format', 'Unknown format.' );
		}
	}

	/**
	 * Encodes the given data.
	 * The data is passed by reference and the method returns a `WP_Error` object upon encoding failure.
	 *
	 * @since 3.6
	 *
	 * @param mixed $data Data, passed by reference.
	 * @return WP_Error
	 */
	public function encode_reference( &$data ): WP_Error {
		$value = $this->encode( $data );

		if ( is_wp_error( $value ) ) {
			return $value;
		}

		$data = $value;
		return new WP_Error();
	}

	/**
	 * Decodes the given JSON data.
	 *
	 * @since 3.6
	 *
	 * @param mixed $data Data.
	 * @return mixed|WP_Error Decoded data. A `WP_Error` object upon decoding failure.
	 */
	private function decode_from_json( $data ) {
		if ( ! is_string( $data ) ) {
			return new WP_Error( 'pll-json-not-a-string', 'Value is not a string.' );
		}

		$decoded = json_decode( $data, true );

		if ( json_last_error() !== JSON_ERROR_NONE ) {
			return new WP_Error( 'pll-json-decoding-error', json_last_error_msg() );
		}

		return $decoded;
	}

	/**
	 * Encodes the given data to JSON.
	 *
	 * @since 3.6
	 *
	 * @param mixed $data Data.
	 * @return string|WP_Error Encoded data. A `WP_Error` object upon decoding failure.
	 */
	private function encode_to_json( $data ) {
		$encoded = wp_json_encode( $data, JSON_PRESERVE_ZERO_FRACTION ); // Cannot trigger an Exception since we use the default value for `$depth`.

		if ( ! is_string( $encoded ) ) {
			return new WP_Error( 'pll-json-encoding-error', json_last_error_msg() );
		}

		return $encoded;
	}
}
