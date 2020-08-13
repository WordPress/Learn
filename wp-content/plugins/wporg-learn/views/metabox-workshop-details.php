<?php
/**
 * Template for Workshop Details metabox
 */

/** @var WP_Post $post */
/** @var DateInterval $duration_interval */
/** @var array $captions */
?>

<p>
	<label><?php esc_html_e( 'Duration', 'wporg_learn' ); ?></label><br />
	<label for="workshop-duration-hours">
		<input
			id="workshop-duration-hours"
			name="duration[h]"
			class="tiny-text"
			type="number"
			value="<?php echo absint( $duration_interval->h ); ?>"
			max="23"
		/>
		<?php esc_html_e( 'hours', 'wporg_learn' ); ?>
	</label>
	<label for="workshop-duration-minutes">
		<input
			id="workshop-duration-minutes"
			name="duration[m]"
			class="tiny-text"
			type="number"
			value="<?php echo absint( $duration_interval->i ); ?>"
			max="59"
		/>
		<?php esc_html_e( 'minutes', 'wporg_learn' ); ?>
	</label>
	<label for="workshop-duration-seconds">
		<input
				id="workshop-duration-seconds"
				name="duration[s]"
				class="tiny-text"
				type="number"
				value="<?php echo absint( $duration_interval->s ); ?>"
				max="59"
		/>
		<?php esc_html_e( 'seconds', 'wporg_learn' ); ?>
	</label>
</p>

<?php // todo Change this to a select dropdown with locale values. ?>
<p>
	<label for="workshop-video-language"><?php esc_html_e( 'Language', 'wporg_learn' ); ?></label>
	<input
		id="workshop-video-language"
		name="video-language"
		type="text"
		value="<?php echo esc_attr( $post->video_language ); ?>"
	/>
</p>

<?php // todo Change this to a multiselect dropdown with locale values. ?>
<p>
	<label for="workshop-video-caption-language"><?php esc_html_e( 'Captions', 'wporg_learn' ); ?></label>
	<textarea id="workshop-video-caption-language" name="video-caption-language"><?php echo esc_attr( implode( ', ', $captions ) ); ?></textarea>
	<span class="help">
		<?php esc_html_e( 'Separate multiple languages with a comma.', 'wporg_learn' ); ?>
	</span>
</p>
