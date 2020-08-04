<?php
/**
 * Template part for displaying bbPress topics on the front page.
 *
 * @link https://codex.wordpress.org/Template_Hierarchy
 *
 * @package WPBBP
 */

?>

<?php do_action( 'bbp_before_main_content' ); ?>

<?php do_action( 'bbp_template_notices' ); ?>

<section class="three-up" id="forum-welcome">
	<div>
		<div class="info-box">
					<span class="dashicons
					<?php
					/* translators: dashicon class name for 'Welcome to Support' section. Do not translate into your own language. */
					esc_attr_e( 'dashicons-sos', 'wporg-forums' );
					?>
					"></span>
			<h3><?php _e( 'Welcome to Support', 'wporg-forums' ); ?></h3>
			<p><?php _e( 'Our community-based Support Forums are a great place to learn, share, and troubleshoot.', 'wporg-forums' ); ?></p>
			<p><?php _e( '<a href="https://wordpress.org/support/welcome/">Get started</a>', 'wporg-forums' ); ?></p>
		</div>
	</div>
	<div>
		<div class="info-box">
					<span class="dashicons
					<?php
					/* translators: dashicon class name for 'Documentation' section. Do not translate into your own language. */
					esc_attr_e( 'dashicons-portfolio', 'wporg-forums' );
					?>
					"></span>
			<h3><?php _e( 'Documentation', 'wporg-forums' ); ?></h3>
			<p><?php _e( 'Your first stop where you\'ll find information on everything from installing to creating plugins.', 'wporg-forums' ); ?></p>
			<p><?php _e( '<a href="https://codex.wordpress.org/">Explore documentation</a>', 'wporg-forums' ); ?></p>
		</div>
	</div>
	<div>
		<div class="info-box">
					<span class="dashicons
					<?php
					/* translators: dashicon class name for 'Get Involved' section. Do not translate into your own language. */
					esc_attr_e( 'dashicons-hammer', 'wporg-forums' );
					?>
					"></span>
			<h3><?php _e( 'Get Involved', 'wporg-forums' ); ?></h3>
			<p><?php _e( 'The Support Handbook is great for tips, tricks, and advice regarding giving the best support possible.', 'wporg-forums' ); ?></p>
			<p><?php _e( '<a href="https://make.wordpress.org/support/handbook/">Explore the Handbook</a>', 'wporg-forums' ); ?></p>
		</div>
	</div>
</section>

<hr />

<section>
	<?php bbp_get_template_part( 'content', 'archive-forum' ); ?>

	<div id="viewdiv">
		<ul id="views">
			<?php wporg_support_get_views(); ?>
		</ul>
	</div><!-- #viewdiv -->
</section>

<?php do_action( 'bbp_after_main_content' ); ?>
