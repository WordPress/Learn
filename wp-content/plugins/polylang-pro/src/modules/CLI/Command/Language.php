<?php
/**
 * @package Polylang-Pro
 */

namespace WP_Syntex\Polylang_Pro\Modules\CLI\Command;

use WP_CLI;
use WP_CLI\Utils;
use PLL_Language;
use WP_Syntex\Polylang\Model\Languages;
use WP_Syntex\Polylang_Pro\Modules\CLI\Formatter;

/**
 * Manages languages.
 *
 * @since 3.8
 *
 * @phpstan-import-type LanguageData from PLL_Language
 */
class Language {

	/**
	 * Languages model.
	 *
	 * @var Languages
	 */
	private $model;

	/**
	 * Constructor.
	 *
	 * @since 3.8
	 *
	 * @param Languages $model The languages model.
	 */
	public function __construct( Languages $model ) {
		$this->model = $model;
	}

	/**
	 * Creates a language.
	 *
	 * ## OPTIONS
	 *
	 * <locale>
	 * : WordPress locale or custom locale. If the locale is not found in the core languages list, it will be created but the name, slug, flag and direction will be mandatory.
	 *
	 * [--name=<name>]
	 * : Language name (used only for display). If not provided, will fallback to Polylang core languages list.
	 *
	 * [--slug=<slug>]
	 * : Language slug (ideally 2-letters ISO 639-1 language code). Must be unique. If not provided, will fallback to Polylang core languages list.
	 *
	 * [--flag=<flag>]
	 * : Country code for the flag. If not provided, will fallback to Polylang core languages list.
	 *
	 * [--dir=<dir>]
	 * : Text direction for the language.
	 * ---
	 * default: ltr
	 * options:
	 *   - ltr
	 *   - rtl
	 * ---
	 *
	 * [--order=<order>]
	 * : Language order when displayed.
	 * ---
	 * default: 0
	 * ---
	 *
	 * [--skip-default-cat]
	 * : If set, no default category will be created for this language.
	 *
	 * ## EXAMPLES
	 *
	 *     # Create a language using locale only (fallback to core languages list)
	 *     wp pll language create en_US
	 *
	 *     # Create a language with custom name and slug
	 *     wp pll language create en_US --name="English" --slug=en
	 *
	 *     # Create a French language with custom flag
	 *     wp pll language create fr_FR --name="Français" --slug=fr --flag=fr --order=1
	 *
	 *     # Create an Arabic language with RTL support
	 *     wp pll language create ar --dir=rtl --order=2
	 *
	 *     # Create a language without default category
	 *     wp pll language create es_ES --name="Español" --slug=es --skip-default-cat
	 *
	 * @param string[] $args       The command arguments.
	 * @param string[] $assoc_args The command associative arguments.
	 * @phpstan-param array{name?: string, slug?: string, flag?: string, dir?: string, order?: string, skip-default-cat?: bool} $assoc_args
	 *
	 * @since 3.8
	 */
	public function create( $args, $assoc_args ): void {
		list( $locale ) = $args;

		$language_args           = $this->prepare_language_args( $assoc_args );
		$language_args['locale'] = $locale;

		$language = $this->model->add( $language_args );

		if ( is_wp_error( $language ) ) {
			WP_CLI::error( $language->get_error_message() );
		}

		/** @var PLL_Language $language */
		WP_CLI::success( sprintf( 'Language "%1$s" (%2$s) created successfully.', $language->name, $language->slug ) );
	}

	/**
	 * Updates a language.
	 *
	 * ## OPTIONS
	 *
	 * <id-or-slug>
	 * : The ID or slug of the language to update. Must be unique among all languages.
	 *
	 * [--name=<name>]
	 * : Language name (used only for display).
	 *
	 * [--locale=<locale>]
	 * : WordPress locale. If something wrong is used for the locale, the .mo files will not be loaded.
	 *
	 * [--slug=<slug>]
	 * : New language slug (ideally 2-letters ISO 639-1 language code). Must be unique among all languages.
	 *
	 * [--dir=<dir>]
	 * : Text direction for the language.
	 * ---
	 * options:
	 *   - ltr
	 *   - rtl
	 * ---
	 *
	 * [--order=<order>]
	 * : Language order when displayed.
	 *
	 * [--flag=<flag>]
	 * : Country code for the flag.
	 *
	 * ## EXAMPLES
	 *
	 *     # Update language name only
	 *     wp pll language update en --name="English (US)"
	 *
	 *     # Update multiple properties
	 *     wp pll language update fr --name="Français" --locale=fr_FR --flag=fr --order=1
	 *
	 *     # Change language slug
	 *     wp pll language update en --new-slug=en_us
	 *
	 *     # Update text direction
	 *     wp pll language update ar --dir=rtl
	 *
	 *     # Update language order
	 *     wp pll language update es --order=3
	 *
	 * @param string[] $args       The command arguments.
	 * @param string[] $assoc_args The command associative arguments.
	 * @phpstan-param array{name?: string, locale?: string, new-slug?: string, dir?: string, order?: string, flag?: string} $assoc_args
	 *
	 * @since 3.8
	 */
	public function update( $args, $assoc_args ): void {
		list( $id_or_slug ) = $args;

		$language = $this->model->get( $id_or_slug );

		if ( empty( $language ) ) {
			WP_CLI::error( 'Language not found.' );
		}

		/** @var PLL_Language $language */
		$update_args = $this->prepare_language_args( $assoc_args, $language );

		$language = $this->model->update( $update_args );

		if ( is_wp_error( $language ) ) {
			WP_CLI::error( $language->get_error_message() );
		}

		/** @var PLL_Language $language */
		WP_CLI::success( sprintf( 'Language "%1$s" (%2$s) updated successfully.', $language->name, $language->slug ) );
	}

	/**
	 * Deletes a language.
	 *
	 * ## OPTIONS
	 *
	 * <id-or-slug>
	 * : The ID or slug of the language to delete.
	 *
	 * [--yes]
	 * : Answer yes to the confirmation message.
	 *
	 * ## EXAMPLES
	 *
	 *     # Delete English language
	 *     wp pll language delete en
	 *
	 * @param string[] $args The command arguments.
	 * @param string[] $assoc_args The command associative arguments.
	 * @phpstan-param array{yes?: bool} $assoc_args
	 *
	 * @since 3.8
	 */
	public function delete( $args, $assoc_args ): void {
		list( $id_or_slug ) = $args;

		$language = $this->model->get( $id_or_slug );

		if ( empty( $language ) ) {
			WP_CLI::error( 'Language not found.' );
		}

		/** @var PLL_Language $language */
		WP_CLI::confirm(
			sprintf( 'Are you sure you want to delete the language "%1$s" (%2$s)?', $language->name, $language->slug ),
			$assoc_args
		);

		$result = $this->model->delete( $language->term_id );

		if ( ! $result ) {
			WP_CLI::error( sprintf( 'Failed to delete the language "%1$s" (%2$s).', $language->name, $language->slug ) );
		}

		WP_CLI::success( sprintf( 'Language "%1$s" (%2$s) deleted successfully.', $language->name, $language->slug ) );
	}

	/**
	 * Lists languages.
	 *
	 * ## OPTIONS
	 *
	 * [--<field>=<value>]
	 * : Filter by one or more fields.
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
	 * [--field=<field>]
	 * : Prints the value of a single field for each language.
	 *
	 * [--fields=<fields>]
	 * : Comma-separated list of fields to display. Available fields are PLL_Language object properties.
	 *
	 * ## AVAILABLE FIELDS
	 *
	 * These fields will be displayed by default for each term:
	 *
	 * * name
	 * * slug
	 * * locale
	 * * term_id
	 * * order (term_group)
	 * * direction (dir)
	 * * active
	 * * default
	 *
	 * These fields are optionally available:
	 *
	 * * w3c
	 * * facebook
	 * * host
	 * * page_on_front
	 * * page_for_posts
	 * * flag_code
	 * * fallbacks
	 *
	 * ## EXAMPLES
	 *
	 *     # List all languages
	 *     wp pll language list
	 *
	 *     # List specific languages by slugs
	 *     wp pll language list --slug=en,fr,es
	 *
	 *     # List languages with specific output fields only
	 *     wp pll language list --fields=name,slug,locale
	 *
	 * @param string[] $args       The command arguments.
	 * @param string[] $assoc_args The command associative arguments.
	 * @phpstan-param array{field?: string, fields?: string, format?: string} $assoc_args
	 *
	 * @since 3.8
	 */
	public function list( $args, $assoc_args ): void {
		$languages = $this->model->get_list();

		$filters = $assoc_args;
		unset( $filters['fields'] );
		unset( $filters['format'] );
		unset( $filters['field'] );

		if ( ! empty( $filters ) ) {
			foreach ( $filters as $field => $filter_values ) {
				if ( ! property_exists( PLL_Language::class, (string) $field ) ) {
					WP_CLI::warning( sprintf( 'Invalid filter: %s.', $field ) );
					continue;
				}

				$filter_values = array_map( 'trim', explode( ',', (string) $filter_values ) );
				$languages     = array_filter(
					$languages,
					function ( $language ) use ( $field, $filter_values ) {
						// Shallower comparison on purpose, to avoid numeric string issues.
						return in_array( $language->$field, $filter_values ); // phpcs:ignore WordPress.PHP.StrictInArray.MissingTrueStrict
					}
				);
			}
		}

		$this->get_formatter( $assoc_args )->display_items( $this->normalize_languages( $languages ) );
	}

	/**
	 * Gets a language.
	 *
	 * ## OPTIONS
	 *
	 * <id-or-slug>
	 * : The ID or slug of the language to get.
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
	 * [--field=<field>]
	 * : Prints the value of a single field for each language.
	 *
	 * [--fields=<fields>]
	 * : Comma-separated list of fields to display. Available fields are PLL_Language object properties.
	 *
	 * ## AVAILABLE FIELDS
	 *
	 * These fields will be displayed by default for each term:
	 *
	 * * name
	 * * slug
	 * * locale
	 * * term_id
	 * * order (term_group)
	 * * direction (dir)
	 * * active
	 * * default
	 *
	 * These fields are optionally available:
	 *
	 * * w3c
	 * * facebook
	 * * host
	 * * page_on_front
	 * * page_for_posts
	 * * flag_code
	 * * fallbacks
	 *
	 * ## EXAMPLES
	 *
	 *     # Get a language by slug
	 *     wp pll language get en
	 *
	 *     # Get a language by ID
	 *     wp pll language get 1
	 *
	 * @param string[] $args       The command arguments.
	 * @param string[] $assoc_args The command associative arguments.
	 * @phpstan-param array{field?: string, fields?: string, format?: string} $assoc_args
	 *
	 * @since 3.8
	 */
	public function get( $args, $assoc_args ): void {
		if ( is_numeric( $args[0] ) ) {
			$assoc_args['term_id'] = $args[0];
		} else {
			$assoc_args['slug'] = $args[0];
		}
		$this->list( array(), $assoc_args );
	}

	/**
	 * Returns a list of normalized arrays from `PLL_Language` objects.
	 * Maps the `PLL_Language` object properties to the command output fields.
	 *
	 * @since 3.8
	 *
	 * @param PLL_Language[] $languages  The language objects.
	 * @return array<string, mixed> The normalized array.
	 */
	private function normalize_languages( array $languages ) {
		foreach ( $languages as &$language ) {
			$language = $language->to_array();

			$language['order']     = $language['term_group'];
			$language['direction'] = $language['is_rtl'] ? 'rtl' : 'ltr';
			$language['default']   = $language['is_default'];
			unset( $language['term_group'], $language['is_rtl'], $language['is_default'] );
		}

		return $languages;
	}

	/**
	 * Returns a formatter instance.
	 *
	 * @since 3.8
	 *
	 * @param array $assoc_args The command associative arguments.
	 * @return Formatter The formatter instance.
	 */
	private function get_formatter( array $assoc_args ) {
		return new Formatter(
			$assoc_args,
			array(
				'name',
				'slug',
				'locale',
				'term_id',
				'order',
				'direction',
				'active',
				'default',
			)
		);
	}

	/**
	 * Prepares the language arguments.
	 *
	 * @since 3.8
	 *
	 * @param array<string, bool|string> $assoc_args The command associative arguments.
	 * @param PLL_Language               $language The language object.
	 *
	 * @return array<string, mixed> The prepared language arguments.
	 *
	 * @phpstan-return array{lang_id: int, name: string, locale: string, slug: string, rtl: bool, term_group: int, flag: string}
	 */
	private function prepare_language_args( array $assoc_args, ?PLL_Language $language = null ): array {
		/** @var array{lang_id: int, name: string, locale: string, slug: string, rtl: bool, term_group: int, flag: string} */
		$parsed_args = array(
			'lang_id'    => $language ? $language->get_tax_prop( 'language', 'term_id' ) : null,
			'name'       => Utils\get_flag_value( $assoc_args, 'name', $language ? $language->name : null ),
			'locale'     => Utils\get_flag_value( $assoc_args, 'locale', $language ? $language->locale : null ),
			'slug'       => Utils\get_flag_value( $assoc_args, 'slug', $language ? $language->slug : null ),
			'rtl'        => 'rtl' === Utils\get_flag_value( $assoc_args, 'dir', $language ? ( $language->is_rtl ? 'rtl' : 'ltr' ) : null ),
			'term_group' => (int) Utils\get_flag_value( $assoc_args, 'order', $language ? $language->term_group : null ), // @phpstan-ignore cast.int
			'flag'       => Utils\get_flag_value( $assoc_args, 'flag', $language ? $language->flag_code : null ),
		);

		if ( Utils\get_flag_value( $assoc_args, 'skip-default-cat' ) ) {
			$parsed_args['no_default_cat'] = true;
		}

		return $parsed_args;
	}
}
