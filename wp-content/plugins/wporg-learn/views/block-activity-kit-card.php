<?php
/**
 * Render callback for the wporg/activity-kit-card block.
 *
 * @var \WP_Block $block
 */

namespace WPOrg_Learn\View\Blocks\Activity_Kit_Card;

defined( 'WPINC' ) || die();

$kit_post_id = $block->context['postId'] ?? 0;

if ( ! $kit_post_id || 'activity_kit' !== get_post_type( $kit_post_id ) ) {
	return '';
}

$kit_post  = get_post( $kit_post_id );
$kit_title = get_the_title( $kit_post_id );
$permalink = get_permalink( $kit_post_id );
$excerpt   = get_the_excerpt( $kit_post );

$duration = get_post_meta( $kit_post_id, '_activity_duration', true );
$zip_id   = (int) get_post_meta( $kit_post_id, '_activity_zip_id', true );
$zip_url  = $zip_id ? wp_get_attachment_url( $zip_id ) : '';

// Route through the counting endpoint so card downloads are tracked too.
$download_url = $zip_url ? \WPOrg_Learn\Activity_Kit_REST\get_download_url( $kit_post_id ) : '';

$level_terms = wp_get_post_terms( $kit_post_id, 'level', array( 'fields' => 'names' ) );
$level_name  = ! is_wp_error( $level_terms ) && ! empty( $level_terms ) ? $level_terms[0] : '';

$thumbnail_html = '';
if ( has_post_thumbnail( $kit_post_id ) ) {
	$thumbnail_html = get_the_post_thumbnail(
		$kit_post_id,
		'medium',
		array(
			'style' => 'width:100%;height:100%;object-fit:cover;',
			'alt'   => esc_attr( $kit_title ),
		)
	);
}
?>
<div class="wporg-activity-kit-card">
	<div class="wporg-activity-kit-card__image" style="aspect-ratio:16/9;overflow:hidden;">
		<?php if ( $thumbnail_html ) : ?>
			<a href="<?php echo esc_url( $permalink ); ?>" tabindex="-1" aria-hidden="true">
				<?php echo $thumbnail_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Thumbnail HTML built by WordPress. ?>
			</a>
		<?php else : ?>
			<div class="wporg-activity-kit-card__image-placeholder" style="width:100%;height:100%;background:#f0f0f0;"></div>
		<?php endif; ?>
	</div>

	<div class="wporg-activity-kit-card__body">
		<h3 class="wporg-activity-kit-card__title">
			<a href="<?php echo esc_url( $permalink ); ?>"><?php echo esc_html( $kit_title ); ?></a>
		</h3>

		<?php if ( $excerpt ) : ?>
			<p class="wporg-activity-kit-card__excerpt" style="-webkit-line-clamp:3;display:-webkit-box;-webkit-box-orient:vertical;overflow:hidden;">
				<?php echo esc_html( $excerpt ); ?>
			</p>
		<?php endif; ?>

		<?php if ( $duration || $level_name ) : ?>
			<div class="wporg-activity-kit-card__meta">
				<?php if ( $duration ) : ?>
					<span class="wporg-activity-kit-card__duration">
					<?php
					if ( ctype_digit( (string) $duration ) ) {
						/* translators: %d: number of minutes */
						echo esc_html( sprintf( __( '%d mins', 'wporg-learn' ), (int) $duration ) );
					} else {
						echo esc_html( $duration );
					}
					?>
				</span>
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
			<?php if ( $download_url ) : ?>
				<a class="wporg-activity-kit-card__download-btn button button-primary"
					href="<?php echo esc_url( $download_url ); ?>">
					<?php esc_html_e( 'Download ↓', 'wporg-learn' ); ?>
				</a>
			<?php endif; ?>
		</div>
	</div>
</div>
