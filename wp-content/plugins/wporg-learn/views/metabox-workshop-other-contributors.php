<?php
/**
 * Template for Other Contributors metabox
 */

/** @var WP_Post $post */
/** @var array $other_contributors */
?>

<?php // todo Change this to a multiselect dropdown that validates wporg usernames. ?>
<p>
	<label for="workshop-other-contributor-wporg-username"><?php esc_html_e( 'WordPress.org User Names', 'wporg_learn' ); ?></label>
	<textarea
		id="workshop-other-contributor-wporg-username"
		name="other-contributor-wporg-username"
	><?php echo esc_html( implode( ', ', $other_contributors ) ); ?></textarea>
	<span class="help">
		<?php esc_html_e( 'Separate multiple contributor user names with a comma.', 'wporg_learn' ); ?>
	</span>
</p>
