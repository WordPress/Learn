<?php
/**
 * File containing the class \Sensei_Pro_Installer\Data_Migrator.
 *
 * @package sensei-pro
 * @since   1.4.0
 */

namespace Sensei_Pro_Installer;

use Exception;
use function Sensei_Pro\Modules\assets_loader;

/**
 * Data migrator class.
 *
 * This class is responsible for running data migrations
 * and not for changing the database schema.
 *
 * Database schema changes must be incorporated to the SQL returned by `Schema::get_query()`, which is applied
 * via dbDelta at both install and update time. If any other kind of database change is required
 * at install time (e.g. populating tables), use the 'sensei_pro_installed' hook.
 *
 * @since 1.4.0
 */
class Data_Migrator {
	/**
	 * The Action Scheduler group name.
	 *
	 * @since 1.4.0
	 */
	const SCHEDULER_GROUP = 'sensei-pro-data-migrator';

	/**
	 * The migrations' directory path.
	 *
	 * @since 1.4.0
	 *
	 * @var string
	 */
	private $migrations_path = __DIR__ . '/migrations';

	/**
	 * Instance of the class.
	 *
	 * @since 1.4.0
	 *
	 * @var self
	 */
	private static $instance;

	/**
	 * Fetches an instance of the class.
	 *
	 * @since 1.4.0
	 *
	 * @return self
	 */
	public static function instance(): self {
		if ( ! self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Constructor.
	 *
	 * @since 1.4.0
	 */
	private function __construct() {}

	/**
	 * Initialize necessary hooks.
	 *
	 * @since 1.4.0
	 */
	public function init() {
		add_action( 'sensei_pro_run_migration', [ $this, 'run_migration' ] );
		add_action( 'sensei_pro_update_db_version', [ $this, 'update_db_version' ] );
		add_action( 'admin_notices', [ $this, 'maybe_show_failed_notice' ] );
		add_action( 'wp_ajax_sensei_pro_data_migrator_dismiss_failed_notice', [ $this, 'handle_dismiss_failed_notice' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_admin_scripts' ] );
	}

	/**
	 * Run the migrations in the background.
	 *
	 * This method should be used after the Action Scheduler has initialized which is on `init` with priority 1.
	 *
	 * IMPORTANT: The run order is determined by the prefix number of the migration file.
	 * Keep that in mind when creating migrations.
	 *
	 * @since 1.4.0
	 * @throws Exception When the action scheduler library is missing.
	 */
	public function run() {
		if ( ! did_action( 'init' ) ) {
			_doing_it_wrong( __METHOD__, esc_html__( 'Running data migrations should not be done before the `init` action.', 'sensei-pro' ), '1.4.0' );
		}

		if ( ! $this->should_run() ) {
			return;
		}

		$scheduled_migrations = [];
		$db_version           = $this->get_db_version();

		foreach ( $this->get_migration_files() as $file_path ) {
			$migration = require $file_path;

			if ( version_compare( $db_version, $migration->target_version(), '>=' ) ) {
				continue;
			}

			$scheduled_migrations[] = as_schedule_single_action(
				time(),
				'sensei_pro_run_migration',
				[ 'file_path' => $file_path ],
				self::SCHEDULER_GROUP
			);
		}

		// After the migrations finish, update the db version to the current plugin version.
		as_schedule_single_action(
			time(),
			'sensei_pro_update_db_version',
			[ 'version' => SENSEI_PRO_VERSION ],
			self::SCHEDULER_GROUP
		);

		/**
		 * Fires after the data migrations are queued.
		 *
		 * @since 1.4.0
		 *
		 * @param array $scheduled_migrations The action IDs of the scheduled migrations.
		 */
		do_action( 'sensei_pro_data_migrated', $scheduled_migrations );
	}

	/**
	 * Run the migration.
	 *
	 * @since 1.4.0
	 * @throws Exception When the migration file is not safe.
	 *
	 * @param string $file_path
	 */
	public function run_migration( string $file_path ) {
		if ( ! $this->verify_migration_file( $file_path ) ) {
			throw new Exception( esc_html__( 'Unknown migration file.', 'sensei-pro' ) );
		}

		$migration = require $file_path;

		try {
			$migration->run();
		} catch ( Exception $e ) {
			update_option( 'sensei_pro_migration_failed', true );
			delete_option( 'sensei_pro_migration_failed_notice_dismissed' );

			throw $e;
		}
	}

	/**
	 * Enqueue the admin assets.
	 *
	 * @since 1.4.0
	 */
	public function enqueue_admin_scripts() {
		// If the Sensei LMS plugin is not activated, the `assets_loader` function won't be available.
		if ( ! function_exists( 'Sensei_Pro\Modules\assets_loader' ) ) {
			return;
		}

		$assets = assets_loader( 'installer' );

		if ( $this->should_show_failed_notice() ) {
			$assets->enqueue( 'sensei-pro-data-migrator', 'admin/data-migrator.js' );

			wp_localize_script(
				'sensei-pro-data-migrator',
				'sensei_pro_data_migrator',
				[ 'dismiss_failed_notice_nonce' => wp_create_nonce( 'sensei_pro_data_migrator_dismiss_failed_notice' ) ]
			);
		}
	}

	/**
	 * Show a notice if the migration has failed.
	 *
	 * @since 1.4.0
	 */
	public function maybe_show_failed_notice() {
		if ( ! $this->should_show_failed_notice() ) {
			return;
		}

		$plugin_data = get_plugin_data( SENSEI_PRO_PLUGIN_FILE );

		?>
		<div id="sensei-pro-data-migrator-failed-notice" class="notice notice-error is-dismissible">
			<p>
				<strong><?php echo esc_html( $plugin_data['Name'] ); ?>:</strong>
				<?php esc_html_e( 'Could not complete the data migration process. Please, contact support.', 'sensei-pro' ); ?>
			</p>
		</div>
		<?php
	}

	/**
	 * Handle the failed migration notice dismissal.
	 *
	 * @since 1.4.0
	 */
	public function handle_dismiss_failed_notice() {
		check_ajax_referer( 'sensei_pro_data_migrator_dismiss_failed_notice' );

		update_option( 'sensei_pro_migration_failed_notice_dismissed', true );
	}

	/**
	 * Check if the migration failed notice should be displayed.
	 *
	 * @since 1.4.0
	 *
	 * @return bool
	 */
	private function should_show_failed_notice(): bool {
		return current_user_can( 'activate_plugins' )
			&& $this->has_migration_failed()
			&& ! $this->is_failed_notice_dismissed();
	}

	/**
	 * Check if the failed notice has been dismissed.
	 *
	 * @since 1.4.0
	 *
	 * @return bool
	 */
	private function is_failed_notice_dismissed(): bool {
		return (bool) get_option( 'sensei_pro_migration_failed_notice_dismissed' );
	}

	/**
	 * Check if the migration has failed.
	 *
	 * @since 1.4.0
	 *
	 * @return bool
	 */
	private function has_migration_failed(): bool {
		return (bool) get_option( 'sensei_pro_migration_failed' );
	}

	/**
	 * Check if this is a safe migration file.
	 *
	 * For now only allow files from the migrations' folder.
	 *
	 * @since 1.4.0
	 *
	 * @param string $file_path
	 *
	 * @return bool
	 */
	private function verify_migration_file( string $file_path ): bool {
		return strpos( $file_path, $this->migrations_path ) === 0;
	}

	/**
	 * Get the migration file paths sorted in a natural order.
	 *
	 * @since 1.4.0
	 *
	 * @return array
	 */
	private function get_migration_files(): array {
		$files = glob( $this->migrations_path . '/*-*.php' );

		natsort( $files );

		return $files;
	}

	/**
	 * Check if the Action Scheduler library is loaded.
	 *
	 * If the Sensei LMS plugin is not activated, the scheduler won't load.
	 *
	 * @since 1.4.0
	 *
	 * @return bool
	 */
	private function is_action_scheduler_loaded(): bool {
		return function_exists( 'as_schedule_single_action' )
			&& function_exists( 'as_has_scheduled_action' );
	}

	/**
	 * Update DB version to current.
	 *
	 * @since 1.4.0
	 *
	 * @param string $version
	 */
	public function update_db_version( string $version ) {
		update_option(
			'sensei_pro_db_version',
			$version
		);
	}

	/**
	 * Get the current database version.
	 *
	 * @since 1.4.0
	 *
	 * @return string|false
	 */
	public function get_db_version() {
		return get_option( 'sensei_pro_db_version' );
	}

	/**
	 * Check if the migrations should run.
	 *
	 * @since 1.4.0
	 *
	 * @return bool
	 */
	private function should_run(): bool {
		return version_compare( $this->get_db_version(), SENSEI_PRO_VERSION, '<' )
			&& $this->is_action_scheduler_loaded()
			&& ! $this->is_running();
	}

	/**
	 * Check if the migration is running.
	 *
	 * @since 1.4.0
	 *
	 * @return bool
	 */
	private function is_running(): bool {
		return as_has_scheduled_action( 'sensei_pro_run_migration' )
			|| as_has_scheduled_action( 'sensei_pro_update_db_version' );
	}

	/**
	 * Set the migrations' directory path.
	 *
	 * @since 1.4.0
	 *
	 * @param string $migrations_path
	 */
	public function set_migrations_path( string $migrations_path ) {
		$this->migrations_path = $migrations_path;
	}
}
