<?php
/**
 * @package Polylang-Pro
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class handling the translation of template content during its creation.
 *
 * @since 3.2
 */
class PLL_FSE_REST_Duplicate_Template extends PLL_FSE_Abstract_Module implements PLL_Module_Interface {

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
	 * @param PLL_Base $polylang Instance of the main Polylang object, passed by reference.
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
		return 'fse_rest_duplicate_template';
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
			add_action( "rest_after_insert_{$post_type}", array( $this, 'translate_content' ), 10, 3 );
		}

		add_filter( 'pll_translate_blocks', array( $this, 'translate_blocks' ), 10, 2 );

		return $this;
	}

	/**
	 * Translates template part content on translation creation.
	 *
	 * @since 3.2
	 *
	 * @param WP_Post         $template The inserted or updated template part.
	 * @param WP_REST_Request $request  The current request.
	 * @param bool            $creating True when creating a template part, false when updating.
	 * @return void
	 */
	public function translate_content( $template, $request, $creating ) {
		if ( empty( $creating ) ) {
			return;
		}

		$lang      = $this->model->get_language( $request->get_param( 'lang' ) );
		$from_post = $request->get_param( 'from_post' );

		if ( empty( $lang ) || empty( $from_post ) || ! ( new PLL_FSE_REST_Route( $request->get_route() ) )->is_template_route() ) {
			return;
		}

		$this->template_model->translate_template_content( $template, $from_post, $lang );
	}

	/**
	 * Recursively translate blocks.
	 *
	 * @since 3.2
	 *
	 * @param array[] $blocks {
	 *     An array of blocks arrays.
	 *
	 *     @type string $blockName    Name of block.
	 *     @type array  $attrs        List of block attributes.
	 *     @type array  $innerBlocks  List of inner blocks.
	 *     @type string $innerHTML    Resultant HTML from inside block comment delimiters after removing inner blocks.
	 *     @type array  $innerContent List of string fragments and null markers where inner blocks were found.
	 * }
	 * @param string  $language Slug language of the target post.
	 * @return array Array of translated blocks.
	 */
	public function translate_blocks( $blocks, $language ) {
		foreach ( $blocks as $k => $block ) {
			switch ( $block['blockName'] ) {
				case 'core/template-part':
					if ( ! empty( $block['attrs']['slug'] ) && ! empty( $block['attrs']['theme'] ) ) {
						$blocks[ $k ]['attrs']['slug'] = $this->translate_template_part( $block['attrs'], $language );
					}

					break;
			}
		}

		return $blocks;
	}

	/**
	 * Returns the slug of the template part translation.
	 * Creates the translation if it does not exist.
	 *
	 * @since 3.2
	 *
	 * @param string[] $attrs {
	 *  The template part slug and theme.
	 *
	 *  @type string $slug  Template part slug.
	 *  @type string $theme Template part theme slug.
	 * }
	 * @param string   $language Slug language of the target post.
	 * @return string Slug of the translated template part.
	 */
	public function translate_template_part( $attrs, $language ) {
		$post = PLL_FSE_Tools::query_template_post( $attrs['slug'], $attrs['theme'], 'wp_template_part' );
		if ( empty( $post ) ) {
			return $attrs['slug'];
		}

		$tr_id = $this->model->post->get( $post->ID, $language );

		// If we don't have a translation, then we create it.
		if ( ! $tr_id ) {
			$tr_id = $this->template_model->create_template_translation( $post, $this->model->get_language( $language ) );
		}

		// Check the content of the template part to see if there is any block to translate.
		$tr_post = get_post( $tr_id );
		$this->template_model->translate_template_content( $tr_post, $post->ID, $this->model->get_language( $language ) );

		return $tr_post->post_name;
	}
}
