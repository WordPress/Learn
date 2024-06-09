<?php
/**
 * Block Name: Sensei Meta List
 * Description: Display the site meta data of a learn or course as a list.
 *
 * @package wporg
 */

namespace WordPressdotorg\Theme\Learn_2024\Sensei_Meta_List;

use Sensei_Utils;
use Sensei_Reports_Overview_Service_Courses;

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
		dirname( dirname( __DIR__ ) ) . '/build/sensei-meta-list',
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

	if ( get_post_type() === 'course' ) {
		$course_service = new Sensei_Reports_Overview_Service_Courses();
		$course_id = $block->context['postId'];

		// Get the total number of learners enrolled in the course
		$learners = Sensei_Utils::sensei_check_for_activity(
			array(
				'type'     => 'sensei_course_status',
				'status'   => 'in-progress',
				'post__in' => $course_id,
			)
		);

		// Get the average grade across all learners
		$average_grade = round( $course_service->get_courses_average_grade( array( $course_id ) ), 0 );

		// Get the average number of days it takes to complete a course
		$average_days = $course_service->get_average_days_to_completion( array( $course_id ) );

		// Set up array of data to be used
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
		);
	}

	foreach ( $meta_fields as $field ) {
		$list_items[] = sprintf(
			'<tr class="is-meta-%1$s">
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
