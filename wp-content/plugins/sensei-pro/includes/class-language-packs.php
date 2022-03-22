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
	const BASE_PACKAGE_URL         = 'https://raw.githubusercontent.com/Automattic/sensei-pro-language-packs/master/packages';
	const PLUGIN_TEXT_DOMAIN       = 'sensei-pro';
	const REMOTE_PACKAGE_TRANSIENT = 'sensei-pro-translations-';

	/**
	 * Request cache of the language pack updates available.
	 *
	 * @var array|null
	 */
	private static $language_pack_updates_cache;

	/**
	 * Instance of class.
	 *
	 * @var self
	 */
	private static $instance;

	/**
	 * Language_Packs constructor. Prevents other instances from being created outside of `Language_Packs::instance()`.
	 */
	private function __construct() {
	}

	/**
	 * Initialize actions and filters for the class.
	 */
	public function init() {
		add_filter( 'site_transient_update_plugins', [ $this, 'add_updated_translations' ] );
	}

	/**
	 * Adds the plugin's language pack updates to the `update_plugins` transient.
	 *
	 * @access private
	 *
	 * @param \stdClass $value Current value of `update_plugins` transient.
	 * @return \stdClass
	 */
	public function add_updated_translations( $value ) {
		if ( empty( $value ) ) {
			return $value;
		}
		$translations_available = $this->get_language_pack_updates();
		foreach ( $translations_available as $locale => $package ) {
			$value->translations[] = $this->prepare_package( $locale, $package );
		}
		return $value;
	}

	/**
	 * Prepare the language package to match what is stored in `update_plugins` transient.
	 *
	 * @param string $locale  Locale of the language package.
	 * @param array  $package Language package meta to prepare.
	 * @return array
	 */
	private function prepare_package( $locale, $package ) {
		$package['type']       = 'plugin';
		$package['slug']       = self::PLUGIN_TEXT_DOMAIN;
		$package['version']    = SENSEI_PRO_VERSION;
		$package['language']   = $locale;
		$package['autoupdate'] = true;

		return $package;
	}

	/**
	 * Gets the available language package updates.
	 *
	 * @return array
	 */
	private function get_language_pack_updates() {
		if ( ! is_null( self::$language_pack_updates_cache ) ) {
			return self::$language_pack_updates_cache;
		}

		$available_translations        = $this->get_available_translations();
		$installed_translations        = wp_get_installed_translations( 'plugins' );
		$installed_plugin_translations = [];
		if ( isset( $installed_translations[ self::PLUGIN_TEXT_DOMAIN ] ) ) {
			$installed_plugin_translations = $installed_translations[ self::PLUGIN_TEXT_DOMAIN ];
		}

		$updates = [];
		foreach ( $available_translations as $locale => $package ) {
			if ( ! isset( $installed_plugin_translations[ $locale ] ) || $this->is_package_newer( $package, $installed_plugin_translations[ $locale ] ) ) {
				$updates[ $locale ] = $package;
			}
		}

		self::$language_pack_updates_cache = $updates;

		return $updates;
	}

	/**
	 * Checks if language package is newer than the PO file installed.
	 *
	 * @param array $package Package data from the remote provider.
	 * @param array $po_meta PO meta information pulled from file header.
	 * @return bool
	 */
	private function is_package_newer( $package, $po_meta ) {
		if ( empty( $package['updated'] ) || empty( $po_meta['PO-Revision-Date'] ) ) {
			return true;
		}

		$package_updated = strtotime( $package['updated'] );
		$po_updated      = strtotime( $po_meta['PO-Revision-Date'] );

		return $package_updated > $po_updated;
	}

	/**
	 * Gets all the available translation packages for the locales that WordPress cares about.
	 *
	 * @return array
	 */
	private function get_available_translations() {
		$locales      = array_values( get_available_languages() );
		$package_data = $this->get_version_package_data();
		$packages     = [];
		foreach ( $locales as $locale ) {
			if ( ! isset( $package_data['packages'][ $locale ] ) ) {
				continue;
			}
			$packages[ $locale ] = $package_data['packages'][ $locale ];
		}
		return $packages;
	}

	/**
	 * Gets the remote package meta information for this version of Sensei PRO.
	 *
	 * @return array
	 */
	private function get_version_package_data() {
		$transient_key = self::REMOTE_PACKAGE_TRANSIENT . SENSEI_PRO_VERSION;
		$data          = get_site_transient( $transient_key );

		if ( false !== $data && is_array( $data ) ) {
			return $data;
		}

		$cache_exp = HOUR_IN_SECONDS;
		$data      = [
			'version'  => SENSEI_PRO_VERSION,
			'packages' => [],
		];

		$response = wp_safe_remote_get( sprintf( self::BASE_PACKAGE_URL . '/%s/index.json', SENSEI_PRO_VERSION ) );

		if ( ! is_wp_error( $response ) && ! empty( $response['body'] ) ) {
			$data_raw = json_decode( $response['body'], true );

			if ( ! empty( $data_raw ) && ! empty( $data_raw['packages'] ) ) {
				$cache_exp = DAY_IN_SECONDS;
				$data      = $data_raw;
			}
		}

		foreach ( $data['packages'] as $locale => $package ) {
			$data['packages'][ $locale ]['package'] = sprintf( self::BASE_PACKAGE_URL . '/%1$s/%2$s.zip', SENSEI_PRO_VERSION, $locale );
		}

		set_site_transient( $transient_key, $data, $cache_exp );

		return $data;
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
