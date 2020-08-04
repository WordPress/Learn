<?php
/**
 * Template part for displaying breadcrumbs
 *
 * @link https://codex.wordpress.org/Template_Hierarchy
 *
 * @package WPBBP
 */

/**
 * Returns list of workshops based on slug
 * 
 * @return array|bool
 */
function get_workshop_from_slug( $slug ) {
    $args = array(
        'name'        => $slug,
        'post_type'   => 'workshop',
        'post_status' => 'publish',
        'numberposts' => 1
      );

    $workshop = get_posts( $args );

    return isset( $workshop[ 0 ] ) ? $workshop[ 0 ] : false;
}

/**
 * Returns whether we are viewing a lesson from a workshop
 * 
 * @param string $referer
 * @return bool
 */
function lesson_came_from_workshop( $referer ) {
    return wporg_post_type_is_lesson() && strrpos( $referer, 'workshop' );
}

$crumbs = [
    [
        'label' => __( 'Learn Home', 'wporg-learn' ),
        'url' => home_url()
    ]
];

$referer = wp_get_referer();

// If we came from a workshop, we want to modify the breadrumbs to bring us back to the workshop  
if( lesson_came_from_workshop( $referer ) ) {
    $workshop = get_workshop_from_slug( basename( $referer ) );

    if( $workshop ) {
        array_push( $crumbs, [
            'label' => __( 'Workshops', 'wporg-learn' ),
            'url' => get_post_type_archive_link( 'workshop' ) 
        ] );

        array_push( $crumbs, [
            'label' => $workshop->post_title,
            'url' => get_post_permalink( $workshop->ID )
        ] );
    }

} else {

    // Get information about the post title.
    $post_type = get_post_type_object( get_post_type( get_queried_object() ) );

    if( wporg_post_type_is_lesson() ){
        array_push( $crumbs, [
            'label' => ucfirst( $post_type->labels->name ),
            'url' => home_url( $post_type->has_archive  )
        ] );
    }
}

array_push( $crumbs, [
    'label' => get_the_title(),
    'url' => ''
] );
?>

<div class="clearfix">
    <div class="bbp-breadcrumb">        
    <?php 
        $crumb_length = count($crumbs);

        for( $x = 0; $x < $crumb_length; $x++ ) {
            if( empty( $crumbs[ $x ][ 'url' ] ) ) {
                echo '<span class="bbp-breadcrumb-current">' . $crumbs[ $x ][ 'label' ] . '</span>';
            } else {
                echo '<a href="' . $crumbs[ $x ][ 'url' ] .'" class="bbp-breadcrumb-home">';
                echo $crumbs[ $x ][ 'label' ];
                echo '</a>';
            }

            if( $x < $crumb_length - 1 ) {
                echo ' <span class="bbp-breadcrumb-sep">»</span> ';
            }   
        }
    ?>
    </div>
</div>
