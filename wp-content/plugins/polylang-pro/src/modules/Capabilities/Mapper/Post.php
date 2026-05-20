<?php
/**
 * @package Polylang-Pro
 */

namespace WP_Syntex\Polylang_Pro\Modules\Capabilities\Mapper;

use Closure;
use WP_Post;
use PLL_Model;
use WP_Post_Type;
use PLL_Sync_Post_Model;
use WP_Syntex\Polylang\Capabilities\User\User_Interface;

/**
 * Class to map edit and delete post type capabilities.
 *
 * @since 3.8
 */
class Post extends Abstract_Mapper {
	/**
	 * List of all post type capabilities with predefined primitive capabilities.
	 *
	 * @var string[]
	 */
	private $capabilities = array(
		'edit_post',
		'delete_post',
		'edit_post_meta',
		'delete_post_meta',
		'add_post_meta',
	);

	/**
	 * List of "delete" post type capabilities with predefined primitive capabilities.
	 *
	 * @var string[]
	 */
	private $delete_post_capabilities = array(
		'delete_post',
	);

	/**
	 * PLL_Model instance.
	 *
	 * @var PLL_Model
	 */
	protected $model;

	/**
	 * Sync post model instance.
	 *
	 * @var PLL_Sync_Post_Model
	 */
	private $sync_post_model;

	/**
	 * Used to prevent an infinite loop in `map()`, and improve performance.
	 *
	 * @var array
	 */
	private $processing_sync = array();

	/**
	 * Constructor.
	 *
	 * @since 3.8
	 *
	 * @param PLL_Model           $model           Model instance.
	 * @param PLL_Sync_Post_Model $sync_post_model Sync post model instance.
	 */
	public function __construct( PLL_Model $model, PLL_Sync_Post_Model $sync_post_model ) {
		$this->model           = $model;
		$this->sync_post_model = $sync_post_model;

		array_map(
			array( $this, 'add_capabilities' ),
			get_post_types( array(), 'objects' ),
		);

		add_action( 'registered_post_type', Closure::fromCallable( array( $this, 'load_capabilities' ) ), 10, 2 );
	}

	/**
	 * Maps a capability to the primitive capabilities required of the given user.
	 *
	 * @since 3.8
	 *
	 * @param string[]       $caps Primitive capabilities required of the user.
	 * @param string         $cap  Capability being checked.
	 * @param User_Interface $user The user object.
	 * @param array          $args Adds context to the capability check, typically starting with an object ID.
	 * @return string[] Updated primitive capabilities required of the user.
	 */
	public function map( array $caps, string $cap, User_Interface $user, array $args ): array {
		if ( empty( $args[0] ) ) {
			// No additional arguments.
			return parent::map( $caps, $cap, $user, $args );
		}

		if ( ! $user->is_translator() ) {
			return parent::map( $caps, $cap, $user, $args );
		}

		if ( ! in_array( $cap, $this->capabilities, true ) ) {
			return parent::map( $caps, $cap, $user, $args );
		}

		// Accepts a post ID or a WP_Post as WordPress does, the latter being undocumented.
		$post = get_post( $args[0] );

		if ( ! $post instanceof WP_Post ) {
			// Wrong type of argument, or the post doesn't exist.
			return parent::map( $caps, $cap, $user, $args );
		}

		$key_suffix = sprintf( '|%d|%s', $user->get_id(), $cap );

		if ( ! empty( $this->processing_sync[ $post->ID . $key_suffix ] ) ) {
			// Already processing this post in the `foreach` loop at the end of this method.
			return parent::map( $caps, $cap, $user, $args );
		}

		if ( ! $this->model->post->is_translated_object_type( $post->post_type ) ) {
			return parent::map( $caps, $cap, $user, $args );
		}

		$language = $this->model->post->get_language( $post->ID );

		if ( ! $language || ! $user->can_translate( $language ) ) {
			$caps[] = 'do_not_allow';
			return $caps;
		}

		if ( in_array( $cap, $this->delete_post_capabilities, true ) ) {
			// No need to deal with synchronization.
			return parent::map( $caps, $cap, $user, $args );
		}

		$synchros = $this->sync_post_model->get( $post->ID );

		if ( empty( $synchros ) ) {
			return parent::map( $caps, $cap, $user, $args );
		}

		/*
		 * Check all languages at once:
		 * - Much faster here than in the `foreach` loop below.
		 * - This allows to stop here if the user doesn't have all the required language capabilities (no need to check
		 *   anything else in this case).
		 */
		if ( ! $user->can_translate_all( array_keys( $synchros ) ) ) {
			$caps[] = 'do_not_allow';
			return $caps;
		}

		/*
		 * Make sure this post is not synchronized with one that the user can't edit (for whatever reason that is not
		 * related to language capabilities).
		 */
		foreach ( $synchros as $tr_id ) {
			if ( (int) $tr_id === $post->ID ) {
				continue;
			}

			$tr_key = $tr_id . $key_suffix;

			$this->processing_sync[ $tr_key ] = true;

			if ( ! $user->has_cap( $cap, (int) $tr_id ) ) {
				unset( $this->processing_sync[ $tr_key ] );
				$caps[] = 'do_not_allow';
				return $caps;
			}

			unset( $this->processing_sync[ $tr_key ] );
		}

		return parent::map( $caps, $cap, $user, $args );
	}

	/**
	 * Loads the post type capabilities.
	 *
	 * @since 3.8
	 *
	 * @param string       $post_type        Post type. Unused.
	 * @param WP_Post_Type $post_type_object Post type object.
	 * @return void
	 */
	private function load_capabilities( $post_type, $post_type_object ): void {
		$post_type_object instanceof WP_Post_Type && $this->add_capabilities( $post_type_object );
	}

	/**
	 * Adds the capabilities for a post type.
	 *
	 * @since 3.8
	 *
	 * @param WP_Post_Type $post_type_object Post type object.
	 * @return void
	 */
	private function add_capabilities( WP_Post_Type $post_type_object ): void {
		if ( ! $this->model->post->is_translated_object_type( $post_type_object->name ) ) {
			return;
		}

		$this->delete_post_capabilities = array_merge(
			$this->delete_post_capabilities,
			array( $post_type_object->cap->delete_post )
		);
		$this->capabilities             = array_merge(
			$this->capabilities,
			$this->delete_post_capabilities,
			array( $post_type_object->cap->edit_post )
		);
	}
}
