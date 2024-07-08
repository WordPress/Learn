<?php
/**
 * Title: Sidebar Meta Info
 * Slug: wporg-learn-2024/sidebar-meta-info
 * Inserter: no
 *
 * @package wporg-learn-2024
 */

use function WPOrg_Learn\Sensei\{get_my_courses_page_url};

$completed_course  = Sensei_Utils::user_completed_course( get_the_ID() );
$current_post      = get_post();
$current_post_type = get_post_type();

?>

<!-- wp:group {"align":"full","className":"wporg-learn-sidebar-meta-info","layout":{"type":"constrained"}} -->
<div class="wp-block-group alignfull wporg-learn-sidebar-meta-info">

	<?php if ( 'course' === $current_post_type ) : ?>
		<!-- wp:sensei-lms/course-progress {<?php echo ( $completed_course ? '"customTextColor":"var(--wp--custom--color--green-50)",' : '' ); ?>"customBarColor":"var(--wp--custom--color--green-50)","height":10,"className":"wporg-learn-sidebar-course-progress"} /-->

		<?php if ( Sensei_Course::is_user_enrolled( get_the_ID() ) ) : ?>
		<!-- wp:paragraph {"style":{"spacing":{"margin":{"bottom":"40px","top":"10px"}},"elements":{"link":{"color":{"text":"var:preset|color|blueberry-1"}}},"typography":{"fontStyle":"normal","fontWeight":"400","lineHeight":26px}},"textColor":"blueberry-1","fontSize":"normal","fontFamily":"inter","className":""wporg-learn-sidebar-all-courses"} -->
		<p class="has-blueberry-1-color has-text-color has-link-color has-inter-font-family has-normal-font-size wporg-learn-sidebar-all-courses" style="font-style:normal;font-weight:400;line-height:26px;margin-top:10px;margin-bottom:40px">
			<a href="<?php echo esc_url( get_my_courses_page_url() ); ?>">
				<?php esc_html_e( 'All My Courses', 'wporg-learn' ); ?>
			</a>
		</p>
		<!-- /wp:paragraph -->
		<?php endif; ?>

		<!-- wp:sensei-lms/button-take-course {"align":"full","borderRadius":2,"className":"is-style-default"} -->
		<div class="wp-block-sensei-lms-button-take-course is-style-default wp-block-sensei-button wp-block-button has-text-align-full">
			<button class="wp-block-button__link" style="border-radius:2px">
				<?php esc_html_e( 'Take this Course', 'wporg-learn' ); ?>
			</button>
		</div>
		<!-- /wp:sensei-lms/button-take-course -->
		<?php endif; ?>

	<?php if ( 'lesson-plan' === $current_post_type ) : ?>
		<?php if ( $current_post->slides_view_url || $current_post->slides_download_url ) : ?>
			<!-- wp:buttons {"style":{"spacing":{"blockGap":"0","margin":{"bottom":"40px"}}},"layout":{"type":"flex","justifyContent":"center"}} -->
			<div class="wp-block-buttons" style="margin-bottom:40px">
				<?php if ( $current_post->slides_view_url ) : ?>
					<!-- wp:button {"textAlign":"center","width":100,"style":{"border":{"radius":"2px"},"spacing":{"padding":{"left":"13px","right":"13px","top":"16px","bottom":"16px"}},"typography":{"lineHeight":0,"fontStyle":"normal","fontWeight":"400"}},"className":"aligncenter is-style-text","fontSize":"normal","fontFamily":"inter"} -->
					<div class="wp-block-button has-custom-width wp-block-button__width-100 has-custom-font-size aligncenter is-style-text has-inter-font-family has-normal-font-size" style="font-style:normal;font-weight:400;line-height:0">
						<a class="wp-block-button__link has-text-align-center wp-element-button" href="<?php echo esc_attr( $current_post->slides_view_url ); ?>" style="border-radius:2px;padding-top:16px;padding-right:13px;padding-bottom:16px;padding-left:13px" target="_blank" rel="noreferrer noopener">
							<?php esc_html_e( 'View slides', 'wporg-learn' ); ?>
							<span aria-hidden="true" class="wp-exclude-emoji">↗</span>
						</a>				
					</div>
					<!-- /wp:button -->
				<?php endif; ?>
				<?php if ( $current_post->slides_download_url ) : ?>
					<!-- wp:button {"textAlign":"center","width":100,"style":{"border":{"radius":"2px"},"spacing":{"padding":{"left":"13px","right":"13px","top":"16px","bottom":"16px"}},"typography":{"lineHeight":0,"fontStyle":"normal","fontWeight":"400"}},"className":"aligncenter is-style-text","fontSize":"normal","fontFamily":"inter"} -->
					<div class="wp-block-button has-custom-width wp-block-button__width-100 has-custom-font-size aligncenter is-style-text has-inter-font-family has-normal-font-size" style="font-style:normal;font-weight:400;line-height:0">
						<a class="wp-block-button__link has-text-align-center wp-element-button" href="<?php echo esc_attr( $current_post->slides_download_url ); ?>" style="border-radius:2px;padding-top:16px;padding-right:13px;padding-bottom:16px;padding-left:13px" target="_blank" rel="noreferrer noopener">
							<?php esc_html_e( 'Download slides', 'wporg-learn' ); ?>
							<span aria-hidden="true" class="wp-exclude-emoji">↗</span>
						</a>				
					</div>
					<!-- /wp:button -->
				<?php endif; ?>
			</div>
			<!-- /wp:buttons -->
		<?php endif; ?>
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

	<!-- wp:wporg-learn/sensei-meta-list {"type":"course","fontSize":"normal"} /-->

	<!-- wp:paragraph {"style":{"typography":{"fontStyle":"normal","fontWeight":"400","lineHeight":"34px"}},"fontSize":"large","fontFamily":"inter"} -->
	<p class="has-inter-font-family has-large-font-size" id="suggestions" style="font-style:normal;font-weight:400;line-height:34px">
		<?php esc_html_e( 'Suggestions', 'wporg-learn' ); ?>
	</p>
	<!-- /wp:paragraph -->

	<!-- wp:paragraph {"style":{"typography":{"fontStyle":"normal","fontWeight":"400,"lineHeight":"26px"},"spacing":{"margin":{"bottom":"40px"}}},"fontSize":"normal","fontFamily":"inter"} -->
	<p class="has-inter-font-family has-normal-font-size" style="margin-bottom:40px;font-style:normal;font-weight:400;line-height:26px">
		<?php esc_html_e( 'Found a typo, grammar error or outdated screenshot? ', 'wporg-learn' ); ?><a href="https://learn.wordpress.org/report-content-feedback/"><?php esc_html_e( 'Contact us', 'wporg-learn' ); ?></a>
	</p>
	<!-- /wp:paragraph -->

	<!-- wp:paragraph {"style":{"typography":{"fontStyle":"normal","fontWeight":"400","lineHeight":"34px"}},"fontSize":"large","fontFamily":"inter"} -->
	<p class="has-inter-font-family has-large-font-size" id="suggestions" style="font-style:normal;font-weight:400;line-height:34px">
		<?php esc_html_e( 'License', 'wporg-learn' ); ?>
	</p>
	<!-- /wp:paragraph -->

	<!-- wp:paragraph {"style":{"typography":{"fontStyle":"normal","fontWeight":"400,"lineHeight":"26px"}},"fontSize":"normal","fontFamily":"inter"} -->
	<p class="has-inter-font-family has-normal-font-size" style="font-style:normal;font-weight:400;line-height:26px">
		<?php esc_html_e( 'This work is licensed under a ', 'wporg-learn' ); ?><a href="http://creativecommons.org/licenses/by-sa/4.0/"><?php esc_html_e( 'Creative Commons Attribution-ShareAlike 4.0 International License', 'wporg-learn' ); ?></a>.
	</p>
	<!-- /wp:paragraph -->

</div>
<!-- /wp:group -->
