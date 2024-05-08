<?php
/**
 * Title: Learning Pathways Page content
 * Slug: wporg-learn-2024/learning-pathways-page-content
 * Inserter: no
 */

// Get all the terms for the learning-pathways taxonomy.
$learning_pathways = get_terms(
	array(
		'taxonomy'   => 'learning-pathways',
		'hide_empty' => false,
	)
);

?>

<pre><?php print_r( $learning_pathways ); ?></pre>
