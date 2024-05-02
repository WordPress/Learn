<?php
/**
 * File containing the Course_Showcase_Listing class.
 *
 * @package sensei-pro
 * @since   1.12.0
 */

namespace Sensei_Pro\Course_Showcase;

use WP_Query;
use Sensei_Course;
use SenseiLMS_Licensing\License_Manager;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class responsible for managing the showcase listings persisted as a Custom Post Type.
 *
 * A listing is the submission of a course by a user to be listed in the Course Showcase.
 *
 * @since 1.12.0
 */
class Course_Showcase_Listing {
	/**
	 * The module name.
	 */
	const MODULE_NAME = 'course-showcase';

	/**
	 * The post type for the Course Showcase listing.
	 */
	public const POST_TYPE = 'sensei_showcase'; // Maximum 20 characters.

	/**
	 * The tab key for the Course Showcase feature.
	 */
	private const TAB_KEY = 'showcase-courses';

	/**
	 * The Screen ID for the Course Showcase listing.
	 */
	private const SCREEN_ID = 'edit-' . self::POST_TYPE;

	/**
	 * Value used to represent that there is a connectivity error.
	 */
	private const ERROR_CONNECTIVITY = 'connectivity';

	/**
	 * The category options transient key.
	 */
	private const CATEGORY_OPTIONS_TRANSIENT_KEY = 'sensei_pro_course_showcase_category_options';

	/**
	 * The language options transient key.
	 */
	private const LANGUAGE_OPTIONS_TRANSIENT_KEY = 'sensei_pro_course_showcase_language_options';

	/**
	 * Whether the site has showcase eligible courses transient key.
	 */
	private const HAS_ELIGIBLE_COURSES_TRANSIENT_KEY = 'sensei_pro_has_showcase_eligible_courses';

	/**
	 * The scale to mount the number of students string.
	 */
	const STUDENTS_NUMBER_SCALE_MINIMUMS = [
		1,
		10,
		25,
		50,
		100,
		500,
		1000,
		5000,
		10000,
		50000,
		100000,
	];

	/**
	 * Singleton instance.
	 *
	 * @var Course_Showcase_Listing
	 */
	private static $instance;

	/**
	 * Script and stylesheet loading.
	 *
	 * @var \Sensei_Assets
	 */
	private $assets;

	/**
	 * The mapper for the Course Showcase API.
	 *
	 * @var Course_Showcase_SenseiLMSCom_Mapper
	 */
	private $mapper;

	/**
	 * The response errors from previous status checks.
	 *
	 * @var \WP_Error[]
	 */
	private $status_errors = [];

	/**
	 * Course_Showcase_Listing constructor.
	 */
	private function __construct() {
		// Silence is golden.
	}

	/**
	 * Fetch an instance of the class.
	 *
	 * @return Course_Showcase_Listing
	 */
	public static function instance(): Course_Showcase_Listing {
		if ( ! self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Initialize the class.
	 */
	public static function init(): void {
		$instance = self::instance();
		if ( ! $instance->is_feature_available() ) {
			return;
		}

		add_action( 'init', [ $instance, 'register_post_type' ] );
		add_action( 'init', [ $instance, 'register_post_metas' ] );
		add_action( 'sensei_pro_course_showcase_module_setup', [ $instance, 'register_role_caps' ] );

		if ( current_user_can( 'edit_' . self::POST_TYPE . 's' ) ) {
			add_action( 'admin_head', [ $instance, 'highlight_course_menu' ] );
			add_action( 'current_screen', [ $instance, 'register_blocks' ] );
			add_action( 'current_screen', [ $instance, 'redirect_if_feature_is_not_available' ] );
			add_filter( 'current_screen', [ $instance, 'redirect_from_upsell' ] );
			add_action( 'sensei_course_custom_navigation_tabs', [ $instance, 'modify_course_showcase_tab' ] );
			add_filter( 'sensei_courses_navigation_sidebar', [ $instance, 'add_guide_link' ], 10, 2 );
			add_filter( 'sensei_admin_notices', [ $instance, 'add_notice_after_publishing' ] );
			add_filter( 'sensei_admin_notices', [ $instance, 'add_notice_if_feature_is_not_available' ] );
			add_filter( 'sensei_admin_notices', [ $instance, 'add_eligible_notice' ] );
			add_filter( 'sensei_custom_navigation_allowed_screens', [ $instance, 'add_course_showcase_edit_screen' ] );
			add_filter( 'sensei_notices_screen_ids', [ $instance, 'add_course_showcase_edit_screen' ] );
			add_filter( 'sensei_scripts_allowed_post_types', [ $instance, 'add_course_showcase_post_type' ] );
			add_filter( 'sensei_scripts_allowed_post_type_pages', [ $instance, 'change_course_showcase_post_type_pages' ] );
			add_filter( 'the_title', [ $instance, 'get_correct_title' ], 10, 2 );
			add_filter( 'display_post_states', [ $instance, 'add_eligible_state' ], 10, 2 );
			Course_Showcase_Promote_Action::init(); // Initialised only if user has permissions.
		} else {
			add_filter( 'sensei_course_custom_navigation_tabs', [ $instance, 'remove_course_showcase_tab' ] );
		}
		add_filter( 'user_has_cap', [ $instance, 'block_publish_capability' ], 10, 3 );
		add_action( 'wp_after_insert_post', [ $instance, 'save_post' ] );
		add_filter( 'pre_trash_post', [ $instance, 'trash_post' ], 10, 2 );
		add_action( 'wp_head', [ $instance, 'maybe_add_verification_meta_tag' ] );

		// TODO Move these dependencies to constructor.
		$instance->assets = \Sensei_Pro\Modules\assets_loader( self::MODULE_NAME );
		$instance->mapper = new Course_Showcase_SenseiLMSCom_Mapper();

		if ( is_admin() ) {
			require_once __DIR__ . '/class-course-showcase-cpt-list.php';
			Course_Showcase_CPT_List::init();
		}
	}

	/**
	 * Check if the feature is available.
	 *
	 * @return bool
	 */
	private function is_feature_available(): bool {
		return defined( 'Sensei_Course::SHOWCASE_COURSES_SLUG' );
	}

	/**
	 * Register the custom post type.
	 *
	 * @internal
	 */
	public function register_post_type(): void {
		$singular = __( 'Showcase Course', 'sensei-pro' );
		$plural   = __( 'Showcase Courses', 'sensei-pro' );
		register_post_type(
			self::POST_TYPE,
			[
				'labels'          => [
					'name'               => $singular,
					'singular'           => $singular,
					'plural'             => $plural,
					// translators: Placeholder is the item title/name.
					'edit_item'          => sprintf( __( 'Edit %s', 'sensei-pro' ), $singular ),
					// translators: Placeholder is the plural post type label.
					'search_items'       => sprintf( __( 'Search %s', 'sensei-pro' ), $plural ),
					// translators: Placeholder is the link to the post with instructions to publish listings for the course showcase.
					'not_found'          => sprintf( __( 'No listings for the Sensei Showcase found. <a href="%s">Click here for instructions on how to publish new listings.</a>', 'sensei-pro' ), 'https://senseilms.com/documentation/showcase/?utm_source=plugin_sensei&utm_medium=docs-no-listings&utm_campaign=showcase' ),
					'not_found_in_trash' => __( 'No listings for the Sensei Showcase found in Trash.', 'sensei-pro' ),
				],
				'description'     => __( 'Represents the listing of a course in Sensei Showcase.', 'sensei-pro' ),
				'public'          => false,
				'show_ui'         => true,
				'show_in_menu'    => false,
				'show_in_rest'    => true, // Enables the Gutenberg editor.
				'rewrite'         => false,
				'supports'        => [ 'editor', 'custom-fields' ],
				'template'        => [
					[ 'sensei-pro/showcase' ],
				],
				'template_lock'   => 'all', // Locks the template to prevent users from changing it.
				'capability_type' => self::POST_TYPE, // Enable custom capability.
			]
		);
	}

	/**
	 * Register post metas.
	 *
	 * @internal
	 */
	public function register_post_metas(): void {
		register_post_meta(
			self::POST_TYPE,
			'_is_paid',
			[
				'show_in_rest'  => true,
				'single'        => true,
				'type'          => 'boolean',
				'default'       => false,
				'auth_callback' => '__return_true',
			]
		);

		register_post_meta(
			self::POST_TYPE,
			'_title',
			[
				'show_in_rest'  => true,
				'single'        => true,
				'type'          => 'string',
				'default'       => '',
				'auth_callback' => '__return_true',
			]
		);

		register_post_meta(
			self::POST_TYPE,
			'_excerpt',
			[
				'show_in_rest'  => true,
				'single'        => true,
				'type'          => 'string',
				'default'       => '',
				'auth_callback' => '__return_true',
			]
		);

		register_post_meta(
			self::POST_TYPE,
			'_category',
			[
				'show_in_rest'  => true,
				'single'        => true,
				'type'          => 'string',
				'auth_callback' => '__return_true',
			]
		);

		register_post_meta(
			self::POST_TYPE,
			'_language',
			[
				'show_in_rest'  => true,
				'single'        => true,
				'type'          => 'string',
				'auth_callback' => '__return_true',
			]
		);

		register_post_meta(
			self::POST_TYPE,
			'_media',
			[
				'show_in_rest'  => [
					'schema' => [
						'type'       => 'object',
						'properties' => [
							'id'  => [
								'type' => 'integer',
							],
							'src' => [
								'type' => 'string',
							],
						],
					],
				],
				'single'        => true,
				'type'          => 'object',
				'default'       => [
					'id'  => -1,
					'src' => '',
				],
				'auth_callback' => '__return_true',
			]
		);

		register_post_meta(
			self::POST_TYPE,
			'_senseilmscom_response',
			[
				'show_in_rest'  => false,
				'single'        => true,
				'type'          => 'object',
				'default'       => [
					'id'               => -1,
					'secret_key'       => '',
					'verification_key' => '',
				],
				'auth_callback' => '__return_true',
			]
		);

		register_post_meta(
			self::POST_TYPE,
			'_senseilmscom_error',
			[
				'show_in_rest'  => true,
				'single'        => true,
				'type'          => 'string',
				'default'       => '',
				'auth_callback' => '__return_true',
			]
		);

		register_post_meta(
			self::POST_TYPE,
			'_course',
			[
				'show_in_rest'  => true,
				'single'        => true,
				'type'          => 'integer',
				'default'       => -1,
				'auth_callback' => '__return_true',
			]
		);

		register_post_meta(
			self::POST_TYPE,
			'_senseilmscom_submitted',
			[
				'show_in_rest'  => true,
				'single'        => true,
				'type'          => 'boolean',
				'default'       => false,
				'auth_callback' => '__return_true',
			]
		);
	}

	/**
	 * Return the correct title for a course showcase listing post.
	 *
	 * @param string $title   The post title.
	 * @param int    $post_ID The ID of the post.
	 *
	 * @return string The correct title.
	 */
	public function get_correct_title( $title, $post_ID ) {
		if ( self::POST_TYPE === get_post_type( $post_ID ) ) {
			$title = get_post_meta( $post_ID, '_title', true );
		}
		return $title;
	}

	/**
	 * Add eligible state to the course listing posts.
	 *
	 * @internal
	 *
	 * @param array    $post_states The post states.
	 * @param \WP_Post $post The post object.
	 *
	 * @return array Modified post states
	 */
	public function add_eligible_state( $post_states, $post ): array {
		if (
			is_null( $post )
			|| 'course' !== $post->post_type
			|| ! isset( $_GET['eligible-badges'] ) // phpcs:ignore WordPress.Security.NonceVerification
			|| '1' !== $_GET['eligible-badges'] // phpcs:ignore WordPress.Security.NonceVerification
		) {
			return $post_states;
		}

		$feature_availability = Course_Showcase_Feature_Availability::instance();

		if ( $feature_availability->is_course_eligible( $post->ID ) ) {
			$label = __( 'Eligible', 'sensei-pro' );
			$title = __( 'Eligible to be promoted in Sensei\'s Showcase', 'sensei-pro' );
			$color = '#91e7c5';
		} else {
			$label = __( 'Not eligible', 'sensei-pro' );
			$title = __( 'Not eligible to be promoted in Sensei\'s Showcase', 'sensei-pro' );
			$color = '#ff9a98';
		}

		$style = "display:inline-block;padding:0 5px;border-radius:2px;font-size:12px;font-weight:regular;background-color:$color;color:#26212e;";

		$post_states['showcase-eligible'] = '<span title="' . esc_attr( $title ) . '" style="' . esc_attr( $style ) . '">' . $label . '</span>';

		return $post_states;
	}

	/**
	 * Register role caps for the Course Showcase Listing post type.
	 *
	 * @internal
	 * @return void
	 */
	public function register_role_caps() {
		$caps      = [
			'edit_' . self::POST_TYPE,
			'read_' . self::POST_TYPE,
			'delete_' . self::POST_TYPE,
			'create_' . self::POST_TYPE . 's',
			'edit_' . self::POST_TYPE . 's',
			'read_private_' . self::POST_TYPE . 's',
			'delete_' . self::POST_TYPE . 's',
			'delete_private_' . self::POST_TYPE . 's',
			'delete_published_' . self::POST_TYPE . 's',
			'edit_private_' . self::POST_TYPE . 's',
			'edit_published_' . self::POST_TYPE . 's',
		];
		$role_caps = [
			'teacher'       => $caps,
			'administrator' => array_merge(
				$caps,
				[
					'edit_others_' . self::POST_TYPE . 's',
					'delete_others_' . self::POST_TYPE . 's',
				]
			),
		];

		foreach ( $role_caps as $role_key => $capabilities_array ) {
			// Get the role.
			$role = get_role( $role_key );
			if ( empty( $role ) ) {
				continue;
			}
			foreach ( $capabilities_array as $cap_name ) {
				// If the role exists, add required capabilities for the plugin.
				if ( ! $role->has_cap( $cap_name ) ) {
					$role->add_cap( $cap_name );
				}
			}
		}
	}

	/**
	 * Register showcase blocks.
	 *
	 * @internal
	 *
	 * @param \WP_Screen $current_screen Current screen.
	 *
	 * @return void
	 */
	public function register_blocks( $current_screen ): void {
		if ( ! is_admin() || ! $current_screen || ! $current_screen->is_block_editor || self::POST_TYPE !== $current_screen->post_type ) {
			return;
		}

		$this->register_block_assets();

		register_block_type_from_metadata(
			SENSEI_PRO_PLUGIN_DIR_PATH . 'modules/course-showcase/assets/showcase-block/',
			[
				'editor_script' => 'sensei-showcase-block-editor-script',
				'style'         => 'sensei-showcase-block-style',
			]
		);
	}

	/**
	 * Highlight the Sensei -> Courses menu when the Course Showcase edit page is being viewed.
	 *
	 * @internal
	 * @return void
	 */
	public function highlight_course_menu() {
		global $parent_file, $submenu_file, $_wp_real_parent_file;

		$screen = get_current_screen();

		if ( empty( $screen ) ) {
			return;
		}

		// phpcs:disable WordPress.WP.GlobalVariablesOverride.Prohibited -- Only way to highlight our special pages in menu.
		if ( self::SCREEN_ID === $screen->id ) {
			// Course pages.
			$parent_file              = 'sensei';
			$_wp_real_parent_file[''] = 'sensei';
			$submenu_file             = 'edit.php?post_type=course';
		}
	}

	/**
	 * Adds the edit page for the Course Showcase post type to the array of screens where we should load the
	 * custom Sensei admin styles, or where we should show notices on.
	 *
	 * @param array $screens Array of screens to load custom admin styles, or to show notices on.
	 * @internal
	 * @return array Array of screens where to load custom admin styles, or to show notices on.
	 */
	public function add_course_showcase_edit_screen( $screens ) {
		$screens[] = self::SCREEN_ID;
		return $screens;
	}

	/**
	 * Add the Showcase Courses post type to the list of post types for which we should load the custom Sensei
	 * stylesheets.
	 *
	 * @param array $post_types The list of post types to monitor.
	 * @internal
	 * @return array The updated list of post types to monitor.
	 */
	public function add_course_showcase_post_type( $post_types ) {
		$post_types[] = self::POST_TYPE;
		return $post_types;
	}

	/**
	 * Remove the scripts and styles from the post.php and post-new.php pages when the current post type is the
	 * Course Showcase post type.
	 *
	 * @param array $pages The list of pages to load scripts on.
	 *
	 * @return array The updated list of pages.
	 */
	public function change_course_showcase_post_type_pages( $pages ) {
		global $post_type;
		if ( self::POST_TYPE === $post_type ) {
			$exclude = [ 'post.php', 'post-new.php' ];
			$result  = [];
			foreach ( $pages as $page ) {
				if ( ! in_array( $page, $exclude, true ) ) {
					$result[] = $page;
				}
			}
			return $result;
		}
		return $pages;
	}

	/**
	 * Modify the Showcase Courses tab to update the URL and remove the upsell badge on the Courses page.
	 *
	 * @param array $tabs The list of tabs to show on the Courses page.
	 * @internal
	 * @return array The updated list of tabs to show on the Courses page.
	 */
	public function modify_course_showcase_tab( $tabs ) {
		if ( isset( $tabs[ self::TAB_KEY ] ) ) {
			$tabs[ self::TAB_KEY ]['url']       = admin_url( 'edit.php?post_type=' . self::POST_TYPE );
			$tabs[ self::TAB_KEY ]['screen_id'] = self::SCREEN_ID;

			unset( $tabs[ self::TAB_KEY ]['badge'] );

			$feature_availability  = Course_Showcase_Feature_Availability::instance();
			$unavailability_reason = $feature_availability->get_unavailability_reason();
			if ( Course_Showcase_Feature_Availability::ERROR_HIDE === $unavailability_reason ) {
				// Hide the tab if the unavailability reason is specifically to hide the feature.
				unset( $tabs[ self::TAB_KEY ] );
			}
		}

		return $tabs;
	}

	/**
	 * Remove the Showcase Courses tab from the list of tabs shown on the Courses page.
	 *
	 * @param array $tabs The list of tabs to show on the Courses page.
	 * @internal
	 * @return array The updated list of tabs to show on the Courses page.
	 */
	public function remove_course_showcase_tab( $tabs ) {
		unset( $tabs[ self::TAB_KEY ] );

		return $tabs;
	}

	/**
	 * Add guide link to the course showcase pages.
	 *
	 * @internal
	 *
	 * @param string     $content The sidebar content.
	 * @param \WP_Screen $screen  The screen to check on.
	 *
	 * @return string The sidebar with the guide link.
	 */
	public function add_guide_link( $content, $screen ) {
		// Check if it's the course listing with the eligible badges (coming from the Sensei Home notice).
		$is_course_with_eligible_badges = 'course' === $screen->post_type
			&& isset( $_GET['eligible-badges'] ) // phpcs:ignore WordPress.Security.NonceVerification
			&& '1' === $_GET['eligible-badges']; // phpcs:ignore WordPress.Security.NonceVerification

		// Check if it's the showcase listing.
		$is_showcase_page = self::POST_TYPE === $screen->post_type;

		if ( $is_course_with_eligible_badges || $is_showcase_page ) {
			$url  = 'https://senseilms.com/documentation/showcase?utm_source=plugin_sensei&utm_medium=docs&utm_campaign=showcase';
			$link = '<a class="sensei-custom-navigation__info" href="' . $url . '" target="_blank" rel="noreferrer">' . __( 'Guide To Showcasing Your Course', 'sensei-pro' ) . '</a>';

			return $content . $link;
		}

		return $content;
	}

	/**
	 * Redirects to the Sensei Home if the Showcase Courses feature isn't available.
	 *
	 * @param \WP_Screen $screen The screen to check on.
	 * @internal
	 * @return void
	 */
	public function redirect_if_feature_is_not_available( $screen ) {
		if ( ! is_admin() || self::POST_TYPE !== $screen->post_type ) {
			return;
		}
		$feature_availability = Course_Showcase_Feature_Availability::instance();
		$is_available         = $feature_availability->is_available();
		if ( true === $is_available ) {
			return;
		}
		if ( is_wp_error( $is_available ) ) {
			$error = self::ERROR_CONNECTIVITY;
		} else {
			$error = $feature_availability->get_unavailability_reason();
		}
		$url = 'admin.php?page=sensei';
		if ( Course_Showcase_Feature_Availability::ERROR_HIDE !== $error ) {
			// Only add error information if we aren't intentionally hiding the tab.
			$url .= '&error_nonce=' . wp_create_nonce( self::SCREEN_ID ) . '&error=' . $error;
		}
		wp_safe_redirect( admin_url( $url ) );
		exit;
	}

	/**
	 * Redirects from the upsell link to the showcase listing.
	 *
	 * @param \WP_Screen $screen The screen to check on.
	 * @internal
	 * @return void
	 */
	public function redirect_from_upsell( $screen ) {
		if ( 'admin_page_' . Sensei_Course::SHOWCASE_COURSES_SLUG === $screen->id ) {
			wp_safe_redirect( admin_url( 'edit.php?post_type=' . self::POST_TYPE ) );
			exit;
		}
	}

	/**
	 * Add notice to Sensei Home if the redirect is coming from the Showcase Courses page.
	 *
	 * @internal
	 *
	 * @param array $notices Array of notices to show.
	 *
	 * @return array Array of notices to show.
	 */
	public function add_notice_if_feature_is_not_available( $notices ) {
		if ( get_current_screen() === null || get_current_screen()->id !== \Sensei_Home::SCREEN_ID ) {
			return $notices;
		}
		if ( ! isset( $_GET['error'], $_GET['error_nonce'] ) ) {
			return $notices;
		}
		if ( ! check_admin_referer( self::SCREEN_ID, 'error_nonce' ) ) {
			return $notices;
		}
		switch ( $_GET['error'] ) {
			case self::ERROR_CONNECTIVITY:
				$message = __( 'We could not reach SenseiLMS.com. Try again later.', 'sensei-pro' );
				break;
			case Course_Showcase_Feature_Availability::ERROR_TEMPORARY:
				$message = __( 'The Sensei Showcase feature is temporarily not available.', 'sensei-pro' );
				break;
			case Course_Showcase_Feature_Availability::ERROR_UPDATE_REQUIRED:
				$message = __( 'It is necessary to update Sensei Pro to use the Sensei Showcase.', 'sensei-pro' );
				break;
			case Course_Showcase_Feature_Availability::ERROR_LICENSE_REQUIRED:
			case Course_Showcase_Feature_Availability::ERROR_LICENSE_INVALID:
				$message = __( 'It is necessary to have a valid license to use the Sensei Showcase feature.', 'sensei-pro' );
				break;
			default:
				$message = __( 'Unknown error while contacting the Sensei Showcase API.', 'sensei-pro' );
		}
		$notices['sensei-showcase-error-notice'] = [
			'level'       => 'error',
			'type'        => 'user',
			'heading'     => __( 'Sensei Showcase', 'sensei-pro' ),
			'icon'        => 'sensei',
			'message'     => $message,
			'conditions'  => [
				[
					'type'    => 'screens',
					'screens' => [ \Sensei_Home::SCREEN_ID ],
				],
			],
			'actions'     => [],
			'dismissible' => false,
		];

		return $notices;
	}

	/**
	 * Add a notice if the user has just submitted a listing to SenseiLMS.com.
	 *
	 * @param array $notices The notices to show.
	 *
	 * @return array The updated array of notices to show.
	 */
	public function add_notice_after_publishing( $notices ) {
		if ( get_current_screen() === null || get_current_screen()->id !== self::SCREEN_ID ) {
			return $notices;
		}
		if ( ! isset( $_GET['publish_nonce'] ) ) {
			return $notices;
		}
		if ( ! check_admin_referer( self::POST_TYPE, 'publish_nonce' ) ) {
			return $notices;
		}
		$notices['sensei-showcase-publish-notice'] = [
			'level'       => 'success',
			'type'        => 'user',
			'heading'     => __( 'Sensei Showcase', 'sensei-pro' ),
			'icon'        => 'sensei',
			'message'     => __( 'Your listing has been successfully submitted to the Sensei Showcase.', 'sensei-pro' ),
			'conditions'  => [
				[
					'type'    => 'screens',
					'screens' => [ self::SCREEN_ID ],
				],
			],
			'actions'     => [],
			'dismissible' => true,
		];
		return $notices;
	}

	/**
	 * Add Showcase eligible notice when the first course is eligible to be
	 * promoted.
	 *
	 * @internal
	 *
	 * @param array $notices Array of notices to show.
	 *
	 * @return array Array of notices to show.
	 */
	public function add_eligible_notice( $notices ) {
		$feature_availability = Course_Showcase_Feature_Availability::instance();

		// Skip if feature is not available.
		if ( ! $feature_availability->is_available() ) {
			return $notices;
		}

		// Skip if a course was already promoted.
		$showcase_count = wp_count_posts( self::POST_TYPE );
		if (
			$showcase_count->publish > 0
			|| $showcase_count->draft > 0
			|| $showcase_count->pending > 0
		) {
			return $notices;
		}

		// Skip if there's no eligible courses to promote.
		if ( ! $this->has_eligible_courses( $feature_availability ) ) {
			return $notices;
		}

		$notices['showcase-first-course-eligible'] = [
			'icon'        => 'sensei',
			'level'       => 'success',
			'type'        => 'user',
			'message'     => __( 'Great news! You are eligible for promoting your course on Sensei!', 'sensei-pro' ),
			'conditions'  => [
				[
					'type'    => 'screens',
					'screens' => [ \Sensei_Home::SCREEN_ID ],
				],
			],
			'dismissible' => true,
			'info_link'   => [
				'label' => __( 'Learn about Course Promotion', 'sensei-pro' ),
				'url'   => 'https://senseilms.com/documentation/showcase/',
			],
			'actions'     => [
				[
					'label' => __( 'Promote Course', 'sensei-pro' ),
					'url'   => admin_url( 'edit.php?post_type=course&eligible-badges=1' ),
				],
			],
		];
		return $notices;
	}

	/**
	 * Check if site contains an eligible course.
	 *
	 * @param Course_Showcase_Feature_Availability $feature_availability Feature availability instance.
	 *
	 * @return boolean
	 */
	private function has_eligible_courses( $feature_availability ) {
		$has_eligible_courses = get_transient( self::HAS_ELIGIBLE_COURSES_TRANSIENT_KEY );

		if ( false !== $has_eligible_courses ) {
			return '1' === $has_eligible_courses;
		}

		$course_ids_query = new WP_Query(
			[
				'post_type'      => 'course',
				'posts_per_page' => 50, // We get only 50 to avoid a big loop in sites with many courses.
				'post_status'    => 'publish',
				'fields'         => 'ids',
			]
		);

		foreach ( $course_ids_query->posts as $course_id ) {
			if ( $feature_availability->is_course_eligible( $course_id ) ) {
				$has_eligible_courses = true;
				break;
			}
		}

		set_transient(
			self::HAS_ELIGIBLE_COURSES_TRANSIENT_KEY,
			$has_eligible_courses ? '1' : '0',
			HOUR_IN_SECONDS
		);

		return $has_eligible_courses;
	}

	/**
	 * Register block assets.
	 */
	private function register_block_assets() {
		wp_register_style( 'sensei-showcase-card-style', 'https://senseilms.com/wp-content/plugins/senseilms-com-plugins/build/showcase/card.css', [], SENSEI_PRO_VERSION );

		$editor_script_handle = 'sensei-showcase-block-editor-script';
		$this->assets->register( $editor_script_handle, 'showcase-block/index.js' );
		$this->assets->register( 'sensei-showcase-block-style', 'showcase-block/style.css', [ 'sensei-showcase-card-style' ] );

		$this->add_showcase_block_script_variables( $editor_script_handle );
	}

	/**
	 * Add showcase block script variables.
	 *
	 * @param string $script_handle The script handle to attach the inline script.
	 */
	private function add_showcase_block_script_variables( $script_handle ) {
		$redirect_url    = admin_url( 'edit.php?post_type=' . self::POST_TYPE . '&publish_nonce=' . wp_create_nonce( self::POST_TYPE ) );
		$showcase_editor = [
			'studentsNumber'  => $this->get_students_number_scaled(),
			'categoryOptions' => $this->get_category_options(),
			'languageOptions' => $this->get_language_options(),
			'siteLanguage'    => strtolower( get_locale() ),
			'redirectUrl'     => $redirect_url,
		];

		wp_add_inline_script(
			$script_handle,
			sprintf( 'window.sensei = window.sensei || {}; window.sensei.showcaseEditor=%s;', wp_json_encode( $showcase_editor ) ),
			'before'
		);
	}

	/**
	 * Get students number from a course associated to the current listing.
	 *
	 * @return int Number of students - 0 in case of error.
	 */
	private function get_students_number() {
		$students_number = 0;
		$post_id         = isset( $_GET['post'] ) ? intval( $_GET['post'] ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

		if ( ! $post_id ) {
			return $students_number;
		}

		$course_id = get_post_meta( $post_id, '_course', true );

		if ( ! $course_id ) {
			return $students_number;
		}

		return \Sensei_Utils::sensei_check_for_activity(
			[
				'post_id' => $course_id,
				'type'    => 'sensei_course_status',
				'status'  => 'any',
			]
		);
	}

	/**
	 * Get the number of students enrolled in the course using a general scale.
	 *
	 * @return string The number of students, but rounded in a general scale.
	 */
	private function get_students_number_scaled() {
		$students_number = $this->get_students_number();

		foreach ( array_reverse( self::STUDENTS_NUMBER_SCALE_MINIMUMS ) as $min_scale ) {
			if ( $students_number >= $min_scale ) {
				return sprintf( '%s+', number_format( $min_scale ) );
			}
		}

		return '0';
	}

	/**
	 * Get category options for a showcase.
	 *
	 * @return array Category options, empty in case of error.
	 */
	private function get_category_options() {
		$category_options = get_transient( self::CATEGORY_OPTIONS_TRANSIENT_KEY );

		if ( false !== $category_options ) {
			return $category_options;
		}

		$categories_endpoint = $this->get_senseilms_com_api_url() . '/wp/v2/slc_course_showcase_category?_fields=name,slug&per_page=100';
		$categories          = $this->request( $categories_endpoint );

		if ( is_wp_error( $category_options ) ) {
			return [];
		}

		$category_options = array_map(
			function ( $category ) {
				return [
					'label' => wp_specialchars_decode( $category['name'] ),
					'value' => $category['slug'],
				];
			},
			$categories
		);

		set_transient( self::CATEGORY_OPTIONS_TRANSIENT_KEY, $category_options, DAY_IN_SECONDS );

		return $category_options;
	}

	/**
	 * Get language options for a showcase.
	 *
	 * @return array Language options, empty in case of error.
	 */
	private function get_language_options() {
		$languages = get_transient( self::LANGUAGE_OPTIONS_TRANSIENT_KEY );

		if ( false !== $languages ) {
			return $languages;
		}

		require_once ABSPATH . 'wp-admin/includes/translation-install.php';
		$translations = wp_get_available_translations();

		$languages = array_map(
			function ( $translation ) {
				return [
					'label' => wp_specialchars_decode( $translation['native_name'] ),
					'value' => strtolower( $translation['language'] ),
				];
			},
			array_values( $translations )
		);

		// Similar to WordPress core, it adds English as a hard coded option.
		array_unshift(
			$languages,
			[
				'label' => 'English (United States)',
				'value' => 'en_us',
			]
		);

		set_transient( self::LANGUAGE_OPTIONS_TRANSIENT_KEY, $languages, DAY_IN_SECONDS );

		return $languages;
	}

	/**
	 * Block publish_posts capability for showcase listings.
	 * This prevents users from publishing these custom post types.
	 * They also view a "Submit for Review" button instead of "Publish".
	 *
	 * @param array    $allcaps All the capabilities.
	 * @param string[] $caps    Required primitive capabilities for the requested capability.
	 * @param array    $args
	 * @internal
	 * @return array
	 */
	public function block_publish_capability( $allcaps, $caps, $args ) {

		$post_id = $args[2] ?? 0;

		$post_type = get_post_type( $post_id );

		// Ignore revisions.
		$obj = get_post_type_object( $post_type );
		if ( ! $obj || 'revision' === $obj->name ) {
			return $allcaps;
		}

		// Ignore unsupported post types.
		if ( self::POST_TYPE !== $post_type ) {
			return $allcaps;
		}

		$allcaps[ $obj->cap->publish_posts ] = false;

		return $allcaps;
	}

	/**
	 * Handle the Course Showcase submission logic when saving the post.
	 *
	 * @param int $post_ID The data to be saved.
	 * @internal
	 * @return void
	 */
	public function save_post( $post_ID ) {
		if ( ! $this->should_save_post( $post_ID ) ) {
			return;
		}

		$listing                            = $this->mapper->map_listing( $post_ID );
		$response                           = $this->mapper->validate_listing( $listing );
		[ $endpoint, $is_update, $request ] = $this->get_save_request_data( $post_ID, $listing );
		if ( null === $response ) {
			// If the listing is valid, submit the request.
			$response = $this->request( $endpoint, $request );
		}

		// Delete the listing status cache in case it changes during the process.
		$this->delete_listing_status( $post_ID );

		$error = '';
		if ( is_wp_error( $response ) ) {
			if ( ! $is_update ) {
				remove_action( 'wp_after_insert_post', [ $this, 'save_post' ] );
				// If we found an error while updating the post, revert it back to draft.
				$update_result = wp_update_post(
					[
						'ID'          => $post_ID,
						'post_status' => 'draft',
					],
					true
				);
				if ( is_wp_error( $update_result ) ) {
					$response->merge_from( $update_result );
				}
				add_action( 'wp_after_insert_post', [ $this, 'save_post' ] );
			}
			// Save the error that happened on the post meta.
			$error = $response->get_error_message();
		} elseif ( ! isset( $response['listing'] ) ) {
			$error = __( 'The response from SenseiLMS.com was not valid.', 'sensei-pro' );
		} else {
			$result = $this->mapper->decode_listing_response( $response );

			if ( ! $is_update ) {
				update_post_meta( $post_ID, '_senseilmscom_response', $result );
				update_post_meta( $post_ID, '_senseilmscom_submitted', true );
			}

			$this->store_listing_status( $post_ID, $response );

			/**
			 * Fires after a Course Showcase listing is submitted.
			 *
			 * @since 1.12.0
			 * @hook sensei_pro_course_showcase_submitted
			 *
			 * @param {int}   $post_ID   The post ID of the listing.
			 * @param {bool}  $is_update If the listing is being updated or not.
			 * @param {Array} $result    The response from SenseiLMS.com.
			 */
			do_action( 'sensei_pro_course_showcase_submitted', $post_ID, $is_update, $result );
		}

		update_post_meta( $post_ID, '_senseilmscom_error', $error );
	}

	/**
	 * Returns if we should save the post or not.
	 *
	 * @param int $post_ID The Post ID being processed.
	 *
	 * @return bool If the post should be saved or not.
	 */
	private function should_save_post( $post_ID ) {
		// We don't handle updates when there's no post_ID or post defined.
		if ( empty( $post_ID ) ) {
			return false;
		}
		// We don't handle updates from other post types.
		if ( self::POST_TYPE !== get_post_type( $post_ID ) ) {
			return false;
		}
		// We don't handle auto saves and post revisions.
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return false;
		}
		if ( is_int( wp_is_post_revision( $post_ID ) ) ) {
			return false;
		}
		if ( is_int( wp_is_post_autosave( $post_ID ) ) ) {
			return false;
		}
		// We only handle updates to posts that are pending review.
		if ( 'pending' !== get_post_status( $post_ID ) ) {
			return false;
		}
		return true;
	}

	/**
	 * Returns the endpoint and request data for the save request for the endpoint.
	 *
	 * @param int   $post_ID The Post ID being processed.
	 * @param array $listing The listing data to be submitted to the API.
	 *
	 * @return array An array containing the endpoint and the request array to use.
	 */
	private function get_save_request_data( $post_ID, array $listing ) {
		$endpoint = $this->get_senseilms_com_api_url() . '/course-showcase/v1/listing';
		$result   = get_post_meta( $post_ID, '_senseilmscom_response', true );
		$request  = [
			'method'  => 'POST',
			'headers' => [
				'Content-Type' => 'application/json',
			],
			'body'    => wp_json_encode(
				[
					'listing' => $listing,
					'license' => $this->mapper->get_license_parameter(),
				]
			),
			'timeout' => 30,
		];

		$is_update = -1 !== $result['id'];
		if ( $is_update ) {
			$listing_id                          = $result['id'];
			$endpoint                           .= '/' . $listing_id;
			$request['method']                   = 'PUT';
			$request['headers']['Authorization'] = $this->get_authorization_header( $result['secret_key'], 'put-listing-' . $listing_id );
		}
		return [ $endpoint, $is_update, $request ];
	}

	/**
	 * Handle the Course Showcase deactivation when trashing the post.
	 *
	 * @param bool|null $trash Whether to go forward with trashing.
	 * @param WP_Post   $post  Post object.
	 *
	 * @return bool|null Whether to go forward with trashing.
	 */
	public function trash_post( $trash, $post ) {
		if ( self::POST_TYPE !== $post->post_type ) {
			return $trash;
		}

		$senseilmscom_response = get_post_meta( $post->ID, '_senseilmscom_response', true );
		$feature_availability  = Course_Showcase_Feature_Availability::instance();
		$error                 = '';

		if (
			empty( $senseilmscom_response )
			|| empty( $senseilmscom_response['secret_key'] )
			|| empty( $senseilmscom_response['id'] )
			|| ! $feature_availability->is_available()
		) {
			$error = __( 'There was an error deactivating the showcase.', 'sensei-pro' );
		} else {
			$endpoint             = $this->get_senseilms_com_api_url() . '/course-showcase/v1/listing/' . $senseilmscom_response['id'];
			$authorization_header = $this->get_authorization_header( $senseilmscom_response['secret_key'], 'delete-listing-' . $senseilmscom_response['id'] );
			$request              = [
				'method'  => 'DELETE',
				'headers' => [
					'Authorization' => $authorization_header,
				],
			];

			$response = $this->request( $endpoint, $request );

			if ( ! is_wp_error( $response ) && $response['deleted'] ) {
				delete_post_meta( $post->ID, '_senseilmscom_response' );
			} else {
				$error = __( 'There was an error deactivating the showcase.', 'sensei-pro' );
				$trash = false;
			}
		}

		update_post_meta( $post->ID, '_senseilmscom_error', $error );

		return $trash;
	}

	/**
	 * Helper method to handle errors returned by the Sensei LMS.com API.
	 *
	 * @param string $endpoint The endpoint to call.
	 * @param array  $request  The request properties to use on the API call.
	 *
	 * @return array|\WP_Error The response from the API or a WP_Error if something went wrong.
	 */
	private function request( $endpoint, $request = [] ) {
		if ( ! isset( $request['timeout'] ) ) {
			// Extend the timeout to 15 seconds.
			$request['timeout'] = 15;
		}

		$response = wp_safe_remote_request( $endpoint, $request );
		return $this->mapper->decode_response( $response );
	}

	/**
	 * Get the authorization header for the request to the SenseiLMS.com APIs.
	 *
	 * @param string $secret_key The secret key to use when contacting the SenseiLMS.com API.
	 * @param string $action     The action being ran.
	 *
	 * @return string The value of the Authorization header.
	 */
	private function get_authorization_header( $secret_key, $action ) {
		/**
		 * Filter to change the algorithm used to generate the authorization header.
		 * The default is sha256.
		 *
		 * @since  1.12.0
		 * @hook   sensei_pro_course_showcase_authorization_algorithm
		 * @param  {string} $algorithm The algorithm to use.
		 * @return {string} The algorithm to use in the authorization header.
		 */
		$algorithm = apply_filters( 'sensei_pro_course_showcase_authorization_algorithm', 'sha256' );
		$time      = time();
		$signature = hash_hmac( $algorithm, $action . '-' . $time, $secret_key );
		return $time . ':' . $algorithm . ':' . $signature;
	}

	/**
	 * Retrieves a listing given the course ID.
	 *
	 * @param int $course_id The course ID.
	 *
	 * @return \WP_Post|null
	 */
	public function get_listing( int $course_id ): ?\WP_Post {
		$listings = get_posts(
			[
				'post_type'      => self::POST_TYPE,
				'post_status'    => 'any',
				'posts_per_page' => 1,
				'meta_query'     => [
					[
						'key'   => '_course',
						'value' => $course_id,
					],
				],
			]
		);
		return $listings[0] ?? null;
	}

	/**
	 * Creates a listing given the meta values.
	 *
	 * @param array $metas     The meta values to save.
	 * @return int|\WP_Error
	 */
	public function create_listing( array $metas ) {
		$listing_id = wp_insert_post(
			[
				'post_type'    => self::POST_TYPE,
				'post_status'  => 'draft',
				'post_content' => serialize_block(
					[
						'blockName'    => 'sensei-pro/showcase',
						'innerContent' => [],
						'attrs'        => [],
					]
				),
				'meta_input'   => $metas,
			]
		);

		return $listing_id;
	}

	/**
	 * Get the status of a listing.
	 *
	 * @param int $listing_id The listing ID.
	 *
	 * @return array|\WP_Error The listing status array (`status` and `status_code`) or a WP_Error if something went wrong.
	 */
	public function get_listing_status( $listing_id ) {
		if ( ! $this->has_been_submitted( $listing_id ) ) {
			return Course_Showcase_SenseiLMSCom_Mapper::NOT_SUBMITTED_STATUS;
		}

		$status = get_transient( $this->get_status_cache_key( $listing_id ) );
		if ( false !== $status ) {
			return $status;
		}

		// If a request for this listing has already failed in this request, return the error.
		if ( isset( $this->status_errors[ $listing_id ] ) ) {
			return $this->status_errors[ $listing_id ];
		}

		$request_args = $this->get_listing_request_args( $listing_id );
		if ( ! $request_args ) {
			$this->status_errors[ $listing_id ] = new \WP_Error( 'sensei_pro_course_showcase_missing_listing_id' );

			return $this->status_errors[ $listing_id ];
		}

		$listing_details_response_raw = wp_remote_request(
			$request_args['url'],
			[
				'method'  => $request_args['type'],
				'headers' => $request_args['headers'],
			]
		);

		$listing_details_response = $this->mapper->decode_response( $listing_details_response_raw );
		if ( $listing_details_response instanceof \WP_Error ) {
			return $listing_details_response;
		}

		return $this->store_listing_status( $listing_id, $listing_details_response );
	}

	/**
	 * Have a listing check-in with SenseiLMS.com to let it know it is still active.
	 *
	 * @param int $listing_id The listing ID.
	 *
	 * @return true|\WP_Error
	 */
	public function listing_check_in( int $listing_id ) {
		$listing = get_post( $listing_id );

		if (
			! $listing
			|| self::POST_TYPE !== $listing->post_type
			|| empty( $listing->_senseilmscom_response['id'] )
		) {
			return new \WP_Error( 'sensei_pro_course_showcase_invalid_listing_id' );
		}

		$senseilmscom_response = $listing->_senseilmscom_response;
		$endpoint              = sprintf( License_Manager::get_api_url() . '/course-showcase/v1/listing/%s/check', $senseilmscom_response['id'] );

		$request = [
			'method'  => 'POST',
			'timeout' => 30,
			'headers' => [
				'Content-Type'  => 'application/json',
				'Authorization' => $this->get_authorization_header( $senseilmscom_response['secret_key'] ?? '', 'post-listing-' . $senseilmscom_response['id'] ),
			],
			'body'    => wp_json_encode(
				[
					'listing' => $this->mapper->get_check_in_fields( $listing ),
				]
			),
		];

		$response = $this->request( $endpoint, $request );
		if ( $response instanceof \WP_Error ) {
			return $response;
		}

		if ( ! isset( $response['listing'] ) ) {
			return new \WP_Error( 'sensei_pro_course_showcase_invalid_response' );
		}

		// Take this opportunity to update the listing status cache.
		$this->store_listing_status( $listing_id, $response );

		return true;
	}

	/**
	 * Check if a listing has been submitted to SenseiLMS.com.
	 *
	 * @param int $listing_id The listing ID.
	 *
	 * @return bool True if the listing has been submitted.
	 */
	public function has_been_submitted( $listing_id ) {
		$listing = get_post( $listing_id );

		if ( ! $listing || self::POST_TYPE !== $listing->post_type ) {
			return false;
		}

		if ( 'draft' === $listing->post_status ) {
			return false;
		}

		$listing_details = get_post_meta( $listing_id, '_senseilmscom_response', true );

		return ! empty( $listing_details['id'] );
	}

	/**
	 * Get the statuses for multiple listings.
	 *
	 * @param array $listing_ids The listing post IDs.
	 *
	 * @return array The listing statuses.
	 */
	public function get_listing_statuses( $listing_ids ) {
		$requests = [];
		$statuses = [];

		foreach ( $listing_ids as $listing_id ) {
			$course_listing = get_post( $listing_id );
			if ( ! $course_listing || self::POST_TYPE !== $course_listing->post_type ) {
				continue;
			}

			if ( ! $this->has_been_submitted( $listing_id ) ) {
				$statuses[ $listing_id ] = Course_Showcase_SenseiLMSCom_Mapper::NOT_SUBMITTED_STATUS;
				continue;
			}

			$status = get_transient( $this->get_status_cache_key( $listing_id ) );
			if ( false !== $status ) {
				$statuses[ $listing_id ] = $status;
				continue;
			}

			if ( isset( $this->status_errors[ $listing_id ] ) ) {
				$statuses[ $listing_id ] = $this->status_errors[ $listing_id ];
				continue;
			}

			$request_args = $this->get_listing_request_args( $listing_id );
			if ( ! $request_args ) {
				continue;
			}

			$requests[ $listing_id ] = $request_args;
		}

		if ( ! empty( $requests ) ) {
			// WordPress 6.2 updated the library and moved it out of the root namespace. We'll need this as long as we support 6.1 and before.
			$requests_class = class_exists( '\WpOrg\Requests\Requests' ) ? '\WpOrg\Requests\Requests' : '\Requests';
			$requests_class::request_multiple(
				$requests,
				[
					'complete' => function ( $response, $listing_id ) use ( &$statuses ) {
						$decoded_response = $this->mapper->decode_response( $response );
						if ( $decoded_response instanceof \WP_Error ) {
							$status[ $listing_id ] = $decoded_response;

							// Cache the error in memory for future requests.
							$this->status_errors[ $listing_id ] = $decoded_response;
							return;
						}

						$statuses[ $listing_id ] = $this->store_listing_status( $listing_id, $decoded_response );
					},
				]
			);
		}

		return $statuses;
	}

	/**
	 * Parse the response for a listing for the status and cache it.
	 *
	 * @param int   $listing_id       The listing ID.
	 * @param array $listing_response The response from the SenseiLMS.com API.
	 *
	 * @return array The status array containing the `status` and `status_code`.
	 */
	private function store_listing_status( $listing_id, $listing_response ) {
		$status = $this->mapper->map_listing_status( $listing_response );

		set_transient( $this->get_status_cache_key( $listing_id ), $status, $this->get_status_cache_duration( $status['status'] ) );

		return $status;
	}

	/**
	 * Delete a listing status cache.
	 *
	 * @param int $listing_id       The listing ID.
	 */
	private function delete_listing_status( $listing_id ) {
		delete_transient( $this->get_status_cache_key( $listing_id ) );
	}

	/**
	 * Get the listing request arguments.
	 *
	 * @param int $post_ID The post ID.
	 *
	 * @return array|false
	 */
	public function get_listing_request_args( $post_ID ) {
		$result = get_post_meta( $post_ID, '_senseilmscom_response', true );
		if ( ! $result || empty( $result['id'] ) || empty( $result['secret_key'] ) ) {
			return false;
		}

		$listing_id = (int) $result['id'];

		$endpoint = License_Manager::get_api_url() . '/course-showcase/v1/listing/' . $listing_id;

		/**
		 * Filter the request arguments for getting a listing.
		 *
		 * @since 1.13.0
		 *
		 * @hook sensei_pro_course_showcase_get_listing_request_args
		 *
		 * @param {array} $args    The request arguments.
		 * @param {int}   $post_ID The post ID.
		 * @param {array} $result  The result from the SenseiLMS.com API.
		 *
		 * @return {array} The request arguments.
		 */
		return apply_filters(
			'sensei_pro_course_showcase_get_listing_request_args',
			[
				'url'     => $endpoint,
				'type'    => 'GET',
				'headers' => [
					'Content-Type'  => 'application/json',
					'Authorization' => $this->get_authorization_header( $result['secret_key'], 'get-listing-' . $listing_id ),
				],
			],
			$post_ID,
			$result
		);
	}

	/**
	 * Get the duration to cache the listing status based on the current status.
	 *
	 * @param string $status The listing status.
	 *
	 * @return int
	 */
	private function get_status_cache_duration( $status ) {
		if ( 'pending' === $status ) {
			return HOUR_IN_SECONDS;
		}

		return DAY_IN_SECONDS;
	}

	/**
	 * Get the listing's status cache key.
	 *
	 * @param int $post_ID The post ID.
	 *
	 * @return string
	 */
	private function get_status_cache_key( $post_ID ) {
		return 'sensei_course_showcase_listing_status_' . $post_ID;
	}

	/**
	 * Adds verification meta tag to the head of the course page if needed.
	 *
	 * @return void
	 */
	public function maybe_add_verification_meta_tag() {
		if ( ! is_singular( 'course' ) ) {
			return;
		}
		$course = get_post();
		if ( ! $course ) {
			return;
		}
		$listing = $this->get_listing( $course->ID );
		if ( $listing ) {
			$senseilmscom_response = get_post_meta( $listing->ID, '_senseilmscom_response', true );
			if ( isset( $senseilmscom_response['verification_key'] ) && ! empty( $senseilmscom_response['verification_key'] ) ) {
				echo '<meta name="senseilms-com-verification" content="' . esc_attr( $senseilmscom_response['verification_key'] ) . '" />';
			}
		}
	}

	/**
	 * Returns senseilms.com API URL.
	 *
	 * @return string
	 */
	private function get_senseilms_com_api_url() {
		return License_Manager::get_api_url();
	}
}
