<?php
namespace WordPressdotorg\Theme\Learn_2024\Upcoming_Online_Workshops;

use function WPOrg_Learn\Events\{get_discussion_events};

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
		dirname( dirname( __DIR__ ) ) . '/build/upcoming-online-workshops',
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
	$upcoming_online_workshops = get_discussion_events();

	if ( empty( $upcoming_online_workshops ) || is_wp_error( $upcoming_online_workshops ) ) {
		$content = '<!-- wp:pattern {"slug":"wporg-learn-2024/query-no-online-workshops"} /-->';
	} else {
		$content = '<!-- wp:group {"style":{"spacing":{"blockGap":"var:preset|spacing|50"}},"className":"is-style-cards-grid","layout":{"type":"grid","columnCount":null,"minimumColumnWidth":"330px"}} --><div class="wp-block-group is-style-cards-grid">';

		foreach ( $upcoming_online_workshops as $workshop ) {
			$timestamp = strtotime( $workshop['date_utc'] ) - (int) $workshop['date_utc_offset'];

			$content .= sprintf(
				'<!-- wp:wporg/link-wrapper -->
				<a class="wp-block-wporg-link-wrapper" href="%1$s">

					<!-- wp:group {"style":{"spacing":{"blockGap":"0"},"dimensions":{"minHeight":"100%%"}},"layout":{"type":"flex","orientation":"vertical","verticalAlignment":"space-between"}} -->
					<div class="wp-block-group" style="min-height:100%%">

						<!-- wp:heading {"level":3,"style":{"spacing":{"margin":{"top":"0","bottom":"var:preset|spacing|20"}},"typography":{"lineHeight":1.6}},"fontSize":"normal","layout":{"selfStretch":"fill","flexSize":null}} -->
						<h3 class="wp-block-heading has-normal-font-size" style="margin-top:0;margin-bottom:var(--wp--preset--spacing--20);line-height:1.6">%2$s</h3>
						<!-- /wp:heading -->

						<!-- wp:group {"style":{"spacing":{"blockGap":"0"}},"layout":{"type":"constrained"}} -->
						<div class="wp-block-group">
						
							<!-- wp:paragraph {"style":{"typography":{"fontStyle":"normal","fontWeight":"700"}},"className":"is-style-short-text","fontSize":"small"} -->
							<p class="is-style-short-text has-small-font-size" style="font-style:normal;font-weight:700">%3$s</p>
							<!-- /wp:paragraph -->
							
							<!-- wp:paragraph {"style":{"elements":{"link":{"color":{"text":"var:preset|color|charcoal-4"}}}},"textColor":"charcoal-4","className":"is-style-short-text","fontSize":"small"} -->
							<p class="is-style-short-text has-charcoal-4-color has-text-color has-link-color has-small-font-size" data-date-utc="%4$s"></p>
							<!-- /wp:paragraph -->

						</div>
						<!-- /wp:group -->

					</div>
					<!-- /wp:group -->

				</a>
				<!-- /wp:wporg/link-wrapper -->',
				esc_url( $workshop['url'] ),
				esc_html( $workshop['title'] ),
				esc_html( gmdate( 'l F j, Y', $timestamp ) ),
				esc_attr( gmdate( 'c', $timestamp ) ),
			);
		}

		$content .= '</div><!-- /wp:group -->';
	}

	$wrapper_attributes = get_block_wrapper_attributes();
	return sprintf(
		'<div %1$s>%2$s</div>',
		$wrapper_attributes,
		do_blocks( $content )
	);
}
