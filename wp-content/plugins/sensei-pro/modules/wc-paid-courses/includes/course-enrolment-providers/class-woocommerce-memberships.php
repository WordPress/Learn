<?php
/**
 * File containing the class \Sensei_WC_Paid_Courses\Course_Enrolment_Providers\WooCommerce_Memberships.
 *
 * @package sensei-wc-paid-courses
 * @since   2.0.0
 */

namespace Sensei_WC_Paid_Courses\Course_Enrolment_Providers;

use Sensei_WC_Paid_Courses\Admin\WooCommerce_Memberships_Cancelled_Orders_Notice;
use Sensei_WC_Paid_Courses\Background_Jobs\WooCommerce_Memberships_Detect_Cancelled_Orders;
use Sensei_WC_Paid_Courses\Background_Jobs\Membership_Plan_Calculation_Job;
use Sensei_WC_Paid_Courses\Course_Enrolment_Providers;

/**
 * Course enrolment provider for courses where enrolment is controlled by a membership.
 *
 * @since 2.0.0
 */
class WooCommerce_Memberships
	implements \Sensei_Course_Enrolment_Provider_Interface, \Sensei_Course_Enrolment_Provider_Debug_Interface {
	const DATA_KEY_SIGNED_UP         = 'signed_up';
	const WC_MEMBERSHIPS_PLUGIN_PATH = 'woocommerce-memberships/woocommerce-memberships.php';

	/**
	 * Singleton instance.
	 *
	 * @var self
	 */
	private static $instance;

	/**
	 * Membership data before update.
	 *
	 * @var array
	 */
	private $membership_pre_update_data = [];

	/**
	 * Membership data before delete.
	 *
	 * @var array
	 */
	private $membership_pre_delete_data = [];

	/**
	 * User IDs for which to bypass the Membership cache.
	 *
	 * @var array
	 */
	private $bypass_membership_cache_for_user = [];

	/**
	 * Plan IDs for which to bypass the Membership cache.
	 *
	 * @var array
	 */
	private $bypass_membership_cache_for_plan = [];

	/**
	 * Membership plan data before update.
	 *
	 * @var array
	 */
	private $membership_plan_pre_update_data = [];

	/**
	 * Snapshot of the membership rules option.
	 *
	 * @see maybe_recalculate_course_enrolments
	 *
	 * @var array
	 */
	private $membership_rules_before_course_update;

	/**
	 * Provides singleton instance.
	 *
	 * @return self
	 */
	public static function instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Check if WooCommerce Memberships is active.
	 *
	 * @return bool
	 */
	public static function is_active() {
		return \Sensei_Utils::is_plugin_present_and_activated(
			'WC_Memberships',
			self::WC_MEMBERSHIPS_PLUGIN_PATH
		);
	}

	/**
	 * Adds the actions related to memberships.
	 */
	public function init() {
		\add_filter( 'sensei_is_legacy_enrolled', [ $this, 'maybe_allow_legacy_manual_enrolment' ], 10, 3 );

		// Recalculate when Membership is saved.
		add_action( 'pre_post_update', [ $this, 'save_membership_pre_update_data' ] );
		add_action( 'wc_memberships_user_membership_saved', [ $this, 'handle_membership_save' ], 10, 2 );
		add_action( 'wc_memberships_user_membership_transferred', [ $this, 'handle_membership_transfer' ], 10, 3 );
		add_action( 'wc_memberships_user_membership_deleted', [ $this, 'save_membership_pre_delete_data' ] );
		add_action( 'deleted_post', [ $this, 'handle_membership_post_deleted' ] );
		add_filter( 'wc_memberships_renew_membership', [ $this, 'allow_renewal_when_removed' ], 100, 3 );

		// Bypass Memberships cache if needed.
		add_filter( 'wc_memberships_user_membership', [ $this, 'bypass_user_membership_cache' ] );

		// Handle sign-ups for user on frontend.
		add_filter( 'sensei_can_user_manually_enrol', [ $this, 'can_user_sign_up' ], 11, 2 );
		add_filter( 'sensei_frontend_learner_enrolment_handler', [ $this, 'provide_frontend_sign_up_handler' ], 11, 3 );

		// Recalculate when Membership plan is saved.
		add_action( 'pre_post_update', [ $this, 'handle_membership_plan_pre_update_data' ] );
		add_action( 'shutdown', [ $this, 'handle_membership_plan_update' ] );

		// Recalculate when a course category is added or removed from a course.
		add_action( 'set_object_terms', [ $this, 'handle_course_category_update' ], 10, 6 );

		// Handle Membership plan update in course page.
		add_action( 'save_post', [ $this, 'store_membership_rules' ], 2, 0 );
		add_action( 'wc_memberships_save_meta_box', [ $this, 'maybe_recalculate_course_enrolments' ], 10, 0 );

		// Plan recalculation job.
		Membership_Plan_Calculation_Job::init();

		// Handle the detection and notification of cancelled orders with active memberships.
		WooCommerce_Memberships_Detect_Cancelled_Orders::init();
		if ( is_admin() ) {
			WooCommerce_Memberships_Cancelled_Orders_Notice::instance()->init();
		}
	}

	/**
	 * Class constructor. Private so it can only be initialized internally.
	 */
	private function __construct() {}

	/**
	 * Gets the unique identifier of this enrolment provider.
	 *
	 * @return int
	 */
	public function get_id() {
		return 'wc-memberships';
	}

	/**
	 * Gets the descriptive name of the provider.
	 *
	 * @return string
	 */
	public function get_name() {
		return esc_html__( 'WooCommerce Memberships', 'sensei-pro' );
	}

	/**
	 * Check if this course enrolment provider manages enrolment for a particular course.
	 *
	 * @param int $course_id Course post ID.
	 *
	 * @return bool
	 */
	public function handles_enrolment( $course_id ) {
		return \wc_memberships_is_post_content_restricted( $course_id );
	}

	/**
	 * Check if this course enrolment provider is enrolling a user to a course.
	 *
	 * @param int $user_id   User ID.
	 * @param int $course_id Course post ID.
	 *
	 * @return bool `true` if this provider enrols the student and `false` if not.
	 */
	public function is_enrolled( $user_id, $course_id ) {
		if ( ! $this->has_active_membership( $user_id, $course_id ) ) {
			return false;
		}

		return $this->is_signed_up( $user_id, $course_id );
	}

	/**
	 * Check if a user has an active membership.
	 *
	 * @access private
	 *
	 * @param int $user_id   User ID.
	 * @param int $course_id Course post ID.
	 *
	 * @return bool
	 */
	public function has_active_membership( $user_id, $course_id ) {
		add_filter( 'user_has_cap', [ $this, 'remove_manage_woocommerce_cap' ], 1, 2 );

		// Check to see if the current user can access the content, ignoring the `manage_woocommerce` capability.
		$has_active_membership = user_can( $user_id, 'wc_memberships_view_restricted_post_content', $course_id )
									&& user_can( $user_id, 'wc_memberships_view_delayed_post_content', $course_id );

		remove_filter( 'user_has_cap', [ $this, 'remove_manage_woocommerce_cap' ], 1, 2 );

		return $has_active_membership;
	}

	/**
	 * Check if the member has signed up. This shouldn't be called for non-members.
	 *
	 * @access private
	 *
	 * @param int $user_id   User ID.
	 * @param int $course_id Course post ID.
	 *
	 * @return bool
	 */
	public function is_signed_up( $user_id, $course_id ) {
		$provider_state = $this->get_provider_state( $user_id, $course_id );
		$is_signed_up   = $provider_state->get_stored_value( self::DATA_KEY_SIGNED_UP );

		if ( null === $is_signed_up ) {
			$is_signed_up = $this->get_initial_sign_up_value( $user_id, $course_id );

			if ( $is_signed_up ) {
				$this->add_user_sign_up( $user_id, $course_id );
			} else {
				$this->remove_user_sign_up( $user_id, $course_id );
			}
		}

		return $is_signed_up;
	}

	/**
	 * Should we auto-enrol learners in any course a membership plan grants access to.
	 *
	 * @param int $user_id   User ID.
	 * @param int $course_id Course post ID.
	 *
	 * @return bool
	 */
	private function should_auto_enrol_membership_courses( $user_id, $course_id ) {
		$auto_enrol_courses = (bool) Sensei()->settings->get( 'sensei_wc_memberships_auto_enrol_courses' );

		/**
		 * Determine if we should automatically start users on any courses that are part of this user membership;
		 *
		 * @since 1.0.0
		 *
		 * @deprecated 2.0.0
		 *
		 * @param bool $auto_enrol_courses True if we should auto start the course.
		 * @param null $user_membership    User membership object (No longer available).
		 */
		$auto_enrol_courses = (bool) apply_filters_deprecated( 'sensei_wc_paid_courses_memberships_auto_start_courses', [ $auto_enrol_courses, null ], '2.0.0', 'sensei_wc_paid_courses_memberships_auto_enrol_courses' );

		/**
		 * Determine if we should automatically enrol users on any courses that are part of this user membership;
		 *
		 * @since 2.0.0
		 *
		 * @param bool $auto_enrol_courses True if we should auto enrol the learner in the course.
		 * @param int  $user_id            User ID.
		 * @param int  $course_id          Course post ID.
		 */
		return (bool) apply_filters( 'sensei_wc_paid_courses_memberships_auto_enrol_courses', $auto_enrol_courses, $user_id, $course_id );
	}

	/**
	 * Get the IDs for the courses that a membership plan provides access to.
	 *
	 * @param int $plan_id The Plan ID.
	 *
	 * @return int[]
	 */
	private function get_membership_plan_course_ids( $plan_id ) {
		$course_ids = [];
		$rules      = wc_memberships()->get_rules_instance()->get_plan_rules( $plan_id );

		foreach ( $rules as $rule ) {
			$course_ids = array_merge( $course_ids, $this->get_course_ids_from_rule( $rule ) );
		}

		return array_unique( array_map( 'intval', $course_ids ) );
	}

	/**
	 * Get the initial value for if a user is signed up for a course.
	 *
	 * @param int $user_id   User ID.
	 * @param int $course_id Course post ID.
	 *
	 * @return bool
	 */
	private function get_initial_sign_up_value( $user_id, $course_id ) {
		$is_signed_up = $this->should_auto_enrol_membership_courses( $user_id, $course_id );

		// Check if we need to migrate from legacy.
		$legacy_migration_start_time = get_option( 'sensei_enrolment_legacy' );
		if ( $legacy_migration_start_time ) {
			$legacy_migration_date = \DateTime::createFromFormat( 'U', $legacy_migration_start_time );

			// If the latest membership for this course/user is before the migration time, set it to if the user
			// has started the course.
			$latest_start_date = $this->get_latest_membership_start_date( $user_id, $course_id );
			if ( $latest_start_date ) {
				$latest_start_date = new \DateTime( $latest_start_date );
				if ( $latest_start_date < $legacy_migration_date ) {
					$is_signed_up = (bool) \Sensei_Utils::has_started_course( $course_id, $user_id );
				}
			}
		}

		/**
		 * Check if a user should be signed up initially.
		 *
		 * @since 3.0.0
		 *
		 * @param bool $is_signed_up If the user is initially signed up for the course.
		 * @param int  $user_id      User ID.
		 * @param int  $course_id    Course post ID.
		 */
		return apply_filters( 'sensei_wc_paid_courses_memberships_is_signed_up', $is_signed_up, $user_id, $course_id );
	}

	/**
	 * Sign up a user for the course.
	 *
	 * @param int $user_id   User ID.
	 * @param int $course_id Course post ID.
	 */
	private function add_user_sign_up( $user_id, $course_id ) {
		$provider_state = $this->get_provider_state( $user_id, $course_id );
		$provider_state->set_stored_value( self::DATA_KEY_SIGNED_UP, true );
	}

	/**
	 * Remove a user's sign up for a course.
	 *
	 * @param int $user_id   User ID.
	 * @param int $course_id Course post ID.
	 */
	private function remove_user_sign_up( $user_id, $course_id ) {
		$provider_state = $this->get_provider_state( $user_id, $course_id );
		$provider_state->set_stored_value( self::DATA_KEY_SIGNED_UP, false );
	}

	/**
	 * Provide the membership sign up handler when appropriate.
	 *
	 * @access private
	 *
	 * @param callable $handler {
	 *     Frontend enrolment handler. Returns `true` if successful; `false` if not.
	 *
	 *     @type int $user_id   User ID.
	 *     @type int $course_id Course post ID.
	 * }
	 * @param int      $user_id          User ID.
	 * @param int      $course_id        Course post ID.
	 *
	 * @return callable
	 */
	public function provide_frontend_sign_up_handler( $handler, $user_id, $course_id ) {
		// Bail if this provider doesn't handle the course.
		if ( ! $this->handles_enrolment( $course_id ) ) {
			return $handler;
		}

		return [ $this, 'handle_frontend_sign_up' ];
	}

	/**
	 * Handle the frontend sign up for a course.
	 *
	 * @access private
	 *
	 * @param int $user_id   User ID.
	 * @param int $course_id Course post ID.
	 *
	 * @return bool True if successful.
	 */
	public function handle_frontend_sign_up( $user_id, $course_id ) {
		if ( ! $this->has_active_membership( $user_id, $course_id ) ) {
			return false;
		}

		$this->add_user_sign_up( $user_id, $course_id );
		Course_Enrolment_Providers::trigger_course_enrolment_check( $user_id, $course_id );

		return true;
	}

	/**
	 * Check if user can sign up for a course.
	 *
	 * @access private
	 *
	 * @param bool $can_sign_up If a user can sign up using the frontend form.
	 * @param int  $course_id   Course post ID.
	 *
	 * @return bool
	 */
	public function can_user_sign_up( $can_sign_up, $course_id ) {
		$user_id = \get_current_user_id();
		if (
			! $user_id
			|| ! $this->handles_enrolment( $course_id )
		) {
			return $can_sign_up;
		}

		if (
			! $this->has_active_membership( $user_id, $course_id )
			|| $this->is_signed_up( $user_id, $course_id )
		) {
			return false;
		}

		return true;
	}

	/**
	 * Check if this provider is active and a user has a membership for a course.
	 *
	 * @access private
	 *
	 * @param int $user_id   User ID.
	 * @param int $course_id Course post ID.
	 *
	 * @return bool
	 */
	public static function does_user_have_membership( $user_id, $course_id ) {
		if ( ! self::is_active() ) {
			return false;
		}

		if ( Course_Enrolment_Providers::is_learner_removal_enabled() ) {
			// Treat the user as not having a membership if he is removed from the course.
			if ( \Sensei_Course_Enrolment::get_course_instance( $course_id )->is_learner_removed( $user_id ) ) {
				return false;
			}
		}

		$provider_manager    = \Sensei_Course_Enrolment_Manager::instance();
		$self                = self::instance();
		$membership_provider = $provider_manager->get_enrolment_provider_by_id( $self->get_id() );

		if (
			! $membership_provider
			|| ! $membership_provider->handles_enrolment( $course_id )
		) {
			return false;
		}

		return $membership_provider->has_active_membership( $user_id, $course_id );
	}

	/**
	 * Get the provider state for a user.
	 *
	 * @param int $user_id   User ID.
	 * @param int $course_id Course post ID.
	 *
	 * @return \Sensei_Enrolment_Provider_State
	 */
	private function get_provider_state( $user_id, $course_id ) {
		$course_enrolment = \Sensei_Course_Enrolment::get_course_instance( $course_id );

		return $course_enrolment->get_provider_state( $this, $user_id );
	}

	/**
	 * Gets the version of the enrolment provider logic. If this changes, enrolment will be recalculated.
	 *
	 * This version should be bumped to the next stable plugin version whenever this provider is modified.
	 *
	 * @return int|string
	 */
	public function get_version() {
		return '2.0.0';
	}

	/**
	 * Before a Membership update, save data that we'll need when processing
	 * the update.
	 *
	 * @since 2.0.0
	 * @access private
	 *
	 * @param int $post_id The post ID.
	 */
	public function save_membership_pre_update_data( $post_id ) {
		if ( 'wc_user_membership' !== get_post_type( $post_id ) ) {
			return;
		}

		$this->membership_pre_update_data[ $post_id ] = [
			'plan_id' => wp_get_post_parent_id( $post_id ),
		];
	}

	/**
	 * Handles the `wc_memberships_user_membership_saved` action.
	 *
	 * This only runs when the Membeship is updated, not when one is created.
	 * This is because the meta fields are not set up correctly at the point
	 * where this is called, and recalculating enrolment would get the wrong
	 * value.
	 *
	 * @since 2.0.0
	 * @access private
	 *
	 * @param \WC_Memberships_Membership_Plan $plan The Membership plan.
	 * @param array                           $args The arguments.
	 */
	public function handle_membership_save( $plan, $args ) {
		if (
			! $plan instanceof \WC_Memberships_Membership_Plan
			|| ! isset( $args['user_id'] )
			|| ! isset( $args['user_membership_id'] )
		) {
			return;
		}

		$user_id = $args['user_id'];
		$this->invalidate_user_plan_enrolments( $user_id, $plan->get_id(), true );

		// If we're changing the Plan, handle the previous Plan as well.
		$membership_id    = $args['user_membership_id'];
		$previous_plan_id = isset( $this->membership_pre_update_data[ $membership_id ]['plan_id'] )
			? $this->membership_pre_update_data[ $membership_id ]['plan_id']
			: null;

		if ( $previous_plan_id && $plan->get_id() !== $previous_plan_id ) {
			$this->invalidate_user_plan_enrolments( $user_id, $previous_plan_id );
		}
	}

	/**
	 * Handles the `wc_memberships_user_membership_transfer` action.
	 *
	 * @since 2.0.0
	 * @access private
	 *
	 * @param \WC_Memberships_User_Membership $membership     The user membership.
	 * @param \WP_User                        $new_owner      The new owner.
	 * @param \WP_User                        $previous_owner The previous owner.
	 */
	public function handle_membership_transfer( $membership, $new_owner, $previous_owner ) {
		if (
			! $membership instanceof \WC_Memberships_User_Membership
			|| ! $previous_owner instanceof \WP_User
		) {
			return;
		}

		$plan_id = $membership->get_plan_id();
		$this->invalidate_user_plan_enrolments( $previous_owner->ID, $plan_id );
		$this->invalidate_user_plan_enrolments( $new_owner->ID, $plan_id, true );
	}

	/**
	 * Handles the `wc_memberships_user_membership_deleted` action.
	 *
	 * @since 2.0.0
	 * @access private
	 *
	 * @param \WC_Memberships_User_Membership $membership The user membership.
	 */
	public function save_membership_pre_delete_data( $membership ) {
		if ( ! $membership instanceof \WC_Memberships_User_Membership ) {
			return;
		}

		$id = $membership->get_id();

		// Set data for after deletion is complete.
		$this->membership_pre_delete_data[ $id ]            = [];
		$this->membership_pre_delete_data[ $id ]['user_id'] = $membership->get_user_id();
		$this->membership_pre_delete_data[ $id ]['plan_id'] = $membership->get_plan_id();
	}

	/**
	 * Allows a membership to be renewed even when active if the user has been removed from the course.
	 *
	 * @since 2.0.2
	 * @access private
	 *
	 * @param bool                            $renew  Default value to allow renewals.
	 * @param \WC_Memberships_Membership_Plan $plan   The membership plan.
	 * @param array                           $args   Additional args.
	 *
	 * @return bool Whether to allow renewal.
	 */
	public function allow_renewal_when_removed( $renew, \WC_Memberships_Membership_Plan $plan, $args ) {
		$course_ids = $this->get_membership_plan_course_ids( $plan->get_id() );

		foreach ( $course_ids as $course_id ) {
			if ( $this->was_membership_removed( $args['user_id'], $course_id ) ) {
				return true;
			}
		}

		return $renew;
	}

	/**
	 * Handles the `post_deleted` action to invalidate the user enrolments when
	 * their membership is deleted.
	 *
	 * @since 2.0.0
	 *
	 * @param int $post_id The post ID.
	 */
	public function handle_membership_post_deleted( $post_id ) {
		if (
			! isset( $this->membership_pre_delete_data[ $post_id ]['user_id'] )
			|| ! isset( $this->membership_pre_delete_data[ $post_id ]['plan_id'] )
		) {
			return;
		}

		$user_id = $this->membership_pre_delete_data[ $post_id ]['user_id'];
		$plan_id = $this->membership_pre_delete_data[ $post_id ]['plan_id'];
		$this->invalidate_user_plan_enrolments( $user_id, $plan_id );
	}

	/**
	 * Invalidate all enrolments for the given user on courses covered by the
	 * given plan.
	 *
	 * @since 2.0.0
	 *
	 * @param int  $user_id        The User ID.
	 * @param int  $plan_id        The Plan ID.
	 * @param bool $restore_users  If enabled, users that have been previously removed from the course will be restored.
	 */
	private function invalidate_user_plan_enrolments( $user_id, $plan_id, $restore_users = false ) {
		// Clear cache for this User and Plan.
		wc_memberships_is_user_active_or_delayed_member( $user_id, $plan_id, false );

		// Bypass Membership cache for this User and Plan in this request.
		$this->bypass_membership_cache_for_user[ $user_id ] = true;
		$this->bypass_membership_cache_for_plan[ $plan_id ] = true;

		$rules = wc_memberships()->get_rules_instance()->get_plan_rules( $plan_id );

		foreach ( $rules as $rule ) {
			$course_ids = $this->get_course_ids_from_rule( $rule );

			foreach ( $course_ids as $course_id ) {
				if ( $restore_users && $this->was_membership_removed( $user_id, $course_id ) ) {
					\Sensei_Course_Enrolment::get_course_instance( $course_id )->restore_learner( $user_id );
				}

				Course_Enrolment_Providers::trigger_course_enrolment_check( $user_id, $course_id );
			}
		}
	}

	/**
	 * Get course IDs from rule.
	 * According to the rule content type, it will get the courses directly or from taxonomy.
	 *
	 * @param \WC_Memberships_Membership_Plan_Rule $rule Rule to get the associated course IDs.
	 *
	 * @return int[] $course_ids Course IDs associated to rule.
	 */
	private function get_course_ids_from_rule( $rule ) {
		if ( 'content_restriction' !== $rule->get_rule_type() ) {
			return [];
		}

		$is_course_type          = $rule->is_content_type( 'post_type' ) && $rule->is_content_type_name( 'course' );
		$is_course_category_type = $rule->is_content_type( 'taxonomy' ) && $rule->is_content_type_name( 'course-category' );

		// Course restriction rule.
		if ( $is_course_type ) {
			return $this->get_course_ids_from_course_rule( $rule );
		}

		// Term restriction rule.
		if ( $is_course_category_type ) {
			return $this->get_course_ids_from_course_category_rule( $rule );
		}

		return [];
	}

	/**
	 * Get course IDs from course rule.
	 *
	 * @param \WC_Memberships_Membership_Plan_Rule $rule Rule to get the associated course IDs.
	 *
	 * @return int[] $course_ids Course IDs associated to rule.
	 */
	private function get_course_ids_from_course_rule( $rule ) {
		$course_ids = $rule->get_object_ids();

		// An empty rule for membership plan means all contents for the post type or taxonomy.
		if ( empty( $course_ids ) ) {
			return $this->get_all_course_ids();
		}

		return array_unique( $course_ids );
	}

	/**
	 * Get course IDs from course categories rule.
	 *
	 * @param \WC_Memberships_Membership_Plan_Rule $rule Rule to get the associated course IDs.
	 *
	 * @return int[] $course_ids Course IDs associated to rule.
	 */
	private function get_course_ids_from_course_category_rule( $rule ) {
		$course_category_ids = $rule->get_object_ids();
		$course_ids          = [];

		// An empty rule for membership plan means all contents for the post type or taxonomy.
		if ( empty( $course_category_ids ) ) {
			$course_category_ids = get_terms( 'course-category', [ 'hide_empty' => true ] );
		}

		foreach ( $course_category_ids as $course_category_id ) {
			$term_course_ids = $this->get_all_course_ids( $course_category_id );

			$course_ids = array_merge(
				$course_ids,
				$term_course_ids
			);
		}

		return array_unique( $course_ids );
	}

	/**
	 * Get all course IDs.
	 *
	 * @param int $course_category_id Course category id to filter the courses.
	 *
	 * @return int[] $course_ids Course IDs.
	 */
	private function get_all_course_ids( $course_category_id = null ) {
		$args = [
			'post_type'   => 'course',
			'post_status' => 'publish',
			'fields'      => 'ids',
			'numberposts' => -1,
		];

		if ( $course_category_id ) {
			$args['tax_query'] = [
				[
					'taxonomy' => 'course-category',
					'terms'    => $course_category_id,
				],
			];
		}

		return get_posts( $args );
	}

	/**
	 * Bypass the cached value for the given Membership if its User or Plan
	 * matched one of the given IDs. This is needed because WC Memberships
	 * does not properly reset its cache when the User or Plan is changed
	 * for a Membership.
	 *
	 * @since 2.0.0
	 * @access private
	 *
	 * @param \WC_Memberships_User_Membership $user_membership The Membership.
	 *
	 * @return \WC_Memberships_User_Membership|null The Membership from the DB.
	 */
	public function bypass_user_membership_cache( $user_membership ) {
		// Reload the Membership from the DB if needed.
		if ( $this->should_bypass_cache_for_membership( $user_membership ) ) {
			$user_memberships = get_posts(
				[
					'author'      => $user_membership->get_user_id(),
					'post_type'   => 'wc_user_membership',
					'post_parent' => $user_membership->get_plan_id(),
					'post_status' => 'any',
				]
			);

			if ( empty( $user_memberships ) ) {
				return null;
			}

			return new \WC_Memberships_User_Membership( $user_memberships[0] );
		}

		return $user_membership;
	}

	/**
	 * Determine whether we should bypass the WC Memberships cache for the given
	 * Membership.
	 *
	 * @param \WC_Memberships_User_Membership $user_membership The Membership.
	 *
	 * @return bool
	 */
	private function should_bypass_cache_for_membership( $user_membership ) {
		$user_id = $user_membership->get_user_id();
		$plan_id = $user_membership->get_plan_id();

		return (
			isset( $this->bypass_membership_cache_for_user[ $user_id ] )
			&& $this->bypass_membership_cache_for_user[ $user_id ]
		) || (
			isset( $this->bypass_membership_cache_for_plan[ $plan_id ] )
			&& $this->bypass_membership_cache_for_plan[ $plan_id ]
		);
	}

	/**
	 * Handle membership plan pre update.
	 *
	 * Hooked into `pre_post_update`.
	 *
	 * @since 2.0.0
	 * @access private
	 *
	 * @param int $post_id Post ID.
	 */
	public function handle_membership_plan_pre_update_data( $post_id ) {
		if ( 'wc_membership_plan' !== get_post_type( $post_id ) ) {
			return;
		}

		$membership_plan = wc_memberships_get_membership_plan( $post_id );

		$rules_clone = array_map(
			function( $rule ) {
				return clone $rule;
			},
			$membership_plan->get_rules( 'content_restriction' )
		);

		$this->membership_plan_pre_update_data = [
			'plan_id' => $post_id,
			'status'  => get_post_status( $post_id ),
			'rules'   => $rules_clone,
		];
	}

	/**
	 * Handle membership plan update.
	 *
	 * Hooked into `shutdown`.
	 *
	 * @since 2.0.0
	 * @access private
	 */
	public function handle_membership_plan_update() {
		if ( empty( $this->membership_plan_pre_update_data ) ) {
			return;
		}

		$plan_id = $this->membership_plan_pre_update_data['plan_id'];

		if ( 'wc_membership_plan' !== get_post_type( $plan_id ) ) {
			return;
		}

		$new_status    = get_post_status( $plan_id );
		$old_status    = $this->membership_plan_pre_update_data['status'];
		$active_status = 'publish';
		$publishing    = $old_status !== $active_status && $new_status === $active_status;
		$unpublishing  = $old_status === $active_status && $new_status !== $active_status;

		// Skips if the post is remaining unpublished.
		if ( $new_status !== $active_status && $old_status !== $active_status ) {
			return;
		}

		$membership_plan = wc_memberships_get_membership_plan( $plan_id );
		$new_rules       = $membership_plan->get_rules( 'content_restriction' );
		$old_rules       = $this->membership_plan_pre_update_data['rules'];

		$courses_to_recalculate = array_unique(
			array_merge(
				$this->get_courses_to_recalculate_from_new_rules( $new_rules, $old_rules, $publishing, $unpublishing ),
				$this->get_courses_to_recalculate_from_old_rules( $old_rules, $new_rules )
			)
		);

		if ( empty( $courses_to_recalculate ) ) {
			return;
		}

		Membership_Plan_Calculation_Job::start( $plan_id, $courses_to_recalculate );
	}

	/**
	 * Get "courses to recalculate" looping through new rules.
	 *
	 * @param \WC_Memberships_Membership_Plan_Rule[] $new_rules    Rules after the update.
	 * @param \WC_Memberships_Membership_Plan_Rule[] $old_rules    Rules before the update.
	 * @param bool                                   $publishing   Flag if it is publishing the plan.
	 * @param bool                                   $unpublishing Flag if it is unpublishing the plan.
	 *
	 * @return int[] $courses_to_recalculate Array with courses ID that need recalculation.
	 */
	private function get_courses_to_recalculate_from_new_rules( $new_rules, $old_rules, $publishing, $unpublishing ) {
		$courses_to_recalculate = [];

		foreach ( $new_rules as $key => $rule ) {
			$new_course_ids  = $this->get_course_ids_from_rule( $rule );
			$access_schedule = $rule->get_access_schedule();

			$old_rule            = isset( $old_rules[ $key ] ) ? $old_rules[ $key ] : null;
			$old_course_ids      = isset( $old_rule ) ? $this->get_course_ids_from_rule( $old_rule ) : [];
			$old_access_schedule = isset( $old_rule ) ? $old_rule->get_access_schedule() : null;

			if ( $access_schedule !== $old_access_schedule || $publishing || $unpublishing ) {
				// Set all courses to recalculate if changing access schedule,
				// only new courses if publishing, or only old courses if unpublishing.
				$courses_to_recalculate = array_merge(
					$courses_to_recalculate,
					$unpublishing ? [] : $new_course_ids,
					$publishing ? [] : $old_course_ids
				);

				continue;
			}

			// Set only added and removed courses to recalculate.
			$added_courses   = array_diff( $new_course_ids, $old_course_ids );
			$removed_courses = array_diff( $old_course_ids, $new_course_ids );

			$courses_to_recalculate = array_merge(
				$courses_to_recalculate,
				$added_courses,
				$removed_courses
			);
		}

		return array_unique( $courses_to_recalculate );
	}

	/**
	 * Get "courses to recalculate" from removed rules (The old ones that is not on the new rules).
	 *
	 * @param \WC_Memberships_Membership_Plan_Rule[] $old_rules Rules before the update.
	 * @param \WC_Memberships_Membership_Plan_Rule[] $new_rules Rules after the update.
	 *
	 * @return int[] $courses_to_recalculate Array with courses ID that need recalculation.
	 */
	private function get_courses_to_recalculate_from_old_rules( $old_rules, $new_rules ) {
		$courses_to_recalculate = [];

		foreach ( $old_rules as $key => $old_rule ) {
			if ( isset( $new_rules[ $key ] ) ) {
				continue;
			}

			$course_ids = $this->get_course_ids_from_rule( $old_rule );

			$courses_to_recalculate = array_merge(
				$courses_to_recalculate,
				$course_ids
			);
		}

		return array_unique( $courses_to_recalculate );
	}

	/**
	 * Handle course category change.
	 *
	 * Hooked into `set_object_terms`.
	 *
	 * @since 2.0.0
	 * @access private
	 *
	 * @param int    $object_id  Object ID.
	 * @param array  $terms      An array of object terms.
	 * @param array  $tt_ids     An array of term taxonomy IDs.
	 * @param string $taxonomy   Taxonomy slug.
	 * @param bool   $append     Whether to append new terms to the old terms.
	 * @param array  $old_tt_ids Old array of term taxonomy IDs.
	 */
	public function handle_course_category_update( $object_id, $terms, $tt_ids, $taxonomy, $append, $old_tt_ids ) {
		$course_category_taxonomy = 'course-category';

		if ( $course_category_taxonomy !== $taxonomy ) {
			return;
		}

		$changed_terms = array_merge( $terms, $old_tt_ids );
		$course_id     = $object_id;

		foreach ( $changed_terms as $term ) {
			$rules = wc_memberships()->get_rules_instance()->get_taxonomy_term_content_restriction_rules( $taxonomy, $term );

			foreach ( $rules as $rule ) {
				$plan_id = $rule->get_membership_plan_id();

				Membership_Plan_Calculation_Job::start( $plan_id, [ $course_id ] );
			}
		}
	}

	/**
	 * Remove the `manage_woocommerce` cap when checking if a user has
	 * enrolment through this provider. Otherwise this cap will give enrolment
	 * regardless of whether the user has a Membership.
	 *
	 * @since 2.0.0
	 * @access private
	 *
	 * @param array $allcaps All capabilities for the user.
	 * @param array $caps    The capabilities being checked.
	 */
	public function remove_manage_woocommerce_cap( $allcaps, $caps ) {
		if (
			in_array( 'wc_memberships_view_restricted_post_content', $caps, true )
			|| in_array( 'wc_memberships_view_delayed_post_content', $caps, true )
		) {
			$allcaps['manage_woocommerce'] = false;
		}

		return $allcaps;
	}

	/**
	 * Prevent learners who have progress and an active membership from being granted manual enrolment.
	 *
	 * Legacy behavior often kept people enrolled after their membership ended. To not break that past behavior,
	 * this will provide manual enrolment to those who had access because it was never withdrawn. However,
	 * going forward if a membership ends the user's enrolment will be withdrawn.
	 *
	 * @param bool $is_legacy_enrolled If manual enrolment should be provided. Starts as true if they had course progress.
	 * @param int  $user_id            User ID.
	 * @param int  $course_id          Course post ID.
	 *
	 * @return bool
	 */
	public function maybe_allow_legacy_manual_enrolment( $is_legacy_enrolled, $user_id, $course_id ) {
		// No need to check if they are already not being given manual enrolment or this provider doesn't handle enrolment.
		if ( ! $is_legacy_enrolled || ! $this->handles_enrolment( $course_id ) ) {
			return $is_legacy_enrolled;
		}

		// If the user has an active membership, do not allow them to be provided with manual enrolment.
		if ( $this->has_active_membership( $user_id, $course_id ) ) {
			return false;
		}

		/**
		 * Allows sites to provide manual enrolment to learners with inactive memberships on migration.
		 * This defaults to `true` because most learners with inactive memberships maintained enrolment in the course
		 * before Sensei v3.
		 *
		 * Filter this flag to return `false` to not provide manual enrolment on migration to anyone with an ended
		 * membership that would provide access to this course. Once the initial migration to v3 has occurred, this
		 * filter does not have any effect.
		 *
		 * @since 2.0.0
		 *
		 * @param bool $provide_manual_enrolment Whether ended memberships should be provided manual enrolment.
		 * @param int  $user_id                  User ID.
		 * @param int  $course_id                Course post ID.
		 */
		$inactive_memberships_provide_manual_enrolment = apply_filters( 'sensei_wc_paid_courses_migration_inactive_memberships_provide_manual_enrolment', true, $user_id, $course_id );
		if (
			! $inactive_memberships_provide_manual_enrolment
			&& $this->has_inactive_membership( $user_id, $course_id )
		) {
			return false;
		}

		return $is_legacy_enrolled;
	}

	/**
	 * Get the latest start date for the user's memberships for a course.
	 *
	 * @param int $user_id   User ID.
	 * @param int $course_id Course post ID.
	 *
	 * @return string
	 */
	private function get_latest_membership_start_date( $user_id, $course_id ) {
		$memberships       = $this->get_active_memberships_with_course( $user_id, $course_id );
		$latest_start_date = null;
		foreach ( $memberships as $membership ) {
			if (
				null === $latest_start_date
				|| ( $membership->post->post_date_gmt > $latest_start_date )
			) {
				$latest_start_date = $membership->post->post_date_gmt;
			}
		}

		return $latest_start_date;
	}

	/**
	 * Get the active memberships that provide access to a course.
	 *
	 * @param int   $user_id   User ID.
	 * @param int   $course_id Course post ID.
	 * @param array $args      Membership query arguments.
	 *
	 * @return \WC_Memberships_User_Membership[]
	 */
	private function get_active_memberships_with_course( $user_id, $course_id, $args = [] ) {
		$args['status'] = wc_memberships()->get_user_memberships_instance()->get_active_access_membership_statuses();

		return $this->get_memberships_with_course( $user_id, $course_id, $args );
	}

	/**
	 * Checks to see if a user has an ended membership.
	 *
	 * @param int $user_id   User ID.
	 * @param int $course_id Course post ID.
	 *
	 * @return bool
	 */
	private function has_inactive_membership( $user_id, $course_id ) {
		$cancelled_memberships = $this->get_inactive_memberships_with_course( $user_id, $course_id );

		return ! empty( $cancelled_memberships );
	}

	/**
	 * Get the cancelled memberships that provide access to a course.
	 *
	 * @param int   $user_id   User ID.
	 * @param int   $course_id Course post ID.
	 * @param array $args      Membership query arguments.
	 *
	 * @return \WC_Memberships_User_Membership[]
	 */
	private function get_inactive_memberships_with_course( $user_id, $course_id, $args = [] ) {
		$cancelled_statuses = [
			'cancelled',
			'expired',
			'paused',
		];

		$args['status'] = $cancelled_statuses;

		return $this->get_memberships_with_course( $user_id, $course_id, $args );
	}

	/**
	 * Get the memberships that provide access to a course.
	 *
	 * @param int   $user_id   User ID.
	 * @param int   $course_id Course post ID.
	 * @param array $args      Membership query arguments.
	 *
	 * @return \WC_Memberships_User_Membership[]
	 */
	private function get_memberships_with_course( $user_id, $course_id, $args = [] ) {
		$course_id          = intval( $course_id );
		$course_memberships = [];
		$user_memberships   = wc_memberships()->get_user_memberships_instance()->get_user_memberships( $user_id, $args );

		foreach ( $user_memberships as $membership ) {
			$plan       = $membership->get_plan();
			$course_ids = $this->get_membership_plan_course_ids( $plan->get_id() );
			if ( in_array( $course_id, $course_ids, true ) ) {
				$course_memberships[] = $membership;
			}
		}

		return $course_memberships;
	}

	/**
	 * Store a snapshot of the 'wc_memberships_rules' option.
	 *
	 * @see maybe_recalculate_course_enrolments
	 *
	 * @access private
	 */
	public function store_membership_rules() {
		if ( 'course' === get_post_type() ) {
			$this->membership_rules_before_course_update = get_option( 'wc_memberships_rules', false );
		}
	}

	/**
	 * Detect any changes in 'wc_memberhips_rules' option and possibly trigger enrolment recalculation.
	 *
	 * @access private
	 */
	public function maybe_recalculate_course_enrolments() {
		if ( false === $this->membership_rules_before_course_update || 'course' !== get_post_type() ) {
			return;
		}

		$membership_rules_after = get_option( 'wc_memberships_rules', [] );

		if ( $membership_rules_after !== $this->membership_rules_before_course_update ) {
			\Sensei_Course_Enrolment::get_course_instance( get_post()->ID )->recalculate_enrolment();
		}
	}
	/**
	 * Provide debugging information about a user's enrolment in a course.
	 *
	 * @param int $user_id   User ID.
	 * @param int $course_id Course post ID.
	 *
	 * @return string[] Array of human readable debug messages. Allowed HTML tags: a[href]; strong; em; span[style,class]
	 */
	public function debug( $user_id, $course_id ) {
		$messages = [];

		if ( ! $this->handles_enrolment( $course_id ) ) {
			return $messages;
		}

		$has_active_membership = false;

		$memberships = $this->get_memberships_with_course( $user_id, $course_id );
		foreach ( $memberships as $membership ) {
			if ( $membership->is_active() ) {
				$has_active_membership                                    = true;
				$messages[ 'active-membership-' . $membership->get_id() ] = sprintf(
					// translators: %1$s is URL to edit/view the membership; #%2$s is the membership number.
					__( '<a href="%1$s">Membership #%2$s</a> has an active status and is providing access to enrollment.', 'sensei-pro' ),
					get_edit_post_link( $membership->get_id() ),
					$membership->get_id()
				);
			} else {
				$messages[ 'inactive-membership-' . $membership->get_id() ] = sprintf(
					// translators: %1$s is URL to edit/view the membership; #%2$s is the membership number.
					__( '<a href="%1$s">Membership #%2$s</a> would provide access to enrollment for this course but it does not have an active status.', 'sensei-pro' ),
					get_edit_post_link( $membership->get_id() ),
					$membership->get_id()
				);
			}
		}

		if ( empty( $memberships ) ) {
			$messages['no-memberships'] = __( 'Learner does not have any memberships that would provide access to enrollment for this course.', 'sensei-pro' );
		}

		if ( $this->is_signed_up( $user_id, $course_id ) ) {
			if ( $has_active_membership ) {
				$messages['signed-up-with-membership'] = __( 'Learner has signed up for this course and has an active membership.', 'sensei-pro' );
			} else {
				$messages['signed-up-without-membership'] = __( 'Learner has signed up for this course but does not have an active membership.', 'sensei-pro' );
			}
		} else {
			if ( $has_active_membership ) {
				$messages['not-signed-up-with-membership'] = __( 'Learner has not signed up for this course but has an active membership so they should be able to sign up from the course page.', 'sensei-pro' );
			} else {
				$messages['not-signed-up-without-membership'] = __( 'Learner has not signed up for this course and is not able to without an active membership.', 'sensei-pro' );
			}
		}

		return $messages;
	}

	/**
	 * Helper method which checks if a user which has an active membership was removed from the course.
	 *
	 * @param int $user_id    The user.
	 * @param int $course_id  The course.
	 *
	 * @return bool
	 */
	private function was_membership_removed( $user_id, $course_id ) {
		if ( ! Course_Enrolment_Providers::is_learner_removal_enabled() ) {
			return false;
		}

		return $this->handles_enrolment( $course_id )
			&& \Sensei_Course_Enrolment::get_course_instance( $course_id )->is_learner_removed( $user_id )
			&& $this->has_active_membership( $user_id, $course_id );
	}
}
