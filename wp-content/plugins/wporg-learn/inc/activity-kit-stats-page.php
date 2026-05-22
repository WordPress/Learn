<?php

namespace WPOrg_Learn\Activity_Kit_Stats;

defined( 'WPINC' ) || die();

/**
 * Actions and filters.
 */
add_action( 'admin_enqueue_scripts', __NAMESPACE__ . '\enqueue_scripts' );

/**
 * Enqueue Chart.js and the stats dashboard script on the stats page only.
 *
 * @param string $hook Page hook suffix.
 */
function enqueue_scripts( $hook ) {
	if ( 'activity_kit_page_activity-kit-stats' !== $hook ) {
		return;
	}

	wp_enqueue_script(
		'chartjs',
		'https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js',
		array(),
		'4.4.0',
		true
	);

	wp_enqueue_script(
		'activity-kit-stats',
		\WPOrg_Learn\PLUGIN_URL . 'js/activity-kit-stats/index.js',
		array( 'chartjs', 'wp-api-fetch' ),
		null,
		true
	);

	wp_localize_script(
		'activity-kit-stats',
		'activityKitStats',
		array(
			'restUrl' => rest_url( 'activity-kits/v1/stats' ),
			'nonce'   => wp_create_nonce( 'wp_rest' ),
		)
	);
}

/**
 * Render the Activity Kit Stats admin page.
 */
function render_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'You do not have permission to access this page.', 'wporg-learn' ) );
	}

	$kits = get_posts( array(
		'post_type'      => 'activity_kit',
		'posts_per_page' => -1,
		'post_status'    => array( 'publish', 'draft' ),
		'orderby'        => 'title',
		'order'          => 'ASC',
	) );

	?>
	<div class="wrap activity-kit-stats-page">
		<h1><?php esc_html_e( 'Activity Kit Stats', 'wporg-learn' ); ?></h1>

		<div class="activity-kit-stats-filters">
			<label for="ak-filter-metric"><?php esc_html_e( 'Metric', 'wporg-learn' ); ?></label>
			<select id="ak-filter-metric">
				<option value="both"><?php esc_html_e( 'Views & Downloads', 'wporg-learn' ); ?></option>
				<option value="views"><?php esc_html_e( 'Views only', 'wporg-learn' ); ?></option>
				<option value="downloads"><?php esc_html_e( 'Downloads only', 'wporg-learn' ); ?></option>
			</select>

			<label for="ak-filter-range"><?php esc_html_e( 'Time Range', 'wporg-learn' ); ?></label>
			<select id="ak-filter-range">
				<option value="all"><?php esc_html_e( 'All time', 'wporg-learn' ); ?></option>
				<option value="7d"><?php esc_html_e( 'Last 7 days', 'wporg-learn' ); ?></option>
				<option value="30d"><?php esc_html_e( 'Last 30 days', 'wporg-learn' ); ?></option>
				<option value="90d"><?php esc_html_e( 'Last 90 days', 'wporg-learn' ); ?></option>
			</select>

			<label for="ak-filter-kit"><?php esc_html_e( 'Activity Kit', 'wporg-learn' ); ?></label>
			<select id="ak-filter-kit">
				<option value=""><?php esc_html_e( 'All kits', 'wporg-learn' ); ?></option>
				<?php foreach ( $kits as $kit ) : ?>
					<option value="<?php echo esc_attr( $kit->post_name ); ?>">
						<?php echo esc_html( $kit->post_title ); ?>
					</option>
				<?php endforeach; ?>
			</select>
		</div>

		<div class="activity-kit-stats-chart-wrap">
			<canvas id="ak-stats-chart" height="100"></canvas>
		</div>

		<table class="wp-list-table widefat fixed striped activity-kit-stats-table">
			<thead>
				<tr>
					<th><?php esc_html_e( 'Activity Kit', 'wporg-learn' ); ?></th>
					<th><?php esc_html_e( 'Views', 'wporg-learn' ); ?></th>
					<th><?php esc_html_e( 'Downloads', 'wporg-learn' ); ?></th>
				</tr>
			</thead>
			<tbody id="ak-stats-table-body">
				<tr><td colspan="3"><?php esc_html_e( 'Loading…', 'wporg-learn' ); ?></td></tr>
			</tbody>
		</table>
	</div>
	<?php
}
