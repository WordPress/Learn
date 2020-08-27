<?php
/**
 * Template part for displaying the filter component
 *
 * @link https://codex.wordpress.org/Template_Hierarchy
 *
 * @package WPBBP
 */


$exampleItems = array(
	array (
		'label' => 'Beginner',
		'value' => 'beginner'
	),
	array (
		'label' => 'Not Beginner',
		'value' => 'no_beginner'
	)
);

$buckets = array(
	__( 'Category 1', 'wporg-learn' ) => $exampleItems
);

?>

<div class="show-filters">
	<div class="wp-filter">
		<div class="row between center wp-filter-controls">
			<a class="button button-large drawer-toggle" href="#"><span>Feature Filter</span></a>
			<form class="search-form">
				<label class="screen-reader-text" for="wp-filter-search-input">Search Workshop</label>
				<input name="keyword" placeholder="Search workshops..." type="search" id="wp-filter-search-input" class="wp-filter-search">
				<button type="submit">Search</button>
			</form>
		</div>
		<form id="filters" method="post">
			<div class="filter-drawer">
				<div class="row gutters buttons">
					<div class="col-12">
						<button type="submit" class="apply-filters button button-large button-secondary">Apply Filters<span></span></button>
						<button type="button" class="clear-filters button button-large button-secondary">Clear</button>
					</div>
				</div>

			<div class="filtered-by">
				<span>Filtering by:</span>
				<div class="tags"></div>
				<a href="#">Edit</a>
			</div>
			<div class="row">

			<?php foreach( $buckets as $key => $value  ) :?>
				<div class="col-3 filter-group">
					<h4><?php esc_html_e( $key ); ?></h4>
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