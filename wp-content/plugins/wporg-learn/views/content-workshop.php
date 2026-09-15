<?php

/**
 * Content for auto-generated workshop posts.
 *
 * ⚠️ Note that if the template for the workshop post type changes, this will need to be updated as well.
 *
 * phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped -- This builds the post_content
 * string that gets saved to the database, not page output, so the blurbs are assembled into block
 * markup here and must not be escaped. The blurbs come through `sanitize_application_text()`, which
 * resolves character references, strips tags and swaps square brackets for parentheses, and the
 * assembled content is filtered by `wp_filter_post_kses()` on save for authors without
 * `unfiltered_html`, which includes every form submitter.
 */

/** @var array $blurbs */

?>

<!-- wp:paragraph {"placeholder":"<?php esc_html_e( 'Describe what the workshop is about.', 'wporg-learn' ); ?>"} -->
<p><?php echo $blurbs['description']; ?></p>
<!-- /wp:paragraph -->

<!-- wp:heading {"level":"2"} -->
<h2><?php esc_html_e( 'Learning outcomes', 'wporg-learn' ); ?></h2>
<!-- /wp:heading -->

<!-- wp:list {"ordered":true,"className":"workshop-page_list"} -->
<ol class="workshop-page_list">
	<?php echo $blurbs['learning-objectives']; ?>
</ol>
<!-- /wp:list -->

<!-- wp:heading {"level":"2"} -->
<h2><?php esc_html_e( 'Comprehension questions', 'wporg-learn' ); ?></h2>
<!-- /wp:heading -->

<!-- wp:list {"ordered":true,"className":"workshop-page_list"} -->
<ol class="workshop-page_list">
	<?php echo $blurbs['comprehension-questions']; ?>
</ol>
<!-- /wp:list -->

<!-- wp:heading {"className":"transcript"} -->
<h2 class="transcript" id="transcript"><?php esc_html_e( 'Transcript', 'wporg-learn' ); ?></h2>
<!-- /wp:heading -->

<!-- wp:paragraph {"placeholder":"<?php esc_html_e( 'Copy the transcript from Otter. See handbook for instructions.', 'wporg-learn' ); ?>"} -->
<p></p>
<!-- /wp:paragraph -->
