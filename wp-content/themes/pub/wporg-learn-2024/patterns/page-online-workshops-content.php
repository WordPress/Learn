<?php
/**
 * Title: Online Workshops Page Content
 * Slug: wporg-learn-2024/page-online-workshops-content
 * Inserter: no
 */

?>

<!-- wp:group {"style":{"spacing":{"margin":{"bottom":"var:preset|spacing|50"}}},"layout":{"type":"constrained","justifyContent":"left","contentSize":"730px"}} -->
<div class="wp-block-group" style="margin-bottom:var(--wp--preset--spacing--50)">

	<!-- wp:heading {"level":1} -->
	<h1 class="wp-block-heading"><?php esc_html_e( 'Online Workshops', 'wporg-learn' ); ?></h1>
	<!-- /wp:heading -->

	<!-- wp:paragraph -->
	<p><?php esc_html_e( 'Online workshops are live sessions where you can learn alongside other WordPress enthusiasts. They are a safe zone where you can come as you are, develop new ideas, explore issues, ask questions, network over shared interests, exchange theories, collaborate on work, and thrive in uncertainty.', 'wporg-learn' ); ?></p>
	<!-- /wp:paragraph -->

	<!-- wp:buttons -->
	<div class="wp-block-buttons">

		<!-- wp:button {"className":"is-style-outline"} -->
		<div class="wp-block-button is-style-outline"><a class="wp-block-button__link wp-element-button" href="apply-to-facilitate"><?php esc_html_e( 'Apply to facilitate', 'wporg-learn' ); ?></a></div>
		<!-- /wp:button -->

		<!-- wp:button {"className":"is-style-fill"} -->
		<div class="wp-block-button is-style-fill"><a class="wp-block-button__link wp-element-button" href="https://wordpress.tv/category/learn-wordpress-online-workshops/"><?php esc_html_e( 'View recorded online workshops', 'wporg-learn' ); ?></a></div>
		<!-- /wp:button -->

	</div>
	<!-- /wp:buttons -->

</div>
<!-- /wp:group -->

<!-- wp:wporg-meeting-calendar/main /-->

<!-- wp:columns -->
<div class="wp-block-columns">

	<!-- wp:column -->
	<div class="wp-block-column">

		<!-- wp:paragraph {"fontSize":"small"} -->
		<p class="has-small-font-size">
			<?php echo wp_kses_post(
				sprintf(
					/* translators: %1$s: meetup.com online workshops link, %2$s: code of conduct link */
					__( 'RSVPs and communications are handled through <a href="%1$s">the Meetup.com group</a>. Each event links to the RSVP page. Events are shown in your local time. You must agree to the <a href="%2$s">Code of Conduct</a> in order to participate in online workshops.', 'wporg-learn' ),
					esc_url( 'https://www.meetup.com/learn-wordpress-online-workshops/' ),
					esc_url( 'https://learn.wordpress.org/online-workshops/code-of-conduct/' ),
				)
			); ?>
		</p>
		<!-- /wp:paragraph -->

	</div>
	<!-- /wp:column -->

	<!-- wp:column -->
	<div class="wp-block-column">

		<!-- wp:paragraph {"align":"right","fontSize":"small"} -->
		<p class="has-text-align-right has-small-font-size"><?php esc_html_e( 'Subscribe to this calendar:', 'wporg-learn' ); ?> <a href="https://calendar.google.com/calendar/u/0/embed?src=3f9k1go9k6bks9u41i20u2lje56hd1fv@import.calendar.google.com" target="_blank" rel="noreferrer noopener"><?php esc_html_e( 'Google Calendar ↗', 'wporg-learn' ); ?></a> &middot; <a href="https://learn.wordpress.org/meetings.ics" target="_blank" rel="noreferrer noopener"><?php esc_html_e( 'ICS', 'wporg-learn' ); ?></a> &middot; <a href="https://learn.wordpress.org/feed/?post_type=meeting" target="_blank" rel="noreferrer noopener"><?php esc_html_e( 'RSS', 'wporg-learn' ); ?></a></p>
		<!-- /wp:paragraph -->

	</div>
	<!-- /wp:column -->

</div>
<!-- /wp:columns -->

<!-- wp:wporg/notice -->
<div class="wp-block-wporg-notice is-tip-notice">
	<div class="wp-block-wporg-notice__icon"></div>
	<div class="wp-block-wporg-notice__content">
		<p>
			<?php echo wp_kses_post(
				sprintf(
					/* translators: %s: WordPress Playground link */
					__( 'In order to enhance your learning experience, <a href="%s" target="_blank" rel="noreferrer noopener">click here to open a private and secure WordPress site that only you can access</a>', 'wporg-learn' ),
					esc_url( 'https://developer.wordpress.org/playground/demo/?step=playground&amp;theme=twentytwentythree' ),
				)
			); ?>
		</p>
	</div>
</div>
<!-- /wp:wporg/notice -->
