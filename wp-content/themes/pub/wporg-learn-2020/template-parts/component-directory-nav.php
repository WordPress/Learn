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
		<li class="<?php echo ( wporg_post_type_is_lesson() ? 'current' : '' ); ?>"><a href="<?php echo esc_url( home_url( '/lesson-plans/' ) ); ?>"><?php esc_html_e( 'Lesson Plans', 'wporg-learn' ); ?></a></li>
	</ul>
</nav>
