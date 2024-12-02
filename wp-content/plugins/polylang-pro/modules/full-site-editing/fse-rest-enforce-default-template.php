<?php
/**
 * @package Polylang-Pro
 */

defined( 'ABSPATH' ) || exit;

/**
 * A class that makes sure that a template in default language exists when a template in non-default-language is
 * created.
 *
 * @since 3.2
 */
class PLL_FSE_REST_Enforce_Default_Template extends PLL_FSE_Abstract_Module implements PLL_Module_Interface {

	/**
	 * Instance of `PLL_FSE_Template_Model`.
	 *
	 * @var PLL_FSE_Template_Model
	 */
	protected $template_model;

	/**
	 * Constructor.
	 *
	 * @since 3.2
	 *
	 * @param  PLL_Base $polylang Instance of the main Polylang object, passed by reference.
	 * @return void
	 */
	public function __construct( &$polylang ) {
		parent::__construct( $polylang );
		$this->template_model = &$polylang->fse_template_model;
	}

	/**
	 * Returns the module's name.
	 *
	 * @since 3.2
	 *
	 * @return string
	 */
	public static function get_name() {
		return 'fse_enforce_default_template';
	}

	/**
	 * Sub-module init.
	 *
	 * @since 3.2
	 *
	 * @return self
	 */
	public function init() {
		foreach ( PLL_FSE_Tools::get_template_post_types() as $post_type ) {
			add_action( "rest_after_insert_{$post_type}", array( $this, 'maybe_duplicate_template' ), 10, 3 );
		}
		return $this;
	}

	/**
	 * Duplicates the template in default language. Either by creating it or updating its content.
	 * As WordPress creates empty template right away, we have to look after the first insertion of the content
	 * to duplicate it into the default language.
	 *
	 * @since 3.2
	 *
	 * @param  WP_Post         $post     Inserted or updated post object.
	 * @param  WP_REST_Request $request  Request object.
	 * @param  bool            $creating True when creating a post, false when updating.
	 * @return void
	 */
	public function maybe_duplicate_template( $post, $request, $creating ) {
		if ( ! $post instanceof WP_Post || ! $request instanceof WP_REST_Request ) {
			// Invalid arguments.
			return;
		}

		if ( ! pll_is_edit_rest_request( $request ) ) {
			return;
		}
		
		if ( ! empty( $request->get_param( 'from_post' ) ) ) {
			// We're creating a template from a template that exists in the database.
			return;
		}

		$post_lang = $this->model->post->get_language( $post->ID );
		$req_lang  = $request->get_param( 'lang' );
		if ( empty( $post_lang ) || $post_lang->slug !== $req_lang ) {
			// The template's language and the one from the request doesn't match.
			return;
		}

		if ( $creating ) {
			$this->maybe_create_default_template( $post );
		} else {
			$this->maybe_update_default_template_content( $post, $post_lang );
		}
	}

	/**
	 * Creates a template in default language.
	 *
	 * @since 3.2
	 *
	 * @param  WP_Post $post Inserted post object.
	 * @return void
	 */
	protected function maybe_create_default_template( $post ) {
		$def_lang     = $this->model->get_default_language();
		$translations = $this->model->post->get_translations( $post->ID );

		if ( empty( $def_lang ) || ! empty( $translations[ $def_lang->slug ] ) ) {
			// This template already has a corresponding template in the default language.
			return;
		}

		// Create the template in the database.
		$this->template_model->create_template_translation( $post, $def_lang );
	}

	/**
	 * Updates default language template content by duplicating from a secondary language.
	 *
	 * @since 3.2
	 *
	 * @param  WP_Post      $post Updated post object.
	 * @param  PLL_Language $lang The requested language object.
	 * @return void
	 */
	protected function maybe_update_default_template_content( $post, $lang ) {
		$def_lang = $this->model->get_default_language();

		if ( empty( $def_lang ) || $def_lang->slug === $lang->slug ) {
			// The template's language is the default one.
			return;
		}

		$def_lang_template = get_post( (int) $this->model->post->get_translation( $post->ID, $def_lang->slug ) );

		if ( empty( $def_lang_template ) || ! empty( $def_lang_template->post_content ) ) {
			// The template in default language doesn't exist or has already been duplicated.
			return;
		}

		$def_lang_template->post_content = $post->post_content;

		wp_update_post( $def_lang_template );
	}
}
