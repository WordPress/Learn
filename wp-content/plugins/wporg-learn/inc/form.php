<?php

namespace WPOrg_Learn\Form;

use WP_Error, WP_User;
use WordPressdotorg\Validator;
use function WordPressdotorg\Locales\get_locales_with_native_names;
use function WPOrg_Learn\get_views_path;

defined( 'WPINC' ) || die();

/**
 * The schema for the workshop application fields.
 *
 * @return array
 */
function get_workshop_application_field_schema() {
	return array(
		'type'       => 'object',
		'label'      => 'submission',
		'properties' => array(
			'wporg-user-name'         => array(
				'input_filters' => FILTER_SANITIZE_STRING,
				'type'          => 'string',
				'required'      => true,
				'default'       => '',
			),
			'first-name'              => array(
				'input_filters' => FILTER_SANITIZE_STRING,
				'type'          => 'string',
				'required'      => false,
				'default'       => '',
			),
			'last-name'               => array(
				'input_filters' => FILTER_SANITIZE_STRING,
				'type'          => 'string',
				'required'      => false,
				'default'       => '',
			),
			'email'                   => array(
				'input_filters' => FILTER_SANITIZE_EMAIL,
				'type'          => 'string',
				'format'        => 'email',
				'required'      => true,
				'default'       => '',
			),
			'online-presence'         => array(
				'input_filters' => FILTER_SANITIZE_STRING,
				'type'          => 'string',
				'required'      => true,
				'default'       => '',
			),
			'workshop-title'          => array(
				'input_filters' => FILTER_SANITIZE_STRING,
				'type'          => 'string',
				'required'      => true,
				'default'       => '',
			),
			'description'             => array(
				'input_filters' => FILTER_SANITIZE_STRING,
				'type'          => 'string',
				'required'      => true,
				'default'       => '',
			),
			'description-short'       => array(
				'input_filters' => FILTER_SANITIZE_STRING,
				'type'          => 'string',
				'required'      => true,
				'default'       => '',
			),
			'learning-objectives'     => array(
				'input_filters' => FILTER_SANITIZE_STRING,
				'type'          => 'string',
				'required'      => true,
				'default'       => '',
			),
			'comprehension-questions' => array(
				'input_filters' => FILTER_SANITIZE_STRING,
				'type'          => 'string',
				'required'      => true,
				'default'       => '',
			),
			'audience'                => array(
				'input_filters' => array(
					'filter' => FILTER_SANITIZE_STRING,
					'flags'  => FILTER_REQUIRE_ARRAY,
				),
				'type'          => 'array',
				'items'         => array(
					'type' => 'string',
				),
				'minItems'      => 1,
				'required'      => true,
				'default'       => array(),
			),
			'experience-level'        => array(
				'input_filters' => array(
					'filter' => FILTER_SANITIZE_STRING,
					'flags'  => FILTER_REQUIRE_ARRAY,
				),
				'type'          => 'array',
				'items'         => array(
					'type' => 'string',
				),
				'minItems'      => 1,
				'required'      => true,
				'default'       => array(),
			),
			'language'                => array(
				'input_filters' => FILTER_SANITIZE_STRING,
				'type'          => 'string',
				'enum'          => array_keys( get_locales_with_native_names() ),
				'required'      => true,
				'default'       => 'en_US',
			),
			'timezone'                => array(
				'input_filters' => FILTER_SANITIZE_STRING,
				'type'          => 'string',
				'required'      => true,
				'default'       => 'UTC+0',
			),
			'comments'                => array(
				'input_filters' => FILTER_SANITIZE_STRING,
				'type'          => 'string',
				'required'      => false,
				'default'       => '',
			),
			'nonce'                   => array(
				'input_filters' => FILTER_SANITIZE_STRING,
				'type'          => 'string',
				'required'      => true,
				'default'       => '',
			),
		),
	);
}

/**
 * Parse a workshop application submission from an HTTP POST request.
 *
 * @return array
 */
function get_workshop_application_form_submission() {
	$schema = get_workshop_application_field_schema();

	$submission = filter_input_array(
		INPUT_POST,
		wp_list_pluck( $schema['properties'], 'input_filters' ),
		false
	);

	if ( empty( $submission ) ) {
		return array();
	}

	$submission = array_merge( $submission, get_workshop_application_form_user_details() );

	return array_filter( $submission );
}

/**
 * Get relevant data about the current user for the application form.
 *
 * @return array
 */
function get_workshop_application_form_user_details() {
	$current_user = wp_get_current_user();

	if ( ! $current_user instanceof WP_User ) {
		return array();
	}

	return array(
		'wporg-user-name' => $current_user->user_login,
		'first-name'      => $current_user->user_firstname,
		'last-name'       => $current_user->user_lastname,
		'email'           => $current_user->user_email,
	);
}

/**
 * Validate a workshop application submission.
 *
 * @param array|object $submission
 *
 * @return array|object|WP_Error
 */
function validate_workshop_application_form_submission( $submission ) {
	$validator  = new Validator( get_workshop_application_field_schema() );
	$submission = (object) $submission;

	return $validator->validate( $submission );
}

/**
 * Do stuff with a valid form submission.
 *
 * @param array $submission
 */
function process_workshop_application_form_submission( $submission ) {
	// todo
}

/**
 * Render the workshop application form in its various states.
 *
 * @return string
 */
function render_workshop_application_form() {
	$schema       = get_workshop_application_field_schema();
	$defaults     = wp_list_pluck( $schema['properties'], 'default' );
	$form         = wp_parse_args( get_workshop_application_form_user_details(), $defaults );
	$errors       = null;
	$error_fields = array();

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

	$submitted = filter_input( INPUT_POST, 'submit' );
	if ( $submitted ) {
		$submission = get_workshop_application_form_submission();
		$form       = wp_parse_args( $submission, $form );
		$validation = validate_workshop_application_form_submission( $submission );

		if ( is_wp_error( $validation ) ) {
			$errors = $validation;
			$error_fields = array_map(
				function( $code ) {
					return str_replace( 'submission:', '', $code );
				},
				$validation->get_error_data( 'error' )
			);
		} else {
			process_workshop_application_form_submission( $validation );
		}
	}

	ob_start();
	require get_views_path() . 'form-workshop-application.php';
	return ob_get_clean();
}

/**
 * Generate HTML markup for an input field.
 *
 * @param array $args
 *
 * @return string
 */
function render_input_field( array $args ) {
	$args = wp_parse_args(
		$args,
		array(
			'label'         => '',
			'type'          => 'text',
			'id'            => '',
			'class'         => array(),
			'name'          => '',
			'value'         => '',
			'required'      => false,
			'readonly'      => false,
			'error_message' => '',
		)
	);

	if ( ! empty( $args['error_message'] ) ) {
		$args['class'][] = 'error';
	}

	$args['class'] = implode( ' ', array_unique( $args['class'] ) );

	ob_start();
	?>
	<label for="<?php echo esc_attr( $args['id'] ); ?>" class="<?php echo esc_attr( $args['class'] ); ?>">
		<?php if ( ! empty( $args['label'] ) ) : ?>
			<span class="label-text"><?php echo esc_html( $args['label'] ); ?></span>
		<?php endif; ?>
		<?php if ( true === $args['required'] ) : ?>
			<span class="required-field"><?php esc_html_e( '(required)', 'wporg-learn' ); ?></span>
		<?php endif; ?>
		<?php if ( ! empty( $args['error_message'] ) ) : ?>
			<span class="notice notice-error">
				<?php echo wp_kses_data( $args['error_message'] ); ?>
			</span>
		<?php endif; ?>
		<input
			id="<?php echo esc_attr( $args['id'] ); ?>"
			name="<?php echo esc_attr( $args['name'] ); ?>"
			type="<?php echo esc_attr( $args['type'] ); ?>"
			value="<?php echo esc_attr( $args['value'] ); ?>"
			<?php echo true === $args['required'] ? 'required' : ''; ?>
			<?php echo true === $args['readonly'] ? 'readonly' : ''; ?>
		/>
	</label>
	<?php

	return ob_get_clean();
}

/**
 * Generate HTML markup for a textarea field.
 *
 * @param array $args
 *
 * @return string
 */
function render_textarea_field( array $args ) {
	$args = wp_parse_args(
		$args,
		array(
			'label'         => '',
			'id'            => '',
			'class'         => array(),
			'name'          => '',
			'value'         => '',
			'required'      => false,
			'error_message' => '',
		)
	);

	if ( ! empty( $args['error_message'] ) ) {
		$args['class'][] = 'error';
	}

	$args['class'] = implode( ' ', array_unique( $args['class'] ) );

	ob_start();
	?>
	<label for="<?php echo esc_attr( $args['id'] ); ?>" class="<?php echo esc_attr( $args['class'] ); ?>">
		<?php if ( ! empty( $args['label'] ) ) : ?>
			<span class="label-text"><?php echo esc_html( $args['label'] ); ?></span>
		<?php endif; ?>
		<?php if ( true === $args['required'] ) : ?>
			<span class="required-field"><?php esc_html_e( '(required)', 'wporg-learn' ); ?></span>
		<?php endif; ?>
		<?php if ( ! empty( $args['error_message'] ) ) : ?>
			<span class="notice notice-error">
				<?php echo wp_kses_data( $args['error_message'] ); ?>
			</span>
		<?php endif; ?>
		<textarea
			id="<?php echo esc_attr( $args['id'] ); ?>"
			name="<?php echo esc_attr( $args['name'] ); ?>"
			<?php echo true === $args['required'] ? 'required' : ''; ?>
		><?php echo esc_html( $args['value'] ); ?></textarea>
	</label>
	<?php

	return ob_get_clean();
}
