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
	<?php if( isset( $args['icon'] ) ) :?>
		<div class="content-icon"><span class="dashicons dashicons-<?php echo $args['icon']; ?>"></span></div>
	<?php endif; ?>
	<h2><?php _e( 'Have an Idea for a Workshop? Let us know!' ); ?></h2>
	<a class="button button-primary button-large" href="https://wordcampcentral.survey.fm/learn-wordpress-workshop-application"><?php _e( 'Submit an Idea' ); ?></a>
</section>