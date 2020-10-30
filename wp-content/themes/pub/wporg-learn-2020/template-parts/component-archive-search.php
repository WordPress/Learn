<?php
$search_query = filter_input( INPUT_GET, 'search' );
$search_label = get_post_type_object( get_post_type() )->labels->search_items;
?>
<div class="search-form--is-inline search-form--is-muted search-form--has-border search-form--has-medium-text">
	<form role="search" method="get" class="search-form">
		<label for="archive-search" class="screen-reader-text"><?php echo esc_html( _x( 'Search for:', 'label', 'wporg-learn' ) ); ?></label>
		<input type="search" id="archive-search" class="search-field" placeholder="<?php echo esc_attr( $search_label ); ?>" value="<?php echo esc_attr( $search_query ); ?>" name="search" />
		<button class="button button-primary button-search">
			<i class="dashicons dashicons-search"></i>
			<span class="screen-reader-text"><?php echo esc_html( $search_label ); ?></span>
		</button>
	</form>
</div>
