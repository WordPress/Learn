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
	<input type="checkbox" id="<?php echo $args['value']; ?>" name="<?php echo $args['value']; ?>" value="<?php echo $args['value']; ?>">
	<label for="<?php echo $args['value']; ?>"><?php echo $args['label']; ?></label>
</li>