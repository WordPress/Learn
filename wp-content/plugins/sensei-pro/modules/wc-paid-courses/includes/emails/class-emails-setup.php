<?php
/**
 * File containing the class Emails_Setup.
 *
 * @package sensei-pro
 * @since   1.12.0
 */

namespace Sensei_WC_Paid_Courses\Emails;

use Sensei\Internal\Emails\Email_Customization;
use Sensei\Internal\Emails\Generators\Email_Generators_Abstract;
use Sensei_WC_Paid_Courses\Background_Jobs\Student_No_Progress_Recurring_Job;
use Sensei_WC_Paid_Courses\Emails\Generators\Student_No_Progress_Email_Generator;

/**
 * Emails_Setup class.
 *
 * @since 1.12.0
 */
class Emails_Setup {
	/**
	 * Instance of class.
	 *
	 * @var self
	 */
	private static $instance;

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
		add_filter( 'sensei_email_generators', [ $this, 'initialize_templated_emails' ] );
	}

	/**
	 * Setup the no progress emails.
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

		$no_progress_periods = Student_No_Progress_Recurring_Job::get_no_progress_periods();
		foreach ( $no_progress_periods as $days ) {
			$generator = new Student_No_Progress_Email_Generator( Email_Customization::instance()->repository, $days );

			$email_generators[ $generator->get_identifier() ] = $generator;
		}

		return $email_generators;
	}
}
