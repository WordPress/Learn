<?php
$terms = get_terms( array(
	'taxonomy'   => 'wporg_lesson_category',
	'hide_empty' => false,
) );

$current_term = get_query_var( 'term' ) ?: 'all';
?>

<div class="lesson-plan-filters col-3">
	<ul>
		<li>
			<a href="<?php echo esc_url( get_post_type_archive_link( 'lesson-plan' ) ); ?>" class="<?php echo ( 'all' === $current_term ) ? 'current' : ''; ?>">
				<?php esc_html_e( 'All', 'wporg-learn' ); ?>
			</a>
		</li>
		<?php foreach ( $terms as $t ) : ?>
			<li>
				<a href="<?php echo esc_url( get_term_link( $t, 'wporg_lesson_category' ) ); ?>" class="<?php echo ( $t->slug === $current_term ) ? 'current' : ''; ?>">
					<?php echo esc_html( $t->name ); ?>
				</a>
			</li>
		<?php endforeach; ?>
	</ul>
</div>
