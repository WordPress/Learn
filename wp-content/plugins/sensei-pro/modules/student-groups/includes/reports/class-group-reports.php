<?php
/**
 * File containing the class \Sensei_Pro_Student_Groups\Reports\Group_Reports.
 *
 * @package student-groups
 */

namespace Sensei_Pro_Student_Groups\Reports;

use WP_Query;
use WP_User_Query;

/**
 * Class that enables group reports.
 *
 * @since 1.5.0
 */
class Group_Reports {
	/**
	 * Instance of class.
	 *
	 * @var self
	 */
	private static $instance;

	/**
	 * Provider's constructor.
	 */
	private function __construct() {}

	/**
	 * Fetches an instance of the class.
	 *
	 * @since 1.5.0
	 *
	 * @return self
	 */
	public static function instance(): self {
		if ( ! self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Initializes the class and adds all filters and actions.
	 *
	 * @since 1.5.0
	 */
	public function init() {
		add_action( 'sensei_reports_overview_after_top_filters', [ $this, 'output_group_filter_for_students' ] );
		add_action( 'sensei_reports_overview_students_data_provider_pre_user_query', [ $this, 'filter_students_by_group' ] );
		add_filter( 'sensei_reports_overview_export_button_url', [ $this, 'add_group_filter_to_export_button_url' ] );
	}

	/**
	 * Print the group filter on the student reports screen.
	 *
	 * @since  1.5.0
	 * @access private
	 *
	 * @param string $report_type
	 */
	public function output_group_filter_for_students( string $report_type ) {
		if ( 'users' !== $report_type ) {
			return;
		}

		?>
		<label for="sensei-group-filter">
			<?php esc_html_e( 'Group', 'sensei-pro' ); ?>:
		</label>

		<select name="group_filter" id="sensei-group-filter">
			<option>
				<?php esc_html_e( 'Select a group', 'sensei-pro' ); ?>
			</option>

			<?php foreach ( $this->get_all_groups() as $group ) : ?>
				<option
					value="<?php echo esc_attr( $group->ID ); ?>"
					<?php selected( $group->ID, $this->get_group_filter_value(), true ); ?>
				>
					<?php echo esc_html( get_the_title( $group ) ); ?>
				</option>
			<?php endforeach; ?>
		</select>
		<?php
	}

	/**
	 * Modify the query to filter the students by the selected group.
	 *
	 * @since  1.5.0
	 * @access private
	 *
	 * @param WP_User_Query $query
	 */
	public function filter_students_by_group( WP_User_Query $query ) {
		$group_filter = $this->get_group_filter_value();

		if ( ! $group_filter ) {
			return;
		}

		global $wpdb;

		$query->query_from .= $wpdb->prepare(
			" INNER JOIN {$wpdb->prefix}sensei_pro_groups_students
				ON {$wpdb->prefix}sensei_pro_groups_students.student_id = {$wpdb->users}.ID
				AND {$wpdb->prefix}sensei_pro_groups_students.group_id = %d ",
			$group_filter
		);
	}

	/**
	 * Add group filter param to the export button url.
	 *
	 * @since  1.5.0
	 * @access private
	 *
	 * @param string $url
	 *
	 * @return string
	 */
	public function add_group_filter_to_export_button_url( string $url ): string {
		$group_filter = $this->get_group_filter_value();

		if ( ! $group_filter ) {
			return $url;
		}

		return add_query_arg(
			[ 'group_filter' => $group_filter ],
			$url
		);
	}

	/**
	 * Get the selected group ID.
	 *
	 * @since 1.5.0
	 *
	 * @return int The group ID or 0 if none is selected.
	 */
	private function get_group_filter_value(): int {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Arguments used for filtering.
		return isset( $_GET['group_filter'] ) ? (int) $_GET['group_filter'] : 0;
	}

	/**
	 * Get all group posts.
	 *
	 * @since 1.5.0
	 *
	 * @return \WP_Post[]
	 */
	private function get_all_groups(): array {
		$query = new WP_Query(
			[
				'post_type'      => 'group',
				'posts_per_page' => -1,
				'post_status'    => 'any',
			]
		);

		return $query->posts;
	}
}
