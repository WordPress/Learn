<?php
/**
 * File containing the class Sensei_Interactive_Blocks_Sensei_Home\Providers\Help.
 *
 * @package sensei-blocks-home
 * @since   1.8.0
 */

namespace Sensei_Interactive_Blocks_Sensei_Home\Providers;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class responsible for generating the Help structure for Sensei Home screen in Sensei Blocks.
 */
class Help {

	/**
	 * Return a list of categories which each contain multiple help items.
	 *
	 * @return array[]
	 */
	public function get(): array {
		return [
			$this->create_category(
				__( 'Get the most out of Sensei Blocks', 'sensei-pro' ),
				[

					$this->create_item( __( 'Sensei Blocks documentation', 'sensei-pro' ), 'https://senseilms.com/docs/' ),
					$this->create_item( __( 'Create a support ticket', 'sensei-pro' ), 'https://senseilms.com/contact/' ),
				]
			),
		];
	}

	/**
	 * Create category array structure.
	 *
	 * @param string  $title The category title.
	 * @param array[] $items The items in the category.
	 * @return array
	 */
	private function create_category( $title, $items ) {
		return [
			'title' => $title,
			'items' => $items,
		];
	}

	/**
	 * Create item array structure.
	 *
	 * @param string      $title The item title.
	 * @param string|null $url Optional. Action url.
	 *
	 * @return array
	 */
	private function create_item( string $title, ?string $url = null ) {
		return [
			'title'      => $title,
			'url'        => $url,
			'icon'       => null,
			'extra_link' => null,
		];
	}

}
