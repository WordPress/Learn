<?php

namespace WPOrg_Learn\View\Blocks\Lesson_Count;

defined( 'WPINC' ) || die();


/**
 * Render the block output.
 *
 * @param array    $attributes The block attributes.
 * @param string   $content The block content.
 * @param WP_Block $block The block object.
 * @return string The rendered output.
 */
function render( $attributes, $content, $block ) {
	$post_type = $block->context['postType'];

	if ( 'course' !== $post_type || ! class_exists( 'Sensei_Main' ) ) {
		return '';
	}

	$lessons = Sensei()->course->course_lessons( $block->context['postId'] );

	if ( empty( $lessons ) ) {
		return '';
	}

	$lesson_count = count( $lessons );

	$content = sprintf(
		/* translators: %s: The number of lessons in the course. */
		_n( '%s lesson', '%s lessons', $lesson_count, 'wporg-learn' ),
		esc_html( $lesson_count )
	);

	$wrapper_attributes = get_block_wrapper_attributes();
	return sprintf(
		'<p %1$s>%2$s</p>',
		$wrapper_attributes,
		esc_html( $content )
	);
}
