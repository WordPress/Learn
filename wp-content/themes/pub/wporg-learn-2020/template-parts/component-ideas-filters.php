<?php
/**
 * Template part for displaying the filter component.
 */

$buckets = array(
	array(
		'label' => __( 'Status', 'wporg-learn' ),
		'name'  => 'status',
		'items' => get_terms( array(
			'taxonomy' => 'wporg_idea_status',
			'fields'   => 'id=>name',
		) ),
	),
	array(
		'label' => __( 'Type', 'wporg-learn' ),
		'name'  => 'idea-type',
		'items' => get_terms( array(
			'taxonomy' => 'wporg_idea_type',
			'fields'   => 'id=>name',
		) ),
	),
);
?>

<form id="filters" class="filter-form" method="get" action="<?php echo esc_url( get_post_type_archive_link( 'wporg_idea' ) ); ?>">
	<div class="row between">
		<?php foreach ( $buckets as $bucket ) :
			if ( empty( $bucket['items'] ) ) :
				continue;
			endif;
			?>
			<div class="filter-group col-2">
				<label for="<?php echo esc_attr( $bucket['name'] ); ?>" class="filter-group-label">
					<?php echo esc_html( $bucket['label'] ); ?>
					<select
						id="<?php echo esc_attr( $bucket['name'] ); ?>"
						class="filter-group-select"
						name="<?php echo esc_attr( $bucket['name'] ); ?>"
						style="width: 100%;"
						data-placeholder="<?php esc_attr_e( 'Any', 'wporg-learn' ); ?>"
					>
						<option value=""><?php esc_html_e( 'Any', 'wporg-learn' ); ?></option>
						<?php foreach ( $bucket['items'] as $item_value => $item_label ) : ?>
							<option
								value="<?php echo esc_attr( $item_value ); ?>"
								<?php selected( $item_value, filter_input( INPUT_GET, $bucket['name'] ) ); ?>
							>
								<?php if ( in_array( $bucket['name'], array( 'language', 'captions' ) ) ) :
									printf(
										'%s [%s]',
										esc_html( $item_label ),
										esc_html( $item_value ),
									);
								else :
									echo esc_html( $item_label );
								endif; ?>
							</option>
						<?php endforeach; ?>
					</select>
				</label>
			</div>
		<?php endforeach; ?>
		<div class="filter-buttons col-2">
			<button type="submit" class="button button-large button-secondary">
				<?php esc_html_e( 'Apply Filters', 'wporg-learn' ); ?>
			</button>
			<a href="<?php echo esc_url( get_post_type_archive_link( 'wporg_idea' ) ); ?>" class="clear-filters">
				<?php esc_html_e( 'Clear All Filters', 'wporg-learn' ); ?>
			</a>
		</div>
	</div>
</form>
