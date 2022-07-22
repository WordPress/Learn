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
		<a href="#" onclick="window.print()" class="lp-sidebar-print" aria-label="<?php esc_attr_e( 'Print View', 'wporg-learn' ); ?>">
			<?php esc_html_e( 'Print Lesson Plan', 'wporg-learn' ); ?>
		</a>
		<ul>
			<?php
			foreach ( wporg_learn_get_lesson_plan_taxonomy_data( get_the_ID(), 'single' ) as $detail ) {
				if ( ! empty( $detail['value'] ) ) {
					include locate_template( 'template-parts/component-taxonomy-item.php' );
				}
			}
			?>
		</ul>

		<ul class="lp-links">
			<?php if ( $post->slides_view_url ) : ?>
				<li>
					<a href="<?php echo esc_attr( $post->slides_view_url ); ?>" target="_blank">
						<span class="dashicons dashicons-admin-page"></span>
						<?php esc_html_e( 'View Lesson Plan Slides', 'wporg-learn' ); ?>
					</a>
				</li>
			<?php endif; ?>
			<?php if ( $post->slides_download_url ) : ?>
				<li>
					<a href="<?php echo esc_attr( $post->slides_download_url ); ?>">
						<span class="dashicons dashicons-download"></span>
						<?php esc_html_e( 'Download Lesson Slides', 'wporg-learn' ); ?>
					</a>
				</li>
			<?php endif; ?>
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
