<?php
/**
 * @package Polylang-Pro
 */

/**
 * Class to manage object IDs translation across pieces of content.
 *
 * @since 3.7
 */
class PLL_Sync_Ids {
	/**
	 * @var PLL_Model
	 */
	public $model;

	/**
	 * Constructor.
	 *
	 * @since 3.7
	 *
	 * @param PLL_Model $model Main model.
	 */
	public function __construct( PLL_Model $model ) {
		$this->model = $model;
	}
	/**
	 * Translates a single ID according to the given type.
	 *
	 * @since 3.7
	 *
	 * @param mixed        $id              Numeric value of the source object ID.
	 * @param string       $type            Object type (`post`, `term`, `attachment` or `wp_block`).
	 * @param PLL_Language $target_language Target language.
	 * @param WP_Post|null $target_post     Main target post object. Default to `null`, meaning not saved yet or not applicable.
	 * @return string|int Translated ID, `0` if not found.
	 *
	 * @phpstan-return ($id is int ? int : string)
	 */
	public function translate( $id, string $type, PLL_Language $target_language, ?WP_Post $target_post = null ) {
		if ( ! is_numeric( $id ) ) {
			return 0;
		}

		$result = 0;
		switch ( $type ) {
			case 'post':
				$result = $this->model->post->get( (int) $id, $target_language );
				break;

			case 'term':
				$result = $this->model->term->get( (int) $id, $target_language );
				break;

			case 'attachment':
				global $wpdb;

				$result = $this->model->post->get( (int) $id, $target_language );
				if ( empty( $result ) ) {
					$result = $this->model->post->create_media_translation( (int) $id, $target_language );
				}

				// If we don't have a translation and did not success to create one, return current media
				if ( empty( $result ) ) {
					break;
				}

				// Attach to the translated post
				if ( ! wp_get_post_parent_id( $result ) && $target_post instanceof WP_Post && 0 < (int) $target_post->ID ) {
					// Query inspired by wp_media_attach_action()
					$wpdb->query( $wpdb->prepare( "UPDATE $wpdb->posts SET post_parent = %d WHERE post_type = 'attachment' AND ID = %d", (int) $target_post->ID, $result ) );
					clean_attachment_cache( $result );
				}
				break;

			case 'wp_block':
				// Smart copy.
				$result = $this->model->post->get( (int) $id, $target_language );

				if ( ! empty( $result ) ) {
					break;
				}

				$post = get_post( (int) $id );
				if ( empty( $post ) ) {
					break;
				}

				$post->ID = 0;
				$tr_id    = $this->model->post->insert(
					array_merge(
						$post->to_array(),
						array(
							'translations' => $this->model->post->get_translations( (int) $id ),
						)
					),
					$target_language
				);

				$result = is_wp_error( $tr_id ) ? $id : $tr_id;

				break;

			default:
				_doing_it_wrong(
					__METHOD__ . '()',
					'Please use a known type has second parameter `$type`.  Either `post`, `term`, `attachment` or `wp_block`',
					'3.7'
				);
				break;
		}

		// Ensure type is the same.
		return is_string( $id ) ? (string) $result : (int) $result;
	}
}
