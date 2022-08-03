<?php
/**
 * File containing the interface \Sensei_Pro_Installer\Migration.
 *
 * @package sensei-pro
 * @since   1.4.0
 */

namespace Sensei_Pro_Installer;

/**
 * Migration interface.
 *
 * @since 1.4.0
 */
interface Migration {
	/**
	 * The targeted plugin version.
	 *
	 * @since 1.4.0
	 *
	 * @return string
	 */
	public function target_version(): string;

	/**
	 * Run the migration.
	 *
	 * @since 1.4.0
	 */
	public function run();
}
