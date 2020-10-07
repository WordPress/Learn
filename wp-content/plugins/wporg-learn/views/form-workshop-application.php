<?php

namespace WPOrg_Learn\View\Form;

use WP_Error;
use function WordPressdotorg\Locales\get_locales_with_native_names;
use function WPOrg_Learn\Form\{ render_input_field, render_textarea_field };

defined( 'WPINC' ) || die();

/** @var string $state */
/** @var array $form */
/** @var WP_Error|null $errors */
/** @var array $error_fields */
/** @var array $messages */
/** @var array $audience */
/** @var string $audience_other */
/** @var array $experience_level */
/** @var string $experience_level_other */

$prefix = 'submission:';
?>

<?php if ( in_array( $state, array( 'new', 'error' ) ) ) : ?>
	<p>
		<?php
		printf(
			wp_kses_post( __( 'You are logged in as <a href="%1$s">%2$s</a>. <a href="%3$s">Log out?</a>', 'wporg-learn' ) ),
			esc_url( "https://profiles.wordpress.org/{$form['wporg-user-name']}/" ),
			esc_html( $form['wporg-user-name'] ),
			esc_url( wp_logout_url( apply_filters( 'the_permalink', get_permalink() ) ) )
		);
		?>
	</p>
	<form method="post" class="wp-block wporg-learn-workshop-application-form">
		<?php if ( ! empty( $messages ) ) : ?>
			<?php foreach ( $messages as $message ) : ?>
				<div class="notice notice-error">
					<?php echo wp_kses_post( wpautop( $message ) ); ?>
				</div>
			<?php endforeach; ?>
		<?php endif; ?>
		<fieldset class="section">
			<legend>
				<?php esc_html_e( 'Presenter Details', 'wporg-learn' ); ?>
			</legend>
			<p>
				<?php esc_html_e( 'The following data from your user profile will be used in this application:', 'wporg-learn' ); ?>
			</p>
			<?php
			echo render_input_field( array( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				'label'         => __( 'WordPress.org User Name', 'wporg-learn' ),
				'id'            => 'wporg-user-name',
				'value'         => $form['wporg-user-name'],
				'readonly'      => true,
			) );
			echo render_input_field( array( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				'label'         => __( 'First Name', 'wporg-learn' ),
				'id'            => 'first-name',
				'value'         => $form['first-name'],
				'readonly'      => true,
			) );
			echo render_input_field( array( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				'label'         => __( 'Last Name', 'wporg-learn' ),
				'id'            => 'last-name',
				'value'         => $form['last-name'],
				'readonly'      => true,
			) );
			echo render_input_field( array( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				'label'         => __( 'Email', 'wporg-learn' ),
				'type'          => 'email',
				'id'            => 'email',
				'value'         => $form['email'],
				'readonly'      => true,
			) );
			echo render_textarea_field( array( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				'label'         => __( 'Where can we find you online? Please share links to your website(s) and as many social media accounts as applicable, including but not limited to Twitter, Linkedin, Facebook, Instagram, etc.', 'wporg-learn' ),
				'id'            => 'online-presence',
				'name'          => 'online-presence',
				'value'         => $form['online-presence'],
				'required'      => true,
				'error_message' =>
					in_array( 'online-presence', $error_fields, true )
						? $errors->get_error_message( "{$prefix}online-presence" )
						: '',
			) );
			?>
		</fieldset>
		<fieldset class="section">
			<legend>
				<?php esc_html_e( 'Workshop Details', 'wporg-learn' ); ?>
			</legend>
			<?php
			echo render_input_field( array( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				'label'         => __( 'Workshop Title', 'wporg-learn' ),
				'id'            => 'workshop-title',
				'name'          => 'workshop-title',
				'value'         => $form['workshop-title'],
				'required'      => true,
				'error_message' =>
					in_array( 'workshop-title', $error_fields, true )
						? $errors->get_error_message( "{$prefix}workshop-title" )
						: '',
			) );
			echo render_textarea_field( array( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				'label'         => __( 'Brief workshop description (less than 150 words)', 'wporg-learn' ),
				'id'            => 'description-short',
				'name'          => 'description-short',
				'value'         => $form['description-short'],
				'required'      => true,
				'error_message' =>
					in_array( 'description-short', $error_fields, true )
						? $errors->get_error_message( "{$prefix}description-short" )
						: '',
			) );
			echo render_textarea_field( array( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				'label'         => __( 'Full workshop description', 'wporg-learn' ),
				'id'            => 'description',
				'name'          => 'description',
				'value'         => $form['description'],
				'required'      => true,
				'error_message' =>
					in_array( 'description', $error_fields, true )
						? $errors->get_error_message( "{$prefix}description" )
						: '',
			) );
			echo render_textarea_field( array( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				'label'         => __( 'What are the learning objectives for this workshop?', 'wporg-learn' ),
				'id'            => 'learning-objectives',
				'name'          => 'learning-objectives',
				'value'         => $form['learning-objectives'],
				'required'      => true,
				'error_message' =>
					in_array( 'learning-objectives', $error_fields, true )
						? $errors->get_error_message( "{$prefix}learning-objectives" )
						: '',
			) );
			echo render_textarea_field( array( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				'label'         => __( 'What comprehension questions should we ask at the end of your workshop? List at least 3 but no more than 10 questions for workshop viewers to answer on their own or discuss with a group to ensure they properly understood the material.', 'wporg-learn' ),
				'id'            => 'comprehension-questions',
				'name'          => 'comprehension-questions',
				'value'         => $form['comprehension-questions'],
				'required'      => true,
				'error_message' =>
					in_array( 'comprehension-questions', $error_fields, true )
						? $errors->get_error_message( "{$prefix}comprehension-questions" )
						: '',
			) );
			?>
			<fieldset class="checkbox-group <?php echo in_array( 'audience', $error_fields, true ) ? 'error' : ''; ?>">
				<legend class="label-text">
					<?php esc_html_e( 'Who is this workshop intended for?', 'wporg-learn' ); ?>
					<span class="required-field"><?php esc_html_e( '(required)', 'wporg-learn' ); ?></span>
				</legend>
				<?php if ( in_array( 'audience', $error_fields, true ) ) : ?>
					<span class="notice notice-error">
						<?php echo wp_kses_data( $errors->get_error_message( "{$prefix}audience" ) ); ?>
					</span>
				<?php endif; ?>
				<?php foreach ( $audience as $audience_value => $audience_label ) : ?>
					<label for="audience-<?php echo esc_attr( $audience_value ); ?>" class="label-checkbox">
						<input
							id="audience-<?php echo esc_attr( $audience_value ); ?>"
							name="audience[]"
							type="checkbox"
							value="<?php echo esc_attr( $audience_value ); ?>"
							<?php checked( in_array( $audience_value, $form['audience'], true ) ); ?>
						/>
						<span class="label-text-checkbox"><?php echo esc_html( $audience_label ); ?></span>
					</label>
				<?php endforeach; ?>
				<label for="audience-other" class="label-checkbox">
					<input
						id="audience-other"
						type="checkbox"
						<?php checked( ! is_null( $audience_other ) ); ?>
					/>
					<span class="label-text-checkbox"><?php esc_html_e( 'Other', 'wporg-learn' ); ?></span>
					<input
						id="audience-other-text"
						name="audience[]"
						type="text"
						value="<?php echo esc_attr( $audience_other ); ?>"
						<?php disabled( is_null( $audience_other ) ); ?>
					/>
				</label>
			</fieldset>
			<fieldset class="checkbox-group <?php echo in_array( 'experience-level', $error_fields, true ) ? 'error' : ''; ?>">
				<legend class="label-text">
					<?php esc_html_e( 'What experience level is this workshop aimed at?', 'wporg-learn' ); ?>
					<span class="required-field"><?php esc_html_e( '(required)', 'wporg-learn' ); ?></span>
				</legend>
				<?php if ( in_array( 'experience-level', $error_fields, true ) ) : ?>
					<span class="notice notice-error">
						<?php echo wp_kses_data( $errors->get_error_message( "{$prefix}experience-level" ) ); ?>
					</span>
				<?php endif; ?>
				<?php foreach ( $experience_level as $experience_level_value => $experience_level_label ) : ?>
					<label for="experience-level-<?php echo esc_attr( $experience_level_value ); ?>" class="label-checkbox">
						<input
								id="experience-level-<?php echo esc_attr( $experience_level_value ); ?>"
								name="experience-level[]"
								type="checkbox"
								value="<?php echo esc_attr( $experience_level_value ); ?>"
							<?php checked( in_array( $experience_level_value, $form['experience-level'], true ) ); ?>
						/>
						<span class="label-text-checkbox"><?php echo esc_html( $experience_level_label ); ?></span>
					</label>
				<?php endforeach; ?>
				<label for="experience-level-other" class="label-checkbox">
					<input
						id="experience-level-other"
						type="checkbox"
						<?php checked( ! is_null( $experience_level_other ) ); ?>
					/>
					<span class="label-text-checkbox"><?php esc_html_e( 'Other', 'wporg-learn' ); ?></span>
					<input
						id="experience-level-other-text"
						name="experience-level[]"
						type="text"
						value="<?php echo esc_attr( $experience_level_other ); ?>"
						<?php disabled( is_null( $experience_level_other ) ); ?>
					/>
				</label>
			</fieldset>
			<label for="language" <?php echo in_array( 'language', $error_fields, true ) ? 'class="error"' : ''; ?>>
				<span class="label-text"><?php esc_html_e( 'In what language will this workshop be presented?', 'wporg-learn' ); ?></span>
				<span class="required-field"><?php esc_html_e( '(required)', 'wporg-learn' ); ?></span>
				<?php if ( in_array( 'language', $error_fields, true ) ) : ?>
					<span class="notice notice-error">
						<?php echo wp_kses_data( $errors->get_error_message( "{$prefix}language" ) ); ?>
					</span>
				<?php endif; ?>
				<select id="language" name="language" required>
					<?php foreach ( get_locales_with_native_names() as $code => $name ) : ?>
						<option value="<?php echo esc_attr( $code ); ?>" <?php selected( $code, $form['language'] ); ?>>
							<?php echo esc_html( $name ); ?>
						</option>
					<?php endforeach; ?>
				</select>
			</label>
			<label for="timezone" <?php echo in_array( 'timezone', $error_fields, true ) ? 'class="error"' : ''; ?>>
				<span class="label-text"><?php esc_html_e( 'From what timezone would you conduct discussion groups?', 'wporg-learn' ); ?></span>
				<span class="required-field"><?php esc_html_e( '(required)', 'wporg-learn' ); ?></span>
				<?php if ( in_array( 'timezone', $error_fields, true ) ) : ?>
					<span class="notice notice-error">
						<?php echo wp_kses_data( $errors->get_error_message( "{$prefix}timezone" ) ); ?>
					</span>
				<?php endif; ?>
				<select id="timezone" name="timezone" required>
					<?php echo wp_timezone_choice( $form['timezone'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				</select>
			</label>
			<?php
			echo render_textarea_field( array( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				'label'         => __( 'Is there anything else you think we should know?', 'wporg-learn' ),
				'id'            => 'comments',
				'name'          => 'comments',
				'value'         => $form['comments'],
				'error_message' =>
					in_array( 'comments', $error_fields, true )
						? $errors->get_error_message( "{$prefix}comments" )
						: '',
			) );
			?>
		</fieldset>
		<?php wp_nonce_field( 'workshop-application-' . $form['wporg-user-name'], 'nonce' ); ?>
		<input
			type="submit"
			name="submit"
			class="button button-primary"
			value="<?php esc_attr_e( 'Submit', 'wporg-learn' ); ?>"
		/>
	</form>
<?php elseif ( 'success' === $state ) : ?>
	<h2>
		<?php esc_html_e( 'Success!', 'wporg-learn' ); ?>
	</h2>
	<p>
		<?php esc_html_e( 'Your application has been submitted.', 'wporg-learn' ); ?>
	</p>
<?php else : ?>
	<p>
		<?php
		printf(
			wp_kses_post( __( 'You must be logged in with your WordPress.org account to view and submit this application. <a href="%s">Log in.</a>', 'wporg-learn' ) ),
			esc_url( wp_login_url( apply_filters( 'the_permalink', get_permalink() ) ) )
		);
		?>
	</p>
<?php endif; ?>
