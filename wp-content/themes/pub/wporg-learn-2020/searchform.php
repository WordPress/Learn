<?php
/**
 * Template for search form.
 *
 * @link https://codex.wordpress.org/Template_Hierarchy
 *
 * @package WPBBP
 */

?>
<form role="search" method="get" class="search-form" action="<?php echo esc_url( home_url( '/' ) ); ?>">
	<label for="s" class="screen-reader-text"><?php echo esc_html( _x( 'Search for:', 'label', 'wporg-learn' ) ); ?></label>
	<?php
		$placeholder = _x( 'Search Lesson Plans and Workshop Ideas', 'placeholder', 'wporg-learn' );
	?>
	<input type="search" id="s" class="search-field" placeholder="<?php echo esc_attr( $placeholder ); ?>" value="<?php the_search_query(); ?>" name="s" />
	<?php if ( $project ) : ?>
	<input type="hidden" name="intext" value="<?php echo esc_attr( $project->prefixed_title ); ?>" />
	<?php endif; ?>
	<button class="button button-primary button-search"><i class="dashicons dashicons-search"></i><span class="screen-reader-text"><?php esc_html_e( 'Search Lesson Plans and Workshop Ideas', 'wporg-learn' ); ?></span></button>
</form>
