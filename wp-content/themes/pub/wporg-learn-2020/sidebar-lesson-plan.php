<?php
/**
 * The sidebar containing the lesson plans widget area.
 *
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
 *
 * @package WordPressdotorg\Theme
 */

namespace WordPressdotorg\Theme;

?>
<aside class="lp-sidebar">
	<?php
	if ( is_active_sidebar( 'wporg-learn-lesson-plans' ) ) :
		dynamic_sidebar( 'wporg-learn-lesson-plans' );
	endif;
	?>
</aside>
