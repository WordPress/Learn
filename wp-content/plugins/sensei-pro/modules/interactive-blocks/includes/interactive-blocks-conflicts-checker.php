<?php
/**
 * Conflicts checker.
 *
 * @package sensei-interactive-blocks
 *
 * @since 1.2.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once dirname( __DIR__ ) . '/shared-module/includes/class-conflicts-checker.php';
require_once ABSPATH . 'wp-admin/includes/plugin.php';

// The namespace is replaced with `Sensei_Blocks` in the `Conflicts_Checker` during the build.
use Sensei_Blocks\Conflicts_Checker;

/**
 * Tells if Sensei Pro has conflicts with other activated plugins.
 */
function sensei_interactive_blocks_has_conflicts(): bool { // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedFunctionFound

	$checker = new Conflicts_Checker(
		[
			'plugin_slug' => 'sensei-interactive-blocks',
			'conflicts'   => [
				[
					'plugin_slug' => 'sensei-pro',
					'message'     => __(
						"<strong>Sensei Pro</strong> already includes all the blocks included in the <strong>Sensei Blocks</strong> plugin.
						You don't need <strong>Sensei Blocks</strong> plugin if you already using <strong>Sensei Pro</strong> plugin.",
						'sensei-pro'
					),
				],
				[
					'plugin_slug' => 'woothemes-sensei',
					'message'     => __(
						"<strong>Sensei Pro (WC Paid Courses)</strong> already includes all the blocks included in the <strong>Sensei Blocks</strong> plugin.
						You don't need <strong>Sensei Blocks</strong> plugin if you already using <strong>Sensei Pro (WC Paid Courses)</strong> plugin.",
						'sensei-pro'
					),
				],
			],
		]
	);

	$has_conflicts = $checker->has_conflicts() || is_plugin_active( 'sensei-pro/sensei-pro.php' ) || is_plugin_active( 'woothemes-sensei/woothemes-sensei.php' );

	return $has_conflicts;
}
