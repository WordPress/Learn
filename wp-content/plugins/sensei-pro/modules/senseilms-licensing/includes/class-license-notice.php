<?php
/**
 * File containing the class SenseiLMS_Licensing\License_Notice.
 *
 * @package senseilms-licensing
 * @since   1.0.0
 */

namespace SenseiLMS_Licensing;

/**
 * Shows a notification in the admin area when license is not valid and we are in a page referencing Sensei.
 *
 * @since 1.0.0
 */
class License_Notice {

	/**
	 * The plugin slug.
	 *
	 * @var string
	 */
	private $plugin_slug;

	/**
	 * Private constructor.
	 *
	 * @param string $main_plugin_file The main plugin file.
	 */
	private function __construct( $main_plugin_file ) {
		$this->plugin_slug = basename( $main_plugin_file, '.php' );
	}

	/**
	 * Initialize the License_Notice class.
	 *
	 * @param string $main_plugin_file The main plugin file.
	 */
	public static function init( $main_plugin_file ) {
		$instance = new self( $main_plugin_file );

		if ( is_admin() ) {
			// Hook to `sensei_admin_notices` to show notices.
			add_filter( 'sensei_admin_notices', [ $instance, 'maybe_add_sensei_admin_notice_due_license_status' ] );
		}
	}

	/**
	 * Check license status and add the corresponding Sensei admin notice.
	 *
	 * @param array $notices The current Sensei notices that will be shown.
	 *
	 * @hooked sensei_admin_notices
	 *
	 * @return mixed
	 */
	public function maybe_add_sensei_admin_notice_due_license_status( $notices ) {
		if ( $this->is_sensei_extensions_page() ) {
			return $notices;
		}

		$status = License_Manager::get_license_status( $this->plugin_slug );
		if ( empty( $status['license_key'] ) ) {
			// License is not set yet.
			$notices['senseilms-no-license'] = [
				'type'    => 'user',
				'icon'    => 'sensei',
				'style'   => 'error',
				'heading' => __( 'Sensei Pro', 'sensei-pro' ),
				'message' => __( 'Finish setting up Sensei Pro to continue receiving new features and updates.', 'sensei-pro' ),
				'actions' => [
					[
						'label' => __( 'Finish Setup', 'sensei-pro' ),
						'url'   => admin_url( 'admin.php?page=sensei_pro_setup_wizard' ),
					],
				],
			];
		} else {
			if ( isset( $status['is_valid'] ) && ! $status['is_valid'] ) {
				// License is set but invalid.
				$notices['senseilms-invalid-license'] = [
					'type'    => 'user',
					'icon'    => 'sensei',
					'style'   => 'error',
					'heading' => __( 'Sensei Pro', 'sensei-pro' ),
					'message' => __( 'We noticed a problem with your Sensei Pro license, which could prevent future updates.', 'sensei-pro' ),
					'actions' => [
						[
							'label' => __( 'Check License', 'sensei-pro' ),
							'url'   => admin_url( 'admin.php?page=sensei_pro_setup_wizard' ),
						],
					],
				];
			}
		}

		return $notices;
	}

	/**
	 * Tells if the current page is the Sensei extensions page.
	 *
	 * @return bool
	 */
	public function is_sensei_extensions_page(): bool {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Arguments used for comparison.
		if ( isset( $_GET['page'] ) && 'sensei-extensions' === $_GET['page'] ) {
			return true;
		}
		return false;
	}
}
