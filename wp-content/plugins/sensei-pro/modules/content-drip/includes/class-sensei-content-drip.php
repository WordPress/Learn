<?php
/**
 * File containing the class Sensei_Content_Drip.
 *
 * @package sensei-pro
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Sensei_Content_Drip
 *
 * The main class for the content drip plugin. This class hooks the plugin into the required WordPress actions.
 *
 * @package WordPress
 * @subpackage Sensei Content Drip
 * @category Utilities
 * @author WooThemes
 * @since 1.0.0
 *
 * @property Scd_Ext_Settings $settings
 * @property Scd_Ext_Utils $utils
 * @property Scd_Ext_Access_Control $access_control
 * @property Scd_Ext_Lesson_Frontend $lesson_frontend
 * @property Scd_Ext_Lesson_Admin $lesson_admin
 * @property Scd_Ext_Quiz_Frontend $quiz_frontend
 * @property Scd_Ext_Drip_Email $drip_email
 * @property Scd_Ext_Manual_Drip $manual_drip
 *
 * Table Of Contents:
 * - __construct
 * - enqueue_styles
 * - enqueue_scripts
 * - admin_enqueue_styles
 * - admin_enqueue_scripts
 * - instance
 * - __clone
 * - __wakeup
 * - install
 * - _log_version_number
 * - initialize_classes
 * - get_date_format_string
 */
class Sensei_Content_Drip {
	const MODULE_NAME = 'content-drip';

	/**
	 * The single instance of Sensei_Content_Drip.
	 *
	 * @var    object
	 * @access private
	 * @static
	 * @since  1.0.0
	 */
	private static $instance = null;

	/**
	 * The version number.
	 *
	 * @var    string
	 * @access private
	 * @since  1.0.0
	 */
	private $version;

	/**
	 * The token.
	 *
	 * @var    string
	 * @access private
	 * @since  1.0.0
	 */
	private $token;

	/**
	 * The main plugin directory.
	 *
	 * @var    string
	 * @access private
	 * @since  1.0.0
	 */
	private $dir;

	/**
	 * The plugin assets directory.
	 *
	 * @var    string
	 * @access private
	 * @since  1.0.0
	 */
	private $assets_dir;

	/**
	 * The plugin assets URL.
	 *
	 * @var    string
	 * @access private
	 * @since  1.0.0
	 */
	private $assets_url;

	/**
	 * The cron hook.
	 *
	 * @var    string
	 * @access private
	 * @since  1.0.0
	 */
	private $cron_hook;

	/**
	 * Constructor function.
	 *
	 * @access public
	 * @since  1.0.0
	 */
	public function __construct() {
		// TODO: remove the need for this.
		$this->version   = SENSEI_PRO_VERSION;
		$this->token     = 'sensei_content_drip';
		$this->dir       = dirname( SENSEI_CONTENT_DRIP_PLUGIN_FILE );
		$this->cron_hook = 'woo_scd_daily_cron_hook';

		// TODO: Switch to module helper for these hooks.
		register_activation_hook( SENSEI_PRO_PLUGIN_FILE, [ $this, 'activate' ] );
		register_deactivation_hook( SENSEI_PRO_PLUGIN_FILE, [ $this, 'deactivate' ] );
	}

	/**
	 * Set up all hooks and filters.
	 */
	public static function init() {
		$instance = self::instance();

		// Ensure cron job is set up.
		$instance->maybe_setup_cron();

		$instance->assets     = \Sensei_Pro\Modules\assets_loader( self::MODULE_NAME );
		$instance->assets_dir = $instance->assets->src_path();
		$instance->assets_url = $instance->assets->asset_url();

		/**
		 * Returns the main instance of Sensei_Content_Drip to prevent the need to use globals.
		 *
		 * @since  1.0.0
		 * @return object Sensei_Content_Drip
		 */
		function Sensei_Content_Drip() {
			// phpcs:ignore Squiz.Classes.SelfMemberReference.NotUsed -- Used in global function
			return Sensei_Content_Drip::instance();
		}

		// Load admin JS & CSS.
		add_action( 'admin_enqueue_scripts', [ $instance, 'admin_enqueue_scripts' ], 10, 1 );
		add_action( 'admin_enqueue_scripts', [ $instance, 'admin_enqueue_styles' ], 10, 1 );

		// Load and initialize classes.
		add_action( 'init', [ $instance, 'initialize_classes' ], 0 );
	}

	/**
	 * Load admin CSS.
	 *
	 * @since  1.0.0
	 * @param string $hook
	 * @return void
	 */
	public function admin_enqueue_styles( $hook = '' ) {
		global $post;

		// Load the lesson idit/new screen css.
		if ( ( 'post.php' === $hook || 'post-new.php' === $hook ) && ( ! empty( $post ) && 'lesson' === $post->post_type ) ) {
			wp_register_style( $this->token . '-admin-lesson', esc_url( $this->assets_url ) . 'css/admin-lesson.css', [], $this->version );
			wp_enqueue_style( $this->token . '-admin-lesson' );

			// jQuey UI.
			wp_register_style( 'scd-jquery-ui', esc_url( $this->assets_url ) . 'css/jquery-ui.css', [], $this->version );
			wp_enqueue_style( 'scd-jquery-ui' );
		}
	}

	/**
	 * Load admin Javascript.
	 *
	 * @since  1.0.0
	 * @param string $hook
	 * @return void
	 */
	public function admin_enqueue_scripts( $hook = '' ) {
		global $post;

		// Load the lesson idit/new screen script.
		if ( ( 'post.php' === $hook || 'post-new.php' === $hook ) && ( ! empty( $post ) && 'lesson' === $post->post_type ) ) {
			wp_register_script(
				$this->token . '-lesson-admin-script',
				esc_url( $this->assets_url ) . 'js/admin-lesson.js',
				[
					'underscore',
					'jquery',
					'jquery-ui-datepicker',
					'backbone',
				],
				$this->version,
				true
			);
			wp_enqueue_script( $this->token . '-lesson-admin-script' );
		}

		// Load the learner management functionality script.
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Used for comparison
		if ( 'sensei-lms_page_sensei_learners' === $hook && isset( $_GET['course_id'] ) && isset( $_GET['view'] ) && 'learners' === $_GET['view'] ) {
			wp_register_script(
				$this->token . '-admin-manual-drip-script',
				esc_url( $this->assets_url ) . 'js/admin-manual-drip.js',
				[
					'underscore',
					'jquery',
					'backbone',
				],
				$this->version,
				true
			);
			wp_enqueue_script( $this->token . '-admin-manual-drip-script' );
		}
	}

	/**
	 * Main Sensei_Content_Drip Instance
	 *
	 * Ensures only one instance of Sensei_Content_Drip is loaded or can be loaded.
	 *
	 * @since  1.0.0
	 * @static
	 * @see    Sensei_Content_Drip()
	 * @return Sensei_Content_Drip
	 */
	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Cloning is forbidden.
	 *
	 * @since 1.0.0
	 */
	public function __clone() {
		_doing_it_wrong( esc_html( __FUNCTION__ ), esc_html__( 'Cheatin&#8217; huh?', 'sensei-pro' ), esc_html( $this->version ) );
	}

	/**
	 * Unserializing instances of this class is forbidden.
	 *
	 * @since 1.0.0
	 */
	public function __wakeup() {
		_doing_it_wrong( esc_html( __FUNCTION__ ), esc_html__( 'Cheatin&#8217; huh?', 'sensei-pro' ), esc_html( $this->version ) );
	}

	/**
	 * Ensure the cron job is setup.
	 */
	private function maybe_setup_cron() {
		$today_start         = strtotime( date_i18n( 'Y-m-d' ) );
		$tomorrow_start      = $today_start + 24 * HOUR_IN_SECONDS;
		$scheduled_time      = $tomorrow_start + 30 * MINUTE_IN_SECONDS;
		$scheduled_time_unix = $scheduled_time - get_option( 'gmt_offset' ) * HOUR_IN_SECONDS;
		wp_schedule_event( $scheduled_time_unix, 'daily', $this->cron_hook );
	}

	/**
	 * Installation. Runs on activation.
	 *
	 * @since  1.0.0
	 * @access private
	 * @return void
	 */
	public function activate() {
		$this->log_version_number();

		if ( false !== wp_next_scheduled( $this->cron_hook ) ) {
			wp_clear_scheduled_hook( $this->cron_hook );
		}

		$this->maybe_setup_cron();
	}

	/**
	 * Runs on deactivation.
	 *
	 * @since 2.0.0
	 * @access private
	 */
	public function deactivate() {
		wp_clear_scheduled_hook( $this->cron_hook );
	}

	/**
	 * Log the plugin version number.
	 *
	 * @access private
	 * @since  1.0.0
	 * @return void
	 */
	private function log_version_number() {
		update_option( $this->token . '_version', $this->version );
	}

	/**
	 * Initialize classed need needed within this pluin
	 *
	 * @since  1.0.0
	 * @return void
	 */
	public function initialize_classes() {
		$classes = [
			'settings',
			'utils',
			'access-control',
			'lesson-frontend',
			'lesson-admin',
			'quiz-frontend',
			'drip-email',
			'manual-drip',
		];

		foreach ( $classes as $class_id ) {
			// Build the full class file name and path.
			$full_class_file_name = 'class-scd-ext-' . trim( $class_id ) . '.php';
			$file_path            = $this->dir . '/includes/' . $full_class_file_name;

			// Check if the file exists.
			if ( empty( $full_class_file_name ) || ! file_exists( $file_path ) ) {
				continue;
			}

			// Include the class file.
			require_once realpath( $file_path );
		}

		// Instantiate the classes.
		$this->settings        = new Scd_Ext_Settings();
		$this->utils           = new Scd_Ext_Utils();
		$this->access_control  = new Scd_Ext_Access_Control();
		$this->lesson_frontend = new Scd_Ext_Lesson_Frontend();
		$this->lesson_admin    = new Scd_Ext_Lesson_Admin();
		$this->quiz_frontend   = new Scd_Ext_Quiz_Frontend();
		$this->drip_email      = new Scd_Ext_Drip_Email();
		$this->manual_drip     = new Scd_Ext_Manual_Drip();
	}

	/**
	 * Get the date format and allow the user to filter it. This format applies for the whole
	 * content drip extension
	 *
	 * @since  1.0.0
	 * @return string $date_format
	 */
	public function get_date_format_string() {
		$date_format = 'l jS F Y';

		/**
		 * Ffilter scd_drip_message_date_format
		 *
		 * @param  string
		 * @deprecated since 1.0.2
		 */
		// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- Backwards compatibility
		return esc_html( apply_filters( 'scd_drip_message_date_format', $date_format ) );
	}

	/**
	 * Load a template from the `templates` directory. This function outputs the
	 * rendered HTML from the template.
	 *
	 * The template may be overridden by the theme by putting a file with the
	 * same name in the `sensei-content-drip` directory of the theme root.
	 *
	 * @since 2.0.0
	 *
	 * @param string $name The template file name.
	 * @param array  $args The arguments to pass to the template file.
	 */
	public function load_template( $name, $args = [] ) {
		$default_path = realpath( $this->dir . '/templates' ) . '/';
		$theme_path   = 'sensei-content-drip/';
		Sensei_Templates::get_template( $name, $args, $theme_path, $default_path );
	}

	/**
	 * Check if course enrolment is handled by the legacy method.
	 *
	 * @since 2.0.2
	 */
	public function is_legacy_enrolment() {
		if ( ! interface_exists( '\Sensei_Course_Enrolment_Provider_Interface' ) ) {
			return true;
		}

		return false;
	}
}
