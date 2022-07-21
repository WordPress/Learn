<?php
/**
 * The sidebar containing the lesson plans widget area.
 *
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
 *
 * @package WordPressdotorg\Theme
 */

namespace WordPressdotorg\Theme;

?>
<aside class="lp-sidebar">
	<div class="lp-details">
		<ul class="taxonomies-meta">
			<?php
			foreach ( wporg_learn_get_lesson_plan_taxonomy_data( get_the_ID(), 'single' ) as $detail ) {
				if ( ! empty( $detail['value'] ) ) {
					include locate_template( 'template-parts/component-taxonomy-item.php' );
				}
			}
			?>
		</ul>

		<ul class="lp-actions">
			<?php if ( $post->slides_view_url ) : ?>
				<li>
					<a class="lp-action" href="<?php echo esc_attr( $post->slides_view_url ); ?>" target="_blank">
						<span class="dashicons dashicons-admin-page" aria-hidden="true"></span>
						<?php esc_html_e( 'View Lesson Plan Slides', 'wporg-learn' ); ?>
					</a>
				</li>
			<?php endif; ?>
			<?php if ( $post->slides_download_url ) : ?>
				<li>
					<a class="lp-action" href="<?php echo esc_attr( $post->slides_download_url ); ?>">
						<span class="dashicons dashicons-download" aria-hidden="true"></span>
						<?php esc_html_e( 'Download Lesson Slides', 'wporg-learn' ); ?>
					</a>
				</li>
			<?php endif; ?>
			<li>
				<button class="lp-action" onclick="window.print()">
					<span class="dashicons dashicons-printer" aria-hidden="true"></span>
					<?php esc_html_e( 'Print Lesson Plan', 'wporg-learn' ); ?>
				</button>
			</li>
		</ul>
	</div>
	<div class="lp-sidebar-dynamic">
		<?php
		if ( is_active_sidebar( 'wporg-learn-lesson-plans' ) ) :
			dynamic_sidebar( 'wporg-learn-lesson-plans' );
		endif;
		?>
	</div>
</aside>
