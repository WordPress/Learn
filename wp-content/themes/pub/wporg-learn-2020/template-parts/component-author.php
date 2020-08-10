<?php
/**
 * Template part for display author information
 *
 * @link https://codex.wordpress.org/Template_Hierarchy
 *
 * @package WPBBP
 */

$display_name = get_the_author_meta( 'display_name' );
$nickname = get_the_author_meta( 'nickname' );
$user_url = get_the_author_meta( 'url' );
$url = get_avatar_url( get_the_author_meta( 'ID' ) );

?>

<div class="workshop-author">
	<div>
		<img class="workshop-author_profile" src="<?php echo $url; ?>" />
	</div>
	<div>
		<div class="workshop-author_name"><?php echo $display_name ?></div>
		<?php if( ! empty( $nickname ) ) : ?>
			<a class="workshop-author_handle" href="<?php echo $user_url; ?>" target="_blank"><?php echo get_the_author_meta( 'nickname' ); ?></a>
		<?php endif; ?>
	</div>
</div>