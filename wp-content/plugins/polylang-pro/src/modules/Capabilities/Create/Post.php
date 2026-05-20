<?php
/**
 * @package Polylang-Pro
 */

namespace WP_Syntex\Polylang_Pro\Modules\Capabilities\Create;

use PLL_Model;
use PLL_Language;
use WP_Syntex\Polylang\REST\Request;
use WP_Syntex\Polylang\Capabilities\Capabilities;
use WP_Syntex\Polylang\Capabilities\Create\Post as Create_Post;

/**
 * Class to check if the user has the capabilities to create a post in the contextual language.
 */
class Post {
	/**
	 * Create_Post instance.
	 *
	 * @var Create_Post
	 */
	private $create_post;

	/**
	 * PLL_Model instance.
	 *
	 * @var PLL_Model
	 */
	private $model;

	/**
	 * Constructor.
	 *
	 * @since 3.8
	 *
	 * @param PLL_Model         $model     The model instance.
	 * @param Request           $request   The request instance.
	 * @param PLL_Language|null $pref_lang The preferred language.
	 * @param PLL_Language|null $curlang   The current language.
	 */
	public function __construct( PLL_Model $model, Request $request, ?PLL_Language $pref_lang, ?PLL_Language $curlang ) {
		$this->create_post = new Create_Post( $model, $request, $pref_lang, $curlang );
		$this->model       = $model;
	}

	/**
	 * Initializes the capabilities checker.
	 *
	 * @since 3.8
	 *
	 * @return self
	 */
	public function init(): self {
		add_filter( 'wp_insert_post_empty_content', array( $this, 'check_capabilities' ), 0, 2 );

		return $this;
	}

	/**
	 * Checks if the user has the capabilities to create a post in the contextual language.
	 *
	 * @since 3.8
	 *
	 * @param bool  $maybe_empty Whether the post should be considered "empty". Unused.
	 * @param array $postarr     Array of post data.
	 *
	 * @return bool|never Untouched `$maybe_empty` parameter value. Die if the user does not have the capabilities.
	 */
	public function check_capabilities( $maybe_empty, $postarr ) {
		if ( ! empty( $postarr['ID'] ) || ! $this->model->post->is_translated_object_type( $postarr['post_type'] ) ) {
			return $maybe_empty;
		}

		Capabilities::get_user()->can_translate_or_die(
			$this->create_post->get_language()
		);

		return $maybe_empty;
	}
}
