<?php
/** @var WP_Post $post */
/** @var array $facilitators */
?>

<?php // todo Change this to a multiselect dropdown that validates wporg usernames. ?>
<p>
	<label for="workshop-facilitator-wporg-username"><?php _e( 'WordPress.org User Names', 'wporg_learn' ); ?></label>
	<textarea id="workshop-facilitator-wporg-username" name="facilitator-wporg-username"><?php
		echo sanitize_textarea_field( implode( ', ', $facilitators ) );
	?></textarea>
	<span class="help">
		<?php _e( 'Separate multiple facilitator user names with a comma.', 'wporg_learn' ); ?>
	</span>
</p>
