<?php
/**
 * @package Polylang-Pro
 */

namespace WP_Syntex\Polylang_Pro\Integrations\ACF;

use WP_Syntex\Polylang_Pro\Integrations\ACF\Labels\Field_Groups;
use WP_Syntex\Polylang_Pro\Integrations\ACF\Labels\Post_Type;
use WP_Syntex\Polylang_Pro\Integrations\ACF\Labels\Taxonomy;
use WP_Syntex\Polylang_Pro\Integrations\ACF\Translation_Instructions;

/**
 * Manages compatibility with Advanced Custom Fields Pro.
 *
 * @since 2.0
 */
class Main {

	/**
	 * @var Field_Settings
	 */
	public $field_settings;

	/**
	 * @var Field_Groups
	 */
	public $field_groups_labels;

	/**
	 * @var Post_Type
	 */
	public $post_types_labels;

	/**
	 * @var Taxonomy
	 */
	public $taxonomies_labels;

	/**
	 * @var Translation_Instructions
	 */
	public $translation_instructions;

	/**
	 * Constructor
	 *
	 * @since 3.7
	 */
	public function __construct() {
		$this->field_settings           = new Field_Settings();
		$this->field_groups_labels      = new Field_Groups();
		$this->post_types_labels        = new Post_Type();
		$this->taxonomies_labels        = new Taxonomy();
		$this->translation_instructions = new Translation_Instructions();
	}

	/**
	 * Initializes filters for ACF.
	 *
	 * @since 2.0
	 *
	 * @return void
	 */
	public function on_acf_init(): void {
		$this->field_settings->on_acf_init();
		$this->translation_instructions->on_acf_init();

		/**
		 * Filters whether ACF labels translation should be enabled.
		 * This allows users to completely disable ACF labels translation (fields, post types, taxonomies) if they don't need it.
		 *
		 * @since 3.7.5
		 *
		 * @param bool $enabled Whether ACF labels translation is enabled. Default true.
		 */
		$labels_translation_enabled = apply_filters( 'pll_enable_acf_labels_translation', true );
		if ( $labels_translation_enabled ) {
			$this->field_groups_labels->on_acf_init();
			$this->post_types_labels->on_acf_init();
			$this->taxonomies_labels->on_acf_init();
		}

		Dispatcher::on_acf_init();

		acf_register_location_type( Location_Language::class );

		add_filter( 'acf/get_taxonomies', array( $this, 'get_taxonomies' ) );
		add_filter( 'pll_get_post_types', array( $this, 'get_post_types' ) );
		add_action( 'init', array( Dispatcher::class, 'on_blocks_registered' ), 999 ); // Late so blocks have a chance to register, usually done on `init`.

		PLL()->model->cache->clean( 'post_types' ); // A bit hacky.
	}

	/**
	 * Tells whether or not ACF integration can be used.
	 *
	 * @since 3.7
	 *
	 * @return bool True if the integration can be used, false otherwise.
	 */
	public static function can_use(): bool {
		return defined( 'ACF_VERSION' ) && version_compare( ACF_VERSION, '6.0.0', '>=' );
	}

	/**
	 * Prevents ACF to display our private taxonomies.
	 *
	 * @since 2.8
	 *
	 * @param string[] $taxonomies Taxonomy names.
	 * @return string[]
	 */
	public function get_taxonomies( $taxonomies ) {
		return array_diff( $taxonomies, get_taxonomies( array( '_pll' => true ) ) );
	}

	/**
	 * Makes sure not to translate the Field Groups post type.
	 *
	 * @since 2.0
	 * @since 3.7 Removed second param and disallow to translate the field groups.
	 *
	 * @param string[] $post_types List of post types.
	 * @return string[]
	 */
	public function get_post_types( $post_types ) {
		unset( $post_types['acf-field-group'] );
		return $post_types;
	}
}
