<?php
/**
 * Title: Learning Pathways Page content
 * Slug: wporg-learn-2024/learning-pathways-terms
 * Inserter: no
 */

/**
 * TODO https://github.com/WordPress/Learn/issues/2430
 * This is a temporary solution to display the learning pathways on the learning pathways page.
 * This will be replaced with a block in the future.
 */

$learning_pathways = get_terms(
	array(
		'taxonomy'   => 'learning-pathway',
		'hide_empty' => false,
	)
);

if ( ! empty( $learning_pathways ) && ! is_wp_error( $learning_pathways ) ) { ?>
	<ul>
		<?php foreach ( $learning_pathways as $learning_pathway ) { ?>
			<li>
				<a href="<?php echo esc_url( get_term_link( $learning_pathway ) ); ?>">
					<?php echo esc_html( $learning_pathway->name ); ?>
				</a>
			</li>
		<?php } ?>
	</ul>
<?php }
