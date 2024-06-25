<?php
/**
 * Title: Single Tutorial Embed
 * Slug: wporg-learn-2024/single-tutorial-embed
 * Inserter: no
 */

global $wp_embed;
global $post;

if ( ! isset( $post->video_url ) ) {
	return;
}

?>

<!-- wp:embed {"url":"<?php echo esc_url( $post->video_url ); ?>","type":"video","providerNameSlug":"wordpress-tv","responsive":true,"className":"wp-embed-aspect-16-9 wp-has-aspect-ratio","autoembed":true} -->
<figure class="wp-block-embed is-type-video is-provider-wordpress-tv wp-block-embed-wordpress-tv wp-embed-aspect-16-9 wp-has-aspect-ratio">

	<div class="wp-block-embed__wrapper">
		<?php echo $wp_embed->autoembed( $post->video_url ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
	</div>

</figure>
<!-- /wp:embed -->
