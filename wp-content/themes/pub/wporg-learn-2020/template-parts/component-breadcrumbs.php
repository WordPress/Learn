<?php
/**
 * Template part for displaying breadcrumbs
 *
 * @link https://codex.wordpress.org/Template_Hierarchy
 *
 * @package WPBBP
 */

$crumbs = array(
	array(
		'label' => __( 'Learn Home', 'wporg-learn' ),
		'url'   => home_url(),
	),
);

if ( is_post_type_archive() ) {
	array_push( $crumbs, array(
		'label' => post_type_archive_title( '', false ),
		'url' => '',
	));
} elseif ( is_singular() ) {
	if ( is_single() ) {
		$cpt_object = get_post_type_object( get_post_type() );

		array_push($crumbs, array(
			'label' => $cpt_object->label,
			'url'   => home_url( $cpt_object->has_archive ),
		));
	}
	array_push( $crumbs, array(
		'label' => get_the_title(),
		'url'   => '',
	) );
} elseif ( is_search() ) {
	array_push( $crumbs, array(
		'label' => get_search_query(),
		'url' => '',
	));
}
?>

<div role="navigation" aria-label="<?php esc_attr_e( 'Breadcrumbs', 'wporg-learn' ); ?>" class="clearfix site-breadcrumbs">
	<div class="bbp-breadcrumb">
	<?php
	$crumb_length = count( $crumbs );

	for ( $x = 0; $x < $crumb_length; $x++ ) {
		if ( empty( $crumbs[ $x ]['url'] ) ) {
			echo '<span class="bbp-breadcrumb-current">' . esc_html( $crumbs[ $x ]['label'] ) . '</span>';
		} else {
			echo '<a href="' . esc_url( $crumbs[ $x ]['url'] ) . '" class="bbp-breadcrumb-home">';
			echo esc_html( $crumbs[ $x ]['label'] );
			echo '</a>';
		}

		if ( $x < $crumb_length - 1 ) {
			echo ' <span class="bbp-breadcrumb-sep">»</span> ';
		}
	}
	?>
	</div>
</div>
