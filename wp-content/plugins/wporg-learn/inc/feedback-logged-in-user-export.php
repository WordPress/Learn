<?php
/**
 * Export Jetpack Forms responses with the logged-in user who submitted each one.
 *
 * Jetpack's own "Export CSV" for Forms responses doesn't include the logged-in user, even though Jetpack
 * captures it internally (display name, username, user ID) at submission time. This adds a WP-CLI command
 * and a Tools admin page that produce the same full export Jetpack's own CSV does, plus that missing data,
 * joined by feedback post ID.
 *
 * @package WPOrg_Learn
 */

namespace WPOrg_Learn\Feedback_Export;

use Automattic\Jetpack\Forms\ContactForm\Contact_Form_Plugin;
use Automattic\Jetpack\Forms\ContactForm\Feedback;
use WP_Error;

defined( 'WPINC' ) || die();

add_action( 'admin_menu', __NAMESPACE__ . '\add_admin_page' );
add_action( 'admin_post_wporg_learn_export_feedback_logged_in_users', __NAMESPACE__ . '\handle_admin_export_request' );

/**
 * Query feedback responses and build one full export row per response.
 *
 * Each row has every field Jetpack's own CSV export has (ID, Date, Title, Source, the form's own fields,
 * Consent, IP Address, Country code, Browser) plus Logged-in username, Logged-in display name, and
 * Logged-in user ID (empty for guest submissions).
 *
 * @param array $args Optional 'post' (parent post ID, 0 for all forms), 'status' (array of post statuses,
 *                    defaults to publish/draft), 'after', and 'before' (date filters) keys.
 * @return array[]|WP_Error One associative row per non-test response — an empty array means no responses
 *                          matched the filters — or a WP_Error if the Jetpack Forms integration itself
 *                          broke (so that's never confused with a genuinely empty result).
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

	$query_args = array(
		'post_type'      => Feedback::POST_TYPE,
		'post_status'    => $args['status'],
		'posts_per_page' => -1,
		'fields'         => 'ids',
		'orderby'        => 'ID',
		'order'          => 'ASC',
	);

	if ( $args['post'] ) {
		$query_args['post_parent'] = (int) $args['post'];
	}

	if ( $args['after'] || $args['before'] ) {
		$query_args['date_query'] = array_filter( array(
			'after'  => $args['after'],
			'before' => $args['before'],
		) );
	}

	$feedback_ids = get_posts( $query_args );

	if ( empty( $feedback_ids ) ) {
		return array();
	}

	/*
	 * Same data Jetpack's own CSV export uses: column name (e.g. " ID", "Name", " Consent") => array of
	 * values, one per response, in the order Jetpack chose to include them (test responses excluded).
	 */
	$columns = Contact_Form_Plugin::init()->get_export_feedback_data( $feedback_ids, false );

	if ( empty( $columns ) ) {
		return new WP_Error( 'feedback_export_no_columns', __( 'Jetpack Forms did not return any export data for these responses.', 'wporg-learn' ) );
	}

	/*
	 * Jetpack prefixes its own meta columns with a space to avoid clashing with form field names; find
	 * that "ID" column (rather than assuming row order matches $feedback_ids) so each row's logged-in user
	 * data is joined by its real feedback ID, not by position.
	 */
	$id_column = null;
	foreach ( array_keys( $columns ) as $column_name ) {
		if ( 'ID' === trim( $column_name ) ) {
			$id_column = $column_name;
			break;
		}
	}

	if ( null === $id_column ) {
		return new WP_Error( 'feedback_export_no_id_column', __( 'Could not find the response ID column in Jetpack\'s export data.', 'wporg-learn' ) );
	}

	$rows = array();

	foreach ( $columns[ $id_column ] as $index => $feedback_id ) {
		$row = array();

		foreach ( $columns as $column_name => $values ) {
			$row[ $column_name ] = $values[ $index ] ?? '';
		}

		// Only fetched for responses Jetpack actually kept, and once each — not for every queried ID up front.
		$feedback       = Feedback::get( (int) $feedback_id );
		$logged_in_user = ( $feedback instanceof Feedback ) ? $feedback->get_logged_in_user() : null;

		$row['Logged-in username']     = $logged_in_user['username'] ?? '';
		$row['Logged-in display name'] = $logged_in_user['display_name'] ?? '';
		$row['Logged-in user ID']      = $logged_in_user['id'] ?? '';

		$rows[] = $row;
	}

	return $rows;
}

/**
 * Build an Export_CSV instance from a set of rows, ready to save or emit.
 *
 * @param array[] $rows Non-empty rows from get_rows().
 * @return \WordPressdotorg\MU_Plugins\Utilities\Export_CSV
 */
function build_csv( array $rows ) {
	return new \WordPressdotorg\MU_Plugins\Utilities\Export_CSV( array(
		'filename' => array( 'wpftp-feedback', gmdate( 'Y-m-d' ) ),
		'headers'  => array_keys( $rows[0] ),
		'data'     => $rows,
	) );
}

/**
 * Add a "WPFTP Feedback" page under Tools.
 */
function add_admin_page(): void {
	add_management_page(
		__( 'WordPress Facilitator Training Feedback', 'wporg-learn' ),
		__( 'WPFTP Feedback', 'wporg-learn' ),
		'export',
		'wporg-learn-feedback-logged-in-users',
		__NAMESPACE__ . '\render_admin_page'
	);
}

/**
 * Get the parent post IDs that have at least one feedback response, for the form picker.
 *
 * @return array Post ID => post title.
 */
function get_form_options(): array {
	$parent_ids = Contact_Form_Plugin::get_all_parent_post_ids( array(
		'post_status' => array( 'publish', 'draft' ),
	) );

	$options = array();
	foreach ( $parent_ids as $parent_id ) {
		if ( $parent_id ) {
			$options[ $parent_id ] = get_the_title( $parent_id );
		}
	}

	return $options;
}

/**
 * Render the Tools > WPFTP Feedback page.
 */
function render_admin_page(): void {
	if ( ! current_user_can( 'export' ) ) {
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
			<?php esc_html_e( 'Jetpack Forms\' own CSV export leaves out the logged-in user who submitted each response, even though Jetpack captures it. This downloads the full response export — every field Jetpack\'s own export has, plus the logged-in user.', 'wporg-learn' ); ?>
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
	if ( ! current_user_can( 'export' ) ) {
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

	$rows = get_rows( array(
		'post'   => isset( $_POST['post'] ) ? (int) $_POST['post'] : 0,
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

	build_csv( $rows )->emit_file();
}
