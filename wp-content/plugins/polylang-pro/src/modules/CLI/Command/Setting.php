<?php
/**
 * @package Polylang-Pro
 */

namespace WP_Syntex\Polylang_Pro\Modules\CLI\Command;

use WP_CLI;
use WP_CLI\Utils;
use WP_Syntex\Polylang\Options\Options;
use WP_Syntex\Polylang_Pro\Modules\CLI\Formatter;

/**
 * Manages Polylang settings.
 *
 * @since 3.8
 */
class Setting {
	/**
	 * Error mode constant for exiting the script.
	 *
	 * @var string
	 */
	private const ERROR_MODE_EXIT = 'error';

	/**
	 * Error mode constant for non-exiting errors.
	 *
	 * @var string
	 */
	private const ERROR_MODE_WARNING = 'warning';

	/**
	 * Options instance.
	 *
	 * @var Options
	 */
	private $options;

	/**
	 * Adapter instance.
	 *
	 * @var Setting_Adapter
	 */
	private $adapter;

	/**
	 * List of settings keys that are not supported yet.
	 * Typically, these are settings that are nested objects.
	 *
	 * @var array
	 */
	private $unsupported = array(
		'nav_menus',
		'machine_translation_services',
	);

	/**
	 * Constructor.
	 *
	 * @since 3.8
	 *
	 * @param Options $options Options instance.
	 */
	public function __construct( Options $options ) {
		$this->options = $options;
		$this->adapter = new Setting_Adapter();
	}

	/**
	 * Sets a scalar Polylang setting.
	 *
	 * ## OPTIONS
	 *
	 * <key>
	 * : The key of the setting to set.
	 *
	 * <value>
	 * : The value of the setting to set.
	 *
	 * ## EXAMPLES
	 *
	 *     # Activate media translation setting
	 *     wp pll setting set media_support true
	 *
	 *     # Activate media duplication setting
	 *     wp pll setting set media_duplicate true
	 *
	 *     # Set domains using JSON
	 *     wp pll setting set domains '{"fr":"example.fr"}'
	 *
	 * @param string[] $args The command arguments.
	 *
	 * @since 3.8
	 */
	public function set( $args ): void {
		list( $input_key, $input_value ) = $args;
		list( $key, $value )             = $this->adapter->get_args( $args );

		$this->is_supported( $key, self::ERROR_MODE_EXIT );
		$this->is_mutable( $key, self::ERROR_MODE_EXIT );

		$decoded_value = Utils\is_json( $value ) ? json_decode( $value, true ) : $value;
		if ( ! $this->is_scalar( $key ) && ! is_array( $decoded_value ) ) {
			WP_CLI::error( sprintf( 'Setting "%s" is not a scalar. Use a JSON string for list or map settings instead.', $key ) );
		}

		$property = $this->options->get_schema()['properties'][ $key ];

		if ( null === $decoded_value ) {
			WP_CLI::error( sprintf( 'Invalid value "%1$s" for type "%2$s".', $value, $property['type'] ) );
		}

		$errors = $this->options->set( $key, $decoded_value );

		if ( $errors->has_errors() ) {
			WP_CLI::error( $errors->get_error_message() );
		}

		$this->save( self::ERROR_MODE_EXIT );

		WP_CLI::success( sprintf( 'Setting "%1$s" set to "%2$s".', $input_key, $input_value ) );
	}

	/**
	 * Returns one or more Polylang setting(s) if given key(s), or lists all settings if no key is provided.
	 *
	 * ## OPTIONS
	 *
	 * [--keys=<keys>]
	 * : Comma-separated list of keys to get. Default is all.
	 * ---
	 * default: all
	 * ---
	 *
	 * [--format=<format>]
	 * : Render output in a particular format.
	 * ---
	 * default: table
	 * options:
	 *   - table
	 *   - json
	 *   - yaml
	 *   - csv
	 * ---
	 *
	 * ## EXAMPLES
	 *
	 *     # Get specific settings
	 *     wp pll setting get --keys=media_support,force_lang
	 *
	 *     # List all settings
	 *     wp pll setting get
	 *
	 * @param string[] $args       The command arguments.
	 * @param string[] $assoc_args The command associative arguments.
	 * @phpstan-param array{keys?: string, format?: string} $assoc_args
	 *
	 * @since 3.8
	 */
	public function get( $args, $assoc_args ): void {
		$assoc_args = $this->adapter->get_assoc_args( $assoc_args );
		$settings   = $this->options->get_all();
		$schema     = $this->options->get_schema();
		/** @var string $keys_filter */
		$keys_filter = Utils\get_flag_value( $assoc_args, 'keys', 'all' );

		if ( 'all' !== $keys_filter ) {
			$requested_keys = array_map( 'trim', explode( ',', $keys_filter ) );
			$filtered_settings = array();

			foreach ( $requested_keys as $key ) {
				if ( ! $this->is_supported( $key, self::ERROR_MODE_WARNING ) ) {
					continue;
				}

				$filtered_settings[ $key ] = $settings[ $key ];
			}

			$settings = $filtered_settings;
		} else {
			foreach ( $this->unsupported as $unsupported_key ) {
				unset( $settings[ $unsupported_key ] );
			}
		}

		if ( empty( $settings ) ) {
			WP_CLI::warning( 'No settings found.' );

			return;
		}

		$this->get_formatter( $assoc_args )->display_items( $this->normalize_settings( $settings, $schema['properties'] ) );
	}

	/**
	 * Adds a value to a list type Polylang setting.
	 *
	 * ## OPTIONS
	 *
	 * <key>
	 * : The key of the setting to add a value to.
	 *
	 * <value>
	 * : The value to add to the setting. For map type settings, this is the subkey to add a value to.
	 *
	 * [<subvalue>]
	 * : The subvalue to add to the setting. For map type settings, this is the value of the subkey. Not applicable for list type settings.
	 * ---
	 * default: ''
	 * ---
	 *
	 * [--yes]
	 * : Answer yes to the confirmation message.
	 *
	 * ## EXAMPLES
	 *
	 *     # Add a custom post type to the list of translatable post types
	 *     wp pll setting add post_types custom_post_type
	 *
	 *     # Add an English domain to the map of domains
	 *     wp pll setting add domains en example.com
	 *
	 * @param string[] $args       The command arguments.
	 * @param string[] $assoc_args The command associative arguments.
	 * @phpstan-param array{yes?: bool} $assoc_args
	 *
	 * @since 3.8
	 */
	public function add( $args, $assoc_args ): void {
		list( $key, $value ) = $args;
		$subvalue = $args[2] ?? '';

		$this->is_supported( $key, self::ERROR_MODE_EXIT );

		$this->is_mutable( $key, self::ERROR_MODE_EXIT );

		$this->is_array_like( $key, self::ERROR_MODE_EXIT );

		if ( $this->is_list( $key ) ) {
			$success_message = sprintf( 'Value "%1$s" added to setting "%2$s".', $value, $key );

			$errors = $this->options->add( $key, $value );

			WP_CLI::log( $errors->get_error_message() );
		} else {
			if ( '' === $subvalue ) {
				WP_CLI::error( sprintf( 'Subvalue is required for map type setting "%s".', $key ) );
			}

			/** @var array $prev_map */
			$prev_map = $this->options->get( $key );
			if ( isset( $prev_map[ $value ] ) ) {
				WP_CLI::confirm(
					sprintf( 'Key "%1$s" already exists in setting "%2$s". Do you want to override it?', $value, $key ),
					$assoc_args
				);
			}

			$success_message = sprintf( 'Key "%1$s" with value "%2$s" added to setting "%3$s".', $value, $subvalue, $key );

			$errors = $this->options->add( $key, array( $value => $subvalue ) );
		}

		if ( $errors->has_errors() ) {
			WP_CLI::error( $errors->get_error_message() );
		}

		$this->save( self::ERROR_MODE_EXIT );

		WP_CLI::success( $success_message );
	}

	/**
	 * Removes a value from a list type Polylang setting.
	 *
	 * ## OPTIONS
	 *
	 * <key>
	 * : The key of the setting to remove a value from.
	 *
	 * <value>
	 * : The value to remove from the setting. For map type settings, this is the subkey to remove a value from.
	 *
	 * ## EXAMPLES
	 *
	 *     # Remove a custom post type from the list of translatable post types
	 *     wp pll setting remove post_types custom_post_type
	 *
	 *     # Remove an English domain from the map of domains
	 *     wp pll setting remove domains en
	 *
	 * @param string[] $args The command arguments.
	 *
	 * @since 3.8
	 */
	public function remove( $args ): void {
		list( $key, $value ) = $args;

		$this->is_supported( $key, self::ERROR_MODE_EXIT );

		$this->is_mutable( $key, self::ERROR_MODE_EXIT );

		$this->is_array_like( $key, self::ERROR_MODE_EXIT );

		/** @var array $current_value Array like options. */
		$current_value = $this->options->get( $key );

		if ( $this->is_list( $key ) ) {
			if ( ! in_array( $value, $current_value, true ) ) {
				WP_CLI::error( sprintf( 'Value "%1$s" does not exist in setting "%2$s".', $value, $key ) );
			}

			$success_message = sprintf( 'Value "%1$s" removed from setting "%2$s".', $value, $key );
		} else {
			if ( ! isset( $current_value[ $value ] ) ) {
				WP_CLI::error( sprintf( 'Key "%1$s" does not exist in setting "%2$s".', $value, $key ) );
			}

			$success_message = sprintf( 'Key "%1$s" removed from setting "%2$s".', $value, $key );
		}

		$errors = $this->options->remove( $key, $value );
		if ( $errors->has_errors() ) {
			WP_CLI::error( $errors->get_error_message() );
		}

		$this->save( self::ERROR_MODE_EXIT );

		WP_CLI::success( $success_message );
	}

	/**
	 * Resets a Polylang setting to its default value, or all settings if no key is provided.
	 *
	 * ## OPTIONS
	 *
	 * [--keys=<keys>]
	 * : Comma-separated list of keys to reset. If not provided, all settings will be reset.
	 *
	 * [--yes]
	 * : Answer yes to the confirmation message.
	 *
	 * ## EXAMPLES
	 *
	 *     # Reset specific settings
	 *     wp pll setting reset --keys=media_support,force_lang
	 *
	 *     # Reset all settings
	 *     wp pll setting reset
	 *
	 * @param string[] $args The command arguments.
	 * @param string[] $assoc_args The command associative arguments.
	 * @phpstan-param array{keys?: string, yes?: bool} $assoc_args
	 *
	 * @since 3.8
	 */
	public function reset( $args, $assoc_args ): void {
		$assoc_args = $this->adapter->get_assoc_args( $assoc_args );
		/** @var string $keys_filter */
		$keys_filter = Utils\get_flag_value( $assoc_args, 'keys' );
		$current_settings = $this->options->get_all();

		if ( $keys_filter ) {
			$keys_to_reset = array_map( 'trim', explode( ',', $keys_filter ) );

			foreach ( $keys_to_reset as $key ) {
				if ( ! $this->is_supported( $key, self::ERROR_MODE_WARNING ) || ! $this->is_mutable( $key, self::ERROR_MODE_WARNING ) ) {
					continue;
				}

				$this->options->reset( $key );
			}

			$this->save( self::ERROR_MODE_EXIT );

			WP_CLI::success( sprintf( 'Settings "%s" reset to default values.', $keys_filter ) );
		} else {
			WP_CLI::confirm(
				'Are you sure you want to reset all Polylang settings to their default values?',
				$assoc_args
			);

			foreach ( $current_settings as $key => $value ) {
				if ( $this->is_read_only( $key ) ) {
					continue;
				}

				$this->options->reset( $key );
			}

			$this->save( self::ERROR_MODE_EXIT );

			WP_CLI::success( 'All Polylang settings reset to default values.' );
		}
	}

	/**
	 * Saves options or exits with error if save fails.
	 *
	 * @since 3.8
	 *
	 * @param string $error_mode The error mode. Use `ERROR_MODE_EXIT` or `ERROR_MODE_WARNING` constants.
	 * @return bool|never True if the settings were saved successfully, false otherwise. Never returns if $error_mode is `ERROR_MODE_EXIT`.
	 */
	private function save( string $error_mode ): bool {
		$result = $this->options->save();

		return $result || WP_CLI::$error_mode( 'Failed to save the settings.' );
	}

	/**
	 * Fails if the given setting is not supported.
	 *
	 * @since 3.8
	 *
	 * @param string $key        The setting key.
	 * @param string $error_mode The error mode. Use `ERROR_MODE_EXIT` or `ERROR_MODE_WARNING` constants.
	 * @return bool|never True if the setting is supported, false otherwise. Never returns if $error_mode is `ERROR_MODE_EXIT`.
	 */
	private function is_supported( string $key, string $error_mode ) {
		if ( ! in_array( $key, $this->unsupported, true ) && isset( $this->options->get_schema()['properties'][ $key ] ) ) {
			return true;
		}

		WP_CLI::$error_mode( sprintf( 'Setting "%s" is not supported.', $key ) );

		return false;
	}

	/**
	 * Fails if the given setting is read-only.
	 *
	 * @since 3.8
	 *
	 * @param string $key        The setting key.
	 * @param string $error_mode The error mode. Use `ERROR_MODE_EXIT` or `ERROR_MODE_WARNING` constants.
	 * @return bool|never True if the setting is mutable, false otherwise. Never returns if $error_mode is `ERROR_MODE_EXIT`.
	 */
	private function is_mutable( string $key, string $error_mode ) {
		if ( $this->is_read_only( $key ) ) {
			WP_CLI::$error_mode( sprintf( 'Setting "%s" is read-only and cannot be modified.', $key ) );

			return false;
		}

		return true;
	}

	/**
	 * Fails if the given setting is not a list or map setting.
	 *
	 * @since 3.8
	 *
	 * @param string $key        The setting key.
	 * @param string $error_mode The error mode. Use `ERROR_MODE_EXIT` or `ERROR_MODE_WARNING` constants.
	 * @return bool|never True if the setting is a list or map setting, false otherwise. Never returns if $error_mode is `ERROR_MODE_EXIT`.
	 */
	private function is_array_like( string $key, string $error_mode ) {
		if ( ! $this->is_list( $key ) && ! $this->is_map( $key ) ) {
			WP_CLI::$error_mode( sprintf( 'Setting "%s" is not a list or map setting. Use "set" command for scalar settings.', $key ) );

			return false;
		}

		return true;
	}

	/**
	 * Returns a formatter instance.
	 *
	 * @param array $assoc_args The command associative arguments.
	 * @return Formatter The formatter instance.
	 */
	private function get_formatter( array $assoc_args ) {
		return new Formatter(
			$assoc_args,
			array(
				'key',
				'value',
				'type',
			)
		);
	}

	/**
	 * Tells if the given option is read-only.
	 *
	 * @since 3.8
	 *
	 * @param string $key The option key.
	 * @return boolean True if the option is read-only, false otherwise.
	 */
	private function is_read_only( string $key ): bool {
		$schema = $this->options->get_schema();

		if ( ! isset( $schema['properties'][ $key ] ) ) {
			return false;
		}

		return ! empty( $schema['properties'][ $key ]['readonly'] );
	}

	/**
	 * Tells if the given option key is for a scalar setting.
	 *
	 * @since 3.8
	 *
	 * @param string $key The option key.
	 * @return bool True if the option is a scalar, false otherwise.
	 */
	private function is_scalar( string $key ): bool {
		$schema = $this->options->get_schema();

		return isset( $schema['properties'][ $key ]['type'] ) && in_array( $schema['properties'][ $key ]['type'], array( 'string', 'boolean', 'integer' ), true );
	}

	/**
	 * Tells if the given option key is for a list setting.
	 *
	 * @since 3.8
	 *
	 * @param string $key The option key.
	 * @return bool True if the option is a list, false otherwise.
	 */
	private function is_list( string $key ): bool {
		$schema = $this->options->get_schema();

		return isset( $schema['properties'][ $key ]['type'] ) && 'array' === $schema['properties'][ $key ]['type'];
	}

	/**
	 * Tells if the given option key is for a map setting.
	 *
	 * @since 3.8
	 *
	 * @param string $key The option key.
	 * @return bool True if the option is a map, false otherwise.
	 */
	private function is_map( string $key ): bool {
		$schema = $this->options->get_schema();
		return isset( $schema['properties'][ $key ]['type'] ) && 'object' === $schema['properties'][ $key ]['type'];
	}

	/**
	 * Returns a list of normalized settings.
	 *
	 * @since 3.8
	 *
	 * @param array $settings   The settings.
	 * @param array $properties List of properties.
	 * @return array[] The normalized array.
	 *
	 * @phpstan-return list<array<string, mixed>>
	 */
	private function normalize_settings( array $settings, array $properties ) {
		$items = array();
		foreach ( $settings as $key => $value ) {
			$items[] = $this->adapter->get_item( $key, $value, $properties[ $key ] );
		}
		return $items;
	}
}
