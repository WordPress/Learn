<?php
/**
 * Template part for displaying the filter component.
 */

$buckets = array(
	array(
		'label' => __( 'Series', 'wporg-learn' ),
		'name'  => 'series',
		'items' => get_terms( array(
			'taxonomy' => 'wporg_workshop_series',
			'fields'   => 'id=>name',
		) ),
	),
	array(
		'label' => __( 'Topic', 'wporg-learn' ),
		'name'  => 'topic',
		'items' => get_terms( array(
			'taxonomy' => 'topic',
			'fields'   => 'id=>name',
		) ),
	),
	array(
		'label' => __( 'Language', 'wporg-learn' ),
		'name'  => 'language',
		'items' => \WPOrg_Learn\Post_Meta\get_available_workshop_locales( 'video_language', 'native' ),
	),
	array(
		'label' => __( 'Subtitles', 'wporg-learn' ),
		'name'  => 'captions',
		'items' => \WPOrg_Learn\Post_Meta\get_available_workshop_locales( 'video_caption_language', 'native' ),
	),
	array(
		'label' => __( 'WordPress Version', 'wporg-learn' ),
		'name'  => 'wp_version',
		'items' => get_terms( array(
			'taxonomy' => 'wporg_wp_version',
			'fields'   => 'id=>name',
		) ),
	),
);
?>

<form id="filters" class="filter-form" method="get" action="<?php echo esc_url( get_post_type_archive_link( 'wporg_workshop' ) ); ?>">
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
						data-placeholder="<?php esc_attr_e( 'Select', 'wporg-learn' ); ?>"
					>
						<option value="">Select</option>
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
			<a href="<?php echo esc_url( get_post_type_archive_link( 'wporg_workshop' ) ); ?>" class="clear-filters">
				<?php esc_html_e( 'Clear All Filters', 'wporg-learn' ); ?>
			</a>
		</div>
	</div>
</form>
