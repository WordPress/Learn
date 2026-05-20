<?php
/**
 * @package Polylang-Pro
 */

namespace WP_Syntex\Polylang_Pro\Modules\Capabilities\Create;

use PLL_Model;
use PLL_Language;
use WP_Syntex\Polylang\REST\Request;
use WP_Syntex\Polylang\Capabilities\Capabilities;
use WP_Syntex\Polylang\Capabilities\Create\Term as Create_Term;

/**
 * Class to check if the user has the capabilities to create a term in the contextual language.
 */
class Term {
	/**
	 * Create_Term instance.
	 *
	 * @var Create_Term
	 */
	private $create_term;

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
		$this->create_term = new Create_Term( $model, $request, $pref_lang, $curlang );
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
		add_filter( 'pre_insert_term', array( $this, 'check_capabilities' ), 0, 2 );

		return $this;
	}

	/**
	 * Checks if the user has the capabilities to create a term in the contextual language.
	 *
	 * @since 3.8
	 *
	 * @param string|\WP_Error $term     The term name to add, or a `WP_Error` object if there's an error. Unused.
	 * @param string           $taxonomy Taxonomy slug.
	 *
	 * @return string|\WP_Error|never The term name to add, or a `WP_Error` object if there's an error. Die if the user does not have the capabilities.
	 */
	public function check_capabilities( $term, $taxonomy ) {
		if ( is_int( $term ) || ! $this->model->term->is_translated_object_type( $taxonomy ) ) {
			return $term;
		}

		Capabilities::get_user()->can_translate_or_die(
			$this->create_term->get_language()
		);

		return $term;
	}
}
