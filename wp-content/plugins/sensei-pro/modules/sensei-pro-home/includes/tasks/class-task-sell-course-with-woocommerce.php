<?php
/**
 * Sell Course With WooCommerce task.
 *
 * @package senseilms-home
 * @since   1.23.0
 */

namespace Sensei_Pro_Home\Tasks;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Sensei Pro Home main class.
 */
class Task_Sell_Course_With_WooCommerce {
	const CONFIGURED_TO_SELL_COURSE_WITH_WOOCOMMERCE_OPTION_KEY = 'sensei_pro_home_task_configured_to_sell_course_with_woocommerce';

	const SELL_COURSE_TASK_ID = 'sell-course-with-woocommerce';

	/**
	 * Class instance.
	 *
	 * @var Task_Sell_Course_With_WooCommerce
	 */
	private static $instance;

	/**
	 * Retrieve the Task_Sell_Course_With_WooCommerce instance.
	 */
	public static function instance(): Task_Sell_Course_With_WooCommerce {
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
		$instance = self::instance();

		if ( ! defined( 'SENSEI_PRO_PLUGIN_DIR_URL' ) ) {
			// We do not want to run this on Sensei Interactive Blocks.
			return;
		}

		add_filter( 'sensei_home_tasks', [ $instance, 'add_sell_course_task' ] );
		add_action( 'added_post_meta', [ $instance, 'complete_sell_course_task' ], 10, 3 );
	}

	/**
	 * Add (or overwrite if it already exists) the task "Sell your Course with WooCommerce" to the task list returned by Sensei Home.
	 *
	 * @param array $tasks The original tasks array to modify.
	 *
	 * @return array The tasks array with the sell course task included.
	 */
	public function add_sell_course_task( array $tasks ): array {
		if ( ! $this->should_add_task() ) {
			return $tasks;
		}

		$task_id           = self::SELL_COURSE_TASK_ID;
		$tasks[ $task_id ] = [
			'id'       => $task_id,
			'title'    => esc_html__( 'Sell your course with WooCommerce', 'sensei-pro' ),
			'priority' => 400,
			'done'     => $this->persist_and_check_course_with_products(),
		];

		$edit_link_for_last_course = $this->get_edit_link_for_last_course();
		$site_has_products         = $this->site_has_products();

		if ( $edit_link_for_last_course && $site_has_products ) {
			$tasks[ $task_id ]['url'] = $edit_link_for_last_course;
		} else {
			$tasks[ $task_id ]['disabled'] = true;
			$tasks[ $task_id ]['info']     = esc_html__( 'Create a course and product to unlock this task', 'sensei-pro' );
		}

		return $tasks;
	}

	/**
	 * Check if the task should be added.
	 *
	 * @return bool
	 */
	private function should_add_task(): bool {
		if ( ! function_exists( 'get_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}
		if ( ! is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
			return false;
		}

		$features = Sensei()->setup_wizard->get_wizard_user_data( 'features' );
		return in_array( 'woocommerce', $features['selected'], true );
	}

	/**
	 * Get the edit link for the last created course.
	 *
	 * @return string|false The edit link for the last created course, or false if no course is found.
	 */
	private function get_edit_link_for_last_course() {
		$last_course = get_posts(
			[
				'post_type'      => 'course',
				'posts_per_page' => 1,
				'orderby'        => 'date',
				'order'          => 'DESC',
				'post_status'    => [ 'publish', 'draft' ],
			]
		);

		if ( empty( $last_course ) ) {
			return false;
		}

		return add_query_arg(
			[
				'show-course-sell-tour' => 'true',
			],
			get_edit_post_link( $last_course[0]->ID, '&' )
		);
	}

	/**
	 * Check if the site has products.
	 *
	 * @return bool
	 */
	private function site_has_products(): bool {
		$products = wc_get_products(
			[
				'limit'  => 1,
				'status' => [ 'publish', 'draft' ],
			]
		);

		return ! empty( $products );
	}

	/**
	 * Check if there is at least one course with products and persist it.
	 *
	 * @return bool
	 */
	private function persist_and_check_course_with_products(): bool {
		$has_course_with_products = get_option( self::CONFIGURED_TO_SELL_COURSE_WITH_WOOCOMMERCE_OPTION_KEY, -1 );

		if ( -1 !== $has_course_with_products && $has_course_with_products ) {
			return (bool) $has_course_with_products;
		}

		$courses_with_products = get_posts(
			[
				'post_type'        => 'course',
				'meta_key'         => '_course_woocommerce_product',
				'meta_compare_key' => 'EXISTS',
				'post_status'      => [ 'publish', 'draft' ],
				'posts_per_page'   => 1,
			]
		);

		$has_course_with_products = ! empty( $courses_with_products ) ? 1 : 0;

		if ( $has_course_with_products ) {
			sensei_log_event( 'home_task_complete', [ 'type' => self::SELL_COURSE_TASK_ID ] );
			update_option( self::CONFIGURED_TO_SELL_COURSE_WITH_WOOCOMMERCE_OPTION_KEY, $has_course_with_products, false );
		}

		return (bool) $has_course_with_products;
	}

	/**
	 * Complete the task "Sell your Course with WooCommerce" when a product is related to a course.
	 *
	 * @param int    $meta_id   ID of updated metadata entry.
	 * @param int    $object_id Post ID.
	 * @param string $meta_key  Metadata key.
	 */
	public function complete_sell_course_task( $meta_id, $object_id, $meta_key ) {
		if ( '_course_woocommerce_product' !== $meta_key ) {
			return;
		}

		$post_type = get_post_type( $object_id );

		if ( 'course' !== $post_type ) {
			return;
		}

		// Persist result to complete the task.
		$this->persist_and_check_course_with_products();
	}
}
