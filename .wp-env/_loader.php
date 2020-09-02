<?php
// This file handles special loading of mu-plugins.

foreach ( scandir( __DIR__ . '/pub' ) as $file ) {
	if ( $file[0] === '.' || is_dir( __DIR__ . '/pub/' . $file ) ) { // phpcs:ignore WordPress.PHP.YodaConditions.NotYoda
		continue;
	}

	require_once __DIR__ . '/pub/' . $file;
}
