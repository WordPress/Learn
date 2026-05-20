<?php
/**
 * @package Polylang-Pro
 */

namespace WP_Syntex\Polylang_Pro\Integrations\ACF;

use WP_Post;
use WP_Term;
use Translations;
use PLL_Language;
use PLL_Export_Data;
use WP_Syntex\Polylang_Pro\Integrations\ACF\Entity\Post;
use WP_Syntex\Polylang_Pro\Integrations\ACF\Entity\Term;
use WP_Syntex\Polylang_Pro\Integrations\ACF\Entity\Media;
use WP_Syntex\Polylang_Pro\Integrations\ACF\Entity\Blocks;
use WP_Syntex\Polylang_Pro\Integrations\ACF\Strategy\Copy;
use WP_Syntex\Polylang_Pro\Integrations\ACF\Location_Language;
use WP_Syntex\Polylang_Pro\Integrations\ACF\Strategy\Synchronize;
use WP_Syntex\Polylang_Pro\Integrations\ACF\Strategy\Collect_Post_Ids;
use WP_Syntex\Polylang_Pro\Integrations\ACF\Strategy\Collect_Term_Ids;
use WP_Syntex\Polylang_Pro\Integrations\ACF\Entity\Abstract_Object;

/**
 * This class is part of the ACF compatibility.
 * Holds all hooks registrations regarding field translations logic.
 * Controls and applies strategies to ACF fields regarding
 * object types and context (e.g. export, import, update, new translation creation, blocks...).
 *
 * @since 3.7
 */
class Dispatcher {
	/**
	 * Setup filters.
	 *
	 * @since 3.7
	 *
	 * @return void
	 */
	public static function on_acf_init() {
		/*
		 * Removes ACF fields from synchronized metas by Polylang, this way ACF integration manage itself the synchronization.
		 * Applied after `PLL_Sync_Post_Model::copy_post_metas/copy_taxonomies` for it to be effective.
		 */
		add_filter( 'pll_copy_post_metas', array( Post::class, 'remove_acf_metas_from_pll_sync' ), 10, 4 );
		add_filter( 'pll_copy_term_metas', array( Term::class, 'remove_acf_metas_from_pll_sync' ), 10, 4 );

		add_action( 'pll_post_synchronized', array( static::class, 'on_post_synchronized' ), 10, 4 );

		add_action( 'pll_duplicate_term', array( static::class, 'on_duplicate_term' ), 10, 3 );

		add_filter( 'acf/update_value', array( static::class, 'update' ), 5, 3 );
		add_filter( 'acf/pre_render_field', array( static::class, 'translate_rendered_field' ), 10, 2 );

		add_action( 'pll_after_post_translation', array( static::class, 'translate' ), 10, 4 );
		add_action( 'pll_after_term_translation', array( static::class, 'translate' ), 10, 4 );
		add_action( 'pll_after_post_export', array( static::class, 'export' ), 10, 3 );
		add_action( 'pll_after_term_export', array( static::class, 'export' ), 10, 3 );

		add_filter( 'pll_collect_post_ids', array( static::class, 'collect_post_ids' ), 10, 2 );
		add_filter( 'pll_collect_term_ids', array( static::class, 'collect_term_ids' ), 10, 2 );

		if ( PLL()->options['media_support'] ) {
			add_action( 'pll_translate_media', array( static::class, 'copy_media_fields' ), 10, 3 );
		}

		add_action( 'admin_enqueue_scripts', array( Post::class, 'admin_enqueue_scripts' ) );
		add_action( 'admin_enqueue_scripts', array( Term::class, 'admin_enqueue_scripts' ) );
		add_action( 'wp_ajax_acf_post_lang_choice', array( Post::class, 'on_ajax_post_lang_choice' ) );
		add_action( 'wp_ajax_acf_term_lang_choice', array( Term::class, 'on_ajax_term_lang_choice' ) );
		add_filter( 'acf/fields/relationship/query', array( static::class, 'add_language_to_query' ), 10, 3 );
		add_filter( 'acf/fields/post_object/query', array( static::class, 'add_language_to_query' ), 10, 3 );
	}

	/**
	 * Initializes ACF blocks integration when blocks are registered.
	 *
	 * @since 3.7
	 *
	 * @return void
	 */
	public static function on_blocks_registered(): void {
		if ( ! function_exists( 'acf_get_block_types' ) || empty( acf_get_block_types() ) ) {
			return;
		}

		add_filter( 'pll_collect_post_ids', array( static::class, 'collect_post_ids_in_blocks' ), 10, 2 );
		add_filter( 'pll_collect_term_ids', array( static::class, 'collect_term_ids_in_blocks' ), 10, 2 );
		add_filter( 'pll_translate_blocks_with_context', array( static::class, 'copy_blocks' ), 10, 3 );
		add_filter( 'pll_filter_translated_post', array( static::class, 'translate_blocks' ), 10, 4 );
		add_action( 'pll_after_post_export', array( static::class, 'export_blocks' ), 10, 2 );
	}

	/**
	 * Filters the field about to be rendered.
	 *
	 * @since 3.7
	 *
	 * @param array      $field  Custom field definition.
	 * @param int|string $acf_id ACF post ID.
	 * @return mixed Modified custom field.
	 */
	public static function translate_rendered_field( $field, $acf_id ) {
		$object = static::get_by_acf_id( $acf_id );
		return empty( $object ) ? $field : $object->translate_rendered_field( $field );
	}

	/**
	 * Filters the custom field value when updated.
	 *
	 * @since 3.7
	 *
	 * @param mixed      $value  Custom field value.
	 * @param int|string $acf_id ACF post ID.
	 * @param array      $field  Custom field definition.
	 * @return mixed Modified custom field value.
	 */
	public static function update( $value, $acf_id, $field ) {
		$object = static::get_by_acf_id( $acf_id );
		return empty( $object ) ? $value : $object->update( $value, $field );
	}

	/**
	 * Copies or synchronizes ACF custom fields when using Polylang's copy post function (and not the post-new.php where ACF filters are applied).
	 * (e.g. using bulk translate, creating a synchronized post).
	 *
	 * @since 3.7
	 *
	 * @param int    $post_id    ID of the source post.
	 * @param int    $tr_post_id ID of the target post.
	 * @param string $lang       Language of the target post.
	 * @param string $sync      `sync` if doing synchro, `copy` otherwise.
	 * @return void
	 *
	 * @phpstan-param 'sync'|'copy' $sync
	 */
	public static function on_post_synchronized( $post_id, $tr_post_id, $lang, $sync ) {
		( new Post( $post_id ) )->on_post_synchronized( $tr_post_id, $lang, $sync );
	}

	/**
	 * Export custom fields to translate.
	 *
	 * @since 3.7
	 *
	 * @param PLL_Export_Data $export The export object.
	 * @param object          $from   The object to export.
	 * @param object|null     $to     The translated object if it exists, `null` otherwise.
	 * @return void
	 */
	public static function export( $export, $from, $to ) {
		$object = self::get_by_object( $from );
		if ( ! empty( $object ) && $export instanceof PLL_Export_Data ) {
			$object->export( $export, $to );
		}
	}

	/**
	 * Collects post IDs from fields.
	 *
	 * @since 3.7
	 *
	 * @param int[]   $linked_ids Object IDs linked to a post.
	 * @param WP_Post $post       The post we get other post from.
	 * @return int[]
	 */
	public static function collect_post_ids( $linked_ids, $post ) {
		$object = self::get_by_object( $post );

		if ( ! empty( $object ) ) {
			return array_merge( (array) $linked_ids, ( new Collect_Post_Ids() )->get( $object ) );
		}

		return $linked_ids;
	}

	/**
	 * Collects post IDs from fields in ACF blocks.
	 *
	 * @since 3.7
	 *
	 * @param int[]   $linked_ids Object IDs linked to a post.
	 * @param WP_Post $post       The post we get other post from.
	 * @return int[]
	 */
	public static function collect_post_ids_in_blocks( $linked_ids, $post ) {
		if ( has_blocks( $post ) ) {
			$linked_ids = array_merge( (array) $linked_ids, ( new Collect_Post_Ids() )->get( new Blocks( $post->ID ) ) );
		}

		return $linked_ids;
	}

	/**
	 * Collects term IDs from fields.
	 *
	 * @since 3.7
	 *
	 * @param int[]   $linked_ids Object IDs linked to a post.
	 * @param WP_Post $post       The post we get other term from.
	 * @return int[]
	 */
	public static function collect_term_ids( $linked_ids, $post ) {
		$object = self::get_by_object( $post );

		if ( ! empty( $object ) ) {
			return array_merge( (array) $linked_ids, ( new Collect_Term_Ids() )->get( $object ) );
		}

		return $linked_ids;
	}


	/**
	 * Collects term IDs from fields in ACF blocks.
	 *
	 * @since 3.7
	 *
	 * @param int[]   $linked_ids Object IDs linked to a post.
	 * @param WP_Post $post       The post we get other term from.
	 * @return int[]
	 */
	public static function collect_term_ids_in_blocks( $linked_ids, $post ) {
		if ( has_blocks( $post ) ) {
			$linked_ids = array_merge( (array) $linked_ids, ( new Collect_Term_Ids() )->get( new Blocks( $post->ID ) ) );
		}

		return $linked_ids;
	}

	/**
	 * Translates the custom fields from a given object.
	 *
	 * @since 3.7
	 *
	 * @param object       $from         Source object to get the custom fields from.
	 * @param object       $to           Translated object to translate the custom fields from.
	 * @param PLL_Language $target_lang  Target language object.
	 * @param Translations $translations A set of translations to search the custom fields translations in.
	 * @return void
	 */
	public static function translate( $from, $to, $target_lang, $translations ) {
		/*
		 * Remove filter for `Dispatcher::translate_rendered_field` to avoid running `Strategy\Copy` on the same object.
		 * For instance, when translating fields with DeepL, we don't want to override the translated values with the original ones with `Strategy\Copy`.
		 */
		remove_filter( 'acf/pre_render_field', array( self::class, 'translate_rendered_field' ) );

		$object = self::get_by_object( $from );
		if ( ! empty( $object ) && $target_lang instanceof PLL_Language && $translations instanceof Translations ) {
			$object->translate( $to, $target_lang, $translations );
		}
	}


	/**
	 * Adds the language of the current object to the arguments that will be used for the query
	 * in the `relationship` and `post_object` ACF fields.
	 *
	 * @since 3.7
	 *
	 * @param array      $args   Arguments to retrieve posts.
	 * @param array      $field  The current field.
	 * @param int|string $acf_id ACF post ID.
	 * @return array The arguments to retrieve posts with the current object language.
	 */
	public static function add_language_to_query( $args, $field, $acf_id ) {
		/**
		 * Gets the language sent with AJAX to reload fields in the right language.
		 * For the term creation page, since there is no current language.
		 * And for the term edit page when switching languages, since the language is not saved on switch.
		 * See `addFilter` on `relationship_ajax_data` and `select2_ajax_data` in `term.js` for the language dispatch.
		 */
		$language = isset( $_POST['lang'] ) ? PLL()->model->languages->get( sanitize_key( $_POST['lang'] ) ) : null; // phpcs:ignore WordPress.Security.NonceVerification.Missing
		if ( isset( $language ) && $language instanceof PLL_Language ) {
			$args['lang'] = $language->slug;
			return $args;
		}

		// Otherwise it's a term editing page (displayed without language switch) or a post editing/creation page.
		$object = static::get_by_acf_id( $acf_id );
		return empty( $object ) ? $args : $object->add_language_to_query( $args );
	}

	/**
	 * Translates the media fields.
	 *
	 * @since 3.7
	 *
	 * @param int          $from_id         The source media ID.
	 * @param int          $to_id           The target media ID.
	 * @param PLL_Language $target_language The target language.
	 * @return void
	 */
	public static function copy_media_fields( $from_id, $to_id, $target_language ) {
		( new Media( $from_id ) )->copy_fields( $to_id, $target_language );
	}

	/**
	 * Saves ACF fields for a term that was automatically duplicated when a post has been duplicated.
	 *
	 * @since 3.7
	 *
	 * @param int    $from Term ID of the source term.
	 * @param int    $to   Term ID of the new term translation.
	 * @param string $lang Language code of the new translation.
	 * @return void
	 */
	public static function on_duplicate_term( $from, $to, $lang ) {
		$lang = PLL()->model->get_language( $lang );
		if ( $lang instanceof PLL_Language ) {
			( new Term( $from ) )->apply_to_all_fields(
				new Copy(),
				$to,
				array( 'target_language' => $lang )
			);
		}
	}

	/**
	 * Copies the field values in blocks.
	 *
	 * @since 3.7
	 *
	 * @param array        $blocks      The blocks.
	 * @param PLL_Language $target_lang The target language.
	 * @param WP_Post|null $source_post The source post, `null` if not available.
	 * @return array The blocks.
	 */
	public static function copy_blocks( $blocks, $target_lang, $source_post ) {
		if ( ! is_array( $blocks ) || ! $target_lang instanceof PLL_Language || ! $source_post instanceof WP_Post ) {
			return $blocks;
		}
		return ( new Blocks() )->copy( $blocks, $target_lang, $source_post );
	}

	/**
	 * Translates fields in blocks during import.
	 *
	 * @since 3.7
	 *
	 * @param WP_Post      $to              Translated post where to translate the custom fields included in blocks.
	 * @param WP_Post      $from            Source post where to get the custom fields included in blocks.
	 * @param PLL_Language $target_lang     The target language.
	 * @param Translations $translations    A set of translations where to search translations of the custom fields translations included in blocks.
	 * @return WP_Post The translated post.
	 */
	public static function translate_blocks( $to, $from, $target_lang, $translations ) {
		if ( ! $to instanceof WP_Post ) {
			return $to;
		}

		return ( new Blocks( $from->ID ) )->translate( $to, $target_lang, $translations );
	}

	/**
	 * Adds ACF fields in blocks to the exported data.
	 *
	 * @since 3.7
	 *
	 * @param PLL_Export_Data $export The export data.
	 * @param WP_Post         $from   The source post.
	 * @return void
	 */
	public static function export_blocks( $export, $from ) {
		( new Blocks( $from->ID ) )->export( $export );
	}

	/**
	 * Builds an Abstract_Object based on the object type, typically post or term.
	 *
	 * @since 3.7
	 *
	 * @param int|string $acf_id ACF post ID.
	 * @return Abstract_Object|null.
	 */
	protected static function get_by_acf_id( $acf_id ): ?Abstract_Object {
		$decoded = acf_decode_post_id( $acf_id );
		$id      = (int) $decoded['id'];

		switch ( $decoded['type'] ) {
			case 'post':
				if ( PLL()->options['media_support'] && 'attachment' === get_post_type( $id ) ) {
					return new Media( $id );
				}
				if ( pll_is_translated_post_type( (string) get_post_type( $id ) ) ) {
					return new Post( $id );
				}
				break;
			case 'term':
				$term = get_term( $id );
				if ( $term instanceof WP_Term && pll_is_translated_taxonomy( $term->taxonomy ) ) {
					return new Term( $id );
				}

				// No nonce to check.
				if ( 0 === $id && ! empty( $_GET['new_lang'] ) && ! empty( $_GET['taxonomy'] ) // phpcs:ignore WordPress.Security.NonceVerification.Recommended
					&& pll_is_translated_taxonomy( sanitize_key( $_GET['taxonomy'] ) ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
					// This is a term creation with `term_0`, see `acf_form_taxonomy::add_term()`.
					return new Term( $id );
				}
				break;
		}

		return null;
	}

	/**
	 * Builds an Abstract_Object based on the WP object, typically `WP_Post` or `WP_Term`.
	 *
	 * @since 3.7
	 *
	 * @param object $object The object.
	 * @return Abstract_Object|null.
	 */
	protected static function get_by_object( $object ): ?Abstract_Object {
		if ( $object instanceof WP_Post ) {
			if ( 'attachment' === $object->post_type && PLL()->options['media_support'] ) {
				return new Media( $object->ID );
			}
			if ( pll_is_translated_post_type( $object->post_type ) ) {
				return new Post( (int) $object->ID );
			}
		}
		if ( $object instanceof WP_Term && pll_is_translated_taxonomy( $object->taxonomy ) ) {
			return new Term( $object->term_id );
		}

		return null;
	}
}
