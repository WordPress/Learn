<?php
/**
 * The sidebar containing the workshops widget area.
 *
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
 *
 * @package WordPressdotorg\Theme
 */

namespace WordPressdotorg\Theme;
?>
<aside class="lp-sidebar">
	<div class="lp-details">
		<?php
		if ( is_active_sidebar( 'wporg-learn-workshops' ) ) :
			dynamic_sidebar( 'wporg-learn-workshops' );
		endif;
		?>
	</div>
</aside>
