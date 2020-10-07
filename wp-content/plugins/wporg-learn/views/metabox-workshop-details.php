<?php
/**
 * Template for Workshop Details metabox
 */

/** @var WP_Post $post */
/** @var DateInterval $duration_interval */
/** @var array $locales */
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

<?php wp_nonce_field( 'workshop-metaboxes', 'workshop-metabox-nonce' ); ?>

<p>
	<label for="workshop-video-language"><?php esc_html_e( 'Language', 'wporg_learn' ); ?></label>
	<select id="workshop-video-language" name="video-language" style="width: 100%;">
		<?php foreach ( $locales as $code => $label ) : ?>
			<option value="<?php echo esc_attr( $code ); ?>" <?php selected( $code, $post->video_language ); ?>>
				<?php echo esc_html( $label ); ?>
			</option>
		<?php endforeach; ?>
	</select>
</p>

<p>
	<label for="workshop-video-caption-language"><?php esc_html_e( 'Captions', 'wporg_learn' ); ?></label>
	<select id="workshop-video-caption-language" name="video-caption-language[]" style="width: 100%;" multiple>
		<?php foreach ( $locales as $code => $label ) : ?>
			<option value="<?php echo esc_attr( $code ); ?>" <?php selected( in_array( $code, $captions, true ) ); ?>>
				<?php echo esc_html( $label ); ?>
			</option>
		<?php endforeach; ?>
	</select>
</p>

<script>
	( function( $ ) {
		$( '#workshop-video-language, #workshop-video-caption-language' ).select2();
	} )( jQuery );
</script>
