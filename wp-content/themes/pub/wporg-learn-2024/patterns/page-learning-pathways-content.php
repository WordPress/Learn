<?php
/**
 * Title: Learning Pathways Page Content
 * Slug: wporg-learn-2024/page-learning-pathways-content
 * Inserter: no
 */

use function WordPressdotorg\Theme\Learn_2024\Template_Helpers\{get_my_courses_page_url};
?>

<!-- wp:heading {"level":1} -->
<h1 class="wp-block-heading"><?php esc_html_e( 'Learning Pathways', 'wporg-learn' ); ?></h1>
<!-- /wp:heading -->

<!-- wp:paragraph {"style":{"spacing":{"margin":{"top":"var:preset|spacing|10"}}}} -->
<p style="margin-top:var(--wp--preset--spacing--10)"><?php esc_html_e( 'Build your skills progressively with a complete program that can take you from beginner to advanced at your own pace.', 'wporg-learn' ); ?></p>
<!-- /wp:paragraph -->

<!-- wp:buttons {"style":{"spacing":{"margin":{"bottom":"var:preset|spacing|50"}}}} -->
<div class="wp-block-buttons" style="margin-bottom:var(--wp--preset--spacing--50)">

	<!-- wp:button -->
	<div class="wp-block-button"><a class="wp-block-button__link wp-element-button" href="<?php echo esc_url( get_my_courses_page_url() ); ?>"><?php esc_html_e( 'My Courses', 'wporg-learn' ); ?></a>
	</div>
	<!-- /wp:button -->

</div>
<!-- /wp:buttons -->
