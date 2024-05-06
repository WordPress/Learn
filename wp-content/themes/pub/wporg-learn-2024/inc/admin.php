<?php

namespace WordPressdotorg\Theme\Learn_2024\Admin;

use WP_Query;
use function WordPressdotorg\Theme\Learn_2024\Taxonomy\get_available_taxonomy_terms;

defined( 'WPINC' ) || die();

/**
 * Actions and filters.
 */
add_action( 'restrict_manage_posts', __NAMESPACE__ . '\add_admin_list_table_filters', 10, 2 );

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

	$level            = filter_input( INPUT_GET, 'wporg_experience_level', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
	$available_levels = get_available_taxonomy_terms( 'level', $post_type );

	if ( empty( $available_levels ) ) {
		return;
	}

	?>

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
