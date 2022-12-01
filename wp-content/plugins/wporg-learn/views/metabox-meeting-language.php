<?php
/**
 * Template for Other Contributors metabox
 */

/** @var WP_Post $post */
/** @var array $locales */
/** @var string $language */
?>

<?php wp_nonce_field( 'meeting-metaboxes', 'meeting-metabox-nonce' ); ?>

<p>
	<select id="meeting-language" name="meeting-language" style="width: 100%;">
		<?php foreach ( $locales as $code => $label ) : ?>
			<option value="<?php echo esc_attr( $code ); ?>" <?php selected( $code, $language ); ?>>
				<?php echo esc_html( $label ); ?>
			</option>
		<?php endforeach; ?>
	</select>
</p>
