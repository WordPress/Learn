<?php
$search_query = filter_input( INPUT_GET, 'search' );

$pt = '';
if ( is_post_type_archive() ) {
	$pt = get_query_var( 'post_type' );
} else if ( is_tax() ) {
	$current_tax = get_taxonomy( get_query_var( 'taxonomy' ) );
	$obj_types = $current_tax->object_type ?? array();
	$pt = array_shift( $obj_types );
}
$search_label = get_post_type_object( $pt )->labels->search_items ?? '';

$form_action = get_post_type_archive_link( $pt );
?>
<div class="search-form--is-inline search-form--is-muted search-form--has-border search-form--has-medium-text">
	<form role="search" method="get" class="search-form" action="<?php echo esc_attr( $form_action ); ?>">
		<label for="archive-search" class="screen-reader-text">
			<?php echo esc_html( _x( 'Search for:', 'label', 'wporg-learn' ) ); ?>
		</label>
		<input
			type="search"
			id="archive-search"
			class="search-field"
			placeholder="<?php echo esc_attr( $search_label ); ?>"
			value="<?php echo esc_attr( $search_query ); ?>"
			name="search"
		/>
		<button class="button button-primary button-search">
			<i class="dashicons dashicons-search"></i>
			<span class="screen-reader-text"><?php echo esc_html( $search_label ); ?></span>
		</button>
	</form>
</div>
