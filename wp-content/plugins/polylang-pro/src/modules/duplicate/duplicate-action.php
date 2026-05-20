<?php
/**
 * @package Polylang-Pro
 */

/**
 * Class to manage duplication action.
 *
 * @since 3.6
 */
class PLL_Duplicate_Action {
	/**
	 * Duplicate user meta name.
	 *
	 * @var string
	 * @phpstan-var non-falsy-string
	 */
	const META_NAME = 'pll_duplicate_content';

	/**
	 * Reference to the plugin options.
	 *
	 * @var \WP_Syntex\Polylang\Options\Options
	 */
	protected $options;

	/**
	 * Reference to the PLL_Sync_Content instance.
	 *
	 * @var PLL_Sync_Content
	 */
	protected $sync_content;

	/**
	 * @var PLL_Admin_Links|null
	 */
	protected $links;

	/**
	 * Used to manage user meta.
	 *
	 * @var PLL_Toggle_User_Meta
	 */
	protected $user_meta;

	/**
	 * Cloned post for REST API slug and content restoration.
	 *
	 * @since 3.8
	 * @var WP_Post|null
	 */
	protected $cloned_post = null;

	/**
	 * Constructor
	 *
	 * @since 3.6
	 *
	 * @param PLL_Admin $polylang Polylang object.
	 */
	public function __construct( PLL_Admin &$polylang ) {
		$this->options      = $polylang->options;
		$this->sync_content = &$polylang->sync_content;
		$this->links        = &$polylang->links;
		$this->user_meta    = new PLL_Toggle_User_Meta( static::META_NAME );

		/*
		 * After class instantiation and before terms and post metas are copied in Polylang.
		 */
		add_filter( 'use_block_editor_for_post', array( $this, 'new_post_translation' ), 2000 );
	}

	/**
	 * Fires the content copy
	 *
	 * @since 2.5
	 * @since 3.1 Add $is_block_editor param as the method is now hooked to the filter use_block_editor_for_post.
	 *
	 * @param bool $is_block_editor Whether the post can be edited or not.
	 * @return bool
	 */
	public function new_post_translation( $is_block_editor ) {
		global $post;
		static $done = array();

		if ( ! $post instanceof WP_Post || in_array( $post->ID, $done, true ) || empty( $this->links ) ) {
			return $is_block_editor;
		}

		// Capability check already done in post-new.php.
		$data = $this->links->get_data_from_new_post_translation_request();

		if ( empty( $data['from_post'] ) || empty( $data['new_lang'] ) || ! $this->user_meta->is_active() ) {
			return $is_block_editor;
		}

		if ( ! current_user_can( 'read_post', $data['from_post'] ) ) {
			wp_die(
				esc_html__( 'Sorry, you are not allowed to read this item.', 'polylang-pro' ),
				403
			);
		}

		$done[] = $post->ID; // Avoid a second duplication in the block editor.

		$this->sync_content->copy_content( $data['from_post'], $post, $data['new_lang'] );

		// Clone the post after copying to capture the computed slug and content for later use in REST response.
		$this->cloned_post = clone $post;

		// Maybe duplicates the featured image.
		if ( $this->options['media_support'] ) {
			add_filter( 'pll_translate_post_meta', array( $this->sync_content, 'duplicate_thumbnail' ), 10, 3 );
		}

		// Maybe duplicate terms.
		add_filter( 'pll_maybe_translate_term', array( $this->sync_content, 'duplicate_term' ), 10, 3 );

		// Ensures the REST preloading has proper slug and content for template resolution.
		add_filter( "rest_prepare_{$post->post_type}", array( $this, 'prepare_response' ), 10, 3 );

		return $is_block_editor;
	}

	/**
	 * Ensures translated posts have proper slug and content assignment during specific REST API editor requests.
	 *
	 * When creating post translations, the new auto-draft post has an empty `post_name`,
	 * which results in an empty slug in REST API responses. This causes the block editor's template resolution system
	 * (`getTemplateId`) to fall back to generic templates instead of specific ones (like `page-{slug}`).
	 *
	 * This hook intercepts REST responses for translation creation context and restores the proper slug and content
	 * from the cloned post, allowing the right template resolution.
	 *
	 * Specifically targets single post requests (`/wp/v2/{post_type}/{id}`) to avoid interference with other endpoints
	 * which don't require data restoration.
	 *
	 * @since 3.8
	 *
	 * @param WP_REST_Response $response The REST response object.
	 * @param WP_Post          $post     The post object being prepared.
	 * @param WP_REST_Request  $request  The REST request object.
	 *
	 * @return WP_REST_Response Modified response with restored slug and content.
	 *
	 * @phpstan-param WP_REST_Request<array> $request
	 */
	public function prepare_response( $response, $post, $request ): WP_REST_Response {
		// Only process edit context requests (editor preloading) with empty slugs and a cloned post available for the same post ID.
		if ( $request->get_param( 'context' ) !== 'edit'
			|| ! empty( $post->post_name )
			|| empty( $this->cloned_post )
			|| $this->cloned_post->ID !== $post->ID ) {
			return $response;
		}

		$post_type_object = get_post_type_object( $post->post_type );
		if ( empty( $post_type_object ) || empty( $post_type_object->rest_base ) || ! is_string( $post_type_object->rest_base ) ) {
			return $response;
		}

		/*
		 * Checks we're in a single post GET request matching WordPress REST route pattern.
		 * WordPress registers routes as: `/wp/v2/{rest_base}/(?P<id>[\d]+)`, see `WP_REST_Posts_Controller::register_routes()`.
		 */
		$expected_route = sprintf( '#^/wp/v2/%s/(?P<id>[\d]+)$#', preg_quote( $post_type_object->rest_base, '#' ) );
		if ( 'GET' !== $request->get_method() || ! preg_match( $expected_route, $request->get_route() ) ) {
			return $response;
		}

		$response_data = $response->get_data();
		if ( ! is_array( $response_data ) ) {
			return $response;
		}

		// Restore slug, content, and excerpt from the cloned post.
		$response_data['slug']           = $this->cloned_post->post_name;
		$response_data['content']['raw'] = $this->cloned_post->post_content;
		$response_data['excerpt']['raw'] = $this->cloned_post->post_excerpt;

		// Template assignment: check for explicitly assigned template (classic themes use _wp_page_template meta).
		$response_data['template'] = (string) get_page_template_slug( $this->cloned_post->ID );
		if ( empty( $response_data['template'] ) ) {
			// No explicit assignment, build slug-specific template for block themes.
			$prefix = ( 'page' === $this->cloned_post->post_type ) ? 'page' : "single-{$this->cloned_post->post_type}";
			$slug   = "{$prefix}-{$this->cloned_post->post_name}";
			if ( get_block_template( get_stylesheet() . '//' . $slug ) ) {
				// Only assign if template exists to prevent "Not found" errors.
				$response_data['template'] = $slug;
			}
		}

		// Update the response with the restored values.
		$response->set_data( $response_data );

		return $response;
	}
}
