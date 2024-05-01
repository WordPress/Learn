<?php
/**
 * File containing the class \Sensei_Pro_Student_Groups\List_Table\Group_Students_List_Table.
 *
 * @package student-groups
 * @since   1.4.0
 */

namespace Sensei_Pro_Student_Groups\View;

use Sensei_Pro\Assets;
use Sensei_Pro_Student_Groups\Data_Provider\Group_Students\Group_Students_Data_Provider;
use Sensei_Pro_Student_Groups\List_Table\Group_Students_List_Table;
use Sensei_Pro_Student_Groups\Repositories\Group_Student_Repository;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 *  Manage the Groups List View
 *
 * @package student-groups
 * @since   1.4.0
 */
class Student_Groups_View {
	/**
	 * Data provider for current table.
	 *
	 * @var Group_Students_List_Table
	 */
	private $list_table;

	/**
	 * The current view of student groups. Possible values are 'group_students', 'group_access_period'.
	 *
	 * @var string
	 */
	private $view;

	/**
	 * The group id for this page.
	 *
	 * @var int $group_id group id.
	 */
	private $group_id = 0;

	/**
	 * The group name.
	 *
	 * @var string $group_name group name.
	 */
	private $group_name = '';

	/**
	 * Class Constructor
	 *
	 * @param string $module_name module name.
	 */
	public function __construct( $module_name ) {
		global $wpdb;
		$group_students_repository    = new Group_Student_Repository( $wpdb );
		$group_students_data_provider = new Group_Students_Data_Provider( $group_students_repository );

		// phpcs:ignore WordPress.Security.NonceVerification
		if ( isset( $_GET['group_id'] ) ) {
			// phpcs:ignore WordPress.Security.NonceVerification
			$this->group_id = sanitize_text_field( wp_unslash( $_GET['group_id'] ) );
		}

		$post = get_post( $this->group_id );

		if ( $post ) {
			$this->group_name = $post->post_title;
		}

		$this->list_table = new Group_Students_List_Table( $group_students_data_provider, $this->group_id, $this->group_name );

		$this->list_table->prepare_items();

		// Register styles.
		$assets = new Assets( $module_name );
		wp_register_style( 'student-groups-view-style', esc_url( $assets->asset_url() ) . 'student-groups-view-style.css', [], SENSEI_PRO_VERSION );
		wp_enqueue_style( 'student-groups-view-style' );

		// Set view.
		// phpcs:disable WordPress.Security.NonceVerification -- No data are modified.
		if ( isset( $_GET['view'] ) && in_array( $_GET['view'], [ 'group_students', 'group_access_period' ], true ) ) {
			$this->view = sanitize_text_field( wp_unslash( $_GET['view'] ) );
		}

		// Set group id.
		// phpcs:ignore WordPress.Security.NonceVerification
		if ( isset( $_GET['group_id'] ) ) {
			// phpcs:ignore WordPress.Security.NonceVerification
			$this->group_id = sanitize_text_field( wp_unslash( $_GET['group_id'] ) );
		}
		add_action( 'sensei_before_list_table', [ $this, 'sensei_group_page_course_selection_dropdown' ] );
	}

	/**
	 *
	 * List table header to display tab menu consisting of Group Students and Cohorts and Access Period.
	 *
	 * @since   1.4.0
	 * @access public
	 */
	public function get_list_table_header_menu() {
		$group_students_text      = __( 'Group Students', 'sensei-pro' );
		$group_access_period_text = __( 'Cohorts and Access Period', 'sensei-pro' );

		// phpcs:ignore WordPress.Security.NonceVerification -- Argument is used to filter courses.
		if ( isset( $_GET['filter_by_course_id'] ) && '' !== esc_html( sanitize_text_field( wp_unslash( $_GET['filter_by_course_id'] ) ) ) ) {
			$selected_course = (int) $_GET['filter_by_course_id']; // phpcs:ignore WordPress.Security.NonceVerification
		}

		$group_students_url      = add_query_arg(
			[
				'view'     => 'group_students',
				'page'     => 'student_groups',
				'group_id' => $this->group_id,
			],
			admin_url( 'admin.php' )
		);
		$group_access_period_url = add_query_arg(
			[
				'view'     => 'group_access_period',
				'page'     => 'student_groups',
				'group_id' => $this->group_id,
			],
			admin_url( 'admin.php' )
		);

		$group_students_class      = '';
		$group_access_period_class = '';

		$menu = [];

		switch ( $this->view ) {
			case 'group_students':
				$group_students_class = 'current';
				break;

			case 'group_access_period':
			default:
				$group_access_period_class = 'current';
				break;
		}
		$menu['group_students']      = sprintf( '<a href="%s" class="%s">%s</a>', esc_url( $group_students_url ), esc_attr( $group_students_class ), esc_html( $group_students_text ) );
		$menu['group_access_period'] = sprintf( '<a href="%s" class="%s">%s</a>', esc_url( $group_access_period_url ), esc_attr( $group_access_period_class ), esc_html( $group_access_period_text ) );

		echo '<ul class="sensei-student-groups__submenu">';
		foreach ( $menu as $class => $item ) {
			$menu[ $class ] = "<li class='$class'>$item";
		}

		echo wp_kses_post( implode( ' </li>', $menu ) ) . '</li>';
		echo '</ul>';
	}

	/**
	 * Display student groups page content.
	 *
	 * @access public
	 * @since 1.4.0
	 */
	public function display_student_groups() {
		$group_list_page_url = add_query_arg(
			[ 'post_type' => 'group' ],
			admin_url( 'edit.php' )
		);
		?>
		<div class="sensei-student-groups__navigation">
			<div class="sensei-student-groups__header_wrapper">
				<h1 class="sensei-student-groups__header">
					<a href="<?php echo esc_url( $group_list_page_url ); ?>" class="sensei-student-groups__back-button">
						<span class="dashicons dashicons-arrow-left-alt sensei-student-groups__back-button-arrow" />
					</a>
					&nbsp;
					<?php echo wp_kses_post( $this->group_name ); ?>
					<?php

					if ( 'group_students' === $this->view ) {
						printf(
							'<div class="add-student-to-group-button" data-group-id="%s" data-group-name="%s" ></div>',
							esc_attr( $this->group_id ),
							esc_attr( $this->group_name )
						);
					}
					?>
				</h1>
			</div>
			<?php $this->get_list_table_header_menu(); ?>
		</div>
		<div id="poststuff">
			<?php
			switch ( $this->view ) {
				case 'group_students':
					$this->list_table->display();
					break;

				case 'group_access_period':
					printf(
						'<div id="access-period-page" data-group-id="%s"',
						esc_attr( $this->group_id )
					);
					break;
			}
			?>
		</div>
		<?php
	}

	/**
	 * Display course selection dropdown on group studetnts page.
	 *
	 * @since 1.4.0
	 *
	 * @access private
	 */
	public function sensei_group_page_course_selection_dropdown() {
		if ( ! isset( $_GET['view'] ) || ! in_array( $_GET['view'], [ 'group_students' ], true ) ) {
			return;
		}
		$courses         = \Sensei_Course::get_all_courses();
		$selected_course = 0;
		// phpcs:ignore WordPress.Security.NonceVerification -- Argument is used to filter courses.
		if ( isset( $_GET['filter_by_course_id'] ) && '' !== esc_html( sanitize_text_field( wp_unslash( $_GET['filter_by_course_id'] ) ) ) ) {
			$selected_course = (int) $_GET['filter_by_course_id']; // phpcs:ignore WordPress.Security.NonceVerification
		}
		?>
		<div class="alignleft">
			<form action="" method="get">
				<?php
				\Sensei_Utils::output_query_params_as_inputs( [ 'filter_by_course_id' ] );
				$this->courses_select( $courses, $selected_course, 'courses-select-filter', 'filter_by_course_id', __( 'Filter By Course', 'sensei-pro' ) );
				?>
				<button type="submit" id="filt" class="button action"><?php echo esc_html__( 'Filter', 'sensei-pro' ); ?></button>
			</form>
		</div>
		<?php
	}
	/**
	 * Helper method to display a select element which contain courses.
	 *
	 * @param array   $courses         The courses options.
	 * @param integer $selected_course The selected course.
	 * @param string  $select_id       The id of the element.
	 * @param string  $name            The name of the element.
	 * @param string  $select_label    The label of the element.
	 * @param bool    $multiple        Whether multiple selections are allowed.
	 */
	private function courses_select( $courses, $selected_course, $select_id = 'course-select', $name = 'course_id', $select_label = null, $multiple = false ) {
		if ( null === $select_label ) {
			$select_label = __( 'Select Course', 'sensei-pro' );
		}
		?>

		<select id="<?php echo esc_attr( $select_id ); ?>" data-placeholder="<?php echo esc_attr( $select_label ); ?>" name="<?php echo esc_attr( $name ); ?>" class="sensei-course-select" <?php echo $multiple ? 'multiple="true"' : ''; ?>>
			<option value="0"><?php echo esc_html( $select_label ); ?></option>
			<?php
			foreach ( $courses as $course ) {
				echo '<option value="' . esc_attr( $course->ID ) . '"' . selected( $course->ID, $selected_course, false ) . '>' . esc_html( $course->post_title ) . '</option>';
			}
			?>
		</select>
		<?php
	}
}
