<?php

namespace WordPressdotorg\Theme\Learn_2024\Admin;

use WP_Query;
use function WordPressdotorg\Locales\get_locale_name_from_code;
use function WordPressdotorg\Theme\Learn_2024\Taxonomy\get_available_taxonomy_terms;

defined( 'WPINC' ) || die();

/**
 * Actions and filters.
 */
add_action( 'restrict_manage_posts', __NAMESPACE__ . '\add_admin_list_table_filters', 10, 2 );
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

	$audience            = filter_input( INPUT_GET, 'wporg_audience', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
	$level               = filter_input( INPUT_GET, 'wporg_experience_level', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
	$available_audiences = get_available_taxonomy_terms( 'audience', $post_type );
	$available_levels    = get_available_taxonomy_terms( 'level', $post_type );
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

	<?php
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
