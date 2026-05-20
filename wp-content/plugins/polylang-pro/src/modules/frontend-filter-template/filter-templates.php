<?php
/**
 * @package Polylang-Pro
 */

/**
 * Class to filter the template hierarchy.
 *
 * @since 3.7
 */
class PLL_Filter_Templates {
	/**
	 * Current model.
	 *
	 * @var PLL_Model
	 */
	private $model;

	/**
	 * Array of template types to filter, with types as key and class as value.
	 *
	 * @var string[]
	 * @phpstan-var array<non-falsy-string, class-string<PLL_Abstract_Filter_Template>>
	 */
	private $template_types = array(
		'category' => PLL_Filter_Template_Core_Taxonomy::class,
		'tag'      => PLL_Filter_Template_Core_Taxonomy::class,
		'taxonomy' => PLL_Filter_Template_Custom_Taxonomy::class,
		'page'     => PLL_Filter_Template_Page::class,
		'single'   => PLL_Filter_Template_Single::class,
	);

	/**
	 * Constructor.
	 *
	 * @since 3.7
	 *
	 * @param PLL_Frontend $polylang Main Polylang object.
	 */
	public function __construct( PLL_Frontend $polylang ) {
		$this->model = $polylang->model;
	}

	/**
	 * Hooks to the template hierarchy filters with the corresponding objects.
	 *
	 * @since 3.7
	 *
	 * @return self
	 */
	public function init(): self {
		foreach ( $this->template_types as $type => $class ) {
			$template_filter = new $class( $this->model );
			add_filter( "{$type}_template_hierarchy", array( $template_filter, 'filter' ) );
		}

		return $this;
	}
}
