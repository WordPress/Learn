<?php
/**
 * The sidebar containing the courses widget area.
 *
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
 *
 * @package WordPressdotorg\Theme
 */

namespace WordPressdotorg\Theme;
?>
<aside class="lp-sidebar">
	<div class="lp-details">
		<div class="lp-suggestion">
			<h2 class="lp-suggestion_title h4"><?php esc_html_e( 'Suggestions', 'wporg-learn' ); ?></h2>
			<p><?php esc_html_e( 'Found a typo, grammar error,or outdated screenshot?', 'wporg-learn' ); ?></p>
			<p><?php esc_html_e( 'Used this lesson plan in your event and have some suggestions?', 'wporg-learn' ); ?></p>
			<a href="https://learn.wordpress.org/report-content-errors/"><?php esc_html_e( 'Let us know!', 'wporg-learn' ); ?></a>
		</div>
		<br/>
		<?php
		if ( is_active_sidebar( 'wporg-learn-courses' ) ) :
			dynamic_sidebar( 'wporg-learn-courses' );
		endif;
		?>
	</div>
</aside>