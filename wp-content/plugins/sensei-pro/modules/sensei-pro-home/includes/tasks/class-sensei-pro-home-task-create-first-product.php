<?php
/**
 * Sensei_Pro_Home_Task_Create_First_Product class.
 *
 * @package senseilms-home
 * @since   1.23.0
 */

namespace Sensei_Pro_Home\Tasks;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Sensei_Pro_Home_Task_Create_First_Product main class.
 *
 * @since 1.23.0
 */
class Sensei_Pro_Home_Task_Create_First_Product {
	const CREATED_FIRST_PRODUCT_OPTION_KEY = 'sensei_pro_home_task_created_first_product';
	const CREATE_FIRST_PRODUCT_TASK_ID     = 'create-first-product';

	/**
	 * Class instance.
	 *
	 * @var Sensei_Pro_Home_Task_Create_First_Product | null
	 */
	private static $instance;

	/**
	 * Class constructor.
	 */
	private function __construct() {
		// Silence is golden.
	}

	/**
	 * Retrieve the Sensei_Pro_Home_Task_Create_First_Product instance.
	 */
	public static function instance(): Sensei_Pro_Home_Task_Create_First_Product {
		if ( ! self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}
	/**
	 * Initializes the class and adds all filters and actions.
	 */
	public function init() {
		$instance = self::instance();

		if ( ! $instance->should_add_task() ) {
			return;
		}

		if ( ! defined( 'SENSEI_PRO_PLUGIN_DIR_URL' ) ) {
			// We do not want to run this on Sensei Interactive Blocks.
			return;
		}

		add_filter( 'sensei_home_tasks', [ $instance, 'add_create_first_product_task' ] );

		// This is needed for the case when the product is created via the product page.
		add_action( 'woocommerce_update_product', [ $instance, 'check_and_persist_if_task_is_completed' ] );
		// This is needed for the case when the product is created via the quick edit, or the Course sidebar.
		add_action( 'woocommerce_new_product', [ $instance, 'check_and_persist_if_task_is_completed' ] );
	}

	/**
	 * Adds the task to create the first product.
	 *
	 * @param array $tasks The original tasks array to modify.
	 *
	 * @return array The tasks array with the "create-first-product" included.
	 */
	public function add_create_first_product_task( $tasks ) {
		$task_id             = self::CREATE_FIRST_PRODUCT_TASK_ID;
		$woo_new_proudct_url = admin_url( 'post-new.php?post_type=product' );

		$tasks[ $task_id ] = [
			'id'       => $task_id,
			'title'    => __( 'Create your first product', 'sensei-pro' ),
			'priority' => 220,
			'url'      => $woo_new_proudct_url,
			'done'     => $this->check_and_persist_if_task_is_completed(),
		];

		return $tasks;
	}

	/**
	 * Check if the task is completed.
	 *
	 * @internal
	 */
	public function check_and_persist_if_task_is_completed(): bool {
		$has_completed_task = get_option( self::CREATED_FIRST_PRODUCT_OPTION_KEY, 0 );

		if ( $has_completed_task ) {
			return true;
		}

		$has_any_draft_or_published_product = $this->has_any_draft_or_published_product();

		if ( $has_any_draft_or_published_product ) {
			sensei_log_event( 'home_task_complete', [ 'type' => self::CREATE_FIRST_PRODUCT_TASK_ID ] );
			update_option( self::CREATED_FIRST_PRODUCT_OPTION_KEY, true );
		}

		return $has_any_draft_or_published_product;
	}

	/**
	 * Check if there is any product created.
	 *
	 * @internal
	 */
	public function has_any_draft_or_published_product() {
		$products = wc_get_products(
			[
				'status' => [ 'draft', 'publish' ],
			]
		);

		return ! empty( $products );
	}

	/**
	 * Check if this task should be added.
	 *
	 * @return bool True if the task should be added, false otherwise.
	 */
	private function should_add_task() {
		// If WooCommerce is not installed or not activated, we do not need to add this task.
		if ( ! class_exists( 'WooCommerce' ) ) {
			return false;
		}

		// If the user does not intend to sell courses, we do not need to add this task.
		$features = Sensei()->setup_wizard->get_wizard_user_data( 'features' );
		return in_array( 'woocommerce', $features['selected'], true );
	}
}
