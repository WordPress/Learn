<?php
/**
 * Title: Sidebar Course
 * Slug: wporg-learn-2024/sidebar-course
 * Inserter: no
 *
 * @package wporg-learn-2024
 */

use function WPOrg_Learn\Sensei\{get_my_courses_page_url};

$completed_course = Sensei_Utils::user_completed_course( get_the_ID() );
$current_post     = get_post();

?>

<!-- wp:group {"style":{"spacing":{"blockGap":"var:preset|spacing|40"}},"className":"wporg-learn-sidebar-meta-info","layout":{"type":"flex","orientation":"vertical","justifyContent":"stretch"}} -->
<div class="wp-block-group wporg-learn-sidebar-meta-info">
	
	<?php if ( Sensei_Course::is_user_enrolled( get_the_ID() ) ) : ?>

		<!-- wp:group {"layout":{"type":"constrained"}} -->
		<div class="wp-block-group">

			<!-- wp:sensei-lms/course-progress {<?php echo ( $completed_course ? '"customTextColor":"var(--wp--custom--color--green-50)",' : '' ); ?>"customBarColor":"var(--wp--custom--color--green-50)","height":10,"className":"wporg-learn-sidebar-course-progress"} /-->

			<!-- wp:paragraph {"style":{"spacing":{"margin":{"top":"10px"}},"elements":{"link":{"color":{"text":"var:preset|color|blueberry-1"}}},"typography":{"fontStyle":"normal","fontWeight":"400","lineHeight":26px}},"textColor":"blueberry-1","fontSize":"normal","fontFamily":"inter","className":""wporg-learn-sidebar-all-courses"} -->
			<p class="has-blueberry-1-color has-text-color has-link-color has-inter-font-family has-normal-font-size wporg-learn-sidebar-all-courses" style="font-style:normal;font-weight:400;line-height:26px;margin-top:10px">
				<a href="<?php echo esc_url( get_my_courses_page_url() ); ?>">
					<?php esc_html_e( 'All My Courses', 'wporg-learn' ); ?>
				</a>
			</p>
			<!-- /wp:paragraph -->

		</div>
		<!-- /wp:group -->

	<?php endif; ?>

	<!-- wp:group {"layout":{"type":"constrained"}} -->
	<div class="wp-block-group">

		<!-- wp:sensei-lms/course-actions -->

			<!-- wp:sensei-lms/button-take-course {"align":"full"} -->
			<div class="wp-block-sensei-lms-button-take-course is-style-default wp-block-sensei-button wp-block-button has-text-align-full">
				<button class="wp-block-button__link"><?php esc_html_e( 'Take this course', 'wporg-learn' ); ?></button>
			</div>
			<!-- /wp:sensei-lms/button-take-course -->

			<!-- wp:sensei-lms/button-continue-course {"align":"full"} -->
			<div class="wp-block-sensei-lms-button-continue-course is-style-default wp-block-sensei-button wp-block-button has-text-align-full">
				<a class="wp-block-button__link"><?php esc_html_e( 'Continue', 'wporg-learn' ); ?></a>
			</div>
			<!-- /wp:sensei-lms/button-continue-course -->

			<!-- wp:sensei-lms/button-view-results {"align":"full"} -->
			<div class="wp-block-sensei-lms-button-view-results is-style-default wp-block-sensei-button wp-block-button has-text-align-full">
				<a class="wp-block-button__link"><?php esc_html_e( 'View results', 'wporg-learn' ); ?></a>
			</div>
			<!-- /wp:sensei-lms/button-view-results -->
			
		<!-- /wp:sensei-lms/course-actions -->

		<!-- wp:buttons {"layout":{"type":"flex","justifyContent":"center"}} -->
		<div class="wp-block-buttons">
			
			<!-- wp:button {"textAlign":"center","width":100,"style":{"typography":{"lineHeight":0,"fontStyle":"normal","fontWeight":"400"},"spacing":{"padding":{"left":"var:preset|spacing|10","right":"var:preset|spacing|10"}}},"className":"aligncenter is-style-outline","fontFamily":"inter"} -->
			<div class="wp-block-button has-custom-width wp-block-button__width-100 aligncenter is-style-outline has-inter-font-family" style="font-style:normal;font-weight:400">
				<a class="wp-block-button__link has-text-align-center wp-element-button" href="https://wordpress.org/playground/demo/?step=playground&amp;theme=twentytwentythree" style="padding-right:var(--wp--preset--spacing--10);padding-left:var(--wp--preset--spacing--10)" target="_blank" rel="noreferrer noopener"><?php esc_html_e( 'Practice on a private demo site', 'wporg-learn' ); ?></a>
			</div>
			<!-- /wp:button -->
		
		</div>
		<!-- /wp:buttons -->

	</div>
	<!-- /wp:group -->

	<!-- wp:pattern {"slug":"wporg-learn-2024/sidebar-details"} /-->

</div>
<!-- /wp:group -->
