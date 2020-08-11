<?php
$terms = get_terms( array(
	'taxonomy' => 'wporg_lesson_category',
	'hide_empty' => false,
) );

$current_term = get_query_var( 'term' ) ?: 'all';
?>

<div class="wp-filter">
    <ul class="filter-links">
        <li>
	        <a href="<?php echo esc_url( get_post_type_archive_link( 'lesson-plan' ) ) ?>" class="<?php echo ( 'all' === $current_term ) ? 'current' : ''; ?>">
		        <?php _e( 'All', 'wporg-learn' ); ?>
	        </a>
        </li>
	    <?php foreach ( $terms as $term ) : ?>
	        <li>
	            <a href="<?php echo get_term_link( $term, 'wporg_lesson_category' ); ?>" class="<?php echo ( $term->slug === $current_term ) ? 'current' : ''; ?>">
		            <?php echo esc_html( $term->name ); ?>
	            </a>
	        </li>
	    <?php endforeach; ?>
    </ul>
</div>
