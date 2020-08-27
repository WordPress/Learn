<?php

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
					<h4>Layout</h4>
					<ol class="feature-group">
						<li>
							<input type="checkbox" id="filter-id-grid-layout" value="grid-layout">
							<label for="filter-id-grid-layout">Grid Layout</label>
						</li>
												<li>
							<input type="checkbox" id="filter-id-one-column" value="one-column">
							<label for="filter-id-one-column">One Column</label>
						</li>
					</ol>
				</div>
				<div class="col-3 filter-group">
					<h4>Features</h4>
					<ol class="feature-group">
						<li>
							<input type="checkbox" id="filter-id-accessibility-ready" value="accessibility-ready">
							<label for="filter-id-accessibility-ready">Accessibility Ready</label>
						</li>
						<li>
							<input type="checkbox" id="filter-id-block-patterns" value="block-patterns">
							<label for="filter-id-block-patterns">Block Editor Patterns</label>
						<li>
							<input type="checkbox" id="filter-id-custom-menu" value="custom-menu">
							<label for="filter-id-custom-menu">Custom Menu</label>
						</li>
						<li>
							<input type="checkbox" id="filter-id-editor-style" value="editor-style">
							<label for="filter-id-editor-style">Editor Style</label>
						</li>
						<li>
							<input type="checkbox" id="filter-id-featured-image-header" value="featured-image-header">
							<label for="filter-id-featured-image-header">Featured Image Header</label>
						</li>
					</ol>
				</div>
				<div class="col-3 filter-group">
					<h4>Subject</h4>
					<ol class="feature-group">
						<li>
							<input type="checkbox" id="filter-id-blog" value="blog">
							<label for="filter-id-blog">Blog</label>
						</li>

						<li>
							<input type="checkbox" id="filter-id-portfolio" value="portfolio">
							<label for="filter-id-portfolio">Portfolio</label>
						</li>
					</ol>
				</div>
				<div class="col-3 filter-group">
					<h4>Subject</h4>
					<ol class="feature-group">
						<li>
							<input type="checkbox" id="filter-id-blog" value="blog">
							<label for="filter-id-blog">Blog</label>
						</li>
						<li>
							<input type="checkbox" id="filter-id-portfolio" value="portfolio">
							<label for="filter-id-portfolio">Portfolio</label>
						</li>
					</ol>
				</div>
			</div>
		</div>
	</div>
</div>