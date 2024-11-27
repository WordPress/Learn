<?php

if ( ! defined( 'WP_CLI' ) ) {
	return;
}

/**
 * Converts a tutorial post to a lesson post type.
 */
class WPORG_Learn_Tutorial_To_Lesson_Command extends WP_CLI_Command {
	/**
	 * Converts tutorials to lessons based on the provided URL or file.
	 *
	 * ## OPTIONS
	 *
	 * <source>
	 * : The URL of the tutorial post to convert, or path to a file containing URLs (one per line)
	 *
	 * [--live]
	 * : Actually perform the conversion (default is dry-run)
	 *
	 * [--file]
	 * : Indicates that the source is a file containing URLs
	 *
	 * ## EXAMPLES
	 *
	 * wp wporg-learn convert-tutorial-to-lesson https://learn.wordpress.org/tutorial/slug
	 * wp wporg-learn convert-tutorial-to-lesson urls.txt --file
	 * wp wporg-learn convert-tutorial-to-lesson urls.txt --file --live
	 */
	public function __invoke( $args, $assoc_args ) {
		$source = $args[0];
		$is_dry_run = ! isset( $assoc_args['live'] );
		$is_file = isset( $assoc_args['file'] );

		$urls = array();
		if ( $is_file ) {
			if ( ! file_exists( $source ) ) {
				WP_CLI::error( "File not found: $source" );
				return;
			}
			$urls = array_filter( explode( "\n", file_get_contents( $source ) ) );
		} else {
			$urls = array( $source );
		}

		foreach ( $urls as $url ) {
			$url = trim( $url );
			if ( empty( $url ) ) {
				continue;
			}

			// Process each URL
			$this->process_url( $url, $is_dry_run );
		}
	}

	/**
	 * Process a single URL.
	 */
	private function process_url( $url, $is_dry_run ) {
		// Get post ID from URL
		$post_id = url_to_postid( $url );

		if ( ! $post_id ) {
			WP_CLI::warning( "No post found for URL: $url" );
			return;
		}

		$post = get_post( $post_id );

		if ( ! $post ) {
			WP_CLI::warning( "Could not retrieve post with ID: $post_id" );
			return;
		}

		if ( 'wporg_workshop' !== $post->post_type ) {
			WP_CLI::warning( "Post is not a tutorial (Post ID: $post_id)" );
			return;
		}

		if ( 'lesson' === $post->post_type ) {
			WP_CLI::warning( "Post is already a lesson (Post ID: $post_id)" );
			return;
		}

		if ( $is_dry_run ) {
			WP_CLI::line( sprintf(
				"Dry run for:\nURL: %s\nTitle: %s\nPost ID: %d\nPost Type: %s\n---------------",
				$url,
				$post->post_title,
				$post_id,
				$post->post_type
			) );
			return;
		}

		// Update the post type
		$updated = wp_update_post( array(
			'ID' => $post_id,
			'post_type' => 'lesson',
		) );

		if ( is_wp_error( $updated ) ) {
			WP_CLI::warning( 'Failed to update post type: ' . $updated->get_error_message() );
			return;
		}

		WP_CLI::success( "Successfully converted tutorial to lesson (Post ID: $post_id)" );
	}
}

WP_CLI::add_command( 'wporg-learn convert-tutorial-to-lesson', 'WPORG_Learn_Tutorial_To_Lesson_Command' );
