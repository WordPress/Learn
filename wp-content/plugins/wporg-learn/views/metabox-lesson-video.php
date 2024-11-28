<?php
/**
 * Template for Lesson Video metabox
 */

/** @var WP_Post $post */
?>

<?php if ( $post->video_url ) { ?>

	<p>
		<label for="lesson-video-url">
			<?php esc_html_e( 'This Lesson was converted from a Tutorial, and the WordPress.tv URL was:', 'wporg_learn' ); ?>
		</label>
		<input type="text" id="lesson-video-url" name="lesson-video-url" value="<?php echo esc_url( $post->video_url ); ?>" readonly />
	</p>

<?php } else { ?>

	<p><?php esc_html_e( 'If this Lesson was converted from a Tutorial and had a WordPress.tv URL, it would be displayed here.', 'wporg_learn' ); ?></p>

<?php } ?>

<?php wp_nonce_field( 'lesson-metaboxes', 'lesson-metabox-nonce' ); ?>
