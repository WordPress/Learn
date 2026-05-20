<?php
/**
 * @package Polylang-Pro
 */

use WP_Syntex\Polylang\Model\Languages;

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
			/* translators: accessibility text, %s is a native language name */
			'disabled'   => sprintf( __( 'You are not allowed to synchronize this post in %s', 'polylang-pro' ), $language->name ),
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

	/**
	 * Tells whether the button is disabled or not.
	 *
	 * @since 3.8
	 *
	 * @return bool
	 */
	public function is_disabled(): bool {
		global $post_ID;

		if ( empty( $post_ID ) ) {
			return true;
		}

		$action = (string) current_action();

		if ( ! str_starts_with( $action, 'pll_before_post_translation_' ) ) {
			return false;
		}

		$lang = substr( $action, 28 );

		return ! $this->sync_model->current_user_can_synchronize( $post_ID, $lang );
	}

	/**
	 * Saves the button state.
	 * Returns `false` if the button is disabled.
	 *
	 * @since 3.8
	 *
	 * @param string $post_type Current post type.
	 * @param bool   $active    New requested button state.
	 * @return bool Whether the new button state is accepted or not.
	 */
	protected function toggle_option( $post_type, $active ) { // phpcs:ignore VariableAnalysis.CodeAnalysis.VariableAnalysis.UnusedVariable
		if ( ! isset( $_POST['_pll_nonce'] ) || ! is_string( $_POST['_pll_nonce'] ) || ! wp_verify_nonce( $_POST['_pll_nonce'], 'pll_language' ) ) {
			return false;
		}

		if ( ! isset( $_POST['action'], $_POST['pll_post_id'] ) || ! is_string( $_POST['action'] ) || ! is_scalar( $_POST['pll_post_id'] ) ) {
			return false;
		}

		$post_ID = absint( $_POST['pll_post_id'] );

		if ( empty( $post_ID ) ) {
			return false;
		}

		$languages = $this->model->languages->filter( 'translator' )->get_list( array( 'fields' => 'slug' ) );

		if ( empty( $languages ) ) {
			return false;
		}

		$languages = implode( '|', $languages );

		if ( ! preg_match( "/^toggle_pll_sync_post\[(?<lang>{$languages})\]$/", wp_unslash( $_POST['action'] ), $matches ) ) { // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
			// The user is not allowed to translate to the given language.
			return false;
		}

		return $this->sync_model->current_user_can_synchronize( $post_ID, $matches['lang'] );
	}
}
