<?php

use function WordPressdotorg\Locales\get_locale_name_from_code;
use function WPOrg_Learn\Post_Meta\get_workshop_duration;

$topics = wp_get_post_terms( $post->ID, 'topic', array( 'fields' => 'names' ) );
$topics_string = implode( ', ', array_map( 'esc_html', $topics ) );
$languages = esc_html( get_locale_name_from_code( $post->video_language, 'native' ) );
$duration = get_workshop_duration( $post, 'string' );

$post_tags = array(
	'category' => $topics_string,
	'clock' => $duration,
	'admin-site-alt3' => $languages,
)

?>
<footer>
<?php foreach ( $post_tags as $post_tag => $value ) : ?>
	<?php if ( ! empty( $value ) ) : ?>
		<div class="footer_item">
			<i class="dashicons dashicons-<?php echo esc_attr( $post_tag ); ?>"></i>
			<span><?php echo esc_attr( $value ); ?></span>
		</div>
	<?php endif; ?>
<?php endforeach; ?>
</footer>
