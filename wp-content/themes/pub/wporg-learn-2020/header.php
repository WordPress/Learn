<?php
/**
 * The header for our theme.
 *
 * This is the template that displays all of the <head> section and everything up until <div id="content">
 *
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
 *
 * @package WPBBP
 */

namespace WordPressdotorg\Forums;

global $wporg_global_header_options;
if ( !isset( $wporg_global_header_options['in_wrapper'] ) )
	$wporg_global_header_options['in_wrapper'] = '';
$wporg_global_header_options['in_wrapper'] .= '<a class="skip-link screen-reader-text" href="#content">' . esc_html__( 'Skip to content', 'wporg-forums' ) . '</a>';
wporg_get_global_header();

?>

<div id="page" class="site">
	<a class="skip-link screen-reader-text" href="#main"><?php esc_html_e( 'Skip to content', 'wporg-forums' ); ?></a>

	<div id="content">
		<header id="masthead" class="site-header <?php echo is_front_page() ? 'home' : ''; ?>" role="banner">
			<div class="site-branding">
				<?php
				if ( is_front_page() ) {
				?>
				<h1 class="site-title"><a href="<?php echo esc_url(home_url('/')); ?>" rel="home"><?php _ex('Learn WordPress', 'Site title', 'wporg-forums'); ?></a></h1>

				<p class="site-description">
					<?php
					/* Translators: subhead */
					_e('Whether you&#8217;re a first-time blogger or seasoned developer, there&#8217;s always more to learn. From community members all over the world, these vast resources will help you learn more about WordPress and teach it to others.', 'wporg-forums');
					?>
				</p>

			    <form role="search" method="get" class="search-form" action="<?php esc_url( home_url( '/' ) ) ?>">
			        <label>
			            <span class="screen-reader-text"><?php _e('Search for:', 'wporg-forums' ) ?></span>
			            <input type="search" class="search-field" placeholder="<?php esc_attr_e( 'Search a teaching resource', 'wporg-forums' )?>" value="<?php get_search_query() ?>" name="s" />
			        </label>
			        <button type="submit" class="search-submit button button-primary button-search"><i class="dashicons dashicons-search"></i><span class="screen-reader-text"><?php esc_attr_e( 'Search', 'wporg-forums' ) ?></span></button>
			    </form>

				<?php
				} elseif ( is_page() ) {
				?>
				<h1 class="site-title"><a href="<?php echo esc_url( get_the_permalink() ); ?>" rel="home"><?php the_title(); ?></a></h1>
				<?php
				} else {
				?>
					<p class="site-title"><a href="<?php echo esc_url(home_url('/')); ?>" rel="home"><?php _ex('Learn WordPress', 'Site title', 'wporg-forums'); ?></a></p>
					<nav id="site-navigation" class="main-navigation" role="navigation">
						<button
							class="menu-toggle dashicons dashicons-arrow-down-alt2"
							aria-controls="primary-menu"
							aria-expanded="false"
							aria-label="<?php esc_attr_e( 'Primary Menu', 'wporg-learn' ); ?>"
						>
						</button>

						<div id="primary-menu" class="menu">
							<?php
							wp_nav_menu( array(
								'theme_location' => 'primary',
								'menu_id'        => 'primary-menu',
							) );
							?>
						</div>
					</nav><!-- #site-navigation -->
				<?php } ?>
			</div><!-- .site-branding -->
		</header><!-- #masthead -->
