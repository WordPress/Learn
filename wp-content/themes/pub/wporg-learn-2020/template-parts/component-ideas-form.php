<?php
/**
 * Template part for displaying the idea submission form.
 *
 * @link https://codex.wordpress.org/Template_Hierarchy
 *
 * @package WPBBP
 */

?>

<h3 class="h4"><?php esc_html_e( 'Submit an Idea', 'wporg-learn' ); ?></h3>

<?php if( is_user_logged_in() ) { ?>

	<form class="contact-form card" method="get" action="<?php echo esc_url( get_post_type_archive_link( 'lesson-plan' ) ); ?>">

		<p>
			<textarea name="idea_description" class="textarea" rows="6" placeholder="<?php esc_attr_e( 'Desribe your idea...', 'wporg-learn' ); ?>"></textarea><br/>
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

		<input type="submit" value="<?php esc_attr_e( 'Submit', 'wporg-learn' ); ?>" class="button button-primary button-large" />
	</form>

<?php } else { ?>

	<div class="card">
		<?php esc_html_e( 'You must be logged in to submit ideas.', 'wporg-learn' ); ?>
	</div>
<?php } ?>
