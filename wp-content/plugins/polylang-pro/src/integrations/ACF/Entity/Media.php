<?php
/**
 * @package  Polylang-Pro
 */

namespace WP_Syntex\Polylang_Pro\Integrations\ACF\Entity;

use PLL_Language;
use WP_Syntex\Polylang_Pro\Integrations\ACF\Strategy\Copy;

/**
 * This class is part of the ACF compatibility.
 * Handles attachment post type.
 *
 * @since 3.7
 */
class Media extends Post {
	/**
	 * Copies media fields when a new translation is created.
	 *
	 * @since 3.7
	 *
	 * @param int          $to_id           Target media ID.
	 * @param PLL_Language $target_language The language to translate into.
	 * @return void
	 */
	public function copy_fields( $to_id, $target_language ) {
		$target_language = PLL()->model->get_language( $target_language );
		if ( empty( $target_language ) ) {
			return;
		}

		$this->maybe_reset_fields_store( $target_language );

		$this->apply_to_all_fields( new Copy(), $to_id, array( 'target_language' => $target_language ) );
	}

	/**
	 * Transforms a post ID to the corresponding ACF post ID.
	 *
	 * @since 3.7
	 *
	 * @param int $id Post ID.
	 * @return string ACF post ID.
	 */
	protected static function acf_id( $id ): string {
		return 'attachment_' . $id;
	}

	/**
	 * Does nothing for media. `self::translate_fields()` does the job instead.
	 *
	 * @since 3.7
	 *
	 * @param array $field Custom field definition.
	 * @return array Custom field of the target object.
	 */
	public function translate_rendered_field( $field ) { // phpcs:ignore VariableAnalysis.CodeAnalysis.VariableAnalysis.UnusedVariable

		/*
		 * Does nothing, media translations are created in two times.
		 * 1. A request is made to create the media translation.
		 * 2. A redirect is made toward the edit page of the media, where ACF loads the fields.
		 */
		return $field;
	}
}
