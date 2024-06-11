<?php
/**
 * Title: Sidebar Meta Info
 * Slug: wporg-learn-2024/sidebar-meta-info
 * Inserter: no
 */

 use function WPOrg_Learn\Sensei\{get_my_courses_page_url}

?>

<!-- wp:group {"align":"full","className":"wporg-learn-sidebar-meta-info","layout":{"type":"constrained"}} -->
<div class="wp-block-group alignfull wporg-learn-sidebar-meta-info">

	<?php if ( Sensei_Course::is_user_enrolled( get_the_ID() ) ) : ?>
	<!-- wp:paragraph {"style":{"elements":{"link":{"color":{"text":"var:preset|color|blueberry-1"}}},"typography":{"fontStyle":"normal","fontWeight":"400","lineHeight":26px}},"textColor":"blueberry-1","fontSize":"normal","fontFamily":"inter","className":""wporg-learn-sidebar-all-courses"} -->
	<p class="has-blueberry-1-color has-text-color has-link-color has-inter-font-family has-normal-font-size wporg-learn-sidebar-all-courses" style="font-style:normal;font-weight:400;line-height:26px">
		<a href="<?php echo esc_url( get_my_courses_page_url() ); ?>">
			<?php esc_html_e( 'All My Courses', 'wporg-learn' ); ?>
		</a>
	</p>
	<!-- /wp:paragraph -->
	<?php endif; ?>

	<!-- wp:sensei-lms/course-progress {"barColor":"blueberry-1","barBackgroundColor":"blueberry-3","height":8,"className":"wporg-learn-sidebar-course-progress"} /-->

	<!-- wp:sensei-lms/button-take-course {"align":"full","borderRadius":2,"className":"is-style-default"} -->
	<div class="wp-block-sensei-lms-button-take-course is-style-default wp-block-sensei-button wp-block-button has-text-align-full">
		<button class="wp-block-button__link" style="border-radius:2px">
			<?php esc_html_e( 'Take this Course', 'wporg-learn' ); ?>
		</button>
	</div>
	<!-- /wp:sensei-lms/button-take-course -->

	<!-- wp:buttons {"layout":{"type":"flex","justifyContent":"center"},"className":""wporg-learn-sidebar-playground"} -->
	<div class="wp-block-buttons">
		<!-- wp:button {"textAlign":"center","width":100,"style":{"border":{"radius":"2px"},"spacing":{"padding":{"left":"13px","right":"13px","top":"16px","bottom":"16px"}},"typography":{"lineHeight":0,"fontStyle":"normal","fontWeight":"400"}},"className":"aligncenter is-style-outline","fontSize":"normal","fontFamily":"inter"} -->
		<div class="wp-block-button has-custom-width wp-block-button__width-100 has-custom-font-size aligncenter is-style-outline has-inter-font-family has-normal-font-size wporg-learn-sidebar-playground" style="font-style:normal;font-weight:400;line-height:0">
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
