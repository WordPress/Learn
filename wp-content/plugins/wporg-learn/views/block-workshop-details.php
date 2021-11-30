<?php

namespace WPOrg_Learn\View\Blocks;

defined( 'WPINC' ) || die();

/**
 * @global WP_Post $post
 * @var    array   $fields
 * @var    string  $quiz_url
 */

$has_transcript = false !== strpos( $post->post_content, 'id="transcript"' );

?>

<div class="wp-block-wporg-learn-workshop-details">
	<?php if ( ! empty( $fields ) ) : ?>
		<ul class="workshop-details-list">
			<?php foreach ( $fields as $key => $field ) : ?>
				<li>
					<b><?php echo esc_html( $field['label'] ); ?></b>
					<span>
						<?php
						$i = 0;
						foreach ( $field['value'] as $field_key => $value ) {
							$url = '';
							if ( ! empty( $field['param'] ) ) {
								$url = trailingslashit( site_url() ) . 'workshops/?' . $key . '=' . $field['param'][ $field_key ];
							}

							if ( 0 < $i ) {
								echo ', ';
							}

							if ( $url ) {
								echo sprintf( '%1$s' . esc_url( $url ) . '%2$s' . esc_html( $value ) . '%3$s', '<a href="', '">', '</a>' );
							} else {
								echo esc_html( $value );
							}
							$i++;
						}
						?>
					</span>
				</li>
			<?php endforeach; ?>

			<?php if ( $has_transcript ) : ?>
				<li>
					<b><?php esc_html_e( 'Transcript', 'wporg-learn' ); ?></b>
					<span>
						<a class="components-button is-secondary is-small" href="#transcript">
							<?php esc_html_e( 'View', 'wporg-learn' ); ?>
						</a>
					</span>
				</li>
			<?php endif; ?>
			<li>
				<b><?php esc_html_e( 'Print View', 'wporg-learn' ); ?></b>
				<span>
						<a class="components-button is-secondary is-small" href="#" onclick="window.print();">
							<?php esc_html_e( 'View', 'wporg-learn' ); ?>
						</a>
					</span>
			</li>
		</ul>
	<?php endif; ?>

	<?php if ( ! empty( $quiz_url ) ) : ?>
		<div class="wp-block-button is-style-primary-full-width">
			<a class="wp-block-button__link" href="<?php echo esc_attr( $quiz_url ); ?>" style="border-radius:5px">
				<?php esc_html_e( 'Take a Quiz, Test Your Knowledge', 'wporg-learn' ); ?>
			</a>
		</div>
	<?php endif; ?>
	<div class="wp-block-button is-style-secondary-full-width">
		<a
			class="wp-block-button__link"
			href="https://learn.wordpress.org/social-learning/"
			style="border-radius:5px"
		>
			<?php esc_html_e( 'Join a Social Learning Space', 'wporg-learn' ); ?>
		</a>
	</div>
</div>
