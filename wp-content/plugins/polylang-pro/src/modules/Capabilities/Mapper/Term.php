<?php
/**
 * @package Polylang-Pro
 */

namespace WP_Syntex\Polylang_Pro\Modules\Capabilities\Mapper;

use Closure;
use WP_Term;
use PLL_Model;
use WP_Taxonomy;
use WP_Syntex\Polylang\Capabilities\User\User_Interface;

/**
 * Class to map taxonomy capabilities.
 *
 * @since 3.8
 */
class Term extends Abstract_Mapper {
	/**
	 * List of taxonomy capabilities with predefined primitive capabilities.
	 *
	 * @var string[]
	 */
	private $capabilities = array(
		'edit_term',
		'delete_term',
		'edit_term_meta',
		'delete_term_meta',
		'add_term_meta',
	);

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
	 * @param PLL_Model $model Model instance.
	 */
	public function __construct( PLL_Model $model ) {
		$this->model = $model;

		array_map(
			array( $this, 'add_capabilities' ),
			get_taxonomies( array(), 'objects' ),
		);

		add_action( 'registered_taxonomy', Closure::fromCallable( array( $this, 'load_capabilities' ) ) );
	}

	/**
	 * Maps a capability to the primitive capabilities required of the given user.
	 *
	 * @since 3.8
	 *
	 * @param string[]       $caps Primitive capabilities required of the user.
	 * @param string         $cap  Capability being checked.
	 * @param User_Interface $user The user object.
	 * @param array          $args Adds context to the capability check, typically starting with an object ID.
	 * @return string[] Updated primitive capabilities required of the user.
	 */
	public function map( array $caps, string $cap, User_Interface $user, array $args ): array {
		if ( empty( $args[0] ) || ! is_numeric( $args[0] ) ) {
			// No additional arguments or non-numeric.
			return parent::map( $caps, $cap, $user, $args );
		}

		if ( ! $user->is_translator() ) {
			return parent::map( $caps, $cap, $user, $args );
		}

		if ( ! in_array( $cap, $this->capabilities, true ) ) {
			return parent::map( $caps, $cap, $user, $args );
		}

		$term = get_term( (int) $args[0] );
		if ( ! $term instanceof WP_Term || ! $this->model->term->is_translated_object_type( $term->taxonomy ) ) {
			return parent::map( $caps, $cap, $user, $args );
		}

		$language = $this->model->term->get_language( $term->term_id );
		if ( $language && $user->can_translate( $language ) ) {
			return parent::map( $caps, $cap, $user, $args );
		}

		$caps[] = 'do_not_allow';

		return $caps;
	}

	/**
	 * Loads the taxonomy capabilities.
	 *
	 * @since 3.8
	 *
	 * @param string $taxonomy Taxonomy name.
	 * @return void
	 */
	private function load_capabilities( $taxonomy ): void {
		$taxonomy_object = get_taxonomy( $taxonomy );
		if ( ! $taxonomy_object ) {
			return;
		}

		$this->add_capabilities( $taxonomy_object );
	}

	/**
	 * Adds the capabilities for a taxonomy.
	 *
	 * @since 3.8
	 *
	 * @param WP_Taxonomy $taxonomy_object Taxonomy object.
	 * @return void
	 */
	private function add_capabilities( WP_Taxonomy $taxonomy_object ): void {
		if ( ! $this->model->term->is_translated_object_type( $taxonomy_object->name ) ) {
			return;
		}

		$this->capabilities = array_merge(
			$this->capabilities,
			array( $taxonomy_object->cap->edit_terms, $taxonomy_object->cap->delete_terms )
		);
	}
}
