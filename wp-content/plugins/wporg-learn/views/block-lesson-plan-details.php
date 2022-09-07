<?php

namespace WPOrg_Learn\View\Blocks;

defined( 'WPINC' ) || die();

/**
 * @global WP_Post $post
 * @var    array   $details
 */

?>

<ul class="wp-block-wporg-learn-lesson-plan-details">
	<?php
	foreach ( $details as $detail ) {
		if ( ! empty( $detail['value'] ) ) { ?>
			<li>
				<span class="dashicons dashicons-<?php echo esc_attr( $detail['icon'] ); ?>"></span>
				<span><?php echo esc_html( $detail['label'] ); ?></span>
				<strong>
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
				</strong>
			</li>
			<?php
		}
	}
	?>
</ul>