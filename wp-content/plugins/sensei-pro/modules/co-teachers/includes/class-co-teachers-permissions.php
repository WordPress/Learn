<?php
/**
 * File containing the Co_Teachers_Permissions class.
 *
 * @package sensei-pro
 * @since   1.9.0
 */

namespace Sensei_Pro_Co_Teachers;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class containing all logic regarding permissions for Co-Teachers.
 */
class Co_Teachers_Permissions {

	/**
	 * Class instance.
	 *
	 * @var Co_Teachers_Permissions
	 */
	private static $instance;

	/**
	 * Main Co-Teachers instace.
	 *
	 * @var Co_Teachers
	 */
	private $co_teachers;

	/**
	 * Retrieve the Co_Teachers_Permissions instance.
	 */
	public static function instance(): Co_Teachers_Permissions {
		if ( ! self::$instance ) {
			self::$instance = new self( Co_Teachers::instance() );
		}

		return self::$instance;
	}

	/**
	 * Class constructor.
	 *
	 * @param Co_Teachers $co_teachers The main Co_Teachers class.
	 */
	public function __construct( Co_Teachers $co_teachers ) {
		$this->co_teachers = $co_teachers;
	}

	/**
	 * Initializes the class and adds all filters and actions.
	 */
	public static function init() {
		$instance = self::instance();

		add_filter( 'user_has_cap', [ $instance, 'grant_access_to_coteachers' ], 10, 3 );
		add_filter( 'posts_where', [ $instance, 'posts_where_filter' ], 10, 2 );
		add_filter( 'sensei_filter_module_terms_by_owner', [ $instance, 'restore_module_terms_for_coteachers' ], 10, 2 );
		add_filter( 'wp_insert_post_data', [ $instance, 'keep_original_author_when_editing_as_coteacher' ], 10, 4 );
		add_filter( 'sensei_filter_queries_set_author', [ $instance, 'set_query_author_for_coteachers' ], 10, 2 );
		add_filter( 'sensei_grading_allowed_user_ids', [ $instance, 'allow_grading_for_coteachers' ], 10, 2 );
	}

	/**
	 * Updates WHERE clause to include the posts a user is a co-teacher for.
	 *
	 * This filter will only work if the following conditions are met:
	 *
	 * - The query is for a course, lesson, or quiz post type.
	 * - The query is for a single author.
	 *
	 * @param string    $where
	 * @param \WP_Query $query
	 * @return mixed|string
	 */
	public function posts_where_filter( $where, $query ) {
		global $wpdb;

		// Ignore requests outside of admin panel and haven't explicitly set the context as a teacher-filter view.
		$is_teacher_filter_context = isset( $query->query['context'] ) && 'teacher-filter' === $query->get( 'context' );
		if ( ! is_admin() && ! $is_teacher_filter_context ) {
			return $where;
		}

		// Ignore requests for unsupported post_types.
		$post_type = $query->query['post_type'] ?? null;
		if ( ! $this->is_post_type_supported( $post_type ) ) {
			return $where;
		}

		// Ignore requests without the `author` set.
		if ( ! isset( $query->query_vars['author'] ) || empty( $query->query_vars['author'] ) ) {
			return $where;
		}

		$author_id = $query->query_vars['author'];

		// Ignore requests with any author parameter other than a singular ID.
		if ( is_array( $author_id ) ) {
			if ( count( $author_id ) !== 1 ) {
				return $where;
			}

			$author_id = $author_id[0];
		}

		if ( ! is_numeric( $author_id ) || intval( $author_id ) < 0 ) {
			return $where;
		}

		// Ignore requests to include multiple authors.
		$author_in = $query->query_vars['author__in'] ?? null;
		if ( ! empty( $author_in ) ) {
			if ( count( $author_in ) !== 1 || intval( $author_in[0] ) !== intval( $author_id ) ) {
				return $where;
			}
		}

		// Ignore requests to exclude multiple authors.
		$author_not_in = $query->query_vars['author__not_in'] ?? null;
		if ( ! empty( $author_not_in ) ) {
			return $where;
		}

		// Unhook to prevent recursion.
		remove_filter( 'posts_where', [ $this, 'posts_where_filter' ], 10 );
		// Disable post_types protection for teachers.
		remove_filter( 'parse_query', [ Sensei()->teacher, 'limit_teacher_edit_screen_post_types' ] );

		// Retrieve additional posts IDs depending on post type.
		$additional_posts_ids = [];
		if ( 'course' === $post_type ) {
			$additional_posts_ids = $this->co_teachers->get_coteacher_courses_ids( $author_id );
		} elseif ( 'lesson' === $post_type ) {
			$courses_ids = $this->co_teachers->get_coteacher_courses_ids( $author_id );
			foreach ( $courses_ids as $course_id ) {
				$lesson_ids           = $this->get_lesson_ids_skipping_ownership( $course_id );
				$additional_posts_ids = array_merge( $additional_posts_ids, $lesson_ids );
			}
		} elseif ( 'quiz' === $post_type ) {
			$courses_ids = $this->co_teachers->get_coteacher_courses_ids( $author_id );
			foreach ( $courses_ids as $course_id ) {
				$lesson_ids = $this->get_lesson_ids_skipping_ownership( $course_id );
				foreach ( $lesson_ids as $lesson_id ) {
					$quiz_id                = $this->get_lesson_quiz_id_skipping_ownership( $lesson_id );
					$additional_posts_ids[] = $quiz_id;
				}
			}
		}
		// Sanitize by casting to integers and removing any zeros.
		$additional_posts_ids = array_map( 'intval', $additional_posts_ids );
		$additional_posts_ids = array_filter(
			$additional_posts_ids,
			function ( $id ) {
				return 0 !== $id;
			}
		);
		$additional_posts_ids = array_unique( $additional_posts_ids );

		// Extend SQL if any additional post ID was retrieved.
		if ( ! empty( $additional_posts_ids ) ) {
			$coauthoring_post_ids_join = esc_sql( implode( ', ', $additional_posts_ids ) );
			$coauthor_query            = "{$wpdb->posts}.ID IN ( {$coauthoring_post_ids_join} )";

			// Add the coauthor post IDs to the author query.
			$where = preg_replace(
				"/{$wpdb->posts}\.post_author IN \(([^)]*)\)/",
				" ( {$wpdb->posts}.post_author IN ( $1 ) OR $coauthor_query ) ",
				$where
			);
		}

		// Enable post_types protection for teachers again.
		add_filter( 'parse_query', [ Sensei()->teacher, 'limit_teacher_edit_screen_post_types' ] );
		// Hook again before we are done.
		add_filter( 'posts_where', [ $this, 'posts_where_filter' ], 10, 2 );

		return $where;
	}

	/**
	 * Hooks into the `user_has_cap` filter and grants access to posts if user is a co-teacher.
	 *
	 * @param bool[]   $allcaps The capabilities that the user has.
	 * @param string[] $caps    Required capabilities.
	 * @param array    $args    Arguments for the capability check.
	 *
	 * @return mixed
	 */
	public function grant_access_to_coteachers( $allcaps, $caps, $args ) {
		$user_id = $args[1] ?? 0;
		$post_id = $args[2] ?? 0;

		$post_type = get_post_type( $post_id );

		// Ignore revisions.
		$obj = get_post_type_object( $post_type );
		if ( ! $obj || 'revision' === $obj->name ) {
			return $allcaps;
		}

		// Ignore unsupported post types.
		if ( ! $this->is_post_type_supported( $post_type ) ) {
			return $allcaps;
		}

		$handled_caps = [
			$obj->cap->edit_post,
			$obj->cap->edit_others_posts,
		];

		if ( 0 === count( array_intersect( $handled_caps, $caps ) ) ) {
			return $allcaps;
		}

		// Ignore original authors.
		$post = get_post( $post_id );
		if ( $user_id === $post->post_author ) {
			return $allcaps;
		}

		// Check if user is a coauthor for the given `post_id`'s course.
		if ( $this->co_teachers->is_coteacher( $user_id, $post ) ) {
			$allcaps[ $obj->cap->edit_post ]         = true;
			$allcaps[ $obj->cap->edit_others_posts ] = true;
		}

		return $allcaps;
	}

	/**
	 * Hooks into `wp_insert_post_data` and updates the post data to keep the original author if the current user is a co-teacher for the current post..
	 *
	 * @param mixed     $data                The data to be saved.
	 * @param mixed     $postarr             The post data.
	 * @param mixed     $unsanitized_postarr Unsanitized post data.
	 * @param bool|null $update              Whether the action is for an existing post being updated or not.
	 * @return mixed
	 */
	public function keep_original_author_when_editing_as_coteacher( $data, $postarr, $unsanitized_postarr, $update = null ) {
		// Compatibility for WP < 6.0.
		if ( null === $update ) {
			$update = ! empty( $postarr['ID'] );
		}

		// Only handle updates.
		if ( ! $update ) {
			return $data;
		}

		// Ignore revisions.
		$post_id = $postarr['ID'];
		if ( wp_is_post_revision( $post_id ) ) {
			return $data;
		}

		$post = get_post( $post_id );

		// Ignore unsupported post types. Also ignore quizzes as their post_author is handled specially.
		if ( ! $this->is_post_type_supported( $post->post_type ) || 'quiz' === $post->post_type ) {
			return $data;
		}

		// Keep original author if current user is a co-author.
		if ( $this->co_teachers->is_coteacher( get_current_user_id(), $post ) ) {
			$data['post_author'] = $post->post_author;
		}

		return $data;
	}

	/**
	 * Ensure the `author` query param is set properly when current user is a
	 * co-teacher.
	 *
	 * @param mixed  $author    The incoming author param.
	 * @param string $screen_id The current screen ID.
	 *
	 * @return string The new author query param.
	 */
	public function set_query_author_for_coteachers( $author, $screen_id ) {
		global $post;

		// Only continue if we are a co-teacher on a course or lesson page.
		if (
			empty( $post )
			|| ! in_array( $screen_id, [ 'course', 'lesson' ], true )
			|| ! $this->co_teachers->is_coteacher( get_current_user_id(), $post )
		) {
			return $author;
		}

		// Include the main teacher in the author query param.
		$author_arr   = explode( ',', $author );
		$teacher      = get_post_field( 'post_author', $post );
		$author_arr[] = $teacher;
		$author       = implode( ',', array_unique( $author_arr ) );

		return $author;
	}

	/**
	 * Checks if given post type is supported or not.
	 *
	 * @param string $post_type The post type.
	 * @return bool
	 */
	private function is_post_type_supported( $post_type ) {
		return in_array( $post_type, [ 'course', 'lesson', 'quiz' ], true );
	}

	/**
	 * Iterates over all the original terms and adds back the terms for modules that are used in a Course where current user is a co-teacher.
	 *
	 * @param \WP_Term[]       $terms          The final terms after removing the unowned terms.
	 * @param \WP_Term[]|int[] $original_terms The original list of all the terms previous to filtering.
	 * @return array
	 */
	public function restore_module_terms_for_coteachers( $terms, $original_terms ) {

		$coauthor_terms = [];

		foreach ( $original_terms as $original_term ) {
			if ( is_numeric( $original_term ) ) {
				// The term id was given, get the term object.
				$original_term = get_term( $original_term, 'module' );
			}

			$object_ids = get_objects_in_term( $original_term->term_id, 'module' );
			foreach ( $object_ids as $post_id ) {
				$post = get_post( $post_id );
				if ( $this->co_teachers->is_coteacher( get_current_user_id(), $post ) ) {
					$coauthor_terms[] = $original_term;
					break; // Continue to next `$original_term`.
				}
			}
		}

		return array_merge( $terms, $coauthor_terms );
	}

	/**
	 * Grant accesses to co-teachers when the grading is for a course ID with co-teachers assigned.
	 *
	 * @param int[] $user_ids The list of user IDs with grading access allowed.
	 * @param int   $course_id The course ID linked to the grading.
	 *
	 * @return int[] Full list of allowed user IDs.
	 */
	public function allow_grading_for_coteachers( $user_ids, $course_id ) {
		$coteacher_ids = $this->co_teachers->get_course_coteachers_ids( $course_id );

		return array_unique( array_merge( $user_ids, $coteacher_ids ) );
	}

	/**
	 * Retrieve lesson IDs associated to a course skipping ownership checks.
	 *
	 * @param int $course_id The course ID.
	 * @return int[] Lesson IDs.
	 */
	private function get_lesson_ids_skipping_ownership( $course_id ) {
		// Disable ownership check for teachers to allow access to unowned lessons.
		remove_action( 'pre_get_posts', [ Sensei()->teacher, 'filter_queries' ] );
		remove_action( 'pre_get_posts', [ Sensei()->teacher, 'course_analysis_teacher_access_limit' ] );
		$lesson_ids = Sensei()->course->course_lessons( $course_id, 'any', 'ids' );
		// Enable ownership check for teachers again.
		add_action( 'pre_get_posts', [ Sensei()->teacher, 'filter_queries' ] );
		add_action( 'pre_get_posts', [ Sensei()->teacher, 'course_analysis_teacher_access_limit' ] );
		return $lesson_ids;
	}

	/**
	 * Retrieve the Quiz ID associated with a lesson skipping ownership checks.
	 *
	 * @param int $lesson_id The lesson ID.
	 * @return int|null The Quiz ID.
	 */
	private function get_lesson_quiz_id_skipping_ownership( $lesson_id ) {
		// Disable ownership check for teachers to allow access to unowned lessons.
		remove_action( 'pre_get_posts', [ Sensei()->teacher, 'filter_queries' ] );
		remove_action( 'pre_get_posts', [ Sensei()->teacher, 'course_analysis_teacher_access_limit' ] );
		$quiz_id = Sensei()->lesson->lesson_quizzes( $lesson_id );
		// Enable ownership check for teachers again.
		add_action( 'pre_get_posts', [ Sensei()->teacher, 'filter_queries' ] );
		add_action( 'pre_get_posts', [ Sensei()->teacher, 'course_analysis_teacher_access_limit' ] );
		return $quiz_id;
	}
}
