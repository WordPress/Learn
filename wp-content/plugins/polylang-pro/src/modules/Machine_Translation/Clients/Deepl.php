<?php
/**
 * @package Polylang Pro
 */

namespace WP_Syntex\Polylang_Pro\Modules\Machine_Translation\Clients;

use WP_Error;
use PLL_Language;
use Translations;
use WP_Syntex\Polylang_Pro\Modules\Machine_Translation\Services\Deepl as Service;

/**
 * Class for DeepL machine translation client.
 *
 * @since 3.6
 *
 * @phpstan-import-type DeeplFormality from Service
 */
class Deepl implements Client_Interface {
	const ROUTE      = 'https://api.deepl.com/';
	const ROUTE_FREE = 'https://api-free.deepl.com/';

	/**
	 * The authentication key to access the DeepL API.
	 *
	 * @var string
	 */
	private $api_key;

	/**
	 * The DeepL formality option.
	 *
	 * @var string
	 */
	private $formality;

	/**
	 * ID of the glossary to use.
	 *
	 * @var string
	 */
	private $glossary_id;

	/**
	 * Constructor.
	 *
	 * @since 3.6
	 * @since 3.8 The option key `glossary_id` has been added.
	 *
	 * @param array $options {
	 *    The service options.
	 *
	 *    @type string $api_key     The API key.
	 *    @type string $formality   Sets whether the translated text should lean towards formal or informal language.
	 *                              Possible values are `'default'`, `'prefer_more'` (formal if available), and `'prefer_less'` (informal if available).
	 *    @type string $glossary_id Optional. ID of the glossary to use. Default is an empty string
	 * }
	 *
	 * @phpstan-param array{
	 *    api_key: string,
	 *    formality?: DeeplFormality,
	 *    glossary_id?: string
	 * } $options
	 */
	public function __construct( array $options ) {
		$this->api_key     = $options['api_key'];
		$this->formality   = $options['formality'] ?? 'default';
		$this->glossary_id = $options['glossary_id'] ?? '';
	}

	/**
	 * Performs a request to machine translation service.
	 *
	 * @since 3.6
	 *
	 * @param Translations      $translations    Translations object.
	 * @param PLL_Language      $target_language Target language.
	 * @param PLL_Language|null $source_language Source language, leave empty for automatic detection.
	 * @return Translations|WP_Error
	 */
	public function translate( Translations $translations, PLL_Language $target_language, $source_language = null ) {
		$tr_strings = array();
		foreach ( $this->split( $translations ) as $batch ) {
			$result = $this->translate_batch( $batch, $target_language, $source_language );

			if ( is_wp_error( $result ) ) {
				// Abort early.
				return $result;
			}

			if ( count( $result ) !== count( $batch ) ) {
				return new WP_Error( 'pll_deepl_incomplete_response', __( 'The DeepL response is incomplete.', 'polylang-pro' ) );
			}

			array_push( $tr_strings, ...$result );
		}


		foreach ( $translations->entries as &$entry ) {
			$entry->translations = (array) array_shift( $tr_strings );
		}

		return $translations;
	}

	/**
	 * Splits an array of strings into several batches manageable by DeepL API.
	 *
	 * @since 3.6
	 *
	 * @param Translations $translations Translations object with entries to split.
	 * @return string[][] Array of arrays of strings, each ones suitable for DeepL.
	 */
	private function split( Translations $translations ): array {
		$count  = 0;
		$size   = 0;
		$chunk  = array();
		$chunks = array();

		foreach ( $translations->entries as $entry ) {
			$length = strlen( $entry->singular );

			++$count;
			$size += $length;

			/*
			* A DeepL translation request body must not exceed 128 * 1024 bytes according to the documentation.
			* {@see https://www.deepl.com/docs-api/translate-text}. We decrease this limit to 120 * 1024 bytes
			* to account for extra bytes added by the request params (100 bytes) + the JSON encoding of the array.
			*/
			if ( $count > 50 || $size > 120 * \KB_IN_BYTES ) {
				$chunks[] = $chunk;

				$count = 0;
				$size  = $length;
				$chunk = array();
			}

			$chunk[] = $entry->singular;
		}

		$chunks[] = $chunk; // Don't forget the last chunk.

		return $chunks;
	}

	/**
	 * Sends a batch of strings to DeepL and returns their translations in the same order.
	 *
	 * @since 3.6
	 *
	 * @param string[]     $batch           Strings to translate.
	 * @param PLL_Language $target_language Target language.
	 * @param PLL_Language $source_language Source language, `null` for auto-detection.
	 * @return string[]|WP_Error
	 */
	private function translate_batch( $batch, PLL_Language $target_language, $source_language = null ) {
		$target_code = Service::get_target_code( $target_language );
		if ( empty( $target_code ) ) {
			return new WP_Error(
				'pll_deepl_target_language_unavailable',
				sprintf(
					/* translators: %1$s is a language name, %2$s is a language locale. */
					__( '%1$s (%2$s) is not available as target language with DeepL.', 'polylang-pro' ),
					$target_language->name,
					sprintf(
						'<code>%s</code>',
						$target_language->locale
					)
				),
				'warning'
			);
		}

		$body = array(
			'target_lang'     => $target_code,
			'tag_handling'    => 'html',
			'split_sentences' => '1',
			'formality'       => $this->get_formality( $target_language ),
			'text'            => $batch,
		);

		if ( ! empty( $this->glossary_id ) ) {
			$body['glossary_id'] = $this->glossary_id;
		}

		if ( ! empty( $source_language ) ) {
			$source_code = Service::get_source_code( $source_language );
			if ( empty( $source_code ) ) {
				return new WP_Error(
					'pll_deepl_source_language_unavailable',
					sprintf(
						/* translators: %1$s is a language name, %2$s is a language locale. */
						__( '%1$s (%2$s) is not available as source language with DeepL.', 'polylang-pro' ),
						$source_language->name,
						sprintf(
							'<code>%s</code>',
							$source_language->locale
						)
					),
					'warning'
				);
			}
			$body['source_lang'] = $source_code;
		}

		$headers = array(
			'Content-Type' => 'application/json',
		);

		$response = $this->request(
			'POST',
			'v2/translate',
			array(
				'headers' => $headers,
				'body'    => wp_json_encode( $body ),
			)
		);

		if ( $this->should_retry_with_empty_glossary( $response ) ) {
			$this->glossary_id = '';

			return $this->translate_batch( $batch, $target_language, $source_language );
		}

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$body = json_decode( $response['body'], true );

		if ( ! is_array( $body ) || empty( $body['translations'] ) ) {
			return new WP_Error( 'pll_deepl_no_translations', __( 'No translations have been returned by DeepL.', 'polylang-pro' ) );
		}

		$tr_strings = array();
		foreach ( $body['translations']  as $translation ) {
			if ( isset( $translation['text'] ) ) {
				$tr_strings[] = $translation['text'];
			}
		}

		return $tr_strings;
	}

	/**
	 * Sends the request to the client, and returns a response or a `WP_Error` in case of failure.
	 *
	 * @since 3.6
	 *
	 * @param string $method   The HTTP method to use.
	 * @param string $endpoint The API endpoint.
	 * @param array  $args     {
	 *     The request arguments.
	 *
	 *     @type array  $headers Optional. Headers to send. Default is an empty array.
	 *     @type string $body    Optional. A JSON-encoded string of data. Nothing is sent by default.
	 * }
	 * @return array|WP_Error
	 */
	private function request( string $method, string $endpoint, array $args = array() ) {
		if ( empty( $this->api_key ) ) {
			// No need to contact DeepL if the API key is empty.
			return $this->check_status_code( 403 );
		}

		$args = array_merge_recursive(
			array(
				'headers' => array(
					'Authorization' => 'DeepL-Auth-Key ' . $this->api_key,
				),
				'method'  => $method,
			),
			$args
		);

		$response = wp_remote_request(
			$this->get_route( $endpoint ),
			$args
		);

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$status_code_error = $this->check_status_code(
			$response['response']['code'],
			$response['body'] ?? ''
		);
		if ( $status_code_error->has_errors() ) {
			return $status_code_error;
		}

		if ( empty( $response['body'] ) ) {
			return new WP_Error( 'pll_deepl_empty_response_body', __( 'No translations have been returned by DeepL.', 'polylang-pro' ) );
		}

		return $response;
	}

	/**
	 * Checks the HTTP status code.
	 *
	 * @see https://github.com/DeepLcom/deepl-php/blob/v1.6.0/src/Translator.php#L691
	 *
	 * @since 3.6
	 * @since 3.8 The parameter `body` has been added.
	 *
	 * @param int    $code The HTTP response code.
	 * @param string $body The response body.
	 * @return WP_Error
	 */
	protected function check_status_code( int $code, string $body = '' ): WP_Error {
		if ( 200 === $code ) {
			return new WP_Error();
		}

		switch ( $code ) {
			case 403:
				return new WP_Error( 'pll_deepl_authentication_failure', __( 'Authentication failure. Please check your DeepL authentication key.', 'polylang-pro' ) );
			case 456:
				return new WP_Error( 'pll_deepl_quota_exceeded', __( 'The DeepL quota for this billing period has been exceeded.', 'polylang-pro' ) );
			case 404:
				return new WP_Error( 'pll_deepl_not_found', __( 'The DeepL server cannot be reached.', 'polylang-pro' ) );
			case 400:
				if ( preg_match( '/No dictionary found for language pair [A-Za-z0-9-]+ in glossary [a-f0-9-]+/', $body ) ) {
					return new WP_Error( 'pll_deepl_no_language_pair_in_glossary', __( 'No language pair found in glossary.', 'polylang-pro' ) );
				}

				return new WP_Error( 'pll_deepl_bad_request', __( 'Bad request.', 'polylang-pro' ) );
			case 429:
				return new WP_Error( 'pll_deepl_too_many_request', __( 'Too many requests, DeepL servers are currently experiencing high load.', 'polylang-pro' ) );
			case 500:
			case 502:
			case 503:
				return new WP_Error( 'pll_deepl_service_unavailable', __( 'DeepL service unavailable.', 'polylang-pro' ) );
			default:
				/* translators: %s is an HTTP status code */
				return new WP_Error( 'pll_deepl_unexpected_status_code', sprintf( __( 'The DeepL server sent an unexpected status code %d.', 'polylang-pro' ), $code ) );
		}
	}

	/**
	 * Tells whether API key is valid.
	 *
	 * @since 3.6
	 *
	 * @return WP_Error An empty WP_Error if valid, a filled WP_Error otherwise.
	 */
	public function is_api_key_valid(): WP_Error {
		$response = $this->request( 'GET', 'v2/usage' );
		if ( is_wp_error( $response ) ) {
			return $response;
		}

		if ( 200 === $response['response']['code'] ) {
			return new WP_Error();
		}

		return $this->check_status_code( 403 );
	}

	/**
	 * Returns current machine translation usage.
	 *
	 * @since 3.6
	 *
	 * @return array|WP_Error {
	 *    A `WP_Error` on error, or an array on success with the following keys.
	 *
	 *    @type int $character_count Character count.
	 *    @type int $character_limit Character limiter.
	 * }
	 *
	 * @phpstan-return array{
	 *    character_count: int<0, max>,
	 *    character_limit: int<0, max>
	 * }|WP_Error
	 */
	public function get_usage() {
		$response = $this->request( 'GET', 'v2/usage' );
		if ( is_wp_error( $response ) ) {
			return $response;
		}

		/** @var array{
		 *    character_count: int<0, max>,
		 *    character_limit: int<0, max>
		 *   }
		 */
		$usage = array_merge(
			array(
				'character_count' => 0,
				'character_limit' => 0,
			),
			(array) json_decode( $response['body'], true )
		);

		if ( $usage['character_limit'] >= pow( 10, 12 ) ) {
			// Usage limit for the "unlimited" plan returns 10^12.
			$usage['character_limit'] = 0;
		}

		return array(
			'character_count' => max( 0, (int) $usage['character_count'] ),
			'character_limit' => max( 0, (int) $usage['character_limit'] ),
		);
	}

	/**
	 * Returns the list of glossaries.
	 *
	 * @since 3.8
	 *
	 * @return array|WP_Error A `WP_Error` on error, or an array on success with glossary IDs as array keys and glossary names as array values.
	 *
	 * @phpstan-return array<non-falsy-string, non-empty-string>|WP_Error
	 */
	public function get_glossaries() {
		$response = $this->request( 'GET', 'v3/glossaries' );
		if ( is_wp_error( $response ) ) {
			return $response;
		}

		/** @var array{ glossaries: array<array{glossary_id: non-falsy-string, name: non-empty-string, dictionaries: list<array{source_lang: non-falsy-string, target_lang: non-falsy-string, entry_count: int<1, max>}>, creation_time: non-falsy-string}> } */
		$data = array_merge(
			array(
				'glossaries' => array(),
			),
			(array) json_decode( $response['body'], true )
		);

		return array_column( $data['glossaries'], 'name', 'glossary_id' );
	}

	/**
	 * Returns the route to be used according to the DeepL plan.
	 *
	 * @since 3.6
	 * @since 3.8 The API version (`v2` or `v3`) must be prepended to the endpoint.
	 *
	 * @param string $endpoint The API endpoint.
	 *
	 * @return string
	 */
	public function get_route( string $endpoint ): string {
		return ( $this->is_free_plan() ? self::ROUTE_FREE : self::ROUTE ) . $endpoint;
	}

	/**
	 * Tells if the key comes from a free plan or not.
	 *
	 * @See https://www.deepl.com/fr/docs-api/api-access/authentication
	 *
	 * @since 3.6
	 *
	 * @return bool True if the key is associated to a free plan, false otherwise.
	 */
	private function is_free_plan(): bool {
		return substr( $this->api_key, -3 ) === ':fx';
	}

	/**
	 * Gets the formality according to the formality of the locale in priority.
	 *
	 * @since 3.6
	 *
	 * @param PLL_Language $language The language object.
	 * @return string
	 */
	private function get_formality( PLL_Language $language ): string {
		if ( str_ends_with( $language->locale, '_formal' ) ) {
			return 'prefer_more';
		}

		if ( str_ends_with( $language->locale, '_informal' ) ) {
			return 'prefer_less';
		}

		return $this->formality;
	}

	/**
	 * Tells whether to retry the request with an empty glossary.
	 *
	 * @since 3.8
	 *
	 * @param array|WP_Error $error The potential error. Array for valid responses.
	 * @return bool
	 */
	private function should_retry_with_empty_glossary( $error ): bool {
		return is_wp_error( $error ) && 'pll_deepl_no_language_pair_in_glossary' === $error->get_error_code();
	}
}
