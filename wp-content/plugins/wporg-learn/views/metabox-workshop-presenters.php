<?php
/**
 * Template for Presenters metabox
 */

/** @var WP_Post $post */
/** @var array $presenters */
?>

<?php // todo Change this to a multiselect dropdown that validates wporg usernames. ?>
<p>
	<label for="workshop-presenter-wporg-username"><?php esc_html_e( 'WordPress.org User Names', 'wporg_learn' ); ?></label>
	<textarea
		id="workshop-presenter-wporg-username"
		name="presenter-wporg-username"
	><?php echo esc_html( implode( ', ', $presenters ) ); ?></textarea>
	<span class="help">
		<?php esc_html_e( 'Separate multiple presenter user names with a comma.', 'wporg_learn' ); ?>
	</span>
</p>
