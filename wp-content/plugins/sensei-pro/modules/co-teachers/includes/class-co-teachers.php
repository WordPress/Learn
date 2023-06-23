<?php
/**
 * Sensei Co-Teachers extension.
 *
 * @package sensei-pro
 * @since   1.9.0
 */

namespace Sensei_Pro_Co_Teachers;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Sensei Co-Teachers extension main class.
 */
class Co_Teachers {
	const SENSEI_MIN_VERSION = '4.9.0';

	/**
	 * The module name.
	 */
	const MODULE_NAME = 'co-teachers';

	/**
	 * Key used as user meta that contains the Course ID (WP_Post) the user is a co-teacher for.
	 */
	const COTEACHER_META_KEY = 'sensei_coteaching';

	/**
	 * List of supported roles. Keep in sync with `modules/co-teachers/assets/js/admin-co-teachers-meta-box/index.js`.
	 */
	const SUPPORTED_ROLES = [ 'teacher' ];

	/**
	 * Class instance.
	 *
	 * @var Co_Teachers
	 */
	private static $instance;

	/**
	 * Cache of course user IDs.
	 *
	 * @var int[][]
	 */
	private $course_user_id_cache = [];

	/**
	 * Retrieve the Co_Teachers instance.
	 */
	public static function instance(): Co_Teachers {
		if ( ! self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Class constructor.
	 */
	private function __construct() {
		// Silence is golden.
	}

	/**
	 * Initializes the class and adds all filters and actions.
	 */
	public static function init() {
		self::instance();

		// Check to make sure we've met the minimum Sensei version for this feature.
		if ( ! function_exists( 'Sensei' ) || ! version_compare( self::SENSEI_MIN_VERSION, \Sensei()->version, '<=' ) ) {
			return;
		}

		// Requirements.
		require_once dirname( __FILE__ ) . '/class-co-teachers-meta-box.php';
		require_once dirname( __FILE__ ) . '/class-co-teachers-permissions.php';
		require_once dirname( __FILE__ ) . '/class-co-teachers-quiz-handler.php';
		require_once dirname( __FILE__ ) . '/class-co-teachers-compat.php';
		require_once dirname( __FILE__ ) . '/class-co-teachers-rest-api.php';
		Co_Teachers_Meta_Box::init();
		Co_Teachers_Permissions::init();
		Co_Teachers_Quiz_Handler::init();
		Co_Teachers_Compat::init();
		Co_Teachers_Rest_Api::init();
		add_action( 'set_user_role', [ self::instance(), 'maybe_remove_coteacher_metas_when_user_role_changes' ], 10, 3 );
	}

	/**
	 * Get all the co-teachers (Users) IDs for the given course ID.
	 *
	 * @param int $course_id The Course ID.
	 *
	 * @return int[]
	 */
	public function get_course_coteachers_ids( $course_id ) {
		if ( isset( $this->course_user_id_cache[ $course_id ] ) ) {
			return $this->course_user_id_cache[ $course_id ];
		}

		$user_ids = array_map(
			'intval',
			get_users(
				[
					'meta_key'   => self::COTEACHER_META_KEY,
					'meta_value' => $course_id,
					'fields'     => 'ID',
				]
			)
		);

		$this->course_user_id_cache[ $course_id ] = $user_ids;

		return $user_ids;
	}

	/**
	 * If the given post is a Course, Lesson, or Quiz, calculates the Course ID
	 * and return a list of its co-teacher's IDs.
	 *
	 * @param int $post_id The post ID.
	 *
	 * @return int[]|null The co-teacher's IDs or null if the post is not a Course, Lesson, or Quiz.
	 */
	public function get_coteachers_ids( $post_id ): array {
		// Depending on `$post_type` extract valid co-author IDs.
		$course_id = null;
		switch ( get_post_type( $post_id ) ) {
			case 'course':
				$course_id = $post_id;
				break;
			case 'lesson':
				$course_id = $post_id ? Sensei()->lesson->get_course_id( $post_id ) : null;
				break;
			case 'quiz':
				$lesson_id = $post_id ? Sensei()->quiz->get_lesson_id( $post_id ) : null;
				$course_id = $lesson_id ? Sensei()->lesson->get_course_id( $lesson_id ) : null;
				break;
		}

		// Course could not be found – return empty array.
		if ( is_null( $course_id ) ) {
			return [];
		}

		return $this->get_course_coteachers_ids( $course_id );
	}

	/**
	 * Check if a given `$user_id` is a co-teacher for the given `$post`.
	 *
	 * @param int|string $user_id The user ID.
	 * @param \WP_Post   $post    The post.

	 * @return bool
	 */
	public function is_coteacher( $user_id, $post ) {
		$coauthor_ids = $this->get_coteachers_ids( $post->ID );

		return in_array( intval( $user_id ), $coauthor_ids, true );
	}


	/**
	 * Get all the courses IDs for the given co-teacher ID.
	 *
	 * @param int $user_id The co-teacher (\WP_User) ID.
	 *
	 * @return int[] The courses IDs.
	 */
	public function get_coteacher_courses_ids( $user_id ) {
		$coteacher_courses_ids = get_user_meta( $user_id, self::COTEACHER_META_KEY, false );

		if ( empty( $coteacher_courses_ids ) ) {
			return [];
		}

		return array_filter( $coteacher_courses_ids, 'intval' );
	}

	/**
	 * Sets the new co-teachers IDs to the given course (WP_Post).
	 *
	 * @param \WP_Post|int $course        The course WP_Post instance or ID.
	 * @param int[]        $coteacher_ids The new co-teachers IDs.
	 */
	public function set_course_coteachers_ids( $course, $coteacher_ids ) {
		$course    = get_post( $course );
		$course_id = $course->ID;

		// Make co-teacher ids unique.
		$coteacher_ids = array_unique( $coteacher_ids );

		// Remove current co-teachers that are not included in the new set.
		$current_coteachers = $this->get_course_coteachers_ids( $course_id );
		$removed_coteachers = array_diff( $current_coteachers, $coteacher_ids );
		foreach ( $removed_coteachers as $removed_coteacher_id ) {
			$this->delete_coteacher( $course_id, $removed_coteacher_id );
		}

		// Add new co-teachers.
		$current_coteachers[] = intval( $course->post_author ); // Trick: We add current author, so it is not added as co-teacher.
		$new_coteachers       = array_diff( $coteacher_ids, $current_coteachers );
		$new_coteachers       = $this->filter_valid_coteachers_ids( $new_coteachers );
		foreach ( $new_coteachers as $new_coteacher_id ) {
			$this->add_coteacher( $course_id, $new_coteacher_id );
		}

		unset( $this->course_user_id_cache[ $course_id ] );
	}

	/**
	 * Registers a WP_User ID as a co-teacher for the given course ID.
	 *
	 * @param int $course_id The course ID.
	 * @param int $user_id   The co-teacher ID (WP_User ID).
	 */
	private function add_coteacher( $course_id, $user_id ) {
		add_user_meta( $user_id, self::COTEACHER_META_KEY, intval( $course_id ) );

		unset( $this->course_user_id_cache[ $course_id ] );
	}

	/**
	 * Unregisters a WP_User ID as a co-teacher for the given course ID.
	 *
	 * @param int $course_id The course ID.
	 * @param int $user_id   The co-teacher ID (WP_User ID).
	 */
	private function delete_coteacher( $course_id, $user_id ) {
		delete_user_meta( $user_id, self::COTEACHER_META_KEY, $course_id );

		unset( $this->course_user_id_cache[ $course_id ] );
	}

	/**
	 * Given a list of user IDs checks for its existance and confirms have a supported role.
	 *
	 * @param int[] $users_ids The users IDs to filter.
	 */
	private function filter_valid_coteachers_ids( $users_ids ) {
		$filtered = [];
		foreach ( $users_ids as $user_id ) {
			$user = get_user_by( 'ID', $user_id );
			if ( $user ) {
				if ( ! empty( array_intersect( $user->roles, self::SUPPORTED_ROLES ) ) ) {
					$filtered[] = $user_id;
				}
			}
		}

		return $filtered;
	}

	/**
	 * Check if current user can manage co-teachers or not.
	 *
	 * @param int $course_id The course ID.
	 * @return bool
	 */
	public function can_current_user_manage_coteachers( $course_id ): bool {
		return current_user_can( 'manage_options' );
	}

	/**
	 * Remove all co-teacher metas from user if supported role is removed.
	 *
	 * @param string   $user_id  The use ID.
	 * @param string   $new_role The new role.
	 * @param string[] $old_roles The previous roles.
	 */
	public function maybe_remove_coteacher_metas_when_user_role_changes( $user_id, $new_role, $old_roles ) {
		$was_old_supported = ! empty( array_intersect( $old_roles, self::SUPPORTED_ROLES ) );
		$is_new_supported  = in_array( $new_role, self::SUPPORTED_ROLES, true );
		if ( $was_old_supported && ! $is_new_supported ) {
			delete_user_meta( $user_id, self::COTEACHER_META_KEY );
		}

		$this->course_user_id_cache = [];
	}
}
