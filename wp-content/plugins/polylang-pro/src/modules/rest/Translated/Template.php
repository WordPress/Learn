<?php
/**
 * @package Polylang-Pro
 */

namespace WP_Syntex\Polylang_Pro\REST\Translated;

use PLL_REST_API;
use PLL_FSE_Tools;

/**
 * Expose language and translations in the REST API for templates.
 *
 * @since 3.2
 * @since 3.8 Moved from modules/full-site-editing/ and renamed from PLL_FSE_REST_Template.
 */
class Template extends Post {
	/**
	 * Constructor
	 *
	 * @since 3.2
	 *
	 * @param PLL_REST_API $rest_api Instance of PLL_REST_API.
	 * @return void
	 */
	public function __construct( PLL_REST_API &$rest_api ) {
		parent::__construct( $rest_api, PLL_FSE_Tools::get_template_post_types() );
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
		$language = $this->model->{$this->get_type()}->get_language( $this->get_rest_id( $object ) );

		if ( ! empty( $language ) ) {
			return $language->slug;
		}

		$lang = $this->model->get_default_language();

		if ( ! empty( $lang ) ) {
			return $lang->slug;
		}

		return false;
	}

	/**
	 * Returns the REST identifier for the item.
	 *
	 * @since 3.8
	 *
	 * @param array|object $item Item array or object, usually a post or term.
	 * @return int The REST identifier, 0 if not found.
	 */
	protected function get_rest_id( $item ): int {
		if ( is_array( $item ) ) {
			return $item['wp_id'] ?? 0;
		}

		return $item->wp_id ?? 0;
	}

	/**
	 * Returns the database identifier for the item.
	 *
	 * @since 3.8
	 *
	 * @param array|object $item Item array or object, usually a post or term.
	 * @return int The database identifier, 0 if not found.
	 */
	protected function get_db_id( $item ): int {
		if ( is_array( $item ) ) {
			return $item['wp_id'] ?? 0;
		}

		return $item->wp_id ?? 0;
	}
}
