<?php
/**
 * Conflicts checker.
 *
 * @package sensei-pro
 *
 * @since 1.2.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once dirname( __DIR__ ) . '/modules/shared-module/includes/class-conflicts-checker.php';

/**
 * Tells if Sensei Pro has conflicts with other activated plugins.
 */
function sensei_pro_has_conflicts(): bool {
	$checker = new \Sensei_Pro\Conflicts_Checker(
		[
			'plugin_slug' => 'sensei-pro',
			'conflicts'   => [
				[
					'plugin_slug' => 'woothemes-sensei',
					'message'     => __(
						"Please deactivate the <strong>Sensei Pro (WC Paid Courses)</strong> plugin and try activating <strong>Sensei Pro</strong> again.
						All the features in <strong>Sensei Pro (WC Paid Courses)</strong> are included in <strong>Sensei Pro</strong>.
						You don't need both plugins.",
						'sensei-pro'
					),
				],
				[
					'plugin_slug'  => 'sensei-interactive-blocks',
					'deactivate'   => 'sensei-interactive-blocks',
					'message_type' => 'notice',
					'message'      => __(
						"<strong>Sensei Blocks</strong> plugin has been disabled. All the features in <strong>Sensei Blocks</strong>
						are included in <strong>Sensei Pro</strong>. You don't need both plugins.",
						'sensei-pro'
					),
				],
			],
		]
	);

	return $checker->has_conflicts();
}
