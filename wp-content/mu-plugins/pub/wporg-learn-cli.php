<?php

if ( ! defined( 'WP_CLI' ) ) {
	return;
}

/**
 * Converts a tutorial post to a lesson post type.
 */
class WPORG_Learn_Tutorial_To_Lesson_Command extends WP_CLI_Command {
	/**
	 * Converts a tutorial post to a lesson based on the provided URL.
	 *
	 * ## OPTIONS
	 *
	 * <url>
	 * : The URL of the tutorial post to convert
	 *
	 * [--live]
	 * : Actually perform the conversion (default is dry-run)
	 *
	 * ## EXAMPLES
	 *
	 * wp wporg-learn-tutorial-to-lesson convert https://learn.wordpress.org/tutorial/slug
	 * wp wporg-learn-tutorial-to-lesson convert https://learn.wordpress.org/tutorial/slug --live
	 *
	 * @param array $args Command arguments.
	 * @param array $assoc_args Associative arguments.
	 */
	public function convert( $args, $assoc_args ) {
		$url = $args[0];
		$is_dry_run = ! isset( $assoc_args['live'] );

		// Get post ID from URL
		$post_id = url_to_postid( $url );

		if ( ! $post_id ) {
			WP_CLI::error( "No post found for URL: $url" );
			return;
		}

		$post = get_post( $post_id );

		if ( ! $post ) {
			WP_CLI::error( "Could not retrieve post with ID: $post_id" );
			return;
		}

		if ( 'wporg_workshop' !== $post->post_type ) {
			WP_CLI::error( "Post is not a tutorial (Post ID: $post_id)" );
			return;
		}

		if ( 'lesson' === $post->post_type ) {
			WP_CLI::error( "Post is already a lesson (Post ID: $post_id)" );
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
			WP_CLI::error( 'Failed to update post type: ' . $updated->get_error_message() );
			return;
		}

		WP_CLI::success( "Successfully converted tutorial to lesson (Post ID: $post_id)" );
	}
}

WP_CLI::add_command( 'wporg-learn-tutorial-to-lesson', 'WPORG_Learn_Tutorial_To_Lesson_Command' );
