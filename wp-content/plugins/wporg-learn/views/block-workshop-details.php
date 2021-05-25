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
			<?php foreach ( $fields as $key => $value ) : ?>
				<li>
					<b><?php echo esc_html( $key ); ?></b>
					<span><?php echo esc_html( $value ); ?></span>
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
		</ul>
	<?php endif; ?>

	<?php if ( ! empty( $quiz_url ) ) : ?>
		<div class="wp-block-button is-style-primary-full-width">
			<a class="wp-block-button__link" href="<?php echo esc_attr( $quiz_url ); ?>" style="border-radius:5px">
				<?php esc_html_e( 'Take a Quiz, Test Your Knowledge', 'wporg-learn' ); ?>
			</a>
		</div>
	<?php endif; ?>
</div>
