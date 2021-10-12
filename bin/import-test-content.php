#!/usr/bin/php
<?php

namespace WPOrg_Learn\Bin\ImportTestContent;

/**
 * CLI script for generating local test content, fetched from the live learn.wordpress.org site.
 *
 * This needs to be run in a wp-env, for example:
 *
 * yarn run wp-env run cli "php bin/import-test-content.php"
 */

// This script should only be called in a CLI environment.
if ( 'cli' != php_sapi_name() ) {
    die();
}


$opts = getopt( '', array( 'post:', 'url:', 'abspath:', 'age:' ) );

require dirname( dirname( __FILE__ ) ) . '/wp-load.php';


function sanitize_meta_input( $meta ) {
	$meta = (array( $meta ) );
	foreach ( $meta as $k => $v ) {
		if ( is_array( $v ) ) {
			$meta[ $k ] = implode( ',', $v );
		}
	}

	return $meta;
}

function import_rest_to_posts( $rest_url ) {
	$response = wp_remote_get( $rest_url );
	if ( is_wp_error( $response ) ) {
		die( $response->get_error_message() );
	}

	$body = wp_remote_retrieve_body( $response );
	$data = json_decode( $body );

	foreach ( $data as $post ) {
		echo "Got {$post->type} {$post->id} {$post->slug}\n";


		// Surely there's a neater way to do this.
		$newpost = array (
			'import_id' => $post->id,
			'post_date' => date( 'Y-m-d H:i:s', strtotime($post->date) ),
			'post_name' => $post->slug,
			'post_title' => $post->title,
			'post_status' => $post->status,
			'post_type' => $post->type,
			'post_title' => $post->title->rendered,
			'post_content' => $post->content->rendered, // TODO: can we re-parse this with parse_plocks() / serialize_block() etc?
			'post_parent' => $post->parent,
			'comment_status' => $post->comment_status,
			'meta_input' => sanitize_meta_input( $post->meta ),
		);

		$r = wp_insert_post( $newpost, true );

		if ( is_wp_error( $r ) ) {
			die( $r->get_error_message() );
		}

		echo "Inserted $post->type $post->id as $r\n";
	}
}

import_rest_to_posts( 'https://learn.wordpress.org/wp-json/wp/v2/wporg_workshop' );
import_rest_to_posts( 'https://learn.wordpress.org/wp-json/wp/v2/lesson-plan' );