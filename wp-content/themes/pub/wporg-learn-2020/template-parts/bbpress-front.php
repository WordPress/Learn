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
			<span class="dashicons dashicons-sos"></span>
			<h3><?php esc_html_e( 'Welcome to Support', 'wporg-learn' ); ?></h3>
			<p><?php esc_html_e( 'Our community-based Support Forums are a great place to learn, share, and troubleshoot.', 'wporg-learn' ); ?></p>
			<p><?php esc_html_e( '<a href="https://wordpress.org/support/welcome/">Get started</a>', 'wporg-learn' ); ?></p>
		</div>
	</div>
	<div>
		<div class="info-box">
			<span class="dashicons dashicons-portfolio"></span>
			<h3><?php esc_html_e( 'Documentation', 'wporg-learn' ); ?></h3>
			<p><?php esc_html_e( 'Your first stop where you\'ll find information on everything from installing to creating plugins.', 'wporg-learn' ); ?></p>
			<p><?php esc_html_e( '<a href="https://codex.wordpress.org/">Explore documentation</a>', 'wporg-learn' ); ?></p>
		</div>
	</div>
	<div>
		<div class="info-box">
			<span class="dashicons dashicons-hammer"></span>
			<h3><?php esc_html_e( 'Get Involved', 'wporg-learn' ); ?></h3>
			<p><?php esc_html_e( 'The Support Handbook is great for tips, tricks, and advice regarding giving the best support possible.', 'wporg-learn' ); ?></p>
			<p><?php esc_html_e( '<a href="https://make.wordpress.org/support/handbook/">Explore the Handbook</a>', 'wporg-learn' ); ?></p>
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
