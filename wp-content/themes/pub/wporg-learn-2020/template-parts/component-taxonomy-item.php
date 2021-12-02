<?php
/**
 * Template part for displaying lesson/workshop details on the content-single part.
 *
 * @link https://codex.wordpress.org/Template_Hierarchy
 *
 * @package WPBBP
 */

?>

<li>
	<span class="dashicons dashicons-<?php echo esc_attr( $detail['icon'] ); ?>"></span>
	<span><?php echo esc_html( $detail['label'] ); ?></span>
	<strong>
		<span>
			<?php //echo esc_html( $detail['value'] ); ?>

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
