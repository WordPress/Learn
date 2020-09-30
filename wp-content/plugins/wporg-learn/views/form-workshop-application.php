<?php

namespace WPOrg_Learn\View\Form;

use WP_Error;
use function WordPressdotorg\Locales\get_locales_with_native_names;

defined( 'WPINC' ) || die();

/** @var array $form */
/** @var WP_Error|null $errors */
/** @var array $error_fields */
/** @var array $audience */
/** @var string $audience_other */
/** @var array $experience_level */
/** @var string $experience_level_other */

$prefix = 'submission:';
?>

<?php if ( is_user_logged_in() ) : ?>
	<form method="post" class="wp-block wporg-learn-workshop-application-form">
		<?php if ( ! empty( $messages ) ) : ?>
			<?php foreach ( $messages as $message ) : ?>
				<div class="notice notice-error">
					<?php echo wp_kses_post( wpautop( $message ) ); ?>
				</div>
			<?php endforeach; ?>
		<?php endif; ?>
		<fieldset>
			<legend>
				<?php esc_html_e( 'Presenter Details', 'wporg-learn' ); ?>
			</legend>
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
			<p>
				<?php esc_html_e( 'The following data from your user profile will be used in this application:', 'wporg-learn' ); ?>
			</p>
			<label for="wporg-user-name">
				<span class="label-text"><?php esc_html_e( 'WordPress.org User Name', 'wporg-learn' ); ?></span>
				<input
					id="wporg-user-name"
					type="text"
					value="<?php echo esc_attr( $form['wporg-user-name'] ); ?>"
					readonly
				/>
			</label>
			<label for="first-name">
				<span class="label-text"><?php esc_html_e( 'First Name', 'wporg-learn' ); ?></span>
				<input
					id="first-name"
					type="text"
					value="<?php echo esc_attr( $form['first-name'] ); ?>"
					readonly
				/>
			</label>
			<label for="last-name">
				<span class="label-text"><?php esc_html_e( 'Last Name', 'wporg-learn' ); ?></span>
				<input
					id="last-name"
					type="text"
					value="<?php echo esc_attr( $form['last-name'] ); ?>"
					readonly
				/>
			</label>
			<label for="email">
				<span class="label-text"><?php esc_html_e( 'Email', 'wporg-learn' ); ?></span>
				<input
					id="email"
					type="email"
					value="<?php echo esc_attr( $form['email'] ); ?>"
					readonly
				/>
			</label>
			<label for="online-presence" <?php echo in_array( 'online-presence', $error_fields, true ) ? 'class="error"' : ''; ?>>
				<span class="label-text"><?php esc_html_e( 'Where can we find you online? Please share links to your website(s) and as many social media accounts as applicable, including but not limited to Twitter, Linkedin, Facebook, Instagram, etc.', 'wporg-learn' ); ?></span>
				<span class="required-field"><?php esc_html_e( '(required)', 'wporg-learn' ); ?></span>
				<?php if ( in_array( 'online-presence', $error_fields, true ) ) : ?>
					<span class="notice notice-error">
						<?php echo wp_kses_data( $errors->get_error_message( "{$prefix}online-presence" ) ); ?>
					</span>
				<?php endif; ?>
				<textarea
					id="online-presence"
					name="online-presence"
					required
				><?php echo esc_html( $form['online-presence'] ); ?></textarea>
			</label>
		</fieldset>
		<fieldset>
			<legend>
				<?php esc_html_e( 'Workshop Details', 'wporg-learn' ); ?>
			</legend>
			<label for="workshop-title" <?php echo in_array( 'workshop-title', $error_fields, true ) ? 'class="error"' : ''; ?>>
				<span class="label-text"><?php esc_html_e( 'Workshop Title', 'wporg-learn' ); ?></span>
				<span class="required-field"><?php esc_html_e( '(required)', 'wporg-learn' ); ?></span>
				<?php if ( in_array( 'workshop-title', $error_fields, true ) ) : ?>
					<span class="notice notice-error">
						<?php echo wp_kses_data( $errors->get_error_message( "{$prefix}workshop-title" ) ); ?>
					</span>
				<?php endif; ?>
				<input
					id="workshop-title"
					name="workshop-title"
					type="text"
					value="<?php echo esc_attr( $form['workshop-title'] ); ?>"
					required
				/>
			</label>
			<label for="description-short" <?php echo in_array( 'description-short', $error_fields, true ) ? 'class="error"' : ''; ?>>
				<span class="label-text"><?php esc_html_e( 'Brief workshop description (less than 150 words)', 'wporg-learn' ); ?></span>
				<span class="required-field"><?php esc_html_e( '(required)', 'wporg-learn' ); ?></span>
				<?php if ( in_array( 'description-short', $error_fields, true ) ) : ?>
					<span class="notice notice-error">
						<?php echo wp_kses_data( $errors->get_error_message( "{$prefix}description-short" ) ); ?>
					</span>
				<?php endif; ?>
				<textarea
					id="description-short"
					name="description-short"
					required
				><?php echo esc_html( $form['description-short'] ); ?></textarea>
			</label>
			<label for="description" <?php echo in_array( 'description', $error_fields, true ) ? 'class="error"' : ''; ?>>
				<span class="label-text"><?php esc_html_e( 'Full workshop description', 'wporg-learn' ); ?></span>
				<span class="required-field"><?php esc_html_e( '(required)', 'wporg-learn' ); ?></span>
				<?php if ( in_array( 'description', $error_fields, true ) ) : ?>
					<span class="notice notice-error">
						<?php echo wp_kses_data( $errors->get_error_message( "{$prefix}description" ) ); ?>
					</span>
				<?php endif; ?>
				<textarea
					id="description"
					name="description"
					required
				><?php echo esc_html( $form['description'] ); ?></textarea>
			</label>
			<label for="learning-objectives" <?php echo in_array( 'learning-objectives', $error_fields, true ) ? 'class="error"' : ''; ?>>
				<span class="label-text"><?php esc_html_e( 'What are the learning objectives for this workshop?', 'wporg-learn' ); ?></span>
				<span class="required-field"><?php esc_html_e( '(required)', 'wporg-learn' ); ?></span>
				<?php if ( in_array( 'learning-objectives', $error_fields, true ) ) : ?>
					<span class="notice notice-error">
						<?php echo wp_kses_data( $errors->get_error_message( "{$prefix}learning-objectives" ) ); ?>
					</span>
				<?php endif; ?>
				<textarea
					id="learning-objectives"
					name="learning-objectives"
					required
				><?php echo esc_html( $form['learning-objectives'] ); ?></textarea>
			</label>
			<label for="comprehension-questions" <?php echo in_array( 'comprehension-questions', $error_fields, true ) ? 'class="error"' : ''; ?>>
				<span class="label-text"><?php esc_html_e( 'What comprehension questions should we ask at the end of your workshop? List at least 3 but no more than 10 questions for workshop viewers to answer on their own or discuss with a group to ensure they properly understood the material.', 'wporg-learn' ); ?></span>
				<span class="required-field"><?php esc_html_e( '(required)', 'wporg-learn' ); ?></span>
				<?php if ( in_array( 'comprehension-questions', $error_fields, true ) ) : ?>
					<span class="notice notice-error">
						<?php echo wp_kses_data( $errors->get_error_message( "{$prefix}comprehension-questions" ) ); ?>
					</span>
				<?php endif; ?>
				<textarea
					id="comprehension-questions"
					name="comprehension-questions"
					required
				><?php echo esc_html( $form['comprehension-questions'] ); ?></textarea>
			</label>
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
					<label for="audience-<?php echo esc_attr( $audience_value ); ?>">
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
				<label for="audience-other">
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
					<label for="experience-level-<?php echo esc_attr( $experience_level_value ); ?>">
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
				<label for="experience-level-other">
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
			<label for="comments" <?php echo in_array( 'comments', $error_fields, true ) ? 'class="error"' : ''; ?>>
				<span class="label-text"><?php esc_html_e( 'Is there anything else you think we should know?', 'wporg-learn' ); ?></span>
				<?php if ( in_array( 'comments', $error_fields, true ) ) : ?>
					<span class="notice notice-error">
						<?php echo wp_kses_data( $errors->get_error_message( "{$prefix}comments" ) ); ?>
					</span>
				<?php endif; ?>
				<textarea id="comments" name="comments"><?php // todo ?></textarea>
			</label>
		</fieldset>
		<?php wp_nonce_field( 'workshop-application-' . $form['wporg-user-name'], 'nonce' ); ?>
		<input
			type="submit"
			name="submit"
			class="is-style-primary"
			value="<?php esc_attr_e( 'Submit', 'wporg-learn' ); ?>"
		/>
	</form>
<?php else : ?>
	<p>
		<?php
		printf(
			wp_kses_post( __( 'You must be logged in with your WordPress.org account to submit this application. <a href="%s">Log in.</a>', 'wporg-learn' ) ),
			esc_url( wp_login_url( apply_filters( 'the_permalink', get_permalink() ) ) )
		);
		?>
	</p>
<?php endif; ?>
