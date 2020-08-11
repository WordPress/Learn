<?php
/**
 * Template part for display author information
 *
 * @link https://codex.wordpress.org/Template_Hierarchy
 *
 * @package WPBBP
 */

$author = wporg_get_workshop_author_query_var();

if( $author ) {

?>

<div class="workshop-author">
	<div>
		<?php echo get_avatar( $author->ID , 56, '', '', array ( "class" => 'workshop-author_profile' ) ); ?>
	</div>
	<div>
		<div class="workshop-author_name"><?php echo $author->display_name; ?></div>
		<?php if( ! empty( $author->nickname ) ) : ?>
			<a class="workshop-author_handle" href="" target="_blank">
				<?php echo $author->nickname; ?>
			</a>
		<?php endif; ?>
	</div>
</div>

<?php } ?>