<?php

namespace WPOrg_Learn\Admin;

use function WordPressdotorg\Locales\get_locale_name_from_code;

defined( 'WPINC' ) || die();

/**
 * Actions and filters.
 */
add_action( 'admin_notices', __NAMESPACE__ . '\show_term_translation_notice' );
add_filter( 'manage_wporg_workshop_posts_columns', __NAMESPACE__ . '\add_workshop_list_table_columns' );
add_action( 'manage_wporg_workshop_posts_custom_column', __NAMESPACE__ . '\render_workshop_list_table_columns', 10, 2 );

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
			echo esc_html( get_locale_name_from_code( $post->video_language, 'english' ) );
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
