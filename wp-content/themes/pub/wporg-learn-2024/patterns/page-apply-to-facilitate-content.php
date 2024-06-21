<?php
/**
 * Title: Apply to Facilitate or Co-host Page Content
 * Slug: wporg-learn-2024/page-apply-to-facilitate-content
 * Inserter: no
 */

?>
 
<!-- wp:heading {"level":1} -->
<h1 class="wp-block-heading">
	<?php esc_html_e( 'Apply to Facilitate or Co-host', 'wporg-learn' ); ?>
</h1>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>
<?php echo wp_kses_post(
	sprintf(
		/* translators: 1: handbook online workshops link, 2: handbook online workshops co-hosting link */
		__( 'Want to know more about online workshops and how they work? <a href="%1$s">Check out the handbook</a> and learn more about facilitating and <a href="%2$s">co-hosting</a> online workshops.', 'wporg-learn' ),
		'https://make.wordpress.org/training/handbook/online-workshops/',
		'https://make.wordpress.org/training/handbook/online-workshops/co-hosting-an-online-workshop/',
	)
); ?>
</p>
<!-- /wp:paragraph -->

<!-- wp:wporg/notice {"type":"info"} -->
<div class="wp-block-wporg-notice is-info-notice">
	<div class="wp-block-wporg-notice__icon"></div>
	<div class="wp-block-wporg-notice__content">
		<p>
		<?php echo wp_kses_post(
			sprintf(
				/* translators: 1: Meetup.com online workshops link, 2: Learn email link */
				__( 'If you are already a WordPress meetup organizer in your local community, then you can organize an <a href="%1$s">online workshop</a> as part of your meetup group! If you would like to facilitate an online workshop for the meetup group, please fill in the form below. If you have any questions, reach out to <a href="%2$s">learn@wordpress.org</a>.', 'wporg-learn' ),
				'https://www.meetup.com/learn-wordpress-online-workshops/',
				'mailto:learn@wordpress.org',
			)
		); ?>
		</p>
	</div>
</div>
<!-- /wp:wporg/notice -->

<!-- wp:heading {"style":{"spacing":{"margin":{"top":"var:preset|spacing|40"}}}} -->
<h2 class="wp-block-heading" style="margin-top:var(--wp--preset--spacing--40)"><?php esc_html_e( 'Submission Form', 'wporg-learn' ); ?></h2>
<!-- /wp:heading -->

<!-- wp:list {"backgroundColor":"light-grey-2"} -->
<ul class="wp-block-list has-light-grey-2-background-color has-background">
	
	<!-- wp:list-item -->
	<li><?php esc_html_e( 'All the fields are required.', 'wporg-learn' ); ?></li>
	<!-- /wp:list-item -->

	<!-- wp:list-item -->
	<li><?php esc_html_e( 'Please fill this form in English.', 'wporg-learn' ); ?></li>
	<!-- /wp:list-item -->

</ul>
<!-- /wp:list -->

<!-- wp:post-content {"layout":{"inherit":true}} /-->

<!-- wp:wporg/notice {"type":"alert","style":{"spacing":{"margin":{"top":"var:preset|spacing|30"}}}} -->
<div class="wp-block-wporg-notice is-alert-notice" style="margin-top:var(--wp--preset--spacing--30)">
	<div class="wp-block-wporg-notice__icon"></div>
	<div class="wp-block-wporg-notice__content">
		<p>
		<?php echo wp_kses_post(
			sprintf(
				/* translators: 1: Training slack channel link */
				__( 'If you do not receive a response in about a week, please reach out in the <a href="%1$s">#training channel in Slack</a> to follow up.', 'wporg-learn' ),
				'http://wordpress.slack.com/messages/training/',
			)
		); ?>
		</p>
	</div>
</div>
<!-- /wp:wporg/notice -->
