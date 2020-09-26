<?php

namespace WPOrg_Learn\View\Form;

use function WordPressdotorg\Locales\get_locales_with_native_names;

defined( 'WPINC' ) || die();

/** @var array $form */

$audience = array(
	'contributors' => __( 'Contributors', 'wporg-learn' ),
	'designers'    => __( 'Designers', 'wporg-learn' ),
	'developers'   => __( 'Developers', 'wporg-learn' ),
	'publishers'   => __( 'Publishers', 'wporg-learn' ),
);
$audience_other = array_diff( $form['audience'], array_keys( $audience ) );
$audience_other = array_shift( $audience_other );

$experience_level = array(
	'beginner'     => __( 'Beginner', 'wporg-learn' ),
	'intermediate' => __( 'Intermediate', 'wporg-learn' ),
	'expert'       => __( 'Expert', 'wporg-learn' ),
);
$experience_level_other = array_diff( $form['experience-level'], array_keys( $experience_level ) );
$experience_level_other = array_shift( $experience_level_other );
?>

<?php if ( is_user_logged_in() ) : ?>
	<form method="post">
		<fieldset>
			<legend>
				<?php esc_html_e( 'Presenter Details', 'wporg-learn' ); ?>
			</legend>
			<p>
				<?php
				printf(
					esc_html__( 'You are logged in as <a href="%1$s">%2$s</a>. <a href="%3$s">Log out?</a>', 'wporg-learn' ),
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
					disabled
				/>
			</label>
			<label for="first-name">
				<span class="label-text"><?php esc_html_e( 'First Name', 'wporg-learn' ); ?></span>
				<input
					id="first-name"
					type="text"
					value="<?php echo esc_attr( $form['first-name'] ); ?>"
					disabled
				/>
			</label>
			<label for="last-name">
				<span class="label-text"><?php esc_html_e( 'Last Name', 'wporg-learn' ); ?></span>
				<input
					id="last-name"
					type="text"
					value="<?php echo esc_attr( $form['last-name'] ); ?>"
					disabled
				/>
			</label>
			<label for="email">
				<span class="label-text"><?php esc_html_e( 'Email', 'wporg-learn' ); ?></span>
				<input
					id="email"
					type="email"
					value="<?php echo esc_attr( $form['email'] ); ?>"
					disabled
				/>
			</label>
			<label for="online-presence">
				<span class="label-text"><?php esc_html_e( 'Where can we find you online? Please share links to your website(s) and as many social media accounts as applicable, including but not limited to Twitter, Linkedin, Facebook, Instagram, etc.', 'wporg-learn' ); ?></span>
				<span class="required-field"><?php esc_html_e( '(required)', 'wporg-learn' ); ?></span>
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
			<label for="workshop-title">
				<span class="label-text"><?php esc_html_e( 'Workshop Title', 'wporg-learn' ); ?></span>
				<span class="required-field"><?php esc_html_e( '(required)', 'wporg-learn' ); ?></span>
				<input
					id="workshop-title"
					name="workshop-title"
					type="text"
					value="<?php echo esc_attr( $form['workshop-title'] ); ?>"
					required
				/>
			</label>
			<label for="description-short">
				<span class="label-text"><?php esc_html_e( 'Brief workshop description (less than 150 words)', 'wporg-learn' ); ?></span>
				<span class="required-field"><?php esc_html_e( '(required)', 'wporg-learn' ); ?></span>
				<textarea
					id="description-short"
					name="description-short"
					required
				><?php echo esc_html( $form['description-short'] ); ?></textarea>
			</label>
			<label for="description">
				<span class="label-text"><?php esc_html_e( 'Full workshop description', 'wporg-learn' ); ?></span>
				<span class="required-field"><?php esc_html_e( '(required)', 'wporg-learn' ); ?></span>
				<textarea
					id="description"
					name="description"
					required
				><?php echo esc_html( $form['description'] ); ?></textarea>
			</label>
			<label for="learning-objectives">
				<span class="label-text"><?php esc_html_e( 'What are the learning objectives for this workshop?', 'wporg-learn' ); ?></span>
				<span class="required-field"><?php esc_html_e( '(required)', 'wporg-learn' ); ?></span>
				<textarea
					id="learning-objectives"
					name="learning-objectives"
					required
				><?php echo esc_html( $form['learning-objectives'] ); ?></textarea>
			</label>
			<label for="comprehension-questions">
				<span class="label-text"><?php esc_html_e( 'What comprehension questions should we ask at the end of your workshop? List at least 3 but no more than 10 questions for workshop viewers to answer on their own or discuss with a group to ensure they properly understood the material.', 'wporg-learn' ); ?></span>
				<span class="required-field"><?php esc_html_e( '(required)', 'wporg-learn' ); ?></span>
				<textarea
					id="comprehension-questions"
					name="comprehension-questions"
					required
				><?php echo esc_html( $form['comprehension-questions'] ); ?></textarea>
			</label>
			<fieldset class="checkbox-group">
				<legend class="label-text">
					<?php esc_html_e( 'Who is this workshop intended for?', 'wporg-learn' ); ?>
					<span class="required-field"><?php esc_html_e( '(required)', 'wporg-learn' ); ?></span>
				</legend>
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
			<fieldset class="checkbox-group">
				<legend class="label-text">
					<?php esc_html_e( 'What experience level is this workshop aimed at?', 'wporg-learn' ); ?>
					<span class="required-field"><?php esc_html_e( '(required)', 'wporg-learn' ); ?></span>
				</legend>
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
			<label for="language">
				<span class="label-text"><?php esc_html_e( 'In what language will this workshop be presented?', 'wporg-learn' ); ?></span>
				<span class="required-field"><?php esc_html_e( '(required)', 'wporg-learn' ); ?></span>
				<select id="language" name="language" required>
					<?php foreach ( get_locales_with_native_names() as $code => $name ) : ?>
						<option value="<?php echo esc_attr( $code ); ?>" <?php selected( $code ); ?>>
							<?php echo esc_html( $name ); ?>
						</option>
					<?php endforeach; ?>
				</select>
			</label>
			<label for="timezone">
				<span class="label-text"><?php esc_html_e( 'From what timezone would you conduct discussion groups?', 'wporg-learn' ); ?></span>
				<span class="required-field"><?php esc_html_e( '(required)', 'wporg-learn' ); ?></span>
				<select id="timezone" name="timezone" required>
					<?php echo wp_timezone_choice( 'UTC+0' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				</select>
			</label>
			<label for="comments">
				<span class="label-text"><?php esc_html_e( 'Is there anything else you think we should know?', 'wporg-learn' ); ?></span>
				<span class="required-field"><?php esc_html_e( '(required)', 'wporg-learn' ); ?></span>
				<textarea id="comments" name="comments" required><?php // todo ?></textarea>
			</label>
		</fieldset>
		<?php wp_nonce_field( 'workshop-application-' . $form['wporg-user-name'], 'nonce' ); ?>
		<input type="submit" value="<?php echo esc_attr( 'Submit', 'wporg-learn' ); ?>" />
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
