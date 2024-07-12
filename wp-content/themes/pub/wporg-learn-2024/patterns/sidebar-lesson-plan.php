<?php
/**
 * Title: Sidebar Lesson Plan
 * Slug: wporg-learn-2024/sidebar-lesson-plan
 * Inserter: no
 *
 * @package wporg-learn-2024
 */

$current_post = get_post();

?>

<!-- wp:group {"style":{"spacing":{"blockGap":"var:preset|spacing|40"}},"className":"wporg-learn-sidebar-meta-info","layout":{"type":"flex","orientation":"vertical","justifyContent":"stretch"}} -->
<div class="wp-block-group wporg-learn-sidebar-meta-info">

	<!-- wp:group {"layout":{"type":"constrained"}} -->
	<div class="wp-block-group">

		<?php if ( $current_post->slides_view_url || $current_post->slides_download_url ) : ?>
			<!-- wp:buttons {"style":{"spacing":{"blockGap":"0"}},"layout":{"type":"flex","justifyContent":"center"}} -->
			<div class="wp-block-buttons">
				<?php if ( $current_post->slides_view_url ) : ?>
					<!-- wp:button {"textAlign":"center","width":100,"style":{"border":{"radius":"2px"},"spacing":{"padding":{"left":"13px","right":"13px","top":"16px","bottom":"16px"}},"typography":{"lineHeight":0,"fontStyle":"normal","fontWeight":"400"}},"className":"aligncenter is-style-fill","fontSize":"normal","fontFamily":"inter"} -->
					<div class="wp-block-button has-custom-width wp-block-button__width-100 has-custom-font-size aligncenter is-style-fill has-inter-font-family has-normal-font-size" style="font-style:normal;font-weight:400;line-height:0">
						<a class="wp-block-button__link has-text-align-center wp-element-button" href="<?php echo esc_attr( $current_post->slides_view_url ); ?>" style="border-radius:2px;padding-top:16px;padding-right:13px;padding-bottom:16px;padding-left:13px" target="_blank" rel="noreferrer noopener">
							<?php esc_html_e( 'View slides ↗', 'wporg-learn' ); ?>
						</a>
					</div>
					<!-- /wp:button -->
				<?php endif; ?>
				<?php if ( $current_post->slides_view_url && $current_post->slides_download_url ) : ?>
					<!-- wp:button {"textAlign":"center","width":100,"style":{"border":{"radius":"2px"},"spacing":{"margin":{"bottom":"40px"},"padding":{"left":"13px","right":"13px","top":"16px","bottom":"16px"}},"typography":{"lineHeight":0,"fontStyle":"normal","fontWeight":"400"}},"className":"aligncenter is-style-text","fontSize":"normal","fontFamily":"inter"} -->
					<div class="wp-block-button has-custom-width wp-block-button__width-100 has-custom-font-size aligncenter is-style-text has-inter-font-family has-normal-font-size" style="font-style:normal;font-weight:400;line-height:0">
						<a class="wp-block-button__link has-text-align-center wp-element-button" href="<?php echo esc_attr( $current_post->slides_download_url ); ?>" style="border-radius:2px;padding-top:16px;padding-right:13px;padding-bottom:16px;padding-left:13px" target="_blank" rel="noreferrer noopener">
							<?php esc_html_e( 'Download slides ↗', 'wporg-learn' ); ?>
						</a>
					</div>
					<!-- /wp:button -->
				<?php elseif ( $current_post->slides_download_url ) : ?>
					<!-- wp:button {"textAlign":"center","width":100,"style":{"border":{"radius":"2px"},"spacing":{"padding":{"left":"13px","right":"13px","top":"16px","bottom":"16px"}},"typography":{"lineHeight":0,"fontStyle":"normal","fontWeight":"400"}},"className":"aligncenter is-style-fill","fontSize":"normal","fontFamily":"inter"} -->
					<div class="wp-block-button has-custom-width wp-block-button__width-100 has-custom-font-size aligncenter is-style-fill has-inter-font-family has-normal-font-size" style="font-style:normal;font-weight:400;line-height:0">
						<a class="wp-block-button__link has-text-align-center wp-element-button" href="<?php echo esc_attr( $current_post->slides_download_url ); ?>" style="border-radius:2px;padding-top:16px;padding-right:13px;padding-bottom:16px;padding-left:13px" target="_blank" rel="noreferrer noopener">
							<?php esc_html_e( 'Download slides ↗', 'wporg-learn' ); ?>
						</a>				
					</div>
					<!-- /wp:button -->
				<?php endif; ?>
			</div>
			<!-- /wp:buttons -->
			<!-- wp:buttons {"layout":{"type":"flex","justifyContent":"center"}} -->
			<div class="wp-block-buttons">
				<!-- wp:button {"textAlign":"center","width":100,"style":{"border":{"radius":"2px"},"spacing":{"padding":{"left":"13px","right":"13px","top":"16px","bottom":"16px"}},"typography":{"lineHeight":0,"fontStyle":"normal","fontWeight":"400"}},"className":"aligncenter is-style-outline","fontSize":"normal","fontFamily":"inter"} -->
				<div class="wp-block-button has-custom-width wp-block-button__width-100 has-custom-font-size aligncenter is-style-outline has-inter-font-family has-normal-font-size" style="font-style:normal;font-weight:400;line-height:0">
					<a class="wp-block-button__link has-text-align-center wp-element-button" onclick="window.print()" style="border-radius:2px;padding-top:16px;padding-right:13px;padding-bottom:16px;padding-left:13px" target="_blank" rel="noreferrer noopener">
						<?php esc_html_e( 'Print Lesson Plan', 'wporg-learn' ); ?>
					</a>
				</div>
				<!-- /wp:button -->
			</div>
			<!-- /wp:buttons -->
		<?php else : ?>
			<!-- wp:buttons {"layout":{"type":"flex","justifyContent":"center"}} -->
			<div class="wp-block-buttons">
				<!-- wp:button {"textAlign":"center","width":100,"style":{"border":{"radius":"2px"},"spacing":{"padding":{"left":"13px","right":"13px","top":"16px","bottom":"16px"}},"typography":{"lineHeight":0,"fontStyle":"normal","fontWeight":"400"}},"className":"aligncenter is-style-fill","fontSize":"normal","fontFamily":"inter"} -->
				<div class="wp-block-button has-custom-width wp-block-button__width-100 has-custom-font-size aligncenter is-style-fill has-inter-font-family has-normal-font-size" style="font-style:normal;font-weight:400;line-height:0">
					<a class="wp-block-button__link has-text-align-center wp-element-button" onclick="window.print()" style="border-radius:2px;padding-top:16px;padding-right:13px;padding-bottom:16px;padding-left:13px" target="_blank" rel="noreferrer noopener">
						<?php esc_html_e( 'Print Lesson Plan', 'wporg-learn' ); ?>
					</a>
				</div>
				<!-- /wp:button -->
			</div>
			<!-- /wp:buttons -->
		<?php endif; ?>

		<!-- wp:buttons {"layout":{"type":"flex","justifyContent":"center"}} -->
		<div class="wp-block-buttons">
			<!-- wp:button {"textAlign":"center","width":100,"style":{"border":{"radius":"2px"},"spacing":{"padding":{"left":"13px","right":"13px","top":"16px","bottom":"16px"}},"typography":{"lineHeight":0,"fontStyle":"normal","fontWeight":"400"}},"className":"aligncenter is-style-outline","fontSize":"normal","fontFamily":"inter"} -->
			<div class="wp-block-button has-custom-width wp-block-button__width-100 has-custom-font-size aligncenter is-style-outline has-inter-font-family has-normal-font-size" style="font-style:normal;font-weight:400;line-height:0">
				<a class="wp-block-button__link has-text-align-center wp-element-button" href="https://wordpress.org/playground/demo/?step=playground&amp;theme=twentytwentythree" style="border-radius:2px;padding-top:16px;padding-right:13px;padding-bottom:16px;padding-left:13px" target="_blank" rel="noreferrer noopener">
					<?php esc_html_e( 'Practice on a private demo site', 'wporg-learn' ); ?>
				</a>
			</div>
			<!-- /wp:button -->
		</div>
		<!-- /wp:buttons -->

	</div>
	<!-- /wp:group -->

	<!-- wp:pattern {"slug":"wporg-learn-2024/sidebar-details"} /-->

</div>
<!-- /wp:group -->
