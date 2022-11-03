<?php
/**
 * Loads the Premium Templates module.
 *
 * @package sensei-pro
 * @author Automattic
 * @since 1.7.0
 */

use Sensei_Pro_Premium_Templates\Premium_Templates;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once dirname( __FILE__ ) . '/includes/class-premium-templates.php';

Premium_Templates::init( \Sensei_Pro\Modules\assets_loader( Premium_Templates::MODULE_NAME ), __DIR__ );
