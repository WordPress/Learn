<?php

namespace WPOrg_Learn\Admin;

use WP_Query;
use function WordPressdotorg\Locales\get_locale_name_from_code;
use function WPOrg_Learn\Post_Meta\get_available_workshop_locales;

defined( 'WPINC' ) || die();

/**
 * Actions and filters.
 */
add_action( 'admin_notices', __NAMESPACE__ . '\show_term_translation_notice' );
add_filter( 'manage_wporg_workshop_posts_columns', __NAMESPACE__ . '\add_workshop_list_table_columns' );
add_action( 'manage_wporg_workshop_posts_custom_column', __NAMESPACE__ . '\render_workshop_list_table_columns', 10, 2 );
add_filter( 'manage_edit-wporg_workshop_sortable_columns', __NAMESPACE__ . '\add_workshop_list_table_sortable_columns' );
add_action( 'restrict_manage_posts', __NAMESPACE__ . '\add_workshop_list_table_filters', 10, 2 );
add_action( 'pre_get_posts', __NAMESPACE__ . '\handle_workshop_list_table_filters' );

/**
 * Show a notice on taxonomy term screens about terms being translatable.
 *
 * @return void
 */
function show_term_translation_notice() {
	global $pagenow, $taxnow, $typenow;

	if ( 'edit-tags.php' !== $pagenow ) {
		return;
	}

	$valid_post_types = array(
		'lesson-plan',
		'wporg_workshop',
		'course',
		'lesson',
	);

	if ( ! in_array( $typenow, $valid_post_types, true ) ) {
		return;
	}

	if ( empty( $taxnow ) ) {
		return;
	}

	$taxonomy = get_taxonomy( $taxnow );
	$labels   = get_taxonomy_labels( $taxonomy );

	?>
	<div class="notice notice-info is-dismissible">
		<p>
			<?php
			printf(
				wp_kses_post( __( '
					Names and descriptions of %1$s can be translated via the Learn WordPress <a href="%2$s">translation project</a>. Once you have added or changed a term\'s name or description, it may take up to 24 hours before it is available for translation.
				', 'wporg-learn' ) ),
				esc_html( $labels->name ),
				'https://translate.wordpress.org/projects/meta/learn-wordpress/'
			);
			?>
		</p>
	</div>
	<?php
}

/**
 * Add additional columns to the post list table for workshops.
 *
 * @param array $columns
 *
 * @return array
 */
function add_workshop_list_table_columns( $columns ) {
	$columns = array_slice( $columns, 0, -2, true )
				+ array( 'video_language' => __( 'Language', 'wporg-learn' ) )
				+ array( 'video_caption_language' => __( 'Captions', 'wporg-learn' ) )
				+ array_slice( $columns, -2, 2, true );

	return $columns;
}

/**
 * Render the cell contents for the additional columns in the post list table for workshops.
 *
 * @param string $column_name
 * @param int    $post_id
 *
 * @return void
 */
function render_workshop_list_table_columns( $column_name, $post_id ) {
	$post = get_post( $post_id );

	switch ( $column_name ) {
		case 'video_language':
			printf(
				'%s [%s]',
				esc_html( get_locale_name_from_code( $post->video_language, 'english' ) ),
				esc_html( $post->video_language )
			);
			break;
		case 'video_caption_language':
			$captions = get_post_meta( $post->ID, 'video_caption_language' );

			echo esc_html( implode(
				', ',
				array_map(
					function( $caption_lang ) {
						return get_locale_name_from_code( $caption_lang, 'english' );
					},
					$captions
				)
			) );
			break;
	}
}

/**
 * Make additional columns sortable.
 *
 * @param array $sortable_columns
 *
 * @return array
 */
function add_workshop_list_table_sortable_columns( $sortable_columns ) {
	$sortable_columns['video_language'] = 'video_language';

	return $sortable_columns;
}

/**
 * Add filtering controls for the workshops list table.
 *
 * @param string $post_type
 * @param string $which
 *
 * @return void
 */
function add_workshop_list_table_filters( $post_type, $which ) {
	if ( 'wporg_workshop' !== $post_type || 'top' !== $which ) {
		return;
	}

	$available_locales = get_available_workshop_locales( 'video_language', 'english', false );
	$language          = filter_input( INPUT_GET, 'language', FILTER_SANITIZE_STRING );

	?>
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
 * Alter the query to include workshop list table filters.
 *
 * @param WP_Query $query
 *
 * @return void
 */
function handle_workshop_list_table_filters( WP_Query $query ) {
	if ( ! is_admin() ) {
		return;
	}

	$current_screen = get_current_screen();

	if ( 'edit-wporg_workshop' === $current_screen->id ) {
		$language = filter_input( INPUT_GET, 'language', FILTER_SANITIZE_STRING );

		if ( $language ) {
			$meta_query = $query->get( 'meta_query', array() );

			if ( ! empty( $meta_query ) ) {
				$meta_query = array(
					'relation' => 'AND',
					$meta_query,
				);
			}

			$meta_query[] = array(
				'key'   => 'video_language',
				'value' => $language,
			);

			$query->set( 'meta_query', $meta_query );
		}

		if ( 'video_language' === $query->get( 'orderby' ) ) {
			$query->set( 'meta_key', 'video_language' );
			$query->set( 'orderby', 'meta_value' );
		}
	}
}
