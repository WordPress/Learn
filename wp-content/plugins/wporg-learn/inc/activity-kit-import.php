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
				'levels' => array( 'intermediate' ),
			),
			array(
				'title'  => 'Debugging for Site Owners',
				'topics' => array( 'site-management' ),
				'levels' => array( 'beginner' ),
			),
			array(
				'title'  => 'eCommerce with WooCommerce',
				'topics' => array( 'woocommerce', 'ecommerce' ),
				'levels' => array( 'beginner' ),
			),
			array(
				'title'  => 'SEO Foundations',
				'topics' => array( 'seo' ),
				'levels' => array( 'beginner' ),
			),
			array(
				'title'  => 'WordPress Playground',
				'topics' => array( 'playground' ),
				'levels' => array( 'beginner' ),
			),
			array(
				'title'  => 'Content Creation',
				'topics' => array( 'content-creation' ),
				'levels' => array( 'beginner' ),
			),
			array(
				'title'  => 'Using AI in your WordPress Dashboard',
				'topics' => array( 'ai', 'site-management' ),
				'levels' => array( 'beginner' ),
			),
			array(
				'title'  => 'Managing your WordPress site with AI',
				'topics' => array( 'ai', 'site-management' ),
				'levels' => array( 'intermediate' ),
			),
			array(
				'title'  => 'Contributor Onboarding',
				'topics' => array( 'contributing' ),
				'levels' => array( 'beginner' ),
			),
			array(
				'title'  => 'WordPress Security Essentials',
				'topics' => array( 'security' ),
				'levels' => array( 'beginner' ),
			),
			array(
				'title'  => 'Accessibility Testing in WordPress',
				'topics' => array( 'accessibility' ),
				'levels' => array( 'beginner' ),
			),
		);

		foreach ( $kits as $kit_data ) {
			$existing = get_page_by_title( $kit_data['title'], OBJECT, 'activity_kit' );

			if ( $existing && ! $force ) {
				\WP_CLI::log( sprintf( 'Skipping "%s" — already exists (ID %d). Use --force to re-import.', $kit_data['title'], $existing->ID ) );
				continue;
			}

			$post_id = wp_insert_post(
				array(
					'post_title'  => $kit_data['title'],
					'post_type'   => 'activity_kit',
					'post_status' => 'publish',
					'post_author' => 1,
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
