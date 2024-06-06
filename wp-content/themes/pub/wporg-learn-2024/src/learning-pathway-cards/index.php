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
			'hide_empty' => true,
		)
	);
	$is_mini           = isset( $attributes['isMini'] ) && $attributes['isMini'];

	if ( empty( $learning_pathways ) || is_wp_error( $learning_pathways ) ) {
		$content = __( 'No learning pathways found.', 'wporg-learn' );
	} else {
		$content = '<!-- wp:group {"style":{"spacing":{"blockGap":"var:preset|spacing|50"}},"className":"is-style-cards-grid","layout":{"type":"grid","columnCount":null,"minimumColumnWidth":"350px"}} --><div class="wp-block-group is-style-cards-grid">';

		foreach ( $learning_pathways as $learning_pathway ) {
			$content .= $is_mini ? render_mini_card( $learning_pathway ) : render_full_card( $learning_pathway );
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

/**
 * Render the full card for a learning pathway.
 *
 * @param object $learning_pathway The learning pathway object.
 * @return string Returns the full card markup.
 */
function render_full_card( $learning_pathway ) {
	$count = $learning_pathway->count;

	return sprintf(
		'<!-- wp:group {"style":{"spacing":{"padding":{"top":"0","bottom":"0","left":"0","right":"0"},"blockGap":"0"}},"className":"wporg-learn-learning-pathway-card-full","layout":{"type":"flex","orientation":"vertical","flexWrap":"nowrap","justifyContent":"stretch"}} -->
		<div class="wp-block-group wporg-learn-learning-pathway-card-full" style="padding-top:0;padding-right:0;padding-bottom:0;padding-left:0">

			<!-- wp:group {"className":"wporg-learn-learning-pathway-card-header","style":{"backgroundColor":"%1$s","border":{"bottom":{"color":"var:preset|color|light-grey-1","width":"1px"}},"spacing":{"blockGap":"0"}},"layout":{"type":"flex","flexWrap":"nowrap","justifyContent":"space-between","verticalAlignment":"stretch"}} -->
			<div class="wp-block-group wporg-learn-learning-pathway-card-header has-%1$s-background-color has-background" style="border-bottom-color:var(--wp--preset--color--light-grey-1);border-bottom-width:1px;background-color:var(--wp--custom--color--%1$s)">

				<!-- wp:group {"style":{"spacing":{"padding":{"top":"var:preset|spacing|30","bottom":"var:preset|spacing|30","left":"30px","right":"0"},"blockGap":"0"},"className":"wporg-learn-learning-pathway-card-header-content","layout":{"selfStretch":"fixed","flexSize":"50%%"}},"layout":{"type":"flex","orientation":"vertical","justifyContent":"stretch"}} -->
				<div class="wp-block-group wporg-learn-learning-pathway-card-header-content" style="padding-top:var(--wp--preset--spacing--30);padding-right:0;padding-bottom:var(--wp--preset--spacing--30);padding-left:30px">

					<!-- wp:heading {"style":{"spacing":{"margin":{"top":"0"}},"elements":{"link":{"color":{"text":"var:preset|color|charcoal-1"}}},"typography":{"fontStyle":"normal","fontWeight":"400","lineHeight":"1.3"}},"textColor":"charcoal-1","fontSize":"huge","fontFamily":"eb-garamond"} -->
					<h2 class="wp-block-heading has-charcoal-1-color has-text-color has-link-color has-eb-garamond-font-family has-huge-font-size" style="margin-top:0;font-style:normal;font-weight:400;line-height:1.3">%2$s</h2>
					<!-- /wp:heading -->

					<!-- wp:paragraph {"textColor":"charcoal-1","className":"is-style-short-text","fontSize":"small"} -->
					<p class="is-style-short-text has-charcoal-1-color has-text-color has-small-font-size">%3$s</p>
					<!-- /wp:paragraph -->

				</div>
				<!-- /wp:group -->

				<!-- wp:group {"style":{"layout":{"selfStretch":"fixed","flexSize":"50%%"},"background":{"backgroundImage":{"url":"%4$s","id":184,"source":"file"},"backgroundPosition":"0%% 50%%"}},"layout":{"type":"constrained"}} -->
				<div class="wp-block-group" aria-hidden="true"></div>
				<!-- /wp:group -->

			</div>
			<!-- /wp:group -->

			<!-- wp:query {"queryId":15,"query":{"perPage":5,"pages":0,"offset":0,"postType":"course","order":"desc","orderBy":"date","author":"","search":"","exclude":[],"sticky":"","inherit":false,"taxQuery":{"learning-pathway":[%5$s]},"parents":[]}} -->
			<div class="wp-block-query">

				<!-- wp:group {"style":{"spacing":{"blockGap":"0"}},"layout":{"type":"flex","orientation":"vertical","justifyContent":"stretch","verticalAlignment":"space-between"}} -->
				<div class="wp-block-group">
				
					<!-- wp:group {"style":{"spacing":{"padding":{"top":"var:preset|spacing|20","bottom":"var:preset|spacing|20","left":"30px","right":"30px"}},"border":{"bottom":{"color":"var:preset|color|light-grey-1","width":"1px"},"top":{},"right":{},"left":{}}},"layout":{"type":"default"}} -->
					<div class="wp-block-group" style="border-bottom-color:var(--wp--preset--color--light-grey-1);border-bottom-width:1px;padding-top:var(--wp--preset--spacing--20);padding-right:30px;padding-bottom:var(--wp--preset--spacing--20);padding-left:30px">
					
						<!-- wp:post-template -->

							<!-- wp:post-title {"level":3,"isLink":true,"style":{"typography":{"fontStyle":"normal","fontWeight":"400"},"spacing":{"margin":{"bottom":"0"}},"elements":{"link":{"color":{"text":"var:preset|color|charcoal-1"}}}},"fontSize":"normal","fontFamily":"inter"} /-->

						<!-- /wp:post-template -->
					
					</div>
					<!-- /wp:group -->
					
					<!-- wp:paragraph {"align":"right","style":{"spacing":{"padding":{"top":"var:preset|spacing|10","bottom":"var:preset|spacing|10","left":"30px","right":"30px"}},"elements":{"link":{"color":{"text":"var:preset|color|charcoal-1"}}}},"textColor":"charcoal-1","fontSize":"small"} -->
					<p class="has-text-align-right has-charcoal-1-color has-text-color has-link-color has-small-font-size" style="padding-top:var(--wp--preset--spacing--10);padding-right:30px;padding-bottom:var(--wp--preset--spacing--10);padding-left:30px"><a href="%6$s">%7$s</a></p>
					<!-- /wp:paragraph -->
				
				</div>
				<!-- /wp:group -->

			</div>
			<!-- /wp:query -->

		</div>
		<!-- /wp:group -->',
		esc_attr( $learning_pathway->slug ),
		esc_html( $learning_pathway->name ),
		esc_html( $learning_pathway->description ),
		esc_url( get_stylesheet_directory_uri() . '/assets/learning-pathway-' . $learning_pathway->slug . '.svg' ),
		esc_html( $learning_pathway->term_id ),
		esc_url( get_term_link( $learning_pathway ) ),
		$count > 1
			? sprintf(
				/* translators: %s: Learning Pathway course count  */
				__( 'See all %s courses</a>', 'wporg-learn' ),
				esc_html( $count ),
			)
			: __( 'See course', 'wporg-learn' ),
	);
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
			
				<!-- wp:group {"style":{"spacing":{"padding":{"top":"var:preset|spacing|30","bottom":"var:preset|spacing|30","left":"30px","right":"30px"},"blockGap":"10px"},"layout":{"selfStretch":"fixed","flexSize":"55%%"},"border":{"right":{"color":"var:preset|color|light-grey-1","width":"1px"},"top":{},"bottom":{},"left":{}}},"layout":{"type":"flex","orientation":"vertical"}} -->
				<div class="wp-block-group" style="border-right-color:var(--wp--preset--color--light-grey-1);border-right-width:1px;padding-top:var(--wp--preset--spacing--30);padding-right:30px;padding-bottom:var(--wp--preset--spacing--30);padding-left:30px">
				
					<!-- wp:heading {"level":3,"style":{"spacing":{"margin":{"top":"0"}},"elements":{"link":{"color":{"text":"var:preset|color|charcoal-1"}}}},"textColor":"charcoal-1","fontSize":"small"} -->
					<h3 class="wp-block-heading has-charcoal-1-color has-text-color has-link-color has-small-font-size" style="margin-top:0">%2$s</h3>
					<!-- /wp:heading -->

					<!-- wp:paragraph {"style":{"elements":{"link":{"color":{"text":"var:preset|color|charcoal-4"}}}},"textColor":"charcoal-4","fontSize":"small"} -->
					<p class="has-charcoal-4-color has-text-color has-link-color has-small-font-size">%3$s</p>
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
