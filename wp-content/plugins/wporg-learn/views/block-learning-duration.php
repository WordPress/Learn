<?php

namespace WPOrg_Learn\View\Blocks\Learning_Duration;

use function WPOrg_Learn\Utils\ensure_float;

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

	if ( 'course' !== $post_type && 'lesson' !== $post_type ) {
		return '';
	}

	$duration = ensure_float( get_post_meta( $block->context['postId'], '_duration', true ) );

	if ( empty( $duration ) ) {
		return '';
	}

	if ( 1 === $duration ) {
		$content = __( '1 hour', 'wporg-learn' );
	} elseif ( $duration > 1 ) {
		$content = sprintf(
			/* translators: %s: duration in hours */
			__( '%s hours', 'wporg-learn' ),
			$duration
		);
	} else {
		// Display it in minutes.
		$minutes = round( $duration * 60 );
		$content = sprintf(
			/* translators: %s: duration in minutes */
			__( '%s minutes', 'wporg-learn' ),
			$minutes
		);
	}

	$wrapper_attributes = get_block_wrapper_attributes();
	return sprintf(
		'<p %1$s>%2$s</p>',
		$wrapper_attributes,
		esc_html( $content )
	);
}
