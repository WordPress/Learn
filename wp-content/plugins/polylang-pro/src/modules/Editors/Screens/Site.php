<?php
/**
 * @package Polylang Pro
 */

namespace WP_Syntex\Polylang_Pro\Editors\Screens;

use PLL_Base;
use PLL_Model;
use WP_Screen;
use PLL_Language;
use PLL_FSE_Tools;

/**
 * Class to manage Site editor scripts.
 */
class Site extends Abstract_Screen {
	/**
	 * @var PLL_Language|false|null
	 */
	protected $curlang;

	/**
	 * Constructor
	 *
	 * @since 3.7
	 *
	 * @param PLL_Base $polylang Polylang object.
	 */
	public function __construct( PLL_Base &$polylang ) {
		parent::__construct( $polylang );

		$this->curlang = &$polylang->curlang;
	}

	/**
	 * Adds required hooks.
	 *
	 * @since 3.7
	 *
	 * @return static
	 */
	public function init() {
		parent::init();
		add_filter( 'pll_admin_ajax_params', array( $this, 'ajax_filter' ) );

		return $this;
	}

	/**
	 * Adds the language to the data added to all AJAX requests.
	 *
	 * @since 3.7
	 *
	 * @param array $params List of parameters to add to the admin ajax request.
	 * @return array
	 */
	public function ajax_filter( $params ) {
		$screen = get_current_screen();

		if ( empty( $screen ) ) {
			return $params;
		}

		if ( ! $this->screen_matches( $screen ) ) {
			return $params;
		}

		$editor_lang = $this->get_language();

		if ( empty( $editor_lang ) ) {
			return $params;
		}

		$params['lang'] = $editor_lang->slug;
		return $params;
	}


	/**
	 * Tells whether the given screen is the Site edtitor or not.
	 *
	 * @since 3.7
	 *
	 * @param  WP_Screen $screen The current screen.
	 * @return bool True if Site editor screen, false otherwise.
	 */
	protected function screen_matches( WP_Screen $screen ): bool {
		return (
			'site-editor' === $screen->base
			&& $this->model->post_types->is_translated( 'wp_template_part' )
			&& $screen->is_block_editor()
		);
	}

	/**
	 * Returns the language to use in the Site editor.
	 *
	 * @since 3.7
	 *
	 * @return PLL_Language|null
	 */
	protected function get_language(): ?PLL_Language {
		if ( ! empty( $this->curlang ) && PLL_FSE_Tools::is_site_editor() ) {
			return $this->curlang;
		}

		return null;
	}

	/**
	 * Returns the screen name for the Site editor to use across all process.
	 *
	 * @since 3.7
	 *
	 * @return string
	 */
	protected function get_screen_name(): string {
		return 'site';
	}
}
