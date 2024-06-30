<?php
/**
 * Title: Course Completed Page Content
 * Slug: wporg-learn-2024/page-course-completed-content
 * Inserter: no
 */

?>
 
<!-- wp:heading {"level":1} -->
<h1 class="wp-block-heading">
	<?php esc_html_e( 'Course Completed', 'wporg-learn' ); ?>
</h1>
<!-- /wp:heading -->

<!-- wp:heading {"style":{"spacing":{"margin":{"top":"40px","bottom":"10px"}}},"fontSize":"large","fontFamily":"inter"} -->
<h2 class="wp-block-heading has-inter-font-family has-large-font-size" style="margin-top:40px;margin-bottom:10px">
	<?php esc_html_e( 'Congratulations on completing this course!', 'wporg-learn' ); ?>
</h2>
<!-- /wp:heading -->

<!-- wp:paragraph {"style":{"spacing":{"margin":{"top":"0"}}},"fontSize":"normal","fontFamily":"inter"} -->
<p class="has-inter-font-family has-normal-font-size" style="margin-top:0">
	<?php esc_html_e( 'Please share your feedback on this course by completing a brief survey.', 'wporg-learn' ); ?>
</p>
<!-- /wp:paragraph -->

<!-- wp:buttons -->
<div class="wp-block-buttons">
	<!-- wp:button {"style":{"spacing":{"padding":{"left":"32px","right":"32px","top":"17px","bottom":"17px"}}}} -->
	<div class="wp-block-button">
		<a class="wp-block-button__link wp-element-button" href="https://docs.google.com/forms/d/e/1FAIpQLSf0QMflUedxjta0u5qS4_pl-9aY06BDBXgRn2PoZA1gRvD9jw/viewform" style="padding-top:17px;padding-right:32px;padding-bottom:17px;padding-left:32px">
			<?php esc_html_e( 'Complete the survey', 'wporg-learn' ); ?>
		</a>
	</div>
	<!-- /wp:button -->
</div>
<!-- /wp:buttons -->

<!-- wp:heading {"style":{"spacing":{"margin":{"top":"40px","bottom":"20px"}}},"fontSize":"large","fontFamily":"inter"} -->
<h2 class="wp-block-heading has-inter-font-family has-large-font-size" style="margin-top:40px;margin-bottom:20px">
	<?php esc_html_e( 'A Developers Guide to Block Themes - Part 3', 'wporg-learn' ); ?>
</h2>
<!-- /wp:heading -->

<!-- wp:buttons -->
<div class="wp-block-buttons">
	<!-- wp:button {"style":{"spacing":{"padding":{"left":"32px","right":"32px","top":"17px","bottom":"17px"}}},"className":"is-style-outline"} -->
	<div class="wp-block-button is-style-outline">
		<a class="wp-block-button__link wp-element-button" href="<?php echo esc_url( site_url( '/courses-overview/' ) ); ?>" style="padding-top:17px;padding-right:32px;padding-bottom:17px;padding-left:32px">
			<?php esc_html_e( 'Find more courses', 'wporg-learn' ); ?>
		</a>
	</div>
	<!-- /wp:button -->
</div>
<!-- /wp:buttons -->
