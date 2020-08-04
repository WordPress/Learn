<?php
/**
 * Template part for displaying page content in page.php.
 *
 * @link https://codex.wordpress.org/Template_Hierarchy
 *
 * @package WPBBP
 */

/**
 * Return the category url for filter bar
 *
 * @return string
 */
function get_cat_url( $str ){
    // We need to pluralize it to match the archive url
   return esc_url( home_url( get_post_type() . "s" . '/' . $str . '/' ) ) ;
}

/**
 * Returns whether we should should the selected class
 *
 * @return bool
 */
function is_selected( $str ) {
    return wporg_get_cat_or_default_slug() == $str;
}

/**
 * Returns the current class for navigation itme
 *
 * @return string
 */
function get_filter_class( $str ) {
    return ( is_selected( $str ) ? 'current' : '' );
}

$categories = wporg_get_filter_categories();

?>

<?php if( $categories ) : ?>
    <div class="wp-filter">
        <ul class="filter-links">
        <? foreach( $categories as $cat ) : ?>
            <li><a href="<?php echo get_cat_url( $cat->slug ); ?>" class="<?php echo get_filter_class( $cat->slug ) ?>"><?php _ex( $cat->name, 'themes', 'wporg-learn' ); ?></a></li>
        <? endforeach; ?>
        </ul>

    </div>
<?php endif; ?>


