<?php
/**
 * Title: Contribute Page Content
 * Slug: wporg-learn-2024/contribute-page-content
 * Inserter: no
 */

?>
 
<!-- wp:heading {"style":{"typography":{"fontSize":"36px","fontStyle":"normal","fontWeight":"400"}},"fontFamily":"eb-garamond"} -->
<h2 class="wp-block-heading has-eb-garamond-font-family" style="font-size:36px;font-style:normal;font-weight:400">
	<?php esc_html_e( 'Contribute', 'wporg-learn' ); ?>
</h2>
<!-- /wp:heading -->

<!-- wp:paragraph {"style":{"typography":{"fontStyle":"normal","fontWeight":"400"},"spacing":{"margin":{"bottom":"40px","top":"0px"}}},"fontSize":"normal","fontFamily":"inter"} -->
<p class="has-inter-font-family has-normal-font-size" style="margin-top:0px;margin-bottom:40px;font-style:normal;font-weight:400">
	<?php esc_html_e( "Here's how you can get involved and create content for Learn WordPress", 'wporg-learn' ); ?>
</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"style":{"typography":{"fontStyle":"normal","fontWeight":"600"}},"fontSize":"extra-large","fontFamily":"inter"} -->
<h2 class="wp-block-heading has-inter-font-family has-extra-large-font-size" style="font-style:normal;font-weight:600">
	<?php esc_html_e( 'Create a Tutorial', 'wporg-learn' ); ?>
</h2>
<!-- /wp:heading -->

<!-- wp:paragraph {"style":{"typography":{"fontStyle":"normal","fontWeight":"400"},"spacing":{"margin":{"bottom":"var:preset|spacing|20","top":"var:preset|spacing|20"}}},"fontSize":"small","fontFamily":"inter"} -->
<p class="has-inter-font-family has-small-font-size" style="margin-top:var(--wp--preset--spacing--20);margin-bottom:var(--wp--preset--spacing--20);font-style:normal;font-weight:400">
	<?php echo wp_kses_post(
		sprintf(
			/* translators: 1: tutorial link, 2: application link */
			__( 'Tutorials can be on any topic related to WordPress, can be for any level of experience, and in any language. 
			To get started, check out this <a href="%1$s">tutorial about creating tutorials</a>. Then <a href="%2$s">submit your tutorial presenter application</a>.', 'wporg-learn' ),
			site_url( '/tutorial/how-to-submit-a-workshop/' ),
			site_url( '/tutorial-presenter-application/' ),
		)
	); ?>
</p>
<!-- /wp:paragraph -->

<!-- wp:embed {"url":"https://videopress.com/v/52hW5V7s","type":"video","providerNameSlug":"videopress","responsive":true,"className":"wp-embed-aspect-16-9 wp-has-aspect-ratio"} -->
<figure class="wp-block-embed is-type-video is-provider-videopress wp-block-embed-videopress wp-embed-aspect-16-9 wp-has-aspect-ratio"><div class="wp-block-embed__wrapper">
https://videopress.com/v/52hW5V7s
</div></figure>
<!-- /wp:embed -->

<!-- wp:heading {"style":{"typography":{"fontStyle":"normal","fontWeight":"600"},"spacing":{"margin":{"top":"50px"}}},"fontSize":"extra-large","fontFamily":"inter"} -->
<h2 class="wp-block-heading has-inter-font-family has-extra-large-font-size" style="margin-top:50px;font-style:normal;font-weight:600">
	<?php esc_html_e( 'Facilitate an online workshop', 'wporg-learn' ); ?>
</h2>
<!-- /wp:heading -->

<!-- wp:paragraph {"style":{"typography":{"fontStyle":"normal","fontWeight":"400"},"spacing":{"margin":{"bottom":"0","top":"var:preset|spacing|20"}}},"fontSize":"small","fontFamily":"inter"} -->
<p class="has-inter-font-family has-small-font-size" style="margin-top:var(--wp--preset--spacing--20);margin-bottom:0;font-style:normal;font-weight:400">
	<?php echo wp_kses_post(
		sprintf(
			/* translators: 1: read this post link, 2: apply link, 3: organize an online workshop link */
			__( 'To learn more about online workshop facilitators, <a href="%1$s">read this post</a>. You can <a href="%2$s">apply to become an online workshop facilitator here</a>, 
			or you can even <a href="%3$s">organize an online workshop for your local WordPress meetup</a>.', 'wporg-learn' ),
			'https://make.wordpress.org/community/2020/08/11/tuesday-trainings-how-to-be-an-excellent-discussion-group-leader/',
			site_url( '/social-learning/' ),
			'https://make.wordpress.org/community/handbook/virtual-events/organize-learn-wordpress-discussion-groups-for-your-wordpress-meetup/',
		)
	); ?>
</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"style":{"typography":{"fontStyle":"normal","fontWeight":"600"},"spacing":{"margin":{"top":"50px"}}},"fontSize":"extra-large","fontFamily":"inter"} -->
<h2 class="wp-block-heading has-inter-font-family has-extra-large-font-size" style="margin-top:50px;font-style:normal;font-weight:600">
	<?php esc_html_e( 'Update and contribute lesson plans', 'wporg-learn' ); ?>
</h2>
<!-- /wp:heading -->

<!-- wp:paragraph {"style":{"typography":{"fontStyle":"normal","fontWeight":"400"},"spacing":{"margin":{"bottom":"var:preset|spacing|20","top":"var:preset|spacing|20"}}},"fontSize":"small","fontFamily":"inter"} -->
<p class="has-inter-font-family has-small-font-size" style="margin-top:var(--wp--preset--spacing--20);margin-bottom:var(--wp--preset--spacing--20);font-style:normal;font-weight:400">
	<?php echo wp_kses_post(
		sprintf(
			/* translators: 1: lesson plans link, 2: training channel link, 3: welcome page link, 4: get started page link */
			__( '<a href="%1$s">Lesson Plans</a> provide an outline and complete script for anyone wanting to run their own classes at in-person or virtual events. 
			View our video about submitting Lesson Plans. You can even use these lesson plans for the Tutorials that you submit! 
			The Training team meets weekly in <a href="%2$s">#training channel</a> in Slack. See their <a href="%3$s">Welcome page for meeting times</a>. 
			Learn more on the Training team\'s <a href="%4$s">Get Started page</a>.', 'wporg-learn' ),
			site_url( '/lesson-plans/' ),
			'http://wordpress.slack.com/messages/training/',
			'https://make.wordpress.org/training/handbook/getting-started/how-we-work-together/',
			'https://make.wordpress.org/training/handbook/getting-started/',
		)
	); ?>
</p>
<!-- /wp:paragraph -->

<!-- wp:embed {"url":"https://videopress.com/v/qkmHo4ug","type":"video","providerNameSlug":"videopress","responsive":true,"className":"wp-embed-aspect-16-9 wp-has-aspect-ratio"} -->
<figure class="wp-block-embed is-type-video is-provider-videopress wp-block-embed-videopress wp-embed-aspect-16-9 wp-has-aspect-ratio"><div class="wp-block-embed__wrapper">
https://videopress.com/v/qkmHo4ug
</div></figure>
<!-- /wp:embed -->

<!-- wp:heading {"style":{"typography":{"fontStyle":"normal","fontWeight":"600"},"spacing":{"margin":{"top":"50px"}}},"fontSize":"extra-large","fontFamily":"inter"} -->
<h2 class="wp-block-heading has-inter-font-family has-extra-large-font-size" style="margin-top:50px;font-style:normal;font-weight:600">
	<?php esc_html_e( 'Update and contribute courses', 'wporg-learn' ); ?>
</h2>
<!-- /wp:heading -->

<!-- wp:paragraph {"style":{"typography":{"fontStyle":"normal","fontWeight":"400"},"spacing":{"margin":{"bottom":"var:preset|spacing|20","top":"var:preset|spacing|20"}}},"fontSize":"small","fontFamily":"inter"} -->
<p class="has-inter-font-family has-small-font-size" style="margin-top:var(--wp--preset--spacing--20);margin-bottom:var(--wp--preset--spacing--20);font-style:normal;font-weight:400">
	<?php echo wp_kses_post(
		sprintf(
			/* translators: 1: courses link, 2: training channel link, 3: welcome page link, 4: get started page link */
			__( '<a href="%1$s">Courses</a> consist of long-form lessons in multiple media formats - they can include text, video and images. 
			The Training team meets weekly in <a href="%2$s">#training channel</a> in Slack. See their <a href="%3$s">Welcome page for meeting times</a>. 
			Learn more on the Training team\'s <a href="%4$s">Get Started page</a>.', 'wporg-learn' ),
			site_url( '/courses/' ),
			'http://wordpress.slack.com/messages/training/',
			'https://make.wordpress.org/training/handbook/getting-started/how-we-work-together/',
			'https://make.wordpress.org/training/handbook/getting-started/',
		)
	); ?>
</p>
<!-- /wp:paragraph -->

<!-- wp:shortcode -->
[videopress 9hC1sT88]
<!-- /wp:shortcode -->

<!-- wp:heading {"style":{"typography":{"fontStyle":"normal","fontWeight":"600"},"spacing":{"margin":{"top":"50px"}}},"fontSize":"extra-large","fontFamily":"inter"} -->
<h2 class="wp-block-heading has-inter-font-family has-extra-large-font-size" style="margin-top:50px;font-style:normal;font-weight:600">
	<?php esc_html_e( 'Join the Training Team', 'wporg-learn' ); ?>
</h2>
<!-- /wp:heading -->

<!-- wp:paragraph {"style":{"typography":{"fontStyle":"normal","fontWeight":"400"},"spacing":{"margin":{"bottom":"0","top":"0"}}},"fontSize":"small","fontFamily":"inter"} -->
<p class="has-inter-font-family has-small-font-size" style="margin-top:0;margin-bottom:0;font-style:normal;font-weight:400">
	<?php echo wp_kses_post(
		sprintf(
			/* translators: 1: training channel link, 2: training team link */
			__( 'To get even more involved, <strong>join the Training Team</strong> to help build the platform, review workshop applications, decide on future content, and more. 
			If you\'re interested in helping, share your interest in the <a href="%1$s">#training channel</a>, join one of the Training team meetings, 
			or follow <a href="%2$s">the Training team</a> for news on working group-specific meetings.', 'wporg-learn' ),
			'http://wordpress.slack.com/messages/training/',
			'https://make.wordpress.org/training/',
		)
	); ?>
</p>
<!-- /wp:paragraph -->
