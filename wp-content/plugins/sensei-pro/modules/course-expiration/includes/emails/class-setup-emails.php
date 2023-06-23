<?php
/**
 * File containing the class \Sensei_Pro_Course_Expiration\Emails\Setup_Emails.
 *
 * @package sensei-pro
 * @since   1.12.0
 */

namespace Sensei_Pro_Course_Expiration\Emails;

use Sensei\Internal\Emails\Email_Customization;
use Sensei\Internal\Emails\Generators\Email_Generators_Abstract;
use Sensei_Pro_Course_Expiration\Background_Jobs\Course_Expiration_Notification_Recurring_Job;
use Sensei_Pro_Course_Expiration\Emails\Generators\Course_Expiration_Email_Generator;

/**
 * Setup_Emails class.
 *
 * @since 1.12.0
 */
class Setup_Emails {
	/**
	 * Instance of class.
	 *
	 * @var self
	 */
	private static $instance;

	/**
	 * Legacy email instances.
	 *
	 * @var array
	 */
	public $legacy_mail_instaces;

	/**
	 * Fetches an instance of the class.
	 *
	 * @return self
	 */
	public static function instance() {
		if ( ! self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
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
		add_action( 'init', [ $this, 'initialize_legacy_emails' ], 20 );
		add_filter( 'sensei_email_generators', [ $this, 'initialize_templated_emails' ] );
		add_filter( 'sensei_disable_legacy_emails', [ $this, 'disable_legacy_emails' ] );
	}

	/**
	 * Setup the templated course expiration notification emails.
	 *
	 * @param Email_Generators_Abstract[] $email_generators Array of email generators.
	 *
	 * @access private
	 *
	 * @internal
	 *
	 * @return Email_Generators_Abstract[] $email_generators Array of email generators.
	 */
	public function initialize_templated_emails( $email_generators ) {
		if ( ! $email_generators ) {
			$email_generators = [];
		}

		$remaining_days = Course_Expiration_Notification_Recurring_Job::get_remaining_days_for_notifications();
		foreach ( $remaining_days as $days ) {
			$generator = new Course_Expiration_Email_Generator( Email_Customization::instance()->repository, $days );

			$email_generators[ $generator->get_identifier() ] = $generator;
		}

		return $email_generators;
	}

	/**
	 * Initialize legacy emails.
	 *
	 * @internal
	 *
	 * @access private
	 */
	public function initialize_legacy_emails() {
		$this->legacy_mail_instaces = [];

		$remaining_days = Course_Expiration_Notification_Recurring_Job::get_remaining_days_for_notifications();
		foreach ( $remaining_days as $days ) {
			$mail = new Course_Expiration_Email( $days );
			add_action( "sensei_pro_course_expiration_{$days}_days_mail", [ $mail, 'send' ], 10, 2 );
			$this->legacy_mail_instaces[] = $mail;
		}
	}

	/**
	 * Disable legacy emails.
	 *
	 * @internal
	 *
	 * @access private
	 */
	public function disable_legacy_emails() {
		remove_action( 'init', [ $this, 'initialize_legacy_emails' ], 20 );
	}
}
