<?php
// This file handles special loading of mu-plugins.

require_once __DIR__ . '/pub/class-validator.php';
require_once __DIR__ . '/pub/locales.php';

// Enable Jetpack OpenGraph output.
add_filter( 'jetpack_enable_open_graph', '__return_true' );
