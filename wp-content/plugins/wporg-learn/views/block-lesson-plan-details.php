<?php

namespace WPOrg_Learn\View\Blocks;

defined( 'WPINC' ) || die();

/**
 * @var array $details
 */

?>

<?php if ( get_post_type() === 'lesson-plan' && ! empty( $details ) ) : ?>
	<ul class="wp-block-wporg-learn-lesson-plan-details">
		<?php
		foreach ( $details as $detail ) {
			if ( ! empty( $detail['value'] ) ) { ?>
				<li>
					<strong><?php echo esc_html( $detail['label'] ); ?></strong>
					<span>
						<?php
						$i = 0;
						foreach ( $detail['value'] as $key => $value ) {
							$url = trailingslashit( site_url() ) . 'lesson-plans/?' . $detail['slug'] . '[]=' . $key;

							if ( 0 < $i ) {
								echo ', ';
							}

							echo '<a href="' . esc_attr( $url ) . '">' . esc_html( $value ) . '</a>';
							$i++;
						}
						?>
					</span>
				</li>
				<?php
			}
		}
		?>
	</ul>
<?php endif; ?>
