<?php
/**
 * Template for Workshop Details metabox
 */

/** @var WP_Post $post */
/** @var DateInterval $duration_interval */
/** @var array $locales */
/** @var array $captions */
/** @var array $all_lessons */
/** @var array $selected_lessons */
?>

<p>
	<label><?php esc_html_e( 'WordPress.tv URL', 'wporg_learn' ); ?></label><br />
	<label for="workshop-video-url">
		<textarea
			id="workshop-video-url"
			name="video-url"
			class="large-text"
			rows="4"
		><?php echo esc_url( $post->video_url ); ?></textarea>
	</label>
</p>

<p>
	<label><?php esc_html_e( 'Duration', 'wporg_learn' ); ?></label><br />
	<label for="workshop-duration-hours">
		<input
			id="workshop-duration-hours"
			name="duration[h]"
			class="tiny-text"
			type="number"
			value="<?php echo absint( $duration_interval->h ); ?>"
			min="0"
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
			min="0"
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
				min="0"
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
	<label for="workshop-video-caption-language"><?php esc_html_e( 'Subtitles', 'wporg_learn' ); ?></label>
	<select id="workshop-video-caption-language" name="video-caption-language[]" style="width: 100%;" multiple>
		<?php foreach ( $locales as $code => $label ) : ?>
			<option value="<?php echo esc_attr( $code ); ?>" <?php selected( in_array( $code, $captions, true ) ); ?>>
				<?php echo esc_html( $label ); ?>
			</option>
		<?php endforeach; ?>
	</select>
</p>

<p>
	<label for="workshop-linked-lesson-id"><?php esc_html_e( 'Linked Quiz', 'wporg_learn' ); ?></label>
	<select id="workshop-linked-lesson-id" name="linked-lesson-id" style="width: 100%;">
		<option value="" disabled hidden <?php selected( ! $post->linked_lesson_id ); ?>></option>
		<?php foreach ( $all_lessons as $lesson ) : ?>
			<option value="<?php echo esc_attr( $lesson->ID ); ?>" <?php selected( $lesson->ID, $post->linked_lesson_id ); ?>>
				<?php echo esc_html( get_the_title( $lesson->ID ) ); ?>
			</option>
		<?php endforeach; ?>
	</select>
</p>

<script>
	( function( $ ) {
		$( '#workshop-video-language, #workshop-video-caption-language, #workshop-linked-lesson-id' ).select2();
	} )( jQuery );
</script>
