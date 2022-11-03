<?php
/**
 * File containing the class \Sensei_Pro_Student_Groups\List_Table\Group_Students_List_Table.
 *
 * @package student-groups
 * @since   1.4.0
 */

namespace Sensei_Pro_Student_Groups\List_Table;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Sensei_Pro_Student_Groups\Data_Provider\Group_Students\Group_Students_Data_Provider;

require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';

/**
 *  Manage the Groups Students List View
 *
 * @package student-groups
 * @since   1.4.0
 */
class Group_Students_List_Table extends \Sensei_List_Table {

	/**
	 * Data provider for current table.
	 *
	 * @var Group_Students_Data_Provider
	 */
	private $group_students_data_provider;

	/**
	 * ID of the group.
	 *
	 * @var int
	 */
	private $group_id;

	/**
	 * Name of the group.
	 *
	 * @var string
	 */
	private $group_name;


	/**
	 * Render the bulk action placeholder
	 * It returns false to prevent WP to render the default bulk actions html
	 *
	 * @access public
	 * @since 1.4.0
	 */
	public function set_bulk_actions() {
		printf( '<div id="group-students-bulk-action" data-group-id="%s"></div>', esc_attr( $this->group_id ) );
		return false;
	}
	/**
	 * Class Constructor
	 *
	 * @param Group_Students_Data_Provider $data_provider group students data provider.
	 * @param int                          $group_id group id to list students in the group.
	 * @param string                       $group_name name of the group.
	 */
	public function __construct( Group_Students_Data_Provider $data_provider, int $group_id, string $group_name ) {
		$this->group_students_data_provider = $data_provider;
		$this->group_id                     = $group_id;
		$this->group_name                   = $group_name;

		parent::__construct( 'group_students' );

		add_filter( 'sensei_list_bulk_actions', [ $this, 'set_bulk_actions' ], 10, 1 );
	}


	/**
	 * Return the table columns
	 *
	 * @access public
	 * @since 1.4.0
	 *
	 * @return array column ids.
	 */
	public function get_columns() {
		return [
			'cb'                 => '<input type="checkbox" />',
			'title'              => sprintf(
			// translators: placeholder is the price.
				__( 'Students (%s)', 'sensei-pro' ),
				$this->total_items
			),
			'email'              => __( 'Email', 'sensei-pro' ),
			'courses'            => __( 'Enrolled Courses', 'sensei-pro' ),
			'last_activity_date' => __( 'Last Activity', 'sensei-pro' ),
			'actions'            => '',
		];
	}

	/**
	 * Prepare the table to be rendered
	 *
	 * @access public
	 * @since 1.4.0
	 *
	 * @return void
	 */
	public function prepare_items(): void {

		$items_per_page = 10;
		$page           = $this->get_pagenum();
		$offset         = ( $page - 1 ) * $items_per_page;

		$user_query_args = [
			'per_page' => $items_per_page,
			'offset'   => $offset,
		];

		// phpcs:ignore WordPress.Security.NonceVerification
		if ( isset( $_GET['orderby'] ) ) {
			// phpcs:ignore WordPress.Security.NonceVerification
			$orderby_param          = sanitize_text_field( wp_unslash( $_GET['orderby'] ) );
			$is_valid_orderby_param = $this->is_orderby_param_valid( $orderby_param );
			if ( $is_valid_orderby_param ) {
				$user_query_args['orderby'] = $orderby_param;
			}
		}
		// phpcs:ignore WordPress.Security.NonceVerification
		if ( isset( $_GET['order'] ) ) {
			// phpcs:ignore WordPress.Security.NonceVerification
			$user_query_args['order'] = sanitize_text_field( wp_unslash( $_GET['order'] ) );
		}

		// phpcs:ignore WordPress.Security.NonceVerification
		if ( isset( $_GET['filter_by_course_id'] ) && absint( $_GET['filter_by_course_id'] ) ) {
			// phpcs:ignore WordPress.Security.NonceVerification
			$user_query_args['filter_by_course_id'] = sanitize_text_field( wp_unslash( $_GET['filter_by_course_id'] ) );
		}
		$result = $this->group_students_data_provider->get_items(
			$user_query_args,
			$this->group_id
		);

		$this->items = $result->get_items();

		$this->total_items = $result->get_total_items();
		$this->set_pagination_args(
			[
				'total_items' => $result->get_total_items(),
				'per_page'    => $items_per_page,
			]
		);
	}



	/**
	 * Return single row data.
	 *
	 * @access public
	 * @since 1.4.0
	 *
	 * @param array $item item.
	 *
	 * @return array row data.
	 */
	public function get_row_data( $item ) {
		return [
			'cb'                 => sprintf( '<input type="checkbox" name="students[]" value="%s" class="students-selector" />', $item['ID'] ),
			'title'              => $this->format_user_name( $item['ID'], $item['display_name'] ),
			'email'              => $item['user_email'],
			'courses'            => $this->get_learner_courses_html( $item['ID'] ),
			'last_activity_date' => (
				$item['last_activity_date']
					? $this->format_last_activity_date( $item['last_activity_date'] )
					: __( 'N/A', 'sensei-pro' )
			),
			'actions'            => sprintf(
				'<div class="group-student-action-menu" data-group-id="%s" data-student-id="%s" data-student-name="%s" />',
				esc_attr( $this->group_id ),
				esc_attr( $item['ID'] ),
				esc_attr( $item['display_name'] )
			),
		];
	}

	/**
	 * Format user name wrapping or not with a link.
	 *
	 * @param int  $user_id user's id.
	 * @param bool $user_name Indicate if it should return the wrap the name with the student link.
	 *
	 * @return string Return the student full name (first_name+last_name) optionally wrapped by a link
	 */
	private function format_user_name( $user_id, $user_name ) {

		$url = add_query_arg(
			[
				'user_id' => $user_id,
			],
			admin_url( 'user-edit.php' )
		);

		return '<strong><a class="row-title" href="' . esc_url( $url ) . '">' . esc_html( $user_name ) . '</a></strong>';
	}

	/**
	 * Define the columns that are going to be used in the table
	 *
	 * @return array The array of columns to use with the table
	 */
	public function get_sortable_columns() {
		return [
			'title'              => [ 'display_name', false ],
			'email'              => [ 'user_email', false ],
			'last_activity_date' => [ 'last_activity_date', false ],
		];
	}
	/**
	 * Check if the orderby param is valid.
	 *
	 * @param string $value orderby param.
	 * @return boolean Is orderby param valid.
	 */
	private function is_orderby_param_valid( $value ) {
		$sortable_columns = $this->get_sortable_columns();

		// if orderby param value is defined in sortable columns return true.
		foreach ( array_keys( $sortable_columns ) as $column_key ) {
			if ( $value === $sortable_columns[ $column_key ][0] ) {
				return true;
			}
		}
		return false;
	}


	/**
	 * Override no items wp list table function.
	 *
	 * @since 1.4.0
	 * @access public
	 */
	public function no_items() {        ?>
		<div class="group-students-table__no-items-container">
			<?php
			echo sprintf(
				'<div class="group-students-table__no-items-title">%s</div>',
				esc_attr( __( "This group doesn't have any students yet.", 'sensei-pro' ) )
			);
			?>
			<?php
			echo sprintf(
				'<div class="add-student-to-group-button" data-group-id="%s" data-group-name="%s" data-is-primary="true" ></div>',
				esc_attr( $this->group_id ),
				esc_attr( $this->group_name )
			);
			?>
		</div>
		<?php
	}

	/**
	 * Format the last activity date to a more readable form.
	 *
	 * @since 1.4.0
	 *
	 * @param string $date The last activity date.
	 *
	 * @return string The formatted last activity date.
	 */
	private function format_last_activity_date( string $date ) {
		$timezone     = new \DateTimeZone( 'GMT' );
		$now          = new \DateTime( 'now', $timezone );
		$date         = new \DateTime( $date, $timezone );
		$diff_in_days = $now->diff( $date )->days;

		// Show a human readable date if activity is within 6 days.
		if ( $diff_in_days < 7 ) {
			return sprintf(
			/* translators: Time difference between two dates. %s: Number of seconds/minutes/etc. */
				__( '%s ago', 'sensei-pro' ),
				human_time_diff( $date->getTimestamp() )
			);
		}

		return wp_date( get_option( 'date_format' ), $date->getTimestamp(), $timezone );
	}

	/**
	 * Helper method to display the content of the enrolled courses' column of the table.
	 *
	 * @param int $user_id  The user id to display their enrolled courses.
	 *
	 * @return string The HTML for the column.
	 */
	private function get_learner_courses_html( $user_id ) {
		$query   = \Sensei_Learner::instance()->get_enrolled_courses_query( $user_id );
		$courses = $query->get_posts();

		if ( empty( $courses ) ) {
			return __( 'N/A', 'sensei-pro' );
		}

		$courses_total = $query->post_count;
		$visible_count = 3;
		$html_items    = [];
		$more_button   = '';

		foreach ( $courses as $course ) {
			$course_management_url = add_query_arg(
				[
					'page'      => 'sensei_learners',
					'course_id' => absint( $course->ID ),
					'view'      => 'learners',
				],
				admin_url( 'admin.php' )
			);

			$html_items[] = '<a href="' . esc_url( $course_management_url ) .
				'" class="sensei-group-students__enrolled-course" data-course-id="' . esc_attr( $course->ID ) . '">' .
				esc_html( $course->post_title ) .
				'</a>';
		}

		if ( $courses_total > $visible_count ) {
			$more_button = '<a href="#" class="sensei-group-students__enrolled-courses-more-link">' .
				sprintf(
				/* translators: %d: the number of links to be displayed */
					esc_html__( '+%d more', 'sensei-pro' ),
					intval( $courses_total - $visible_count )
				) .
				'</a>';
		}

		$visible_courses = implode( '', array_slice( $html_items, 0, $visible_count ) );
		$hidden_courses  = implode( '', array_slice( $html_items, $visible_count ) );

		return $visible_courses . '<div class="sensei-group-students__enrolled-courses-detail hidden">' . $hidden_courses . '</div>' . $more_button;
	}
}
