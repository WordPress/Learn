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

// Get information about the post title.
$cpt_object = get_post_type_object( get_post_type( get_queried_object() ) );

if ( 'lesson-plan' === get_post_type() ) {
	array_push( $crumbs, array(
		'label' => ucfirst( $cpt_object->labels->name ),
		'url'   => home_url( $cpt_object->has_archive ),
	) );
}

array_push( $crumbs, array(
	'label' => get_the_title(),
	'url'   => '',
) );
?>

<div class="clearfix">
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
