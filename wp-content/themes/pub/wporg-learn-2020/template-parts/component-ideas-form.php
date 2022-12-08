<?php
/**
 * Template part for displaying the idea submission form.
 *
 * @link https://codex.wordpress.org/Template_Hierarchy
 *
 * @package WPBBP
 */

// Process idea submission
$idea_submitted = false;
if ( isset( $_POST['idea-submitted'] ) && 'submitted' == $_POST['idea-submitted'] ) {
	$idea_submitted = wporg_process_submitted_idea( $_POST );
}

// Create action URL for submission form
if ( is_single() ) {
	$form_action = get_permalink();
} else {
	$form_action = get_post_type_archive_link( 'wporg_idea' );
}

// Make sure filtering variables are included in form action URL
$actions = array();

if ( isset( $_GET['status'] ) ) {
	$actions['status'] = $_GET['status'];
}

if ( isset( $_GET['idea-type'] ) ) {
	 $actions['idea-type'] = $_GET['idea-type'];
}

if ( isset( $_GET['ordering'] ) ) {
	 $actions['ordering'] = $_GET['ordering'];
}

$i = 0;
foreach ( $actions as $slug => $value ) {
	if ( $i > 0 ) {
		$form_action .= '&';
	} else {
		$form_action .= '?';
	}

	$form_action .= esc_attr( $slug ) . '=' . esc_attr( $value );

	$i++;
}

// Output thank you message after successful submission and nsure page refresh doesn't cause resubmission
if ( $idea_submitted ) { ?>
	<div class="notice notice-success notice-alt notice-idea-submitted">
		<p><?php esc_html_e( 'Thank you for submitting your content idea!', 'wporg-learn' ); ?></p>
	</div>
	<script>
		if ( window.history.replaceState ) {
			window.history.replaceState( null, '', window.location.href );
		}
	</script>
<?php } ?>

<div class="card">

	<h3 class="h4"><?php esc_html_e( 'Submit an idea', 'wporg-learn' ); ?></h3>

	<?php if ( is_user_logged_in() ) { ?>

		<form class="contact-form" method="post" action="<?php echo esc_url( $form_action ); ?>">

			<p>
				<?php esc_html_e( 'Is there a topic that you would like to see covered on Learn WordPress? Submit your idea here:', 'wporg-learn' ); ?>
			</p>

			<p>
				<textarea name="idea_description" class="textarea" rows="7" maxlength="1000" placeholder="<?php esc_attr_e( 'Describe your content idea...', 'wporg-learn' ); ?>"></textarea><br/>
				<small><em><?php eas_html_e( 'Limit: 1000 characters', 'wporg-learn' ); ?></em></small>
			</p>

			<p>
				<?php esc_html_e( 'Type: ', 'wporg-learn' ); ?>
				<select name="idea_type">
					<option value="tutorial"><?php esc_html_e( 'Tutorial', 'wporg-learn' ); ?></option>
					<option value="online-workshop"><?php esc_html_e( 'Online Workshop', 'wporg-learn' ); ?></option>
					<option value="course"><?php esc_html_e( 'Course', 'wporg-learn' ); ?></option>
					<option value="lesson-plan"><?php esc_html_e( 'Lesson Plan', 'wporg-learn' ); ?></option>
				</select>
			</p>

			<?php wp_nonce_field( 'submit_idea' ); ?>

			<input type="hidden" name="idea-submitted" value="submitted" />
			<input type="submit" value="<?php esc_attr_e( 'Submit', 'wporg-learn' ); ?>" class="button button-primary button-large" />

		</form>

	<?php } else {
		esc_html_e( 'You must be logged in to submit content ideas.', 'wporg-learn' );
	} ?>

</div>
