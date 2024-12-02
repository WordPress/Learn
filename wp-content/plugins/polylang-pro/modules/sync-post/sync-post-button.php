<?php
/**
 * @package Polylang-Pro
 */

/**
 * Buttons for posts synchronization
 *
 * @since 2.1
 */
class PLL_Sync_Post_Button extends PLL_Metabox_Button {
	/**
	 * @var PLL_Model
	 */
	public $model;

	/**
	 * @var PLL_Sync_Post_Model
	 */
	protected $sync_model;

	/**
	 * The language corresponding to the button.
	 *
	 * @var PLL_Language
	 */
	protected $language;

	/**
	 * Constructor
	 *
	 * @since 2.1
	 *
	 * @param PLL_Sync_Post_Model $sync_model An instance of PLL_Sync_Post_Model.
	 * @param PLL_Language        $language   The language.
	 */
	public function __construct( $sync_model, $language ) {
		$args = array(
			'position'   => "before_post_translation_{$language->slug}",
			'activate'   => __( 'Synchronize this post', 'polylang-pro' ),
			'deactivate' => __( "Don't synchronize this post", 'polylang-pro' ),
			'class'      => 'dashicons-before dashicons-controls-repeat',
			'before'     => '<td class="pll-sync-column pll-column-icon">',
			'after'      => '</td>',
		);

		parent::__construct( "pll_sync_post[{$language->slug}]", $args );

		$this->sync_model = $sync_model;
		$this->model      = $sync_model->model;
		$this->language   = $language;
	}

	/**
	 * Displays the button
	 *
	 * @since 2.6
	 *
	 * @param string $post_type The current post type.
	 * @return void
	 */
	public function add_icon( $post_type ) {
		global $post_ID;

		$action = current_action();

		if ( 0 === strpos( $action, 'pll_before_post_translation_' ) ) {
			$lang = substr( $action, 28 );

			if ( ! empty( $post_ID ) && $this->sync_model->current_user_can_synchronize( $post_ID, $lang ) ) {
				parent::add_icon( $post_type );
			} else {
				printf( '<td class="pll-sync-column pll-column-icon"></td>' );
			}
		}
	}

	/**
	 * Tells whether the button is active or not
	 *
	 * @since 2.1
	 *
	 * @return bool
	 */
	public function is_active() {
		global $post;

		if ( empty( $post ) ) {
			return false; // FIXME this resets all sync when the language is changed.
		}

		$term = $this->model->post->get_object_term( $post->ID, 'post_translations' );

		if ( ! empty( $term ) ) {
			$language = $this->model->post->get_language( $post->ID ); // FIXME is it already evaluated?
			$d        = maybe_unserialize( $term->description );
			return $language && is_array( $d ) && isset( $d['sync'][ $this->language->slug ], $d['sync'][ $language->slug ] ) && $d['sync'][ $this->language->slug ] === $d['sync'][ $language->slug ];
		}

		return false;
	}
}
