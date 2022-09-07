<?php

namespace WPOrg_Learn\View\Blocks;

defined( 'WPINC' ) || die();

/**
 * @global WP_Post $post
 * @var    array   $details
 */

?>

<div class="wp-block-wporg-learn-lesson-plan-details">
	<ul class="wp-block-wporg-learn-lesson-plan-meta">
		<?php
		foreach ( $details as $detail ) {
			if ( ! empty( $detail['value'] ) ) { ?>
				<li>
					<span class="dashicons dashicons-<?php echo esc_attr( $detail['icon'] ); ?>"></span>
					<span><?php echo esc_html( $detail['label'] ); ?></span>
					<strong>
						<span>
							<?php
							$i = 0;
							foreach ( $detail['value'] as $key => $value ) {
								$url = trailingslashit( site_url() ) . 'lesson-plans/?' . $detail['slug'] . '[]=' . $key;
								
								if ( 0 < $i ) {
									echo ', ';
								}
								
								echo '<a href="' . esc_attr( $url ) . '">' . esc_html( $value ) . '</a>';
								$i++;
							}
							?>
						</span>
					</strong>
				</li>
				<?php
			}
		}
		?>
	</ul>
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
</div>
