<?php
$taxonomies = array(
	array(
		'label'   => get_taxonomy_labels( get_taxonomy( 'audience' ) )->singular_name,
		'terms'   => get_terms( array( 'taxonomy' => 'audience' ) ),
		'name'    => 'audience',
		'current' => filter_input( INPUT_GET, 'audience', FILTER_VALIDATE_INT, FILTER_REQUIRE_ARRAY ) ?: array(),
	),
	array(
		'label'   => get_taxonomy_labels( get_taxonomy( 'duration' ) )->singular_name,
		'terms'   => get_terms( array( 'taxonomy' => 'duration' ) ),
		'name'    => 'duration',
		'current' => filter_input( INPUT_GET, 'duration', FILTER_VALIDATE_INT, FILTER_REQUIRE_ARRAY ) ?: array(),
	),
	array(
		'label'   => get_taxonomy_labels( get_taxonomy( 'level' ) )->singular_name,
		'terms'   => get_terms( array( 'taxonomy' => 'level' ) ),
		'name'    => 'level',
		'current' => filter_input( INPUT_GET, 'level', FILTER_VALIDATE_INT, FILTER_REQUIRE_ARRAY ) ?: array(),
	),
	array(
		'label'   => get_taxonomy_labels( get_taxonomy( 'instruction_type' ) )->singular_name,
		'terms'   => get_terms( array( 'taxonomy' => 'instruction_type' ) ),
		'name'    => 'type',
		'current' => filter_input( INPUT_GET, 'type', FILTER_VALIDATE_INT, FILTER_REQUIRE_ARRAY ) ?: array(),
	),
	array(
		'label'   => get_taxonomy_labels( get_taxonomy( 'wporg_wp_version' ) )->singular_name,
		'terms'   => get_terms( array( 'taxonomy' => 'wporg_wp_version' ) ),
		'name'    => 'wp_version',
		'current' => filter_input( INPUT_GET, 'wp_version', FILTER_VALIDATE_INT, FILTER_REQUIRE_ARRAY ) ?: array(),
	),
);
?>

<form class="sidebar-filters col-3" method="get" action="<?php echo esc_url( get_post_type_archive_link( 'lesson-plan' ) ); ?>">
	<h3 class="h4"><?php esc_html_e( 'Filter Lesson Plans', 'wporg-learn' ); ?></h3>

	<div class="filter-buttons">
		<button type="submit" class="button button-large button-secondary">
			<?php esc_html_e( 'Apply Filters', 'wporg-learn' ); ?>
		</button>
		<a href="<?php echo esc_url( get_post_type_archive_link( 'lesson-plan' ) ); ?>" class="clear-filters">
			<?php esc_html_e( 'Clear All Filters', 'wporg-learn' ); ?>
		</a>
	</div>

	<?php foreach ( $taxonomies as $txnmy ) : ?>
		<h4 class="h5"><?php echo esc_html( $txnmy['label'] ); ?></h4>
		<ul>
			<?php foreach ( $txnmy['terms'] as $trm ) : ?>
				<li>
					<label for="<?php echo esc_attr( "{$trm->term_id}-{$trm->slug}" ); ?>">
						<input
							id="<?php echo esc_attr( "{$trm->term_id}-{$trm->slug}" ); ?>"
							type="checkbox"
							name="<?php echo esc_attr( $txnmy['name'] ); ?>[]"
							value="<?php echo esc_attr( $trm->term_id ); ?>"
							<?php checked( in_array( $trm->term_id, $txnmy['current'], true ) ); ?>
						/>
						<?php echo esc_html( $trm->name ); ?>
					</label>
				</li>
			<?php endforeach; ?>
		</ul>
	<?php endforeach; ?>

	<h4 class="h5">
		<?php echo esc_html( get_taxonomy_labels( get_taxonomy( 'wporg_lesson_plan_series' ) )->singular_name ); ?>
	</h4>
	<ul>
		<?php foreach ( get_terms( array( 'taxonomy' => 'wporg_lesson_plan_series' ) ) as $trm ) : ?>
			<li>
				<label for="<?php echo esc_attr( "{$trm->term_id}-{$trm->slug}" ); ?>">
					<input
						id="<?php echo esc_attr( "{$trm->term_id}-{$trm->slug}" ); ?>"
						type="radio"
						name="series"
						value="<?php echo esc_attr( $trm->term_id ); ?>"
						<?php
						checked(
							$trm->term_id,
							filter_input( INPUT_GET, 'series', FILTER_VALIDATE_INT )
						);
						?>
					/>
					<?php echo esc_html( $trm->name ); ?>
				</label>
			</li>
		<?php endforeach; ?>
	</ul>
</form>
