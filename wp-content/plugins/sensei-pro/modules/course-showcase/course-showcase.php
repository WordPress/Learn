<?php
/**
 * Loads the Course Showcase module.
 *
 * @package sensei-pro
 * @author Automattic
 * @since 1.12.0
 */

namespace Sensei_Pro\Course_Showcase;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once dirname( __FILE__ ) . '/includes/class-course-showcase.php';

add_action( 'plugins_loaded', [ Course_Showcase::class, 'init' ] );
