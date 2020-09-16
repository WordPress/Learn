<div class="search-form--is-inline search-form--is-muted search-form--has-border search-form--has-medium-text">
	<form role="search" method="get" class="search-form">
		<label for="workshop-search" class="screen-reader-text"><?php echo esc_html( _x( 'Search for:', 'label', 'wporg-learn' ) ); ?></label>
		<input type="search" id="workshop-search" class="search-field" placeholder="<?php echo esc_attr_e( 'Search Workshops', 'wporg-learn' ); ?>" value="<?php the_search_query(); ?>" name="search" />
		<button class="button button-primary button-search">
			<i class="dashicons dashicons-search"></i>
			<span class="screen-reader-text"><?php esc_html_e( 'Search Workshops', 'wporg-learn' ); ?></span>
		</button>
	</form>
</div>
