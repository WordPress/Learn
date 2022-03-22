<?php
/**
 * File containing the class \Sensei_WC_Paid_Courses\Sensei_WC_Paid_Courses.
 *
 * @package sensei-wc-paid-courses
 * @since   1.0.0
 */

namespace Sensei_WC_Paid_Courses;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Sensei_Templates;
use Sensei_Pro\Background_Jobs\Scheduler;
use Sensei_Course_Theme_Option;

/**
 * Main Sensei WooCommerce Paid Courses class.
 *
 * @class Sensei_WC_Paid_Courses
 */
final class Sensei_WC_Paid_Courses {
	const MODULE_NAME                  = 'wc-paid-courses';
	const SCRIPT_ADMIN_COURSE_METADATA = 'sensei-wcpc-admin-course-metadata';
	const STYLE_LEARNER_MANAGEMENT     = 'sensei-wcpc-learner-management-styles';

	/**
	 * Instance of class.
	 *
	 * @var Sensei_WC_Paid_Courses
	 */
	private static $instance;

	/**
	 * Plugin directory.
	 *
	 * @var string
	 */
	private $wcpc_dir;

	/**
	 * Script and stylesheet loading.
	 *
	 * @var \Sensei_Assets
	 */
	public $assets;

	/**
	 * Initialize the singleton instance.
	 *
	 * @since 1.0.0
	 */
	private function __construct() {
		$this->wcpc_dir = dirname( __DIR__ );

		// TODO: We should not use this constant here, the plugin file should be injected instead.
		register_deactivation_hook( SENSEI_PRO_PLUGIN_FILE, [ $this, 'deactivation' ] );
	}

	/**
	 * Initializes the class and adds all filters and actions.
	 *
	 * @since 1.0.0
	 *
	 * @param bool $init_all Pass in `true` to load and initialize both frontend and admin functionality. `false` by default.
	 */
	public static function init( $init_all = false ) {
		$instance = self::instance();

		add_action( 'init', [ $instance, 'load_plugin_textdomain' ], 0 );

		$instance->assets = \Sensei_Pro\Modules\assets_loader( self::MODULE_NAME );

		$skip_plugin_deps_check = defined( 'SENSEI_WC_PAID_COURSES_SKIP_DEPS_CHECK' ) && SENSEI_WC_PAID_COURSES_SKIP_DEPS_CHECK;

		// Do not continue loading module unless WooCommerce is installed and activated.
		require_once $instance->wcpc_dir . '/includes/class-dependency-checker.php';
		if ( ! $skip_plugin_deps_check && ! \Sensei_WC_Paid_Courses\Dependency_Checker::woocommerce_dependency_is_met() ) {
			// Add prompt in the Course Editor.
			add_action( 'enqueue_block_editor_assets', [ $instance, 'enqueue_block_editor_woocommerce_prompt' ] );
			return;
		}

		$instance->include_dependencies( $init_all );

		Deprecated_Hooks::init();
		Courses::instance()->init();
		Settings::instance()->init();
		Widgets::instance()->init();
		Course_Enrolment_Providers::instance()->init();
		Blocks\Block_Purchase_Course::init();

		/**
		 * Hook in WooCommerce functionality.
		 */
		add_action( 'init', [ 'Sensei_WC', 'load_woocommerce_integration_hooks' ] );

		/**
		 * Hook in WooCommerce Memberships functionality.
		 */
		add_action( 'init', [ 'Sensei_WC_Memberships', 'load_wc_memberships_integration_hooks' ] );

		if ( Course_Enrolment_Providers::use_legacy_enrolment_method() ) {
			/**
			 * Hook in WooCommerce Subscriptions functionality.
			 */
			add_action( 'init', [ 'Sensei_WC_Subscriptions', 'load_wc_subscriptions_integration_hooks' ] );
		}

		if ( $init_all || ! is_admin() ) {
			add_action( 'init', [ $instance, 'frontend_init' ] );
		}

		if ( $init_all ) {
			add_action( 'init', [ $instance, 'admin_init' ] );
		} else {
			add_action( 'admin_init', [ $instance, 'admin_init' ] );
		}

		add_action( 'admin_enqueue_scripts', [ $instance, 'register_admin_scripts' ], 9 );
		add_action( 'enqueue_block_editor_assets', [ $instance, 'enqueue_block_editor_assets' ] );

		// Filter base fields for event logging.
		add_filter( 'sensei_event_logging_base_fields', [ $instance, 'filter_event_logging_base_fields' ] );

		// Set up REST API endpoints.
		add_action( 'rest_api_init', [ $instance, 'init_rest_api_endpoints' ], 1 );
	}

	/**
	 * Initializes the frontend functionality.
	 *
	 * @since 1.0.0
	 */
	public function frontend_init() {
		Frontend\Courses::instance()->init();
		Frontend\Lessons::instance()->init();
		Frontend\Shortcodes::instance()->init();
	}

	/**
	 * Initializes the admin functionality.
	 *
	 * @since 1.0.0
	 */
	public function admin_init() {
		Admin\Courses::instance()->init();
		Admin\Products::instance()->init();
	}

	/**
	 * Clean up on deactivation.
	 *
	 * @since 2.0.0
	 */
	public function deactivation() {
		if ( class_exists( 'ActionScheduler_Versions' ) ) {
			// Cancel all pending jobs.
			$sensei_wc_paid_courses_scheduler = Scheduler::instance();
			$sensei_wc_paid_courses_scheduler->cancel_all_jobs();
		}
	}

	/**
	 * Registers scripts used in admin.
	 */
	public function register_admin_scripts() {
		$this->assets->register( self::SCRIPT_ADMIN_COURSE_METADATA, 'js/admin-course-metadata.js', [ 'jquery' ], true );
		$this->assets->register( self::STYLE_LEARNER_MANAGEMENT, 'css/sensei-wcpc-admin-learner-management.css' );

		wp_add_inline_script(
			self::SCRIPT_ADMIN_COURSE_METADATA,
			sprintf(
				'window.sensei_admin_course_metadata = %s',
				wp_json_encode( [ 'product_options_placeholder' => __( 'Select a product', 'sensei-pro' ) ] )
			)
		);
	}

	/**
	 * Enqueue an asset for the block editor.
	 *
	 * @since 1.2.0
	 *
	 * @param string $script The built JS filename without the suffix.
	 */
	public function enqueue_block_editor_asset( $script ) {
		if ( ! $this->is_block_editor_supported() ) {
			return;
		}

		$handle = $this->block_editor_asset_handle( $script );
		$src    = "block-editor/$script.js";

		$this->assets->enqueue( $handle, $src );
	}

	/**
	 * Localize an asset for the block editor.
	 *
	 * @since 1.2.0
	 *
	 * @param string $script The built JS filename without the suffix.
	 * @param array  $data   The localization data.
	 */
	public function localize_block_editor_asset( $script, $data ) {
		if ( ! $this->is_block_editor_supported() ) {
			return;
		}

		$handle = $this->block_editor_asset_handle( $script );
		wp_localize_script( $handle, str_replace( '-', '_', $handle ), $data );
		wp_set_script_translations( $handle, 'sensei-pro' );
	}

	/**
	 * Enqueue WooCommerce prompt in the block editor
	 *
	 * @access private
	 * @since 1.1.0
	 */
	public function enqueue_block_editor_woocommerce_prompt() {
		$screen    = get_current_screen();
		$post_type = $screen->id;

		if ( 'course' === $post_type ) {
			$handle = $this->block_editor_asset_handle( 'woocommerce-prompt' );
			$src    = 'block-editor/woocommerce-prompt.js';

			$this->assets->enqueue( $handle, $src );
		}
	}

	/**
	 * Enqueues assets for the block editor for the Course and Lesson editors.
	 *
	 * @access private
	 * @since 1.1.0
	 */
	public function enqueue_block_editor_assets() {
		$screen    = get_current_screen();
		$post_type = $screen->id;

		if ( ! $this->is_block_editor_supported() ) {
			return;
		}

		if ( in_array( $post_type, [ 'lesson', 'course' ], true ) ) {
			$this->enqueue_block_editor_asset( $post_type );

			$handle      = $this->block_editor_asset_handle( $post_type );
			$inline_data = [
				'user_display_name'  => wp_get_current_user()->display_name,
				'assets_url'         => $this->assets->asset_url(),
				'wc_currency_symbol' => function_exists( 'get_woocommerce_currency_symbol' ) ? get_woocommerce_currency_symbol() : '',
			];
			wp_add_inline_script(
				$handle,
				'window.senseiWcPaidCoursesBlockEditorData = ' . wp_json_encode( $inline_data ),
				'before'
			);
		}
	}

	/**
	 * Determine whether block editor customizations are supported.
	 *
	 * @return bool
	 */
	public function is_block_editor_supported() {
		global $woocommerce;

		$wc_version     = $woocommerce->version;
		$min_wc_version = '3.6';

		return version_compare( $min_wc_version, $wc_version, '<=' );
	}

	/**
	 * Get the handle for the given script.
	 *
	 * @access private
	 * @since 1.2.0
	 *
	 * @param string $script The built JS filename without the suffix.
	 * @return string
	 */
	private function block_editor_asset_handle( $script ) {
		return "sensei-wc-paid-courses-block-editor-$script";
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

	/**
	 * Include required files.
	 *
	 * @param bool $load_all Load all dependencies for frontend and admin.
	 */
	private function include_dependencies( $load_all = false ) {
		include_once $this->wcpc_dir . '/includes/background-jobs/class-woocommerce-memberships-detect-cancelled-orders.php';

		include_once $this->wcpc_dir . '/includes/woocommerce-integrations/class-sensei-wc.php';
		include_once $this->wcpc_dir . '/includes/woocommerce-integrations/class-sensei-wc-memberships.php';
		include_once $this->wcpc_dir . '/includes/woocommerce-integrations/class-sensei-wc-subscriptions.php';
		include_once $this->wcpc_dir . '/includes/woocommerce-integrations/class-sensei-wc-utils.php';

		include_once $this->wcpc_dir . '/includes/class-deprecated-hooks.php';
		include_once $this->wcpc_dir . '/includes/class-courses.php';
		include_once $this->wcpc_dir . '/includes/class-settings.php';
		include_once $this->wcpc_dir . '/includes/class-widgets.php';
		include_once $this->wcpc_dir . '/includes/class-course-enrolment-providers.php';

		// Blocks.
		include_once $this->wcpc_dir . '/includes/blocks/class-block-purchase-course.php';

		// Background jobs.
		include_once $this->wcpc_dir . '/includes/background-jobs/class-membership-plan-calculation-job.php';

		// Load admin dependencies.
		if ( $load_all || is_admin() ) {
			include_once $this->wcpc_dir . '/includes/admin/class-courses.php';
			include_once $this->wcpc_dir . '/includes/admin/class-products.php';
			include_once $this->wcpc_dir . '/includes/admin/class-woocommerce-memberships-cancelled-orders-notice.php';
		}

		// Load frontend dependencies.
		if ( $load_all || ! is_admin() ) {
			include_once $this->wcpc_dir . '/includes/frontend/class-courses.php';
			include_once $this->wcpc_dir . '/includes/frontend/class-quizzes.php';
			include_once $this->wcpc_dir . '/includes/frontend/class-lessons.php';
			include_once $this->wcpc_dir . '/includes/frontend/class-shortcodes.php';
		}
	}

	/**
	 * Loads textdomain for plugin.
	 */
	public function load_plugin_textdomain() {
		$domain = 'sensei-wc-paid-courses';
		$locale = is_admin() && function_exists( 'get_user_locale' ) ? get_user_locale() : get_locale();
		// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- Using commonly used core hook to fetch locales.
		$locale = apply_filters( 'plugin_locale', $locale, $domain );

		unload_textdomain( $domain );
		load_textdomain( $domain, WP_LANG_DIR . '/' . $domain . '/' . $domain . '-' . $locale . '.mo' );
		load_plugin_textdomain( $domain, false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
	}

	/**
	 * Filter the base fields for Sensei event logging.
	 *
	 * @since 1.0.1
	 * @access private
	 *
	 * @param array $base_fields The previous fields.
	 *
	 * @return array
	 */
	public function filter_event_logging_base_fields( $base_fields ) {
		$base_fields['paid'] = 1;
		return $base_fields;
	}

	/**
	 * Initialize REST API endpoints.
	 */
	public function init_rest_api_endpoints() {
		include_once $this->wcpc_dir . '/includes/rest-api/controllers/class-course-products.php';
	}

	/**
	 * Get a template file.
	 *
	 * @since 1.1.0
	 *
	 * @param string $template_name Template name.
	 * @param array  $args          Optional. Arguments to pass to template. Default empty array.
	 */
	public static function get_template( $template_name, $args = [] ) {
		Sensei_Templates::get_template(
			$template_name,
			$args,
			'sensei-wc-paid-courses/',
			untrailingslashit( dirname( dirname( __FILE__ ) ) ) . '/templates/'
		);
	}

	/**
	 * Get a template part.
	 *
	 * The load order is:
	 *
	 * yourtheme/{$slug}-{$name}.php
	 * yourtheme/sensei-wc-paid-courses/{$slug}-{$name}.php
	 * plugins/sensei-wc-paid-courses/templates/{$slug}-{$name}.php
	 * yourtheme/{$slug}.php
	 * yourtheme/sensei-wc-paid-courses/{$slug}.php
	 *
	 * @since 1.1.0
	 *
	 * @param string $slug Template slug.
	 * @param string $name Optional. Template name. Default null.
	 * @param array  $args  Optional. Arguments to pass to template part. Default empty array.
	 */
	public static function get_template_part( $slug, $name = null, $args = [] ) {
		if ( $args && is_array( $args ) ) {
			extract( $args ); // @codingStandardsIgnoreLine
		}

		if ( $name ) {
			// First, look in yourtheme/{$slug}-{$name}.php and yourtheme/sensei-wc-paid-course/{$slug}-{$name}.php.
			$template = locate_template(
				[
					"{$slug}-{$name}.php",
					'sensei-wc-paid-courses/' . "{$slug}-{$name}.php",
				]
			);

			// If the template file was not found, look in plugins/sensei-wc-paid-courses/templates/{$slug}-{$name}.php.
			if ( ! $template ) {
				$fallback = dirname( dirname( __FILE__ ) ) . "/templates/{$slug}-{$name}.php";
				$template = file_exists( $fallback ) ? $fallback : '';
			}
		}

		// If the template file was still not found, look in yourtheme/{$slug}.php and yourtheme/sensei-wc-paid-course/{$slug}.php.
		if ( ! $template ) {
			$template = locate_template(
				[
					"{$slug}.php",
					'sensei-wc-paid-courses/' . "{$slug}.php",
				]
			);
		}

		/**
		 * Filter the template filename.
		 *
		 * @since 1.1.0
		 *
		 * @param string $template Template filename.
		 * @param string $slug     Template slug.
		 * @param string $name     Optional. Template name. Default null.
		 */
		$template = apply_filters( 'sensei_wc_paid_courses_get_template_part', $template, $slug, $name );

		if ( $template ) {
			include $template;
		}
	}

	/**
	 * Check if it should use Learning Mode template.
	 *
	 * @since 2.6.4
	 *
	 * @return boolean
	 */
	public static function should_use_learning_mode() {
		// Method introduced in Sensei 4.0.1.
		if ( method_exists( 'Sensei_Course_Theme_Option', 'should_use_learning_mode' ) ) {
			return Sensei_Course_Theme_Option::should_use_learning_mode();
		}

		if ( method_exists( Sensei_Course_Theme_Option::instance(), 'should_use_sensei_theme' ) ) {
			return Sensei_Course_Theme_Option::instance()->should_use_sensei_theme();
		}

		return false;
	}
}
