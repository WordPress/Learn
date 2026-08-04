<?php
/**
 * Admin page for Activity Kit stats, backed by Jetpack Stats.
 *
 * @package WPOrg_Learn
 */

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

	$script_asset_path = \WPOrg_Learn\get_build_path() . 'activity-kit-stats.asset.php';
	if ( ! is_readable( $script_asset_path ) ) {
		return;
	}
	$script_asset = require $script_asset_path; // phpcs:ignore WordPressVIPMinimum.Files.IncludingFile.UsingVariable

	wp_enqueue_script(
		'activity-kit-stats',
		\WPOrg_Learn\get_build_url() . 'activity-kit-stats.js',
		$script_asset['dependencies'],
		$script_asset['version'],
		true
	);

	wp_localize_script(
		'activity-kit-stats',
		'activityKitStats',
		array(
			'restUrl'          => rest_url( 'activity-kits/v1/stats' ),
			'nonce'            => wp_create_nonce( 'wp_rest' ),
			'jetpackAvailable' => class_exists( '\Automattic\Jetpack\Stats\WPCOM_Stats' ),
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

	$kits = get_posts(
		array(
			'post_type'      => 'activity_kit',
			'posts_per_page' => -1,
			'post_status'    => 'publish',
			'orderby'        => 'title',
			'order'          => 'ASC',
		)
	);

	?>
	<style>
		.ak-stats-filter-bar {
			background: #fff; border: 1px solid #c3c4c7;
			padding: 12px 16px; margin-bottom: 20px;
			display: flex; align-items: center; flex-wrap: wrap; gap: 20px;
		}
		.ak-filter-group { display: flex; align-items: center; gap: 8px; }
		.ak-filter-group label { font-size: 12px; font-weight: 600; color: #646970; white-space: nowrap; }
		.ak-metric-toggle, .ak-time-tabs {
			display: flex; border: 1px solid #c3c4c7; border-radius: 3px; overflow: hidden;
		}
		.ak-metric-toggle button, .ak-time-tabs button {
			background: #fff; border: none; border-right: 1px solid #c3c4c7;
			padding: 5px 12px; font-size: 12px; font-family: inherit;
			color: #646970; cursor: pointer; transition: background .1s, color .1s; white-space: nowrap;
		}
		.ak-metric-toggle button:last-child, .ak-time-tabs button:last-child { border-right: none; }
		.ak-metric-toggle button:hover, .ak-time-tabs button:hover { background: #f6f7f7; color: #1d2327; }
		.ak-metric-toggle button.is-active, .ak-time-tabs button.is-active { background: #3858e9; color: #fff; }
		.ak-kit-select {
			border: 1px solid #c3c4c7; border-radius: 3px; padding: 5px 8px;
			font-size: 12px; color: #1d2327; background: #fff; min-width: 180px;
		}
		.ak-back-link-bar { display: none; margin-bottom: 12px; font-size: 13px; }
		.ak-back-link-bar a { color: #3858e9; text-decoration: none; }
		.ak-back-link-bar a:hover { text-decoration: underline; }
		.ak-back-link-bar.is-visible { display: block; }
		.ak-kit-banner {
			display: none; background: #eef0fd; border: 1px solid #9fb1ff;
			border-left: 4px solid #3858e9; padding: 10px 16px; margin-bottom: 16px;
			font-size: 13px; color: #1d2327;
		}
		.ak-kit-banner.is-visible { display: flex; align-items: center; gap: 12px; }
		.ak-kit-banner a { color: #3858e9; text-decoration: none; margin-left: auto; font-size: 12px; }
		.ak-kit-banner a:hover { text-decoration: underline; }
		.ak-summary-strip { display: flex; gap: 0; margin-bottom: 20px; }
		.ak-summary-box {
			flex: 1; border: 1px solid #c3c4c7; padding: 16px 20px;
			background: #fff; margin-right: -1px;
		}
		.ak-summary-box:last-child { margin-right: 0; }
		.ak-summary-box .ak-stat-number { font-size: 28px; font-weight: 600; line-height: 1.2; display: block; }
		.ak-summary-box .ak-stat-label { font-size: 12px; color: #646970; text-transform: uppercase; letter-spacing: .04em; margin-top: 4px; display: block; }
		.ak-summary-box.is-views .ak-stat-number { color: #3858e9; }
		.ak-summary-box.is-downloads .ak-stat-number { color: #9fb1ff; }
		.ak-summary-box.is-rate .ak-stat-number { color: #1d2327; }
		.ak-postbox { background: #fff; border: 1px solid #c3c4c7; margin-bottom: 20px; }
		.ak-postbox-header {
			display: flex; align-items: center; border-bottom: 1px solid #c3c4c7;
			padding: 0 12px; min-height: 42px;
		}
		.ak-postbox-header h2 { font-size: 14px; font-weight: 600; color: #1d2327; margin: 0; }
		.ak-postbox-header .ak-subtitle { margin-left: auto; font-size: 12px; color: #646970; }
		.ak-postbox-inside { padding: 16px; }
		.ak-chart-legend { display: flex; gap: 20px; margin-bottom: 12px; font-size: 12px; color: #646970; }
		.ak-legend-item { display: flex; align-items: center; gap: 6px; }
		.ak-legend-swatch { width: 12px; height: 12px; border-radius: 2px; flex-shrink: 0; }
		.ak-chart-wrap { position: relative; height: 300px; }
		.ak-stats-table { width: 100%; border-collapse: collapse; font-size: 13px; }
		.ak-stats-table thead tr { background: #f6f7f7; }
		.ak-stats-table thead th {
			padding: 8px 12px; text-align: left; font-weight: 600; color: #1d2327;
			border-bottom: 1px solid #c3c4c7; white-space: nowrap; cursor: pointer; user-select: none;
		}
		.ak-stats-table thead th:hover { color: #3858e9; }
		.ak-stats-table thead th.is-sorted { color: #3858e9; }
		.ak-stats-table thead th .ak-sort-arrow { display: inline-block; margin-left: 4px; font-size: 11px; opacity: .8; }
		.ak-stats-table tbody tr:nth-child(even) { background: #f9f9f9; }
		.ak-stats-table tbody tr:hover { background: #f6f7f7; cursor: pointer; }
		.ak-stats-table tbody tr.is-selected { background: #f0f0f1 !important; }
		.ak-stats-table tbody td { padding: 8px 12px; border-bottom: 1px solid #f0f0f1; color: #1d2327; vertical-align: middle; }
		.ak-stats-table tbody tr:last-child td { border-bottom: none; }
		.ak-stats-table tbody td a { color: #3858e9; text-decoration: none; }
		.ak-stats-table tbody td a:hover { text-decoration: underline; }
		.ak-col-number { text-align: right; font-variant-numeric: tabular-nums; }
		.ak-stats-table thead th.ak-col-number { text-align: right; }
		.ak-hidden-col { display: none !important; }
		.page-title-row { display: flex; align-items: center; justify-content: space-between; margin-bottom: 16px; }
		.ak-chart-slider-wrap {
			display: none; margin-top: 12px; padding: 0 4px;
		}
		.ak-chart-slider-wrap.is-visible { display: block; }
		.ak-chart-slider-row { display: flex; align-items: center; gap: 10px; }
		.ak-chart-slider-row input[type="range"] { flex: 1; cursor: pointer; }
		.ak-chart-slider-label { font-size: 11px; color: #646970; white-space: nowrap; min-width: 80px; text-align: right; }
	</style>

	<div class="wrap">
		<div class="page-title-row">
			<div>
				<h1 class="wp-heading-inline"><?php esc_html_e( 'Activity Kit Stats', 'wporg-learn' ); ?></h1>
			</div>
			<a href="#" class="page-title-action" id="ak-export-csv"><?php esc_html_e( 'Export CSV', 'wporg-learn' ); ?></a>
		</div>
		<div class="wp-header-end"></div>

		<!-- Filter bar -->
		<div class="ak-stats-filter-bar">
			<div class="ak-filter-group">
				<label><?php esc_html_e( 'Metric', 'wporg-learn' ); ?></label>
				<div class="ak-metric-toggle" role="group">
					<button type="button" class="is-active" data-ak-metric="both"><?php esc_html_e( 'Both', 'wporg-learn' ); ?></button>
					<button type="button" data-ak-metric="views"><?php esc_html_e( 'Views only', 'wporg-learn' ); ?></button>
					<button type="button" data-ak-metric="downloads"><?php esc_html_e( 'Downloads only', 'wporg-learn' ); ?></button>
				</div>
			</div>

			<div class="ak-filter-group">
				<label><?php esc_html_e( 'Time range', 'wporg-learn' ); ?></label>
				<div class="ak-time-tabs" role="group">
					<button type="button" data-ak-range="7d"><?php esc_html_e( 'Last 7 days', 'wporg-learn' ); ?></button>
					<button type="button" data-ak-range="30d"><?php esc_html_e( 'Last 30 days', 'wporg-learn' ); ?></button>
					<button type="button" data-ak-range="90d"><?php esc_html_e( 'Last 90 days', 'wporg-learn' ); ?></button>
					<button type="button" class="is-active" data-ak-range="all"><?php esc_html_e( 'All time', 'wporg-learn' ); ?></button>
				</div>
			</div>

			<div class="ak-filter-group">
				<label for="ak-filter-kit"><?php esc_html_e( 'Kit', 'wporg-learn' ); ?></label>
				<select class="ak-kit-select" id="ak-filter-kit">
					<option value=""><?php esc_html_e( 'All kits', 'wporg-learn' ); ?></option>
					<?php foreach ( $kits as $kit ) : ?>
						<option value="<?php echo esc_attr( $kit->post_name ); ?>">
							<?php echo esc_html( $kit->post_title ); ?>
						</option>
					<?php endforeach; ?>
				</select>
			</div>
		</div>

		<!-- Back link (shown when a single kit is selected) -->
		<div class="ak-back-link-bar" id="ak-back-link-bar">
			<a href="#" id="ak-back-link">← <?php esc_html_e( 'Back to all kits', 'wporg-learn' ); ?></a>
		</div>

		<!-- Kit detail banner -->
		<div class="ak-kit-banner" id="ak-kit-banner">
			<span><?php esc_html_e( 'Showing stats for:', 'wporg-learn' ); ?> <strong id="ak-kit-banner-name"></strong></span>
			<a href="#" id="ak-kit-banner-back"><?php esc_html_e( 'View all kits →', 'wporg-learn' ); ?></a>
		</div>

		<!-- Summary strip -->
		<div class="ak-summary-strip" id="ak-summary-strip">
			<div class="ak-summary-box" id="ak-box-kits">
				<span class="ak-stat-number" id="ak-total-kits"><?php echo count( $kits ); ?></span>
				<span class="ak-stat-label"><?php esc_html_e( 'Total Kits', 'wporg-learn' ); ?></span>
			</div>
			<div class="ak-summary-box is-views" id="ak-box-views">
				<span class="ak-stat-number" id="ak-summary-views">—</span>
				<span class="ak-stat-label"><?php esc_html_e( 'Total Views', 'wporg-learn' ); ?></span>
			</div>
			<div class="ak-summary-box is-downloads" id="ak-box-downloads">
				<span class="ak-stat-number" id="ak-summary-downloads">—</span>
				<span class="ak-stat-label"><?php esc_html_e( 'Total Downloads', 'wporg-learn' ); ?></span>
			</div>
			<div class="ak-summary-box is-rate" id="ak-box-rate" style="display:none">
				<span class="ak-stat-number" id="ak-summary-rate">—</span>
				<span class="ak-stat-label"><?php esc_html_e( 'Download Rate', 'wporg-learn' ); ?></span>
			</div>
		</div>

		<!-- Chart postbox -->
		<div class="ak-postbox">
			<div class="ak-postbox-header">
				<h2 id="ak-chart-title"><?php esc_html_e( 'Views vs. Downloads — All Kits', 'wporg-learn' ); ?></h2>
				<span class="ak-subtitle" id="ak-chart-subtitle"><?php esc_html_e( 'All time', 'wporg-learn' ); ?></span>
			</div>
			<div class="ak-postbox-inside">
				<div class="ak-chart-legend" id="ak-chart-legend">
					<div class="ak-legend-item" id="ak-legend-views">
						<div class="ak-legend-swatch" style="background:#3858e9;"></div>
						<span><?php esc_html_e( 'Views', 'wporg-learn' ); ?></span>
					</div>
					<div class="ak-legend-item" id="ak-legend-downloads">
						<div class="ak-legend-swatch" style="background:#9fb1ff;"></div>
						<span><?php esc_html_e( 'Downloads', 'wporg-learn' ); ?></span>
					</div>
				</div>
				<div class="ak-chart-wrap">
					<canvas id="ak-stats-chart"></canvas>
				</div>
				<div class="ak-chart-slider-wrap" id="ak-chart-slider-wrap">
					<div class="ak-chart-slider-row">
						<input type="range" id="ak-chart-slider" min="0" step="1" value="0" />
						<span class="ak-chart-slider-label" id="ak-chart-slider-label"></span>
					</div>
				</div>
			</div>
		</div>

		<!-- Data table postbox -->
		<div class="ak-postbox">
			<div class="ak-postbox-header">
				<h2><?php esc_html_e( 'Kit Details', 'wporg-learn' ); ?></h2>
				<span class="ak-subtitle" id="ak-table-subtitle"><?php esc_html_e( 'Click a row to see a single kit\'s stats', 'wporg-learn' ); ?></span>
			</div>
			<div style="padding:0;">
				<table class="ak-stats-table" id="ak-stats-table">
					<thead>
						<tr>
							<th data-col="title" data-type="string">
								<?php esc_html_e( 'Kit Name', 'wporg-learn' ); ?> <span class="ak-sort-arrow"></span>
							</th>
							<th data-col="views" data-type="number" class="ak-col-number is-sorted" id="ak-th-views">
								<?php esc_html_e( 'Views', 'wporg-learn' ); ?> <span class="ak-sort-arrow">↓</span>
							</th>
							<th data-col="downloads" data-type="number" class="ak-col-number" id="ak-th-downloads">
								<?php esc_html_e( 'Downloads', 'wporg-learn' ); ?> <span class="ak-sort-arrow"></span>
							</th>
							<th data-col="rate" data-type="number" class="ak-col-number" id="ak-th-rate">
								<?php esc_html_e( 'Download Rate', 'wporg-learn' ); ?> <span class="ak-sort-arrow"></span>
							</th>
							<th data-col="updated" data-type="string">
								<?php esc_html_e( 'Last Updated', 'wporg-learn' ); ?> <span class="ak-sort-arrow"></span>
							</th>
						</tr>
					</thead>
					<tbody id="ak-stats-table-body">
						<tr><td colspan="5"><?php esc_html_e( 'Loading…', 'wporg-learn' ); ?></td></tr>
					</tbody>
				</table>
			</div>
		</div>
	</div>
	<?php
}
