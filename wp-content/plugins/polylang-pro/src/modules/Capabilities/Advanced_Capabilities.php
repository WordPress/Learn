<?php
/**
 * @package Polylang-Pro
 */

namespace WP_Syntex\Polylang_Pro\Modules\Capabilities;

use WP_User;
use PLL_Base;
use PLL_Model;
use PLL_Language;
use WP_Syntex\Polylang\Capabilities\Capabilities;
use WP_Syntex\Polylang\Capabilities\User\NOOP;
use WP_Syntex\Polylang\Capabilities\User\User_Interface;
use WP_Syntex\Polylang_Pro\Modules\Capabilities\Mapper\Term;
use WP_Syntex\Polylang_Pro\Modules\Capabilities\Mapper\Post;
use WP_Syntex\Polylang_Pro\Modules\Capabilities\Mapper\Internal;
use WP_Syntex\Polylang_Pro\Modules\Capabilities\User\Translator;
use WP_Syntex\Polylang_Pro\Modules\Capabilities\Mapper\Do_Not_Allow;
use WP_Syntex\Polylang_Pro\Modules\Capabilities\Mapper\Abstract_Mapper;
use WP_Syntex\Polylang_Pro\Modules\Capabilities\Mapper\Machine_Translation;
use WP_Syntex\Polylang_Pro\Modules\Capabilities\Create\Post as Create_Post;
use WP_Syntex\Polylang_Pro\Modules\Capabilities\Create\Term as Create_Term;

/**
 * Class to orchestrate the capabilities mappers.
 *
 * @since 3.8
 */
class Advanced_Capabilities {
	/**
	 * @var Abstract_Mapper
	 */
	private $mapper_chain;

	/**
	 * @var Create_Post
	 */
	public $create_post;

	/**
	 * @var Create_Term
	 */
	public $create_term;

	/**
	 * @var PLL_Model
	 */
	private $model;

	/**
	 * Constructor.
	 *
	 * @since 3.8
	 *
	 * @param PLL_Base $polylang Polylang instance.
	 */
	public function __construct( PLL_Base $polylang ) {
		$this->model       = $polylang->model;
		$this->create_post = (
			new Create_Post(
				$polylang->model,
				$polylang->request,
				property_exists( $polylang, 'pref_lang' ) ? $polylang->pref_lang : null,
				property_exists( $polylang, 'curlang' ) ? $polylang->curlang : null
			)
		)->init();
		$this->create_term = (
			new Create_Term(
				$polylang->model,
				$polylang->request,
				property_exists( $polylang, 'pref_lang' ) ? $polylang->pref_lang : null,
				property_exists( $polylang, 'curlang' ) ? $polylang->curlang : null
			)
		)->init();

		$this->mapper_chain = new Do_Not_Allow();
		$this->mapper_chain
			->set_next( new Post( $polylang->model, $polylang->sync_post_model ) )
			->set_next( new Term( $polylang->model ) )
			->set_next( new Machine_Translation() )
			->set_next( new Internal( Capabilities::LANGUAGES ) )
			->set_next( new Internal( Capabilities::TRANSLATIONS ) );

		add_filter( 'map_meta_cap', array( $this, 'map' ), 10, 4 );
		add_filter( 'pll_admin_preferred_language', array( $this, 'filter_preferred_language' ) );
	}

	/**
	 * Maps a capability to the primitive capabilities required of the given user.
	 *
	 * @since 3.8
	 *
	 * @param string[] $caps    Primitive capabilities required of the user.
	 * @param string   $cap     Capability being checked.
	 * @param int      $user_id The user ID.
	 * @param array    $args    Adds context to the capability check, typically
	 *                          starting with an object ID.
	 * @return string[] Updated primitive capabilities required of the user.
	 */
	public function map( $caps, $cap, $user_id, $args ) {
		if ( ! is_array( $caps ) || ! is_string( $cap ) || ! is_numeric( $user_id ) || ! is_array( $args ) ) {
			// Type safety.
			return $caps;
		}

		return $this->mapper_chain->map(
			$caps,
			$cap,
			Capabilities::get_user( new WP_User( $user_id ) ),
			$args
		);
	}

	/**
	 * Filters the preferred language to ensure it's authorized for translators.
	 * - If the preferred language is authorized, keeps it.
	 * - Otherwise, returns the first authorized language.
	 *
	 * @since 3.8
	 *
	 * @param PLL_Language|false $pref_lang The preferred language.
	 * @return PLL_Language|false The authorized preferred language, or false if none available.
	 */
	public function filter_preferred_language( $pref_lang ) {
		if ( $pref_lang instanceof PLL_Language ) {
			$user = Capabilities::get_user();
			if ( $user->can_translate( $pref_lang ) ) {
				return $pref_lang;
			}
		}

		// Otherwise, return the first authorized language.
		$authorized_languages = $this->model->languages->filter( 'translator' )->get_list();
		// If no language can be found, return false as `PLL_Model::get_default_language()` or `PLL_Model::get_language()`.
		return ! empty( $authorized_languages ) ? reset( $authorized_languages ) : false;
	}
}
