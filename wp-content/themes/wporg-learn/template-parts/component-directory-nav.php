<?php
/**
 * Template part for displaying navigation in archive.php.
 *
 * @link https://codex.wordpress.org/Template_Hierarchy
 *
 * @package WPBBP
 */

?>

<nav id="site-navigation" class="directory-navigation" role="navigation">
    <ul id="menu-theme-directory" class="menu">
        <li class="<?php echo ( wporg_post_type_is_lesson() ? "current" : "" ) ?>"><a href="<?php echo home_url( '/lesson-plans/' ); ?>"><?php _e( 'Lesson Plans', 'wporg-themes' ); ?></a></li>
        <li class="<?php echo ( wporg_post_type_is_workshop() ? "current" : "" ) ?>"><a href="<?php echo home_url( '/workshops/' ); ?>"><?php _e( 'Workshop Ideas', 'wporg-themes' ); ?></a></li>
    </ul>
</nav>