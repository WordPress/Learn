<?php
/**
 * Template part for display other contributor information
 *
 * @link https://codex.wordpress.org/Template_Hierarchy
 *
 * @package WPBBP
 */

$args              = wp_parse_args( $args, array( 'class' => '' ) );
$other_contributor = $args['other_contributor'];

?>

<?php if ( $other_contributor ) : ?>
	<div class="workshop-other-contributor <?php echo esc_attr( $args['class'] ); ?> ">
		<div>
			<div class="workshop-other-contributor_name"><?php echo esc_html( $other_contributor->display_name ); ?></div>
			<?php if ( ! empty( $other_contributor->user_nicename ) ) : ?>
				<a class="workshop-other-contributor_handle" href="<?php printf( 'https://profiles.wordpress.org/%s/', esc_attr( $other_contributor->user_login ) ); ?>">
					<?php
					printf(
						'@%s',
						esc_html( $other_contributor->user_nicename )
					);
					?>
				</a>
			<?php endif; ?>
		</div>
	</div>
<?php endif; ?>
