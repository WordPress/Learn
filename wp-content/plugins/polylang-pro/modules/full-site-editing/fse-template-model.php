<?php
/**
 * @package Polylang-Pro
 */

defined( 'ABSPATH' ) || exit;

/**
 * Model for templates.
 *
 * @since 3.2
 */
class PLL_FSE_Template_Model extends PLL_FSE_Abstract_Module implements PLL_Module_Interface {

	/**
	 * Used to translate template content on the fly.
	 *
	 * @var PLL_Sync_Content
	 */
	protected $sync_content;

	/**
	 * Constructor.
	 *
	 * @since 3.2
	 *
	 * @param PLL_Base $polylang Instance of the main Polylang object, passed by reference.
	 */
	public function __construct( &$polylang ) {
		parent::__construct( $polylang );

		$this->sync_content = $polylang->sync_content;
	}

	/**
	 * Returns the module's name.
	 *
	 * @since 3.2
	 *
	 * @return string
	 */
	public static function get_name() {
		return 'fse_template_model';
	}

	/**
	 * Sub-module init.
	 *
	 * @since 3.2
	 *
	 * @return self
	 */
	public function init() {
		return $this;
	}

	/**
	 * Creates a template translation.
	 *
	 * @since 3.2
	 *
	 * @param  WP_Post      $post      Instance of the source template.
	 * @param  PLL_Language $language  Instance of the new translation language.
	 * @return int                     ID of the new template. 0 on failure.
	 */
	public function create_template_translation( WP_Post $post, PLL_Language $language ) {
		$def_language = $this->model->get_default_language();

		if ( empty( $def_language ) ) {
			return 0;
		}

		$post    = $post->to_array();
		$post_id = $post['ID'];
		unset( $post['ID'] );

		// Post's slug.
		$slug_instance = new PLL_FSE_Template_Slug( $post['post_name'], $this->get_languages_slugs() );

		if ( $def_language->slug === $language->slug ) {
			$post['post_name'] = $slug_instance->remove_language();
		} else {
			$post['post_name'] = $slug_instance->update_language( $language->slug );
		}

		// Taxonomies.
		$tax_defaults = array(
			// Theme taxonomy.
			'wp_theme' => wp_get_theme()->get_stylesheet(),
		);

		if ( is_object_in_taxonomy( $post['post_type'], 'wp_template_part_area' ) ) {
			// Template part area taxonomy.
			$tax_defaults['wp_template_part_area'] = WP_TEMPLATE_PART_AREA_UNCATEGORIZED; // @phpstan-ignore-line
		}

		$tax_defaults = array_filter( $tax_defaults, 'taxonomy_exists', ARRAY_FILTER_USE_KEY ); // Make sure `wp_get_post_terms()` doesn't return a `WP_Error` object.

		if ( ! empty( $tax_defaults ) ) {
			$terms = wp_get_post_terms( $post_id, array_keys( $tax_defaults ) );

			if ( is_array( $terms ) ) { // phpStan...
				$terms = wp_list_pluck( $terms, 'slug', 'taxonomy' );
			} else {
				$terms = array();
			}

			$post['tax_input'] = wp_parse_args( $terms, $tax_defaults );
		}

		// Create the post.
		/** @var int|WP_Error */
		$new_post_id = wp_insert_post( wp_slash( $post ) );

		if ( empty( $new_post_id ) || is_wp_error( $new_post_id ) ) {
			// Whoops.
			return 0;
		}

		// Set the language.
		$this->model->post->set_language( $new_post_id, $language );

		// Set the translation group.
		$translations = $this->model->post->get_translations( $post_id );
		$translations[ $language->slug ] = $new_post_id;
		$this->model->post->save_translations( $post_id, $translations );

		return $new_post_id;
	}

	/**
	 * Translates the content of the given template.
	 *
	 * @since 3.2
	 *
	 * @param  WP_Post      $target_template  The template to translate.
	 * @param  int          $from_template_id The source template post ID.
	 * @param  PLL_Language $target_language  The target language object.
	 * @return int          The post ID on success. The value 0 on failure.
	 */
	public function translate_template_content( WP_Post $target_template, $from_template_id, PLL_Language $target_language ) {
		$from_language = $this->model->post->get_language( $from_template_id );

		if ( ! $from_language instanceof PLL_Language ) {
			// The source template has no language defined.
			return 0;
		}

		$target_template->post_content = $this->sync_content->translate_content( $target_template->post_content, $target_template, $from_language, $target_language );

		return wp_update_post( $target_template );
	}
}
