<?php
/**
 * WP-CLI command for exporting WPFTP Jetpack Forms feedback.
 *
 * Shares its query/CSV logic with the Tools admin page in feedback-logged-in-user-export.php, which that
 * file must be loaded before this one for.
 *
 * @package WPOrg_Learn
 */

namespace WPOrg_Learn\Feedback_Export;

use Automattic\Jetpack\Forms\ContactForm\Contact_Form_Plugin;

defined( 'WPINC' ) || die();

if ( ! defined( 'WP_CLI' ) || ! WP_CLI ) {
	return;
}

/**
 * WP-CLI commands for exporting Jetpack Forms feedback responses, including the logged-in user data that
 * Jetpack's own CSV export doesn't have.
 */
class Feedback_Export_CLI {

	/**
	 * Export Jetpack Forms feedback responses, with the logged-in user who submitted each one.
	 *
	 * Produces the same full export Jetpack's own "Export CSV" does (every response field, ID, Date,
	 * Consent, IP Address, etc.), plus the logged-in user (display name, username, ID) Jetpack's own
	 * export leaves out. See render_admin_page()/handle_admin_export_request() in
	 * feedback-logged-in-user-export.php for the equivalent Tools > WPFTP Feedback admin page, for
	 * exporting without shell access.
	 *
	 * ## OPTIONS
	 *
	 * [--post=<id>]
	 * : Limit to responses submitted to a single form (its parent post ID). Defaults to all forms.
	 *
	 * [--status=<status>]
	 * : Comma-separated post statuses to include. Defaults to publish,draft, matching Jetpack's own default export.
	 *
	 * [--after=<date>]
	 * : Only include responses submitted after this date.
	 *
	 * [--before=<date>]
	 * : Only include responses submitted before this date.
	 *
	 * [--dir=<path>]
	 * : Directory to save the CSV in. Defaults to the current working directory.
	 *
	 * ## EXAMPLES
	 *
	 *     wp feedback export
	 *     wp feedback export --post=12345 --dir=/tmp
	 *
	 * @subcommand export
	 * @when after_wp_load
	 */
	public function export( $args, $assoc_args ) {
		if ( ! class_exists( Contact_Form_Plugin::class ) ) {
			\WP_CLI::error( 'Jetpack Forms is not active.' );
		}

		$status = \WP_CLI\Utils\get_flag_value( $assoc_args, 'status', 'publish,draft' );

		$rows = get_rows( array(
			'post'   => (int) \WP_CLI\Utils\get_flag_value( $assoc_args, 'post', 0 ),
			'status' => array_map( 'trim', explode( ',', $status ) ),
			'after'  => \WP_CLI\Utils\get_flag_value( $assoc_args, 'after', '' ),
			'before' => \WP_CLI\Utils\get_flag_value( $assoc_args, 'before', '' ),
		) );

		if ( is_wp_error( $rows ) ) {
			\WP_CLI::error( $rows->get_error_message() );
		}

		if ( empty( $rows ) ) {
			\WP_CLI::error( 'No feedback responses found for the given arguments.' );
		}

		$dir = \WP_CLI\Utils\get_flag_value( $assoc_args, 'dir', getcwd() );

		$csv = build_csv( $rows );

		if ( $csv->error->has_errors() ) {
			\WP_CLI::error( $csv->error->get_error_message() );
		}

		$saved_to = $csv->save_file( $dir );

		if ( ! $saved_to ) {
			\WP_CLI::error( $csv->error->get_error_message() );
		}

		\WP_CLI::success( sprintf( 'Exported %d response(s) to %s', count( $rows ), $saved_to ) );
	}
}

\WP_CLI::add_command( 'feedback', __NAMESPACE__ . '\Feedback_Export_CLI' );
