<?php
/**
 * File containing the class \Sensei_Pro\Student_Groups.
 *
 * @package student-groups
 * @since   1.4.0
 */

namespace Sensei_Pro_Student_Groups;

use Sensei_Pro_Student_Groups\Enrolment\Enrolment_Handler;
use Sensei_Pro_Student_Groups\Enrolment\Groups_Provider;
use Sensei_Pro_Student_Groups\Enrolment\Providers;
use Sensei_Pro_Student_Groups\Reports\Group_Reports;
use Sensei_Pro_Student_Groups\Repositories\Group_Course_Repository;
use Sensei_Pro_Student_Groups\Assets\Components_Provider;
use Sensei_Pro_Student_Groups\Repositories\Group_Student_Repository;
use Sensei_Pro_Student_Groups\Rest_Api\Controllers\Group_Courses_Controller;
use Sensei_Pro_Student_Groups\Rest_Api\Controllers\Group_Students_Controller;
use Sensei_Pro_Student_Groups\Rest_Api\Controllers\Groups_Controller;
use Sensei_Pro_Student_Groups\Settings\Group_Settings;
use Sensei_Pro_Student_Groups\Students\Group_Bulk_Actions;
use Sensei_Pro_Student_Groups\View\Student_Groups_View;
use Sensei_Pro_Student_Groups\Blocks\Join_Group_Blocks;
use WP_Post;
use function Sensei_Pro_Student_Groups\get_join_group_page_attributes;
use function get_current_screen;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_filter(
	'sensei_default_feature_flag_settings',
	function ( $settings ) {
		$settings['group_signup'] = true;
		return $settings;
	}
);

/**
 * Student groups.
 *
 * @since 1.4.0
 */
class Student_Groups {

	const MODULE_NAME = 'student-groups';

	const GROUP_POST_TYPE = 'group';

	const GROUP_SIGNUP_CODE_META_NAME = 'sensei_group_signup_code';

	/**
	 * Instance of class.
	 *
	 * @var self
	 */
	private static $instance;

	/**
	 * Page slug.
	 *
	 * @var string Page slug.
	 */
	public $page_slug;

	/**
	 * Page name.
	 *
	 * @var string Page name.
	 */
	public $name;

	/**
	 * Plugin directory.
	 *
	 * @var string
	 */
	private $ssg_dir;

	/**
	 * Plugin directory.
	 *
	 * @var string
	 */
	private $post_type;

	/**
	 * Script and stylesheet loading.
	 *
	 * @var Components_Provider
	 */
	public $assets;

	/**
	 * Group student repository.
	 *
	 * @var Group_Student_Repository
	 */
	private $group_student_repository;

	/**
	 * Group course repository.
	 *
	 * @var Group_Course_Repository
	 */
	private $group_course_repository;

	/**
	 * Access control instance.
	 *
	 * @var Access_Control
	 */
	public $access_control;

	/**
	 * Constructor
	 *
	 * @since  1.4.0
	 */
	public function __construct() {
		$this->post_type = 'group';
		$this->ssg_dir   = dirname( __DIR__ );

		$this->page_slug = 'student_groups';
		$this->name      = __( 'Groups', 'sensei-pro' );
		$this->load_classes();
		$this->assets = new Components_Provider();

		global $wpdb;
		$this->group_student_repository = new Group_Student_Repository( $wpdb );
		$this->group_course_repository  = new Group_Course_Repository( $wpdb );

		$this->access_control = new Access_Control( $this->group_course_repository );
	}

	/**
	 * Set the groups list table column
	 *
	 * @since  1.4.0
	 * @param array $columns
	 * @return array
	 */
	public function set_groups_column( $columns ): array {
		$columns['students'] = _x( 'Students', 'column name', 'sensei-pro' );
		$columns['courses']  = _x( 'Group Courses', 'column name', 'sensei-pro' );

		if ( function_exists( 'Sensei' ) && Sensei()->feature_flags->is_enabled( 'group_signup' ) ) {
			$columns['signup'] = _x( 'Invite Link', 'column name', 'sensei-pro' );
		}

		$columns['actions'] = '';
		unset( $columns['date'] );

		return $columns;
	}

	/**
	 * Set the custom columns on the admin groups list table
	 *
	 * @param string $key
	 * @param int    $group_id
	 * @return void
	 */
	public function set_group_custom_columns( string $key, int $group_id ): void {
		$edit_group_students_url = $this->get_edit_group_students_url( $group_id );
		$group_settings_url      = $this->get_group_settings_url( $group_id );

		if ( 'actions' === $key && ! self::is_trash_view() ) {
			$group = get_post( $group_id );
			printf(
				'<div class="group-action-menu" data-group-id="%s" data-group-name="%s" data-edit-group-students-url="%s" data-group-settings-url="%s" />',
				esc_attr( $group_id ),
				esc_attr( $group->post_title ),
				esc_attr( $edit_group_students_url ),
				esc_attr( $group_settings_url )
			);
		}

		if ( 'students' === $key ) {
			echo esc_html( $this->group_student_repository->get_count_for_group( $group_id ) );
		}

		if ( 'courses' === $key ) {
			echo esc_html( $this->group_course_repository->get_count_for_group( $group_id ) );
		}

		if ( 'signup' === $key ) {
			$signup_link = $this->get_group_signup_link( $group_id );

			$signup_page_id = Sensei()->settings->get( Group_Settings::GROUP_SIGNUP_PAGE_SETTING );
			$signup_page_id = $signup_page_id ? intval( $signup_page_id ) : 0;

			if ( 0 !== $signup_page_id ) {
				$edit_page_link = admin_url( 'post.php?post=' . $signup_page_id . '&action=edit' );

				printf(
					'<div class="sensei-group-copy-signup-link-container" data-signup-link="%s" data-edit-page-link="%s" />',
					esc_url( $signup_link ),
					esc_url( $edit_page_link ),
				);
			}
		}
	}

	/**
	 * Get group signup link.
	 *
	 * @param int $group_id The group ID.
	 *
	 * @return string The signup link or empty string in case of error.
	 */
	private function get_group_signup_link( int $group_id ) {
		$page_permalink = $this->get_group_signup_page_permalink();

		if ( ! $page_permalink ) {
			return '';
		}

		$code = $this->get_group_signup_code( $group_id );

		if ( ! $code ) {
			return '';
		}

		return add_query_arg(
			[ 'group-code' => $code ],
			$page_permalink
		);
	}

	/**
	 * Get group signup page permalink.
	 * The page is created if it does not exist yet.
	 *
	 * @return string|false The permalink URL. False if the post does not exist.
	 */
	private function get_group_signup_page_permalink() {
		$page_id = Sensei()->settings->get( Group_Settings::GROUP_SIGNUP_PAGE_SETTING );
		$page_id = $page_id ? intval( $page_id ) : 0;

		if ( 0 !== $page_id ) {
			$page = get_post( $page_id );

			if ( null === $page || 'page' !== $page->post_type || 'publish' !== $page->post_status ) {
				$page_id = 0;
			}
		}

		if ( 0 === $page_id ) {
			// Remove action to add page to the menu. It works when not using Navigation block.
			remove_action( 'transition_post_status', '_wp_auto_add_pages_to_menu' );

			// Create page.
			$page_id = wp_insert_post( get_join_group_page_attributes() );

			// Restore action to add page to the menu.
			add_action( 'transition_post_status', '_wp_auto_add_pages_to_menu', 10, 3 );

			Sensei()->settings->set( Group_Settings::GROUP_SIGNUP_PAGE_SETTING, $page_id );

			// See https://github.com/Automattic/sensei/issues/7194.
			Sensei()->settings->get_settings();
		}

		return get_permalink( $page_id );
	}

	/**
	 * Remove join group page from page list by filtering the query args.
	 *
	 * @internal
	 *
	 * @param array $query_args The query args.
	 *
	 * @return array Filtered query args, excluding join group page.
	 */
	public function remove_join_group_page_from_page_list( $query_args ) {
		$page_id = Sensei()->settings->get( Group_Settings::GROUP_SIGNUP_PAGE_SETTING );
		$page_id = $page_id ? intval( $page_id ) : 0;

		if ( 0 === $page_id ) {
			return $query_args;
		}

		$query_args['post__not_in'][] = $page_id;

		return $query_args;
	}

	/**
	 * Add filter to remove the join group page from the Navigation block before processing the block.
	 * The pages are listed on the navigation when using the Page List block inside the navigation.
	 *
	 * @internal
	 *
	 * @param string|null $pre_render
	 * @param array       $parsed_block The parsed block.
	 *
	 * @return string|null Returns the `$pre_render` without touching it.
	 */
	public function add_join_group_page_filter( $pre_render, $parsed_block ) {
		if ( 'core/navigation' === $parsed_block['blockName'] ) {
			add_filter( 'get_pages_query_args', [ $this, 'remove_join_group_page_from_page_list' ] );
		}

		return $pre_render;
	}

	/**
	 * Remove join group page filter after Navigation block rendering was processed.
	 * So the normal behavior is restored.
	 *
	 * @internal
	 *
	 * @param string $block_content
	 *
	 * @return string $block_content Returns the `$block_content` without touching it.
	 */
	public function remove_join_group_page_filter( $block_content ) {
		remove_filter( 'get_pages_query_args', [ $this, 'remove_join_group_page_from_page_list' ] );

		return $block_content;
	}

	/**
	 * Get group signup code.
	 * The code is generated if it does not exist yet.
	 *
	 * @param int $group_id The group ID.
	 *
	 * @return string|false The signup code. False in case of error.
	 */
	private function get_group_signup_code( int $group_id ) {
		$code = get_post_meta( $group_id, self::GROUP_SIGNUP_CODE_META_NAME, true );

		if ( empty( $code ) ) {
			$code = wp_generate_password( 12, false );
			$meta = update_post_meta( $group_id, self::GROUP_SIGNUP_CODE_META_NAME, $code );

			if ( false === $meta ) {
				return false;
			}
		}

		return $code;
	}

	/**
	 * Initializes the class and adds all filters and actions related to the frontend.
	 *
	 * @since 1.4.0
	 */
	public static function init() {
		$instance = self::instance();

		// TODO: Add the post type. The following is a stub, should be replaced with a real implementation.
		add_action( 'init', [ $instance, 'register_post_type' ] );

		// Add wrapper around the student groups page.
		add_action( 'sensei_pro_student_groups_wrapper_container', [ $instance, 'wrapper_container' ] );

		// Set up REST API endpoints.
		add_action( 'rest_api_init', [ $instance, 'init_rest_api_endpoints' ], 1 );

		// Add custom navigation.
		add_action( 'in_admin_header', [ $instance, 'add_custom_navigation' ] );

		// Add custom group columns.
		add_filter( 'manage_group_posts_columns', [ $instance, 'set_groups_column' ] );

		// Add custom group action value.
		add_action( 'manage_group_posts_custom_column', [ $instance, 'set_group_custom_columns' ], 10, 2 );

		// Disable standard filters, bulk actions and submenu on the groups page.
		add_filter( 'bulk_actions-edit-group', '__return_empty_array' );
		add_filter( 'disable_months_dropdown', [ $instance, 'disable_list_table_filter' ], 10, 2 );
		add_filter( 'disable_categories_dropdown', [ $instance, 'disable_list_table_filter' ], 10, 2 );
		add_filter( 'disable_formats_dropdown', [ $instance, 'disable_list_table_filter' ], 10, 2 );

		// Remove row actions for groups, except for the trashed groups.
		if ( ! self::is_trash_view() ) {
			add_filter( 'post_row_actions', [ $instance, 'disable_list_table_row_actions' ], 10, 2 );
		}

		add_filter( 'wp_untrash_post_status', [ $instance, 'restore_groups_to_published' ], 10, 2 );

		add_filter(
			'sensei_scripts_allowed_post_types',
			function ( $post_types ) {
				$post_types[] = 'group';

				return $post_types;
			}
		);
		add_filter(
			'sensei_custom_navigation_allowed_screens',
			function ( $page_array ) {
				$page_array[] = 'edit-group';

				return $page_array;
			}
		);
		add_action( 'admin_enqueue_scripts', [ $instance, 'enqueue_scripts' ] );

		// Perform group related actions on relevant post deletion.
		add_action( 'delete_post', [ $instance, 'perform_actions_on_post_deletion' ], 10, 2 );

		if ( is_admin() ) {
			// Modify group edit post link to redirect to student groups page.
			add_filter( 'get_edit_post_link', [ $instance, 'get_student_groups_page_url' ], 10, 4 );
		}

		// Add actions to add the students to courses.
		$instance->init_enrolment_actions();

		// Init the student groups enrolment provider.
		Providers::instance()->init();

		// Add Group Submenu.
		add_action( 'admin_menu', [ $instance, 'add_group_submenu' ] );

		// Add action to add groups menu item on the right place.
		add_action( 'sensei_pro_groups_menu_item', [ $instance, 'add_groups_submenu_page' ] );

		add_action( 'admin_head', [ $instance, 'remove_submenu_page_on_load' ] );
		add_filter( 'submenu_file', [ $instance, 'highlight_proper_submenu_item_for_group_pages' ] );

		// Init the student groups access control.
		$instance->access_control->init();

		// Init the group reports.
		Group_Reports::instance()->init();

		// Init the group related bulk actions.
		Group_Bulk_Actions::instance()->init();

		if ( function_exists( 'Sensei' ) && Sensei()->feature_flags->is_enabled( 'group_signup' ) ) {
			// Init the group settings.
			Group_Settings::instance()->init();

			// Init the Join Group blocks.
			( new Join_Group_Blocks( $instance->group_student_repository ) )->init();

			// Avoid listing the join group page in the Navigation block.
			add_filter( 'pre_render_block', [ $instance, 'add_join_group_page_filter' ], 10, 2 );
			add_filter( 'render_block_core/page-list', [ $instance, 'remove_join_group_page_filter' ] );
		}
	}

	/**
	 * Disables list table filters on the groups page.
	 *
	 * @access private
	 *
	 * @param bool   $disabled Whether the filter should be disabled.
	 * @param string $post_type The post type.
	 *
	 * @return bool
	 */
	public function disable_list_table_filter( $disabled, $post_type ) {
		if ( $post_type === $this->post_type ) {
			$disabled = true;
		}

		return $disabled;
	}

	/**
	 * Disable the row actions on the groups post type table.
	 *
	 * @since  1.4.0
	 * @access private
	 *
	 * @param array   $actions
	 * @param WP_Post $post
	 *
	 * @return array
	 */
	public function disable_list_table_row_actions( array $actions, WP_Post $post ): array {
		if ( $post->post_type !== $this->post_type ) {
			return $actions;
		}

		return [];
	}

	/**
	 * Returns publish status for the group we recover.
	 *
	 * @since  1.4.1
	 * @access private
	 *
	 * @param string $new_status New status for the post.
	 * @param int    $post_id Post ID.
	 */
	public function restore_groups_to_published( string $new_status, int $post_id ): string {
		$post = get_post( $post_id );
		if ( 'group' !== $post->post_type ) {
			return $new_status;
		}
		return 'publish';
	}

	/**
	 * Add custom navigation to the admin pages.
	 *
	 * @since  1.4.0
	 * @access private
	 */
	public function add_custom_navigation() {
		$screen = get_current_screen();
		if ( ! $screen ) {
			return;
		}

		if ( ! in_array( $screen->id, [ 'edit-group' ], true ) ) {
			return;
		}

		?>
		<div id="sensei-custom-navigation" class="sensei-custom-navigation">
			<div class="sensei-custom-navigation__heading">
				<div class="sensei-custom-navigation__title">
					<h1><?php esc_html_e( 'Groups', 'sensei-pro' ); ?></h1>
				</div>
				<div class="sensei-custom-navigation__links">
					<span id="group-add-button" />
					<div id="group-creation-modal-container"></div>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Add enrolment handlers to group actions.
	 *
	 * @since 1.4.0
	 * @access private
	 */
	public function init_enrolment_actions() {
		$enrolment_provider = Groups_Provider::instance();
		$enrolment_handler  = new Enrolment_Handler(
			$this->group_course_repository,
			$this->group_student_repository,
			$enrolment_provider
		);

		// Enroll all group students to the course when the course was added to the group.
		add_action(
			'sensei_pro_student_groups_group_course_added',
			[ $enrolment_handler, 'enroll_group_students_in_course' ],
			10,
			2
		);

		// Enroll the student in the group courses when the student was added to the group.
		add_action(
			'sensei_pro_student_groups_group_student_added',
			[ $enrolment_handler, 'enroll_student_in_group_courses' ],
			10,
			2
		);

		// Remove the student from the course when they were removed manually.
		add_action(
			'sensei_course_enrolment_status_changed',
			[ $enrolment_handler, 'remove_enrolment' ],
			10,
			3
		);

		// Remove the student from the all group courses when removed from group.
		add_action(
			'sensei_pro_student_groups_group_students_removed',
			[ $enrolment_handler, 'remove_students_from_group_courses' ],
			10,
			2
		);

		// Remove the student from courses when the courses removed from the group.
		add_action(
			'sensei_pro_student_groups_group_courses_removed',
			[ $enrolment_handler, 'remove_enrolment_in_courses_for_group' ],
			10,
			2
		);
	}

	/**
	 * Initialize REST API endpoints.
	 *
	 * @access private
	 * @since  1.4.0
	 */
	public function init_rest_api_endpoints() {
		( new Groups_Controller() )->register_routes();
		( new Group_Students_Controller( $this->group_student_repository ) )->register_routes();
		( new Group_Courses_Controller( $this->group_course_repository ) )->register_routes();
	}

	/**
	 * Load the required classes.
	 *
	 * @since 1.4.0
	 */
	private function load_classes() {
		require_once $this->ssg_dir . '/includes/join-group-page.php';
		include_once $this->ssg_dir . '/includes/class-access-control.php';
		include_once $this->ssg_dir . '/includes/assets/class-components-provider.php';
		include_once $this->ssg_dir . '/includes/rest-api/controllers/class-groups-controller.php';
		include_once $this->ssg_dir . '/includes/rest-api/controllers/class-wp-rest-groups-controller.php';
		require_once $this->ssg_dir . '/includes/models/class-group-student.php';
		require_once $this->ssg_dir . '/includes/repositories/class-group-student-repository.php';
		require_once $this->ssg_dir . '/includes/rest-api/responses/class-student-response.php';
		require_once $this->ssg_dir . '/includes/rest-api/controllers/class-group-students-controller.php';
		require_once $this->ssg_dir . '/includes/data-provider/group-students/class-group-students-data-provider.php';
		require_once $this->ssg_dir . '/includes/data-provider/group-students/class-group-students-result.php';
		require_once $this->ssg_dir . '/includes/list-table/class-group-students-list-table.php';
		require_once $this->ssg_dir . '/includes/view/class-student-groups-view.php';
		require_once $this->ssg_dir . '/includes/rest-api/controllers/class-group-courses-controller.php';
		require_once $this->ssg_dir . '/includes/repositories/class-group-course-repository.php';
		require_once $this->ssg_dir . '/includes/rest-api/responses/class-course-response.php';
		require_once $this->ssg_dir . '/includes/models/class-group-course.php';
		require_once $this->ssg_dir . '/includes/models/class-access-period.php';
		require_once $this->ssg_dir . '/includes/enrolment/class-groups-provider.php';
		require_once $this->ssg_dir . '/includes/enrolment/class-providers.php';
		require_once $this->ssg_dir . '/includes/enrolment/class-enrolment-handler.php';
		require_once $this->ssg_dir . '/includes/reports/class-group-reports.php';
		require_once $this->ssg_dir . '/includes/settings/class-group-settings.php';
		require_once $this->ssg_dir . '/includes/students/class-group-bulk-actions.php';
		require_once $this->ssg_dir . '/includes/blocks/class-join-group-blocks.php';
	}

	/**
	 *
	 * Fetches an instance of the class.
	 *
	 * @return self
	 */
	public static function instance() {
		if ( ! self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Register the student groups post type.
	 *
	 * @access private
	 * @since  1.4.0
	 */
	public function register_post_type() {
		register_post_type(
			self::GROUP_POST_TYPE,
			[
				'labels'                => [
					'name'               => __( 'Groups', 'sensei-pro' ),
					'singular_name'      => __( 'Group', 'sensei-pro' ),
					'add_new'            => __( 'Add New', 'sensei-pro' ),
					'add_new_item'       => __( 'Add New Group', 'sensei-pro' ),
					'edit_item'          => __( 'Edit Group', 'sensei-pro' ),
					'new_item'           => __( 'New Group', 'sensei-pro' ),
					'view_item'          => __( 'View Group', 'sensei-pro' ),
					'search_items'       => __( 'Search Groups', 'sensei-pro' ),
					'not_found'          => __( 'No groups found', 'sensei-pro' ),
					'not_found_in_trash' => __( 'No groups found in trash', 'sensei-pro' ),
					'parent_item_colon'  => __( 'Parent Group:', 'sensei-pro' ),
					'menu_name'          => __( 'Groups', 'sensei-pro' ),
				],
				'public'                => false,
				'publicly_queryable'    => false,
				'show_ui'               => true,
				'show_in_menu'          => false,
				'query_var'             => true,
				'rewrite'               => false,
				'capability_type'       => 'post',
				'has_archive'           => false,
				'supports'              => [ 'title' ],
				'show_in_rest'          => true,
				'rest_base'             => 'groups',
				'rest_controller_class' => '\Sensei_Pro_Student_Groups\Rest_Api\Controllers\WP_REST_Groups_Controller',
			]
		);
	}

	/**
	 * Get student groups redirect url or post edit url.
	 *
	 * @access public
	 * @since  1.4.0
	 *
	 * @param string $url     string default url.
	 * @param string $post_id string post id.
	 *
	 * @return string url for student groups redirect or edit post url.
	 */
	public function get_student_groups_page_url( string $url, string $post_id ): string {
		if ( ! function_exists( 'get_current_screen' ) ) {
			return $url;
		}

		$screen = get_current_screen();
		// If the post type is not group, return default url.
		if ( ! $screen || $this->post_type !== $screen->post_type ) {
			return $url;
		}

		return $this->get_edit_group_students_url( $post_id );
	}

	/**
	 * Get student groups url.
	 *
	 * @since  1.4.0
	 *
	 * @param string $post_id string post id.
	 *
	 * @return string url for students page.
	 */
	private function get_edit_group_students_url( $post_id ) {
		return add_query_arg(
			[
				'view'     => 'group_students',
				'page'     => 'student_groups',
				'group_id' => $post_id,
			],
			admin_url( 'admin.php' )
		);
	}

	/**
	 * Get settings groups url.
	 *
	 * @since  1.4.0
	 *
	 * @param string $post_id string post id.
	 *
	 * @return string url for settings page.
	 */
	private function get_group_settings_url( $post_id ) {
		return add_query_arg(
			[
				'view'     => 'group_access_period',
				'page'     => 'student_groups',
				'group_id' => $post_id,
			],
			admin_url( 'admin.php' )
		);
	}

	/**
	 * Function to get student groups page.
	 *
	 * @since  1.4.0
	 * @access public
	 */
	public function student_groups_page(): void {
		sensei_log_event(
			'sensei_pro_student_groups_display'
		);
		/**
		 * Before student groups page content.
		 *
		 * @since 1.4.0
		 * @hook  sensei_pro_student_groups_wrapper_container
		 */
		do_action( 'sensei_pro_student_groups_wrapper_container', 'top' );

		$view = new Student_Groups_View( self::MODULE_NAME );
		$view->display_student_groups();

		/**
		 * After student groups page content.
		 *
		 * @since 1.4.0
		 * @hook  sensei_pro_student_groups_wrapper_container
		 */
		do_action( 'sensei_pro_student_groups_wrapper_container', 'bottom' );

		$this->enqueue_scripts();
	}


	/**
	 * Returns if the current page is groups trash
	 *
	 * @return bool Return true if it is the trash view
	 */
	private static function is_trash_view(): bool {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		return isset( $_GET['post_type'] ) && 'group' === $_GET['post_type'] && isset( $_GET['post_status'] ) && 'trash' === $_GET['post_status'];
	}

	/**
	 * Return the current view
	 *
	 * @since  1.4.0
	 * @access private
	 * @return ?string  The current view name.
	 */
	private function get_current_view(): ?string {
		// phpcs:ignore WordPress.Security.NonceVerification
		if ( isset( $_GET['view'] ) && in_array( $_GET['view'], [ 'group_students', 'group_access_period' ], true ) ) {
			// phpcs:ignore WordPress.Security.NonceVerification
			return sanitize_text_field( wp_unslash( $_GET['view'] ) );
		}
		return null;
	}

	/**
	 * Enqueue scripts.
	 *
	 * @since  1.4.0
	 * @access public
	 */
	public function enqueue_scripts() {
		$screen = get_current_screen();
		$view   = $this->get_current_view();

		if ( ! $screen ) {
			return;
		}

		if ( in_array( $screen->id, [ 'edit-group' ], true ) ) {
			$this->assets->enqueue_component( 'group-creation-modal', [ 'wp-components' ], [ 'wp-components' ] );
			$this->assets->enqueue_component( 'group-action-menu', [], [] );
			$this->assets->enqueue_component( 'copy-signup-link-button' );
		}

		if ( in_array( $screen->id, [ 'sensei-lms_page_student_groups' ], true ) ) {
			$this->assets->enqueue_component( 'group-action-menu', [], [] );
			$this->assets->enqueue_component( 'group-student-action-menu', [ 'wp-components' ], [ 'wp-components' ] );
			$this->assets->enqueue_component( 'add-student-to-group-button', [ 'wp-components' ], [ 'wp-components' ] );
			$this->assets->enqueue_component( 'group-students-page', [ 'jquery' ], [], false );

			if ( 'group_students' === $view ) {
				$this->assets->enqueue_component( 'group-students-bulk-actions' );
			}

			if ( 'group_access_period' === $view ) {
				$this->assets->enqueue_component( 'add-courses-to-group', [ 'wp-components' ], [ 'wp-components' ] );
			}
		}

		if ( 'sensei-lms_page_sensei_learners' === $screen->id ) {
			$this->assets->enqueue_component( 'student-management-bulk-actions', [ 'wp-hooks' ], [], false );
		}
	}

	/**
	 * Wrapper container wrapper_container wrapper.
	 *
	 * @since  1.4.0
	 *
	 * @param string $which which wrapper top or bottom.
	 */
	public function wrapper_container( $which ) {
		if ( 'top' === $which ) {
			?>
			<div id="woothemes-sensei" class="wrap woothemes-sensei">
			<?php
		} elseif ( 'bottom' === $which ) {
			?>
			</div><!--/#woothemes-sensei-->
			<?php
		}
	}

	/**
	 * Hook action to perform operations on post deletion.
	 *
	 * @since  1.4.0
	 *
	 * @access private
	 *
	 * @param int     $post_id ID of the post being deleted.
	 * @param WP_Post $post    The post being deleted.
	 */
	public function perform_actions_on_post_deletion( int $post_id, WP_Post $post ) {
		global $wpdb;

		switch ( $post->post_type ) {
			case 'group':
				// Remove all group-course or group-student relation data on group deletion.
				( new Group_Student_Repository( $wpdb ) )->delete_all_relations_for_group( $post_id );
				( new Group_Course_Repository( $wpdb ) )->delete_all_relations_for_group( $post_id );
				break;
			case 'course':
				// Remove all group-course relation data on course deletion.
				( new Group_Course_Repository( $wpdb ) )->delete_all_by_course( $post_id );
				break;
			default:
				break;
		}
	}

	/**
	 * Solves highlighting for submenu items
	 *
	 * @access private
	 *
	 * @since 1.4.0
	 * @return void
	 */
	public function remove_submenu_page_on_load() {
		// Adding the group-students submenu with parent slug will make the menu
		// item visible on the navbar. So once we're done registering it, we remove it
		// in the code below, so we get the parent highlighting, but no menu item.
		remove_submenu_page( 'sensei', $this->page_slug );
	}

	/**
	 * Adds the menu pages required for groups.
	 *
	 * @access private
	 *
	 * @since 1.4.0
	 * @return void
	 */
	public function add_group_submenu() {

		// Registering with 'null' parent_slug will add the menu without any issue,
		// but the generated page won't be under any parent menu item when rendered,
		// so we won't have control over which menu item gets highlighted. Deifining
		// the parent slug makes sure when this page is rendered, the parent menu item
		// will be highlighted.

		// We're removing the parent slug when the page is not the actual Group Students
		// so that it doesn't show up in the Calypso menu.
		global $pagenow;
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$parent_slug = ( 'admin.php' === $pagenow && isset( $_GET['page'] ) && 'student_groups' === $_GET['page'] ) ? 'sensei' : '';

		add_submenu_page(
			$parent_slug,
			'Group Students',
			'Group Students',
			'edit_courses',
			$this->page_slug,
			[ $this, 'student_groups_page' ]
		);
	}

	/**
	 * Adds the groups item to admin menu.
	 *
	 * @access public
	 *
	 * @since 1.4.0
	 * @return void
	 */
	public function add_groups_submenu_page() {
		if ( ! current_user_can( 'manage_sensei' ) ) {
			return;
		}

		add_submenu_page(
			'sensei',
			__( 'Groups', 'sensei-pro' ),
			__( 'Groups', 'sensei-pro' ),
			'edit_courses',
			'edit.php?post_type=group',
			null
		);
	}

	/**
	 * Highlight right submenu item for group submenu pages.
	 *
	 * @access private
	 *
	 * @since 1.4.0
	 *
	 * @param string $submenu_file Current submenu file.
	 *
	 * @return string Modified or same submenu file based on screen ID.
	 */
	public function highlight_proper_submenu_item_for_group_pages( $submenu_file ) {
		// Only the main menu item 'Sensei LMS' is highlighted at this point.
		// Setting the global submenu_file to group will make sure
		// 'Sensei LMS -> Groups' is highlighted.

		$screen = get_current_screen();

		if ( $screen && in_array( $screen->id, [ 'sensei-lms_page_student_groups' ], true ) ) {
			// phpcs:disable WordPress.WP.GlobalVariablesOverride.Prohibited
			return 'edit.php?post_type=group';
			// phpcs:enable WordPress.WP.GlobalVariablesOverride.Prohibited
		}

		return $submenu_file;
	}
}
