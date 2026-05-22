<?php
/**
 * Render callback for the wporg/activity-kit-card block.
 *
 * @var \WP_Block $block
 */

namespace WPOrg_Learn\View\Blocks\Activity_Kit_Card;

defined( 'WPINC' ) || die();

$post_id = $block->context['postId'] ?? 0;

if ( ! $post_id ) {
	return '';
}

$post      = get_post( $post_id );
$title     = get_the_title( $post_id );
$permalink = get_permalink( $post_id );
$excerpt   = get_the_excerpt( $post );

$duration  = get_post_meta( $post_id, '_activity_duration', true );
$zip_url   = get_post_meta( $post_id, '_activity_zip_url', true );

$level_terms = wp_get_post_terms( $post_id, 'level', array( 'fields' => 'names' ) );
$level_name  = ! is_wp_error( $level_terms ) && ! empty( $level_terms ) ? $level_terms[0] : '';

$thumbnail_html = '';
if ( has_post_thumbnail( $post_id ) ) {
	$thumbnail_html = get_the_post_thumbnail( $post_id, 'medium', array(
		'style' => 'width:100%;height:100%;object-fit:cover;',
		'alt'   => esc_attr( $title ),
	) );
}
?>
<div class="wporg-activity-kit-card">
	<div class="wporg-activity-kit-card__image" style="aspect-ratio:16/9;overflow:hidden;">
		<?php if ( $thumbnail_html ) : ?>
			<a href="<?php echo esc_url( $permalink ); ?>" tabindex="-1" aria-hidden="true">
				<?php echo $thumbnail_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			</a>
		<?php else : ?>
			<div class="wporg-activity-kit-card__image-placeholder" style="width:100%;height:100%;background:#f0f0f0;"></div>
		<?php endif; ?>
	</div>

	<div class="wporg-activity-kit-card__body">
		<h3 class="wporg-activity-kit-card__title">
			<a href="<?php echo esc_url( $permalink ); ?>"><?php echo esc_html( $title ); ?></a>
		</h3>

		<?php if ( $excerpt ) : ?>
			<p class="wporg-activity-kit-card__excerpt" style="-webkit-line-clamp:3;display:-webkit-box;-webkit-box-orient:vertical;overflow:hidden;">
				<?php echo esc_html( $excerpt ); ?>
			</p>
		<?php endif; ?>

		<?php if ( $duration || $level_name ) : ?>
			<div class="wporg-activity-kit-card__meta">
				<?php if ( $duration ) : ?>
					<span class="wporg-activity-kit-card__duration"><?php echo esc_html( $duration ); ?></span>
				<?php endif; ?>
				<?php if ( $level_name ) : ?>
					<span class="wporg-activity-kit-card__level"><?php echo esc_html( $level_name ); ?></span>
				<?php endif; ?>
			</div>
		<?php endif; ?>

		<div class="wporg-activity-kit-card__actions">
			<a class="wporg-activity-kit-card__view-btn button button-secondary"
			   href="<?php echo esc_url( $permalink ); ?>">
				<?php esc_html_e( 'View', 'wporg-learn' ); ?>
			</a>
			<?php if ( $zip_url ) : ?>
				<a class="wporg-activity-kit-card__download-btn button button-primary"
				   href="<?php echo esc_url( $zip_url ); ?>"
				   data-post-id="<?php echo absint( $post_id ); ?>">
					<?php esc_html_e( 'Download', 'wporg-learn' ); ?>
				</a>
			<?php endif; ?>
		</div>
	</div>
</div>
