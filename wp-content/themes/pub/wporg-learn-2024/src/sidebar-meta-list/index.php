<?php
/**
 * Block Name: Sidebar Meta List
 * Description: Display the site meta data of a learn or course as a list.
 *
 * @package wporg
 */

namespace WordPressdotorg\Theme\Learn_2024\Sidebar_Meta_List;

use Sensei_Utils;
use Sensei_Reports_Overview_Service_Courses;
use function WPOrg_Learn\Post_Meta\{get_workshop_duration};
use function WordPressdotorg\Locales\get_locale_name_from_code;

add_action( 'init', __NAMESPACE__ . '\init' );

/**
 * Registers the block using the metadata loaded from the `block.json` file.
 * Behind the scenes, it registers also all assets so they can be enqueued
 * through the block editor in the corresponding context.
 *
 * @see https://developer.wordpress.org/reference/functions/register_block_type/
 */
function init() {
	register_block_type(
		dirname( dirname( __DIR__ ) ) . '/build/sidebar-meta-list',
		array(
			'render_callback' => __NAMESPACE__ . '\render',
		)
	);
}

/**
 * Render the block content.
 *
 * @param array    $attributes Block attributes.
 * @param string   $content    Block default content.
 * @param WP_Block $block      Block instance.
 *
 * @return string Returns the block markup.
 */
function render( $attributes, $content, $block ) {
	if ( ! isset( $block->context['postId'] ) ) {
		return '';
	}

	$list_items = array();

	if ( 'course' === $block->context['postType'] ) {
		$course_service = new Sensei_Reports_Overview_Service_Courses();
		$course_id = $block->context['postId'];

		// Get the total number of learners enrolled in the course.
		$learners = Sensei_Utils::sensei_check_for_activity(
			array(
				'type'     => 'sensei_course_status',
				'status'   => 'in-progress',
				'post__in' => $course_id,
			)
		);

		// Get the average grade across all learners.
		$average_grade = round( $course_service->get_courses_average_grade( array( $course_id ) ), 0 );

		// Get the average number of days it takes to complete a course.
		$average_days = $course_service->get_average_days_to_completion( array( $course_id ) );

		// Get the last updated time.
		$last_updated = get_last_updated_time( $course_id );

		// Set up array of data to be used.
		$meta_fields = array(
			array(
				'label' => __( 'Enrolled learners', 'wporg-learn' ),
				'value' => $learners,
				'key'   => 'learners',
			),
			array(
				'label' => __( 'Average final grade', 'wporg-learn' ),
				'value' => $average_grade . '%',
				'key'   => 'average-grade',
			),
			array(
				'label' => __( 'Average days to completion', 'wporg-learn' ),
				'value' => $average_days,
				'key'   => 'average-days',
			),
			array(
				'label' => __( 'Last updated', 'wporg-learn' ),
				'value' => $last_updated,
				'key'   => 'last-updated',
			),
		);
	} elseif ( 'wporg_workshop' === $block->context['postType'] ) {
		$workshop = get_post( $block->context['postId'] );

		if ( ! $workshop ) {
			return '';
		}

		$meta_fields = array(
			array(
				'label' => __( 'Length', 'wporg-learn' ),
				'value' => get_workshop_duration( $workshop, 'string' ),
				'key'   => 'length',
			),
			array(
				'label' => __( 'Language', 'wporg-learn' ),
				'value' => esc_html( get_locale_name_from_code( $workshop->language, 'native' ) ),
				'key'   => 'language',
			),
		);

		$captions = get_post_meta( $block->context['postId'], 'video_caption_language' );
		$subtitles = array_map(
			function( $caption_lang ) {
				return esc_html( get_locale_name_from_code( $caption_lang, 'native' ) );
			},
			$captions
		);

		if ( ! empty( $captions ) ) {
			$meta_fields[] = array(
				'label' => __( 'Subtitles', 'wporg-learn' ),
				'value' => implode( ', ', $subtitles ),
				'key'   => 'subtitles',
			);
		}
	} elseif ( 'lesson-plan' === $block->context['postType'] ) {
		$lesson_plan_id = $block->context['postId'];

		$duration         = get_post_taxonomy_terms( $lesson_plan_id, 'duration' );
		$audience         = get_post_taxonomy_terms( $lesson_plan_id, 'audience' );
		$level            = get_post_taxonomy_terms( $lesson_plan_id, 'level', true, 'lesson-plans' );
		$instruction_type = get_post_taxonomy_terms( $lesson_plan_id, 'instruction_type' );
		$wporg_wp_version = get_post_taxonomy_terms( $lesson_plan_id, 'wporg_wp_version' );
		$last_updated     = get_last_updated_time( $lesson_plan_id );

		$meta_fields = array(
			array(
				'label' => __( 'Duration', 'wporg-learn' ),
				'value' => $duration,
				'key'   => 'duration',
			),
			array(
				'label' => __( 'Audience', 'wporg-learn' ),
				'value' => $audience,
				'key'   => 'audience',
			),
			array(
				'label' => __( 'Level', 'wporg-learn' ),
				'value' => $level,
				'key'   => 'level',
			),
			array(
				'label' => __( 'Type', 'wporg-learn' ),
				'value' => $instruction_type,
				'key'   => 'type',
			),
			array(
				'label' => __( 'WordPress Version', 'wporg-learn' ),
				'value' => $wporg_wp_version,
				'key'   => 'type',
			),
			array(
				'label' => __( 'Last updated', 'wporg-learn' ),
				'value' => $last_updated,
				'key'   => 'last-updated',
			),
		);
	}

	foreach ( $meta_fields as $field ) {
		$list_items[] = sprintf(
			'<tr class="is-meta-%1$s is-style-short-text">
				<th scope="row">%2$s</th>
				<td>%3$s</td>
			</tr>',
			$field['key'],
			$field['label'],
			wp_kses_post( $field['value'] )
		);
	}

	$wrapper_attributes = get_block_wrapper_attributes();
	return sprintf(
		'<div %s><table>%s</table></div>',
		$wrapper_attributes,
		join( '', $list_items )
	);
}

/**
 * Get the last updated time for a post.
 *
 * @param int $post_id The ID of a post.
 *
 * @return string The last updated time.
 */
function get_last_updated_time( $post_id ) {
	$last_updated_time = get_post_modified_time( 'U', false, $post_id );
	$current_time = current_time( 'timestamp' );

	$time_diff = human_time_diff( $last_updated_time, $current_time );

	// If the time difference is greater than 30 days, display the specific date.
	if ( $current_time - $last_updated_time > 30 * DAY_IN_SECONDS ) {
		$last_updated = get_post_modified_time( 'M jS, Y', false, $post_id );
	} else {
		$last_updated = sprintf( '%s ago', $time_diff );
	}

	return $last_updated;
}

/**
 * Returns taxonomy terms with or without links.
 *
 * @param int    $post_id   Post ID.
 * @param string $tax       Taxonomy.
 * @param bool   $link      Whether to include term links.
 * @param string $post_type Post type.
 *
 * @return string Taxonomy terms with or without links.
 */
function get_post_taxonomy_terms( $post_id, $tax, $link = false, $post_type = '' ) {
	$terms     = get_the_terms( $post_id, $tax );
	$query_var = get_taxonomy( $tax )->query_var;

	$output = '';

	if ( ! is_wp_error( $terms ) && ! empty( $terms ) ) {
		foreach ( $terms as $term ) {
			$term_name = $term->name;

			if ( $link ) {
				$term_link = add_query_arg( array( $query_var => $term->slug ), home_url( "/$post_type/" ) );
				$output   .= ( $output ? ', ' : '' ) . '<a href="' . $term_link . '">' . esc_html( $term_name ) . '</a>';
			} else {
				$output .= ( $output ? ', ' : '' ) . esc_html( $term_name );
			}
		}
	}

	return $output;
}
