<?php
/**
 * @package Polylang Updater
 */

namespace WP_Syntex\Polylang_Pro\Updater;

use DateTime;
use stdClass;

defined( 'ABSPATH' ) || exit;

/**
 * Allows to download translations from TranslationsPress.
 * This is a modified version of the library available at https://github.com/WP-Translations/t15s-registry.
 * This version supports only WP Syntex's plugins.
 *
 * @since 1.0
 */
class Translations {
	/**
	 * Transient key.
	 *
	 * @var string
	 */
	public const TRANSIENT_KEY = 't15s-polylang';

	/**
	 * TranslationsPress API URL for the company.
	 *
	 * @var string
	 */
	private const API_URL = 'https://packages.translationspress.com/wp-syntex/packages.json';

	/**
	 * Project directory slug.
	 *
	 * @var string
	 */
	private $slug;

	/**
	 * Installed translations.
	 *
	 * @var array|null
	 */
	private static $installed_translations;

	/**
	 * Available languages.
	 *
	 * @var array|null
	 */
	private static $available_languages;

	/**
	 * Adds a new project to load translations for.
	 *
	 * @since 1.0
	 *
	 * @param string $slug Project directory slug.
	 */
	public function __construct( string $slug ) {
		$this->slug = $slug;

		add_action( 'init', array( self::class, 'register_clean_translations_cache' ), 9999 );
		add_filter( 'translations_api', array( $this, 'translations_api' ), 10, 3 );
		add_filter( 'site_transient_update_plugins', array( $this, 'site_transient_update_plugins' ) );
	}

	/**
	 * Short-circuits translations API requests for private projects.
	 * Hooked to `translations_api`.
	 *
	 * @since 1.0
	 *
	 * @param bool|array $result         The result object. Default false.
	 * @param string     $requested_type The type of translations being requested.
	 * @param object     $args           Translation API arguments.
	 * @return bool|array
	 */
	public function translations_api( $result, $requested_type, $args ) {
		$args = (array) $args; // The type of this param ins not clear in WP (object|array|null).
		if ( 'plugins' === $requested_type && $this->slug === $args['slug'] ) {
			return self::get_translations( $args['slug'] );
		}

		return $result;
	}

	/**
	 * Filters the translations transients to include the private plugin.
	 * Hooked to `site_transient_update_plugins`.
	 *
	 * @see wp_get_translation_updates().
	 *
	 * @since 1.0
	 *
	 * @param stdClass|false $value The transient value.
	 * @return stdClass|false
	 */
	public function site_transient_update_plugins( $value ) {
		if ( ! $value instanceof stdClass ) {
			$value = new stdClass();
		}

		if ( ! isset( $value->translations ) ) {
			$value->translations = array();
		}

		$translations = self::get_translations( $this->slug );

		if ( ! isset( $translations['translations'] ) ) {
			return $value;
		}

		$installed_translations = self::get_installed_translations();

		foreach ( (array) $translations['translations'] as $translation ) {
			if ( in_array( $translation['language'], self::get_available_languages(), true ) ) {
				if ( isset( $installed_translations[ $this->slug ][ $translation['language'] ] ) && $translation['updated'] ) {
					$local  = new DateTime( $installed_translations[ $this->slug ][ $translation['language'] ]['PO-Revision-Date'] );
					$remote = new DateTime( $translation['updated'] );

					if ( $local >= $remote ) {
						continue;
					}
				}

				$translation['type'] = 'plugin';
				$translation['slug'] = $this->slug;

				$value->translations[] = $translation;
			}
		}

		return $value;
	}

	/**
	 * Registers actions for clearing translation caches.
	 * Hooked to `init`.
	 *
	 * @since 1.0
	 *
	 * @return void
	 */
	public static function register_clean_translations_cache(): void {
		add_action( 'set_site_transient_update_plugins', array( self::class, 'clean_translations_cache' ) );
		add_action( 'delete_site_transient_update_plugins', array( self::class, 'clean_translations_cache' ) );
	}

	/**
	 * Clears existing translation cache.
	 *
	 * @since 1.0
	 *
	 * @return void
	 */
	public static function clean_translations_cache(): void {
		$translations = get_site_transient( self::TRANSIENT_KEY );

		if ( ! is_object( $translations ) ) {
			return;
		}

		/*
		 * Don't delete the cache if the transient gets changed multiple times
		 * during a single request. Set cache lifetime to maximum 15 seconds.
		 */
		$cache_lifespan   = 15;
		$time_not_changed = isset( $translations->_last_checked ) && ( time() - $translations->_last_checked ) > $cache_lifespan;

		if ( ! $time_not_changed ) {
			return;
		}

		delete_site_transient( self::TRANSIENT_KEY );
	}

	/**
	 * Returns the translations for a given project.
	 *
	 * @since 1.0
	 *
	 * @param string $slug Project directory slug.
	 * @return array Translation data.
	 */
	private static function get_translations( string $slug ): array {
		$translations = get_site_transient( self::TRANSIENT_KEY );

		if ( ! $translations instanceof stdClass ) {
			$translations = new stdClass();
		}

		if ( isset( $translations->{$slug} ) && is_array( $translations->{$slug} ) ) {
			return $translations->{$slug};
		}

		$translations = json_decode( wp_remote_retrieve_body( wp_remote_get( self::API_URL, array( 'timeout' => 3 ) ) ), true );

		if ( is_array( $translations ) ) {
			$translations = (object) $translations;
		} else {
			$translations = new stdClass();
		}

		$translations->_last_checked = time();

		set_site_transient( self::TRANSIENT_KEY, $translations );

		if ( isset( $translations->{$slug} ) && is_array( $translations->{$slug} ) ) {
			return $translations->{$slug};
		}

		return array();
	}

	/**
	 * Returns installed translations.
	 *
	 * Used to cache the result of `wp_get_installed_translations()` as it is very expensive.
	 *
	 * @since 1.0
	 *
	 * @return array
	 */
	private static function get_installed_translations(): array {
		if ( null === self::$installed_translations ) {
			self::$installed_translations = wp_get_installed_translations( 'plugins' );
		}
		return self::$installed_translations;
	}

	/**
	 * Returns available languages.
	 *
	 * Used to cache the result of `get_available_languages()` as it is very expensive.
	 *
	 * @since 1.0
	 *
	 * @return array
	 */
	private static function get_available_languages(): array {
		if ( null === self::$available_languages ) {
			self::$available_languages = (array) get_available_languages();
		}
		return self::$available_languages;
	}
}
