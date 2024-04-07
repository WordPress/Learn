<?php

namespace WPOrg_Learn\View\Blocks;

defined( 'WPINC' ) || die();

/**
 * @var array $details
 */
$is_updated = get_the_modified_date( "ymd" ) != get_the_date( "ymd" );

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
		<!-- Date and modified date -->
		<li>
			<b><?php esc_html_e('Published', 'wporg-learn'); ?></b>
			<span>
				<?php echo get_the_date(); ?>
			</span>
		</li>
		<?php if ($is_updated) : ?>
			<li>
				<b><?php esc_html_e('Updated', 'wporg-learn'); ?></b>
				<span>
					<?php echo get_the_modified_date(); ?>
				</span>
			</li>
		<?php endif; ?>
		<!-- END Date and modified date -->

	</ul>
<?php endif; ?>
