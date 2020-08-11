<?php
/**
 * Template part for display author information
 *
 * @link https://codex.wordpress.org/Template_Hierarchy
 *
 * @package WPBBP
 */


$args = wp_parse_args( $args );
$author = $args[ 'author' ];
?>

<?php if( $author ) : ?>
<div class="workshop-author">
	<div>
		<?php echo get_avatar( $author->ID , 56, '', '', array ( "class" => 'workshop-author_profile' ) ); ?>
	</div>
	<div>
		<div class="workshop-author_name"><?php echo esc_html(  $author->display_name ); ?></div>
		<?php if( ! empty( $author->nickname ) ) : ?>
			<a class="workshop-author_handle" href="<?php echo esc_url( $author->user_url ); ?>" target="_blank">
				<?php echo esc_html( $author->nickname ); ?>
			</a>
		<?php endif; ?>
	</div>
</div>
<?php endif; ?>