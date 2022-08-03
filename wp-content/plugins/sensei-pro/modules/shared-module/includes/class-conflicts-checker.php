<?php
/**
 * File containing the class \Sensei_Pro\Conflicts_Checker.
 *
 * @package sensei-pro
 * @since   1.2.0
 */

namespace Sensei_Pro;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( class_exists( '\Sensei_Pro\Conflicts_Checker' ) ) {
	return;
}

/**
 * Sensei Conflicts Checker
 *
 * Can be used by any plugin to mitigate conflict with any other plugin.
 *
 * @since 1.2.0
 */
class Conflicts_Checker {

	/**
	 * Prefix for transient that is used to determine if the target plugin
	 * is being activated or not.
	 *
	 * @var string
	 */
	const PLUGIN_ACTIVATION_TRANSIENT_PREFIX = 'plugin-activation-transient-';

	/**
	 * The conflict check settings.
	 *
	 * @var array
	 */
	private $settings = [];

	/**
	 * Class constructor.
	 *
	 * @param array $settings {
	 *   The conflict check settings.
	 *
	 *   @type string $plugin_slug The slug of a plugin that is performing the conflict check.
	 *   @type array  $conflicts {
	 *     The list of plugins that are conflicting and the messages that needs to be displayed
	 *     to the user for each conflict.
	 *
	 *     @type string $plugin_slug The slug of a plugin that is conflicting.
	 *     @type string $message     The message to be shown to the user that explains the conflict.
	 *   }
	 * }
	 */
	public function __construct( array $settings = [] ) {
		$this->settings = $settings;
	}

	/**
	 *  Given the plugin slug, retrieves the plugin file path relative to plugins directory.
	 *
	 * @param string $plugin_slug The plugin slug.
	 * @return string
	 */
	public static function get_plugin_filename( string $plugin_slug ): string {
		require_once ABSPATH . 'wp-admin/includes/plugin.php';
		$plugins = get_plugins();
		foreach ( $plugins as $plugin_file => $plugin_data ) {
			if ( "$plugin_slug.php" === basename( $plugin_file ) ) {
				return $plugin_file;
			}
		}
		return '';
	}

	/**
	 * Given the plugin slug, retrieves the absolute path of the plugin's main file.
	 *
	 * @param string $plugin_slug
	 */
	public static function get_plugin_file( string $plugin_slug ): string {
		$plugin_filename = self::get_plugin_filename( $plugin_slug );
		return $plugin_filename ? realpath( WP_PLUGIN_DIR . "/$plugin_filename" ) : '';
	}

	/**
	 * Given the plugin slug, tells if that plugin is active.
	 *
	 * @param string $plugin_slug The plugin slug.
	 * @return bool True if plugin is activated and false otherwise.
	 */
	public static function is_plugin_active( string $plugin_slug ): bool {
		$plugin_filename = self::get_plugin_filename( $plugin_slug );
		if ( ! $plugin_filename ) {
			return false;
		}
		return is_plugin_active( $plugin_filename );
	}

	/**
	 * Tells if the plugin is being activated.
	 */
	public function is_activating(): bool {
		$plugin_slug = $this->settings['plugin_slug'];
		if ( get_transient( self::PLUGIN_ACTIVATION_TRANSIENT_PREFIX . $plugin_slug ) ) {
			return true;
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( ! isset( $_GET['action'] ) || 'activate' !== $_GET['action'] ) {
			return false;
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( ! isset( $_GET['plugin'] ) || self::get_plugin_filename( $plugin_slug ) !== $_GET['plugin'] ) {
			return false;
		}

		set_transient( self::PLUGIN_ACTIVATION_TRANSIENT_PREFIX . $plugin_slug, true, 3 );

		return true;
	}

	/**
	 * Tells if the plugin has conflicts and deactivates itself if it has any.
	 */
	public function has_conflicts(): bool {
		// We check for plugin conflicts only during the current plugin activation.
		if ( ! $this->is_activating() ) {
			return false;
		}

		$has_conflicts = false;
		foreach ( $this->settings['conflicts'] as $conflict ) {
			if ( $this->conflicts_with( $conflict ) ) {
				$has_conflicts = true;
			}
		}

		return $has_conflicts;
	}

	/**
	 * Checks if the current plugin is conflicting with the given plugin.
	 * Attaches appropriate action hooks if there is a conflict.
	 *
	 * @param array $conflict {
	 *   Conflicting plugin check settings.
	 *
	 *   @type string $plugin_slug  The slug of a plugin that is conflicting.
	 *   @type string $deactivate   The plugin to deactivate. If empty then the current plugin is deactivated.
	 *   @type string $message      The message to display to the user.
	 *   @type string $message_type The type of the message to display. Values: "error", "notice", "updated notice". Default: "error".
	 * }
	 *
	 * @return bool
	 */
	public function conflicts_with( array $conflict ): bool {
		if ( self::is_plugin_active( $conflict['plugin_slug'] ) ) {
			$deactivate   = isset( $conflict['deactivate'] ) ? $conflict['deactivate'] : $this->settings['plugin_slug'];
			$message      = $conflict['message'];
			$message_type = isset( $conflict['message_type'] ) ? $conflict['message_type'] : 'error';

			add_action(
				'admin_notices',
				function () use ( $message, $message_type ) {
					self::add_conflict_notice( $message, $message_type );
				}
			);

			add_action(
				'admin_init',
				function () use ( $deactivate ) {
					self::deactivate( $deactivate );
				}
			);

			return true;
		}
		return false;
	}

	/**
	 * Deactivate self.
	 *
	 * @param string $plugin_slug The slug of a plugin to deactivate.
	 */
	public static function deactivate( $plugin_slug ) {
		$plugin_file = self::get_plugin_file( $plugin_slug );
		deactivate_plugins( $plugin_file );
	}

	/**
	 * Adds the given message as an error notice in WP Admin
	 *
	 * @param string $message The message to be displayed.
	 * @param string $message_type The message type class.
	 */
	public static function add_conflict_notice( string $message, string $message_type ) {
		$screen = get_current_screen();

		if ( ! current_user_can( 'activate_plugins' ) || 'plugins' !== $screen->id ) {
			return;
		}

		echo '<div class="' . wp_kses( $message_type, [] ) . '"><p>';
		echo wp_kses( $message, [ 'strong' => [] ] );
		echo '</p></div>';
	}
}
