<?php
/**
 * Template part for displaying the a filter item
 *
 * @link https://codex.wordpress.org/Template_Hierarchy
 *
 * @package WPBBP
 */

$args = wp_parse_args( $args );

?>

<li>
	<input type="checkbox" id="filter-id-grid-layout" value="<?php echo $args['value']; ?>">
	<label for="filter-id-grid-layout"><?php echo $args['label']; ?></label>
</li>