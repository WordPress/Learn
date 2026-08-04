<?php
/**
 * Settings: Activity Kit feedback form URL.
 *
 * Registers a site option for the global feedback form URL, editable
 * via the WordPress admin under Activity Kits → Settings.
 *
 * @package WPOrg_Learn
 */

namespace WPOrg_Learn\Activity_Kit_Settings;

defined( 'WPINC' ) || die();

add_action( 'admin_init', __NAMESPACE__ . '\register_settings' );
add_action( 'admin_menu', __NAMESPACE__ . '\add_settings_page' );

/**
 * Register the site option with the Settings API.
 */
function register_settings(): void {
	register_setting(
		'activity_kit_settings',
		'activity_kit_feedback_url',
		array(
			'type'              => 'string',
			'sanitize_callback' => 'esc_url_raw',
			'default'           => '',
		)
	);
}

/**
 * Add a Settings submenu under the Activity Kits post-type menu.
 */
function add_settings_page(): void {
	add_submenu_page(
		'edit.php?post_type=activity_kit',
		__( 'Activity Kit Settings', 'wporg-learn' ),
		__( 'Settings', 'wporg-learn' ),
		'manage_options',
		'activity-kit-settings',
		__NAMESPACE__ . '\render_settings_page'
	);
}

/**
 * Render the settings page.
 */
function render_settings_page(): void {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Activity Kit Settings', 'wporg-learn' ); ?></h1>
		<form method="post" action="options.php">
			<?php
			settings_fields( 'activity_kit_settings' );
			do_settings_sections( 'activity_kit_settings' );
			?>
			<table class="form-table" role="presentation">
				<tr>
					<th scope="row">
						<label for="activity_kit_feedback_url">
							<?php esc_html_e( 'Feedback Form URL', 'wporg-learn' ); ?>
						</label>
					</th>
					<td>
						<input
							type="url"
							id="activity_kit_feedback_url"
							name="activity_kit_feedback_url"
							value="<?php echo esc_attr( get_option( 'activity_kit_feedback_url', '' ) ); ?>"
							class="regular-text"
							placeholder="https://..."
						/>
						<p class="description">
							<?php esc_html_e( 'The URL of the feedback form linked from every activity kit detail page. The kit slug is appended automatically as a ?kit= parameter. Leave blank to hide the feedback strip.', 'wporg-learn' ); ?>
						</p>
					</td>
				</tr>
			</table>
			<?php submit_button(); ?>
		</form>
	</div>
	<?php
}
