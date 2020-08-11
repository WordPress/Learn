<?php
/**
 * Template part for display author information
 *
 * @link https://codex.wordpress.org/Template_Hierarchy
 *
 * @package WPBBP
 */

$args = wp_parse_args( $args, array( 'class' => '' ) );
$presenter = $args['presenter'];

?>

<?php if ( $presenter ) : ?>
	<div class="workshop-presenter <?php echo esc_attr( $args['class'] ); ?> ">
		<div>
			<?php echo get_avatar( $presenter->ID, 56, '', '', array( 'class' => 'workshop-presenter_profile' ) ); ?>
		</div>
		<div>
			<div class="workshop-presenter_name"><?php echo esc_html( $presenter->display_name ); ?></div>
			<?php if ( ! empty( $presenter->user_nicename ) ) : ?>
				<a class="workshop-presenter_handle" href="<?php printf( 'https://profiles.wordpress.org/%s/', esc_attr( $presenter->user_login ) ); ?>">
					<?php
					printf(
						'@%s',
						esc_html( $presenter->user_nicename )
					);
					?>
				</a>
			<?php endif; ?>
		</div>
	</div>
<?php endif; ?>
