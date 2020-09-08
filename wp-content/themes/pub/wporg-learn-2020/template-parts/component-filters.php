<?php
/**
 * Template part for displaying the filter component.
 */

$buckets = array(
	array(
		'label' => __( 'Language', 'wporg-learn' ),
		'name'  => 'language',
		'meta_key' => 'video_language',
		'items' => \WPOrg_Learn\Post_Meta\get_available_workshop_locales( 'video_language', 'native' ),
	),
	array(
		'label' => __( 'Captions', 'wporg-learn' ),
		'name'  => 'captions',
		'meta_key' => 'video_caption_language',
		'items' => \WPOrg_Learn\Post_Meta\get_available_workshop_locales( 'video_caption_language', 'native' ),
	),
);
?>

<div class="js-filter-drawer">
	<div class="wp-filter">
		<div class="row between center filter-drawer-controls">
			<a class="js-filter-drawer-toggle button button-large drawer-toggle" href="#"><?php esc_html_e( 'Filter Workshops', 'wporg-learn' ); ?></a>
			<div class="search-form--is-inline search-form--is-muted search-form--has-border search-form--has-medium-text">
				<?php get_search_form( array( 'placeholder' => __( 'Search Workshops', 'wporg-learn' ) ) ); ?>
			</div>
		</div>
		<form id="filters" class="js-filter-drawer-form" method="get">
			<div class="filter-drawer">
				<div class="row gutters buttons">
					<div class="col-12">
						<button type="submit" disabled="disabled" class="js-apply-filters-toggle button button-large button-secondary">
							<?php esc_html_e( 'Apply Filters', 'wporg-learn' ); ?>
						</button>
						<button type="button" class="js-clear-filters-toggle button button-large button-secondary">
							<?php esc_html_e( 'Clear', 'wporg-learn' ); ?>
						</button>
					</div>
				</div>
				<div class="row">
					<?php foreach ( $buckets as $bucket ) :
						if ( empty( $bucket['items'] ) ) :
							continue;
						endif;
						?>
						<div class="col-3 filter-group">
							<label for="<?php echo esc_attr( $bucket['name'] ); ?>"><?php echo esc_html( $bucket['label'] ); ?></label>
							<select
								id="<?php echo esc_attr( $bucket['name'] ); ?>"
								class="filter-bucket-select"
								name="<?php echo esc_attr( $bucket['name'] ); ?>"
							>
								<option value=""></option>
								<?php foreach ( $bucket['items'] as $item_value => $item_label ) : ?>
									<option
										value="<?php echo esc_attr( $item_value ); ?>"
										<?php selected( $item_value, filter_input( INPUT_GET, $bucket['name'] ) ); ?>
									>
										<?php echo esc_html( $item_label ); ?>
									</option>
								<?php endforeach; ?>
							</select>
						</div>
					<?php endforeach; ?>
				</div>
			</div>
		</form>
	</div>
</div>

<script>
	( function( $ ) {
		$( '.filter-bucket-select' ).select2();
	} )( jQuery );
</script>
