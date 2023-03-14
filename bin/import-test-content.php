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

if ( 'local' !== wp_get_environment_type() ) {
	die( 'Not safe to run on ' . esc_html( get_site_url() ) );
}

/**
 * Sanitize postmeta from the rest API for the format required by wp_insert_post.
 *
 * @return array An array suitable for meta_input.
 */
function sanitize_meta_input( $meta ) {
	$meta = array( $meta );
	foreach ( $meta as $k => $v ) {
		if ( is_array( $v ) ) {
			$meta[ $k ] = implode( ',', $v );
		}
	}

	return $meta;
}

/**
 * Import posts from a remote REST API to the local test site.
 *
 * @param string $rest_url The remote REST API endpoint URL.
 */
function import_rest_to_posts( $rest_url ) {
	$response = wp_remote_get( $rest_url );
	$status_code = wp_remote_retrieve_response_code( $response );

	if ( is_wp_error( $response ) ) {
		die( esc_html( $response->get_error_message() ) );
	} elseif ( 200 !== wp_remote_retrieve_response_code( $response ) ) {
		die( esc_html( "HTTP Error $status_code \n" ) );
	}

	$body = wp_remote_retrieve_body( $response );
	$data = json_decode( $body );

	foreach ( $data as $post ) {
		echo esc_html( "Got {$post->type} {$post->id} {$post->slug}\n" );

		// Surely there's a neater way to do this.
		$newpost = array(
			'import_id' => $post->id,
			'post_date' => gmdate( 'Y-m-d H:i:s', strtotime( $post->date ) ),
			'post_name' => $post->slug,
			'post_title' => $post->title,
			'post_status' => $post->status,
			'post_type' => $post->type,
			'post_title' => $post->title->rendered,
			'post_content' => ( $post->content_raw ?? $post->content->rendered ),
			'post_parent' => $post->parent,
			'comment_status' => $post->comment_status,
			'meta_input' => sanitize_meta_input( $post->meta ),
		);

		$new_post_id = wp_insert_post( $newpost, true );

		if ( is_wp_error( $new_post_id ) ) {
			die( esc_html( $new_post_id->get_error_message() ) );
		}

		echo esc_html( "Inserted $post->type $post->id as $new_post_id\n" );
	}
}

import_rest_to_posts( 'https://learn.wordpress.org/wp-json/wp/v2/wporg_workshop?context=wporg_export&per_page=50' );
import_rest_to_posts( 'https://learn.wordpress.org/wp-json/wp/v2/lesson-plan?context=wporg_export&per_page=50' );
import_rest_to_posts( 'https://learn.wordpress.org/wp-json/wp/v2/pages?per_page=50' );
