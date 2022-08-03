<?php
/**
 * File containing the class \Sensei_Pro_Student_Groups\Assets\Assets_Provider.
 *
 * @package student-groups
 * @since   1.4.0
 */

namespace Sensei_Pro_Student_Groups\Assets;

use \Sensei_Pro\Assets;

/**
 * Assets_Provider class
 */
class Components_Provider {

	const MODULE_NAME = 'student-groups';
	/**
	 * Script loading.
	 *
	 * @var Assets
	 */
	private $js_assets = null;

	/**
	 * Style loading.
	 *
	 * @var Assets
	 */
	private $css_assets = null;

	/**
	 * Class constructor.
	 *  Two Assets instances are required because by default the wp-scripts build
	 *  is storing the css files in folder at the same level of the module and each Sensei Asset instance is limited by the folder
	 *  E.g.
	 *  student-groups
	 *  style-student-groups
	 */
	public function __construct() {
		$this->js_assets  = new Assets( self::MODULE_NAME );
		$this->css_assets = new Assets( 'style-' . self::MODULE_NAME );
	}

	/**
	 * Enqueue a component following the wp-script build conventions.
	 *
	 * @param string  $component Component name without file extension.
	 * @param array   $js_dependencies JS Dependencies.
	 * @param array   $css_dependencies CSS Dependencies.
	 * @param boolean $auto_include_component_css Enable/Disable the component css auto loading.
	 * @return void
	 */
	public function enqueue_component( string $component, array $js_dependencies = [], array $css_dependencies = [], bool $auto_include_component_css = true ) {

		$js_path  = $component . '.js';
		$css_path = $component . '.css';

		$this->js_assets->enqueue( self::MODULE_NAME . '-' . $component, $js_path, $js_dependencies );

		if ( $auto_include_component_css ) {
			$this->css_assets->enqueue( self::MODULE_NAME . '-style-' . $component, $css_path, $css_dependencies );
		}

	}
}


