#!/usr/bin/php
<?php

namespace WPOrg_Learn\Bin\I18n;

use Requests;

require_once dirname( __DIR__ ) . '/vendor/autoload.php';

const ENDPOINT_BASE = 'https://learn.wordpress.org/wp-json/wp/v2/';

/**
 * Get data about taxonomies from a REST API endpoint.
 *
 * @param array $valid_post_types Slugs of post types that support the taxonomies we want.
 *
 * @return array
 */
function get_taxonomies( array $valid_post_types = array() ) {
	$endpoint = ENDPOINT_BASE . 'taxonomies';

	$response = Requests::get( $endpoint );

	if ( 200 !== $response->status_code ) {
		die( 'Could not retrieve taxonomy data.' );
	}

	$taxonomies = json_decode( $response->body, true );

	if ( ! is_array( $taxonomies ) ) {
		die( 'Taxonomies request returned unexpected data.' );
	}

	if ( count( $valid_post_types ) > 0 ) {
		$taxonomies = array_filter(
			$taxonomies,
			function( $tax ) use ( $valid_post_types ) {
				$supported_types = $tax['types'];
				$matches = array_intersect( $supported_types, $valid_post_types );

				return count( $matches ) > 0;
			}
		);
	}

	return $taxonomies;
}

/**
 * Get data about a taxonomy's terms from a REST API endpoint.
 *
 * @param string $taxonomy
 *
 * @return array
 */
function get_taxonomy_terms( $taxonomy ) {
	$endpoint = ENDPOINT_BASE . $taxonomy . '?per_page=100';

	$response = Requests::get( $endpoint );

	if ( 200 !== $response->status_code ) {
		die( sprintf(
			'Could not retrieve terms for %s.',
			$taxonomy // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		) );
	}

	$terms = json_decode( $response->body, true );

	if ( ! is_array( $terms ) ) {
		die( sprintf(
			'Terms request for %s returned unexpected data.',
			$taxonomy // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		) );
	}

	return $terms;
}

/**
 * Run the script.
 */
function main() {
	if ( 'cli' === php_sapi_name() ) {
		echo "\n";
		echo "Retrieving taxonomies...\n";
	}

	$valid_post_types = array(
		'lesson-plan',
		'wporg_workshop',
		'course',
		'lesson',
	);
	$taxonomies = get_taxonomies( $valid_post_types );

	if ( 'cli' === php_sapi_name() ) {
		echo "Retrieving terms...\n";
		echo "\n";
	}

	$terms_by_tax = array();
	foreach ( $taxonomies as $taxonomy ) {
		$terms = get_taxonomy_terms( $taxonomy['slug'] );

		if ( count( $terms ) > 0 ) {
			$terms_by_tax[ $taxonomy['name'] ] = $terms;
		}

		unset( $terms );
	}

	$file_content = '';
	foreach ( $terms_by_tax as $tax_label => $terms ) {
		$label = addcslashes( $tax_label, "'" );

		foreach ( $terms as $term ) {

			$name = addcslashes( $term['name'], "'" );
			$link = addcslashes( $term['link'], '*' );

			$file_content .= "/* translators: {$link} */\n_x( '{$name}', '$label term name', 'wporg-learn' );\n";

			if ( 'cli' === php_sapi_name() ) {
				echo "$name\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			}

			if ( $term['description'] ) {
				$description = addcslashes( $term['description'], "'" );
				$file_content .= "/* translators: {$link} */\n_x( '{$description}', '$label term description', 'wporg-learn' );\n";
			}
		}
	}

	$path = dirname( __DIR__ ) . '/extra';
	if ( ! is_readable( $path ) ) {
		mkdir( $path );
	}

	$file_name = 'translation-strings.php';
	$file_header = <<<HEADER
<?php
/**
 * Generated file for translation strings.
 *
 * Used to import additional strings into the learn-wordpress translation project.
 *
 * ⚠️ This is a generated file. Do not edit manually. See bin/i18n.php.
 * ⚠️ Do not require or include this file anywhere.
 */


HEADER;

	file_put_contents( $path . '/' . $file_name, $file_header . $file_content );

	if ( 'cli' === php_sapi_name() ) {
		echo "\n";
		echo "Done.\n";
	}
}

main();
