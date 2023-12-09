<?php
/**
 * Template part for displaying a submit idea CTA
 *
 * @link https://codex.wordpress.org/Template_Hierarchy
 *
 * @package WPBBP
 */

$args = wp_parse_args( $args );

?>

<section class="submit-idea-cta">
	<?php if ( isset( $args['icon'] ) ) : ?>
		<div aria-hidden="true" class="content-icon"><span class="dashicons dashicons-<?php echo esc_attr( $args['icon'] ); ?>"></span></div>
	<?php endif; ?>
	<h2><?php esc_html_e( 'Have an idea for new content? Let us know!', 'wporg-learn' ); ?></h2>
	<div class="buttons">
		<a class="button button-primary button-large" href="https://learn.wordpress.org/tutorial-presenter-application/">
			<?php esc_html_e( 'Apply to present a tutorial', 'wporg-learn' ); ?>
		</a>
		<a class="button button-secondary button-large" href=" https://github.com/WordPress/Learn/issues/new/choose" target="_blank" rel="noreferrer noopener">
			<?php esc_html_e( 'Submit a topic idea', 'wporg-learn' ); ?>
		</a>
	</div>
</section>
