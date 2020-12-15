<?php
$taxonomies = array(
	array(
		'label' => get_taxonomy_labels( get_taxonomy( 'audience' ) )->singular_name,
		'terms' => get_terms( array( 'taxonomy' => 'audience' ) ),
	),
	array(
		'label' => get_taxonomy_labels( get_taxonomy( 'duration' ) )->singular_name,
		'terms' => get_terms( array( 'taxonomy' => 'duration' ) ),
	),
	array(
		'label' => get_taxonomy_labels( get_taxonomy( 'instruction_type' ) )->singular_name,
		'terms' => get_terms( array( 'taxonomy' => 'instruction_type' ) ),
	),
	array(
		'label' => get_taxonomy_labels( get_taxonomy( 'level' ) )->singular_name,
		'terms' => get_terms( array( 'taxonomy' => 'level' ) ),
	),
);

$current_term = get_query_var( 'term' );
?>

<div class="lesson-plan-filters col-3">
	<h3 class="h4"><?php esc_html_e( 'Filter Lesson Plans', 'wporg-learn' ); ?></h3>

	<?php foreach ( $taxonomies as $txnmy ) : ?>
		<h4 class="h5"><?php echo esc_html( $txnmy['label'] ); ?></h4>
		<ul>
			<?php foreach ( $txnmy['terms'] as $trm ) : ?>
				<li>
					<a
						href="<?php echo esc_url( get_term_link( $trm->term_id ) ); ?>"
						class="<?php echo ( $trm->slug === $current_term ) ? 'current' : ''; ?>"
					>
						<?php echo esc_html( $trm->name ); ?>
					</a>
				</li>
			<?php endforeach; ?>
		</ul>
	<?php endforeach; ?>
</div>
