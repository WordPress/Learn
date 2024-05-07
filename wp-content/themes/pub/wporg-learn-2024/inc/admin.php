<?php

namespace WordPressdotorg\Theme\Learn_2024\Admin;

use WP_Query;
use function WordPressdotorg\Locales\get_locale_name_from_code;
use function WordPressdotorg\Theme\Learn_2024\Post_Meta\get_available_post_type_locales;
use function WordPressdotorg\Theme\Learn_2024\Taxonomy\get_available_taxonomy_terms;

defined( 'WPINC' ) || die();

/**
 * Actions and filters.
 */
add_action( 'restrict_manage_posts', __NAMESPACE__ . '\add_admin_list_table_filters', 10, 2 );
add_action( 'pre_get_posts', __NAMESPACE__ . '\handle_admin_list_table_filters' );

foreach ( array( 'meeting', 'course', 'lesson' ) as $pt ) {
	add_filter( 'manage_' . $pt . '_posts_columns', __NAMESPACE__ . '\add_list_table_language_column' );
	add_filter( 'manage_' . $pt . '_posts_custom_column', __NAMESPACE__ . '\render_list_table_language_column', 10, 2 );
}

/**
 * Add filtering controls for the course and lesson list tables.
 *
 * @param string $post_type
 * @param string $which
 *
 * @return void
 */
function add_admin_list_table_filters( $post_type, $which ) {
	if ( ( 'course' !== $post_type && 'lesson' !== $post_type ) || 'top' !== $which ) {
		return;
	}

	$audience    = filter_input( INPUT_GET, 'wporg_audience', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
	$level       = filter_input( INPUT_GET, 'wporg_experience_level', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
	$language    = filter_input( INPUT_GET, 'language', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
	$post_status = filter_input( INPUT_GET, 'post_status', FILTER_SANITIZE_FULL_SPECIAL_CHARS );

	$available_audiences = get_available_taxonomy_terms( 'audience', $post_type );
	$available_levels    = get_available_taxonomy_terms( 'level', $post_type );
	$available_locales   = get_available_post_type_locales( 'language', $post_type, $post_status );
	?>

		<label for="filter-by-audience" class="screen-reader-text">
			<?php esc_html_e( 'Filter by audience', 'wporg-learn' ); ?>
		</label>
		<select id="filter-by-audience" name="wporg_audience">
			<option value=""<?php selected( ! $audience ); ?>><?php esc_html_e( 'Any audience', 'wporg-learn' ); ?></option>
			<?php foreach ( $available_audiences as $code => $name ) : ?>
				<option value="<?php echo esc_attr( $code ); ?>"<?php selected( $code, $audience ); ?>>
					<?php echo esc_html( $name ); ?>
				</option>
			<?php endforeach; ?>
		</select>

		<label for="filter-by-level" class="screen-reader-text">
			<?php esc_html_e( 'Filter by level', 'wporg-learn' ); ?>
		</label>
		<select id="filter-by-level" name="wporg_experience_level">
			<option value=""<?php selected( ! $level ); ?>><?php esc_html_e( 'Any level', 'wporg-learn' ); ?></option>
			<?php foreach ( $available_levels as $code => $name ) : ?>
				<option value="<?php echo esc_attr( $code ); ?>"<?php selected( $code, $level ); ?>>
					<?php echo esc_html( $name ); ?>
				</option>
			<?php endforeach; ?>
		</select>

		<label for="filter-by-language" class="screen-reader-text">
			<?php esc_html_e( 'Filter by language', 'wporg-learn' ); ?>
		</label>
		<select id="filter-by-language" name="language">
			<option value=""<?php selected( ! $language ); ?>><?php esc_html_e( 'Any language', 'wporg-learn' ); ?></option>
			<?php foreach ( $available_locales as $code => $name ) : ?>
				<option value="<?php echo esc_attr( $code ); ?>"<?php selected( $code, $language ); ?>>
					<?php
					printf(
						'%s [%s]',
						esc_html( $name ),
						esc_html( $code )
					);
					?>
				</option>
			<?php endforeach; ?>
		</select>

	<?php
}

/**
 * Alter the query to include course and lesson list table filters.
 *
 * @param WP_Query $query
 *
 * @return void
 */
function handle_admin_list_table_filters( WP_Query $query ) {
	if ( ! is_admin() || ! function_exists( 'get_current_screen' ) ) {
		return;
	}

	$current_screen = get_current_screen();

	if ( ! $current_screen ) {
		return;
	}

	if ( 'edit-course' === $current_screen->id || 'edit-lesson' === $current_screen->id ) {
		$language = filter_input( INPUT_GET, 'language', FILTER_SANITIZE_FULL_SPECIAL_CHARS );

		if ( $language ) {
			$meta_query = $query->get( 'meta_query', array() );

			if ( ! empty( $meta_query ) ) {
				$meta_query = array(
					'relation' => 'AND',
					$meta_query,
				);
			}

			$meta_query[] = array(
				'key'   => 'language',
				'value' => $language,
			);

			$query->set( 'meta_query', $meta_query );
		}

		if ( 'language' === $query->get( 'orderby' ) ) {
			$query->set( 'meta_key', 'language' );
			$query->set( 'orderby', 'meta_value' );
		}
	}
}

/**
 * Add a language column to the post list table.
 *
 * @param array $columns
 *
 * @return array
 */
function add_list_table_language_column( $columns ) {
	$columns = array_slice( $columns, 0, -2, true )
				+ array( 'language' => __( 'Language', 'wporg-learn' ) )
				+ array_slice( $columns, -2, 2, true );

	return $columns;
}

/**
 * Render the cell contents for the additional language columns in the post list table.
 *
 * @param string $column_name
 * @param int    $post_id
 *
 * @return void
 */
function render_list_table_language_column( $column_name, $post_id ) {
	$language = get_post_meta( get_the_ID(), 'language', true );

	if ( 'language' === $column_name ) {
		printf(
			'%s [%s]',
			esc_html( get_locale_name_from_code( $language, 'english' ) ),
			esc_html( $language )
		);
	}
}
