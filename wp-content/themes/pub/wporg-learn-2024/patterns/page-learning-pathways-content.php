<?php
/**
 * Title: Learning Pathways Page Content
 * Slug: wporg-learn-2024/page-learning-pathways-content
 * Inserter: no
 */

use function WPOrg_Learn\Sensei\{get_my_courses_page_url}
?>

<!-- wp:paragraph {"style":{"spacing":{"margin":{"top":"0"}}}} -->
<p style="margin-top:0"><?php esc_html_e( 'Dive into our learning pathways which will take you from beginner to expert at your own pace.', 'wporg-learn' ); ?></p>
<!-- /wp:paragraph -->

<!-- wp:buttons {"style":{"spacing":{"margin":{"bottom":"var:preset|spacing|50"}}}} -->
<div class="wp-block-buttons" style="margin-bottom:var(--wp--preset--spacing--50)">

	<!-- wp:button -->
	<div class="wp-block-button"><a class="wp-block-button__link wp-element-button" href="<?php echo esc_url( get_my_courses_page_url() ); ?>"><?php esc_html_e( 'My Courses', 'wporg-learn' ); ?></a>
	</div>
	<!-- /wp:button -->

</div>
<!-- /wp:buttons -->
