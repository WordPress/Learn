<?php
/**
 * Template for search form.
 *
 * @link https://codex.wordpress.org/Template_Hierarchy
 *
 * @package WPBBP
 */

$placeholder = isset( $args['placeholder'] ) ? $args['placeholder'] : _x( 'Search for a learning resource', 'placeholder', 'wporg-learn' );
?>
<form role="search" method="get" class="search-form" action="<?php echo esc_url( home_url( '/' ) ); ?>">
	<label for="s" class="screen-reader-text"><?php echo esc_html( _x( 'Search for:', 'label', 'wporg-learn' ) ); ?></label>
	<input
		type="search"
		id="s"
		class="search-field"
		placeholder="<?php echo esc_attr( $placeholder ); ?>"
		value="<?php the_search_query(); ?>"
		name="s"
	/>
	<button class="button button-primary button-search">
		<i class="dashicons dashicons-search"></i>
		<span class="screen-reader-text">
			<?php esc_html_e( 'Search', 'wporg-learn' ); ?>
		</span>
	</button>
</form>
