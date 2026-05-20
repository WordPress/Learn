<?php
/**
 * @package Polylang Pro
 */

namespace WP_Syntex\Polylang_Pro\Modules\CLI;

use WP_CLI\Formatter as WP_CLI_Formatter;

/**
 * Adapts the WP CLI formatter to handle booleans correctly.
 *
 * @since 3.8
 */
class Formatter {

	/**
	 * Output format arguments.
	 *
	 * @var array
	 */
	private $assoc_args;

	/**
	 * WP CLI formatter.
	 *
	 * @var WP_CLI_Formatter
	 */
	private $wp_cli_formatter;

	/**
	 * Constructor.
	 *
	 * @since 3.8
	 *
	 * @param array $assoc_args Output format arguments.
	 * @param array $fields     Fields to display of each item.
	 */
	public function __construct( $assoc_args, $fields = null ) {
		$this->assoc_args       = $assoc_args;
		$this->wp_cli_formatter = new WP_CLI_Formatter( $assoc_args, $fields );
	}

	/**
	 * Displays multiple items according to the output arguments.
	 * This displays true/false strings when using formats that don't display booleans nicely (table, csv).
	 *
	 * @since 3.8
	 *
	 * @param array $items The items to display.
	 * @return void
	 */
	public function display_items( $items ) {
		if ( ! empty( $this->assoc_args['format'] ) && in_array( $this->assoc_args['format'], array( 'json', 'yaml' ), true ) ) {
			$this->wp_cli_formatter->display_items( $items );
			return;
		}

		foreach ( $items as &$item ) {
			foreach ( $item as &$prop ) {
				if ( is_bool( $prop ) ) {
					$prop = $prop ? 'true' : 'false';
				}
			}
		}

		$this->wp_cli_formatter->display_items( $items );
	}
}
