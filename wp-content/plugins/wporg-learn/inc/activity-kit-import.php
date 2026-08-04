<?php

namespace WPOrg_Learn\Activity_Kit_Import;

defined( 'WPINC' ) || die();

if ( ! defined( 'WP_CLI' ) || ! WP_CLI ) {
	return;
}

/**
 * WP-CLI commands for Activity Kit management.
 */
class Activity_Kit_CLI {

	/**
	 * Import the initial 11 Activity Kit posts.
	 *
	 * ## OPTIONS
	 *
	 * [--force]
	 * : Re-import even if posts already exist.
	 *
	 * ## EXAMPLES
	 *
	 *     wp activity-kit import
	 *
	 * @when after_wp_load
	 */
	public function import( $args, $assoc_args ) {
		$force = \WP_CLI\Utils\get_flag_value( $assoc_args, 'force', false );

		$kits = array(
			array(
				'title'  => 'Debugging for Developers',
				'topics' => array( 'development' ),
				'levels' => array( 'Intermediate' ),
			),
			array(
				'title'  => 'Debugging for Site Owners',
				'topics' => array( 'site-management' ),
				'levels' => array( 'Beginner' ),
			),
			array(
				'title'  => 'eCommerce with WooCommerce',
				'topics' => array( 'woocommerce', 'ecommerce' ),
				'levels' => array( 'Beginner' ),
			),
			array(
				'title'  => 'SEO Foundations',
				'topics' => array( 'seo' ),
				'levels' => array( 'Beginner' ),
			),
			array(
				'title'  => 'WordPress Playground',
				'topics' => array( 'playground' ),
				'levels' => array( 'Beginner' ),
			),
			array(
				'title'  => 'Content Creation',
				'topics' => array( 'content-creation' ),
				'levels' => array( 'Beginner' ),
			),
			array(
				'title'  => 'Using AI in your WordPress Dashboard',
				'topics' => array( 'ai', 'site-management' ),
				'levels' => array( 'Beginner' ),
			),
			array(
				'title'  => 'Managing your WordPress site with AI',
				'topics' => array( 'ai', 'site-management' ),
				'levels' => array( 'Intermediate' ),
			),
			array(
				'title'  => 'Contributor Onboarding',
				'topics' => array( 'contributing' ),
				'levels' => array( 'Beginner' ),
			),
			array(
				'title'  => 'WordPress Security Essentials',
				'topics' => array( 'security' ),
				'levels' => array( 'Beginner' ),
			),
			array(
				'title'  => 'Accessibility Testing in WordPress',
				'topics' => array( 'accessibility' ),
				'levels' => array( 'Beginner' ),
			),
		);

		foreach ( $kits as $kit_data ) {
			$existing_query = new \WP_Query(
				array(
					'post_type'      => 'activity_kit',
					'post_status'    => 'any',
					'title'          => $kit_data['title'],
					'posts_per_page' => 1,
					'fields'         => 'ids',
				)
			);
			$existing       = $existing_query->have_posts() ? get_post( $existing_query->posts[0] ) : null;

			if ( $existing && ! $force ) {
				\WP_CLI::log( sprintf( 'Skipping "%s" — already exists (ID %d). Use --force to re-import.', $kit_data['title'], $existing->ID ) );
				continue;
			}

			$post_id = wp_insert_post(
				array(
					'post_title'  => $kit_data['title'],
					'post_type'   => 'activity_kit',
					'post_status' => 'draft',
					'post_author' => 0,
				),
				true
			);

			if ( is_wp_error( $post_id ) ) {
				\WP_CLI::warning( sprintf( 'Failed to create "%s": %s', $kit_data['title'], $post_id->get_error_message() ) );
				continue;
			}

			if ( ! empty( $kit_data['topics'] ) ) {
				wp_set_object_terms( $post_id, $kit_data['topics'], 'topic' );
			}

			if ( ! empty( $kit_data['levels'] ) ) {
				wp_set_object_terms( $post_id, $kit_data['levels'], 'level' );
			}

			\WP_CLI::success( sprintf( 'Created "%s" (ID %d)', $kit_data['title'], $post_id ) );
		}

		\WP_CLI::log( 'Import complete.' );
	}
}

\WP_CLI::add_command( 'activity-kit', __NAMESPACE__ . '\Activity_Kit_CLI' );
