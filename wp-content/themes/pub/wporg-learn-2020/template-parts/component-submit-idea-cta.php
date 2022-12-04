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
	<a class="button button-primary button-large" href="https://learn.wordpress.org/tutorial-presenter-application/">
		<span aria-hidden="true"><?php esc_html_e( 'Apply to present a tutorial', 'wporg-learn' ); ?></span>
		<span class="screen-reader-text"><?php esc_html_e( 'Apply to present a tutorial', 'wporg-learn' ); ?></span>
	</a>
	<a class="button button-secondary button-large" href="https://github.com/WordPress/Learn/issues/new?assignees=&labels=Awaiting+Triage%2C+Needs+Subject+Matter+Expert&template=topic-idea.md&title=Topic+Idea%3A+TOPIC+TITLE" target="_blank" rel="noreferrer noopener">
		<span aria-hidden="true"><?php esc_html_e( 'Submit a topic idea', 'wporg-learn' ); ?></span>
		<span class="screen-reader-text"><?php esc_html_e( 'Submit a topic idea', 'wporg-learn' ); ?></span>
	</a>
</section>
