<?php
/**
 * Title: 404 Page content
 * Slug: wporg-learn-2024/404-page-content
 * Inserter: no
 */

?>

<!-- wp:group {"style":{"border":{"top":{"width":"1px","color":"var:preset|color|charcoal-1"},"right":{"width":"0px","style":"none"},"bottom":{"width":"0px","style":"none"},"left":{"width":"0px","style":"none"}},"spacing":{"padding":{"left":"var:preset|spacing|60","right":"var:preset|spacing|60"},"margin":{"top":"0"}}},"backgroundColor":"charcoal-2","textColor":"white","className":"site-content-container"} -->
<main class="wp-block-group site-content-container has-white-color has-charcoal-2-background-color has-text-color has-background" style="border-top-color:var(--wp--preset--color--charcoal-1);border-top-width:1px;border-right-style:none;border-right-width:0px;border-bottom-style:none;border-bottom-width:0px;border-left-style:none;border-left-width:0px;margin-top:0;padding-right:var(--wp--preset--spacing--60);padding-left:var(--wp--preset--spacing--60)"><!-- wp:group {"textColor":"charcoal-1","className":"wporg-parent-oops-container","layout":{"type":"constrained"}} -->
<div class="wp-block-group wporg-parent-oops-container has-charcoal-1-color has-text-color"><!-- wp:paragraph {"textColor":"white"} -->
<p class="has-white-color has-text-color">Change the text color of the group here to adjust "Oops" color.</p>
<!-- /wp:paragraph --></div>
<!-- /wp:group -->

<!-- wp:heading {"level":1} -->
<h1><?php esc_html_e( 'Sorry, we couldn’t find this page.', 'wporg-learn' ); ?></h1>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p><?php esc_html_e( 'We’ve moved some things around to make the Learn WordPress experience better for you. Know what you’re looking for? Try a search:', 'wporg-learn' ); ?></p>
<!-- /wp:paragraph -->

<!-- wp:search {"showLabel":false,"placeholder":"Search...","width":100,"widthUnit":"%","buttonText":"Search","buttonPosition":"button-inside","buttonUseIcon":true,"style":{"color":{"background":"#ffffff00"}},"textColor":"charcoal-1","className":"is-style-default"} /-->

<!-- wp:paragraph "style":{"spacing":{"margin":{"top":"var:preset|spacing|50"}}} -->
<p style="margin-top:var(--wp--preset--spacing--50)"><?php esc_html_e( 'Or, explore some of the newest resources on Learn:', 'wporg-learn' ); ?></p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>
	<?php
	printf(
		/* translators: 1: Learning Pathways URL, 2. Online Workshops URL, 3. Lessons Archive URL */
		wp_kses_post( __( '<a href="%1$s">Learning Pathways</a>: Go from beginner to expert at your own pace.<br><a href="%2$s">Online Workshops</a>: Join a live session taught by experienced WordPress professionals.<br><a href="%3$s">Lessons</a>: Improve your WordPress expertise with versatile lessons featuring a blend of videos, exercises, quizzes, and more.', 'wporg-learn' ) ),
		esc_url( site_url( '/learning-pathways/' ) ),
		esc_url( site_url( '/online-workshops/' ) ),
		esc_url( site_url( '/lessons/' ) )
	);
	?>
</p>
<!-- /wp:paragraph -->

</main>
<!-- /wp:group -->
