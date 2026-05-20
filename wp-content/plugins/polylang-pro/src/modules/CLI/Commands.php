<?php
/**
 * @package Polylang Pro
 */

namespace WP_Syntex\Polylang_Pro\Modules\CLI;

use WP_CLI;
use PLL_Model;
use WP_Syntex\Polylang_Pro\Modules\CLI\Command\Language;
use WP_Syntex\Polylang_Pro\Modules\CLI\Command\Setting;

/**
 * Registers Polylang CLI Commands.
 *
 * @since 3.8
 */
class Commands {

	/**
	 * Polylang model.
	 *
	 * @var PLL_Model
	 */
	private $model;

	/**
	 * Base command.
	 *
	 * @var string
	 */
	private $base = 'pll';

	/**
	 * Constructor.
	 *
	 * @since 3.8
	 *
	 * @param PLL_Model $model The Polylang model.
	 */
	public function __construct( PLL_Model $model ) {
		$this->model = $model;
	}

	/**
	 * Registers Polylang CLI Commands.
	 *
	 * @since 3.8
	 *
	 * @return void
	 */
	public function register(): void {
		WP_CLI::add_command( "$this->base setting", new Setting( $this->model->options ) );
		WP_CLI::add_command( "$this->base language", new Language( $this->model->languages ) );
	}
}
