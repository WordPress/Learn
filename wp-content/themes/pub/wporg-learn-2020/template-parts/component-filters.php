<?php

$exampleItems = array(
	array (
		'label' => 'Label',
		'value' => 'value'
	)
)

?>

<div class="show-filters">
	<div class="wp-filter">
		<div class="row between center wp-filter-controls">
			<a class="button button-large drawer-toggle" href="#"><span>Feature Filter</span></a>
			<div class="search-form">
				<label class="screen-reader-text" for="wp-filter-search-input">Search Themes</label>
				<input placeholder="Search workshops..." type="search" id="wp-filter-search-input" class="wp-filter-search">
			</div>
		</div>
		<div>
			<div class="filter-drawer">
				<div class="row gutters buttons">
					<div class="col-12">
						<button type="button" disabled="disabled" class="apply-filters button button-large button-secondary">Apply Filters<span></span></button>
						<button type="button" class="clear-filters button button-large button-secondary">Clear</button>
					</div>
				</div>

			<div class="filtered-by">
				<span>Filtering by:</span>
				<div class="tags"></div>
				<a href="#">Edit</a>
			</div>
			<div class="row">
				<div class="col-3 filter-group">
					<h4><?php esc_html_e( 'Category 1', 'wporg-learn' ); ?></h4>
					<ol class="feature-group">
						<?php foreach ( $exampleItems as $item ) : ?>
							<?php get_template_part( 'template-parts/component', 'filter-item', $item ); ?>
						<?php endforeach; ?>
					</ol>
				</div>
				<div class="col-3 filter-group">
					<h4><?php esc_html_e( 'Category 2', 'wporg-learn' ); ?></h4>
					<ol class="feature-group">
						<?php foreach ( $exampleItems as $item ) : ?>
							<?php get_template_part( 'template-parts/component', 'filter-item', $item ); ?>
						<?php endforeach; ?>
					</ol>
				</div>
				<div class="col-3 filter-group">
					<h4><?php esc_html_e( 'Category 3', 'wporg-learn' ); ?></h4>
					<ol class="feature-group">
						<?php foreach ( $exampleItems as $item ) : ?>
							<?php get_template_part( 'template-parts/component', 'filter-item', $item ); ?>
						<?php endforeach; ?>
					</ol>
				</div>
				<div class="col-3 filter-group">
					<h4><?php esc_html_e( 'Category 4', 'wporg-learn' ); ?></h4>
					<ol class="feature-group">
						<?php foreach ( $exampleItems as $item ) : ?>
							<?php get_template_part( 'template-parts/component', 'filter-item', $item ); ?>
						<?php endforeach; ?>
					</ol>
				</div>
			</div>
		</div>
	</div>
</div>