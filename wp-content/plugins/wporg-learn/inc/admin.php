<?php

namespace WPOrg_Learn\Admin;

use WP_Query;
use function WordPressdotorg\Locales\get_locales_with_english_names;
use function WordPressdotorg\Locales\get_locale_name_from_code;
use function WPOrg_Learn\Post_Meta\get_available_workshop_locales;

defined( 'WPINC' ) || die();

/**
 * Actions and filters.
 */
add_action( 'admin_notices', __NAMESPACE__ . '\show_term_translation_notice' );
add_filter( 'manage_wporg_workshop_posts_columns', __NAMESPACE__ . '\add_workshop_list_table_columns' );
add_filter( 'manage_edit-topic_columns', __NAMESPACE__ . '\add_topic_list_table_column' );
foreach ( array( 'lesson-plan', 'meeting', 'course', 'lesson' ) as $pt ) {
	add_filter( 'manage_' . $pt . '_posts_columns', __NAMESPACE__ . '\add_list_table_language_column' );
}
add_action( 'manage_wporg_workshop_posts_custom_column', __NAMESPACE__ . '\render_workshop_list_table_columns', 10, 2 );
add_action( 'manage_topic_custom_column', __NAMESPACE__ . '\render_topics_list_table_columns', 10, 3 );
foreach ( array( 'lesson-plan', 'meeting', 'course', 'lesson' ) as $pt ) {
	add_filter( 'manage_' . $pt . '_posts_custom_column', __NAMESPACE__ . '\render_list_table_language_column', 10, 2 );
}
add_filter( 'manage_edit-wporg_workshop_sortable_columns', __NAMESPACE__ . '\add_workshop_list_table_sortable_columns' );
add_action( 'restrict_manage_posts', __NAMESPACE__ . '\add_workshop_list_table_filters', 10, 2 );
add_action( 'pre_get_posts', __NAMESPACE__ . '\handle_workshop_list_table_filters' );
add_filter( 'display_post_states', __NAMESPACE__ . '\add_post_states', 10, 2 );
foreach ( array( 'lesson-plan', 'wporg_workshop', 'course', 'lesson' ) as $pt ) {
	add_filter( 'views_edit-' . $pt, __NAMESPACE__ . '\list_table_views' );
}
add_action( 'pre_get_posts', __NAMESPACE__ . '\handle_list_table_views' );
add_action( 'bulk_edit_custom_box', __NAMESPACE__ . '\add_language_bulk_edit_field', 10, 2 );
add_action( 'save_post', __NAMESPACE__ . '\language_bulk_edit_save' );

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
				+ array( 'language' => __( 'Language', 'wporg-learn' ) )
				+ array( 'video_caption_language' => __( 'Subtitles', 'wporg-learn' ) )
				+ array_slice( $columns, -2, 2, true );

	return $columns;
}

/**
 * Add additional columns to the terms list table for topics.
 *
 * @param array $columns
 *
 * @return array
 */
function add_topic_list_table_column( $columns ) {
	$columns = array_slice( $columns, 0, -1, true )
				+ array( 'icon' => __( 'Icon', 'wporg-learn' ) )
				+ array( 'sticky' => __( 'Sticky', 'wporg-learn' ) )
				+ array_slice( $columns, -1, 1, true );

	return $columns;
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
		case 'language':
			$language = get_post_meta( get_the_ID(), 'language', true );

			printf(
				'%s [%s]',
				esc_html( get_locale_name_from_code( $language, 'english' ) ),
				esc_html( $language )
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
 * Render the cell contents for the additional columns in the terms list table for topics.
 *
 * @param string $content
 * @param string $column_name
 * @param int    $term_id
 *
 * @return void
 */
function render_topics_list_table_columns( $content, $column_name, $term_id ) {
	switch ( $column_name ) {
		case 'icon':
			$icon = get_term_meta( $term_id, 'dashicon-class', true );

			echo esc_html( $icon );
			break;
		case 'sticky':
			$sticky = get_term_meta( $term_id, 'sticky', true );

			echo $sticky ? '<span class="dashicons dashicons-sticky"></span>' : '';
			break;
	}
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

/**
 * Make additional columns sortable.
 *
 * @param array $sortable_columns
 *
 * @return array
 */
function add_workshop_list_table_sortable_columns( $sortable_columns ) {
	$sortable_columns['language'] = 'language';

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

	$available_locales = get_available_workshop_locales( 'language', 'english', false );
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
	if ( ! is_admin() || ! function_exists( 'get_current_screen' ) ) {
		return;
	}

	$current_screen = get_current_screen();

	if ( ! $current_screen ) {
		return;
	}

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
 * Custom post states for list tables.
 *
 * @param array    $post_states
 * @param \WP_Post $post
 *
 * @return mixed
 */
function add_post_states( $post_states, $post ) {
	$expiration_date = $post->expiration_date;

	if ( $expiration_date ) {
		$exp = strtotime( $expiration_date );
		$now = strtotime( 'now' );

		if ( $exp > $now ) {
			$post_states[] = sprintf(
				esc_html__( 'Expires in %s', 'wporg-learn' ),
				esc_html( human_time_diff( $now, $exp ) )
			);
		} else {
			$post_states[] = sprintf(
				'<span style="color: #b32d2e;">%s</span>',
				esc_html__( 'Expired', 'wporg-learn' )
			);
		}
	}

	return $post_states;
}

/**
 * Add view links to the patterns list table.
 *
 * @param array $views
 *
 * @return array
 */
function list_table_views( $views ) {
	global $typenow;

	$wants_expired = filter_input( INPUT_GET, 'expired', FILTER_VALIDATE_BOOLEAN );

	$url = add_query_arg(
		array(
			'post_type' => $typenow,
			'expired' => 1,
		),
		admin_url( 'edit.php' )
	);

	$extra_attributes = '';
	if ( $wants_expired ) {
		$extra_attributes = ' class="current" aria-current="page"';
	}

	$expired_posts_query = new \WP_Query( array(
		'post_type'   => $typenow,
		'post_status' => 'any',
		'numberposts' => 1,
		'meta_query'  => array(
			array(
				'key'     => 'expiration_date',
				'value'   => current_time( 'mysql' ),
				'compare' => '<',
			),
		),
	) );

	$views['expired'] = sprintf(
		'<a href="%s"%s>%s</a>',
		esc_url( $url ),
		$extra_attributes,
		sprintf(
			/* translators: %s: Number of posts. */
			_n(
				'Expired <span class="count">(%s)</span>',
				'Expired <span class="count">(%s)</span>',
				$expired_posts_query->found_posts,
				'wporg-learn'
			),
			number_format_i18n( $expired_posts_query->found_posts )
		)
	);

	return $views;
}


/**
 * Modify the query that populates the patterns list table.
 *
 * @param WP_Query $query
 *
 * @return void
 */
function handle_list_table_views( WP_Query $query ) {
	global $typenow;

	$wants_expired = filter_input( INPUT_GET, 'expired', FILTER_VALIDATE_BOOLEAN );

	if ( ! is_admin() || ! $query->is_main_query() || ! function_exists( 'get_current_screen' ) ) {
		return;
	}

	$current_screen = get_current_screen();

	if ( 'edit-' . $typenow === $current_screen->id ) {
		if ( $wants_expired ) {
			$meta_query = $query->get( 'meta_query', array() );

			$meta_query[] = array(
				'key'     => 'expiration_date',
				'value'   => current_time( 'mysql' ),
				'compare' => '<',
			);

			$query->set( 'meta_query', $meta_query );
		}
	}
}

/**
 * Render a field for the additional language meta field on the bulk edit screen.
 *
 * @param string $column_name
 * @param string $post_type
 *
 * @return void
 */
function add_language_bulk_edit_field( $column_name, $post_type ) {
	$post_types_with_language = array( 'lesson-plan', 'wporg_workshop', 'meeting', 'course', 'lesson' );
	if ( in_array( $post_type, $post_types_with_language, true ) && 'language' === $column_name ) {
		$locales = get_locales_with_english_names();
		?>
			<fieldset class="inline-edit-col-right">
				<div class="inline-edit-group wp-clearfix">
					<label class="inline-edit-status alignleft">
						<span class="title">Language</span>
						<select name="language">
							<option value="0" selected>— No Change —</option>
						<?php foreach ( $locales as $code => $label ) : ?>
							<option value="<?php echo esc_attr( $code ); ?>">
								<?php echo esc_html( $label ); ?>
							</option>
						<?php endforeach; ?>
						</select>
					</label>
				</div>
			</fieldset>
		<?php
	}
}

/**
 * Update the language meta field when bulk editing.
 *
 * @param int $post_id
 *
 * @return void
 */
function language_bulk_edit_save( $post_id ) {
	if ( empty( $_REQUEST['language'] ) || empty( $_REQUEST['_wpnonce'] ) || ! wp_verify_nonce( $_REQUEST['_wpnonce'], 'bulk-posts' ) ) {
		return;
	}

	update_post_meta( $post_id, 'language', $_REQUEST['language'] );
}
