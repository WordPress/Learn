<?php
/**
 * @package Polylang-Pro
 */

/**
 * Base class to manage shared slugs for posts
 *
 * @since 1.9
 */
class PLL_Share_Post_Slug {
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
	 * @var PLL_Links_Model
	 */
	public $links_model;

	/**
	 * The current language.
	 *
	 * @var PLL_Language|null
	 */
	public $curlang;

	/**
	 * Constructor
	 *
	 * @since 1.9
	 *
	 * @param object $polylang Polylang object.
	 */
	public function __construct( &$polylang ) {
		$this->options     = &$polylang->options;
		$this->model       = &$polylang->model;
		$this->links_model = &$polylang->links_model;
		$this->curlang     = &$polylang->curlang;

		// Get page by pagename and lang.
		add_action( 'parse_query', array( $this, 'parse_query' ), 0 ); // Before all other functions hooked to 'parse_query'.

		// Get post by name and lang.
		add_filter( 'posts_join', array( $this, 'posts_join' ), 10, 2 );
		add_filter( 'posts_where', array( $this, 'posts_where' ), 10, 2 );

		add_filter( 'wp_unique_post_slug', array( $this, 'wp_unique_post_slug' ), 10, 6 );
		add_action( 'pll_translate_media', array( $this, 'pll_translate_media' ), 20, 2 ); // After PLL_Admin_Sync to avoid reverse sync.
	}

	/**
	 * Modifies the query object when a page is queried by slug and language
	 * This must be the first function hooked to 'parse_query' to run so that others get the right queried page
	 *
	 * @since 1.9
	 *
	 * @param WP_Query $query Reference to a WP_Query object.
	 * @return void
	 */
	public function parse_query( $query ) {
		if ( $lang = $this->get_language_for_filter( $query ) ) {
			$qv = $query->query_vars;

			// For hierarchical custom post types.
			if ( empty( $qv['pagename'] ) && ! empty( $qv['name'] ) && ! empty( $qv['post_type'] ) && array_intersect( get_post_types( array( 'hierarchical' => true ) ), (array) $qv['post_type'] ) ) {
				$qv['pagename'] = $qv['name'];
			}

			if ( ! empty( $qv['pagename'] ) ) {
				/*
				 * A simpler solution is available at https://github.com/mirsch/polylang-slug/commit/4bf2cb80256fc31347455f6539fac0c20f403c04
				 * But it supposes that pages sharing slug are translations of each other which we don't.
				 */
				/** @var WP_Post|null $queried_object */
				$queried_object = $this->get_page_by_path( $qv['pagename'], $lang->slug, OBJECT, empty( $qv['post_type'] ) ? 'page' : $qv['post_type'] );

				// If we got nothing or an attachment, check if we also have a post with the same slug. See https://core.trac.wordpress.org/ticket/24612
				if ( empty( $qv['post_type'] ) && ( empty( $queried_object ) || 'attachment' === $queried_object->post_type ) && preg_match( '/^[^%]*%(?:postname)%/', get_option( 'permalink_structure' ) ) ) {
					/** @var WP_Post|null $post */
					$post = $this->get_page_by_path( $qv['pagename'], $lang->slug, OBJECT, 'post' );
					if ( $post ) {
						$queried_object = $post;
					}
				}

				if ( ! empty( $queried_object ) ) {
					$query->queried_object    = $queried_object;
					$query->queried_object_id = (int) $queried_object->ID;
				}
			}
		}
	}

	/**
	 * Retrieves a page given its path.
	 * This is the same function as WP get_page_by_path()
	 * Rewritten to make it language dependent
	 *
	 * @since 1.9
	 *
	 * @param string          $page_path Page path.
	 * @param string          $lang      Language slug.
	 * @param string          $output    Optional. Output type. Accepts OBJECT, ARRAY_N, or ARRAY_A. Default OBJECT.
	 * @param string|string[] $post_type Optional. Post type or array of post types. Default 'page'.
	 * @return WP_Post|mixed[]|null WP_Post on success or null on failure.
	 *
	 * @phpstan-param non-empty-string $lang
	 * @phpstan-param 'ARRAY_A'|'ARRAY_N'|'OBJECT' $output
	 * @phpstan-return array<int|string, mixed>|WP_Post|null
	 */
	protected function get_page_by_path( $page_path, $lang, $output = OBJECT, $post_type = 'page' ) {
		global $wpdb;

		$page_path = rawurlencode( urldecode( $page_path ) );
		$page_path = str_replace( '%2F', '/', $page_path );
		$page_path = str_replace( '%20', ' ', $page_path );
		$parts = explode( '/', trim( $page_path, '/' ) );
		$parts = array_map( 'sanitize_title_for_query', $parts );
		$escaped_parts = esc_sql( $parts );

		$in_string = "'" . implode( "','", (array) $escaped_parts ) . "'";

		if ( is_array( $post_type ) ) {
			$post_types = $post_type;
		} else {
			$post_types = array( $post_type, 'attachment' );
		}

		$post_types = esc_sql( $post_types );
		$post_type_in_string = "'" . implode( "','", $post_types ) . "'";
		$sql  = "SELECT ID, post_name, post_parent, post_type FROM {$wpdb->posts}";
		$sql .= $this->model->post->join_clause();
		$sql .= " WHERE post_name IN ( {$in_string} ) AND post_type IN ( {$post_type_in_string} )";
		$sql .= $this->model->post->where_clause( $lang );

		// PHPCS:ignore WordPress.DB.PreparedSQL.NotPrepared
		$pages = $wpdb->get_results( $sql, OBJECT_K );

		$revparts = array_reverse( $parts );

		$foundid = 0;
		foreach ( (array) $pages as $page ) {
			if ( $page->post_name === $revparts[0] ) {
				$count = 0;
				$p = $page;
				while ( 0 !== (int) $p->post_parent && isset( $pages[ $p->post_parent ] ) ) {
					++$count;
					$parent = $pages[ $p->post_parent ];
					if ( ! isset( $revparts[ $count ] ) || $parent->post_name !== $revparts[ $count ] ) {
						break;
					}
					$p = $parent;
				}

				if ( 0 === (int) $p->post_parent && count( $revparts ) === $count + 1 && $p->post_name === $revparts[ $count ] ) {
					$foundid = $page->ID;
					if ( $page->post_type === $post_type ) {
						break;
					}
				}
			}
		}

		if ( $foundid ) {
			return get_post( $foundid, $output );
		}

		return null;
	}

	/**
	 * Adds our join clause to sql query.
	 * Useful when querying a post by name.
	 *
	 * @since 1.9
	 *
	 * @param string   $join  Original join clause.
	 * @param WP_Query $query The WP_Query object.
	 * @return string Modified join clause.
	 */
	public function posts_join( $join, $query ) {
		if ( $this->get_language_for_filter( $query ) ) {
			return $join . $this->model->post->join_clause();
		}
		return $join;
	}

	/**
	 * Adds our where clause to sql query.
	 * Useful when querying a post by name.
	 *
	 * @since 1.9
	 *
	 * @param string   $where Original where clause.
	 * @param WP_Query $query The WP_Query object.
	 * @return string Modified where clause.
	 */
	public function posts_where( $where, $query ) {
		if ( $language = $this->get_language_for_filter( $query ) ) {
			return $where . $this->model->post->where_clause( $language );
		}
		return $where;
	}

	/**
	 * Checks if the query must be filtered or not
	 *
	 * @since 1.9
	 *
	 * @param WP_Query $query The WP_Query object.
	 * @return PLL_Language|false The language to use for the filter, false if the query should be kept unfiltered.
	 */
	protected function get_language_for_filter( $query ) {
		$qv = $query->query_vars;

		if ( empty( $qv['name'] ) && empty( $qv['pagename'] ) ) {
			return false;
		}

		$post_type = empty( $qv['post_type'] ) ? 'post' : $qv['post_type'];

		if ( ! empty( $qv['attachment'] ) ) {
			$post_type = 'attachment';
		}

		if ( ! $this->model->is_translated_post_type( $post_type ) ) {
			return false;
		}

		if ( ! empty( $qv['lang'] ) ) {
			return $this->model->get_language( $qv['lang'] );
		}

		if ( isset( $qv['tax_query'] ) && is_array( $qv['tax_query'] ) ) {
			foreach ( $qv['tax_query'] as $tax_query ) {
				if ( isset( $tax_query['taxonomy'] ) && 'language' === $tax_query['taxonomy'] ) {
					if ( is_array( $tax_query['terms'] ) ) {
						if ( 1 < count( $tax_query['terms'] ) ) {
							// Several language terms queried.
							continue;
						}

						$term = reset( $tax_query['terms'] );
					} else {
						$term = $tax_query['terms'];
					}

					if ( isset( $tax_query['field'] ) && 'term_taxonomy_id' === $tax_query['field'] ) {
						$term = "tt:{$term}";
					}

					$lang = $this->model->get_language( $term );

					if ( $lang ) {
						return $lang;
					}
				}
			}
		}

		if ( ! empty( $this->curlang ) ) {
			return $this->curlang;
		}

		return false;
	}

	/**
	 * Checks if the slug is unique within language.
	 * Thanks to @AndyDeGroo for https://wordpress.org/support/topic/plugin-polylang-identical-page-names-in-different-languages?replies=8#post-2669927
	 * Thanks to Ulrich Pogson for https://github.com/grappler/polylang-slug/blob/master/polylang-slug.php
	 *
	 * @since 1.9
	 *
	 * @param string $slug          The slug defined by wp_unique_post_slug() in WP.
	 * @param int    $post_ID       The post id.
	 * @param string $post_status   Not used.
	 * @param string $post_type     The Post type.
	 * @param int    $post_parent   The id of the post parent.
	 * @param string $original_slug The original slug before it is modified by wp_unique_post_slug in WP.
	 * @return string Original slug if it is unique in the language or the modified slug otherwise.
	 */
	public function wp_unique_post_slug( $slug, $post_ID, $post_status, $post_type, $post_parent, $original_slug ) {
		global $wpdb;

		// Return slug if it was not changed.
		if ( $original_slug === $slug || 0 === $this->options['force_lang'] || ! $this->model->is_translated_post_type( $post_type ) ) {
			return $slug;
		}

		$lang = $this->model->post->get_language( $post_ID );

		if ( empty( $lang ) ) {
			return $slug;
		}

		if ( 'attachment' === $post_type ) {
			// Attachment slugs must be unique across all types.
			$sql  = "SELECT post_name FROM {$wpdb->posts}";
			$sql .= $this->model->post->join_clause();
			$sql .= $wpdb->prepare( ' WHERE post_name = %s AND ID != %d', $original_slug, $post_ID );
			$sql .= $this->model->post->where_clause( $lang ) . ' LIMIT 1';

			// PHPCS:ignore WordPress.DB.PreparedSQL.NotPrepared
			$post_name_check = $wpdb->get_var( $sql );
		}

		elseif ( is_post_type_hierarchical( $post_type ) ) {
			// Page slugs must be unique within their own trees. Pages are in a separate namespace than posts so page slugs are allowed to overlap post slugs.
			$sql  = "SELECT ID FROM {$wpdb->posts}";
			$sql .= $this->model->post->join_clause();
			$sql .= $wpdb->prepare( " WHERE post_name = %s AND post_type IN ( %s, 'attachment' ) AND ID != %d AND post_parent = %d", $original_slug, $post_type, $post_ID, $post_parent );
			$sql .= $this->model->post->where_clause( $lang ) . ' LIMIT 1';

			// PHPCS:ignore WordPress.DB.PreparedSQL.NotPrepared
			$post_name_check = $wpdb->get_var( $sql );
		}

		else {
			// Post slugs must be unique across all posts.
			$sql  = "SELECT post_name FROM {$wpdb->posts}";
			$sql .= $this->model->post->join_clause();
			$sql .= $wpdb->prepare( ' WHERE post_name = %s AND post_type = %s AND ID != %d', $original_slug, $post_type, $post_ID );
			$sql .= $this->model->post->where_clause( $lang ) . ' LIMIT 1';

			// PHPCS:ignore WordPress.DB.PreparedSQL.NotPrepared
			$post_name_check = $wpdb->get_var( $sql );
		}

		return $post_name_check ? $slug : $original_slug;
	}

	/**
	 * Updates the attachment slug when creating a translation to allow to share slugs
	 * This second step is needed because wp_unique_post_slug is called before the language is set
	 *
	 * @since 1.9
	 *
	 * @param int $post_id Original attachment id.
	 * @param int $tr_id   Translated attachment id.
	 * @return void
	 */
	public function pll_translate_media( $post_id, $tr_id ) {
		$post = get_post( $post_id );
		if ( $post ) {
			wp_update_post( array( 'ID' => $tr_id, 'post_name' => $post->post_name ) );
		}
	}
}
