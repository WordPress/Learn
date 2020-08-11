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
		<span><?php echo esc_html( $detail['values'] ); ?></span>
	</strong>
</li>
