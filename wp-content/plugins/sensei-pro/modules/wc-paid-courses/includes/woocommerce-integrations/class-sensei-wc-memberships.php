<?php
/**
 * Sensei WooCommerce Memberships Integration
 *
 * All functions needed to integrate Sensei and WooCommerce Memberships
 *
 * @package sensei-wc-paid-courses
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Sensei_WC_Paid_Courses\Sensei_WC_Paid_Courses;
use Sensei_WC_Paid_Courses\Courses;
use Sensei_WC_Paid_Courses\Course_Enrolment_Providers;

// @phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedClassFound -- Legacy classname.

/**
 * Class Sensei_WC_Memberships
 */
class Sensei_WC_Memberships {
	const WC_MEMBERSHIPS_PLUGIN_PATH = 'woocommerce-memberships/woocommerce-memberships.php';

	const WC_MEMBERSHIPS_VIEW_RESTRICTED_POST_CONTENT = 'wc_memberships_view_restricted_post_content';

	/**
	 * Disable query cache for `wc_memberships_get_membership_plans()` calls.
	 * This should only be set to true in PHPUnit tests.
	 *
	 * @var bool
	 */
	public static $disable_membership_plan_query_cache = false;

	/**
	 * Load WC Memberships integration hooks if WC Memberships is active
	 *
	 * @return void
	 */
	public static function load_wc_memberships_integration_hooks() {
		if ( false === self::is_wc_memberships_active() ) {
			return;
		}

		// Remove default restriction functionality for wc memberships on courses.
		add_action( 'wp', [ __CLASS__, 'disable_wc_membership_course_restrictions' ], 999 );

		if ( Course_Enrolment_Providers::use_legacy_enrolment_method() ) {
			// Add custom restriction functionality.
			add_filter( 'sensei_is_course_content_restricted', [ __CLASS__, 'is_course_access_restricted' ], 10, 2 );
			add_filter( 'sensei_couse_access_permission_message', [ __CLASS__, 'add_wc_memberships_notice' ], 10, 2 );
			add_filter( 'sensei_display_start_course_form', [ __CLASS__, 'display_start_course_form_to_members_only' ], 10, 2 );
			add_filter( 'sensei_user_can_register_for_course', [ __CLASS__, 'display_start_course_form_to_members_only' ], 10, 2 );
		}

		add_filter( 'wc_memberships_restricted_message_html', [ __CLASS__, 'customize_membership_notice' ] );

		// Load block editor assets.
		add_action( 'enqueue_block_editor_assets', [ __CLASS__, 'enqueue_block_editor_assets' ] );
		add_action( 'enqueue_block_editor_assets', [ __CLASS__, 'localize_block_editor_assets' ], 30 );

		// When querying for courses attached to product(s), include courses attached to a product through a Membership.
		add_filter( 'sensei_wc_paid_courses_get_product_courses', [ __CLASS__, 'include_membership_courses_in_product_courses' ], 10, 2 );

		// Show notice on Membership Plan and Course page if a product is double-attached.
		add_filter( 'admin_notices', [ __CLASS__, 'notify_double_attached_product_on_membership_plan' ] );
		add_filter( 'admin_notices', [ __CLASS__, 'notify_double_attached_product_on_course' ] );
		add_filter( 'sensei_wc_paid_courses_memberships_block_editor_l10n_data', [ __CLASS__, 'notify_double_attached_product_on_course_block_editor' ] );

		// Adds Memberships restrictions support to Sensei Lessons and Optionally, Course Videos.
		add_action( 'wp', [ __CLASS__, 'restrict_lesson_details' ] );
		add_action( 'wp', [ __CLASS__, 'restrict_course_videos' ] );

		// Filter the assignable products to not list membership products.
		add_filter( 'sensei_wc_paid_courses_assignable_products_query_args', [ __CLASS__, 'exclude_associated_membership_products' ], 10, 3 );
	}

	/**
	 * Disable WC Memberships restrictions for courses. We add our own custom
	 * restriction functionality elsewhere.
	 *
	 * @since 1.1.0
	 * @access private
	 */
	public static function disable_wc_membership_course_restrictions() {
		if ( ! is_singular( 'course' ) ) {
			return;
		}

		$restrictions = wc_memberships()->get_restrictions_instance()->get_posts_restrictions_instance();
		remove_action( 'the_post', [ $restrictions, 'restrict_post' ], 0 );
	}

	/**
	 * Is Course Access Restricted.
	 *
	 * @deprecated 2.0.0
	 *
	 * @param bool $access_restricted Access Restricted.
	 * @param int  $course_id Course ID.
	 * @return bool
	 */
	public static function is_course_access_restricted( $access_restricted, $course_id ) {
		_deprecated_function( __METHOD__, '2.0.0' );

		if ( false === self::is_wc_memberships_active() ) {
			return $access_restricted;
		}

		return self::is_content_restricted( $course_id );
	}

	/**
	 * Is content restricted?
	 *
	 * @deprecated 2.0.0
	 *
	 * @param int $object_id The object id.
	 * @return bool
	 */
	private static function is_content_restricted( $object_id ) {
		if ( get_current_user_id() > 0 ) {
			$access_restricted = ! current_user_can( self::WC_MEMBERSHIPS_VIEW_RESTRICTED_POST_CONTENT, $object_id );
			return $access_restricted;
		}

		return wc_memberships_is_post_content_restricted( $object_id );
	}

	/**
	 * Add Notice.
	 *
	 * @deprecated 2.0.0
	 *
	 * @param string $content The content.
	 * @return string
	 */
	public static function add_wc_memberships_notice( $content = '' ) {
		_deprecated_function( __METHOD__, '2.0.0' );

		global $post;

		if ( false === self::is_wc_memberships_active() ) {
			return $content;
		}

		if (
			isset( $post->ID )
			&& ! in_array( get_post_type( $post->ID ), [ 'course', 'lesson', 'quiz' ], true )
			|| ! self::is_content_restricted( $post->ID )
		) {
			return $content;
		}

		echo wp_kses_post( \WC_Memberships_User_Messages::get_message_html( 'content_restricted', [ 'post_id' => $post->ID ] ) );

		return false;
	}

	/**
	 * Display Start Course form to members only.
	 *
	 * Applied to the `sensei_display_start_course_form` filter to determine
	 * if the 'start taking this course' form should be displayed for a given course.
	 * If a course has membership rules, restrict to active logged in members.
	 *
	 * @deprecated 2.0.0
	 *
	 * @param bool $should_display Should Display.
	 * @param int  $course_id The course in question.
	 *
	 * @return bool|int The course id or false in case a restriction applies.
	 */
	public static function display_start_course_form_to_members_only( $should_display, $course_id ) {
		_deprecated_function( __METHOD__, '2.0.0' );

		return ! self::is_course_access_restricted( $should_display, $course_id );
	}

	/**
	 * Determine if WC Memberships is installed and active
	 *
	 * @return bool
	 */
	public static function is_wc_memberships_active() {
		return Sensei_Utils::is_plugin_present_and_activated(
			'WC_Memberships',
			self::WC_MEMBERSHIPS_PLUGIN_PATH
		);
	}

	/**
	 * Gets the course membership product IDs for a given course.
	 *
	 * @since 1.1.0
	 * @since 2.5.0 Added $plan_args argument.
	 *
	 * @param int|string $course_id Course ID.
	 * @param bool       $paid      Whether to get IDs for only paid products. Default false.
	 * @param bool       $free      Whether to get IDs for only free products. Default false.
	 * @param array      $plan_args Plan query args.
	 *
	 * @return int[] Array of product IDs.
	 */
	public static function get_course_membership_product_ids( $course_id, $paid = false, $free = false, $plan_args = [] ) {
		$product_ids = [];
		$all         = ( ! $paid && ! $free ) || ( $paid && $free );

		if ( ! self::is_wc_memberships_active() ) {
			return $product_ids;
		}

		// Get all membership plans that grant access upon product purchase.
		$plans = wc_memberships_get_membership_plans(
			wp_parse_args(
				$plan_args,
				[
					'meta_query' => [
						[
							'key'   => '_access_method',
							'value' => 'purchase',
						],
					],
				]
			)
		);

		// Check if the course is part of a membership plan.
		foreach ( $plans as $plan ) {
			// Get content restriction rules.
			$rules = $plan->get_rules( 'content_restriction' );

			// Check if course is part of content restriction rules.
			foreach ( $rules as $rule ) {
				// Check for course or course-category restriction rule.
				$course_rule          = 'course' === $rule->get_content_type_name();
				$course_category_rule = 'course-category' === $rule->get_content_type_name();

				if ( ! ( $course_rule || $course_category_rule ) ) {
					continue;
				}

				$object_ids = $rule->get_object_ids();

				// Check if the restriction rule is for this course.
				foreach ( $object_ids as $object_id ) {
					if ( $course_rule && intval( $course_id ) !== $object_id ) {
						continue;
					} elseif ( $course_category_rule && ! has_term( $object_id, 'course-category', $course_id ) ) {
						continue;
					}

					if ( $all ) {
						$product_ids = array_merge( $plan->get_product_ids(), $product_ids );

						// Move to next membership plan.
						continue 3;
					}

					// Check price of each product.
					foreach ( $plan->get_product_ids() as $product_id ) {
						$product = wc_get_product( $product_id );

						if ( ! ( $product instanceof \WC_Product ) ) {
							continue;
						}

						$price = $product->get_price();

						if (
							( $free && ( '' === $price || '0' === $price ) ) ||
							( $paid && ( '' !== $price && '0' !== $price ) )
						) {
							$product_ids[] = $product_id;
						}
					}
				}
			}
		}

		return array_unique( $product_ids );
	}

	/**
	 * Start courses associated with new membership
	 * so they show up on "my courses".
	 *
	 * Hooked into wc_memberships_user_membership_saved and wc_memberships_user_membership_created
	 *
	 * @deprecated 2.0.0
	 *
	 * @param mixed $membership_plan The Membership Plan.
	 * @param array $args The args.
	 */
	public static function on_wc_memberships_user_membership_saved( $membership_plan, $args = [] ) {
		_deprecated_function( __METHOD__, '2.0.0' );

		$user_membership_id = isset( $args['user_membership_id'] ) ? absint( $args['user_membership_id'] ) : null;

		if ( ! $user_membership_id ) {
			return;
		}

		$user_membership = wc_memberships_get_user_membership( $user_membership_id );
		self::start_courses_associated_with_membership( $user_membership );
	}

	/**
	 * Start courses associated with an active membership if not already started
	 * so they show up on "my courses".
	 *
	 * Hooked into wc_memberships_user_membership_status_changed
	 *
	 * @deprecated 2.0.0
	 *
	 * @param WC_Memberships_User_Membership $user_membership The user membership.
	 */
	public static function start_courses_associated_with_membership( $user_membership ) {
		_deprecated_function( __METHOD__, '2.0.0' );

		if ( false === self::is_wc_memberships_active() ) {
			return;
		}

		if ( ! $user_membership ) {
			return;
		}

		$auto_start_courses = self::should_auto_start_membership_courses( $user_membership );
		if ( false === $auto_start_courses ) {
			return;
		}

		$user_id         = $user_membership->get_user_id();
		$membership_plan = $user_membership->get_plan();
		if ( empty( $membership_plan ) ) {
			return;
		}

		$restricted_content = $membership_plan->get_restricted_content( 0 );
		if ( empty( $restricted_content ) ) {
			return;
		}

		foreach ( $restricted_content->get_posts() as $maybe_course ) {
			if ( empty( $maybe_course ) || 'course' !== $maybe_course->post_type ) {
				continue;
			}

			$course_id = $maybe_course->ID;

			/**
			 * Filter sensei_wc_paid_courses_memberships_auto_start_course.
			 *
			 * Determine if we should automatically start users on a specific course
			 * that is part of a user membership and has not started yet.
			 *
			 * @since 1.0.0
			 *
			 * @deprecated 2.0.0
			 *
			 * @param bool                           $auto_start_courses True if we should auto-start the course.
			 * @param WC_Memberships_User_Membership $user_membership    User membership object.
			 * @param int                            $course_id          Course ID that will be started.
			 * @param int                            $user_id            User ID that will be started.
			 */
			$auto_start_course = (bool) apply_filters( 'sensei_wc_paid_courses_memberships_auto_start_course', true, $user_membership, $course_id, $user_id );

			if ( $auto_start_course && false === Sensei_Utils::user_started_course( $course_id, $user_id ) ) {
				Sensei_Utils::user_start_course( $user_id, $course_id );
			}
		}
	}

	/**
	 * Should we auto start any Courses this Membership controls access to?
	 *
	 * @deprecated 2.0.0
	 *
	 * @param WC_Memberships_User_Membership $user_membership User Membership.
	 * @return bool
	 */
	private static function should_auto_start_membership_courses( $user_membership ) {
		$auto_start_courses = (bool) Sensei()->settings->get( 'sensei_wc_memberships_auto_start_courses' );

		/**
		 * Determine if we should automatically start users on any courses that are part of this user membership;
		 *
		 * @since 1.0.0
		 *
		 * @deprecated 2.0.0
		 *
		 * @param bool                           $auto_start_courses True if we should auto start the course.
		 * @param WC_Memberships_User_Membership $user_membership    User membership object.
		 */
		return (bool) apply_filters( 'sensei_wc_paid_courses_memberships_auto_start_courses', $auto_start_courses, $user_membership );
	}

	/**
	 * Is My Courses Page
	 *
	 * @deprecated 2.0.0
	 *
	 * @param int $post_id Post Id.
	 * @return bool
	 */
	public static function is_my_courses_page( $post_id ) {
		_deprecated_function( __METHOD__, '2.0.0' );

		return is_page() && intval( Sensei()->settings->get( 'my_course_page' ) ) === intval( $post_id );
	}

	/**
	 * Customize the membership notice on course pages.
	 *
	 * @since 1.1.0
	 *
	 * @param string $html_message Message that restricts the content.
	 * @return string Message that restricts the content.
	 */
	public static function customize_membership_notice( $html_message ) {
		if ( is_post_type_archive( 'course' ) || is_tax( 'course-category' ) ) {
			// Show the course excerpt instead of the membership notice.
			return wp_kses_post( get_the_excerpt() );
		} elseif ( is_singular( 'course' ) ) {
			return '';
		}

		return $html_message;
	}

	/**
	 * Required: Restrict lesson videos & quiz links until the member has access to the lesson.
	 * Used to ensure content dripping from Memberships is compatible with Sensei.
	 *
	 * This will also remove the "complete lesson" button until the lesson is available.
	 */
	public static function restrict_lesson_details() {
		global $post;

		// sanity checks.
		if ( ! function_exists( 'wc_memberships_get_user_access_start_time' ) || ! function_exists( 'Sensei' ) || 'lesson' !== get_post_type( $post ) ) {
			return;
		}

		// if access start time isn't set, or is after the current date, remove the video.
		if ( ! wc_memberships_get_user_access_start_time(
			get_current_user_id(),
			'view',
			[
				'lesson' => $post->ID,
			]
		)
			|| time() < wc_memberships_get_user_access_start_time(
				get_current_user_id(),
				'view',
				[
					'lesson' => $post->ID,
				],
				true
			) ) {

			remove_action( 'sensei_single_lesson_content_inside_after', [ 'Sensei_Lesson', 'footer_quiz_call_to_action' ] );
			remove_action( 'sensei_single_lesson_content_inside_before', [ 'Sensei_Lesson', 'user_lesson_quiz_status_message' ], 20 );

			remove_action( 'sensei_lesson_video', [ Sensei()->frontend, 'sensei_lesson_video' ], 10, 1 );
			remove_action( 'sensei_lesson_meta', [ Sensei()->frontend, 'sensei_lesson_meta' ], 10 );
			remove_action( 'sensei_complete_lesson_button', [ Sensei()->frontend, 'sensei_complete_lesson_button' ] );
		}
	}

	/**
	 * Optional: Restrict course videos unless the member has access.
	 * Used if you don't want to show course previews to non-members.
	 */
	public static function restrict_course_videos() {
		global $post;

		// sanity checks.
		if ( ! function_exists( 'wc_memberships_get_user_access_start_time' ) || ! function_exists( 'Sensei' ) || 'course' !== get_post_type( $post ) ) {
			return;
		}

		$restrict_course_video = (bool) Sensei()->settings->get( 'sensei_wc_memberships_restrict_course_video' );

		if ( ! $restrict_course_video ) {
			return;
		}

		// if access start time isn't set, or is after the current date, remove the video.
		if ( ! wc_memberships_get_user_access_start_time(
			get_current_user_id(),
			'view',
			[
				'course' => $post->ID,
			]
		)
			|| time() < wc_memberships_get_user_access_start_time(
				get_current_user_id(),
				'view',
				[
					'course' => $post->ID,
				],
				true
			) ) {

			remove_action( 'sensei_single_course_content_inside_before', [ 'Sensei_Course', 'the_course_video' ], 40 );
			remove_action( 'sensei_no_permissions_inside_before_content', [ 'Sensei_Course', 'the_course_video' ], 40 );
		}
	}

	/**
	 * Exclude associated membership products from the assignable products.
	 *
	 * @access private
	 *
	 * @since 2.5.0
	 *
	 * @param array        $args   The query args.
	 * @param WP_Post|null $course The course as a WP_Post.
	 *
	 * @return array Filtered query args.
	 */
	public static function exclude_associated_membership_products( $args, $course ) {
		if ( ! $course ) {
			return $args;
		}

		$course_id                     = $course->ID;
		$course_membership_product_ids = self::get_course_membership_product_ids( $course_id, false, false, [ 'post_status' => 'any' ] );
		$course_product_ids            = get_post_meta( $course_id, '_course_woocommerce_product' ) ?? [];
		$exclude_product_ids           = array_diff( $course_membership_product_ids, $course_product_ids );

		if ( ! isset( $args['post__not_in'] ) ) {
			$args['post__not_in'] = [];
		}

		$args['post__not_in'] = array_merge( $args['post__not_in'], $exclude_product_ids );

		return $args;
	}

	/**
	 * Enqueue block assets needed for Memberships functionality.
	 *
	 * @access private
	 * @since 1.2.0
	 */
	public static function enqueue_block_editor_assets() {
		$screen = get_current_screen();
		if ( 'course' === $screen->id ) {
			Sensei_WC_Paid_Courses::instance()->enqueue_block_editor_asset( 'course-wc-memberships' );
		}
	}

	/**
	 * Send localization data for block editor script.
	 *
	 * @access private
	 * @since 1.2.0
	 */
	public static function localize_block_editor_assets() {
		$screen = get_current_screen();
		if ( 'course' === $screen->id ) {
			Sensei_WC_Paid_Courses::instance()->localize_block_editor_asset(
				'course-wc-memberships',
				/**
				 * Filter the localization data for block editor script.
				 *
				 * @since 1.2.0
				 *
				 * @param array $data The localization data.
				 */
				apply_filters( 'sensei_wc_paid_courses_memberships_block_editor_l10n_data', [] )
			);
		}
	}

	/**
	 * Filter the the courses attached to the given products to include membership courses.
	 *
	 * @access private
	 * @since 1.2.0
	 *
	 * @param WP_Post[] $courses     The course objects.
	 * @param array     $product_ids The product IDs.
	 * @return WP_Post[]
	 */
	public static function include_membership_courses_in_product_courses( $courses, $product_ids ) {
		if ( empty( $product_ids ) ) {
			return $courses;
		}

		$plans_query_args = [
			'meta_query' => [
				[
					'key'   => '_access_method',
					'value' => 'purchase',
				],
			],
		];

		if ( self::$disable_membership_plan_query_cache ) {
			// Add a cache-busting query parameter.
			$plans_query_args['_cache_key'] = uniqid();
		}

		$plans = wc_memberships_get_membership_plans( $plans_query_args );

		foreach ( $plans as $plan ) {
			// Only process plans that have one of the given products.
			if ( empty( array_intersect( $product_ids, $plan->get_product_ids() ) ) ) {
				continue;
			}

			// Get the course restriction rules for the plan.
			$rules = $plan->get_rules( 'content_restriction' );

			foreach ( $rules as $rule ) {
				// Build an array of courses that are restricted by this rule.
				$restricted_courses = [];

				// Check if this is a Course restriction rule.
				if ( 'post_type' === $rule->get_content_type() && 'course' === $rule->get_content_type_name() ) {
					// Add this membership's courses to the list.
					$restricted_course_ids = $rule->get_object_ids();

					if ( ! empty( $restricted_course_ids ) ) {
						$restricted_courses = get_posts(
							[
								'posts_per_page' => -1,
								'post_type'      => 'course',
								'post__in'       => $restricted_course_ids,
							]
						);
					}
				}

				// Check if this is a Course Category restriction rule.
				if ( 'taxonomy' === $rule->get_content_type() && 'course-category' === $rule->get_content_type_name() ) {
					// Add all course IDs from the categories to the list.
					$categories = $rule->get_object_ids();

					if ( ! empty( $categories ) ) {
						$restricted_courses = get_posts(
							[
								'posts_per_page' => -1,
								'post_type'      => 'course',
								'tax_query'      => [
									[
										'taxonomy' => 'course-category',
										'terms'    => $categories,
									],
								],
							]
						);
					}
				}

				// Merge in restricted courses.
				$courses = array_merge( $courses, $restricted_courses );
			}
		}

		return $courses;
	}

	/**
	 * Display a warning notice on the Membership Plan page if any course being
	 * restricted is already attached directly to an access-granting product
	 * on the plan.
	 *
	 * @access private
	 * @since 1.2.0
	 */
	public static function notify_double_attached_product_on_membership_plan() {
		global $post;

		$screen = get_current_screen();
		if ( 'wc_membership_plan' !== $screen->id ) {
			return;
		}

		// Find any products that are also directly attached to courses.
		$membership_plan                 = new WC_Memberships_Membership_Plan( $post );
		$double_attached_course_products = self::get_double_attached_course_products_for_plan( $membership_plan );

		// Return if there are no double-attached courses.
		if ( empty( $double_attached_course_products ) ) {
			return;
		}

		// Display notice.
		?>
		<div class="notice notice-warning">
			<p>
				<?php
				echo esc_html(
					_n(
						'It appears that this membership plan contains a content restriction rule for a course that is already associated with the same product. This can result in undesirable behavior.',
						'It appears that this membership plan contains a content restriction rule for some courses that are already associated with the same product. This can result in undesirable behavior.',
						count( $double_attached_course_products ),
						'sensei-pro'
					)
				);
				?>
			</p>
			<p>
				<?php
				echo esc_html(
					_n(
						'Please remove the following product(s) from the Products field on the Edit Course page for the following course:',
						'Please remove the following product(s) from the Products field on the Edit Course page for the following courses:',
						count( $double_attached_course_products ),
						'sensei-pro'
					)
				);
				?>
			</p>
			<ul>
				<?php
				foreach ( $double_attached_course_products as $course_id => $product_ids ) {
					$course_title = get_the_title( $course_id );
					$course_url   = get_edit_post_link( $course_id );

					foreach ( $product_ids as $product_id ) {
						$product_title = get_the_title( $product_id );
						?>
						<li>
							- <a href="<?php echo esc_url( $course_url ); ?>"><?php echo esc_html( $course_title ); ?></a>,
							<?php echo esc_html( $product_title ); ?>
						</li>
						<?php
					}
				}
				?>
			</ul>
			<p>
				<a href="<?php echo esc_url( self::double_attached_product_notice_learn_more_url() ); ?>">
					<?php echo esc_html( self::double_attached_product_notice_learn_more_text() ); ?>
				</a>
			</p>
		</div>
		<?php
	}

	/**
	 * Display a warning notice on the Edit Course page if any attached product
	 * is also attached indirectly through a Membership Plan.
	 *
	 * @access private
	 * @since 1.2.0
	 */
	public static function notify_double_attached_product_on_course() {
		global $post;

		$screen = get_current_screen();
		if ( 'course' !== $screen->id ) {
			return;
		}

		// Find any products that are also directly attached to courses.
		$double_attached_product_ids = self::get_double_attached_products_for_course( $post );

		// Return if there are no double-attached courses.
		if ( empty( $double_attached_product_ids ) ) {
			return;
		}

		// Get notice text.
		$notice_text = self::double_attached_product_notice_text_for_course( $double_attached_product_ids );

		// Display notice.
		?>
		<div class="notice notice-warning">
			<p>
				<?php echo esc_html( $notice_text ); ?>
			</p>
			<p>
				<a href="<?php echo esc_url( self::double_attached_product_notice_learn_more_url() ); ?>">
					<?php echo esc_html( self::double_attached_product_notice_learn_more_text() ); ?>
				</a>
			</p>
		</div>
		<?php
	}

	/**
	 * Add a warning notice to the localization data on the Edit Course block
	 * editor page if any attached product is also attached indirectly through a
	 * Membership Plan. The block editor JS code will pick up on the string in
	 * the localization data and display a warning if it exists.
	 *
	 * @access private
	 * @since 1.2.0
	 *
	 * @param array $data The localization data.
	 * @return array
	 */
	public static function notify_double_attached_product_on_course_block_editor( $data ) : array {
		global $post;

		$screen = get_current_screen();
		if ( 'course' !== $screen->id ) {
			return [];
		}

		// Find any products that are also directly attached to courses.
		$double_attached_product_ids = self::get_double_attached_products_for_course( $post );

		// Return if there are no double-attached courses.
		if ( empty( $double_attached_product_ids ) ) {
			return [];
		}

		// Add notice text.
		$data['double_attached_product_notice']                 = self::double_attached_product_notice_text_for_course( $double_attached_product_ids );
		$data['double_attached_product_notice_learn_more_text'] = self::double_attached_product_notice_learn_more_text();
		$data['double_attached_product_notice_learn_more_url']  = self::double_attached_product_notice_learn_more_url();
		return $data;
	}

	/**
	 * Get the notice text for the Course page for double-attached products.
	 *
	 * @access private
	 * @since 1.2.0
	 *
	 * @param int[] $product_ids The double-attached product IDs.
	 * @return string
	 */
	private static function double_attached_product_notice_text_for_course( $product_ids ) {
		return _n(
			'It appears that this course is associated with a product that is already part of a membership plan for the same course and product. This can result in undesirable behavior.',
			'It appears that this course is associated with products that are already part of one or more membership plans for the same course and products. This can result in undesirable behavior.',
			count( $product_ids ),
			'sensei-pro'
		);
	}

	/**
	 * The "Learn more" text for the double-attached product notice.
	 *
	 * @access private
	 * @since 1.2.0
	 *
	 * @return string
	 */
	private static function double_attached_product_notice_learn_more_text() {
		return __(
			'Learn more about this issue and how to resolve it.',
			'sensei-pro'
		);
	}

	/**
	 * The "Learn more" URL for the double-attached product notice.
	 *
	 * @access private
	 * @since 1.2.0
	 *
	 * @return string
	 */
	private static function double_attached_product_notice_learn_more_url() {
		return 'https://senseilms.com/documentation/selling-courses-as-a-membership/';
	}

	/**
	 * Get the IDs of the courses and products that are attached both directly
	 * and through the given plan.
	 *
	 * @access private
	 * @since 1.2.0
	 *
	 * @param WC_Memberships_Membership_Plan $membership_plan The plan.
	 *
	 * @return array An array of the form `$array[ $course_id ] = $product_ids`
	 */
	public static function get_double_attached_course_products_for_plan( $membership_plan ) {
		$plan_product_ids = $membership_plan->get_product_ids();
		$rules            = $membership_plan->get_rules( 'content_restriction' );

		// Include parent and child products when searching for double-attached courses.
		$parent_child_ids = self::get_product_parents_and_children( $plan_product_ids );
		$plan_product_ids = array_merge( $plan_product_ids, $parent_child_ids );

		// Get the courses on this plan.
		$plan_attached_course_ids = [];
		foreach ( $rules as $rule_name => $rule ) {
			if ( 'post_type' === $rule->get_content_type() && 'course' === $rule->get_content_type_name() ) {
				$plan_attached_course_ids = array_merge( $plan_attached_course_ids, $rule->get_object_ids() );
			}
		}

		// See if there are any courses directly attached to any of the products.
		$direct_attached_course_ids = wp_list_pluck( Courses::get_direct_attached_product_courses( $plan_product_ids ), 'ID' );

		// Get double attached course IDs.
		$double_attached_course_ids = array_intersect( $plan_attached_course_ids, $direct_attached_course_ids );

		// Create the array to return.
		$double_attached_course_products = [];
		foreach ( $double_attached_course_ids as $course_id ) {
			$course_product_ids = get_post_meta( $course_id, '_course_woocommerce_product' );

			// Get the double-attached products.
			$double_attached_product_ids = array_values( array_intersect( $plan_product_ids, $course_product_ids ) );

			if ( ! empty( $double_attached_product_ids ) ) {
				$double_attached_course_products[ $course_id ] = $double_attached_product_ids;
			}
		}

		return $double_attached_course_products;
	}

	/**
	 * Get the IDs of the products that are attached to the givent course both
	 * directly and through a plan.
	 *
	 * @access private
	 * @since 1.2.0
	 *
	 * @param WP_Post|int $course The course object or ID.
	 *
	 * @return array An array of product IDs.
	 */
	public static function get_double_attached_products_for_course( $course ) {
		$course             = get_post( $course );
		$course_product_ids = Sensei_WC::get_course_product_ids( $course->ID, false );
		$course_rules       = wc_memberships()->get_rules_instance()->get_rules(
			[
				'rule_type'         => 'content_restriction',
				'content_type'      => 'post_type',
				'content_type_name' => get_post_type( $course ),
				'object_id'         => $course->ID,
			]
		);

		$plan_product_ids = [];
		foreach ( $course_rules as $rule ) {
			$plan             = $rule->get_membership_plan();
			$plan_product_ids = array_merge(
				$plan_product_ids,
				$plan->get_product_ids()
			);
		}

		// Include parent and child products when searching for double-attached products.
		$parent_child_ids = self::get_product_parents_and_children( $plan_product_ids );
		$plan_product_ids = array_merge( $plan_product_ids, $parent_child_ids );

		return array_values( array_intersect( $course_product_ids, $plan_product_ids ) );
	}

	/**
	 * Given an array of product IDs, return an array of product IDs which
	 * includes all of the parents of the variations, and all of the children of
	 * all variable products in the given list.
	 *
	 * @access private
	 * @since 1.2.0
	 *
	 * @param int[] $product_ids      The initial array of product IDs.
	 * @param bool  $include_parents  Whether to fetch parents for given variations (default: true).
	 * @param bool  $include_children Whether to fetch children for given variable products (default: true).
	 *
	 * @return int[] An array of the parent and child product IDs.
	 */
	private static function get_product_parents_and_children( $product_ids, $include_parents = true, $include_children = true ) {
		$parent_child_ids = [];
		foreach ( $product_ids as $product_id ) {
			$product = wc_get_product( $product_id );

			if ( ! ( $product instanceof \WC_Product ) ) {
				continue;
			}

			$product_type = $product->get_type();
			if ( $include_parents && in_array( $product_type, [ 'subscription_variation', 'variation' ], true ) ) {
				$parent_child_ids[] = $product->get_parent_id();
			} elseif ( $include_children && in_array( $product_type, [ 'variable', 'variable-subscription' ], true ) ) {
				$parent_child_ids = array_merge( $parent_child_ids, $product->get_children() );
			}
		}

		return $parent_child_ids;
	}
}
