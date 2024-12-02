<?php
/**
 * @package Polylang-Pro
 */

/**
 * Duplicate or Synchronize post in Bulk Translate action.
 *
 * @since 2.7
 */
class PLL_Sync_Post_Bulk_Option extends PLL_Bulk_Translate_Option {
	/**
	 * The object used to synchronize posts
	 *
	 * @since 2.7
	 *
	 * @var PLL_Sync_Post_Model
	 */
	private $sync_model;

	/**
	 * Whether the post should be synchronized or not.
	 *
	 * @since 2.7
	 *
	 * @var bool
	 */
	private $do_synchronize;

	/**
	 * Constructor.
	 *
	 * @since 2.7
	 *
	 * @param array               $args       An array of options, mainly for synchronizing the post.
	 * @param PLL_Model           $model      An instance the current Polylang Model.
	 * @param PLL_Sync_Post_Model $sync_model Used to perform synchronization operations.
	 */
	public function __construct( $args, $model, $sync_model ) {
		parent::__construct( $args, $model );
		$this->do_synchronize = $args['do_synchronize'];
		$this->sync_model = $sync_model;
	}

	/**
	 * Checks whether the option should be selectable by the user.
	 *
	 * @since 2.7
	 *
	 * @return bool
	 */
	public function is_available() {
		$screen = get_current_screen();

		if ( $screen && 'edit' === $screen->base ) {
			$post_type = get_post_type_object( $screen->post_type );
			return $post_type && current_user_can( $post_type->cap->edit_posts );
		}

		return false;
	}


	/**
	 * Duplicates or Synchronize the given post, depending on the value of {@see PLL_Sync_Post_Bulk_Action::$synchronize}
	 *
	 * @since 2.7
	 *
	 * @param int    $object_id Identifies a post to duplicate or synchronize.
	 * @param string $lang      A language locale.
	 */
	public function translate( $object_id, $lang ) {
		if ( false === $this->do_synchronize ) {
			$this->sync_model->save_group( $object_id, array() );
		}

		$this->sync_model->copy_post( $object_id, $lang, $this->do_synchronize );
	}
}
