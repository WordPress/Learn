<?php
/**
 * @package Polylang-Pro
 */

namespace WP_Syntex\Polylang_Pro\Modules\Machine_Translation\Settings;

use PLL_Language;
use PLL_Model;
use WP_Error;
use WP_Syntex\Polylang_Pro\Modules\Machine_Translation\Ajax\Deepl as Ajax;
use WP_Syntex\Polylang_Pro\Modules\Machine_Translation\Clients\Deepl as Client;
use WP_Syntex\Polylang_Pro\Modules\Machine_Translation\Languages;
use WP_Syntex\Polylang_Pro\Modules\Machine_Translation\Services\Deepl as Service;

defined( 'ABSPATH' ) || exit;

/**
 * Machine translation settings: DeepL.
 *
 * @since 3.6
 *
 * @phpstan-type DeeplOptions array{
 *    api_key: string,
 *    formality: 'default'|'prefer_more'|'prefer_less'
 * }
 */
class Deepl implements Settings_Interface {
	/**
	 * Name of the action to check the API key.
	 *
	 * @since 3.6
	 *
	 * @var string
	 */
	const API_KEY_ACTION = 'pll_deepl_check_api_key';

	/**
	 * Name of the action to get the DeepL usage.
	 *
	 * @since 3.6
	 *
	 * @var string
	 */
	const USAGE_ACTION = 'pll_deepl_get_usage';

	/**
	 * Service.
	 *
	 * @var Service
	 */
	private $service;

	/**
	 * Polylang's model.
	 *
	 * @var PLL_Model
	 */
	private $model;

	/**
	 * Base of the name attribute used by the inputs.
	 *
	 * @var string
	 *
	 * @phpstan-var non-falsy-string
	 */
	private $input_base_name;

	/**
	 * Stores the fields' options.
	 *
	 * @var array
	 */
	private $options;

	/**
	 * Constructor.
	 *
	 * @since 3.6
	 *
	 * @param string    $input_base_name Base of the name attribute used by the inputs.
	 *                                   Can contain a placeholder `{slug}` that will be replaced by the service's slug.
	 *                                   Ex: `machine_translation_services[{slug}]`.
	 * @param array     $options         Service's options.
	 * @param Service   $service         Service.
	 * @param PLL_Model $model           Polylang's model.
	 *
	 * @phpstan-param non-falsy-string $input_base_name
	 */
	public function __construct( string $input_base_name, array $options, Service $service, PLL_Model $model ) {
		$this->service         = $service;
		$this->model           = $model;
		$this->input_base_name = str_replace( '{slug}', $service::get_slug(), $input_base_name );
		$this->options         = $options;

		add_action( 'wp_ajax_' . self::API_KEY_ACTION, array( $this, 'check_api_key' ) );
		add_action( 'wp_ajax_' . self::USAGE_ACTION, array( $this, 'update_characters_consumption_view' ) );
	}

	/**
	 * Ajax callback that checks for the API key validity.
	 *
	 * @since 3.6
	 *
	 * @return void
	 *
	 * @phpstan-return never
	 */
	public function check_api_key() {
		check_ajax_referer( self::API_KEY_ACTION, '_pll_nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( -1 );
		}

		if ( empty( $_GET['api_key'] ) || ! is_string( $_GET['api_key'] ) ) {
			wp_send_json_error(
				array(
					'message'       => esc_html__( 'Please fill in the API key field.', 'polylang-pro' ),
					'message_class' => 'pll-message-error-auth', // See `get_error_message_class()`.
				)
			);
		}

		$valid = $this->is_api_key_valid(
			array(
				'api_key'   => (string) sanitize_text_field( wp_unslash( $_GET['api_key'] ) ),
				'formality' => 'default',
			)
		);

		if ( $valid->has_errors() ) {
			// The key is invalid or we had a failure while checking it.
			wp_send_json_error(
				array(
					'message'       => esc_html( $valid->get_error_message() ),
					'message_class' => $this->get_error_message_class( $valid ),
				)
			);
		}

		wp_send_json_success();
	}

	/**
	 * Displays the characters consumption view.
	 *
	 * @since 3.6
	 *
	 * @return void
	 *
	 * @phpstan-return never
	 */
	public function update_characters_consumption_view() {
		check_ajax_referer( self::USAGE_ACTION, '_pll_nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( -1 );
		}

		$usage = $this->service->get_client()->get_usage();

		if ( is_wp_error( $usage ) ) {
			// Error while retrieving the data: display the error message.
			wp_send_json_error(
				array(
					'message' => esc_html(
						sprintf(
							/* translators: %s is an error message. */
							__( 'Error while retrieving the data: %s.', 'polylang-pro' ),
							$usage->get_error_message()
						)
					),
				)
			);
		}

		if ( ! $usage['character_limit'] ) {
			// The character limit is 0: display only the character count.
			wp_send_json_success(
				array(
					'message' => esc_html(
						sprintf(
							/* translators: %s is a formatted count number. */
							_n( '%s translated character.', '%s translated characters.', $usage['character_count'], 'polylang-pro' ),
							number_format_i18n( $usage['character_count'] )
						)
					),
				)
			);
		}

		// Display a graphic.
		$percent  = round( $usage['character_count'] * 100 / $usage['character_limit'], 1 );
		$percent  = (float) min( $percent, 100 );
		$decimals = 1;

		if ( floor( $percent ) === $percent ) {
			$decimals = 0;
		}

		wp_send_json_success(
			array(
				'percent_formatted' => number_format_i18n( $percent, $decimals ) . '%',
				'percent'           => (string) $percent . '%',
				'message'           => esc_html(
					sprintf(
						/* translators: %1$s is a formatted count number, %2$s is a formatted limit number. */
						_n( '%1$s / %2$s translated character.', '%1$s / %2$s translated characters.', $usage['character_count'], 'polylang-pro' ),
						number_format_i18n( $usage['character_count'] ),
						number_format_i18n( $usage['character_limit'] )
					)
				),
			)
		);
	}

	/**
	 * Tells if the given service options contain a non-empty authentication key.
	 *
	 * @since 3.6
	 *
	 * @param array $options Options for this service.
	 * @return bool
	 */
	public function has_api_key( array $options ): bool {
		return ! empty( $options['api_key'] ) && is_string( $options['api_key'] ) && '' !== trim( $options['api_key'] );
	}

	/**
	 * Tells if the authentication key from the given service options is valid by contacting the service.
	 *
	 * @since 3.6
	 *
	 * @param array $options Options for this service (must be sanitized beforehand).
	 * @return WP_Error {
	 *    An empty `WP_Error` if the authentication succeeded.
	 *    In the other cases, the `WP_Error` data will contain an array as follow:
	 *
	 *    @type string $type     `'error'` if the API key is invalid, or `'warning'` if there was an error while
	 *                           contacting the service.
	 *    @type string $field_id CSS ID of the field in fault.
	 * }
	 */
	public function is_api_key_valid( array $options ): WP_Error {
		if ( ! $this->has_api_key( $options ) ) {
			$options['api_key'] = '';
		}

		$error = ( new Service( $options, $this->model ) )->get_client()->is_api_key_valid();

		if ( ! $error->has_errors() ) {
			// The key is valid.
			return $error;
		}

		$error->add_data(
			array(
				'message_class' => $this->get_error_message_class( $error ),
				'field_id'      => 'pll-deepl-api-key',
			)
		);

		return $error;
	}

	/**
	 * Sanitizes and validates the options for this service.
	 *
	 * @since 3.6
	 *
	 * @param array $options Options for this service.
	 * @return array Validated options.
	 *
	 * @phpstan-return DeeplOptions
	 */
	public function sanitize_options( array $options ): array {
		$new_options = array(
			'api_key'   => '',
			'formality' => 'default',
		);

		if ( $this->has_api_key( $options ) ) {
			$new_options['api_key'] = (string) sanitize_text_field( $options['api_key'] );
		}

		if ( isset( $options['formality'] ) && in_array( $options['formality'], array( 'prefer_more', 'prefer_less' ), true ) ) {
			$new_options['formality'] = $options['formality'];
		}

		// Return only the validated options.
		return $new_options;
	}

	/**
	 * Prints error notices.
	 *
	 * @since 3.6
	 *
	 * @return void
	 */
	public function print_notices() {
		if ( $this->service->is_active() ) {
			$this->print_view(
				'inner-notices-row',
				array(
					'name'      => $this->service->get_name(),
					'languages' => $this->get_unsupported_languages(),
				)
			);
		}
	}

	/**
	 * Prints settings fields.
	 *
	 * @since 3.6
	 *
	 * @return void
	 */
	public function print_settings_fields() {
		if ( $this->service->is_active() ) {
			$this->print_view(
				'characters-consumption-row',
				array(
					'ajax_action' => self::USAGE_ACTION,
				)
			);
		}

		$this->print_view(
			'service-authentication-row',
			array(
				'ajax_action'     => self::API_KEY_ACTION,
				/* translators: %s is a service name. */
				'button_label'    => sprintf( __( 'Check connection to %s', 'polylang-pro' ), $this->service->get_name() ),
				'id'              => 'deepl-api-key',
				'message_default' => sprintf(
					/* translators: %1$s is an opening link tag leading to account creation, %2$s is an opening link tag leading to the account page, %3$s is a closing link tag. */
					__( '%1$sCreate your account on DeepL%3$s, then %2$sfind your API key at the bottom of your account page%3$s.', 'polylang-pro' ),
					'<a href="https://www.deepl.com/pro-api">',
					'<a href="https://www.deepl.com/account/summary">',
					'</a>'
				),
				'messages_error'  => array(
					'pll-message-error-auth'        => sprintf(
						/* translators: %1$s is an opening link tag leading to the service's account page, %2$s is a closing link tag. */
						__( '%1$sVerify your API key at the bottom of your DeepL account page%2$s.', 'polylang-pro' ),
						'<a href="https://www.deepl.com/account/summary">',
						'</a>'
					),
					'pll-message-error-unavailable' => sprintf(
						/* translators: %1$s is an opening link tag leading to the service's status page, %2$s is a closing link tag. */
						__( 'You can look at %1$sthe DeepL Pro/Free API\'s status%2$s.', 'polylang-pro' ),
						'<a href="https://www.deeplstatus.com/">',
						'</a>'
					),
				),
				'message_success' => __( 'Your API key is valid.', 'polylang-pro' ),
				'option'          => 'api_key',
				'title'           => __( 'API key', 'polylang-pro' ),
			)
		);

		$this->print_view(
			'deepl-formality-row',
			array(
				'option'   => 'formality',
				'formal'   => $this->get_active_languages_by_formality( 'formal' ),
				'informal' => $this->get_active_languages_by_formality( 'informal' ),
			)
		);
	}

	/**
	 * Prints a view.
	 *
	 * @since 3.6
	 *
	 * @param string $view Name of the view.
	 * @param array  $atts Optional. Data to print. See views headers.
	 * @return void
	 */
	private function print_view( string $view, array $atts = array() ) {
		$atts['slug']            = $this->service::get_slug();
		$atts['input_base_name'] = $this->input_base_name;

		if ( isset( $atts['option'] ) && ! isset( $atts['value'] ) ) {
			$atts['value'] = $this->options[ $atts['option'] ] ?? '';
		}

		include __DIR__ . "/views/view-{$view}.php";
	}

	/**
	 * Returns the lists of languages that are not supported by the service.
	 *
	 * @since 3.6
	 * @return string[] Array of language names (and their locale).
	 *
	 * @phpstan-return list<string>
	 */
	private function get_unsupported_languages(): array {
		$languages = array();

		foreach ( $this->model->get_languages_list() as $language ) {
			if ( empty( $this->service::get_target_code( $language ) ) ) {
				$languages[] = $this->get_language_label( $language );
			}
		}

		sort( $languages );

		return $languages;
	}

	/**
	 * Returns the lists of active formal or informal languages.
	 * Formal languages have a locale with a `_formal` suffix (`de_DE_formal`, `nl_NL_formal`),
	 * Informal languages have a `_informal` suffix (`de_CH_informal`).
	 *
	 * @since 3.6
	 *
	 * @param string $formality Formality.
	 * @return string[] Array of arrays of language names (and their locale).
	 *
	 * @phpstan-param 'formal'|'informal' $formality
	 * @phpstan-return list<string>
	 */
	private function get_active_languages_by_formality( string $formality ): array {
		$languages = array();

		foreach ( $this->model->get_languages_list() as $language ) {
			if ( empty( $this->service::get_target_code( $language ) ) ) {
				continue;
			}

			if ( ! preg_match( "@_{$formality}$@", $language->locale ) ) {
				continue;
			}

			$languages[] = $this->get_language_label( $language );
		}

		sort( $languages );

		return $languages;
	}

	/**
	 * Returns a language name and its locale.
	 *
	 * @since 3.6
	 *
	 * @param PLL_Language $language A language object.
	 * @return string
	 */
	private function get_language_label( PLL_Language $language ): string {
		return sprintf(
			/* translators: %1$s is a language name, %2$s is a language locale. */
			_x( '%1$s (%2$s)', 'Language label', 'polylang-pro' ),
			$language->name,
			sprintf( '<code>%s</code>', $language->locale )
		);
	}

	/**
	 * Returns the HTML class corresponding to the given error.
	 * See the array keys for the 'messages_error' in `print_settings_fields()`.
	 *
	 * @since 3.6
	 *
	 * @param WP_Error $error An error object.
	 * @return string `'pll-message-error-auth'` in case of authentication failure, `'pll-message-error-unavailable'` in other cases.
	 */
	private function get_error_message_class( WP_Error $error ): string {
		return 'pll_deepl_authentication_failure' === $error->get_error_code() ? 'pll-message-error-auth' : 'pll-message-error-unavailable';
	}
}
