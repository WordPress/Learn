<?php
/**
 * File containing a migration.
 *
 * @package sensei-pro
 * @since   1.4.0
 */

// TODO: Remove this file once the first real migration is created.

use Sensei_Pro_Installer\Migration;

/**
 * Data migration class.
 *
 * The migration run order is determined by the file prefix number.
 *
 * @since 1.4.0
 */
return new class() implements Migration {
	/**
	 * The targeted plugin version.
	 *
	 * @return string
	 */
	public function target_version(): string {
		return '1.0.0';
	}

	/**
	 * Run the migration.
	 */
	public function run() {
		// TODO: Implement run() method.
	}
};
