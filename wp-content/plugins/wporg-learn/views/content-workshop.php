<?php

/**
 * Content for auto-generated workshop posts.
 *
 * ⚠️ Note that if the template for the workshop post type changes, this will need to be updated as well.
 *
 * phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped -- The data from this file is being saved to
 * the database rather than output; therefore it should be validated rather than escaped. It's validated by
 * `validate_workshop_application_form_submission()`, which strips all HTML tags.
 */

/** @var array $blurbs */

?>

<?php echo $blurbs['description']; ?>

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

<!-- wp:list {"className":"workshop-page_list"} -->
<ul class="workshop-page_list">
	<?php echo $blurbs['comprehension-questions']; ?>
</ul>
<!-- /wp:list -->
