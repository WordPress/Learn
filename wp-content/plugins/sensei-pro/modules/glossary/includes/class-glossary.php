<?php
/**
 * File containing the class \Sensei_Pro_Glossary\Glossary.
 *
 * @package sensei-pro-glossary
 * @since   1.11.0
 */

namespace Sensei_Pro_Glossary;

use Sensei_Pro\Assets;
use function Sensei_Pro\Modules\assets_loader;

/**
 * The main glossary class.
 *
 * @internal
 */
class Glossary {
	/**
	 * The module name.
	 */
	public const MODULE_NAME = 'glossary';

	/**
	 * The glossary admin instance.
	 *
	 * @var Glossary_Admin
	 */
	public $admin;

	/**
	 * The glossary handler instance.
	 *
	 * @var Glossary_Handler
	 */
	public $handler;

	/**
	 * Assets instance for loading module assets.
	 *
	 * @var Assets
	 */
	public $assets;

	/**
	 * Class instance.
	 *
	 * @var self
	 */
	private static $instance;

	/**
	 * Fetch an instance of the class.
	 *
	 * @internal
	 */
	public static function instance(): self {
		if ( ! self::$instance ) {
			self::$instance = new self(
				new Glossary_Admin(),
				new Glossary_Handler( new Glossary_Repository(), new Glossary_Markup_Generator() ),
				assets_loader( self::MODULE_NAME )
			);
		}

		return self::$instance;
	}

	/**
	 * Class constructor.
	 *
	 * @internal
	 *
	 * @param Glossary_Admin   $admin
	 * @param Glossary_Handler $handler
	 * @param Assets           $assets
	 */
	private function __construct( Glossary_Admin $admin, Glossary_Handler $handler, Assets $assets ) {
		$this->admin   = $admin;
		$this->handler = $handler;
		$this->assets  = $assets;
	}

	/**
	 * Initialize the class and add hooks.
	 *
	 * @internal
	 */
	public function init(): void {
		$this->admin->init();
		$this->handler->init();

		add_action( 'wp_footer', [ $this, 'enqueue_frontend_assets' ] );
	}

	/**
	 * Enqueue frontend assets.
	 *
	 * @internal
	 */
	public function enqueue_frontend_assets(): void {
		if ( $this->handler->has_replaced_phrases() ) {
			$this->assets->enqueue( 'sensei-pro-glossary-frontend-js', 'frontend/glossary.js' );
			$this->assets->enqueue( 'sensei-pro-glossary-frontend-css', 'frontend/glossary-style.css' );
		}
	}
}
