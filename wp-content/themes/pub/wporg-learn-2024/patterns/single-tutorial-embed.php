<?php
/**
 * Title: Single Tutorial Embed
 * Slug: wporg-learn-2024/single-tutorial-embed
 * Inserter: no
 */

global $wp_embed;
global $post;

/*
 * A registered pattern can be rendered outside the template it was written for, and this file
 * reads post meta off the global `$post`. That meta is registered per post type, so only render
 * for the type this pattern is designed around.
 */
if ( ! $post instanceof WP_Post || 'wporg_workshop' !== get_post_type( $post ) ) {
	return;
}

// `autoembed()` returns its input unchanged when no provider matches, and the result is echoed
// unescaped below to keep the provider markup intact, so make sure it is a URL first.
$video_url = esc_url_raw( (string) $post->video_url );

if ( ! $video_url ) {
	return;
}

?>

<!-- wp:embed {"url":"<?php echo esc_url( $video_url ); ?>","type":"video","providerNameSlug":"wordpress-tv","responsive":true,"className":"wp-embed-aspect-16-9 wp-has-aspect-ratio","autoembed":true} -->
<figure class="wp-block-embed is-type-video is-provider-wordpress-tv wp-block-embed-wordpress-tv wp-embed-aspect-16-9 wp-has-aspect-ratio">

	<div class="wp-block-embed__wrapper">
		<?php echo $wp_embed->autoembed( $video_url ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
	</div>

</figure>
<!-- /wp:embed -->
