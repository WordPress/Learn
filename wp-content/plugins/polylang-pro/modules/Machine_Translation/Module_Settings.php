<?php
/**
 * @package Polylang-Pro
 */

namespace WP_Syntex\Polylang_Pro\Modules\Machine_Translation;

use WP_Error;
use PLL_Settings;
use PLL_Settings_Preview_Machine_Translation;
use WP_Syntex\Polylang_Pro\Modules\Machine_Translation\Factory;

defined( 'ABSPATH' ) || exit;

/**
 * ...
 *
 * @since 3.6
 */
class Module_Settings extends PLL_Settings_Preview_Machine_Translation {
	/**
	 * List of all translation services' settings.
	 *
	 * @var Settings\Settings_Interface[]
	 */
	private $settings = array();

	/**
	 * Constructor.
	 *
	 * @since 3.6
	 *
	 * @param PLL_Settings $polylang Polylang object.
	 */
	public function __construct( &$polylang ) {
		parent::__construct( $polylang, array( 'active_option' => 'machine_translation_enabled' ) );

		$factory = new Factory( $polylang->model );

		foreach ( $factory->get_all() as $slug => $service ) {
			$this->settings[ $slug ] = $service->get_settings( 'machine_translation_services[{slug}]' );
		}

		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
	}

	/**
	 * Enqueues scripts and styles.
	 *
	 * @since 3.6
	 *
	 * @return void
	 */
	public function admin_enqueue_scripts() {
		$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

		wp_enqueue_style(
			'pll_machine_translation_settings',
			plugins_url( '/css/build/machine-translation-settings' . $suffix . '.css', POLYLANG_ROOT_FILE ),
			array(),
			POLYLANG_VERSION
		);

		wp_enqueue_script(
			'pll_machine_translation_settings',
			plugins_url( '/js/build/machine-translation-settings' . $suffix . '.js', POLYLANG_ROOT_FILE ),
			array( 'wp-url', 'wp-hooks' ),
			POLYLANG_VERSION,
			true
		);

		wp_set_script_translations( 'pll_machine_translation_settings', 'polylang-pro' );
	}

	/**
	 * Displays the settings form.
	 *
	 * @since 3.6
	 *
	 * @return void
	 */
	protected function form() {
		foreach ( $this->settings as $service_settings ) {
			$service_settings->print_notices();

			echo '<table class="form-table pll-table-top"><tbody>';

			$service_settings->print_settings_fields();

			echo '</tbody></table>';
		}
	}

	/**
	 * Sanitizes the settings before saving.
	 *
	 * @since 3.6
	 *
	 * @param array $options Raw options to save.
	 * @return array Sanitized options.
	 */
	protected function update( $options ) {
		$new_options = array(
			'machine_translation_services' => array(),
		);

		foreach ( $this->settings as $slug => $service_settings ) {
			$service_options = $options['machine_translation_services'][ $slug ] ?? array();

			// Is the API key provided?
			$has_api_key = $service_settings->has_api_key( $service_options );

			// Sanitize options.
			$new_options['machine_translation_services'][ $slug ] = $service_settings->sanitize_options( $service_options );

			if ( ! $has_api_key ) {
				// The API key field was empty before sanitization: don't display any error messages.
				continue;
			}

			// Check the API key validity.
			$error = $service_settings->is_api_key_valid( $new_options['machine_translation_services'][ $slug ] );

			if ( ! $error->has_errors() ) {
				// Valid.
				continue;
			}

			/** @phpstan-var array{type: 'error'|'warning', message_class: string, field_id: non-falsy-string} */
			$data = array_merge(
				array(
					'type'          => 'error', // Type of admin notice.
					'message_class' => '', // HTML class of the error message to display (only for types error and warning).
					'field_id'      => '', // HTML id of the field.
				),
				(array) $error->get_error_data()
			);
			
			/*
			 * Overwrites error extra data because `pll_add_notice()` expects a string instead of an array.
			 * The `pll-field-id-` prefix is used to determine which field to highlight.
			 * The `pll-message-class-` prefix is used to determine which error message to show.
			 */
			$error->add_data( "notice-{$data['type']} pll-field-id-{$data['field_id']} pll-message-class-{$data['message_class']}" );
			pll_add_notice( $error );
		}

		// Take care to return only sanitized options.
		return $new_options;
	}
}
