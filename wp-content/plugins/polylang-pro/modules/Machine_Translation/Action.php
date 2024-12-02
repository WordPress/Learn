<?php
/**
 * @package Polylang-Pro
 */

namespace WP_Syntex\Polylang_Pro\Modules\Machine_Translation;

use WP_Post;
use WP_Error;
use PLL_Model;
use PLL_Language;
use PLL_Admin_Base;
use PLL_Admin_Sync;
use PLL_Export_Container;
use PLL_Toggle_User_Meta;
use PLL_Export_Data_From_Posts;
use WP_Syntex\Polylang_Pro\Modules\Machine_Translation\Data;
use WP_Syntex\Polylang_Pro\Modules\Machine_Translation\Processor;
use WP_Syntex\Polylang_Pro\Modules\Machine_Translation\Services\Service_Interface;

/**
 * Class to manage machine translation action.
 *
 * @since 3.6
 */
class Action {
	/**
	 * Instance of the model.
	 *
	 * @var PLL_Model
	 */
	protected $model;

	/**
	 * Instance of the machine translation service.
	 *
	 * @var Service_Interface
	 */
	protected $service;

	/**
	 * Current language.
	 *
	 * @var PLL_Language|null
	 */
	protected $curlang;

	/**
	 * Instance of the machine translation processor.
	 *
	 * @var Processor
	 */
	protected $processor;

	/**
	 * Used to manage user meta.
	 *
	 * @var PLL_Toggle_User_Meta
	 */
	protected $user_meta;

	/**
	 * Whether or not a new post creation has been processed.
	 *
	 * @var bool
	 */
	protected $done = false;

	/**
	 * Used to disable taxonomy and post meta copy.
	 *
	 * @var PLL_Admin_Sync
	 */
	protected $sync;

	/**
	 * Constructor.
	 *
	 * @since 3.6
	 *
	 * @param PLL_Admin_Base    $polylang The Polylang object.
	 * @param Service_Interface $service  Machine translation service.
	 * @return void
	 */
	public function __construct( PLL_Admin_Base $polylang, Service_Interface $service ) {
		$this->model     = $polylang->model;
		$this->service   = $service;
		$this->curlang   = &$polylang->curlang;
		$this->sync      = $polylang->sync;
		$this->processor = new Processor( $polylang, $this->service->get_client() );
		$this->user_meta = new PLL_Toggle_User_Meta( sprintf( 'pll_machine_translation_%s', $this->service->get_slug() ) );

		/*
		 * Before `PLL_Duplicate::new_post_translation()`.
		 */
		add_filter( 'use_block_editor_for_post', array( $this, 'new_post_translation' ), 1900 );
	}

	/**
	 * Fires the content translation.
	 *
	 * @since 3.6
	 *
	 * @param bool $is_block_editor Whether the post can be edited or not.
	 * @return bool
	 */
	public function new_post_translation( $is_block_editor ) {
		global $post, $pagenow; // `$post` is the autosave of the new post.

		if ( $this->done || empty( $post ) || 'post-new.php' !== $pagenow || empty( $_GET['from_post'] ) || empty( $_GET['new_lang'] ) ) {
			return $is_block_editor;
		}

		// Capability check already done in post-new.php.
		check_admin_referer( 'new-post-translation' );

		if ( ! $this->user_meta->is_active() ) {
			return $is_block_editor;
		}

		// Prevent a second translation in the block editor.
		$this->done = true;

		if ( ! is_string( $_GET['new_lang'] ) || ! is_numeric( $_GET['from_post'] ) ) {
			// Invalid data.
			return $is_block_editor;
		}

		$from_post = get_post( (int) $_GET['from_post'] );

		if ( ! $from_post instanceof WP_Post ) {
			// Invalid post.
			return $is_block_editor;
		}

		$target_language = $this->model->get_language( sanitize_key( $_GET['new_lang'] ) );

		if ( empty( $target_language ) ) {
			// Invalid target language.
			return $is_block_editor;
		}

		// No current language during machine translation process (to avoid filtering queries).
		$current_lang_backup = $this->curlang;
		$this->curlang       = null;
		$container           = new PLL_Export_Container( Data::class );
		$export_objects      = new PLL_Export_Data_From_Posts( $this->model );

		$export_objects->send_to_export( $container, array( $from_post ), $target_language );

		// Save translated data.
		$result = $this->processor->translate( $container );

		// All done, set back current language.
		$this->curlang = $current_lang_backup;

		if ( $result->has_errors() ) {
			pll_add_notice( $result );
			return $is_block_editor;
		}

		$result = $this->processor->save( $container );

		if ( $result->has_errors() ) {
			pll_add_notice( $result );
			return $is_block_editor;
		}

		// Ensure global post object is updated.
		$to_post = get_post( (int) $this->model->post->get_translation( $from_post->ID, $target_language ) );

		if ( empty( $to_post ) ) {
			// The translated post doesn't exist anymore for some reason.
			pll_add_notice(
				new WP_Error(
					'pll_machine_translation_no_translation',
					__( 'Unable to retrieve the translation.', 'polylang-pro' )
				)
			);
			return $is_block_editor;
		}

		$post = $to_post; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited

		// Disable duplication.
		remove_filter( 'use_block_editor_for_post', array( $this->sync, 'new_post_translation' ), 5000 );
		add_filter( 'get_user_metadata', array( $this, 'disable_post_duplication' ), 10, 3 );

		return $is_block_editor;
	}

	/**
	 * Filters the user metas to disable post duplication.
	 *
	 * @since 3.6
	 *
	 * @param mixed  $value     The value to return.
	 * @param int    $object_id ID of the object metadata is for.
	 * @param string $meta_key  Metadata key.
	 * @return mixed False for the post duplication meta, the original value otherwize.
	 */
	public function disable_post_duplication( $value, $object_id, $meta_key ) {
		return 'pll_duplicate_content' === $meta_key ? false : $value;
	}
}
