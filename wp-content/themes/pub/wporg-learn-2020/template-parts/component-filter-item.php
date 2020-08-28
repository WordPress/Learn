<?php
/**
 * Template part for displaying the filter component item
 *
 * @link https://codex.wordpress.org/Template_Hierarchy
 *
 * @package WPBBP
 */

$args = wp_parse_args( $args );

?>

<li>
	<input type="checkbox" id="<?php echo esc_attr( $args['value'] ); ?>" name="<?php echo esc_attr( $args['value'] ); ?>" value="<?php echo esc_attr( $args['value'] ); ?>">
	<label for="<?php echo esc_attr( $args['value'] ); ?>"><?php echo esc_attr( $args['label'] ); ?></label>
</li>
