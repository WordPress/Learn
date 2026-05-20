<?php
/**
 * @package Polylang-Pro
 */

/**
 * Manages the synchronization of posts across languages through the REST API
 *
 * @since 2.6
 */
class PLL_Sync_Post_REST {
	/**
	 * @var PLL_Model
	 */
	public $model;

	/**
	 * @var PLL_Sync_Post_Model
	 */
	public $sync_model;

	/**
	 * Constructor
	 *
	 * @since 2.6
	 *
	 * @param PLL_Admin|PLL_REST_Request $polylang Polylang object.
	 */
	public function __construct( &$polylang ) {
		$this->model = &$polylang->model;
		$this->sync_model = &$polylang->sync_post_model;

		add_action( 'rest_api_init', array( $this, 'init' ), 20 ); // After PLL_REST_API.
	}

	/**
	 * Register the 'pll_sync_post' REST field
	 *
	 * @since 2.6
	 *
	 * @return void
	 */
	public function init() {
		foreach ( $this->model->get_translated_post_types() as $type ) {
			register_rest_field(
				$type,
				'pll_sync_post',
				array(
					'get_callback'    => array( $this, 'get_synchronizations' ),
					'update_callback' => array( $this, 'sync_posts' ),
					'schema'          => $this->get_schema(),
				)
			);

			add_action( "rest_after_insert_{$type}", array( $this, 'after_insert_post' ) );
		}
	}

	/**
	 * Returns the object synchronizations.
	 *
	 * Cast to `stdClass` to ensure `json_encode` always returns a JSON object `{}`
	 * rather than a JSON array `[]` when there are no synchronizations.
	 *
	 * @since 2.4
	 *
	 * @param array $object Array of post properties.
	 * @return object
	 */
	public function get_synchronizations( $object ) {
		return (object) array_fill_keys( array_keys( $this->sync_model->get( $object['id'] ) ), true );
	}

	/**
	 * Update the post synchronization group
	 *
	 * @since 2.6
	 *
	 * @param array  $sync_post Array of synchronizations with language code as key and 'true' as value.
	 * @param object $object    The WP_Post object.
	 * @return bool
	 */
	public function sync_posts( $sync_post, $object ) {
		if ( isset( $object->ID ) ) { // Test to avoid a warning with WooCommerce.
			$post_id = (int) $object->ID;

			if ( empty( $sync_post ) ) {
				$this->sync_model->save_group( $post_id, array() );
			} else {
				$languages = array_keys( array_intersect( $sync_post, array( true ) ) );

				foreach ( $languages as $k => $lang ) {
					if ( $this->sync_model->current_user_can_synchronize( $post_id, $lang ) ) {
						$this->sync_model->copy( $post_id, $lang, PLL_Sync_Post_Model::SYNC ); // Don't save the group inside the loop.
					} else {
						unset( $languages[ $k ] );
					}
				}

				$this->sync_model->save_group( $post_id, $languages );
			}
		}

		return true;
	}

	/**
	 * Synchronize posts
	 *
	 * @since 2.6
	 *
	 * @param WP_Post $post Inserted or updated post object.
	 *
	 * @return void
	 */
	public function after_insert_post( $post ) {
		if ( isset( $post->ID ) ) { // Test to avoid a warning with WooCommerce.
			$synchronized_posts = array_diff( $this->sync_model->get( $post->ID ), array( $post->ID ) );
			foreach ( array_keys( $synchronized_posts ) as $lang ) {
				if ( $this->sync_model->current_user_can_synchronize( $post->ID, $lang ) ) {
					$this->sync_model->copy( $post->ID, $lang, PLL_Sync_Post_Model::SYNC );
				}
			}
		}
	}

	/**
	 * Returns the schema for the synchronization group REST field.
	 *
	 * @since 3.8
	 *
	 * @return array JSON schema.
	 */
	private function get_schema(): array {
		return array(
			'description' => __( 'Synchronization group', 'polylang-pro' ),
			'type'        => 'object',
			'properties'  => array_fill_keys(
				$this->model->languages->get_list( array( 'fields' => 'slug' ) ),
				array(
					'type' => 'boolean',
				)
			),
		);
	}
}
