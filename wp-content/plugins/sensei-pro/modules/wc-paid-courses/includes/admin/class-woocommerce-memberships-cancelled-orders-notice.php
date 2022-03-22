<?php
/**
 * File containing the class \Sensei_WC_Paid_Courses\Admin\WooCommerce_Memberships_Cancelled_Orders_Notice.
 *
 * @package sensei-wc-paid-courses
 * @since   2.0.0
 */

namespace Sensei_WC_Paid_Courses\Admin;

use Sensei_WC_Paid_Courses\Background_Jobs\WooCommerce_Memberships_Detect_Cancelled_Orders;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class that handles
 *
 * @access private This class will only exist while supporting legacy enrolment migration.
 * @class Sensei_WC_Paid_Courses\Admin\WooCommerce_Memberships_Cancelled_Orders_Notice
 */
class WooCommerce_Memberships_Cancelled_Orders_Notice {
	const SPECIAL_POST_STATUS           = 'sensei-wc-paid-courses-memberships-cancelled-orders';
	const VIEW_MEMBERSHIPS_NONCE_ACTION = 'sensei-wc-paid-courses-view-memberships-cancelled-orders';
	const DISMISS_NOTICE_NONCE_ACTION   = 'sensei-wc-paid-courses-memberships-cancelled-orders-notice-dismiss';

	/**
	 * Instance of class.
	 *
	 * @var self
	 */
	private static $instance;

	/**
	 * Notice and membership detection state.
	 *
	 * @var array
	 */
	private $notice_state;

	/**
	 * Class constructor. Prevents other instances from being created outside of `WooCommerce_Memberships_Cancelled_Orders_Notice::instance()`.
	 */
	private function __construct() {}

	/**
	 * Initialize actions and filters for the class.
	 *
	 * @access private
	 */
	public function init() {
		if ( ! is_admin() || ! $this->get_membership_ids() ) {
			return;
		}

		add_filter( 'admin_notices', [ $this, 'add_admin_notice' ] );
		add_action( 'wp_ajax_sensei_wc_paid_listings_dismiss_membership_cancelled_orders_notice', [ $this, 'handle_notice_dismiss' ] );
		add_filter( 'views_edit-wc_user_membership', [ $this, 'modify_membership_views_list' ] );
		add_filter( 'parse_query', [ $this, 'modify_membership_view' ] );
	}

	/**
	 * Handle the special query of viewing active memberships associated with cancelled orders.
	 *
	 * @param \WP_Query $query Query that is being filtered.
	 *
	 * @return \WP_Query
	 */
	public function modify_membership_view( $query ) {
		global $typenow;

		if (
			! $this->is_memberships_special_view()
			|| 'wc_user_membership' !== $typenow
		) {
			return $query;
		}

		$membership_ids = $this->get_membership_ids();
		$query->set( 'post__in', $membership_ids );
		$query->set( 'post_status', 'wcm-active' );

		return $query;
	}

	/**
	 * Includes the active with cancelled orders view above the memberships listing.
	 *
	 * @param array $views View links on membership page.
	 *
	 * @return array
	 */
	public function modify_membership_views_list( $views ) {
		if ( ! $this->is_memberships_special_view() ) {
			return $views;
		}

		$custom_views = [
			'active_cancelled_orders' => '<strong> ' . __( 'Active with Cancelled Orders', 'sensei-pro' ) . '</strong>',
		];

		return array_merge( $custom_views, $views );
	}

	/**
	 * Handle the dismissal of the notice.
	 *
	 * @access private
	 */
	public function handle_notice_dismiss() {
		\check_ajax_referer( self::DISMISS_NOTICE_NONCE_ACTION, 'nonce' );

		if ( ! $this->can_manage_memberships() ) {
			wp_die( '', '', 403 );
		}

		if ( $this->is_notice_dismissed() ) {
			return;
		}

		$this->dismiss_notice();
	}

	/**
	 * Dismiss the notice.
	 */
	private function dismiss_notice() {
		$notice_state = $this->get_notice_state();

		$notice_state['status'] = 'dismissed';

		$this->notice_state = $notice_state;

		\update_option( WooCommerce_Memberships_Detect_Cancelled_Orders::TRACKED_MEMBERSHIP_RESULTS_OPTION, \wp_json_encode( $notice_state ) );
	}

	/**
	 * Output the admin notice.
	 *
	 * @access private
	 */
	public function add_admin_notice() {
		if (
			! $this->can_manage_memberships()
			|| ! $this->can_see_notice_on_screen()
			|| $this->is_notice_dismissed()
		) {
			return;
		}

		add_action( 'admin_footer', [ $this, 'output_dismiss_js' ] );

		$allowed_html = [
			'strong' => true,
		];

		$view_memberships_url = \wp_nonce_url( admin_url( 'edit.php?post_type=wc_user_membership&post_status=' . self::SPECIAL_POST_STATUS ), self::VIEW_MEMBERSHIPS_NONCE_ACTION );
		?>
		<div id="sensei-wc-paid-courses-membership-cancelled-orders-notice" class="notice notice-info is-dismissible"
				data-nonce="<?php echo esc_attr( wp_create_nonce( self::DISMISS_NOTICE_NONCE_ACTION ) ); ?>">
			<p>
				<?php
				esc_html_e(
					'Sensei Pro (WooCommerce Paid Courses) has detected active memberships associated with cancelled orders.',
					'sensei-pro'
				);
				?>
			</p>
			<p>
				<?php
				echo wp_kses(
					__(
						'Prior to Sensei Pro (WooCommerce Paid Courses) 3.0, active memberships with cancelled orders
						<strong>did not</strong> provide learners with access to the membership\'s associated courses.
						As of Sensei Pro (WooCommerce Paid Courses) 3.0, active memberships (regardless of order status)
						<strong>do</strong> provide learners with access to the membership\'s associated courses.',
						'sensei-pro'
					),
					$allowed_html
				);
				?>
			</p>
			<p>
				<?php
				esc_html_e(
					'Please review and manually cancel any memberships where the learner should not have access to
					the courses associated with that membership.',
					'sensei-pro'
				);
				?>
			</p>
			<p>
				<a href="<?php echo esc_url( $view_memberships_url ); ?>" class="button button-primary">
					<?php esc_html_e( 'View Active Memberships with Cancelled Orders', 'sensei-pro' ); ?>
				</a>
			</p>
		</div>
		<?php
	}

	/**
	 * Output the JS for dismissing the notice.
	 *
	 * @access private
	 **/
	public function output_dismiss_js() {
		?>
		<script type="text/javascript">
			( function() {
				var noticeSelector = '#sensei-wc-paid-courses-membership-cancelled-orders-notice';
				var $notice = jQuery( noticeSelector );
				if ( $notice.length === 0 ) {
					return;
				}

				var nonce = $notice.data( 'nonce' );

				// Handle button clicks
				jQuery( noticeSelector ).on( 'click', 'button.notice-dismiss', function() {
					jQuery.ajax( {
						type: 'POST',
						url: ajaxurl,
						data: {
							action: 'sensei_wc_paid_listings_dismiss_membership_cancelled_orders_notice',
							nonce: nonce,
						}
					} );
				} );
			} )();
		</script>
		<?php
	}

	/**
	 * Check if we're on the special view in the membership listing.
	 *
	 * @access private
	 *
	 * @return bool
	 */
	public function is_memberships_special_view() {
		$screen = function_exists( 'get_current_screen' ) ? \get_current_screen() : false;
		if (
			// Check to make sure we're on the right screen (when the screen is available).
			(
				$screen
				&& 'edit-wc_user_membership' !== $screen->id
			)

			// Make sure we're viewing the special view.
			|| empty( $_GET['post_status'] )
			|| self::SPECIAL_POST_STATUS !== $_GET['post_status']

			// Make sure we have a valid nonce.
			|| ! isset( $_GET['_wpnonce'] )
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Leave nonce value unmodified.
			|| ! \wp_verify_nonce( \wp_unslash( $_GET['_wpnonce'] ), self::VIEW_MEMBERSHIPS_NONCE_ACTION )

			// One more check for permissions.
			|| ! $this->can_manage_memberships()
		) {
			return false;
		}

		return true;
	}

	/**
	 * Check if current user can see admin notice and manage memberships.
	 *
	 * @return bool
	 */
	private function can_manage_memberships() {
		return current_user_can( 'manage_sensei' ) && current_user_can( 'manage_woocommerce' );
	}

	/**
	 * Check to see if user is on a screen that should see the notice.
	 *
	 * @return bool
	 */
	private function can_see_notice_on_screen() {
		$valid_screens = [
			'course',
			'plugins',
			'plugins-network',
			'dashboard',
			'edit-wc_user_membership',
			'edit-wc_membership_plan',
			'edit-shop_order',
			'sensei-lms_page_sensei_learners',
		];
		$screen        = \get_current_screen();

		if (
			! $screen
			|| ! in_array( $screen->id, $valid_screens, true )
		) {
			return false;
		}

		return true;
	}

	/**
	 * Check to see if this notice is dismissed.
	 *
	 * @return bool
	 */
	private function is_notice_dismissed() {
		$notice_state = $this->get_notice_state();

		// Check if notice is still applicable.
		if ( ! $this->are_memberships_active() ) {
			$this->dismiss_notice();

			return true;
		}

		if (
			$notice_state
			&& isset( $notice_state['status'] )
			&& 'dismissed' === $notice_state['status']
		) {
			return true;
		}

		return false;
	}

	/**
	 * Check if the found memberships are still active.
	 *
	 * @return bool
	 */
	private function are_memberships_active() {
		$membership_ids = $this->get_membership_ids();
		if ( empty( $membership_ids ) ) {
			return false;
		}

		$query = new \WP_Query(
			[
				'post_type'   => 'wc_user_membership',
				'post__in'    => $membership_ids,
				'post_status' => 'wcm-active',
				'fields'      => 'ids',
			]
		);

		return $query->found_posts > 0;
	}

	/**
	 * Get the membership IDs tied to cancelled orders. Returns `false` if none have been detected yet.
	 *
	 * @return false|int[]
	 */
	private function get_membership_ids() {
		$notice_state = $this->get_notice_state();

		if (
			! $notice_state
			|| ! isset( $notice_state['status'] )
			|| 'pending' === $notice_state['status']
		) {
			return false;
		}

		return $this->notice_state['ids'];
	}

	/**
	 * Gets the state of the notice and membership detection.
	 *
	 * @return array|false
	 */
	private function get_notice_state() {
		if ( ! isset( $this->notice_state ) ) {
			$this->notice_state = false;
			$notice_state_json  = \get_option( WooCommerce_Memberships_Detect_Cancelled_Orders::TRACKED_MEMBERSHIP_RESULTS_OPTION );

			if ( $notice_state_json ) {
				$notice_state = json_decode( $notice_state_json, true );

				if ( $notice_state ) {
					if ( isset( $notice_state['ids'] ) ) {
						$notice_state['ids'] = array_map( 'absint', $notice_state['ids'] );
					}

					$this->notice_state = $notice_state;
				}
			}
		}

		return $this->notice_state;
	}

	/**
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
}
