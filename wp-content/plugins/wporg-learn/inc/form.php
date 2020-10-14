<?php

namespace WPOrg_Learn\Form;

use WP_Error, WP_Query, WP_User;
use WordPressdotorg\Validator;
use function WordPressdotorg\Locales\get_locales_with_english_names;
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
				'label'         => __( 'WordPress.org User Name', 'wporg-learn' ),
				'type'          => 'string',
				'required'      => true,
				'default'       => '',
			),
			'first-name'              => array(
				'input_filters' => FILTER_SANITIZE_STRING,
				'label'         => __( 'First Name', 'wporg-learn' ),
				'type'          => 'string',
				'required'      => false,
				'default'       => '',
			),
			'last-name'               => array(
				'input_filters' => FILTER_SANITIZE_STRING,
				'label'         => __( 'Last Name', 'wporg-learn' ),
				'type'          => 'string',
				'required'      => false,
				'default'       => '',
			),
			'email'                   => array(
				'input_filters' => FILTER_SANITIZE_EMAIL,
				'label'         => __( 'Email', 'wporg-learn' ),
				'type'          => 'string',
				'format'        => 'email',
				'required'      => true,
				'default'       => '',
			),
			'online-presence'         => array(
				'input_filters' => FILTER_SANITIZE_STRING,
				'label'         => __( 'Where can we find you online? Please share links to your website(s) and as many social media accounts as applicable, including but not limited to Twitter, Linkedin, Facebook, Instagram, etc.', 'wporg-learn' ),
				'type'          => 'string',
				'required'      => true,
				'default'       => '',
			),
			'workshop-title'          => array(
				'input_filters' => FILTER_SANITIZE_STRING,
				'label'         => __( 'Workshop Title', 'wporg-learn' ),
				'type'          => 'string',
				'required'      => true,
				'default'       => '',
			),
			'description'             => array(
				'input_filters' => FILTER_SANITIZE_STRING,
				'label'         => __( 'Full workshop description', 'wporg-learn' ),
				'type'          => 'string',
				'required'      => true,
				'default'       => '',
			),
			'description-short'       => array(
				'input_filters' => FILTER_SANITIZE_STRING,
				'label'         => __( 'Brief workshop description (less than 150 words)', 'wporg-learn' ),
				'type'          => 'string',
				'required'      => true,
				'default'       => '',
			),
			'learning-objectives'     => array(
				'input_filters' => FILTER_SANITIZE_STRING,
				'label'         => __( 'What are the learning objectives for this workshop?', 'wporg-learn' ),
				'type'          => 'string',
				'required'      => true,
				'default'       => '',
			),
			'comprehension-questions' => array(
				'input_filters' => FILTER_SANITIZE_STRING,
				'label'         => __( 'What comprehension questions should we ask at the end of your workshop? List at least 3 but no more than 10 questions for workshop viewers to answer on their own or discuss with a group to ensure they properly understood the material.', 'wporg-learn' ),
				'type'          => 'string',
				'required'      => true,
				'default'       => '',
			),
			'audience'                => array(
				'input_filters' => array(
					'filter' => FILTER_SANITIZE_STRING,
					'flags'  => FILTER_REQUIRE_ARRAY,
				),
				'label'         => __( 'Who is this workshop intended for?', 'wporg-learn' ),
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
				'label'         => __( 'What experience level is this workshop aimed at?', 'wporg-learn' ),
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
				'label'         => __( 'In what language will this workshop be presented?', 'wporg-learn' ),
				'type'          => 'string',
				'enum'          => array_keys( get_locales_with_english_names() ),
				'required'      => true,
				'default'       => 'en_US',
			),
			'comments'                => array(
				'input_filters' => FILTER_SANITIZE_STRING,
				'label'         => __( 'Is there anything else you think we should know?', 'wporg-learn' ),
				'type'          => 'string',
				'required'      => false,
				'default'       => '',
			),
			'nonce'                   => array(
				'input_filters' => FILTER_SANITIZE_STRING,
				'label'         => '',
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

	$submission = array_map(
		function( $item ) {
			// Ensure arrays don't contain items that are empty strings.
			if ( is_array( $item ) ) {
				$item = array_filter( $item );
			}

			return $item;
		},
		$submission
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

	return $validator->validate( $submission );
}

/**
 * Do stuff with a valid form submission.
 *
 * @param array $submission
 */
function process_workshop_application_form_submission( $submission ) {
	$nonce = $submission['nonce'] ?? '';
	$user  = $submission['wporg-user-name'] ?? '';
	if ( ! wp_verify_nonce( $nonce, 'workshop-application-' . $user ) ) {
		return new WP_Error(
			'submission_error',
			__( 'The form submission failed. Please try again.', 'wporg-learn' )
		);
	}

	if ( is_submission_rate_limited( $submission ) ) {
		return new WP_Error(
			'submission_error',
			__( 'You have reached your submission limit. Try again in an hour.', 'wporg-learn' )
		);
	}

	$validated = validate_workshop_application_form_submission( $submission );

	if ( is_wp_error( $validated ) ) {
		$error_count = count( $validated->get_error_data( 'error' ) );

		$validated->add(
			'submission_error',
			sprintf(
				_n(
					'There is %d form field that needs attention.',
					'There are %d form fields that need attention.',
					$error_count,
					'wporg-learn'
				),
				number_format_i18n( floatval( $error_count ) )
			)
		);

		return $validated;
	}

	$content = prepare_post_content_from_submission( $validated );

	$post_args = array(
		'post_status'  => get_default_workshop_status(),
		'post_type'    => 'wporg_workshop',
		'post_title'   => $validated['workshop-title'],
		'post_excerpt' => $validated['description-short'],
		'post_content' => $content,
		'meta_input'   => array(
			'video_language'       => $validated['language'],
			'original_application' => $validated,
		),
	);

	$result = wp_insert_post( $post_args );

	if ( is_wp_error( $result ) ) {
		return new WP_Error(
			'submission_error',
			$result->get_error_message()
		);
	}

	add_post_meta( $result, 'presenter_wporg_username', $validated['wporg-user-name'] );

	return true;
}

/**
 * Check if a submission should be subject to rate limiting.
 *
 * Rate limiting considers how many successful submissions have been made in the past hour.
 *
 * @param array $submission
 *
 * @return bool
 */
function is_submission_rate_limited( $submission ) {
	$limit = 5;

	$args = array(
		'post_type'   => 'wporg_workshop',
		'post_status' => get_default_workshop_status(),
		'meta_query'  => array(
			array(
				'key'   => 'presenter_wporg_username',
				'value' => $submission['wporg-user-name'],
			),
		),
		'date_query'  => array(
			array(
				'after' => '-1 hour',
			),
		),
	);
	$query = new WP_Query( $args );

	if ( $query->found_posts >= $limit ) {
		return true;
	}

	return false;
}

/**
 * The post status that should be assigned when creating a new workshop post.
 *
 * "Needs Vetting" is a custom status that should be created in the Edit Flow plugin and configured
 * to work with the Workshop post type. If it doesn't exist, however, we should fall back on "Draft".
 *
 * @return string
 */
function get_default_workshop_status() {
	if ( function_exists( 'EditFlow' ) ) {
		$status = 'needs-vetting';
		$all_stati = get_post_stati();
		$module_data = EditFlow()->get_module_by( 'name', 'custom_status' );
		$supported_post_types = EditFlow()->helpers->get_post_types_for_module( $module_data );

		if ( array_key_exists( $status, $all_stati ) && in_array( 'wporg_workshop', $supported_post_types, true ) ) {
			return $status;
		}
	}

	return 'draft';
}

/**
 * Convert certain submission field values into a post content string.
 *
 * @param array $submission
 *
 * @return string
 */
function prepare_post_content_from_submission( $submission ) {
	$blurbs = wp_parse_args(
		$submission,
		array(
			'description'             => '',
			'learning-objectives'     => '',
			'comprehension-questions' => '',
		)
	);

	$blurbs['description'] = wpautop( $blurbs['description'] );
	if ( empty( $blurbs['description'] ) ) {
		$blurbs['description'] = '
			<!-- wp:paragraph {"placeholder":"Describe what the workshop is about."} -->
			<p></p>
			<!-- /wp:paragraph -->
		';
	} else {
		$blurbs['description'] = str_replace(
			array(
				'<p>',
				'</p>',
			),
			array(
				"\n<!-- wp:paragraph -->\n<p>",
				"</p>\n<!-- /wp:paragraph -->\n",
			),
			$blurbs['description']
		);
	}

	foreach ( array( 'learning-objectives', 'comprehension-questions' ) as $key ) {
		// Turn separate lines into list items.
		$content = str_replace( array( "\r\n", "\r" ), "\n", $blurbs[ $key ] );
		$split   = explode( "\n", $content );
		$split   = array_filter( array_map(
			function( $item ) {
				// Attempt to strip out list item enumeration characters.
				$item = preg_replace( '/^([*\-]+|[1-9]{1,2}[\.\)]?|[A-Z]+[\.\)]+) ?/', '', $item );

				return trim( $item );
			},
			(array) $split
		) );

		if ( ! empty( $split ) ) {
			$blurbs[ $key ] = '<li>' . implode( '</li><li>', $split ) . '</li>';
		}
	}

	ob_start();
	require get_views_path() . 'content-workshop.php';
	return ob_get_clean();
}

/**
 * Render the workshop application form in its various states.
 *
 * @return string
 */
function render_workshop_application_form() {
	$schema     = get_workshop_application_field_schema();
	$defaults   = wp_parse_args(
		get_workshop_application_form_user_details(),
		wp_list_pluck( $schema['properties'], 'default' )
	);

	$state = is_user_logged_in() ? 'new' : 'logged-out';

	if ( filter_input( INPUT_POST, 'submit' ) ) {
		$submission = get_workshop_application_form_submission();
		$processed  = process_workshop_application_form_submission( $submission );

		if ( is_wp_error( $processed ) ) {
			$state = 'error';
		} else {
			$state = 'success';
		}
	}

	$form         = $defaults;
	$errors       = null;
	$error_fields = array();
	$messages     = array();

	if ( 'error' === $state ) {
		$form = wp_parse_args( $submission, $defaults );
		$errors = $processed;
		$error_fields = array_map(
			function( $code ) {
				return preg_replace(
					array(
						'/^submission:/',
						'/\[[0-9]+\]$/',
					),
					'',
					$code
				);
			},
			$processed->get_error_data( 'error' ) ?? array()
		);
		$messages = $errors->get_error_messages( 'submission_error' );
	}

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
			'disabled'      => false,
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
			<?php echo true === $args['disabled'] ? 'disabled' : ''; ?>
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
			'rows'          => '',
			'cols'          => '',
			'required'      => false,
			'readonly'      => false,
			'disabled'      => false,
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
			<?php echo $args['rows'] ? sprintf( 'rows="%s"', esc_attr( $args['rows'] ) ) : ''; ?>
			<?php echo $args['cols'] ? sprintf( 'cols="%s"', esc_attr( $args['cols'] ) ) : ''; ?>
			<?php echo true === $args['required'] ? 'required' : ''; ?>
			<?php echo true === $args['required'] ? 'required' : ''; ?>
			<?php echo true === $args['readonly'] ? 'readonly' : ''; ?>
			<?php echo true === $args['disabled'] ? 'disabled' : ''; ?>
		><?php echo esc_html( $args['value'] ); ?></textarea>
	</label>
	<?php

	return ob_get_clean();
}
