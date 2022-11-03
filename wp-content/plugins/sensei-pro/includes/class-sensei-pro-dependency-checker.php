<?php
/**
 * File containing the class \Sensei_Pro_Dependency_Checker.
 *
 * @package sensei-pro
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Sensei Pro Dependencies Check
 *
 * @since 1.0.0
 */
class Sensei_Pro_Dependency_Checker {
	/**
	 * Minimum PHP version.
	 *
	 * @var string
	 */
	private $minimum_php_version;

	/**
	 * Soft minimum Sensei version (plugin still loads with warning).
	 *
	 * @var string
	 */
	private $soft_minimum_sensei_version;

	/**
	 * Hard minimum Sensei version (plugin does NOT load).
	 *
	 * @var string
	 */
	private $hard_minimum_sensei_version;

	/**
	 * Singleton instance.
	 *
	 * @var Sensei_Pro_Dependency_Checker
	 */
	private static $instance;

	/**
	 * Get singleton.
	 *
	 * @return Sensei_Pro_Dependency_Checker
	 */
	private static function get_instance() {

		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Sensei_Pro_Dependency_Checker constructor.
	 */
	private function __construct() {
		$plugin_data = \get_file_data(
			SENSEI_PRO_PLUGIN_FILE,
			[
				'minimum_sensei_version' => 'Sensei requires at least',
				'minimum_php_version'    => 'Requires PHP',
			]
		);

		$this->minimum_php_version         = $plugin_data['minimum_php_version'];
		$this->soft_minimum_sensei_version = $plugin_data['minimum_sensei_version'];
		$this->hard_minimum_sensei_version = defined( 'SENSEI_PRO_HARD_MIN_SENSEI_VERSION' ) ? SENSEI_PRO_HARD_MIN_SENSEI_VERSION : $plugin_data['minimum_sensei_version'];
	}


	/**
	 * Checks if system dependencies are met.
	 *
	 * @return bool
	 */
	public static function are_system_dependencies_met() {
		$are_met  = true;
		$instance = self::get_instance();

		if ( ! $instance->check_php() ) {
			add_action( 'admin_notices', [ __CLASS__, 'add_php_notice' ] );
			$are_met = false;
		}
		if ( ! $are_met ) {
			add_action( 'admin_init', [ __CLASS__, 'deactivate_self' ] );
		}

		return $are_met;
	}

	/**
	 * Checks if all plugin dependencies are met.
	 *
	 * @return bool
	 */
	public static function are_plugin_dependencies_met() {
		$instance = self::get_instance();

		if ( ! class_exists( 'Sensei_Main' ) ) {
			if ( is_admin() ) {
				add_action( 'admin_notices', [ $instance, 'add_sensei_missing_notice' ] );
			}

			return false;
		}

		$sensei_version = Sensei()->version;
		if ( version_compare( $instance->soft_minimum_sensei_version, $sensei_version, '>' ) ) {
			add_filter( 'sensei_admin_notices', [ $instance, 'add_sensei_version_notice' ] );

			if ( version_compare( $instance->hard_minimum_sensei_version, $sensei_version, '>' ) ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Checks for our PHP version requirement.
	 *
	 * @return bool
	 */
	private function check_php() {
		return version_compare( phpversion(), $this->minimum_php_version, '>=' );
	}

	/**
	 * Deactivate self.
	 *
	 * @access private
	 */
	public static function deactivate_self() {
		deactivate_plugins( SENSEI_PRO_PLUGIN_BASENAME );
	}

	/**
	 * Adds notice in WP Admin that minimum version of PHP is not met.
	 *
	 * @access private
	 */
	public static function add_php_notice() {
		$screen        = get_current_screen();
		$valid_screens = [ 'dashboard', 'plugins', 'plugins-network' ];

		if ( ! current_user_can( 'activate_plugins' ) || ! in_array( $screen->id, $valid_screens, true ) ) {
			return;
		}

		$instance = self::get_instance();

		// translators: %1$s is version of PHP that this plugin requires; %2$s is the version of PHP WordPress is running on.
		$message = sprintf( __( '<strong>Sensei Pro</strong> requires a minimum PHP version of %1$s, but you are running %2$s.', 'sensei-pro' ), $instance->minimum_php_version, phpversion() );
		echo '<div class="error"><p>';
		echo wp_kses( $message, [ 'strong' => [] ] );
		$php_update_url = 'https://wordpress.org/support/update-php/';
		if ( function_exists( 'wp_get_update_php_url' ) ) {
			$php_update_url = wp_get_update_php_url();
		}
		printf(
			'<p><a class="button button-primary" href="%1$s" target="_blank" rel="noopener noreferrer">%2$s <span class="screen-reader-text">%3$s</span><span aria-hidden="true" class="dashicons dashicons-external"></span></a></p>',
			esc_url( $php_update_url ),
			esc_html__( 'Learn more about updating PHP', 'sensei-pro' ),
			/* translators: accessibility text */
			esc_html__( '(opens in a new tab)', 'sensei-pro' )
		);
		echo '</p></div>';
	}

	/**
	 * Adds the notice in WP Admin that Sensei is required.
	 *
	 * @access private
	 */
	public function add_sensei_missing_notice() {
		$screen        = get_current_screen();
		$valid_screens = [ 'dashboard', 'plugins', 'plugins-network' ];

		if ( ! current_user_can( 'activate_plugins' ) || ! in_array( $screen->id, $valid_screens, true ) ) {
			return;
		}

		// translators: %1$s is the minimum version number of Sensei that is required.
		$message = sprintf( __( '<strong>Sensei Pro</strong> requires that the plugin <strong>Sensei LMS</strong> (minimum version: <strong>%1$s</strong>) is installed and activated.', 'sensei-pro' ), $this->soft_minimum_sensei_version );
		echo '<div class="error"><p>';
		echo wp_kses( $message, [ 'strong' => [] ] );
		echo '</p></div>';
	}

	/**
	 * Adds the notice in WP Admin that Sensei needs an update.
	 *
	 * @access private
	 *
	 * @param array $notices The filtered sensei notices.
	 */
	public function add_sensei_version_notice( $notices ) {
		$plugin_not_loaded = version_compare( $this->hard_minimum_sensei_version, Sensei()->version, '>' );

		// translators: %1$s is the minimum version number of Sensei that is required, %2$s is the detected version.
		$message = sprintf( __( '<strong>Sensei Pro</strong> requires that the plugin <strong>Sensei LMS, version %1$s</strong> is installed and activated. Version detected: <strong>%2$s</strong>.', 'sensei-pro' ), $this->soft_minimum_sensei_version, Sensei()->version );
		if ( $plugin_not_loaded ) {
			$message .= ' <strong>' . __( 'All features provided by Sensei Pro, including paid courses, will not work until Sensei LMS is updated.', 'sensei-pro' ) . '</strong>';
		}

		$notices[ 'sensei-pro-old-sensei-version-' . $this->soft_minimum_sensei_version ] = [
			'type'        => 'site-wide',
			'icon'        => 'sensei',
			'style'       => 'error',
			'heading'     => __( 'Sensei Pro', 'sensei-pro' ),
			'message'     => $message,
			'dismissible' => false,
			'conditions'  => [
				[
					'type'    => 'screens',
					'screens' => [ 'sensei*', 'plugins', 'plugins-network', 'dashboard' ],
				],
				[
					'type'         => 'user_cap',
					'capabilities' => [ 'activate_plugins' ],
				],
			],
		];

		return $notices;
	}
}
