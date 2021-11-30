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
			foreach ( $detail['value'] as $id => $value ) {
				$url = trailingslashit( site_url() ) . 'lesson-plans/?' . $detail['slug'] . '[]=' . $id;

				if ( 0 < $i ) {
					echo ', ';
				}

				echo sprintf( '%1$s' . esc_attr( $url ) . '%2$s' . esc_html( $value ) . '%3$s', '<a href="', '">', '</a>' );
				$i++;
			}
			?>
		</span>
	</strong>
</li>
