<?php
/**
 * @package Polylang-Pro
 */

/**
 * Manages the synchronization of posts across languages
 *
 * @since 2.1
 */
class PLL_Sync_Post {
	/**
	 * @var PLL_Sync_Post_Model
	 */
	public $sync_model;

	/**
	 * @var PLL_Model
	 */
	public $model;

	/**
	 * Stores all synchronization buttons.
	 *
	 * @var PLL_Sync_Post_Button[]
	 */
	public $buttons;

	/**
	 * Constructor
	 *
	 * @since 2.1
	 * @since 2.7 Registers twos option for the Translate bulk action.
	 *
	 * @param PLL_Frontend|PLL_Admin|PLL_Settings $polylang Polylang object.
	 */
	public function __construct( &$polylang ) {
		$this->sync_model = &$polylang->sync_post_model;
		$this->model      = &$polylang->model;

		// Create buttons in the language metabox.
		if ( $polylang instanceof PLL_Admin && ! wp_doing_cron() && ! ( defined( 'WP_CLI' ) && WP_CLI ) ) {
			// Loaded with `PLL_Admin_Loader`: we're on `admin_init` or `use_block_editor_for_post`.
			$this->add_metabox_buttons();
		}

		add_action( 'pll_bulk_translate_options_init', array( $this, 'add_bulk_translate_options' ) );
		add_filter( 'update_translation_group', array( $this, 'unsync_post' ), 10, 3 );
		add_action( 'pll_save_post', array( $this, 'sync_posts' ), 5 ); // Before PLL_Admin_Sync, Before PLL_ACF, Before PLLWC.
	}

	/**
	 * Registers the buttons.
	 *
	 * @since 3.6.5
	 *
	 * @return void
	 */
	public function add_metabox_buttons(): void {
		foreach ( $this->model->get_languages_list() as $language ) {
			$this->buttons[ $language->slug ] = new PLL_Sync_Post_Button( $this->sync_model, $language );
		}
	}

	/**
	 * Registers options of the Translate bulk action.
	 *
	 * @since 3.6.5
	 *
	 * @param PLL_Bulk_Translate $bulk_translate Instance of `PLL_Bulk_Translate`.
	 * @return void
	 */
	public function add_bulk_translate_options( $bulk_translate ): void {
		$bulk_translate->register_options(
			array(
				new PLL_Sync_Post_Bulk_Option(
					array(
						'name'           => 'pll_sync_post',
						'description'    => __( 'Synchronize translations in selected languages with the original items', 'polylang-pro' ),
						'do_synchronize' => true,
						'priority'       => 10,
					),
					$this->model,
					$this->sync_model
				),
				new PLL_Sync_Post_Bulk_Option(
					array(
						'name'           => 'pll_copy_post',
						'description'    => __( 'Copy original items to selected languages', 'polylang-pro' ),
						'do_synchronize' => false,
						'priority'       => 5,
					),
					$this->model,
					$this->sync_model
				),
			)
		);
	}

	/**
	 * Duplicates the post and saves the synchronization group
	 *
	 * @since 2.1
	 *
	 * @param int $post_id The post id.
	 * @return void
	 */
	public function sync_posts( $post_id ) {
		static $avoid_recursion = false;

		if ( $avoid_recursion ) {
			return;
		}

		if ( ! empty( $_POST['post_lang_choice'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
			// We are editing the post from post.php (only place where we can change the option to sync).
			if ( ! empty( $_POST['pll_sync_post'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
				$sync_post = array_intersect( array_map( 'sanitize_key', $_POST['pll_sync_post'] ), array( 'true' ) ); // phpcs:ignore WordPress.Security.NonceVerification
			}

			if ( empty( $sync_post ) ) {
				$this->sync_model->save_group( $post_id, array() );
				return;
			}
		} else {
			// Quick edit or bulk edit or any place where the Languages metabox is not displayed.
			$sync_post = array_diff( $this->sync_model->get( $post_id ), array( $post_id ) ); // Just remove this post from the list.
		}

		$avoid_recursion = true;

		$languages = array_keys( $sync_post );

		foreach ( $languages as $k => $lang ) {
			if ( $this->sync_model->current_user_can_synchronize( $post_id, $lang ) ) {
				add_filter( 'is_sticky', array( $this, 'handle_sticky_post' ) );
				$this->sync_model->copy_post( $post_id, $lang, false ); // Don't save the group inside the loop.
				remove_filter( 'is_sticky', array( $this, 'handle_sticky_post' ) );
			} else {
				unset( $languages[ $k ] );
			}
		}

		// Save group if the languages metabox is displayed.
		if ( ! empty( $_POST['post_lang_choice'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
			$this->sync_model->save_group( $post_id, $languages );
		}

		$avoid_recursion = false;
	}

	/**
	 *
	 * Unsynchronize a post from the post translations group.
	 *
	 * @since 3.2
	 *
	 * @param (int|string[])[] $tr       List of translations with language codes as array keys and post IDs as array values.
	 *                                   An optional element defined by the 'sync' key includes an array of synchronized translations
	 *                                   with target language code as array keys and source language code as values.
	 * @param string           $old_slug The old language slug.
	 * @param string           $new_slug The new language slug.
	 * @return (int|string[])[]
	 */
	public function unsync_post( $tr, $old_slug, $new_slug ) {
		if ( ! is_array( $tr ) || empty( $tr['sync'] ) || ! is_array( $tr['sync'] ) ) {
			return $tr;
		}

		// Delete sync between translations when deleting a language or update slug.
		// Search old slug in array keys.
		if ( array_key_exists( $old_slug, $tr['sync'] ) ) {
			if ( ! empty( $new_slug ) && ! empty( $tr['sync'][ $old_slug ] ) ) {
				$tr['sync'][ $new_slug ] = $tr['sync'][ $old_slug ];
			}

			unset( $tr['sync'][ $old_slug ] );
		}

		// Search old slug in array values.
		if ( in_array( $old_slug, $tr['sync'] ) ) {
			foreach ( $tr['sync'] as $key => $value ) {
				if ( $value !== $old_slug ) {
					continue;
				}

				// If new slug then replace it.
				if ( $new_slug ) {
					$tr['sync'][ $key ] = $new_slug;
				} else {
					// Otherwise unset the old slug.
					unset( $tr['sync'][ $key ] );
				}
			}
		}

		return $tr;
	}

	/**
	 * Some backward compatibility with Polylang < 2.6
	 * allows for example to call PLL()->sync_post->are_synchronized() used in Polylang for WooCommerce
	 *
	 * @since 2.6
	 *
	 * @param string $func Function name.
	 * @param array  $args Function arguments.
	 * @return mixed|void
	 */
	public function __call( $func, $args ) {
		if ( method_exists( $this->sync_model, $func ) ) {
			if ( WP_DEBUG ) {
				$debug = debug_backtrace( DEBUG_BACKTRACE_IGNORE_ARGS ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions

				trigger_error( // phpcs:ignore WordPress.PHP.DevelopmentFunctions
					sprintf(
						'%1$s() was called incorrectly in %2$s on line %3$s: the call to PLL()->sync_post->%1$s() has been deprecated in Polylang 2.6, use PLL()->sync_post->sync_model->%1$s() instead.' . "\nError handler",
						esc_html( $func ),
						esc_html( $debug[0]['file'] ),
						absint( $debug[0]['line'] )
					)
				);
			}
			return call_user_func_array( array( $this->sync_model, $func ), $args );
		}

		$debug = debug_backtrace( DEBUG_BACKTRACE_IGNORE_ARGS ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions
		trigger_error( // phpcs:ignore WordPress.PHP.DevelopmentFunctions
			sprintf(
				'Call to undefined function PLL()->sync_post->%1$s() in %2$s on line %3$s' . "\nError handler",
				esc_html( $func ),
				esc_html( $debug[0]['file'] ),
				absint( $debug[0]['line'] )
			),
			E_USER_ERROR
		);
	}

	/**
	 * Filter the sticky status of a post.
	 *
	 * @since 3.2
	 *
	 * @param bool $is_sticky Whether or not the source post is sticky.
	 * @return bool
	 */
	public function handle_sticky_post( $is_sticky ) {
		if ( isset( $_POST['_inline_edit'], $_POST['action'] ) && wp_verify_nonce( $_POST['_inline_edit'], 'inlineeditnonce' ) && 'inline-save' === $_POST['action'] ) {
			// For quick edit.
			$is_sticky = isset( $_POST['sticky'] ) && 'sticky' === $_POST['sticky'];
		} elseif ( isset( $_POST['_pll_nonce'], $_POST['action'] ) && wp_verify_nonce( $_POST['_pll_nonce'], 'pll_language' ) && 'editpost' === $_POST['action'] ) {
			// For the classic editor.
			$is_sticky = isset( $_POST['sticky'] ) && 'sticky' === $_POST['sticky'];
		} elseif ( isset( $_GET['_wpnonce'], $_GET['action'], $_GET['sticky'] ) && wp_verify_nonce( $_GET['_wpnonce'], 'bulk-posts' ) && 'edit' === $_GET['action'] ) {
			// For bulk edit (note that 'sticky' can take 3 different values here).
			switch ( $_GET['sticky'] ) {
				case '-1':
					break;
				case 'unsticky':
					$is_sticky = false;
					break;
				case 'sticky':
					$is_sticky = true;
					break;
			}
		}

		return $is_sticky;
	}
}
