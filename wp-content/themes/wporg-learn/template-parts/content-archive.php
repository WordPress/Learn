<?php
/**
 * Template part for displaying single posts in an archive list.
 *
 * @link https://codex.wordpress.org/Template_Hierarchy
 *
 * @package WPBBP
 */

$topic_list = [];

if( wporg_post_type_is_workshop() ) {
	$topics = get_taxonomy_values( get_the_ID(), 'topic' );

	if( !empty( $topics ) ) {
		$topic_list = explode( ',', $topics );
	}
}

?>

<div class="lp-item <?php echo ( wporg_post_type_is_workshop() ? 'lp-item--full' : '' ) ?>">
	<div class="lp-item-wrap <?php echo ( wporg_post_type_is_workshop() ? 'lp-item-wrap--split' : '' ) ?>">
		<h2 class="h4"><a href="<?php echo esc_url( get_the_permalink() ); ?>"><?php the_title(); ?></a></h2>
		<div class="lp-body">
			<div>
				<p class="lp-excerpt <?php echo ( wporg_post_type_is_workshop() ? 'lp-excerpt--short' : '' ) ?>"><?php echo esc_attr( get_the_excerpt() ); ?></p>	
				<?php if( !empty( $topic_list ) ) : ?>
					<ul class="lp-topics <?php echo count( $topic_list ) > 4 ? 'lp-topics--split' : '' ; ?>">
					<?php foreach( $topic_list as $topic ) : ?>
						<li><?php echo $topic; ?></li> 
					<?php endforeach; ?>
					</ul>
				<?php endif; ?>
			</div>
			<div class="lp-details">
				<ul class="lp-details-list <?php echo wporg_post_type_is_lesson() ? 'lp-details-list--split' : '' ?>">
					<?php 
						foreach( wporg_get_custom_taxonomies( get_the_ID() ) as $detail ) {
							if( !empty( $detail[ 'values' ] ) ) {
								include( locate_template( 'template-parts/component-taxonomy-item.php' ) ); 
							}			
						}
					?>
				</ul>
			</div>
		</div>
	</div>
</div>
