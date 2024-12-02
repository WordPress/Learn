<?php
/**
 * @package Polylang-Pro
 */

/**
 * Base class for managing shared slugs for taxonomy terms
 *
 * @since 1.9
 */
class PLL_Share_Term_Slug {
	/**
	 * Stores the plugin options.
	 *
	 * @var array
	 */
	public $options;

	/**
	 * @var PLL_Model
	 */
	public $model;

	/**
	 * Instance of a child class of PLL_Links_Model.
	 *
	 * @var PLL_Links_Model
	 */
	public $links_model;

	/**
	 * Stores the term name before creating a slug if needed.
	 *
	 * @var string
	 */
	private $pre_term_name = '';

	/**
	 * Used to trick WordPress by setting
	 * a transitory unique term slug.
	 *
	 * @var string
	 */
	const TERM_SLUG_SEPARATOR = '___';

	/**
	 * Constructor
	 *
	 * @since 1.9
	 *
	 * @param PLL_Base $polylang Polylang object.
	 */
	public function __construct( &$polylang ) {
		$this->options     = &$polylang->options;
		$this->model       = &$polylang->model;
		$this->links_model = &$polylang->links_model;

		add_action( 'created_term', array( $this, 'save_term' ), 1, 3 );
		add_action( 'edited_term', array( $this, 'save_term' ), 1, 3 );
		add_filter( 'pre_term_name', array( $this, 'set_pre_term_name' ) );
		add_filter( 'pre_term_slug', array( $this, 'set_pre_term_slug' ), 10, 2 );

		// Remove Polylang filter to avoid conflicts when filtering slugs.
		remove_filter( 'pre_term_name', array( $polylang->terms, 'set_pre_term_name' ), 10 );
		remove_filter( 'pre_term_slug', array( $polylang->terms, 'set_pre_term_slug' ), 10 );
	}

	/**
	 * Will make slug unique per language and taxonomy
	 * Mostly taken from wp_unique_term_slug
	 *
	 * @since 1.9
	 *
	 * @param string  $slug The string that will be tried for a unique slug.
	 * @param string  $lang Language slug.
	 * @param WP_Term $term The term object that the $slug will belong too.
	 * @return string Will return a true unique slug.
	 */
	protected function unique_term_slug( $slug, $lang, $term ) {
		global $wpdb;

		$original_slug = $slug; // Save this for the filter at the end.

		// Quick check.
		if ( ! $this->model->term_exists_by_slug( $slug, $lang, $term->taxonomy ) ) {
			/** This filter is documented in /wordpress/wp-includes/taxonomy.php */
			return apply_filters( 'wp_unique_term_slug', $slug, $term, $original_slug );
		}

		/*
		 * As done by WP in term_exists except that we use our own term_exist.
		 * If the taxonomy supports hierarchy and the term has a parent,
		 * make the slug unique by incorporating parent slugs.
		 */
		if ( is_taxonomy_hierarchical( $term->taxonomy ) && ! empty( $term->parent ) ) {
			$the_parent = $term->parent;
			while ( $the_parent > 0 ) {
				$parent_term = get_term( $the_parent, $term->taxonomy );
				if ( ! $parent_term instanceof WP_Term ) {
					break;
				}
				$slug .= '-' . $parent_term->slug;
				if ( ! $this->model->term_exists_by_slug( $slug, $lang ) ) { // Calls our own term_exists.
					/** This filter is documented in /wordpress/wp-includes/taxonomy.php */
					return apply_filters( 'wp_unique_term_slug', $slug, $term, $original_slug );
				}

				$the_parent = $parent_term->parent;
			}
		}

		// If we didn't get a unique slug, try appending a number to make it unique.
		if ( ! empty( $term->term_id ) ) {
			$query = $wpdb->prepare( "SELECT slug FROM {$wpdb->terms} WHERE slug = %s AND term_id != %d", $slug, $term->term_id );
		}
		else {
			$query = $wpdb->prepare( "SELECT slug FROM {$wpdb->terms} WHERE slug = %s", $slug );
		}

		// PHPCS:ignore WordPress.DB.PreparedSQL.NotPrepared
		if ( $wpdb->get_var( $query ) ) {
			$num = 2;
			do {
				$alt_slug = $slug . "-$num";
				++$num;
				$slug_check = $wpdb->get_var( $wpdb->prepare( "SELECT slug FROM {$wpdb->terms} WHERE slug = %s", $alt_slug ) );
			} while ( $slug_check );
			$slug = $alt_slug;
		}

		/** This filter is documented in /wordpress/wp-includes/taxonomy.php */
		return apply_filters( 'wp_unique_term_slug', $slug, $term, $original_slug );
	}

	/**
	 * Ugly hack to enable the same slug in several languages
	 *
	 * @since 1.9
	 *
	 * @param int    $term_id  The term id of a saved term.
	 * @param int    $tt_id    The term taxononomy id.
	 * @param string $taxonomy The term taxonomy.
	 * @return void
	 */
	public function save_term( $term_id, $tt_id, $taxonomy ) {
		global $wpdb;

		// Does nothing except on taxonomies which are filterable.
		if ( ! $this->model->is_translated_taxonomy( $taxonomy ) || 0 === $this->options['force_lang'] ) {
			return;
		}

		$term = get_term( $term_id, $taxonomy );

		if ( ! ( $term instanceof WP_Term ) || false === ( $pos = strpos( $term->slug, self::TERM_SLUG_SEPARATOR ) ) ) {
			return;
		}

		$slug = substr( $term->slug, 0, $pos );
		$lang = substr( $term->slug, $pos + 3 );

		// Need to check for unique slug as we tricked wp_unique_term_slug from WP.
		$slug = $this->unique_term_slug( $slug, $lang, (object) $term );
		$wpdb->update( $wpdb->terms, compact( 'slug' ), compact( 'term_id' ) );
		clean_term_cache( $term_id, $taxonomy );
	}

	/**
	 * Stores the term name to use in 'pre_term_slug'.
	 *
	 * @since 3.3
	 *
	 * @param string $name Term name.
	 * @return string      Unmodified term name.
	 */
	public function set_pre_term_name( $name ) {
		return $this->pre_term_name = $name;
	}

	/**
	 * Appends language slug to the term slug if needed.
	 *
	 * @since 3.3
	 *
	 * @param string $slug     Term slug.
	 * @param string $taxonomy Term taxonomy.
	 * @return string Slug with a language suffix if found.
	 */
	public function set_pre_term_slug( $slug, $taxonomy ) {
		if ( ! $this->model->is_translated_taxonomy( $taxonomy ) ) {
			return $slug;
		}

		if ( ! $slug ) {
			$slug = sanitize_title( $this->pre_term_name );
		}

		if ( ! term_exists( $slug, $taxonomy ) ) {
			return $slug;
		}

		/** This filter is documented in polylang/include/crud-terms.php */
		$lang = apply_filters( 'pll_inserted_term_language', null, $taxonomy, $slug );

		if ( ! $lang instanceof PLL_Language ) {
			return $slug;
		}

		$parent = 0;

		if ( is_taxonomy_hierarchical( $taxonomy ) ) {
			/** This filter is documented in polylang/include/crud-terms.php */
			$parent = apply_filters( 'pll_inserted_term_parent', 0, $taxonomy, $slug );

			$slug .= $this->maybe_get_parent_suffix( $parent, $taxonomy, $slug );
		}

		$term_id = (int) $this->model->term_exists_by_slug( $slug, $lang, $taxonomy, $parent );

		/**
		 * If no term exists in the given language with that slug, it can be created.
		 * Or if we are editing the existing term, trick WordPress to allow shared slugs.
		 */
		if ( ! $term_id || ( ! empty( $_POST['tag_ID'] ) && (int) $_POST['tag_ID'] === $term_id ) || ( ! empty( $_POST['tax_ID'] ) && (int) $_POST['tax_ID'] === $term_id ) ) { // phpcs:ignore WordPress.Security.NonceVerification
			$slug .= self::TERM_SLUG_SEPARATOR . $lang->slug;
		}

		return $slug;
	}

	/**
	 * Returns the parent suffix for the slug only if parent slug is the same as the given one.
	 * Recursively appends the parents slugs like WordPress does.
	 *
	 * @since 3.3
	 *
	 * @param int    $parent   Parent term ID.
	 * @param string $taxonomy Parent taxonomy.
	 * @param string $slug     Child term slug.
	 * @return string Parents slugs if they are the same as the child slug, empty string otherwise.
	 */
	private function maybe_get_parent_suffix( $parent, $taxonomy, $slug ) {
		$parent_suffix = '';
		$the_parent    = get_term( $parent, $taxonomy );

		if ( ! $the_parent instanceof WP_Term || $the_parent->slug !== $slug ) {
			return $parent_suffix;
		}

		/**
		 * Mostly copied from {@see wp_unique_term_slug()}.
		 */
		while ( ! empty( $the_parent ) ) {
			$parent_term = get_term( $the_parent, $taxonomy );
			if ( ! $parent_term instanceof WP_Term ) {
				break;
			}
			$parent_suffix .= '-' . $parent_term->slug;
			if ( ! term_exists( $slug . $parent_suffix ) ) {
				break;
			}
			$the_parent = $parent_term->parent;
		}

		return $parent_suffix;
	}
}
