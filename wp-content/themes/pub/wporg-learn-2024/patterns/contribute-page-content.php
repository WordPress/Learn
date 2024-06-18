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
	<?php
	/* translators: 1: opening a tag for a URL, 2: closing a tag, 3: opening a tag for a URL, 4: closing a tag */
	printf(
		esc_html__(
			'Tutorials can be on any topic related to WordPress, can be for any level of experience, and in any language. 
			To get started, check out this %1$s tutorial about creating tutorials%2$s. Then %3$s submit your tutorial presenter application%4$s.',
			'wporg-learn'
		),
		'<a href="https://learn.wordpress.org/workshop/how-to-submit-a-workshop/">',
		'</a>',
		'<a href="https://learn.wordpress.org/workshop-presenter-application/">',
		'</a>'
	);
	?>
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
	<?php
	/* translators: 1: opening a tag for a URL, 2: closing a tag, 3: opening a tag for a URL, 4: closing a tag, 5: opening a tag for a URL, 6: closing a tag */
	printf(
		esc_html__(
			'To learn more about online workshop facilitators, %1$s read this post%2$s. You can %3$s apply to become an online workshop facilitator here%4$s, 
			or you can even %5$s organize an online workshop for your local WordPress meetup%6$s.',
			'wporg-learn'
		),
		'<a href="https://make.wordpress.org/community/2020/08/11/tuesday-trainings-how-to-be-an-excellent-discussion-group-leader/">',
		'</a>',
		'<a href="https://learn.wordpress.org/social-learning/">',
		'</a>',
		'<a href="https://make.wordpress.org/community/handbook/virtual-events/organize-learn-wordpress-discussion-groups-for-your-wordpress-meetup/">',
		'</a>'
	);
	?>
</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"style":{"typography":{"fontStyle":"normal","fontWeight":"600"},"spacing":{"margin":{"top":"50px"}}},"fontSize":"extra-large","fontFamily":"inter"} -->
<h2 class="wp-block-heading has-inter-font-family has-extra-large-font-size" style="margin-top:50px;font-style:normal;font-weight:600">
	<?php esc_html_e( 'Update and contribute lesson plans', 'wporg-learn' ); ?>
</h2>
<!-- /wp:heading -->

<!-- wp:paragraph {"style":{"typography":{"fontStyle":"normal","fontWeight":"400"},"spacing":{"margin":{"bottom":"var:preset|spacing|20","top":"var:preset|spacing|20"}}},"fontSize":"small","fontFamily":"inter"} -->
<p class="has-inter-font-family has-small-font-size" style="margin-top:var(--wp--preset--spacing--20);margin-bottom:var(--wp--preset--spacing--20);font-style:normal;font-weight:400">
	<?php
	/* translators: 1: opening a tag for a URL, 2: closing a tag, 3: opening a tag for a URL, 4: closing a tag, 5: opening a tag for a URL, 6: closing a tag, 7: opening a tag for a URL, 8: closing a tag */
	printf(
		esc_html__(
			'%1$s Lesson Plans%2$s provide an outline and complete script for anyone wanting to run their own classes at in-person or virtual events. 
			View our video about submitting Lesson Plans. You can even use these lesson plans for the Tutorials that you submit! 
			The Training team meets weekly in %3$s #training channel%4$s in Slack. See their %5$s Welcome page for meeting times%6$s. 
			Learn more on the Training team\'s %7$s Get Started page%8$s.',
			'wporg-learn'
		),
		'<a href="https://learn.wordpress.org/lesson-plans/">',
		'</a>',
		'<a href="http://wordpress.slack.com/messages/training/">',
		'</a>',
		'<a href="https://make.wordpress.org/training/handbook/getting-started/how-we-work-together/">',
		'</a>',
		'<a href="https://make.wordpress.org/training/handbook/getting-started/">',
		'</a>'
	);
	?>
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
	<?php
	/* translators: 1: opening a tag for a URL, 2: closing a tag, 3: opening a tag for a URL, 4: closing a tag, 5: opening a tag for a URL, 6: closing a tag, 7: opening a tag a URL, 8: closing a tag */
	printf(
		esc_html__(
			'%1$s Courses%2$s consist of long-form lessons in multiple media formats - they can include text, video and images. 
			The Training team meets weekly in %3$s #training channel%4$s in Slack. See their %5$s Welcome page for meeting times%6$s. 
			Learn more on the Training team\'s %7$s Get Started page%8$s.',
			'wporg-learn'
		),
		'<a href="https://learn.wordpress.org/courses/">',
		'</a>',
		'<a href="http://wordpress.slack.com/messages/training/">',
		'</a>',
		'<a href="https://make.wordpress.org/training/handbook/getting-started/how-we-work-together/">',
		'</a>',
		'<a href="https://make.wordpress.org/training/handbook/getting-started/">',
		'</a>'
	);
	?>

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
	<?php
	/* translators: 1: opening strong tag, 2: closing strong tag, 3: opening a tag for a URL, 4: closing a tag */
	printf(
		esc_html__(
			'To get even more involved, %1$s join the Training Team%2$s to help build the platform, review workshop applications, decide on future content, and more. 
			If you\'re interested in helping, share your interest in the %3$s #training%4$s channel, join one of the Training team meetings, 
			or follow %5$s the Training team%6$s for news on working group-specific meetings.',
			'wporg-learn'
		),
		'<strong>',
		'</strong>',
		'<a href="http://wordpress.slack.com/messages/training/">',
		'</a>',
		'<a href="https://make.wordpress.org/training/">',
		'</a>'
	);
	?>

</p>
<!-- /wp:paragraph -->
