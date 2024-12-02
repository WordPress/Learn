<?php
/**
 * @package Polylang-Pro
 */

defined( 'ABSPATH' ) || exit;

/**
 * Abstract class for FSE modules.
 *
 * @since 3.2
 */
abstract class PLL_FSE_Abstract_Module {

	/**
	 * Instance of `PLL_Model`.
	 *
	 * @var PLL_Model
	 */
	protected $model;

	/**
	 * Plugin's options.
	 *
	 * @var array
	 */
	protected $options;

	/**
	 * Constructor.
	 *
	 * @since 3.2
	 *
	 * @param  PLL_Base $polylang Instance of the main Polylang object, passed by reference.
	 * @return void
	 */
	public function __construct( PLL_Base &$polylang ) {
		$this->model   = &$polylang->model;
		$this->options = &$polylang->options;
	}

	/**
	 * Returns the list of the slugs of enabled languages.
	 *
	 * @since 1.0
	 *
	 * @return string[]
	 */
	protected function get_languages_slugs() {
		return $this->model->get_languages_list( array( 'fields' => 'slug' ) );
	}
}
