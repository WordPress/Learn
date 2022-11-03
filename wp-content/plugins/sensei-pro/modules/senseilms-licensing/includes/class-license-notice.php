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
	 * The plugin file.
	 *
	 * @var string
	 */
	private $plugin_file;

	/**
	 * Private constructor.
	 *
	 * @param string $main_plugin_file The main plugin file.
	 */
	private function __construct( $main_plugin_file ) {
		$this->plugin_file = $main_plugin_file;
		$this->plugin_slug = basename( $main_plugin_file, '.php' );

		add_filter( 'sensei_home_is_plugin_licensed_' . $this->plugin_slug, [ $this, 'is_plugin_licensed' ] );
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
	 * Check if the plugin is licensed.
	 *
	 * @internal
	 *
	 * @return bool
	 */
	public function is_plugin_licensed() {
		$status = License_Manager::get_license_status( $this->plugin_slug );

		if ( empty( $status['license_key'] ) || ! $status['is_valid'] ) {
			return false;
		}

		return true;
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
		if ( $this->is_sensei_extensions_page() || $this->is_sensei_home_page() ) {
			return $notices;
		}

		require_once ABSPATH . 'wp-admin/includes/plugin.php';
		$plugin_data = get_plugin_data( $this->plugin_file );
		$plugin_name = $plugin_data['Name'];

		$status = License_Manager::get_license_status( $this->plugin_slug );
		if ( empty( $status['license_key'] ) ) {
			// License is not set yet.
			$notices[ "senseilms-no-license-{$this->plugin_slug}" ] = [
				'type'    => 'user',
				'icon'    => 'sensei',
				'style'   => 'error',
				'heading' => $plugin_name,
				// translators: Placeholder is the plugin name.
				'message' => sprintf( __( 'Finish setting up %s to continue receiving new features and updates.', 'sensei-pro' ), $plugin_name ),
				'actions' => [
					[
						'label' => __( 'Finish Setup', 'sensei-pro' ),
						'url'   => \Sensei_Pro_Setup\Wizard::get_setup_url( $this->plugin_slug ),
					],
				],
			];
		} else {
			if ( isset( $status['is_valid'] ) && ! $status['is_valid'] ) {
				// License is set but invalid.
				$notices[ "senseilms-invalid-license-{$this->plugin_slug}" ] = [
					'type'    => 'user',
					'icon'    => 'sensei',
					'style'   => 'error',
					'heading' => $plugin_name,
					// translators: Placeholder is the plugin name.
					'message' => sprintf( __( 'We noticed a problem with your %s license, which could prevent future updates.', 'sensei-pro' ), $plugin_name ),
					'actions' => [
						[
							'label' => __( 'Check License', 'sensei-pro' ),
							'url'   => \Sensei_Pro_Setup\Wizard::get_setup_url( $this->plugin_slug ),
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

	/**
	 * Tells if the current page is the Sensei Home.
	 *
	 * @return bool
	 */
	public function is_sensei_home_page(): bool {
		// Require `Screen_ID_Helper` manually because `senseilms-licensing` module is loaded in a special way.
		include_once __DIR__ . '/../../shared-module/includes/class-screen-id-helper.php';
		$home_screen_id = \Sensei_Pro\Screen_ID_Helper::get_sensei_home_screen_id();
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Arguments used for comparison.
		$screen = get_current_screen();
		return ! is_null( $screen ) && ! is_null( $home_screen_id ) && $home_screen_id === $screen->id;
	}
}
