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
	 * [<blog_id>]
	 * : The ID of the blog to switch to (default: 7 for learn.wordpress.org)
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
	 * wp wporg-learn convert-tutorial-to-lesson https://learn.wordpress.org/tutorial/slug 696
	 * wp wporg-learn convert-tutorial-to-lesson urls.txt --file
	 * wp wporg-learn convert-tutorial-to-lesson urls.txt 696 --file --live
	 */
	public function __invoke( $args, $assoc_args ) {
		$source = $args[0];
		$blog_id = isset( $args[1] ) ? (int) $args[1] : 7;
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

			// Process each URL with blog_id
			$this->process_url( $url, $is_dry_run, $blog_id );
		}
	}

	/**
	 * Process a single URL.
	 */
	private function process_url( $url, $is_dry_run, $blog_id ) {
		// Switch to the specified blog
		switch_to_blog( $blog_id );

		// Extract the slug from the URL
		$path = parse_url( $url, PHP_URL_PATH );
		$slug = basename( untrailingslashit( $path ) );

		WP_CLI::line( "Slug: $slug" );

		// Get post by slug
		$post = get_page_by_path( $slug, OBJECT, 'wporg_workshop' );

		if ( ! $post ) {
			restore_current_blog();
			WP_CLI::warning( "No tutorial found with slug: $slug (URL: $url)" );
			return;
		}

		$post_id = $post->ID;

		if ( 'lesson' === $post->post_type ) {
			restore_current_blog();
			WP_CLI::warning( "Post is already a lesson (Post ID: $post_id)" );
			return;
		}

		if ( $is_dry_run ) {
			restore_current_blog();
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
			restore_current_blog();
			WP_CLI::warning( 'Failed to update post type: ' . $updated->get_error_message() );
			return;
		}

		restore_current_blog();
		WP_CLI::success( "Successfully converted tutorial to lesson (Post ID: $post_id)" );
	}
}

WP_CLI::add_command( 'wporg-learn convert-tutorial-to-lesson', 'WPORG_Learn_Tutorial_To_Lesson_Command' );
