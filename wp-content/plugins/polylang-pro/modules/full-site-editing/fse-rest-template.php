<?php
/**
 * @package Polylang-Pro
 */

defined( 'ABSPATH' ) || exit;

/**
 * Expose terms language and translations in the REST API for templates in particular by filtering the queries.
 *
 * @since 3.2
 */
class PLL_FSE_REST_Template extends PLL_REST_Post {
	/**
	 * The post type of the template.
	 *
	 * @var string
	 */
	protected $post_type = '';

	/**
	 * Constructor
	 *
	 * @since 3.2
	 *
	 * @param PLL_REST_API $rest_api      Instance of PLL_REST_API.
	 * @param string[]     $content_types Array with post types as keys and values.
	 * @return void
	 */
	public function __construct( PLL_REST_API &$rest_api, array $content_types ) {
		parent::__construct( $rest_api, $content_types );

		$this->setter_id_name = 'wp_id';
		$this->getter_id_name = 'wp_id';
	}

	/**
	 * Adds the corresponding hooks.
	 *
	 * @since 3.2
	 *
	 * @return self
	 */
	public function init() {
		add_filter( 'rest_dispatch_request', array( $this, 'get_current_post_type_from_route' ), 10, 2 );
		return $this;
	}

	/**
	 * Overloads parent's method to add the delete link and default language into a translation table data for template post types.
	 * See PLL_REST_Post::generate_translation_data.
	 *
	 * @since 3.2
	 *
	 * @param int          $id       The id of the existing post to get datas for the translations table element.
	 * @param int          $tr_id    The id of the translated post for the given language if exists.
	 * @param PLL_Language $language The given language object.
	 * @return array The translation data of the given language.
	 */
	public function get_translation_table_data( $id, $tr_id, $language ) {
		$translation_data = parent::get_translation_table_data( $id, $tr_id, $language );

		// Gets the template id in the `theme // post name` format to be able to delete it from the UI.
		if ( ! empty( $this->post_type ) && ! empty( $tr_id ) ) {
			$templates = get_block_templates( array( 'wp_id' => $tr_id ), $this->post_type );
			if ( ! empty( $templates ) ) {
				$template = reset( $templates );
				$translation_data['template']['id'] = $template->id;
			}
		}

		return $translation_data;
	}

	/**
	 * Returns the slug of the language assigned to the given post.
	 * Overrides the parent method.
	 *
	 * @since 3.2
	 *
	 * @param array $object Post array.
	 * @return string|false Template's language slug. Default language slug if no language
	 *                      is assigned to the template yet. False on failure.
	 */
	public function get_language( $object ) {
		if ( ! empty( $object[ $this->getter_id_name ] ) ) {
			$language = $this->model->{$this->type}->get_language( $object[ $this->getter_id_name ] );

			if ( ! empty( $language ) ) {
				return $language->slug;
			}
		}

		$lang = $this->model->get_default_language();

		if ( ! empty( $lang ) ) {
			return $lang->slug;
		}

		return false;
	}

	/**
	 * Filters templates by language.
	 *
	 * @since 3.2
	 *
	 * @param WP_Query $query WP_Query object.
	 * @return void
	 */
	public function parse_query( $query ) {
		if ( ! empty( $query->query_vars['post_name__in'] ) && is_array( $query->query_vars['post_name__in'] ) ) {
			// Do not filter query for a single item.
			return;
		}

		if ( isset( $query->query_vars['lang'] ) && empty( $query->query_vars['lang'] ) ) {
			// We've been asking not to filter by language.
			return;
		}

		if ( ! PLL_FSE_Tools::is_template_query( $query ) ) {
			// Not a template part query.
			return;
		}

		// Filter a templates list query by the default language.
		if ( ! empty( $this->request ) ) {
			$lang = $this->request->get_param( 'lang' );
		}

		if ( empty( $lang ) ) {
			$lang = $this->model->options['default_lang'];
		}

		// Since it's a template part query, take care of the ones stored as files with the next hook.
		add_filter( 'get_block_templates', array( $this, 'filter_template_part_list' ), 10, 3 );

		$pll_query = new PLL_Query( $query, $this->model );
		$pll_query->query->set( 'lang', $lang );
		$pll_query->filter_query( $this->model->get_language( $lang ) );
	}

	/**
	 * Returns the current post type from the REST route
	 *
	 * @since 3.2
	 *
	 * @param mixed           $result  Response to replace the requested version with. Can be anything a normal
	 *                                 endpoint can return, or null to not hijack the request.
	 * @param WP_REST_Request $request Request used to generate the response.
	 * @return mixed                   Unchanged value.
	 */
	public function get_current_post_type_from_route( $result, $request ) {
		if ( ! $request instanceof WP_REST_Request ) {
			return $result;
		}

		$route = new PLL_FSE_REST_Route( $request->get_route() );

		if ( ! $route->is_template_route() ) {
			return $result;
		}

		$params = $request->get_params();

		if ( ! empty( $params['postType'] ) && is_string( $params['postType'] ) && PLL_FSE_Tools::is_template_post_type( $params['postType'] ) ) {
			$this->post_type = $params['postType'];
		} else {
			$post_type = $route->get_post_type();

			if ( ! empty( $post_type ) ) {
				$this->post_type = $post_type;
			}
		}

		return $result;
	}

	/**
	 * Filters template part list according to the REST request parameters.
	 * Filtering by language is already done on a `WP_Query` level, see {`self::parse_query()`}.
	 *
	 * @since 3.3.2
	 *
	 * @param WP_Block_Template[] $templates     Array of found block templates.
	 * @param array               $query         Arguments to retrieve templates.
	 * @param string              $template_type 'wp_template' or 'wp_template_part'.
	 * @return WP_Block_Template[] Array of filtered block templates.
	 */
	public function filter_template_part_list( $templates, $query, $template_type ) {
		if ( empty( $this->request ) || isset( $query['wp_id'] ) ) {
			return $templates;
		}

		if ( 'wp_template_part' !== $template_type ) {
			return $templates;
		}

		$lang = $this->request->get_param( 'lang' );

		if ( empty( $lang ) ) {
			return $templates;
		}

		$lang = $this->model->get_language( $lang );

		if ( empty( $lang ) ) {
			return $templates;
		}

		$with_untranslated = $this->request->get_param( 'include_untranslated' );

		if ( ! empty( $with_untranslated ) ) {
			$templates = array_merge( $templates, $this->get_untranslated_template_parts( $lang ) );
		}

		return $this->remove_unwanted_template_part_files( $templates, $lang );
	}

	/**
	 * Filters out template part files if they are already existing translations of them in the list.
	 *
	 * @since 3.3.2
	 *
	 * @param WP_Block_Template[] $templates     Array of found block templates.
	 * @param PLL_Language        $current_lang  Current language object.
	 * @return WP_Block_Template[] Array of filtered block templates.
	 */
	private function remove_unwanted_template_part_files( array $templates, PLL_Language $current_lang ) {
		$templates_to_remove = array();

		// First, let's find template part objects with a language.
		foreach ( $templates as $template ) {
			$template_slug = new PLL_FSE_Template_Slug(
				$template->slug,
				array( $current_lang->slug )
			);

			if ( ! empty( $template->wp_id ) && ! empty( $this->model->post->get_language( $template->wp_id ) ) ) {
				// Current template is custom and has a language.
				$templates_to_remove[] = $template_slug->get_template_slug();
			}
		}

		// Then remove duplicates stored as files.
		foreach ( $templates as $i => $template ) {
			if ( empty( $template->wp_id ) && in_array( $template->slug, $templates_to_remove, true ) ) {
				// Duplicated template part stored in a file.
				unset( $templates[ $i ] );
				continue;
			}
		}

		return $templates;
	}

	/**
	 * Returns template parts without translation in the given language.
	 *
	 * @since 3.3.2
	 *
	 * @param PLL_Language $lang The language to check against.
	 * @return WP_Block_Template[] Array of block template parts objects.
	 */
	private function get_untranslated_template_parts( PLL_Language $lang ) {
		$def_lang = $this->model->get_default_language();

		if ( empty( $def_lang ) ) {
			// No default language.
			return array();
		}

		if ( $lang->slug === $def_lang->slug ) {
			// Untranslated template parts are not available for the default language.
			return array();
		}

		$untranslated_posts          = $this->model->post->get_untranslated( $this->post_type, $lang, $def_lang );
		$untranslated_template_parts = array();

		foreach ( $untranslated_posts as $untranslated_post ) {
			$untranslated_template_part = _build_block_template_result_from_post( $untranslated_post );

			if ( is_wp_error( $untranslated_template_part ) ) {
				continue;
			}

			$untranslated_template_parts[] = $untranslated_template_part;
		}

		return $untranslated_template_parts;
	}

	/**
	 * Returns edit post link for site editor.
	 *
	 * @since 3.4.5
	 *
	 * @param int $post_id ID of the post to get edit link from.
	 * @return string|null The edit post link for the given post. Null if none found.
	 */
	protected function get_site_editor_edit_post_link( $post_id ) {
		$post_type = (string) get_post_type( $post_id );
		if ( empty( $post_type ) ) {
			return (string) get_edit_post_link( $post_id, 'keep ampersand' );
		}

		if ( version_compare( $GLOBALS['wp_version'], '6.3', '>=' ) ) {
			return (string) get_edit_post_link( $post_id, 'keep ampersand' );
		}

		// Backward compatibility with WP **<** 6.3.
		$templates = get_block_templates( array( 'wp_id' => $post_id ), 'wp_template_part' );
		if ( empty( $templates ) ) {
			return (string) get_edit_post_link( $post_id, 'keep ampersand' );
		}

		$template = reset( $templates );
		if ( $template->wp_id !== $post_id ) {
			return (string) get_edit_post_link( $post_id, 'keep ampersand' );
		}

		return add_query_arg(
			array(
				'postId'   => rawurlencode( $template->id ), // postId refers to the WP_Block_Template id (i.e. "theme-slug//template-slug").
				'postType' => $template->type,
				'path'     => rawurlencode( '/template-parts/single' ),
				'canvas'   => 'edit',
			),
			admin_url( 'site-editor.php' )
		);
	}
}
