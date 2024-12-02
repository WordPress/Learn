<?php
/**
 * @package Polylang-Pro
 */

namespace WP_Syntex\Polylang_Pro\Modules\Machine_Translation\Services;

defined( 'ABSPATH' ) || exit;

use PLL_Language;
use PLL_Model;
use WP_Syntex\Polylang_Pro\Modules\Machine_Translation\Clients\Client_Interface;
use WP_Syntex\Polylang_Pro\Modules\Machine_Translation\Clients\Deepl as Client;
use WP_Syntex\Polylang_Pro\Modules\Machine_Translation\Settings\Deepl as Settings;
use WP_Syntex\Polylang_Pro\Modules\Machine_Translation\Settings\Settings_Interface;

/**
 * Machine translation service: DeepL.
 *
 * @phpstan-import-type iconProperties from Service_Interface
 *
 * @since 3.6
 *
 * @phpstan-import-type DeeplOptions from Settings
 */
class Deepl implements Service_Interface {
	/**
	 * Service's options.
	 *
	 * @var array
	 *
	 * @phpstan-var DeeplOptions
	 */
	private $service_options;

	/**
	 * Polylang's model.
	 *
	 * @var PLL_Model
	 */
	private $model;

	/**
	 * Array of supported languages data.
	 *
	 * @since 3.6
	 *
	 * @var array[]
	 */
	private static $languages = array();

	/**
	 * Constructor.
	 *
	 * @since 3.6
	 *
	 * @param array     $options Service's options.
	 * @param PLL_Model $model   Polylang's model.
	 */
	public function __construct( array $options, PLL_Model $model ) {
		/**
		 * @phpstan-var array{api_key?: string, formality?: 'default'|'prefer_less'|'prefer_more'} $options
		 */
		$this->service_options = array_merge(
			array(
				'api_key'   => '',
				'formality' => 'default',
			),
			$options
		);
		$this->model = $model;
	}

	/**
	 * Tells if the service is active.
	 *
	 * @since 3.6
	 *
	 * @return bool
	 */
	public function is_active(): bool {
		return ! empty( $this->service_options['api_key'] );
	}

	/**
	 * Returns a unique identifier of the service.
	 *
	 * @since 3.6
	 *
	 * @return string
	 *
	 * @phpstan-return non-falsy-string
	 */
	public static function get_slug(): string {
		return 'deepl';
	}

	/**
	 * Returns the name of the service.
	 *
	 * @since 3.6
	 *
	 * @return string
	 *
	 * @phpstan-return non-empty-string
	 */
	public function get_name(): string {
		return 'DeepL';
	}

	/**
	 * Returns the svg properties of the service's logo.
	 *
	 * @since 3.6
	 *
	 * @return string[] {
	 *     An array containing the SVG icon properties.
	 *
	 *     @type string $width   The icon width.
	 *     @type string $height  The icon height.
	 *     @type string $xmlns   The SVG namespace URL.
	 *     @type string $viewBox The position and dimension according to the SVG viewport.
	 *     @type string $path_d  The `d` attribute of the SVG `<path>` to define the icon shape.
	 * }
	 * @phpstan-return iconProperties
	 */
	public function get_icon_properties(): array {
		return array(
			'width'   => '20',
			'height'  => '20',
			'xmlns'   => 'http://www.w3.org/2000/svg',
			'viewBox' => '0 0 20 20',
			'path_d'  => 'M17.407,4.133l-6.837,-3.954c-0.354,-0.206 -0.786,-0.206 -1.14,0l-6.837,3.967c-0.354,0.207 -0.57,0.584 -0.57,0.984l-0,7.921c-0,0.41 0.216,0.79 0.57,0.994l6.837,3.957c0.016,0.01 0.036,0.02 0.052,0.03l3.367,1.95l-0.01,-1.714l0.007,-0.951l0.003,0.016l0,-0.321c0,-0.19 0.099,-0.361 0.246,-0.466l0.22,-0.124l0.105,-0.059l-0.007,-0l3.994,-2.312c0.354,-0.206 0.57,-0.583 0.57,-0.993l0,-7.931c0,-0.41 -0.216,-0.787 -0.57,-0.994Zm-8.194,8.056c0.102,0.4 -0.01,0.843 -0.331,1.151c-0.456,0.446 -1.184,0.446 -1.639,-0c-0.492,-0.469 -0.492,-1.246 -0,-1.715c0.455,-0.446 1.183,-0.446 1.639,-0c0.02,0.02 0.039,0.039 0.059,0.062l2.767,-1.593l0.571,0.321l-3.066,1.774Zm4.8,-2.007c-0.456,0.446 -1.187,0.446 -1.639,0c-0.344,-0.328 -0.446,-0.809 -0.308,-1.229l-0.01,0.006l-3.122,-1.806c-0.016,0.016 -0.029,0.033 -0.045,0.046c-0.456,0.446 -1.184,0.446 -1.64,-0c-0.492,-0.469 -0.492,-1.246 0,-1.715c0.456,-0.446 1.184,-0.446 1.64,-0c0.327,0.315 0.436,0.764 0.324,1.17l3.144,1.83c0.007,-0.007 0.01,-0.01 0.017,-0.016c0.456,-0.446 1.187,-0.446 1.639,-0c0.489,0.468 0.489,1.245 0,1.714Z',
		);
	}

	/**
	 * Returns the service's logo as a svg vector.
	 *
	 * @since 3.6
	 *
	 * @return string
	 *
	 * @phpstan-return non-empty-string
	 */
	public function get_icon(): string {
		// Icon from https://www.deepl.com/press.html and modified.
		$icon_properties = $this->get_icon_properties();
		return sprintf(
			'<svg width="%s" height="%s" xmlns="%s" viewBox="%s"><path d="%s"/></svg>',
			$icon_properties['width'],
			$icon_properties['height'],
			$icon_properties['xmlns'],
			$icon_properties['viewBox'],
			$icon_properties['path_d']
		);
	}

	/**
	 * Returns the client that will be processed for the machine translation.
	 *
	 * @since 3.6
	 *
	 * @return Client
	 */
	public function get_client(): Client_Interface {
		return new Client( $this->service_options );
	}

	/**
	 * Returns the object that will print the settings for the machine translation.
	 *
	 * @since 3.6
	 *
	 * @param string $input_base_name Base of the name attribute used by the inputs.
	 *                                Can contain a placeholder `{slug}` that will be replaced by the service's slug.
	 *                                Ex: `machine_translation_services[{slug}]`.
	 * @return Settings
	 *
	 * @phpstan-param non-falsy-string $input_base_name
	 */
	public function get_settings( string $input_base_name ): Settings_Interface {
		return new Settings( $input_base_name, $this->service_options, $this, $this->model );
	}

	/**
	 * Returns machine translation service code for target language if available.
	 *
	 * @since 3.6
	 *
	 * @param PLL_Language $language Language to check.
	 * @return string Language code, empty if not available.
	 */
	public static function get_target_code( PLL_Language $language ): string {
		if ( empty( self::$languages ) ) {
			self::$languages = include POLYLANG_DIR . '/settings/languages.php'; // phpcs:ignore WordPressVIPMinimum.Files.IncludingFile.NotAbsolutePath
		}

		if ( empty( self::$languages[ $language->locale ] ) ) {
			return '';
		}

		return self::$languages[ $language->locale ][ static::get_slug() ] ?? '';
	}

	/**
	 * Returns machine translation service code for source language if available.
	 *
	 * @since 3.6
	 *
	 * @param PLL_Language $language Language to check.
	 * @return string Language code, empty if not available.
	 */
	public static function get_source_code( PLL_Language $language ): string {
			return substr( static::get_target_code( $language ), 0, 2 );
	}
}
