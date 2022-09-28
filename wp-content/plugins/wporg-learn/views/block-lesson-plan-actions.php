<?php

namespace WPOrg_Learn\View\Blocks;

defined( 'WPINC' ) || die();

/**
 * @global WP_Post $post
 */

?>

<?php if ( get_post_type() === 'lesson-plan' ) : ?>
	<ul class="wp-block-wporg-learn-lesson-plan-actions">
		<?php if ( $post->slides_view_url ) : ?>
			<li>
				<a class="button button-xlarge button-secondary" href="<?php echo esc_attr( $post->slides_view_url ); ?>" target="_blank">
					<?php esc_html_e( 'View Slides', 'wporg-learn' ); ?>
				</a>
			</li>
		<?php endif; ?>
		<?php if ( $post->slides_download_url ) : ?>
			<li>
				<a class="button button-xlarge button-secondary" href="<?php echo esc_attr( $post->slides_download_url ); ?>">
					<?php esc_html_e( 'Download Slides', 'wporg-learn' ); ?>
				</a>
			</li>
		<?php endif; ?>
		<li>
			<button class="button button-xlarge button-secondary" onclick="window.print()">
				<?php esc_html_e( 'Print Lesson Plan', 'wporg-learn' ); ?>
			</button>
		</li>
	</ul>
<?php endif; ?>
