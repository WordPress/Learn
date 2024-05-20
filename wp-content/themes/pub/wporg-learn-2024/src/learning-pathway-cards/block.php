<?php
namespace WordPressdotorg\Theme\Learn_2024\Learning_Pathway_Cards;

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
		dirname( dirname( __DIR__ ) ) . '/build/learning-pathway-cards',
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
	$learning_pathways = get_terms(
		array(
			'taxonomy'   => 'learning-pathway',
			'hide_empty' => false,
		)
	);
	$is_mini           = isset( $attributes['isMini'] ) && $attributes['isMini'];

	if ( empty( $learning_pathways ) || is_wp_error( $learning_pathways ) ) {
		return __( 'No learning pathways found.', 'wporg-learn' );
	}

	$content = '<!-- wp:group {"style":{"spacing":{"blockGap":"var:preset|spacing|40"}},"className":"is-style-cards-grid","layout":{"type":"grid","columnCount":"2","minimumColumnWidth":null}} --><div class="wp-block-group is-style-cards-grid">';

	foreach ( $learning_pathways as $learning_pathway ) {
		$content .= $is_mini ? render_mini_card( $learning_pathway ) : render_full_card( $learning_pathway );
	}

	$content .= '</div><!-- /wp:group -->';

	$wrapper_attributes = get_block_wrapper_attributes();
	return sprintf(
		'<div %1$s>%2$s</div>',
		$wrapper_attributes,
		do_blocks( $content )
	);
}

/**
 * Render the full card for a learning pathway.
 *
 * @param object $learning_pathway The learning pathway object.
 * @return string Returns the full card markup.
 */
function render_full_card( $learning_pathway ) {
	return $learning_pathway->name;
}

/**
 * Render the mini card for a learning pathway.
 *
 * @param object $learning_pathway The learning pathway object.
 * @return string Returns the mini card markup.
 */
function render_mini_card( $learning_pathway ) {
	return sprintf(
		'<!-- wp:wporg/link-wrapper {"style":{"spacing":{"padding":{"top":"0","bottom":"0","left":"0","right":"0"}}}} -->
		<a class="wp-block-wporg-link-wrapper" href="%1$s" style="padding-top:0;padding-right:0;padding-bottom:0;padding-left:0">

			<!-- wp:group {"style":{"spacing":{"blockGap":"0"},"dimensions":{"minHeight":"100%%"}},"layout":{"type":"flex","flexWrap":"nowrap","verticalAlignment":"stretch","justifyContent":"space-between"}} -->
			<div class="wp-block-group" style="min-height:100%%">
			
				<!-- wp:group {"style":{"spacing":{"padding":{"top":"var:preset|spacing|20","bottom":"var:preset|spacing|20","left":"20px","right":"20px"},"blockGap":"10px"},"layout":{"selfStretch":"fixed","flexSize":"55%%"},"border":{"right":{"color":"var:preset|color|light-grey-1","width":"1px"},"top":{},"bottom":{},"left":{}}},"layout":{"type":"flex","orientation":"vertical"}} -->
				<div class="wp-block-group" style="border-right-color:var(--wp--preset--color--light-grey-1);border-right-width:1px;padding-top:var(--wp--preset--spacing--20);padding-right:20px;padding-bottom:var(--wp--preset--spacing--20);padding-left:20px">
				
					<!-- wp:heading {"level":3,"style":{"spacing":{"margin":{"top":"0"}}},"fontSize":"small"} -->
					<h3 class="wp-block-heading has-small-font-size" style="margin-top:0">%2$s</h3>
					<!-- /wp:heading -->

					<!-- wp:paragraph {"fontSize":"small"} -->
					<p class="has-small-font-size">%3$s</p>
					<!-- /wp:paragraph -->
				
				</div>
				<!-- /wp:group -->

				<!-- wp:group {"style":{"background":{"backgroundImage":{"url":"%4$s","source":"file"},"backgroundPosition":"50%% 50%%"},"layout":{"selfStretch":"fixed","flexSize":"45%%"},"dimensions":{"minHeight":"100%%"}},"layout":{"type":"default"}} -->
				<div class="wp-block-group" style="min-height:100%%" aria-hidden="true"></div>
				<!-- /wp:group -->
			
			</div>
			<!-- /wp:group -->
			
		</a>
		<!-- /wp:wporg/link-wrapper -->',
		esc_url( get_term_link( $learning_pathway ) ),
		esc_html( $learning_pathway->name ),
		esc_html( $learning_pathway->description ),
		esc_url( get_stylesheet_directory_uri() . '/assets/learning-pathway-' . $learning_pathway->slug . '.svg' ),
	);
}
