<?php
/**
 * @package Polylang-Pro
 */

/**
 * Links Model for translating slugs
 *
 * @since 1.9
 */
class PLL_Translate_Slugs_Model {
	/**
	 * @var PLL_Model
	 */
	public $model;

	/**
	 * Instance of a child class of PLL_Links_Model.
	 *
	 * @var PLL_Links_Permalinks
	 */
	public $links_model;

	/**
	 * Stores the informations on translatable slugs.
	 *
	 * @var array
	 */
	public $translated_slugs;

	/**
	 * Constructor
	 *
	 * @since 1.9
	 *
	 * @param object $polylang Polylang object.
	 */
	public function __construct( &$polylang ) {
		$this->model       = &$polylang->model;
		$this->links_model = &$polylang->links_model;

		add_action( 'switch_blog', array( $this, 'switch_blog' ), 20, 2 ); // After `PLL_Base::switch_blog()`.

		add_action( 'wp_loaded', array( $this, 'init_translated_slugs' ), 1 );

		// Make sure to prepare rewrite rules when flushing.
		add_action( 'pll_prepare_rewrite_rules', array( $this, 'prepare_rewrite_rules' ), 20 ); // After Polylang.

		// Flush rewrite rules when saving string translations.
		add_action( 'pll_save_strings_translations', array( $this, 'flush_rewrite_rules' ) );

		// Register strings for translated slugs.
		add_action( 'admin_init', array( $this, 'register_slugs' ) );
		add_filter( 'pll_sanitize_string_translation', array( $this, 'sanitize_string_translation' ), 10, 2 );

		// Reset cache when adding or modifying languages.
		add_action( 'pll_add_language', array( $this, 'clean_cache' ) );
		add_action( 'pll_update_language', array( $this, 'clean_cache' ) );

		// Make sure we have all (possibly new) translatable slugs in the strings list table.
		if ( $polylang instanceof PLL_Settings && isset( $_GET['page'] ) && 'mlang_strings' === $_GET['page'] ) { // phpcs:ignore WordPress.Security.NonceVerification
			delete_transient( 'pll_translated_slugs' );
		}
	}

	/**
	 * Updates the list of slugs to translate when switching blog
	 *
	 * @since 1.9
	 *
	 * @param int $new_blog The blog id we switch to.
	 * @param int $old_blog The blog id we switch from.
	 * @return void
	 */
	public function switch_blog( $new_blog, $old_blog ) {
		if ( (int) $new_blog === (int) $old_blog ) {
			// Do nothing if same blog.
			return;
		}

		$this->remove_filters();

		if ( pll_is_plugin_active( POLYLANG_BASENAME ) && get_option( 'polylang' ) ) {
			if ( did_action( 'pll_prepare_rewrite_rules' ) ) {
				$this->prepare_rewrite_rules();
			} else {
				add_action( 'pll_prepare_rewrite_rules', array( $this, 'prepare_rewrite_rules' ), 20 ); // After Polylang.
			}
		}
	}

	/**
	 * Initializes the list of translated slugs
	 * Need to wait for all post types and taxonomies to be registered
	 *
	 * @since 1.9
	 *
	 * @return void
	 */
	public function init_translated_slugs() {
		$this->translated_slugs = $this->get_translatable_slugs();

		// Keep only the slugs which are translated to avoid unnecessary rewrite rules.
		foreach ( $this->translated_slugs as $key => $value ) {
			if ( 1 === count( array_unique( $value['translations'] ) ) && reset( $value['translations'] ) === $value['slug'] ) {
				unset( $this->translated_slugs[ $key ] );
			}
		}
	}

	/**
	 * Translates a slug in a permalink ( from original slug ).
	 *
	 * @since 1.9
	 *
	 * @param string       $link The url to modify.
	 * @param PLL_Language $lang The language.
	 * @param string       $type The type of slug to translate.
	 * @return string Modified url.
	 */
	public function translate_slug( $link, $lang, $type ) {
		if ( $lang instanceof PLL_Language && isset( $this->translated_slugs[ $type ] ) && ! empty( $this->translated_slugs[ $type ]['slug'] ) ) {
			$link = preg_replace(
				'#/' . $this->translated_slugs[ $type ]['slug'] . '(/|\?|\#|$)#',
				'/' . $this->get_translated_slug( $type, $lang->slug ) . '$1',
				$link
			);
		}
		return $link;
	}

	/**
	 * Translates a slug in a permalink ( from an already translated slug ).
	 *
	 * @since 1.9
	 *
	 * @param string       $link The url to modify.
	 * @param PLL_Language $lang The language.
	 * @param string       $type The type of slug to translate.
	 * @return string Modified url.
	 */
	public function switch_translated_slug( $link, $lang, $type ) {
		if ( isset( $this->translated_slugs[ $type ] ) && ! empty( $this->translated_slugs[ $type ]['slug'] ) ) {
			$slugs   = $this->translated_slugs[ $type ]['translations'];
			$slugs[] = $this->translated_slugs[ $type ]['slug'];
			$slugs   = $this->encode_deep( $slugs );

			$link = preg_replace(
				'#/(' . implode( '|', array_unique( $slugs ) ) . ')(/|\?|\#|$)#',
				'/' . $this->encode_deep( $this->translated_slugs[ $type ]['translations'][ $lang->slug ] ) . '$2',
				$link
			);
		}
		return $link;
	}

	/**
	 * Returns informations on translatable slugs
	 * and stores them in a transient
	 *
	 * @since 1.9
	 *
	 * @return array
	 */
	public function get_translatable_slugs() {
		global $wp_rewrite;

		$slugs = get_transient( 'pll_translated_slugs' );

		if ( false === $slugs ) {
			$slugs = array();

			foreach ( $this->model->get_languages_list() as $language ) {
				$mo = new PLL_MO();
				$mo->import_from_db( $language );

				// Post types.
				foreach ( get_post_types( array(), 'objects' ) as $type ) {
					if ( ! empty( $type->rewrite['slug'] ) && $this->model->is_translated_post_type( $type->name ) ) {
						$slug = preg_replace( '#%.+?%#', '', $type->rewrite['slug'] ); // For those adding a taxonomy base in custom post type link. See http://wordpress.stackexchange.com/questions/94817/add-category-base-to-url-in-custom-post-type-taxonomy.
						$slug = trim( $slug, '/' ); // It seems that some plugins add / (ex: WooCommerce).
						$slugs[ $type->name ]['slug'] = $slug;
						$tr_slug = $mo->translate( $slug );
						$slugs[ $type->name ]['translations'][ $language->slug ] = empty( $tr_slug ) ? $slug : $tr_slug;

						// Post types archives.
						if ( ! empty( $type->has_archive ) ) {
							if ( true === $type->has_archive ) {
								$slugs[ 'archive_' . $type->name ]['hide'] = true;
							} else {
								$slug = $type->has_archive;
							}

							$slugs[ 'archive_' . $type->name ]['slug'] = $slug;
							$tr_slug = $mo->translate( $slug );
							$slugs[ 'archive_' . $type->name ]['translations'][ $language->slug ] = empty( $tr_slug ) ? $slug : $tr_slug;
						}
					}
				}

				// Taxonomies.
				foreach ( get_taxonomies( array(), 'objects' ) as $tax ) {
					if ( ! empty( $tax->rewrite['slug'] ) && ( $this->model->is_translated_taxonomy( $tax->name ) || 'post_format' === $tax->name ) ) {
						$slug = trim( $tax->rewrite['slug'], '/' ); // It seems that some plugins add / (ex: WooCommerce for product attributes).
						$slugs[ $tax->name ]['slug'] = $slug;
						$tr_slug = $mo->translate( $slug );
						$slugs[ $tax->name ]['translations'][ $language->slug ] = empty( $tr_slug ) ? $slug : $tr_slug;
					}
				}

				// Post formats.
				// get_theme_support sends an array of arrays.
				$formats = get_theme_support( 'post-formats' );

				if ( is_array( $formats ) && isset( $formats[0] ) && is_array( $formats[0] ) ) {
					foreach ( $formats[0] as $format ) {
						$slugs[ 'post-format-' . $format ]['slug'] = $format;
						$tr_format = $mo->translate( $format );
						$slugs[ 'post-format-' . $format ]['translations'][ $language->slug ] = empty( $tr_format ) ? $format : $tr_format;
					}
				}

				// Misc.
				foreach ( array( 'author', 'search', 'attachment' ) as $slug ) {
					$slugs[ $slug ]['slug'] = $slug;
					$tr_slug = $mo->translate( $slug );
					$slugs[ $slug ]['translations'][ $language->slug ] = empty( $tr_slug ) ? $slug : $tr_slug;
				}

				// Paged pages.
				$slugs['paged']['slug'] = 'page';
				$tr_slug = $mo->translate( 'page' );
				$slugs['paged']['translations'][ $language->slug ] = empty( $tr_slug ) ? 'page' : $tr_slug;

				// /blog/.
				if ( ! empty( $wp_rewrite->front ) ) {
					$slug = trim( $wp_rewrite->front, '/' );
					$slugs['front']['slug'] = $slug;
					$tr_slug = $mo->translate( $slug );
					$slugs['front']['translations'][ $language->slug ] = empty( $tr_slug ) ? $slug : $tr_slug;
				}

				/**
				 * Filter the list of translated slugs
				 *
				 * @since 1.9
				 *
				 * @param array  $slugs    The list of slugs.
				 * @param object $language The language object.
				 * @param object $mo       The translations object.
				 */
				$slugs = apply_filters_ref_array( 'pll_translated_slugs', array( $slugs, $language, &$mo ) );
			}

			// Make sure to store the transient only after 'wp_loaded' has been fired to avoid a conflict with Page Builder 2.4.10+.
			if ( did_action( 'wp_loaded' ) ) {
				set_transient( 'pll_translated_slugs', $slugs );
			}
		}

		return $slugs;
	}

	/**
	 * Prepares rewrite rules filters to translate slugs
	 *
	 * @since 1.9
	 * @since 3.5 Hooked to `pll_prepare_rewrite_rules` and remove $pre parameter.
	 *
	 * @return void
	 */
	public function prepare_rewrite_rules() {
		if ( ! $this->model->has_languages() || has_filter( 'rewrite_rules_array', array( $this, 'rewrite_translated_slug' ) ) ) {
			return;
		}

		foreach ( $this->get_rewrite_rules_filter_with_callbacks() as $rule => $callback ) {
			add_filter( $rule, $callback, 5 );
		}
	}

	/**
	 * Removes hooks to filter rewrite rules, see `self::prepare_rewrite_rules()` for added ones.
	 *
	 * @since 3.5
	 *
	 * @return void
	 */
	protected function remove_filters() {
		foreach ( $this->get_rewrite_rules_filter_with_callbacks() as $rule => $callback ) {
			remove_filter( $rule, $callback, 5 );
		}
	}

	/**
	 * Returns *all* rewrite rules filters with their associated callbacks.
	 *
	 * @since 3.5
	 *
	 * @return callable[] Array of hook names as keys and callbacks as values.
	 */
	protected function get_rewrite_rules_filter_with_callbacks() {
		if ( ! method_exists( $this->links_model, 'get_rewrite_rules_filters' ) ) {
			// Current links model instance doesn't support rewrite rules (i.e. nothing to filter here).
			return array();
		}

		$filters = array(
			'rewrite_rules_array' => array( $this, 'rewrite_translated_slug' ),
		);

		foreach ( $this->links_model->get_rewrite_rules_filters() as $type ) {
			$filters[ $type . '_rewrite_rules' ] = array( $this, 'rewrite_translated_slug' );
		}

		return $filters;
	}

	/**
	 * Flush rewrite rules when saving strings translations
	 *
	 * @since 1.9
	 *
	 * @return void
	 */
	public function flush_rewrite_rules() {
		delete_transient( 'pll_translated_slugs' );
		$this->init_translated_slugs();
		flush_rewrite_rules();
	}

	/**
	 * Returns the rewrite rule pattern for the new slug
	 *
	 * @since 1.9
	 * @since 2.2 Add the $capture parameter
	 *
	 * @param string $type    The type of slug.
	 * @param bool   $capture Whether the slugs must be captured, defaults to false.
	 * @return string The pattern.
	 */
	protected function get_translated_slugs_pattern( $type, $capture = false ) {
		$slugs = array( $this->translated_slugs[ $type ]['slug'] );

		foreach ( array_keys( $this->translated_slugs[ $type ]['translations'] ) as $lang ) {
			$slugs[] = preg_quote( $this->translated_slugs[ $type ]['translations'][ $lang ], '#' );
		}

		return ( $capture ? '(' : '(?:' ) . implode( '|', array_unique( $slugs ) ) . ')/';
	}

	/**
	 * Translates a slug in rewrite rules
	 *
	 * @since 1.9
	 *
	 * @param string[] $rules Rewrite rules.
	 * @param string   $type  The type of slug to translate.
	 * @return string[] Modified rewrite rules.
	 */
	protected function translate_rule( $rules, $type ) {
		if ( empty( $this->translated_slugs[ $type ] ) ) {
			return $rules;
		}

		$old = $this->translated_slugs[ $type ]['slug'] . '/';
		$new = $this->get_translated_slugs_pattern( $type );

		$newrules = array();

		foreach ( $rules as $key => $rule ) {
			if ( false !== $found = strpos( $key, $old ) ) {
				$new_key = 0 === $found ? str_replace( $old, $new, $key ) : str_replace( '/' . $old, '/' . $new, $key );
				$newrules[ $new_key ] = $rule;
			} else {
				$newrules[ $key ] = $rule;
			}
		}
		return $newrules;
	}

	/**
	 * Translates the post format slug in rewrite rules
	 *
	 * @since 1.9
	 *
	 * @param string[] $rules Rewrite rules.
	 * @return string[] Modified rewrite rules.
	 */
	protected function translate_post_format_rule( $rules ) {
		$formats = get_theme_support( 'post-formats' );

		if ( ! is_array( $formats ) || ! isset( $formats[0] ) || ! is_array( $formats[0] ) ) {
			return $rules;
		}

		$newrules = array();

		foreach ( $formats[0] as $format ) {
			if ( ! isset( $this->translated_slugs[ 'post-format-' . $format ] ) ) {
				continue;
			}

			$new_slug = '/' . $this->get_translated_slugs_pattern( 'post-format-' . $format, true );

			foreach ( $rules as $key => $rule ) {
				$newrules[ str_replace( '/([^/]+)/', $new_slug, $key ) ] = str_replace( '$matches[1]', $format, $rule );
			}
		}

		return $newrules + $rules;
	}

	/**
	 * Translates the post type archive in rewrite rules
	 *
	 * @since 1.9
	 *
	 * @param string[] $rules Rewrite rules.
	 * @return string[] Modified rewrite rules.
	 */
	protected function translate_post_type_archive_rule( $rules ) {
		$newrules = array();
		$cpts     = array_intersect( $this->model->get_translated_post_types(), get_post_types( array( '_builtin' => false ) ) );

		foreach ( $rules as $key => $rule ) {
			$query = wp_parse_url( $rule, PHP_URL_QUERY );

			if ( ! is_string( $query ) ) {
				continue;
			}

			parse_str( $query, $qv );

			if ( ! empty( $cpts ) && ! empty( $qv['post_type'] ) && is_string( $qv['post_type'] ) && in_array( $qv['post_type'], $cpts ) && ! strpos( $rule, 'name=' ) && isset( $this->translated_slugs[ 'archive_' . $qv['post_type'] ] ) ) {
				$new_slug = $this->get_translated_slugs_pattern( 'archive_' . $qv['post_type'] );
				$newrules[ str_replace( $this->translated_slugs[ 'archive_' . $qv['post_type'] ]['slug'] . '/', $new_slug, $key ) ] = $rule;
			} else {
				$newrules[ $key ] = $rule;
			}
		}
		return $newrules;
	}

	/**
	 * Translates the page slug in rewrite rules
	 *
	 * @since 1.9
	 *
	 * @param string[] $rules Rewrite rules.
	 * @return string[] Modified rewrite rules.
	 */
	protected function translate_paged_rule( $rules ) {
		if ( empty( $this->translated_slugs['paged'] ) ) {
			return $rules;
		}

		$newrules = array();

		$old = $this->translated_slugs['paged']['slug'] . '/';
		$new = $this->get_translated_slugs_pattern( 'paged' );

		foreach ( $rules as $key => $rule ) {
			if ( strpos( $key, '/page/' ) && preg_match( '#\[\d\]|\$\d#', $rule ) ) {
				$newrules[ str_replace( '/' . $old, '/' . $new, $key ) ] = $rule;
			} elseif ( 0 === strpos( $key, 'page/' ) && preg_match( '#\[\d\]|\$\d#', $rule ) ) {
				// Special case for root
				$newrules[ str_replace( $old, $new, $key ) ] = $rule;
			} else {
				$newrules[ $key ] = $rule;
			}
		}

		return $newrules;
	}

	/**
	 * Modifies rewrite rules to translate post types and taxonomies slugs
	 *
	 * @since 1.9
	 *
	 * @param string[] $rules Rewrite rules.
	 * @return string[] Modified rewrite rules.
	 */
	public function rewrite_translated_slug( $rules ) {
		$filter = str_replace( '_rewrite_rules', '', current_filter() );

		$rules = $this->translate_paged_rule( $rules ); // Important that it is the first.

		if ( 'rewrite_rules_array' === $filter ) {
			$rules = $this->translate_post_type_archive_rule( $rules );
		} else {
			if ( 'post_format' === $filter ) {
				$rules = $this->translate_post_format_rule( $rules );
			}

			$rules = $this->translate_rule( $rules, $filter );
		}

		$rules = $this->translate_rule( $rules, 'attachment' );
		$rules = $this->translate_rule( $rules, 'front' );

		return $rules;
	}

	/**
	 * Register strings for translated slugs
	 *
	 * @since 1.9
	 *
	 * @return void
	 */
	public function register_slugs() {
		foreach ( $this->get_translatable_slugs() as $key => $type ) {
			if ( empty( $type['hide'] ) ) {
				pll_register_string( 'slug_' . $key, $type['slug'], __( 'URL slugs', 'polylang-pro' ) );
			}
		}
	}

	/**
	 * Performs the sanitization ( before saving in DB ) of slugs translations
	 *
	 * @since 1.9
	 *
	 * @param string $translation Translation to sanitize.
	 * @param string $name        Unique name for the string, not used.
	 * @return string
	 */
	public function sanitize_string_translation( $translation, $name ) {
		if ( 0 !== strpos( $name, 'slug_' ) ) {
			return $translation;
		}
		// Remove some reserved characters that would result in 404.
		$special_chars = array( '?', '#', '[', ']', '$', '\'', '(', ')', '*', '+', ' ' );
		$translation = str_replace( $special_chars, '', $translation );

		// Inspired by category base sanitization.
		$translation = preg_replace( '#/+#', '/', '/' . str_replace( '#', '', $translation ) );
		if ( empty( $translation ) ) {
			return '';
		}
		$translation = esc_url_raw( $translation );
		$translation = str_replace( 'http://', '', $translation );
		$translation = trim( $translation, '/' );

		return $translation;
	}

	/**
	 * Deletes the transient when adding or modifying a language
	 *
	 * @since 1.9
	 *
	 * @return void
	 */
	public function clean_cache() {
		delete_transient( 'pll_translated_slugs' );
	}

	/**
	 * Get the translated slug.
	 *
	 * Encoding the paged slug is necessary to prevent the WordPress redirect_canonical
	 * function to wrongly redirect to a 404 page
	 *
	 * @since 2.2
	 *
	 * @param string $type The type of slug to translate.
	 * @param string $lang The language slug.
	 * @return string
	 */
	public function get_translated_slug( $type, $lang ) {
		$translation = $this->translated_slugs[ $type ]['translations'][ $lang ];
		$translation = $this->encode_deep( $translation );
		return $translation;
	}

	/**
	 * Recursively rawurlencode an array of slugs while preserving forward slashes
	 *
	 * @since 2.6
	 *
	 * @param mixed $slug The slug or the array of slug to encode.
	 * @return mixed
	 */
	public function encode_deep( $slug ) {
		if ( is_array( $slug ) ) {
			return array_map( array( $this, 'encode_deep' ), $slug );
		}
		return implode( '/', array_map( 'rawurlencode', explode( '/', $slug ) ) );
	}
}
