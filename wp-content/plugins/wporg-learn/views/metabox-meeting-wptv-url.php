<?php

namespace WPOrg_Learn\View\Metabox;

use WP_Post;

defined( 'WPINC' ) || die();

/** @var WP_Post $post */
?>

<p>
	<label for="meeting-wptv-url"><?php esc_html_e( 'WPTV URL', 'wporg_learn' ); ?></label>
	<input
		type="url"
		name="wptv-url"
		id="meeting-wptv-url"
		class="large-text"
		value="<?php echo esc_attr( $post->wptv_url ); ?>"
	/>
</p>

<?php wp_nonce_field( 'meeting-metaboxes', 'meeting-metabox-nonce' ); ?>
