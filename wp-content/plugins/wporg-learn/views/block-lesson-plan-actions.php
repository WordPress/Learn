<?php

namespace WPOrg_Learn\View\Blocks;

defined( 'WPINC' ) || die();

/**
 * @global WP_Post $post
 */

?>

<ul class="wp-block-wporg-learn-lesson-plan-actions">
	<?php if ( $post->slides_view_url ) : ?>
		<li>
			<a class="wp-block-wporg-learn-lesson-plan-action" href="<?php echo esc_attr( $post->slides_view_url ); ?>" target="_blank">
				<span class="dashicons dashicons-admin-page" aria-hidden="true"></span>
				<?php esc_html_e( 'View Lesson Plan Slides', 'wporg-learn' ); ?>
			</a>
		</li>
	<?php endif; ?>
	<?php if ( $post->slides_download_url ) : ?>
		<li>
			<a class="wp-block-wporg-learn-lesson-plan-action" href="<?php echo esc_attr( $post->slides_download_url ); ?>">
				<span class="dashicons dashicons-download" aria-hidden="true"></span>
				<?php esc_html_e( 'Download Lesson Slides', 'wporg-learn' ); ?>
			</a>
		</li>
	<?php endif; ?>
	<li>
		<button class="wp-block-wporg-learn-lesson-plan-action" onclick="window.print()">
			<span class="dashicons dashicons-printer" aria-hidden="true"></span>
			<?php esc_html_e( 'Print Lesson Plan', 'wporg-learn' ); ?>
		</button>
	</li>
</ul>
