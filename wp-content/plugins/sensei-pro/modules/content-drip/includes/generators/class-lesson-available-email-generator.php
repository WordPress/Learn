<?php
/**
 * File containing the Lesson_Available class.
 *
 * @package sensei
 */

namespace Sensei_Pro\Modules\Content_Drip\Emails\Generators;

use Sensei\Internal\Emails\Generators\Email_Generators_Abstract;
use Sensei\Internal\Emails\Email_Repository;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Lesson_Available
 *
 * @internal
 *
 * @since 1.12.0
 */
class Lesson_Available_Email_Generator extends Email_Generators_Abstract {
	/**
	 * Identifier of the email.
	 *
	 * @var string
	 */
	const IDENTIFIER_NAME = 'content_drip';

	/**
	 * Identifier used in usage tracking.
	 *
	 * @var string
	 */
	const USAGE_TRACKING_TYPE = 'learner-lesson-available';

	/**
	 * Lesson_Available_Email_Generator constructor.
	 *
	 * @param Email_Repository $repository Email_Repository instance.
	 *
	 * @access public
	 * @since 1.13.0
	 */
	public function __construct( $repository ) {
		parent::__construct( $repository );
		add_filter( 'sensei_email_is_available', [ $this, 'maybe_make_email_available' ], 10, 2 );
	}

	/**
	 * Initialize the email hooks.
	 *
	 * @access public
	 * @since 1.12.0
	 *
	 * @return void
	 */
	public function init() {
		add_action( 'sensei_pro_content_drip_email_send', [ $this, 'lesson_available_drip_mail_to_student' ], 10, 2 );
	}

	/**
	 * Make email available if it is a content drip email.
	 *
	 * @param bool     $is_available Whether the email is available.
	 * @param \WP_Post $email     Email identifier.
	 *
	 * @internal
	 *
	 * @return bool Whether the email is available.
	 */
	public function maybe_make_email_available( $is_available, $email ) {
		return $is_available ||
			self::IDENTIFIER_NAME === get_post_meta( $email->ID, '_sensei_email_identifier', true );
	}

	/**
	 * Send email to student when a lesson becomes available.
	 *
	 * @param int   $student_id Id of the student.
	 * @param array $lessons    Lessons that became available.
	 *
	 * @access private
	 */
	public function lesson_available_drip_mail_to_student( $student_id, $lessons ) {

		if ( empty( $student_id ) || empty( $lessons ) ) {
			return;
		}

		$student   = new \WP_User( $student_id );
		$recipient = stripslashes( $student->user_email );

		foreach ( $lessons as $lesson ) {
			$this->send_email_action(
				[
					$recipient => [
						'student:id'          => $student_id,
						'student:displayname' => $student->display_name,
						'lesson:id'           => $lesson,
						'lesson:name'         => get_the_title( $lesson ),
						'lesson:url'          => esc_url( get_permalink( $lesson ) ),
						'date:dtext'          => __( 'today', 'sensei-pro' ),
					],
				]
			);
		}
	}
}
