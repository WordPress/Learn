<?php
/**
 * @package Polylang-Pro
 */

use Tribe\Events\Views\V2\Rest_Endpoint;
use Tribe__Events__Main as TEC;
use Tribe__Events__Venue as Venue;
use Tribe__Events__Organizer as Organizer;

/**
 * A class to manage integration with the Event Calendar
 * Version tested: 5.7.0
 *
 * @since 2.2
 */
class PLL_TEC {
	/**
	 * Current language (used to filter the content).
	 *
	 * @var PLL_Language|null
	 */
	protected $curlang;

	/**
	 * The main Polylang object.
	 *
	 * @var PLL_Settings|PLL_Admin|PLL_REST_Request|PLL_Frontend
	 */
	protected $polylang;

	/**
	 * Links Model for translating slugs.
	 *
	 * @var PLL_Translate_Slugs_Model|null
	 */
	protected $slugs_model;

	/**
	 * Cache for the method `is_tec_rest_request()`.
	 *
	 * @var mixed[]
	 */
	protected $is_tec_rest_request = array();

	/**
	 * The list of IDs of translatable slugs dedicated to TEC.
	 *
	 * @var string[]
	 */
	protected $translatable_slug_ids = array();

	/**
	 * The list of post metas to synchronize.
	 *
	 * @var string[]
	 */
	protected static $metas;

	/**
	 * Initializes filters and actions
	 *
	 * @since 2.2
	 *
	 * @param  PLL_Settings|PLL_Admin|PLL_REST_Request|PLL_Frontend $polylang The main Polylang object.
	 * @return void
	 */
	public function init( $polylang ) {
		if ( ! $polylang->model->has_languages() ) {
			return;
		}

		$this->polylang              = $polylang;
		$this->curlang               = null;
		$this->slugs_model           = ! empty( $polylang->translate_slugs ) && ! empty( $polylang->translate_slugs->slugs_model ) ? $polylang->translate_slugs->slugs_model : null;
		$this->is_tec_rest_request   = array();
		$this->translatable_slug_ids = array();

		add_filter( 'pll_get_taxonomies', array( $this, 'translate_taxonomies' ), 10, 2 );
		add_filter( 'pll_get_post_types', array( $this, 'translate_types' ), 10, 2 );

		add_action( 'save_post_' . Venue::POSTTYPE, array( $this, 'set_language' ), 10, 3 );
		add_action( 'save_post_' . Organizer::POSTTYPE, array( $this, 'set_language' ), 10, 3 );

		$tec = TEC::instance();

		if ( empty( $polylang->options['force_lang'] ) ) {
			add_action( 'pll_language_defined', array( $this, 'fix_date_translations' ) );
		}

		self::$metas = array_merge( $tec->metaTags, $tec->venueTags, $tec->organizerTags, array( '_VenueShowMap', '_VenueShowMapLink' ) );

		if ( isset( $GLOBALS['pagenow'], $_GET['from_post'], $_GET['new_lang'] ) && 'post-new.php' === $GLOBALS['pagenow'] ) {
			check_admin_referer( 'new-post-translation' );

			// Defaults values for events
			foreach ( self::$metas as $meta ) {
				$filter = str_replace( array( '_Event', '_Organizer', '_Venue' ), array( '', 'Organizer', 'Venue' ), $meta );
				add_filter( 'tribe_get_meta_default_value_' . $filter, array( $this, 'copy_event_meta' ), 10, 4 ); // Since TEC 4.0.7.
			}

			add_filter( 'tribe_display_event_linked_post_dropdown_id', array( $this, 'translate_linked_post' ) );
		}

		add_action( 'pre_get_posts', array( $this, 'pre_get_posts' ), 60 ); // After `Tribe__Events__Query->pre_get_posts()`.

		add_filter( 'pll_copy_post_metas', array( $this, 'copy_post_metas' ) );
		add_filter( 'pll_translate_post_meta', array( $this, 'translate_meta' ), 10, 3 );

		// Translate links with translated slugs
		add_action( 'init', array( $this, 'reset_slugs' ), 11 ); // Just after `Tribe__Events__Main->init()`.
		add_filter( 'register_taxonomy_args', array( $this, 'register_taxonomy_args' ), 10, 2 );
		add_filter( 'tribe_events_get_link', array( $this, 'get_link' ) );
		add_filter( 'pll_get_archive_url', array( $this, 'pll_get_archive_url' ), 10, 2 );
		add_filter( 'pll_term_link', array( $this, 'filter_tec_term_link' ), 5, 3 ); // Before `PLL_Translate_Slugs->pll_term_link()`.
		add_filter( 'pll_translated_slugs', array( $this, 'pll_translated_slugs' ), 10, 3 );
		add_filter( 'pll_sanitize_string_translation', array( $this, 'sanitize_string_translation' ), 10, 2 );
		add_filter( 'tribe_events_rewrite_i18n_slugs_raw', array( $this, 'rewrite_slugs' ) );

		// Options to translate.
		$keys = array(
			'dateWithYearFormat'    => 1,
			'dateWithoutYearFormat' => 1,
			'monthAndYearFormat'    => 1,
			'dateTimeSeparator'     => 1,
			'timeRangeSeparator'    => 1,
			'tribeEventsBeforeHTML' => 1,
			'tribeEventsAfterHTML'  => 1,
		);

		$args = array(
			'context'           => 'The Events Calendar',
			'sanitize_callback' => array( $this, 'sanitize_strings' ),
		);

		new PLL_Translate_Option( 'tribe_events_calendar_options', $keys, $args );

		// TEC views V2.
		add_filter( 'tribe_events_rewrite_i18n_domains', '__return_empty_array', 10000 ); // No i18n domains, no translations to deal with.
		add_filter( 'tribe_events_rewrite_i18n_slugs', array( $this, 'fix_escaped_dashes_in_slugs' ), 10, 2 );

		add_filter( 'tribe_events_category_slug', array( $this, 'get_category_slug' ) );
		add_filter( 'tribe_events_tag_slug', array( $this, 'get_tag_slug' ) );

		add_filter( 'tribe_events_views_v2_endpoint_url', array( $this, 'add_missing_lang_to_rest_url' ) );
		add_filter( 'locale', array( $this, 'filter_locale_for_rest' ), 5 );

		add_filter( 'tribe_events_views_v2_publicly_visible_views_query_args', array( $this, 'add_language_to_publicly_visible_views_query_args' ), 5 );
		add_filter( 'tribe_events_views_v2_view_template_vars', array( $this, 'translate_widget_view_more_link' ), 5 );
		add_filter( 'tribe_rewrite_parse_query_vars', array( $this, 'force_language_on_tec_parse_query_vars' ), 10, 3 );
		add_filter( 'tribe_events_views_v2_url_query_args', array( $this, 'add_missing_lang_to_query_arg' ) );
		add_filter( 'tribe_rewrite_pre_canonical_url', array( $this, 'add_missing_lang_to_non_canonical_url' ), 10, 2 );
		add_filter( 'tribe_rewrite_pre_canonical_url', array( $this, 'remove_name_arg_from_non_canonical_url' ), 10, 2 );

		add_filter( 'tribe_rewrite_canonical_url', array( $this, 'fix_language_in_canonical_url' ), 5, 2 );
		add_filter( 'tribe_rewrite_canonical_url', array( $this, 'translate_canonical_url' ), 5, 2 );
	}

	/**
	 * Language and translation management for taxonomies.
	 *
	 * @since 2.2
	 *
	 * @param string[] $taxonomies List of taxonomy names for which Polylang manages language and translations.
	 * @param bool     $hide       True when displaying the list in Polylang settings.
	 * @return string[] List of taxonomy names for which Polylang manages language and translations.
	 */
	public function translate_taxonomies( $taxonomies, $hide ) {
		// Hide from Polylang settings
		return $hide ? array_diff( $taxonomies, array( TEC::TAXONOMY ) ) : array_merge( $taxonomies, array( TEC::TAXONOMY ) );
	}

	/**
	 * Language and translation management for custom post types.
	 *
	 * @since 2.2
	 *
	 * @param string[] $types List of post type names for which Polylang manages language and translations.
	 * @param bool     $hide  True when displaying the list in Polylang settings.
	 * @return string[] List of post type names for which Polylang manages language and translations.
	 */
	public function translate_types( $types, $hide ) {
		$tec_types = array( TEC::POSTTYPE, TEC::VENUE_POST_TYPE, TEC::ORGANIZER_POST_TYPE );
		return $hide ? array_diff( $types, $tec_types ) : array_merge( $types, $tec_types );
	}

	/**
	 * Save the language of Venues and Organizers.
	 * Needed when they are created from the Event form.
	 *
	 * @since 2.2
	 *
	 * @param int     $post_id Post id.
	 * @param WP_Post $post    Post object.
	 * @param bool    $update  Whether it is an update or not.
	 * @return void
	 */
	public function set_language( $post_id, $post, $update ) {
		if ( $update || ! isset( $_POST['post_lang_choice'] ) ) {
			return;
		}

		$post_type_object = get_post_type_object( $post->post_type );

		if ( ! $post_type_object || ! current_user_can( $post_type_object->cap->create_posts ) ) {
			return;
		}

		check_admin_referer( 'pll_language', '_pll_nonce' );
		$lang = $this->polylang->model->get_language( sanitize_key( $_POST['post_lang_choice'] ) );

		if ( ! $lang ) {
			return;
		}

		$this->polylang->model->post->set_language( $post_id, $lang );
	}

	/**
	 * Once the language is set from content, this resets all the date-related translations in TEC to the current lang.
	 * In `Tribe__Date_Utils`, TEC stores these translations in static private properties before the language is set in
	 * PLL. Then these translations are use later, AFTER the language is set in PLL, leading to views exploding due to
	 * array keys not being set.
	 * The filter 'tribe_events_get_days_of_week' can't be used because it doesn't include the function's arg `$format`.
	 *
	 * @since 3.1
	 * @see   tribe_events_get_days_of_week()
	 *
	 * @return void
	 */
	public function fix_date_translations() {
		$properties = array(
			'localized_months_full',
			'localized_months_short',
			'localized_weekdays',
			'localized_months',
		);

		foreach ( $properties as $property ) {
			$property = new ReflectionProperty( Tribe__Date_Utils::class, $property );
			$property->setAccessible( true );
			$property->setValue( array() );
		}

		TEC::instance()->setup_l10n_strings();
	}

	/**
	 * Populates default event metas for a newly created event translation
	 *
	 * @since 2.2
	 *
	 * @param mixed  $value  Meta value.
	 * @param int    $id     Post id.
	 * @param string $meta   Meta key.
	 * @param bool   $single Whether to return a single value.
	 * @return mixed
	 */
	public function copy_event_meta( $value, $id, $meta, $single ) {
		if ( ! empty( $_GET['from_post'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
			$value = get_post_meta( (int) $_GET['from_post'], $meta, $single ); // phpcs:ignore WordPress.Security.NonceVerification
		}
		return $value;
	}

	/**
	 * Populates default values for venues and organizers for a newly created event translation.
	 *
	 * @since 2.2
	 *
	 * @param array $posts Array of linked posts.
	 * @return array
	 */
	public function translate_linked_post( $posts ) {
		if ( empty( $posts ) || ! isset( $_GET['new_lang'] ) ) {
			return $posts;
		}

		check_admin_referer( 'new-post-translation' );

		$lang = $this->polylang->model->get_language( sanitize_key( $_GET['new_lang'] ) ); // Make sure this is a valid language.

		if ( empty( $lang ) ) {
			return $posts;
		}

		$lang       = $lang->slug;
		$post_metas = ! empty( $this->polylang->sync ) && ! empty( $this->polylang->sync->post_metas ) ? $this->polylang->sync->post_metas : false;

		foreach ( $posts as $key => $post_id ) {
			$tr_id = pll_get_post( $post_id, $lang );

			if ( ! empty( $tr_id ) ) {
				$posts[ $key ] = $tr_id;
				continue;
			}

			// If the translated venue or organizer doesn't exist, create it.
			$post = get_post( $post_id, ARRAY_A ); // Output as an array for `wp_insert_post()`.

			if ( empty( $post ) ) {
				// `null` value.
				continue;
			}

			unset( $post['ID'] );

			$tr_id = wp_insert_post( wp_slash( $post ) );

			if ( ! is_int( $tr_id ) ) {
				// `WP_Error` value.
				continue;
			}

			$translations          = pll_get_post_translations( $post_id );
			$translations[ $lang ] = $tr_id;

			pll_set_post_language( $tr_id, $lang );
			pll_save_post_translations( $translations );

			if ( ! empty( $post_metas ) ) {
				$post_metas->copy( $post_id, $tr_id, $lang );
			}

			$posts[ $key ] = $tr_id;
		}

		return $posts;
	}

	/**
	 * Removes date filters when searching for untranslated events in the metabox autocomplete field
	 *
	 * @since 2.2.8
	 *
	 * @return void
	 */
	public function pre_get_posts() {
		if ( wp_doing_ajax() && isset( $_GET['action'] ) && 'pll_posts_not_translated' === $_GET['action'] ) { // phpcs:ignore WordPress.Security.NonceVerification
			// See Tribe__Events__Query::pre_get_posts() when should_remove_date_filters() returns true
			remove_filter( 'posts_where', array( 'Tribe__Events__Query', 'posts_where' ) );
			remove_filter( 'posts_fields', array( 'Tribe__Events__Query', 'posts_fields' ) );
			remove_filter( 'posts_orderby', array( 'Tribe__Events__Query', 'posts_orderby' ) );
		}
	}

	/**
	 * Synchronize event metas.
	 *
	 * @since 2.2
	 *
	 * @param array $metas Custom fields to copy or synchronize.
	 * @return array
	 */
	public function copy_post_metas( $metas ) {
		return array_merge( $metas, self::$metas );
	}

	/**
	 * Translate venues and organizers before they are copied or synchronized.
	 *
	 * @since 2.3
	 *
	 * @param mixed  $value Meta value.
	 * @param string $key   Meta key.
	 * @param string $lang  Language of target.
	 * @return mixed
	 */
	public function translate_meta( $value, $key, $lang ) {
		if ( ( '_EventVenueID' === $key || '_EventOrganizerID' === $key ) && $tr_value = pll_get_post( $value, $lang ) ) {
			$value = $tr_value;
		}
		return $value;
	}

	/**
	 * Resets all TEC translated slugs to an English value as the TEC slug translation system
	 * does not work in a multilingual context (TEC 4.4.5 + WP 4.7.3).
	 *
	 * @since 2.2
	 *
	 * @return void
	 */
	public function reset_slugs() {
		$tec = TEC::instance();

		foreach ( $this->get_slugs_to_reset() as $key => $slug ) {
			$tec->$key = $slug;
		}

		// Those are deprecated since TEC 4.0 and should not appear in the list of translatable strings anymore.
		$tec->taxRewriteSlug = $tec->rewriteSlug . '/category';
		$tec->tagRewriteSlug = $tec->rewriteSlug . '/tag';
	}

	/**
	 * Resets the category base rewrite slug in taxonomy.
	 *
	 * @since 2.2
	 *
	 * @param array  $args     Array of arguments for registering a taxonomy.
	 * @param string $taxonomy Taxonomy key.
	 * @return array
	 */
	public function register_taxonomy_args( $args, $taxonomy ) {
		if ( TEC::TAXONOMY === $taxonomy && is_array( $args['rewrite'] ) ) {
			$args['rewrite']['slug'] = TEC::instance()->rewriteSlug . '/category';
		}

		return $args;
	}

	/**
	 * Filters the links to add the language code.
	 *
	 * @since 2.2
	 *
	 * @param string $link Link generated by The Events Calendar.
	 * @return string
	 */
	public function get_link( $link ) {
		$curlang = $this->get_curlang();

		if ( empty( $curlang ) || empty( $this->slugs_model ) ) {
			return $link;
		}

		$link = $this->polylang->links_model->add_language_to_link( $link, $curlang );

		foreach ( $this->get_translatable_slug_ids() as $slug_id ) {
			$link = $this->slugs_model->translate_slug( $link, $curlang, $slug_id );
		}

		return $link;
	}

	/**
	 * Translates slugs in the language switcher.
	 *
	 * @since 2.2
	 *
	 * @param string       $url      Url in the language switcher.
	 * @param PLL_Language $language Language object.
	 * @return string Modified url.
	 */
	public function pll_get_archive_url( $url, $language ) {
		if ( empty( $this->slugs_model ) || ! is_post_type_archive( TEC::POSTTYPE ) ) {
			return $url;
		}

		foreach ( $this->get_translatable_slug_ids() as $slug_id ) {
			$url = $this->slugs_model->switch_translated_slug( $url, $language, $slug_id );
		}

		return $url;
	}

	/**
	 * Modifies term links for the taxonomies used by TEC.
	 * In `PLL_TEC->reset_slugs()` we don't include the full taxonomy slug (`Tribe__Events__Main->taxRewriteSlug`) in
	 * our translated strings, as it is deprecated in TEC 4.0 and also a composite of
	 * `{post type archive slug}/{tax base slug}`. This explains why this method is needed.
	 *
	 * @since 3.1
	 * @see   PLL_TEC->reset_slugs()
	 * @see   PLL_TEC->pll_translated_slugs()
	 *
	 * @param  string       $url  The term link.
	 * @param  PLL_Language $lang The term language.
	 * @param  WP_Term      $term The term object.
	 * @return string
	 */
	public function filter_tec_term_link( $url, $lang, $term ) {
		if ( empty( $this->slugs_model ) || TEC::TAXONOMY !== $term->taxonomy ) {
			return $url;
		}

		$url = $this->slugs_model->translate_slug( $url, $lang, 'archive_' . TEC::POSTTYPE );
		return $this->slugs_model->translate_slug( $url, $lang, 'tribe_category' );
	}

	/**
	 * Fixes the events slug in translatable slugs.
	 * Translates other TEC slugs.
	 *
	 * @since 2.2
	 *
	 * @param array        $slugs    Translated slugs.
	 * @param PLL_Language $language Language object.
	 * @param PLL_MO       $mo       Strings translations object.
	 * @return array
	 */
	public function pll_translated_slugs( $slugs, $language, &$mo ) {
		/**
		 * In `PLL_TEC->reset_slugs()` we don't include the full taxonomy slug (`Tribe__Events__Main->taxRewriteSlug`) in
		 * our translated strings, as it is deprecated in TEC 4.0 and also a composite of
		 * `{post type archive slug}/{tax base slug}`. This is why we unset `$slugs[ TEC::TAXONOMY ]` here.
		 */
		unset( $slugs[ 'archive_' . TEC::POSTTYPE ]['hide'], $slugs[ TEC::TAXONOMY ] );

		$slugs[ 'archive_' . TEC::POSTTYPE ]['slug'] = $slug = TEC::instance()->getRewriteSlug();
		$tr_slug = $mo->translate( $slug );
		$slugs[ 'archive_' . TEC::POSTTYPE ]['translations'][ $language->slug ] = empty( $tr_slug ) ? $slug : $tr_slug;

		foreach ( $this->get_slugs_to_reset() as $slug ) {
			$slugs[ 'tribe_' . $slug ]['slug'] = $slug;
			$tr_slug = $mo->translate( $slug );
			$slugs[ 'tribe_' . $slug ]['translations'][ $language->slug ] = empty( $tr_slug ) ? $slug : $tr_slug;
		}

		return $slugs;
	}

	/**
	 * Performs the sanitization ( before saving in DB ) of slugs translations
	 * The Events Calendar does not accept accents, but let's accept slashes for the event category slug
	 *
	 * @since 1.9
	 *
	 * @param string $translation Translation to sanitize.
	 * @param string $name        Unique name for the string.
	 * @return string
	 */
	public function sanitize_string_translation( $translation, $name ) {
		if ( 'slug_archive_tribe_events' === $name || 0 === strpos( $name, 'slug_tribe_' ) ) {
			$slugs = explode( '/', $translation );
			$slugs = array_map( 'sanitize_title', $slugs );
			return implode( '/', $slugs );
		}
		return $translation;
	}

	/**
	 * Add translated slugs to specific TEC rewrite rules.
	 *
	 * @since 2.2
	 *
	 * @param array $bases Array of arrays of rewrite base slugs.
	 * @return array
	 */
	public function rewrite_slugs( $bases ) {
		$translatable_slugs = $this->get_translatable_slugs();

		if ( empty( $translatable_slugs ) ) {
			return $bases;
		}

		foreach ( $bases as $type => $base ) {
			$default_slug = reset( $base );

			foreach ( $translatable_slugs as $slugs ) {
				if ( $slugs['slug'] === $default_slug ) {
					$bases[ $type ] = array_unique( array_merge( array( $default_slug ), $slugs['translations'] ) );
					break;
				}
			}
		}

		return $bases;
	}

	/**
	 * Translated strings must be sanitized the same way The Events Calendar does before they are saved.
	 * All are of validation_type 'html'.
	 *
	 * @since 2.2
	 *
	 * @param string $translation Translated string.
	 * @param string $name        String name.
	 * @param string $context     String context.
	 * @return string Sanitized translation.
	 */
	public function sanitize_strings( $translation, $name, $context ) {
		if ( 'The Events Calendar' === $context ) {
			$translation = balanceTags( $translation );
		}

		return $translation;
	}

	/**
	 * Filters TEC's base slugs to unescape dashes.
	 *
	 * If `$method` is 'regex', `Tribe__Events__Rewrite->get_bases()` will use `preg_quote()` to get
	 * its slugs ready as regex patterns. However, `-` characters are valid in this context and
	 * should not be escaped (reminder: they come from PLL's string translations).
	 *
	 * @since 3.1
	 * @see   $this->rewrite_slugs()
	 *
	 * @param  string[] $bases  An array of rewrite bases that have been generated.
	 * @param  string   $method The method that's being used to generate the bases; defaults to `regex`.
	 * @return string[]
	 */
	public function fix_escaped_dashes_in_slugs( $bases, $method ) {
		if ( 'regex' !== $method ) {
			return $bases;
		}

		return array_map(
			function ( $base ) {
				return str_replace( '\\-', '-', $base );
			},
			$bases
		);
	}

	/**
	 * Filters the string to be used as the taxonomy slug.
	 * This replaces TEC's translated category slug by the untranslated one, as it is returned by the public method
	 * `Tribe__Events__Main->get_category_slug()`.
	 *
	 * @since 3.1
	 *
	 * @return string
	 */
	public function get_category_slug() {
		return 'category';
	}

	/**
	 * Filters the string to be used as the tag slug.
	 * This replaces TEC's translated tag slug by the untranslated one, as it is returned by a public method
	 * `Tribe__Events__Main->get_tag_slug()`.
	 *
	 * @since 3.1
	 *
	 * @return string
	 */
	public function get_tag_slug() {
		return 'tag';
	}

	/**
	 * Adds the lang to TEC's REST URL.
	 * This provides a way to identify in whish language PLL should work in the REST request.
	 *
	 * @since 3.1
	 *
	 * @param  string $url The View endpoint URL, either a REST API URL or a admin-ajax.php fallback URL if REST API
	 *                     is not available.
	 * @return string
	 */
	public function add_missing_lang_to_rest_url( $url ) {
		$curlang = $this->get_curlang();

		if ( empty( $curlang ) ) {
			return $url;
		}

		$lang = $this->get_lang_from_url_query_arg( $url );

		if ( ! empty( $lang ) ) {
			return $url;
		}

		return add_query_arg( array( 'lang' => $curlang->slug ), $url );
	}

	/**
	 * Filters the locale when TEC is performing a REST request.
	 *
	 * @since 3.1
	 *
	 * @param  string $locale The locale ID.
	 * @return string
	 */
	public function filter_locale_for_rest( $locale ) {
		if ( ! $this->is_tec_rest_request() ) {
			return $locale;
		}

		$curlang = $this->get_curlang();

		if ( empty( $curlang->locale ) ) {
			return $locale;
		}

		return $curlang->locale;
	}

	/**
	 * Filters the query arguments that should be applied to the View links to add the missing language.
	 * The added language is the global current language.
	 *
	 * @since 3.1
	 *
	 * @param  mixed[] $url_args The current URL query arguments, created from a filtered version of the current
	 *                           request context.
	 * @return mixed[]
	 */
	public function add_language_to_publicly_visible_views_query_args( $url_args ) {
		$curlang = $this->get_curlang();

		if ( ! empty( $curlang ) ) {
			$url_args['lang'] = $curlang->slug;
		}
		return $url_args;
	}

	/**
	 * Fixes the "upcoming events" widget link.
	 *
	 * @since 3.1
	 *
	 * @param  mixed[] $template_vars An associative array of template variables. Variables will be extracted in the
	 *                                template hence the key will be the name of the variable available in the template.
	 * @return mixed[]
	 */
	public function translate_widget_view_more_link( $template_vars ) {
		if ( ! isset( $template_vars['view_more_link'] ) ) {
			return $template_vars;
		}

		$curlang = $this->get_curlang();

		if ( empty( $curlang ) || empty( $this->slugs_model ) ) {
			return $template_vars;
		}

		$template_vars['view_more_link'] = home_url( '/' . tribe_get_option( 'eventsSlug', 'events' ) );
		$template_vars['view_more_link'] = $this->polylang->links_model->add_language_to_link( $template_vars['view_more_link'], $curlang );
		$template_vars['view_more_link'] = $this->slugs_model->translate_slug( $template_vars['view_more_link'], $curlang, 'archive_' . TEC::POSTTYPE );
		$template_vars['view_more_link'] = user_trailingslashit( $template_vars['view_more_link'] );

		return $template_vars;
	}

	/**
	 * Filters the array of query variables parsed by TEC to force the use of the right language.
	 * For example, `example.com/events?lang=de` would return the default language instead of using the provided query
	 * arg because TEC tries to use the WP rewrite rules to match the query path, and `events` => default language.
	 * However, this is not needed for `example.com/de/events-de/` and `example.com/?lang=de` because the right
	 * language will be set in these cases.
	 *
	 * @since 3.1
	 * @see   Tribe__Rewrite->parse_request()
	 * @see   PLL_TEC->add_language_to_publicly_visible_views_query_args()
	 *
	 * @param  string[] $query_vars       The parsed query vars array.
	 * @param  string[] $extra_query_vars An associative array of extra query vars that will be processed before
	 *                                    the WordPress defined ones.
	 * @param  string   $url              The URL to parse.
	 * @return string[]
	 */
	public function force_language_on_tec_parse_query_vars( $query_vars, $extra_query_vars, $url ) {
		// Find the lang in the URL...
		$lang = $this->get_lang_from_url_query_arg( $url );

		if ( ! empty( $lang ) ) {
			// ... and add it to the query vars.
			$query_vars['lang'] = $lang->slug;
		}

		return $query_vars;
	}

	/**
	 * Adds the lang to TEC's query arguments that will be used to build a View URL.
	 * This insures that a lang arg is available when building the view's URL.
	 *
	 * @since 3.1
	 *
	 * @param  mixed[] $query_args An array of query args that will be used to build the URL for the View.
	 * @return mixed[]
	 */
	public function add_missing_lang_to_query_arg( $query_args ) {
		if ( isset( $query_args['lang'] ) ) {
			return $query_args;
		}

		$curlang = $this->get_curlang();

		$query_args['lang'] = ! empty( $curlang ) ? $curlang->slug : $this->polylang->options['default_lang'];

		return $query_args;
	}

	/**
	 * Adds the lang to the URL passed to `Tribe__Rewrite->get_canonical_url()`.
	 * This insures that a lang arg is available when building a URL.
	 *
	 * @since 3.1
	 *
	 * @param  string|null $canonical_url The canonical URL, defaults to `null`; returning a non `null` value will
	 *                                    make the logic bail and return the value.
	 * @param  string      $url           The input URL to resolve to a canonical one.
	 * @return string|null
	 */
	public function add_missing_lang_to_non_canonical_url( $canonical_url, $url ) {
		$lang = $this->get_lang_from_url_query_arg( $url );

		if ( ! empty( $lang ) ) {
			// All good.
			return $canonical_url;
		}

		$curlang = $this->get_curlang();

		if ( empty( $curlang ) ) {
			// We're screwed.
			return $canonical_url;
		}

		// Re-inject the URL with the current lang. `$this->get_lang_from_url_query_arg()` will prevent an infinite loop.
		return Tribe__Rewrite::instance()->get_canonical_url( add_query_arg( 'lang', $curlang->slug, $url ) );
	}

	/**
	 * Removes the `name` arg from the URL passed to `Tribe__Rewrite->get_canonical_url()` when there is already a
	 * `post_type` arg: this seems to mess up the process.
	 *
	 * @since 3.1
	 *
	 * @param  string|null $canonical_url The canonical URL, defaults to `null`; returning a non `null` value will
	 *                                    make the logic bail and return the value.
	 * @param  string      $url           The input URL to resolve to a canonical one.
	 * @return string|null
	 */
	public function remove_name_arg_from_non_canonical_url( $canonical_url, $url ) {
		$url_query = wp_parse_url( $url, PHP_URL_QUERY );

		if ( empty( $url_query ) ) {
			return $canonical_url;
		}

		$parsed_query = array();
		wp_parse_str( $url_query, $parsed_query );

		if ( empty( $parsed_query ) ) {
			return $canonical_url;
		}

		if ( empty( $parsed_query['post_type'] ) || empty( $parsed_query['name'] ) || ! empty( $parsed_query['ical'] ) ) {
			return $canonical_url;
		}

		if ( TEC::POSTTYPE !== $parsed_query['post_type'] ) {
			return $canonical_url;
		}

		if ( ! empty( $parsed_query['lang'] ) && $parsed_query['lang'] === $parsed_query['name'] ) {
			// ¯\(°_o)/¯.
			$remove = true;
		} elseif ( tribe_get_option( 'eventsSlug' ) === $parsed_query['name'] ) {
			$remove = true;
		}

		if ( empty( $remove ) ) {
			return $canonical_url;
		}

		// Re-inject the URL. Tests against `$parsed_query['name']` will prevent an infinite loop.
		return Tribe__Rewrite::instance()->get_canonical_url( remove_query_arg( 'name', $url ) );
	}

	/**
	 * Filters TEC's canonical URL to fix the language slug in it.
	 * Because of TEC's method to build URLs, using the rewrite rules array, the language slug is not replaced and is
	 * outputed like the rewrite rule pattern: `/(en|fr|de)/`. This filter replaces the pattern by the language
	 * contained in the original URL. If not found in the original URL, falls back to the current language or the default
	 * one.
	 *
	 * @since 3.1
	 *
	 * @param  string $resolved The resolved, canonical URL.
	 * @param  string $url      The original URL to resolve.
	 * @return string
	 */
	public function fix_language_in_canonical_url( $resolved, $url ) {
		$options = $this->polylang->options;

		// Remove the default language if it must be hidden in the URLs.
		$languages = $this->polylang->model->get_languages_list(
			array(
				'hide_default' => $options['hide_default'],
				'fields'       => 'slug',
			)
		);

		if ( empty( $languages ) ) {
			return $resolved;
		}

		/**
		 * What we want to modify in the URL.
		 * Ex: `/(en|fr|de)`, `/language/(fr|de)`.
		 */
		$language_path = $options['rewrite'] ? '' : '/language';
		$to_replace    = $language_path . '/(' . implode( '|', $languages ) . ')/';

		if ( strpos( $resolved, $to_replace ) === false ) {
			return $resolved;
		}

		// Find the lang in the URL (or fallback).
		$lang = $this->get_lang_from_url_query_arg( $url );

		if ( empty( $lang ) ) {
			// We need a lang, whichever it is.
			$curlang = $this->get_curlang();
			$lang    = ! empty( $curlang ) ? $curlang->slug : $options['default_lang'];
		} else {
			$lang = $lang->slug;
		}

		// Make the final replacement.
		if ( $options['hide_default'] && $options['default_lang'] === $lang ) {
			// The default language is hidden in the URL.
			$replacement = '/';
		} else {
			$replacement = "{$language_path}/{$lang}/";
		}

		return str_replace( $to_replace, $replacement, $resolved );
	}

	/**
	 * Filters TEC's canonical URL to translate all slugs in it.
	 * This is possible because a `lang` arg is available in the "uggly" URL.
	 *
	 * @since 3.1
	 * @see   Tribe__Events__Rewrite->get_dynamic_matchers()
	 *
	 * @param  string $resolved The resolved, canonical URL.
	 * @param  string $url      The original URL to resolve.
	 * @return string
	 */
	public function translate_canonical_url( $resolved, $url ) {
		if ( empty( $this->slugs_model ) ) {
			return $resolved;
		}

		// Find the lang in the URL (or fallback).
		$lang = $this->get_lang_from_url_query_arg_or_fallback( $url );

		if ( empty( $lang ) ) {
			// What?
			return $resolved;
		}

		// Make sure the language is well formatted.
		$resolved = remove_query_arg( 'lang', $resolved );
		$resolved = $this->polylang->links_model->add_language_to_link( $resolved, $lang );

		foreach ( $this->get_translatable_slugs() as $slug_id => $translations ) {
			$resolved = $this->slugs_model->switch_translated_slug( $resolved, $lang, $slug_id );
		}

		return $resolved;
	}

	/**
	 * Tells if a request is a TEC REST API request.
	 * TEC does a good job for their REST URL by providing a `admin-ajax.php` fallback in case the REST API is not
	 * available. Unfortunately, this choice is late in the process so we have to test the given URL against the 2
	 * possibilities.
	 *
	 * @since 3.1
	 * @see   Tribe\Events\Views\V2\Rest_Endpoint->get_url()
	 *
	 * @param  string $requested_url The requested URL. Falls back to the current URL.
	 * @return bool|null             Whether the request is a TEC REST API request. Null if not ready to answer yet.
	 */
	protected function is_tec_rest_request( $requested_url = null ) {
		if ( ! isset( $this->is_tec_rest_request['views_v2_is_enabled'] ) ) {
			if ( ! function_exists( 'tribe_events_views_v2_is_enabled' ) ) {
				return null;
			}

			$this->is_tec_rest_request['views_v2_is_enabled'] = tribe_events_views_v2_is_enabled();
		}

		if ( ! $this->is_tec_rest_request['views_v2_is_enabled'] ) {
			// If the views V2 are not enabled, no REST requests.
			return false;
		}

		if ( empty( $requested_url ) || ! is_string( $requested_url ) ) {
			// Fall back to the current URL.
			if ( ! isset( $this->is_tec_rest_request['pll_requested_url'] ) ) {
				$this->is_tec_rest_request['pll_requested_url'] = (string) set_url_scheme( pll_get_requested_url() );
			}

			$requested_url = $this->is_tec_rest_request['pll_requested_url'];
		} else {
			$requested_url = (string) set_url_scheme( $requested_url );
		}

		if ( isset( $this->is_tec_rest_request[ 'is:' . $requested_url ] ) ) {
			return $this->is_tec_rest_request[ 'is:' . $requested_url ];
		}

		if ( false === strpos( $requested_url, '/admin-ajax.php' ) ) {
			// Test against the REST URL.
			if ( ! isset( $this->is_tec_rest_request['tec_rest_url_pattern'] ) ) {
				$url = $this->get_tec_rest_url( true );

				if ( empty( $url ) ) {
					// `$wp_rewrite` is probably not set yet.
					return null;
				}

				$this->is_tec_rest_request['tec_rest_url_pattern'] = preg_replace( '@[#?].*$@', '', $url );
				$this->is_tec_rest_request['tec_rest_url_pattern'] = sprintf( '@^%s[/?#]@i', preg_quote( $this->is_tec_rest_request['tec_rest_url_pattern'], '@' ) );
			}

			$this->is_tec_rest_request[ 'is:' . $requested_url ] = (bool) preg_match( $this->is_tec_rest_request['tec_rest_url_pattern'], $requested_url );

			return $this->is_tec_rest_request[ 'is:' . $requested_url ];
		}

		// Test against the admin ajax URL.
		if ( ! isset( $this->is_tec_rest_request['tec_ajax_url_action'] ) ) {
			$this->is_tec_rest_request['tec_ajax_url_action'] = $this->get_tec_rest_url( false );
			$this->is_tec_rest_request['tec_ajax_url_action'] = $this->get_query_arg_from_url( $this->is_tec_rest_request['tec_ajax_url_action'], 'action' );
		}

		if ( empty( $this->is_tec_rest_request['tec_ajax_url_action'] ) ) {
			// Uh?
			$this->is_tec_rest_request[ 'is:' . $requested_url ] = false;
			return false;
		}

		$requested_action = $this->get_query_arg_from_url( $requested_url, 'action' );

		$this->is_tec_rest_request[ 'is:' . $requested_url ] = $requested_action === $this->is_tec_rest_request['tec_ajax_url_action'];

		return $this->is_tec_rest_request[ 'is:' . $requested_url ];
	}

	/**
	 * Returns the current language object.
	 * Can return `null` if not defined yet.
	 *
	 * @since 3.1
	 *
	 * @return PLL_Language|null
	 */
	protected function get_curlang() {
		if ( ! empty( $this->curlang ) ) {
			return $this->curlang;
		}

		if ( ! empty( $_REQUEST['lang'] ) && is_string( $_REQUEST['lang'] ) && Polylang::is_rest_request() ) { // phpcs:ignore WordPress.Security.NonceVerification
			// REST request.
			$curlang = $this->polylang->model->get_language( sanitize_key( $_REQUEST['lang'] ) ); // phpcs:ignore WordPress.Security.NonceVerification

			if ( ! empty( $curlang ) ) {
				$this->curlang           = $curlang;
				$this->polylang->curlang = $this->curlang;
				return $this->curlang;
			}
		}

		if ( ! empty( $this->polylang->curlang ) ) {
			// Global context.
			$this->curlang = &$this->polylang->curlang;
			return $this->curlang;
		}

		if ( empty( $this->polylang->options['force_lang'] ) && $this->polylang instanceof PLL_Frontend && ! did_action( 'pll_language_defined' ) ) {
			// Lang defined by content: too soon.
			return null;
		}

		$curlang = $this->polylang->model->get_default_language();

		if ( empty( $curlang ) ) {
			// We're screwed.
			return null;
		}

		// Default lang.
		$this->curlang = $curlang;
		return $this->curlang;
	}

	/**
	 * Returns the list of slugs that need to be reset in TEC, except the deprecated ones.
	 *
	 * @since 3.1
	 * @see   PLL_TEC->reset_slugs()
	 *
	 * @return string[] Array keys match `Tribe__Events__Main`'s properties name.
	 */
	protected function get_slugs_to_reset() {
		return array(
			'category_slug' => 'category',
			'tag_slug'      => 'tag',
			'monthSlug'     => 'month',
			'listSlug'      => 'list',
			'upcomingSlug'  => 'upcoming',
			'pastSlug'      => 'past',
			'daySlug'       => 'day',
			'todaySlug'     => 'today',
			'featured_slug' => 'featured',
			'all_slug'      => 'all',
		);
	}

	/**
	 * Returns the list of IDs of translatable slugs dedicated to TEC.
	 * Ex: `tribe_venue`, `archive_tribe_events`, `paged`, `tribe_today`.
	 *
	 * @since 3.1
	 *
	 * @return string[]
	 */
	protected function get_translatable_slug_ids() {
		if ( ! empty( $this->translatable_slug_ids ) ) {
			return $this->translatable_slug_ids;
		}

		$slug_ids = array(
			Venue::POSTTYPE,
			Organizer::POSTTYPE,
			TEC::POSTTYPE,
			'archive_' . TEC::POSTTYPE,
			'post_tag',
			'paged',
		);

		foreach ( $this->get_slugs_to_reset() as $slug ) {
			$slug_ids[] = 'tribe_' . $slug;
		}

		$this->translatable_slug_ids = array_combine( $slug_ids, $slug_ids );

		return $this->translatable_slug_ids;
	}

	/**
	 * Returns the list of translatable slugs dedicated to TEC.
	 *
	 * @since 3.1
	 *
	 * @return mixed[]
	 */
	protected function get_translatable_slugs() {
		if ( empty( $this->slugs_model ) ) {
			return array();
		}

		return array_intersect_key( $this->slugs_model->get_translatable_slugs(), $this->get_translatable_slug_ids() );
	}

	/**
	 * Returns the value of the `lang` query arg from the given URL.
	 *
	 * @since 3.1
	 *
	 * @param  string $url       The URL to retrieve the arg from.
	 * @return PLL_Language|null The lang object. Null if not found or invalid.
	 */
	protected function get_lang_from_url_query_arg( $url ) {
		$lang = $this->get_query_arg_from_url( $url, 'lang' );

		if ( empty( $lang ) || ! is_string( $lang ) ) {
			return null;
		}

		$lang = $this->polylang->model->get_language( sanitize_key( $lang ) );

		if ( empty( $lang ) ) {
			return null;
		}

		return $lang;
	}

	/**
	 * Returns the value of the `lang` query arg from the given URL.
	 *
	 * @since 3.1
	 *
	 * @param  string $url       The URL to retrieve the arg from.
	 * @return PLL_Language|null The lang object. Null if not found or invalid.
	 */
	protected function get_lang_from_url_query_arg_or_fallback( $url ) {
		$lang = $this->get_lang_from_url_query_arg( $url );

		if ( ! empty( $lang ) ) {
			return $lang;
		}

		$lang = $this->get_curlang();

		if ( ! empty( $lang ) ) {
			return $lang;
		}

		// Uh?
		$lang = $this->polylang->model->get_default_language();

		if ( ! empty( $lang ) ) {
			return $lang;
		}

		// What?
		return null;
	}

	/**
	 * Returns the value of a query arg from the given URL.
	 *
	 * @since 3.1
	 *
	 * @param  string $url            The URL to retrieve the arg from.
	 * @param  string $query_arg_name The name of the query arg to retrieve.
	 * @return string|null            The raw value of the query arg. Null if not found.
	 */
	protected function get_query_arg_from_url( $url, $query_arg_name ) {
		if ( empty( $url ) || ! is_string( $url ) ) {
			return null;
		}

		if ( empty( $query_arg_name ) || ! is_string( $query_arg_name ) ) {
			return null;
		}

		$url_query = wp_parse_url( $url, PHP_URL_QUERY );

		if ( empty( $url_query ) ) {
			return null;
		}

		$parsed = array();
		wp_parse_str( $url_query, $parsed );

		if ( empty( $parsed[ $query_arg_name ] ) || ! is_string( $parsed[ $query_arg_name ] ) ) {
			return null;
		}

		return sanitize_key( $parsed[ $query_arg_name ] );
	}

	/**
	 * Returns TEC's REST URL.
	 *
	 * @since 3.1
	 *
	 * @param  bool $enable_rest True to get the REST URL. False to get the admin ajax URL.
	 * @return string|null       The REST URL. Null if too soon to be determinated: this may happen when requesting the
	 *                           the real REST URL (`$enable_rest` is true) but `$wp_rewrite` is not ready.
	 */
	protected function get_tec_rest_url( $enable_rest ) {
		global $wp_rewrite;

		if ( $enable_rest ) {
			// In this case, `Rest_Endpoint->get_url()` will use `get_rest_url()`.
			if ( is_multisite() && get_blog_option( 0, 'permalink_structure' ) || get_option( 'permalink_structure' ) ) { // See the same test done in `get_rest_url()`.
				// We test `$wp_rewrite` to prevent `get_rest_url()` to explode.
				if ( ! $wp_rewrite instanceof WP_Rewrite ) {
					return null;
				}
			}
		}

		// Force `Rest_Endpoint->is_available()`'s behavior with this filter callback.
		$priority = 100000;
		$callback = function () use ( $enable_rest ) {
			return (bool) $enable_rest;
		};

		add_filter( 'tribe_events_views_v2_rest_endpoint_available', $callback, $priority );
		$url = ( new Rest_Endpoint() )->get_url();
		remove_filter( 'tribe_events_views_v2_rest_endpoint_available', $callback, $priority );

		return $url;
	}
}
