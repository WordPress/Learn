<?php
/**
 * WP-CLI command for exporting WPFTP Jetpack Forms feedback.
 *
 * Shares its query/CSV logic with the Tools admin page in feedback-logged-in-user-export.php.
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
	 * Produces the same response data Jetpack's own "Export CSV" does (every response field, ID, Date,
	 * Consent, IP Address, etc.), plus the status and the logged-in user (display name, username, ID)
	 * Jetpack's own export leaves out. See render_admin_page()/handle_admin_export_request() in
	 * feedback-logged-in-user-export.php for the equivalent Tools > WPFTP Feedback admin page, for
	 * exporting without shell access.
	 *
	 * The file holds usernames and IP addresses, so it is written with owner-only permissions and never
	 * into the WordPress install or its content directory, where it would be served publicly.
	 *
	 * ## OPTIONS
	 *
	 * --dir=<path>
	 * : Directory to save the CSV in. Must exist, be writable, and lie outside the web root.
	 *
	 * [--post=<id>]
	 * : Limit to responses submitted to a single form (its parent post ID, as shown in the Tools page
	 * picker). Defaults to all forms.
	 *
	 * [--status=<status>]
	 * : Comma-separated post statuses to include: publish, draft, spam, trash. Defaults to publish,draft,
	 * matching Jetpack's own default export.
	 *
	 * [--after=<date>]
	 * : Only include responses submitted on or after this date.
	 *
	 * [--before=<date>]
	 * : Only include responses submitted on or before this date.
	 *
	 * ## EXAMPLES
	 *
	 *     wp feedback export --dir=/tmp
	 *     wp feedback export --post=12345 --after=2026-09-01 --before=2026-09-30 --dir=/tmp
	 *
	 * @subcommand export
	 * @when after_wp_load
	 */
	public function export( $args, $assoc_args ) {
		if ( ! class_exists( Contact_Form_Plugin::class ) ) {
			\WP_CLI::error( 'Jetpack Forms is not active.' );
		}

		// A non-numeric ID would cast to 0, which means "all forms".
		$post = \WP_CLI\Utils\get_flag_value( $assoc_args, 'post', null );
		if ( null !== $post && ! ctype_digit( (string) $post ) ) {
			\WP_CLI::error( sprintf( '--post must be a numeric post ID, "%s" given.', $post ) );
		}
		$post = (int) $post;

		$dir = $this->get_output_dir( \WP_CLI\Utils\get_flag_value( $assoc_args, 'dir', '' ) );

		$status = \WP_CLI\Utils\get_flag_value( $assoc_args, 'status', 'publish,draft' );

		$rows = get_rows( array(
			'post'   => $post,
			'status' => explode( ',', (string) $status ),
			'after'  => \WP_CLI\Utils\get_flag_value( $assoc_args, 'after', '' ),
			'before' => \WP_CLI\Utils\get_flag_value( $assoc_args, 'before', '' ),
		) );

		if ( is_wp_error( $rows ) ) {
			\WP_CLI::error( $rows->get_error_message() );
		}

		if ( empty( $rows ) ) {
			// An empty window is a result, not a failure: exit 0 so scheduled runs can tell the two apart.
			\WP_CLI::warning( 'No feedback responses found for the given arguments. Nothing written.' );
			return;
		}

		$path = trailingslashit( $dir ) . get_filename( $post );

		// 'x' refuses to open an existing file, so nothing is ever overwritten.
		$handle = @fopen( $path, 'x' ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fopen, WordPress.PHP.NoSilencedErrors.Discouraged

		if ( ! $handle ) {
			\WP_CLI::error( sprintf( 'Could not create %s (does it already exist?).', $path ) );
		}

		chmod( $path, 0600 ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_chmod

		write_csv( $handle, $rows );
		fclose( $handle ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fclose

		\WP_CLI::success( sprintf( 'Exported %d response(s) to %s', count( $rows ), $path ) );
	}

	/**
	 * Resolve and vet the --dir argument.
	 *
	 * Exits with an error unless the directory exists, is writable, and is outside both the WordPress
	 * install and the content directory: the export has a predictable name and holds personal data, so
	 * anywhere web-served is off limits.
	 *
	 * @param string $dir Raw --dir value.
	 * @return string Absolute directory path.
	 */
	private function get_output_dir( $dir ) {
		$resolved = $dir ? realpath( $dir ) : false;

		if ( ! $resolved || ! is_dir( $resolved ) || ! wp_is_writable( $resolved ) ) {
			\WP_CLI::error( sprintf( '--dir must be an existing, writable directory; "%s" is not.', $dir ) );
		}

		$web_roots = array_filter( array( realpath( ABSPATH ), realpath( WP_CONTENT_DIR ) ) );

		foreach ( $web_roots as $web_root ) {
			if ( 0 === strpos( trailingslashit( $resolved ), trailingslashit( $web_root ) ) ) {
				\WP_CLI::error( sprintf( 'Refusing to write into %s: it is web-served. Choose a directory outside the site, such as /tmp.', $resolved ) );
			}
		}

		return $resolved;
	}
}

\WP_CLI::add_command( 'feedback', __NAMESPACE__ . '\Feedback_Export_CLI' );
