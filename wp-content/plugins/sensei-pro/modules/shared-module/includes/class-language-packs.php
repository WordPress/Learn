<?php
/**
 * File containing the class Sensei_Pro\Language_Packs.
 *
 * @package sensei-pro
 * @since   1.0.0
 */

namespace Sensei_Pro;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class that handles downloading language packs.
 *
 * @class Sensei_Pro\Language_Packs
 */
final class Language_Packs {
	const TRANSLATION_UPDATES_URL  = 'https://translate.wordpress.com/api/translations-updates/sensei';
	const REMOTE_PACKAGE_TRANSIENT = 'sensei-pro-translations-';

	/**
	 * Request cache of the language pack updates available.
	 *
	 * @var array|null
	 */
	private $language_pack_updates_cache;

	/**
	 * Instance of class.
	 *
	 * @var self
	 */
	private static $instance;

	/**
	 * Array of plugins to check for updates. (slug => version).
	 *
	 * @var array
	 */
	private $plugins;

	/**
	 * Language_Packs constructor. Prevents other instances from being created outside of `Language_Packs::instance()`.
	 */
	private function __construct() {
	}

	/**
	 * Initialize actions and filters for the class.
	 *
	 * @param array $plugins Array of plugins to check for updates. (slug => version).
	 */
	public function init( $plugins = [] ) {
		$this->plugins = $plugins;

		add_filter( 'site_transient_update_plugins', [ $this, 'add_updated_translations' ] );
	}

	/**
	 * Adds the plugin's language pack updates to the `update_plugins` transient.
	 *
	 * @access private
	 *
	 * @param \stdClass $transient Current value of `update_plugins` transient.
	 * @return \stdClass
	 */
	public function add_updated_translations( $transient ) {
		if ( empty( $transient ) ) {
			return $transient;
		}

		$translations            = $this->get_language_pack_updates();
		$transient->translations = array_merge( $transient->translations ?? [], $translations );

		return $transient;
	}

	/**
	 * Get translations updates from our translation pack server.
	 *
	 * @return array Update data {plugin_slug => data}
	 */
	public function get_language_pack_updates() {
		if ( ! is_null( $this->language_pack_updates_cache ) ) {
			return $this->language_pack_updates_cache;
		}

		$locales = array_values( get_available_languages() );

		/** This action is documented in WordPress core's wp-includes/update.php */
		// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound
		$locales = apply_filters( 'plugins_update_check_locales', $locales );
		$locales = array_unique( $locales );

		// No locales or plugins, the respone will be empty, we can return now.
		if ( empty( $locales ) || ! $this->plugins ) {
			return [];
		}

		// Check if we've cached this in the last day.
		$transient_key = self::REMOTE_PACKAGE_TRANSIENT . md5( SENSEI_PRO_VERSION . wp_json_encode( $locales ) );
		$data          = get_site_transient( $transient_key );
		if ( false !== $data && is_array( $data ) ) {
			return $data;
		}

		// Set the timeout for the request. We may want to consider increasing this when we add additional plugins.
		$timeout = 5;
		if ( wp_doing_cron() ) {
			$timeout = 30;
		}

		$plugins = [];
		foreach ( $this->plugins as $slug => $version ) {
			$plugins[ $slug ] = [ 'version' => $version ];
		}

		$request_body = [
			'locales' => $locales,
			'plugins' => $plugins,
		];

		$raw_response = wp_remote_post(
			self::TRANSLATION_UPDATES_URL,
			[
				'body'    => wp_json_encode( $request_body ),
				'headers' => [ 'Content-Type: application/json' ],
				'timeout' => $timeout,
			]
		);

		// Something unexpected happened on the translation server side.
		$response_code = wp_remote_retrieve_response_code( $raw_response );
		if ( 200 !== $response_code ) {
			return [];
		}

		$response = json_decode( wp_remote_retrieve_body( $raw_response ), true );

		// API error, api returned but something was wrong.
		if ( array_key_exists( 'success', $response ) && false === $response['success'] ) {
			return [];
		}

		$this->language_pack_updates_cache = $this->parse_language_pack_translations( $response['data'] );

		set_site_transient( $transient_key, $this->language_pack_updates_cache, DAY_IN_SECONDS );

		return $this->language_pack_updates_cache;
	}

	/**
	 * Parse the language pack translations.
	 *
	 * @param array $update_data Update data from translate.wordpress.com.
	 *
	 * @return array
	 */
	private function parse_language_pack_translations( $update_data ) {
		$installed_translations = wp_get_installed_translations( 'plugins' );
		$translations           = [];

		foreach ( $update_data as $plugin_name => $language_packs ) {
			foreach ( $language_packs as $language_pack ) {
				// Maybe we have this language pack already installed so lets check revision date.
				if ( array_key_exists( $plugin_name, $installed_translations ) && array_key_exists( $language_pack['wp_locale'], $installed_translations[ $plugin_name ] ) ) {
					$installed_translation_revision_time = new \DateTime( $installed_translations[ $plugin_name ][ $language_pack['wp_locale'] ]['PO-Revision-Date'] );
					$new_translation_revision_time       = new \DateTime( $language_pack['last_modified'] );

					// Skip if translation language pack is not newer than what is installed already.
					if ( $new_translation_revision_time <= $installed_translation_revision_time ) {
						continue;
					}
				}

				$translations[] = [
					'type'       => 'plugin',
					'slug'       => $plugin_name,
					'language'   => $language_pack['wp_locale'],
					'version'    => $language_pack['version'],
					'updated'    => $language_pack['last_modified'],
					'package'    => $language_pack['package'],
					'autoupdate' => true,
				];
			}
		}

		return $translations;
	}

	/**
	 * Fetches an instance of the class.
	 *
	 * @return self
	 */
	public static function instance() {
		if ( ! self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}
}
