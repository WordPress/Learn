<?php
/**
 * Template for Meeting Language metabox
 */

/** @var WP_Post $post */
/** @var array $locales */
/** @var string $language */
?>

<?php wp_nonce_field( 'meeting-metaboxes', 'meeting-metabox-nonce' ); ?>

<p>
	<select id="meeting-language-selector" name="meeting-language" aria-label="Meeting Language" style="width: 100%;">
		<?php foreach ( $locales as $code => $label ) : ?>
			<option value="<?php echo esc_attr( $code ); ?>" <?php selected( $code, $language ); ?>>
				<?php echo esc_html( $label ); ?>
			</option>
		<?php endforeach; ?>
	</select>
</p>
