<?php
/**
 * @package Polylang-Pro
 */

namespace WP_Syntex\Polylang_Pro\Services\Encoding;

use WP_Error;

/**
 * Allows to decode and encode data in multiple formats.
 *
 * @since 3.6
 * @since 3.8 Renamed and namespaced.
 *            New formats `base64` and `urlencode`.
 *            Handle multiple formats at once.
 *
 * @phpstan-type Formats ''|'base64'|'json'|'serialize'|'urlencode'
 */
class Data_Encoding {
	/**
	 * Encoding formats.
	 *
	 * @var array
	 */
	private $formats;

	/**
	 * Constructor.
	 *
	 * @since 3.6
	 * @since 3.8 Parameter `$format` is renamed into `$formats` and allows a commat-separated list of formats.
	 *
	 * @param string $formats Comma-separated list of encoding formats. Possible values are:
	 *    - `base64`.
	 *    - `json`.
	 *    - `serialize`.
	 *    - `urlencode`.
	 *    An empty string will fall back to `serialize`. Default is `serialize`.
	 */
	public function __construct( string $formats = 'serialize' ) {
		$formats       = explode( ',', $formats );
		$formats       = array_map( 'trim', $formats );
		$formats       = array_filter( $formats );
		$formats       = array_values( $formats );
		$this->formats = ! empty( $formats ) ? $formats : array( 'serialize' );
	}

	/**
	 * Tells if the given format is `serialize` (only), which is what WP uses by default for the metas.
	 *
	 * @since 3.8
	 *
	 * @param string $format Encoding format(s).
	 * @return bool
	 */
	public static function is_serialize( string $format ): bool {
		$format = trim( $format, ', ' );
		return '' === $format || Serialize_Encoding::NAME === $format;
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
		foreach ( array_reverse( $this->formats ) as $format ) {
			if ( ! is_string( $data ) ) {
				return new WP_Error(
					'pll-decoding-not-a-string',
					__( 'The given data cannot be decoded because it is not a string.', 'polylang-pro' )
				);
			}

			$encoder = $this->get_encoder( $format );

			if ( is_wp_error( $encoder ) ) {
				return $encoder;
			}

			$data = $encoder->decode( $data );

			if ( is_wp_error( $data ) ) {
				return $data;
			}
		}

		return $data;
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
		foreach ( $this->formats as $format ) {
			$encoder = $this->get_encoder( $format );

			if ( is_wp_error( $encoder ) ) {
				return $encoder;
			}

			$data = $encoder->encode( $data );

			if ( is_wp_error( $data ) ) {
				return $data;
			}
		}

		return $data;
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
	 * Returns an instance of a format encoder.
	 *
	 * @since 3.8
	 *
	 * @param string $format A format "name".
	 * @return Data_Encoder_Interface|WP_Error
	 */
	private function get_encoder( string $format ) {
		$encoding_classes = array(
			Base64_Encoding::NAME    => Base64_Encoding::class,
			Json_Encoding::NAME      => Json_Encoding::class,
			Serialize_Encoding::NAME => Serialize_Encoding::class,
			Url_Encoding::NAME       => Url_Encoding::class,
		);

		if ( ! empty( $encoding_classes[ $format ] ) ) {
			$class = $encoding_classes[ $format ];
			return new $class();
		}

		/**
		 * Allows to add encoding formats.
		 *
		 * @since 3.8
		 *
		 * @param Data_Encoder_Interface|null $encoder Instance of a format coder.
		 * @param string                      $format  The name of the requested format.
		 */
		$encoder = apply_filters( 'pll_data_encoder', null, $format );

		if ( $encoder instanceof Data_Encoder_Interface ) {
			return $encoder;
		}

		return new WP_Error(
			'pll-encoding-unknown-format',
			sprintf(
				/* translators: %s is a format. */
				__( 'Unknown data encoding format "%s".', 'polylang-pro' ),
				$format
			)
		);
	}
}
