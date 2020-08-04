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
    <span class="dashicons dashicons-<?php echo $detail[ 'icon' ]; ?>"></span>
    <span><?php echo $detail[ 'label']; ?></span>
    <strong>
        <span><?php echo $detail[ 'values' ]; ?></span>
    </strong>
</li>