<?php
namespace WordPressdotorg\Theme\Learn_2024\Learning_Pathway_Header;

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
		dirname( dirname( __DIR__ ) ) . '/build/learning-pathway-header',
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
	$learning_pathway_slug = $attributes['learningPathwaySlug'];

	if ( ! $learning_pathway_slug ) {
		return '';
	}

	global $wp_query;

	// If this is the learning pathway taxonomy archive page, we can use the queried object.
	// On a different taxonomy archive page, we need to fetch the term by slug.
	if ( isset( $wp_query->queried_object->slug ) && $wp_query->queried_object->slug === $learning_pathway_slug ) {
		$learning_pathway_object = $wp_query->queried_object;
	} else {
		$learning_pathway_object = get_term_by( 'slug', $learning_pathway_slug, 'learning-pathway' );
	}

	if ( ! $learning_pathway_object ) {
		return '';
	}

	$content = '<!-- wp:group {"className":"wp-block-wporg-learn-learning-pathway-header-content","align":"full","style":{"spacing":{"padding":{"right":"var:preset|spacing|edge-space","left":"var:preset|spacing|edge-space","top":"0","bottom":"0"}},"border":{"bottom":{"color":"var:preset|color|light-grey-1","width":"1px"}}},"backgroundColor":"' . esc_attr( $learning_pathway_slug ) . '","layout":{"type":"constrained"}} -->
	<div class="wp-block-wporg-learn-learning-pathway-header-content wp-block-group alignfull has-' . esc_attr( $learning_pathway_slug ) . '-background-color has-background" style="border-bottom-color:var(--wp--preset--color--light-grey-1);border-bottom-width:1px;padding-top:0;padding-right:var(--wp--preset--spacing--edge-space);padding-bottom:0;padding-left:var(--wp--preset--spacing--edge-space)">

		<!-- wp:group {"style":{"spacing":{"blockGap":"0"},"background":{"backgroundRepeat":"no-repeat","backgroundSize":"contain","backgroundPosition":"100% 50%"}},"layout":{"type":"flex","flexWrap":"nowrap","justifyContent":"space-between","verticalAlignment":"stretch"}} -->
		<div class="wp-block-group">
			
			<!-- wp:group {"style":{"spacing":{"padding":{"top":"var:preset|spacing|60","bottom":"var:preset|spacing|60"}},"layout":{"selfStretch":"fill","flexSize":null}},"layout":{"type":"constrained"}} -->
			<div class="wp-block-group" style="padding-top:var(--wp--preset--spacing--60);padding-bottom:var(--wp--preset--spacing--60)">

				<!-- wp:query-title {"type":"archive","showPrefix":false} /-->

				<!-- wp:paragraph {"style":{"typography":{"lineHeight":1.6}}} -->
				<p style="line-height:1.6">' . esc_html( $learning_pathway_object->description ) . '</p>
				<!-- /wp:paragraph -->

			</div>
			<!-- /wp:group -->

			<!-- wp:group {"style":{"layout":{"selfStretch":"fixed","flexSize":"33%"},"background":{"backgroundImage":{"url":"' . esc_url( get_stylesheet_directory_uri() . '/assets/learning-pathway-' . $learning_pathway_slug . '.svg' ) . '","source":"file"},"backgroundPosition":"0% 50%"}},"layout":{"type":"constrained"}} -->
			<div class="wp-block-group"></div>
			<!-- /wp:group -->

		</div>
		<!-- /wp:group -->

	</div>
	<!-- /wp:group -->
	
	<!-- wp:group {"align":"full","style":{"spacing":{"padding":{"left":"var:preset|spacing|edge-space","right":"var:preset|spacing|edge-space"}}},"layout":{"type":"constrained"}} -->
	<div class="wp-block-group alignfull" style="padding-right:var(--wp--preset--spacing--edge-space);padding-left:var(--wp--preset--spacing--edge-space)">

		<!-- wp:group {"align":"wide","layout":{"type":"flex","flexWrap":"wrap","justifyContent":"space-between"},"style":{"spacing":{"margin":{"top":"var:preset|spacing|40","bottom":"var:preset|spacing|40"}}}} -->
		<div class="wp-block-group alignwide" style="margin-top:var(--wp--preset--spacing--40);margin-bottom:var(--wp--preset--spacing--40)">

			<!-- wp:search {"label":"' . __( 'Search', 'wporg-learn' ) . '","showLabel":false,"placeholder":"' . __( 'Search', 'wporg-learn' ) . '","width":290,"widthUnit":"px","buttonText":"' . __( 'Search', 'wporg-learn' ) . '","buttonPosition":"button-inside","buttonUseIcon":true,"query":{"wporg_learning_pathway":"' . esc_attr( $learning_pathway_slug ) . '"}} /-->

			<!-- wp:group {"style":{"spacing":{"blockGap":"0"}},"layout":{"type":"flex","flexWrap":"nowrap"},"className":"wporg-query-filters"} -->
			<div class="wp-block-group wporg-query-filters">
				<!-- wp:wporg/query-filter {"key":"learning-pathway-topic"} /-->
				<!-- wp:wporg/query-filter {"key":"learning-pathway-level","multiple":false} /-->
			</div>
			<!-- /wp:group -->

		</div>
		<!-- /wp:group -->
		
	</div>
	<!-- /wp:group -->';

	$wrapper_attributes = get_block_wrapper_attributes();
	return sprintf(
		'<div %1$s>%2$s</div>',
		$wrapper_attributes,
		do_blocks( $content )
	);
}
