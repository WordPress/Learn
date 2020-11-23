<?php

namespace WPOrg_Learn\View\Blocks;

defined( 'WPINC' ) || die();

/** @var array $fields */
/** @var string $quiz_url */
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
