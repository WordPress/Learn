<?php

namespace WPOrg_Learn\View\Blocks\Course_Status;

use Sensei_Utils;

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

	$course_id    = $block->context['postId'];
	$user_id      = get_current_user_id();
	$is_completed = Sensei_Utils::user_completed_course( $course_id, $user_id );

	if ( $is_completed ) {
		$content = __( 'Completed', 'wporg-learn' );
	} elseif ( Sensei_Utils::has_started_course( $course_id, $user_id ) ) {
		$content = __( 'In progress', 'wporg-learn' );
	} else {
		return '';
	}

	$wrapper_attributes = get_block_wrapper_attributes();

	if ( $is_completed ) {
		$wrapper_attributes = str_replace( 'class="', 'class="is-completed ', $wrapper_attributes );
	}

	return sprintf(
		'<p %1$s>%2$s</p>',
		$wrapper_attributes,
		esc_html( $content )
	);
}
