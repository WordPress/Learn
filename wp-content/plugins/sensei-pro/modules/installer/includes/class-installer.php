<?php
/**
 * File containing the class \Sensei_Pro_Installer\Installer.
 *
 * @package sensei-pro
 * @since   1.4.0
 */

namespace Sensei_Pro_Installer;

/**
 * Installer class.
 *
 * Responsible for running DB updates that need to be run per version.
 *
 * @since 1.4.0
 */
class Installer {
	/**
	 * Instance of the class.
	 *
	 * @since 1.4.0
	 * @var self
	 */
	private static $instance;

	/**
	 * The database schema class.
	 *
	 * @since 1.4.0
	 * @var Schema
	 */
	private $schema;

	/**
	 * The data migrator class.
	 *
	 * @since 1.4.0
	 * @var Data_Migrator
	 */
	private $data_migrator;

	/**
	 * Constructor.
	 *
	 * @since 1.4.0
	 *
	 * @param Schema        $schema
	 * @param Data_Migrator $data_migrator
	 */
	public function __construct( Schema $schema, Data_Migrator $data_migrator ) {
		$this->schema        = $schema;
		$this->data_migrator = $data_migrator;
	}

	/**
	 * Fetches an instance of the class.
	 *
	 * @since 1.4.0
	 *
	 * @return self
	 */
	public static function instance(): self {
		if ( ! self::$instance ) {
			self::$instance = new self( new Schema(), Data_Migrator::instance() );
		}

		return self::$instance;
	}

	/**
	 * Initialize necessary hooks.
	 *
	 * @since 1.4.0
	 */
	public function init() {
		register_activation_hook( SENSEI_PRO_PLUGIN_FILE, [ $this, 'install' ] );
		add_action( 'plugins_loaded', [ $this, 'install' ] );
		add_action( 'init', [ $this, 'migrate' ], 5 );

		$this->data_migrator->init();
	}

	/**
	 * Run the installer.
	 *
	 * This method is executed when the plugin is installed or updated.
	 *
	 * @since 1.4.0
	 */
	public function install() {
		if (
			! is_blog_installed()
			|| $this->is_installing()
			|| ! $this->requires_install()
		) {
			return;
		}

		set_transient( 'sensei_pro_installing', 'yes', MINUTE_IN_SECONDS * 10 );

		$this->schema->create_tables();
		$this->update_version();

		delete_transient( 'sensei_pro_installing' );

		/**
		 * Fires after the installation completes.
		 *
		 * @since 1.4.0
		 */
		do_action( 'sensei_pro_installed' );
	}

	/**
	 * Run the migrations.
	 *
	 * This method should be used after the Action Scheduler has initialized which is on `init` with priority 1.
	 *
	 * @since 1.4.0
	 */
	public function migrate() {
		$this->data_migrator->run();
	}

	/**
	 * Get the Schema instance.
	 *
	 * @since 1.4.0
	 *
	 * @return Schema
	 */
	public function get_schema(): Schema {
		return $this->schema;
	}

	/**
	 * Check if the installer is running.
	 *
	 * @since 1.4.0
	 *
	 * @return bool
	 */
	private function is_installing(): bool {
		return 'yes' === get_transient( 'sensei_pro_installing' );
	}

	/**
	 * Determine if the installer needs to be run by checking the plugin's version.
	 *
	 * @since 1.4.0
	 *
	 * @return bool
	 */
	private function requires_install(): bool {
		$version = get_option( 'sensei_pro_version' );

		return version_compare( $version, SENSEI_PRO_VERSION, '<' );
	}

	/**
	 * Update the plugin's version to the current one.
	 *
	 * @since 1.4.0
	 */
	private function update_version() {
		update_option( 'sensei_pro_version', SENSEI_PRO_VERSION );
	}
}
