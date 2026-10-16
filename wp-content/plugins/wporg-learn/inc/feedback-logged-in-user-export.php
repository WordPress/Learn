<?php
/**
 * Export Jetpack Forms responses with the logged-in user who submitted each one.
 *
 * Jetpack's own "Export CSV" for Forms responses doesn't include the logged-in user, even though Jetpack
 * captures it internally (display name, username, user ID) at submission time. This adds a WP-CLI command
 * and a Tools admin page that produce the same response data Jetpack's own CSV does, plus that missing data,
 * joined by feedback post ID. Values are written verbatim, with only Jetpack's own guard against formula
 * injection, so free-text answers survive the export unchanged.
 *
 * @package WPOrg_Learn
 */

namespace WPOrg_Learn\Feedback_Export;

use Automattic\Jetpack\Forms\ContactForm\Contact_Form_Plugin;
use Automattic\Jetpack\Forms\ContactForm\Feedback;
use WP_Error;

defined( 'WPINC' ) || die();

/**
 * Capability required to export responses.
 *
 * The export places a WordPress.org username next to an IP address, so it hangs off its own capability rather
 * than core's general-purpose `export`. Administrators are granted it in capabilities.php; it can be added to
 * other roles explicitly.
 */
const EXPORT_CAPABILITY = 'export_feedback_responses';

/**
 * Post statuses a feedback response can have.
 *
 * Anything else is rejected before it reaches WP_Query, which silently drops the status clause altogether when
 * none of the requested statuses is registered, and would export every row on the site.
 */
const ALLOWED_STATUSES = array( 'publish', 'draft', 'spam', 'trash' );

/**
 * Responses loaded per batch, so a large export does not hold every parsed Feedback object at once.
 */
const BATCH_SIZE = 200;

add_action( 'admin_menu', __NAMESPACE__ . '\add_admin_page' );
add_action( 'admin_post_wporg_learn_export_feedback_logged_in_users', __NAMESPACE__ . '\handle_admin_export_request' );

/**
 * Query feedback responses and build one full export row per response.
 *
 * Each row has every field Jetpack's own CSV export has (ID, Date, Title, Source, the form's own fields,
 * Consent, IP Address, Country code, Browser) plus Status, Logged-in username, Logged-in display name,
 * Logged-in user ID (empty for guest submissions) and Logged-in user recorded (No for responses stored
 * before Jetpack began capturing the submitter's account). The added columns carry the same leading space
 * Jetpack uses for its own meta columns, so they can never collide with a form field label.
 *
 * @param array $args Optional 'post' (parent post ID, 0 for all forms), 'status' (array of post statuses,
 *                    defaults to publish/draft), 'after', and 'before' (date filters, inclusive) keys.
 * @return array[]|WP_Error One associative row per non-test response - an empty array means no responses
 *                          matched the filters - or a WP_Error if the arguments are invalid or the Jetpack
 *                          Forms integration itself broke.
 */
function get_rows( array $args ) {
	if ( ! class_exists( Contact_Form_Plugin::class ) || ! class_exists( Feedback::class ) ) {
		return new WP_Error( 'feedback_export_missing_plugin', __( 'Jetpack Forms is not active.', 'wporg-learn' ) );
	}

	$args = wp_parse_args( $args, array(
		'post'   => 0,
		'status' => array( 'publish', 'draft' ),
		'after'  => '',
		'before' => '',
	) );

	$status = array_values( array_filter( array_map( 'trim', (array) $args['status'] ) ) );

	if ( empty( $status ) || array_diff( $status, ALLOWED_STATUSES ) ) {
		return new WP_Error(
			'feedback_export_invalid_status',
			sprintf(
				/* translators: %s: comma-separated list of allowed statuses. */
				__( 'Status must be one or more of: %s.', 'wporg-learn' ),
				implode( ', ', ALLOWED_STATUSES )
			)
		);
	}

	// An unparseable bound would otherwise become 1970-01-01 inside WP_Date_Query and match every row.
	foreach ( array( 'after', 'before' ) as $bound ) {
		if ( '' !== $args[ $bound ] && false === strtotime( $args[ $bound ] ) ) {
			return new WP_Error(
				'feedback_export_invalid_date',
				sprintf(
					/* translators: 1: "after" or "before", 2: the value that could not be parsed. */
					__( 'Could not parse the "%1$s" date: %2$s', 'wporg-learn' ),
					$bound,
					$args[ $bound ]
				)
			);
		}
	}

	$query_args = array(
		'post_type'      => Feedback::POST_TYPE,
		'post_status'    => $status,
		'posts_per_page' => -1,
		'fields'         => 'ids',
		'orderby'        => 'ID',
		'order'          => 'ASC',
	);

	if ( $args['post'] ) {
		$query_args['post_parent'] = (int) $args['post'];
	}

	if ( $args['after'] || $args['before'] ) {
		// Inclusive, so a bare Y-m-d bound covers the whole day rather than stopping at its midnight.
		$query_args['date_query'] = array_filter( array(
			'after'     => $args['after'],
			'before'    => $args['before'],
			'inclusive' => true,
		) );
	}

	$feedback_ids = get_posts( $query_args );

	if ( empty( $feedback_ids ) ) {
		return array();
	}

	$plugin    = Contact_Form_Plugin::init();
	$id_column = ' ' . __( 'ID', 'jetpack-forms' ); // phpcs:ignore WordPress.WP.I18n.TextDomainMismatch -- must match the key Jetpack builds for its own column.
	$rows      = array();

	foreach ( array_chunk( $feedback_ids, BATCH_SIZE ) as $batch ) {
		// One query for the batch instead of one per response inside Jetpack.
		_prime_post_caches( $batch, false, false );

		/*
		 * Same data Jetpack's own CSV export uses: column name (e.g. " ID", "Name", " Consent") => array of
		 * values, one per response, in the order Jetpack chose to include them (test responses excluded).
		 * Empty when every response in the batch was a form-preview test or no longer exists, which is a
		 * legitimately empty result, not a failure.
		 */
		$columns = $plugin->get_export_feedback_data( $batch, false );

		if ( empty( $columns ) ) {
			continue;
		}

		/*
		 * Jetpack prefixes its own meta columns with a space to avoid clashing with form field names, and
		 * always emits the ID column first; fall back to that position if the translated key is not found.
		 */
		if ( ! isset( $columns[ $id_column ] ) ) {
			$id_column = array_key_first( $columns );
		}

		foreach ( $columns[ $id_column ] as $index => $feedback_id ) {
			$row = array();

			foreach ( $columns as $column_name => $values ) {
				$row[ $column_name ] = $values[ $index ] ?? '';
			}

			// A cache hit: Jetpack already built this object while assembling the columns.
			$feedback       = Feedback::get( (int) $feedback_id );
			$logged_in_user = ( $feedback instanceof Feedback ) ? $feedback->get_logged_in_user() : null;

			$row[' Status']                  = ( $feedback instanceof Feedback ) ? $feedback->get_status() : '';
			$row[' Logged-in username']      = $logged_in_user['username'] ?? '';
			$row[' Logged-in display name']  = $logged_in_user['display_name'] ?? '';
			$row[' Logged-in user ID']       = $logged_in_user['id'] ?? '';
			$row[' Logged-in user recorded'] = logged_in_user_was_recorded( (int) $feedback_id ) ? 'Yes' : 'No';

			$rows[] = $row;
		}

		// Release the parsed Feedback objects before loading the next batch.
		if ( method_exists( Feedback::class, 'clear_cache' ) ) {
			Feedback::clear_cache();
		}
	}

	return $rows;
}

/**
 * Whether Jetpack recorded the submitter's account for a response at all.
 *
 * Jetpack Forms only began capturing the logged-in user in its forms package 7.14.0 (March 2026). Responses
 * stored before that have no `logged_in_user` key in their content, so an empty username on its own cannot
 * tell "not captured" from "not logged in". Jetpack stores a null under that key for guests, so the key's
 * presence is the signal. This reads the stored JSON directly because Feedback exposes only the value.
 *
 * @param int $feedback_id Feedback post ID.
 * @return bool
 */
function logged_in_user_was_recorded( int $feedback_id ): bool {
	$post = get_post( $feedback_id );

	if ( ! $post ) {
		return false;
	}

	$decoded = json_decode( $post->post_content, true );

	if ( ! is_array( $decoded ) ) {
		// Content may be slash-escaped as stored by WordPress; Jetpack's own parser retries the same way.
		$decoded = json_decode( stripslashes( trim( $post->post_content ) ), true );
	}

	return is_array( $decoded ) && array_key_exists( 'logged_in_user', $decoded );
}

/**
 * Column headers for a set of rows: the union of every row's keys, in first-seen order.
 *
 * Rows from different forms, or different batches, can carry different field columns.
 *
 * @param array[] $rows Rows from get_rows().
 * @return string[]
 */
function get_headers( array $rows ): array {
	$headers = array();

	foreach ( $rows as $row ) {
		$headers += array_fill_keys( array_keys( $row ), true );
	}

	return array_keys( $headers );
}

/**
 * Write rows to an open handle as CSV.
 *
 * Headers and values go out verbatim, apart from Jetpack's own `esc_csv()` guard against spreadsheet formula
 * injection, which only touches a cell's first character. This deliberately avoids the shared wporg
 * `Export_CSV` utility, whose per-cell `sanitize_text_field()` flattens paragraph breaks, encodes `<` and
 * strips percent sequences, and whose escaping inserts apostrophes into ordinary prose.
 *
 * @param resource $handle Writable stream.
 * @param array[]  $rows   Non-empty rows from get_rows().
 */
function write_csv( $handle, array $rows ): void {
	$headers = get_headers( $rows );
	$plugin  = Contact_Form_Plugin::init();

	fputcsv( $handle, $headers, ',', '"', '' );

	foreach ( $rows as $row ) {
		$line = array();

		foreach ( $headers as $header ) {
			$line[] = $plugin->esc_csv( (string) ( $row[ $header ] ?? '' ) );
		}

		fputcsv( $handle, $line, ',', '"', '' );
	}
}

/**
 * File name for an export: names the form (or all forms) and the time, so exports never overwrite each other.
 *
 * @param int $post Parent post ID the export was limited to, 0 for all forms.
 * @return string
 */
function get_filename( int $post ): string {
	return sanitize_file_name(
		sprintf( 'wpftp-feedback_%s_%s.csv', $post ? 'form-' . $post : 'all-forms', gmdate( 'Y-m-d-His' ) )
	);
}

/**
 * Add a "WPFTP Feedback" page under Tools.
 */
function add_admin_page(): void {
	add_management_page(
		__( 'WordPress Facilitator Training Feedback', 'wporg-learn' ),
		__( 'WPFTP Feedback', 'wporg-learn' ),
		EXPORT_CAPABILITY,
		'wporg-learn-feedback-logged-in-users',
		__NAMESPACE__ . '\render_admin_page'
	);
}

/**
 * Get the parent post IDs that have at least one feedback response, for the form picker.
 *
 * Covers every status the export can include, so a form whose responses were all marked spam can still be
 * selected. Every label carries the post ID, which is also the value `wp feedback export --post` takes.
 *
 * @return array Post ID => label.
 */
function get_form_options(): array {
	$parent_ids = array_filter( array_map( 'intval', Contact_Form_Plugin::get_all_parent_post_ids( array(
		'post_status' => ALLOWED_STATUSES,
	) ) ) );

	if ( empty( $parent_ids ) ) {
		return array();
	}

	// One query for every parent, whatever its post type, instead of one per option.
	_prime_post_caches( $parent_ids, false, false );

	$options = array();

	foreach ( $parent_ids as $parent_id ) {
		$parent = get_post( $parent_id );

		if ( ! $parent ) {
			continue;
		}

		// Block-editor forms are often stored as untitled jetpack_form posts.
		$title = trim( get_the_title( $parent ) );
		if ( '' === $title ) {
			$title = sprintf( '(%s)', $parent->post_type );
		}

		$options[ $parent_id ] = sprintf( '%s #%d', $title, $parent_id );
	}

	natcasesort( $options );

	return $options;
}

/**
 * Render the Tools > WPFTP Feedback page.
 */
function render_admin_page(): void {
	if ( ! current_user_can( EXPORT_CAPABILITY ) ) {
		wp_die( esc_html__( 'You do not have permission to export this data.', 'wporg-learn' ) );
	}

	if ( ! class_exists( Feedback::class ) || ! class_exists( Contact_Form_Plugin::class ) ) {
		echo '<div class="wrap"><p>' . esc_html__( 'Jetpack Forms is not active.', 'wporg-learn' ) . '</p></div>';
		return;
	}

	if ( isset( $_GET['wporg_learn_feedback_export'] ) && 'empty' === $_GET['wporg_learn_feedback_export'] ) {
		echo '<div class="notice notice-warning"><p>' . esc_html__( 'No feedback responses found for the selected filters.', 'wporg-learn' ) . '</p></div>';
	}
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'WordPress Facilitator Training Feedback', 'wporg-learn' ); ?></h1>
		<p>
			<?php esc_html_e( 'Jetpack Forms\' own CSV export leaves out the logged-in user who submitted each response, even though Jetpack captures it. This downloads the full response export - every field Jetpack\'s own export has, plus the status and the logged-in user. Both dates are inclusive.', 'wporg-learn' ); ?>
		</p>
		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
			<?php wp_nonce_field( 'wporg_learn_export_feedback_logged_in_users' ); ?>
			<input type="hidden" name="action" value="wporg_learn_export_feedback_logged_in_users" />
			<table class="form-table" role="presentation">
				<tr>
					<th scope="row">
						<label for="wporg-learn-feedback-export-post"><?php esc_html_e( 'Form', 'wporg-learn' ); ?></label>
					</th>
					<td>
						<select id="wporg-learn-feedback-export-post" name="post">
							<option value="0"><?php esc_html_e( 'All forms', 'wporg-learn' ); ?></option>
							<?php foreach ( get_form_options() as $post_id => $post_title ) : ?>
								<option value="<?php echo esc_attr( $post_id ); ?>"><?php echo esc_html( $post_title ); ?></option>
							<?php endforeach; ?>
						</select>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Statuses', 'wporg-learn' ); ?></th>
					<td>
						<label>
							<input type="checkbox" name="include_spam" value="1" />
							<?php esc_html_e( 'Include spam', 'wporg-learn' ); ?>
						</label>
						<br />
						<label>
							<input type="checkbox" name="include_trash" value="1" />
							<?php esc_html_e( 'Include trash', 'wporg-learn' ); ?>
						</label>
						<p class="description"><?php esc_html_e( 'The Status column in the export tells the rows apart.', 'wporg-learn' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row">
						<label for="wporg-learn-feedback-export-after"><?php esc_html_e( 'Date range', 'wporg-learn' ); ?></label>
					</th>
					<td>
						<input type="date" id="wporg-learn-feedback-export-after" name="after" />
						<?php esc_html_e( 'to', 'wporg-learn' ); ?>
						<label for="wporg-learn-feedback-export-before" class="screen-reader-text"><?php esc_html_e( 'End date', 'wporg-learn' ); ?></label>
						<input type="date" id="wporg-learn-feedback-export-before" name="before" />
					</td>
				</tr>
			</table>
			<?php submit_button( __( 'Download CSV', 'wporg-learn' ) ); ?>
		</form>
	</div>
	<?php
}

/**
 * Handle the admin-post submission and stream the CSV back to the browser.
 */
function handle_admin_export_request(): void {
	if ( ! current_user_can( EXPORT_CAPABILITY ) ) {
		wp_die( esc_html__( 'You do not have permission to export this data.', 'wporg-learn' ), '', array( 'response' => 403 ) );
	}

	check_admin_referer( 'wporg_learn_export_feedback_logged_in_users' );

	$status = array( 'publish', 'draft' );
	if ( ! empty( $_POST['include_spam'] ) ) {
		$status[] = 'spam';
	}
	if ( ! empty( $_POST['include_trash'] ) ) {
		$status[] = 'trash';
	}

	$post = isset( $_POST['post'] ) ? absint( $_POST['post'] ) : 0;

	$rows = get_rows( array(
		'post'   => $post,
		'status' => $status,
		'after'  => isset( $_POST['after'] ) ? sanitize_text_field( wp_unslash( $_POST['after'] ) ) : '',
		'before' => isset( $_POST['before'] ) ? sanitize_text_field( wp_unslash( $_POST['before'] ) ) : '',
	) );

	if ( is_wp_error( $rows ) ) {
		wp_die( esc_html( $rows->get_error_message() ) );
	}

	if ( empty( $rows ) ) {
		// Fall back to the Tools page itself if there's no referer to bounce back to (e.g. stripped by the browser).
		$redirect_to = wp_get_referer() ?: admin_url( 'tools.php?page=wporg-learn-feedback-logged-in-users' );
		wp_safe_redirect( add_query_arg( 'wporg_learn_feedback_export', 'empty', $redirect_to ) );
		exit;
	}

	nocache_headers();
	header( 'Content-Type: text/csv; charset=utf-8' );
	header( sprintf( 'Content-Disposition: attachment; filename="%s"', get_filename( $post ) ) );

	$output = fopen( 'php://output', 'w' ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fopen
	write_csv( $output, $rows );
	fclose( $output ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fclose

	exit;
}
