<?php

namespace WPOrg_Learn\View\Metabox;

use WP_Post;

defined( 'WPINC' ) || die();

/** @var WP_Post $post */
?>

<p>
	<label for="lesson-plan-slides-view-url"><?php esc_html_e( 'View URL', 'wporg_learn' ); ?></label>
	<input
		type="url"
		name="slides-view-url"
		id="lesson-plan-slides-view-url"
		class="large-text"
		value="<?php echo esc_attr( $post->slides_view_url ); ?>"
	/>
</p>

<p>
	<label for="lesson-plan-slides-download-url"><?php esc_html_e( 'Download URL', 'wporg_learn' ); ?></label>
	<input
		type="url"
		name="slides-download-url"
		id="lesson-plan-slides-download-url"
		class="large-text"
		value="<?php echo esc_attr( $post->slides_download_url ); ?>"
	/>
</p>

<?php wp_nonce_field( 'lesson-plan-metaboxes', 'lesson-plan-metabox-nonce' ); ?>
