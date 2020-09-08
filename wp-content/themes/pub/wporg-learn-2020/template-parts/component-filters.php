<?php
/**
 * Template part for displaying the filter component
 *
 * @link https://codex.wordpress.org/Template_Hierarchy
 *
 * @package WPBBP
 */

$example_items = array(
	array(
		'label' => 'Beginner',
		'value' => 'beginner',
	),
	array(
		'label' => 'Not Beginner',
		'value' => 'no_beginner',
	),
);

$buckets = array(
	__( 'Category 1', 'wporg-learn' ) => $example_items,
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
		<form id="filters" class="js-filter-drawer-form" method="post">
			<div class="filter-drawer">
				<div class="row gutters buttons">
					<div class="col-12">
						<button type="submit" disabled="disabled" class="js-apply-filters-toggle button button-large button-secondary"><?php esc_html_e( 'Apply Filters', 'wporg-learn' ); ?></button>
						<button type="button" class="js-clear-filters-toggle button button-large button-secondary"><?php esc_html_e( 'Clear', 'wporg-learn' ); ?></button>
					</div>
				</div>
			<div class="row">
			<?php foreach ( $buckets as $key => $value ) : ?>
				<div class="col-3 filter-group">
					<h4><?php echo esc_html( $key ); ?></h4>
					<ol class="feature-group">
						<?php foreach ( $value as $item ) : ?>
							<?php get_template_part( 'template-parts/component', 'filter-item', $item ); ?>
						<?php endforeach; ?>
					</ol>
				</div>
			<?php endforeach; ?>
			</div>
		</form>
	</div>
</div>
