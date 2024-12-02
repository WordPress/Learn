<?php
/**
 * @package Polylang-Pro
 */

namespace WP_Syntex\Polylang_Pro\Modules\Machine_Translation\Services;

defined( 'ABSPATH' ) || exit;

use WP_Syntex\Polylang_Pro\Modules\Machine_Translation\Clients\Client_Interface;
use WP_Syntex\Polylang_Pro\Modules\Machine_Translation\Settings\Settings_Interface;

/**
 * Interface to use to define a machine translation service.
 *
 * @phpstan-type iconProperties array{width: non-falsy-string, height: non-falsy-string, xmlns: non-falsy-string, viewBox: non-falsy-string, path_d: non-falsy-string}
 *
 * @since 3.6
 */
interface Service_Interface {

	/**
	 * Tells if the service is active.
	 *
	 * @since 3.6
	 *
	 * @return bool
	 */
	public function is_active(): bool;

	/**
	 * Returns a unique identifier of the service.
	 *
	 * @since 3.6
	 *
	 * @return string
	 *
	 * @phpstan-return non-falsy-string
	 */
	public static function get_slug(): string;

	/**
	 * Returns the name of the service.
	 *
	 * @since 3.6
	 *
	 * @return string
	 *
	 * @phpstan-return non-empty-string
	 */
	public function get_name(): string;

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
	public function get_icon_properties(): array;

	/**
	 * Returns the service's logo as a svg vector.
	 *
	 * @since 3.6
	 *
	 * @return string
	 *
	 * @phpstan-return non-empty-string
	 */
	public function get_icon(): string;

	/**
	 * Returns the client that will be processed for the machine translation.
	 *
	 * @since 3.6
	 *
	 * @return Client_Interface
	 */
	public function get_client(): Client_Interface;

	/**
	 * Returns the object that will print the settings for the machine translation.
	 *
	 * @since 3.6
	 *
	 * @param string $input_base_name Base of the name attribute used by the inputs.
	 *                                Can contain a placeholder `{slug}` that will be replaced by the service's slug.
	 *                                Ex: `machine_translation_services[{slug}]`.
	 * @return Settings_Interface
	 *
	 * @phpstan-param non-falsy-string $input_base_name
	 */
	public function get_settings( string $input_base_name ): Settings_Interface;
}
