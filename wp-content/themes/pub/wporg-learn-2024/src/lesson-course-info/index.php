<?php
/**
 * Block Name: Lesson Course Info
 * Description: Displays information about the course a lesson belongs to.
 *
 * @package wporg
 */

namespace WordPressdotorg\Theme\Learn_2024\Lesson_Course_Info;

use Sensei_Utils;

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
		dirname( dirname( __DIR__ ) ) . '/build/lesson-course-info',
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
	if ( ! isset( $block->context['postId'] ) || ! isset( $block->context['postType'] ) || 'lesson' !== $block->context['postType'] ) {
		return '';
	}

	$course_id = get_post_meta( $block->context['postId'], '_lesson_course', true );

	if ( empty( $course_id ) ) {
		return '';
	}

	$course_title = get_the_title( $course_id );
	$course_permalink = get_permalink( $course_id );

	if ( empty( $course_title ) || ! $course_permalink ) {
		return '';
	}

	$wrapper_attributes = get_block_wrapper_attributes();
	return sprintf(
		'<p %s>%s</p>',
		$wrapper_attributes,
		wp_kses_post(
			sprintf(
				/* translators: 1: Course link, 2: Course title */
				__( 'Part of: <a href="%1$s">%2$s</a>', 'wporg-learn' ),
				get_permalink( $course_id ),
				get_the_title( $course_id ),
			)
		),
	);
}
