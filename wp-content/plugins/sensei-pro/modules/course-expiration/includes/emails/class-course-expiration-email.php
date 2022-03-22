<?php
/**
 * File containing the class \Sensei_Pro_Course_Expiration\Emails\Course_Expiration_Email.
 *
 * @package sensei-pro
 * @since   1.0.1
 */

namespace Sensei_Pro_Course_Expiration\Emails;

use Sensei_WC_Paid_Courses\Sensei_WC_Paid_Courses;
use Sensei_Utils;

/**
 * Course Expiration Email class.
 *
 * @since 1.0.1
 */
class Course_Expiration_Email {
	const TEMPLATE = 'emails/course-expiration.php';

	/**
	 * Remaining days.
	 *
	 * @var int
	 */
	private $remaining_days;

	/**
	 * Formatted expiration date.
	 *
	 * @var DateTimeImmutable
	 */
	private $expiration_date;

	/**
	 * Formatted expiration date.
	 *
	 * @var string
	 */
	private $formatted_expiration_date;

	/**
	 * Course_Expiration_Email constructor.
	 *
	 * @param int $remaining_days The remaining day notification.
	 */
	public function __construct( int $remaining_days = 1 ) {
		$this->remaining_days            = $remaining_days;
		$this->expiration_date           = current_datetime()->modify( "+{$remaining_days} day" );
		$this->formatted_expiration_date = $this->expiration_date->format( __( 'l, F d, Y', 'sensei-pro' ) );
	}

	/**
	 * Course_Expiration_Email constructor.
	 *
	 * @param int $user_id   User ID.
	 * @param int $course_id Course ID.
	 */
	public function send( int $user_id, int $course_id ) {
		$course_permalink = get_permalink( $course_id );
		$course_title     = get_the_title( $course_id );
		$action_label     = Sensei_Utils::user_completed_course( $course_id, $user_id )
			? __( 'View course', 'sensei-pro' )
			: __( 'Resume course', 'sensei-pro' );

		// The content for the different remaining days notifications.
		$content = [
			0 => [
				// translators: Placeholder is the course title.
				'title'   => sprintf( __( 'Your access to %s expires today', 'sensei-pro' ), $course_title ),
				'body'    => __( 'You can enjoy access to all the lesson materials and quizzes until midnight today.', 'sensei-pro' ),
				'actions' => [
					[
						'label' => $action_label,
						'href'  => $course_permalink,
					],
				],
			],
			3 => [
				// translators: Placeholder is the course title.
				'title'   => sprintf( __( 'Your access to %s expires in 3 days', 'sensei-pro' ), $course_title ),
				// translators: Placeholder is the expiration date.
				'body'    => sprintf( __( 'You can enjoy access to all the lesson materials and quizzes until midnight on %s.', 'sensei-pro' ), $this->formatted_expiration_date ),
				'actions' => [
					[
						'label' => $action_label,
						'href'  => $course_permalink,
					],
				],
			],
			7 => [
				// translators: Placeholder is the course title.
				'title'   => sprintf( __( 'Your access to %s expires in a week', 'sensei-pro' ), $course_title ),
				// translators: Placeholder is the expiration date.
				'body'    => sprintf( __( 'You can enjoy access to all the lesson materials and quizzes until midnight on %s.', 'sensei-pro' ), $this->formatted_expiration_date ),
				'actions' => [
					[
						'label' => $action_label,
						'href'  => $course_permalink,
					],
				],
			],
		];

		/**
		 * Expiration notification email content.
		 *
		 * @since 1.0.1
		 * @hook sensei_wc_paid_courses_expiration_notification_email_content
		 *
		 * @param {array}             $content         Email content.
		 * @param {int}               $remaining_days  The remaining day notification.
		 * @param {DateTimeImmutable} $expiration_date The expiration date.
		 * @param {int}               $user_id         User ID.
		 * @param {int}               $course_id       Course ID.
		 *
		 * @return {array} Email content.
		 */
		$content = apply_filters(
			'sensei_wc_paid_courses_expiration_notification_email_content',
			$content,
			$this->remaining_days,
			$this->expiration_date,
			$user_id,
			$course_id
		);

		$email_content = $content[ $this->remaining_days ];

		// Construct data array sensei needs before it can send an email.
		global $sensei_email_data;
		// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
		$sensei_email_data = [ 'heading' => $email_content['title'] ];

		ob_start();
		\Sensei_Templates::get_template(
			self::TEMPLATE,
			$email_content,
			'course-expiration/',
			untrailingslashit( dirname( __FILE__, 3 ) ) . '/templates/'
		);
		$email_html = Sensei()->emails->load_template( 'header' ) .
			ob_get_clean() .
			Sensei()->emails->load_template( 'footer' );

		$user_info = get_userdata( $user_id );

		// Send mail.
		Sensei()->emails->send(
			$user_info->user_email,
			$email_content['title'],
			$email_html
		);
	}
}
