<?php
/**
 * Title: Single Activity Kit Content
 * Slug: wporg-learn-2024/single-activity-kit-content
 * Inserter: no
 *
 * @package WPOrg_Learn
 */

$kit_id       = get_the_ID();
$duration     = get_post_meta( $kit_id, '_activity_duration', true );
$zip_id       = (int) get_post_meta( $kit_id, '_activity_zip_id', true );
// Link at the counting endpoint, not the file; route on ID since slugs may not URL-encode cleanly.
$download_url = ( $zip_id && wp_get_attachment_url( $zip_id ) ) ? \WPOrg_Learn\Activity_Kit_REST\get_download_url( $kit_id ) : '';

$guide_pdf_id  = (int) get_post_meta( $kit_id, '_activity_guide_pdf_id', true );
$slides_pdf_id = (int) get_post_meta( $kit_id, '_activity_slides_pdf_id', true );
$guide_url     = $guide_pdf_id ? wp_get_attachment_url( $guide_pdf_id ) : '';
$slides_url    = $slides_pdf_id ? wp_get_attachment_url( $slides_pdf_id ) : '';

$level_terms = wp_get_post_terms( $kit_id, 'level', array( 'fields' => 'names' ) );
$topic_terms = wp_get_post_terms( $kit_id, 'topic', array( 'fields' => 'names' ) );

$archive_url = get_post_type_archive_link( 'activity_kit' );

// ZIP file size.
$zip_path = $zip_id ? get_attached_file( $zip_id ) : '';
$zip_size = ( $zip_path && file_exists( $zip_path ) ) ? size_format( filesize( $zip_path ) ) : '';

// Build dynamic "what's inside" description for the download box.
if ( $guide_url && $slides_url ) {
	$download_desc = __( 'This download includes a facilitator guide and presentation slide deck.', 'wporg-learn' );
} elseif ( $guide_url ) {
	$download_desc = __( 'This download includes a facilitator guide.', 'wporg-learn' );
} elseif ( $slides_url ) {
	$download_desc = __( 'This download includes a presentation slide deck.', 'wporg-learn' );
} else {
	$download_desc = __( 'All files included as a single .zip download from the WordPress Media Library.', 'wporg-learn' );
}

// SVG icons (matching @wordpress/icons viewBox).
$icon_file     = '<svg width="16" height="16" viewBox="-2 -2 24 24" fill="currentColor" aria-hidden="true" focusable="false"><path d="M13 2H6C4.9 2 4 2.9 4 4v12c0 1.1.9 2 2 2h8c1.1 0 2-.9 2-2V7l-3-5zm-1 1.5L14.5 7H12V3.5zM14 16H6V4h5v4h3v8z"/></svg>';
$icon_desktop  = '<svg width="16" height="16" viewBox="-2 -2 24 24" fill="currentColor" aria-hidden="true" focusable="false"><path d="M20 3H0v11h20V3zM18 12H2V5h16v7zM9 16h2v1H9zm-3 2h8v1H6z"/></svg>';
$icon_download = '<svg width="16" height="16" viewBox="-2 -2 24 24" fill="currentColor" aria-hidden="true" focusable="false"><path d="M10 14.5l-5-5 1.1-1.1 3.15 3.15V3h1.5v9.55l3.15-3.15L15 9.5l-5 5zM3 17h14v-1.5H3V17z"/></svg>';
$icon_back     = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><polyline points="15 18 9 12 15 6"/></svg>';
$icon_clock    = '<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>';
$icon_calendar = '<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>';
$icon_file_lg  = '<svg width="20" height="20" viewBox="-2 -2 24 24" fill="currentColor" aria-hidden="true" focusable="false"><path d="M13 2H6C4.9 2 4 2.9 4 4v12c0 1.1.9 2 2 2h8c1.1 0 2-.9 2-2V7l-3-5zm-1 1.5L14.5 7H12V3.5zM14 16H6V4h5v4h3v8z"/></svg>';
$icon_desk_lg  = '<svg width="20" height="20" viewBox="-2 -2 24 24" fill="currentColor" aria-hidden="true" focusable="false"><path d="M20 3H0v11h20V3zM18 12H2V5h16v7zM9 16h2v1H9zm-3 2h8v1H6z"/></svg>';
?>

<!-- Page header: title + meta row -->
<div class="wporg-activity-kit-header">
	<h1 class="wporg-activity-kit-title"><?php the_title(); ?></h1>

	<div class="wporg-activity-kit-meta">
		<?php if ( $duration ) : ?>
			<span class="wporg-activity-kit-meta__duration">
				<?php echo $icon_clock; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Static SVG markup. ?>
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

		<?php if ( ! is_wp_error( $level_terms ) && ! empty( $level_terms ) ) : ?>
			<span class="wporg-activity-kit-meta__level">
				<?php echo esc_html( implode( ', ', $level_terms ) ); ?>
			</span>
		<?php endif; ?>

		<?php if ( ! is_wp_error( $topic_terms ) && ! empty( $topic_terms ) ) : ?>
			<span class="wporg-activity-kit-meta__topics">
				<?php echo esc_html( implode( ', ', $topic_terms ) ); ?>
			</span>
		<?php endif; ?>

		<span class="wporg-activity-kit-meta__updated">
			<?php echo $icon_calendar; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Static SVG markup. ?>
			<time datetime="<?php echo esc_attr( get_the_modified_date( 'Y-m-d', $kit_id ) ); ?>">
				<?php
				printf(
					/* translators: %s: date the activity kit was last updated, e.g. "May 15, 2025" */
					esc_html__( 'Updated %s', 'wporg-learn' ),
					esc_html( get_the_modified_date( 'F j, Y', $kit_id ) )
				);
				?>
			</time>
		</span>
	</div>
</div>

<!-- Action row: back link + download button -->
<div class="wporg-activity-kit-action-row">
	<a class="wporg-activity-kit-action-row__back" href="<?php echo esc_url( $archive_url ); ?>">
		<?php echo $icon_back; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Static SVG markup. ?>
		<?php esc_html_e( 'Back to Activity Library', 'wporg-learn' ); ?>
	</a>
	<?php if ( $download_url ) : ?>
		<a class="wporg-activity-kit-action-row__download wp-block-button__link"
			href="<?php echo esc_url( $download_url ); ?>">
			<?php echo $icon_download; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Static SVG markup. ?>
			<?php esc_html_e( 'Download kit', 'wporg-learn' ); ?>
		</a>
	<?php endif; ?>
</div>

<!-- Kit Preview: PDF tab toggle -->
<?php if ( $guide_url || $slides_url ) : ?>
<div class="wporg-activity-kit-pdf-section" data-post-id="<?php echo absint( $kit_id ); ?>">
	<div class="wporg-activity-kit-section-label"><?php esc_html_e( 'Kit Preview', 'wporg-learn' ); ?></div>

	<div class="wporg-activity-kit-pdf-tabs">
		<div class="wporg-activity-kit-pdf-tabs__header">
			<div class="wporg-activity-kit-pdf-tabs__nav" role="tablist" aria-label="<?php esc_attr_e( 'Kit preview', 'wporg-learn' ); ?>">
				<?php if ( $guide_url ) : ?>
					<button class="wporg-activity-kit-pdf-tabs__tab is-active"
							role="tab"
							aria-selected="true"
							aria-controls="tab-guide"
							data-tab="guide">
						<?php echo $icon_file; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Static SVG markup. ?>
						<?php esc_html_e( 'Facilitator Guide', 'wporg-learn' ); ?>
					</button>
				<?php endif; ?>
				<?php if ( $slides_url ) : ?>
					<button class="wporg-activity-kit-pdf-tabs__tab<?php echo $guide_url ? '' : ' is-active'; ?>"
							role="tab"
							aria-selected="<?php echo $guide_url ? 'false' : 'true'; ?>"
							aria-controls="tab-slides"
							data-tab="slides">
						<?php echo $icon_desktop; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Static SVG markup. ?>
						<?php esc_html_e( 'Slide Deck', 'wporg-learn' ); ?>
					</button>
				<?php endif; ?>
			</div>

			<a class="wporg-activity-kit-pdf-tabs__dl-btn"
				href="<?php echo esc_url( $guide_url ? $guide_url : $slides_url ); ?>"
				data-guide-url="<?php echo esc_url( $guide_url ); ?>"
				data-slides-url="<?php echo esc_url( $slides_url ); ?>">
				<?php echo $icon_download; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Static SVG markup. ?>
				<?php esc_html_e( 'Download PDF', 'wporg-learn' ); ?>
			</a>
		</div>

		<?php if ( $guide_url ) : ?>
			<div id="tab-guide"
				class="wporg-activity-kit-pdf-tabs__panel is-active"
				role="tabpanel">
				<iframe data-src="<?php echo esc_url( $guide_url ); ?>"
						title="<?php esc_attr_e( 'Facilitator Guide PDF', 'wporg-learn' ); ?>"></iframe>
			</div>
		<?php endif; ?>

		<?php if ( $slides_url ) : ?>
			<div id="tab-slides"
				class="wporg-activity-kit-pdf-tabs__panel<?php echo $guide_url ? '' : ' is-active'; ?>"
				role="tabpanel"
				<?php echo $guide_url ? 'hidden' : ''; ?>>
				<iframe data-src="<?php echo esc_url( $slides_url ); ?>"
						title="<?php esc_attr_e( 'Slide Deck PDF', 'wporg-learn' ); ?>"></iframe>
			</div>
		<?php endif; ?>
	</div>
</div>

<script>
( function () {
	var container = document.querySelector( '.wporg-activity-kit-pdf-tabs' );
	if ( ! container ) return;

	var tabs   = container.querySelectorAll( '.wporg-activity-kit-pdf-tabs__tab' );
	var panels = container.querySelectorAll( '.wporg-activity-kit-pdf-tabs__panel' );
	var dlBtn  = container.querySelector( '.wporg-activity-kit-pdf-tabs__dl-btn' );

	// Chromium browsers (Chrome, Edge, Brave, Opera) support #toolbar=0 to hide
	// the native PDF viewer controls. Firefox and Safari don't, so we use
	// #view=FitH (PDF spec "fit width") which fixes Safari's zoomed-out default
	// view and also shows our tracked Download PDF link as a fallback.
	var isChromium  = typeof window.chrome !== 'undefined';
	var pdfFragment = isChromium ? '#toolbar=0' : '#view=FitH';

	container.querySelectorAll( 'iframe[data-src]' ).forEach( function ( iframe ) {
		iframe.src = iframe.getAttribute( 'data-src' ) + pdfFragment;
	} );

	if ( dlBtn && ! isChromium ) {
		dlBtn.style.display = 'inline-flex';
	}

	tabs.forEach( function ( tab ) {
		tab.addEventListener( 'click', function () {
			var target = tab.dataset.tab;

			tabs.forEach( function ( t ) {
				t.classList.remove( 'is-active' );
				t.setAttribute( 'aria-selected', 'false' );
			} );
			panels.forEach( function ( p ) {
				p.classList.remove( 'is-active' );
				p.setAttribute( 'hidden', '' );
			} );

			tab.classList.add( 'is-active' );
			tab.setAttribute( 'aria-selected', 'true' );

			var panel = document.getElementById( 'tab-' + target );
			if ( panel ) {
				panel.classList.add( 'is-active' );
				panel.removeAttribute( 'hidden' );
			}

			// Swap the PDF download link to match the active tab.
			if ( dlBtn ) {
				var url = 'guide' === target ? dlBtn.dataset.guideUrl : dlBtn.dataset.slidesUrl;
				if ( url ) dlBtn.href = url;
			}
		} );
	} );
} )();
</script>
<?php else : ?>
<p class="wporg-activity-kit-no-pdf"><?php esc_html_e( 'No PDF preview available yet.', 'wporg-learn' ); ?></p>
<?php endif; ?>

<?php
// ---- Feedback strip ----
// Resolve the feedback form URL: per-kit meta overrides global option.
$per_kit_feedback_url = get_post_meta( $kit_id, '_activity_feedback_url', true );
$global_feedback_url  = get_option( 'activity_kit_feedback_url', '' );
$feedback_url         = $per_kit_feedback_url ? $per_kit_feedback_url : $global_feedback_url;

if ( $feedback_url ) :
	$feedback_url   = esc_url( add_query_arg( 'kit', get_post_field( 'post_name', $kit_id ), $feedback_url ) );
	$kit_title      = get_the_title();
	$feedback_label = sprintf(
		/* translators: %s: activity kit title */
		esc_attr__( 'Share feedback about %s (opens in new tab)', 'wporg-learn' ),
		esc_attr( $kit_title )
	);
	$icon_external = '<svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true" focusable="false"><path d="M7 17L17 7M17 7H7M17 7v10"/></svg>';
	?>
<div class="wporg-activity-kit-feedback-strip" role="complementary" aria-label="<?php esc_attr_e( 'Activity kit feedback', 'wporg-learn' ); ?>">
	<p class="wporg-activity-kit-feedback-strip__prompt">
		<?php
		printf(
			/* translators: %s: activity kit title */
			esc_html__( 'Did "%s" work well for your group?', 'wporg-learn' ),
			esc_html( $kit_title )
		);
		?>
	</p>
	<a
		href="<?php echo $feedback_url; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped via esc_url() above. ?>"
		class="wporg-activity-kit-feedback-strip__btn"
		target="_blank"
		rel="noopener noreferrer"
		aria-label="<?php echo $feedback_label; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped via esc_attr() above. ?>"
	>
		<?php esc_html_e( 'Share feedback', 'wporg-learn' ); ?>
		<?php echo $icon_external; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Static SVG markup. ?>
	</a>
</div>
<?php endif; ?>

<!-- Post content / description -->
<div class="wporg-activity-kit-description">
	<?php the_content(); ?>
</div>

<!-- What's included grid -->
<?php if ( $guide_url || $slides_url ) : ?>
<div class="wporg-activity-kit-included">
	<h2><?php esc_html_e( "What's included", 'wporg-learn' ); ?></h2>
	<div class="wporg-activity-kit-included__grid">
		<?php if ( $guide_url ) : ?>
			<div class="wporg-activity-kit-included__card">
				<span class="wporg-activity-kit-included__icon">
					<?php echo $icon_file_lg; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Static SVG markup. ?>
				</span>
				<div class="wporg-activity-kit-included__card-body">
					<h3><?php esc_html_e( 'Facilitator Guide', 'wporg-learn' ); ?></h3>
					<p><?php esc_html_e( 'Step-by-step instructions for running the session, including timing cues and discussion prompts.', 'wporg-learn' ); ?></p>
				</div>
			</div>
		<?php endif; ?>
		<?php if ( $slides_url ) : ?>
			<div class="wporg-activity-kit-included__card">
				<span class="wporg-activity-kit-included__icon">
					<?php echo $icon_desk_lg; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Static SVG markup. ?>
				</span>
				<div class="wporg-activity-kit-included__card-body">
					<h3><?php esc_html_e( 'Slide Deck', 'wporg-learn' ); ?></h3>
					<p><?php esc_html_e( 'Presentation slides for the full workshop as a PDF, ready to present or print.', 'wporg-learn' ); ?></p>
				</div>
			</div>
		<?php endif; ?>
	</div>
</div>
<?php endif; ?>

<!-- Download this kit -->
<?php if ( $download_url ) : ?>
<div class="wporg-activity-kit-download-box">
	<h2><?php esc_html_e( 'Download this kit', 'wporg-learn' ); ?></h2>
	<p><?php echo esc_html( $download_desc ); ?></p>
	<a class="wporg-activity-kit-download-box__btn"
		href="<?php echo esc_url( $download_url ); ?>">
		<?php echo $icon_download; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Static SVG markup. ?>
		<?php esc_html_e( 'Download kit', 'wporg-learn' ); ?>
	</a>
	<p class="wporg-activity-kit-download-box__note">
		<?php esc_html_e( 'Free', 'wporg-learn' ); ?>
		&middot; <?php esc_html_e( 'No account required', 'wporg-learn' ); ?>
		<?php if ( $zip_size ) : ?>
			&middot; <?php echo esc_html( $zip_size ); ?>
		<?php endif; ?>
	</p>
</div>
<?php endif; ?>
