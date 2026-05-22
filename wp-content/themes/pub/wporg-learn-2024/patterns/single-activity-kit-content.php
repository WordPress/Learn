<?php
/**
 * Title: Single Activity Kit Content
 * Slug: wporg-learn-2024/single-activity-kit-content
 * Inserter: no
 */

if ( 'activity_kit' !== get_post_type() ) {
	return;
}

$post_id   = get_the_ID();
$duration  = get_post_meta( $post_id, '_activity_duration', true );
$zip_url   = get_post_meta( $post_id, '_activity_zip_url', true );

$guide_pdf_id  = (int) get_post_meta( $post_id, '_activity_guide_pdf_id', true );
$slides_pdf_id = (int) get_post_meta( $post_id, '_activity_slides_pdf_id', true );
$guide_url     = $guide_pdf_id ? wp_get_attachment_url( $guide_pdf_id ) : '';
$slides_url    = $slides_pdf_id ? wp_get_attachment_url( $slides_pdf_id ) : '';

$level_terms = wp_get_post_terms( $post_id, 'level', array( 'fields' => 'names' ) );
$topic_terms = wp_get_post_terms( $post_id, 'topic', array( 'fields' => 'names' ) );

$archive_url = get_post_type_archive_link( 'activity_kit' );
?>

<!-- Breadcrumb -->
<nav class="wporg-activity-kit-breadcrumb" aria-label="<?php esc_attr_e( 'Breadcrumb', 'wporg-learn' ); ?>">
	<a href="<?php echo esc_url( $archive_url ); ?>"><?php esc_html_e( 'Activity Library', 'wporg-learn' ); ?></a>
	<span aria-hidden="true"> › </span>
	<span><?php the_title(); ?></span>
</nav>

<!-- Header: title + metadata -->
<div class="wporg-activity-kit-header">
	<h1 class="wporg-activity-kit-title"><?php the_title(); ?></h1>

	<div class="wporg-activity-kit-meta">
		<?php if ( $duration ) : ?>
			<span class="wporg-activity-kit-meta__duration"><?php echo esc_html( $duration ); ?></span>
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
	</div>
</div>

<!-- PDF preview tabs -->
<?php if ( $guide_url || $slides_url ) : ?>
<div class="wporg-activity-kit-pdf-tabs" data-post-id="<?php echo absint( $post_id ); ?>">
	<div class="wporg-activity-kit-pdf-tabs__nav" role="tablist">
		<?php if ( $guide_url ) : ?>
			<button class="wporg-activity-kit-pdf-tabs__tab is-active"
			        role="tab"
			        aria-selected="true"
			        aria-controls="tab-guide"
			        data-tab="guide">
				<?php esc_html_e( 'Facilitator Guide', 'wporg-learn' ); ?>
			</button>
		<?php endif; ?>
		<?php if ( $slides_url ) : ?>
			<button class="wporg-activity-kit-pdf-tabs__tab<?php echo $guide_url ? '' : ' is-active'; ?>"
			        role="tab"
			        aria-selected="<?php echo $guide_url ? 'false' : 'true'; ?>"
			        aria-controls="tab-slides"
			        data-tab="slides">
				<?php esc_html_e( 'Slide Deck', 'wporg-learn' ); ?>
			</button>
		<?php endif; ?>
	</div>

	<?php if ( $guide_url ) : ?>
		<div id="tab-guide"
		     class="wporg-activity-kit-pdf-tabs__panel<?php echo $guide_url ? ' is-active' : ''; ?>"
		     role="tabpanel">
			<iframe src="<?php echo esc_url( $guide_url ); ?>"
			        style="width:100%;height:520px;border:none;"
			        title="<?php esc_attr_e( 'Facilitator Guide PDF', 'wporg-learn' ); ?>"></iframe>
		</div>
	<?php endif; ?>

	<?php if ( $slides_url ) : ?>
		<div id="tab-slides"
		     class="wporg-activity-kit-pdf-tabs__panel<?php echo $guide_url ? '' : ' is-active'; ?>"
		     role="tabpanel"
		     <?php echo $guide_url ? 'hidden' : ''; ?>>
			<iframe src="<?php echo esc_url( $slides_url ); ?>"
			        style="width:100%;height:520px;border:none;"
			        title="<?php esc_attr_e( 'Slide Deck PDF', 'wporg-learn' ); ?>"></iframe>
		</div>
	<?php endif; ?>
</div>

<script>
( function () {
	var container = document.querySelector( '.wporg-activity-kit-pdf-tabs' );
	if ( ! container ) return;

	var tabs   = container.querySelectorAll( '.wporg-activity-kit-pdf-tabs__tab' );
	var panels = container.querySelectorAll( '.wporg-activity-kit-pdf-tabs__panel' );

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
		} );
	} );
} )();
</script>
<?php else : ?>
<p class="wporg-activity-kit-no-pdf"><?php esc_html_e( 'No PDF preview available yet.', 'wporg-learn' ); ?></p>
<?php endif; ?>

<!-- Post content / description -->
<div class="wporg-activity-kit-description">
	<?php the_content(); ?>
</div>

<!-- Download button -->
<?php if ( $zip_url ) : ?>
<div class="wporg-activity-kit-download">
	<a class="wp-block-button__link wporg-activity-kit-download__btn"
	   href="<?php echo esc_url( $zip_url ); ?>"
	   data-post-id="<?php echo absint( $post_id ); ?>"
	   data-track-download="1">
		<?php esc_html_e( 'Download Activity Kit', 'wporg-learn' ); ?>
	</a>
</div>

<script>
( function () {
	var btn = document.querySelector( '[data-track-download="1"]' );
	if ( ! btn ) return;

	btn.addEventListener( 'click', function ( e ) {
		var postId  = btn.dataset.postId;
		var href    = btn.href;
		var nonce   = ( window.wpApiSettings && window.wpApiSettings.nonce ) || '';

		fetch( '/wp-json/activity-kits/v1/track', {
			method: 'POST',
			headers: {
				'Content-Type': 'application/json',
				'X-WP-Nonce': nonce,
			},
			body: JSON.stringify( { post_id: parseInt( postId, 10 ), action: 'download' } ),
			keepalive: true,
		} ).catch( function () {} );
	} );
} )();
</script>
<?php endif; ?>
