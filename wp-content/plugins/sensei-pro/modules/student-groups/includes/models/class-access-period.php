<?php
/**
 * File containing the class \Sensei_Pro_Student_Groups\Models\Access_Period.
 *
 * @package student-groups
 * @since   1.4.0
 */

namespace Sensei_Pro_Student_Groups\Models;

use DateTime;

/**
 * Class for representing an access period.
 *
 * @since 1.4.0
 */
class Access_Period {

	/**
	 * Access period start date.
	 *
	 * @var DateTime|null
	 */
	private $start_date;

	/**
	 * Access period end date.
	 *
	 * @var DateTime|null
	 */
	private $end_date;

	/**
	 * Access period constructor.
	 *
	 * @param DateTime|null $start_date Access period start date.
	 * @param DateTime|null $end_date   Access period end date.
	 */
	public function __construct( $start_date, $end_date ) {
		$this->start_date = $start_date;
		$this->end_date   = $end_date;
	}

	/**
	 * Returns access period start date.
	 *
	 * @return DateTime|null
	 */
	public function get_start_date() {
		return $this->start_date;
	}

	/**
	 * Returns access period end date.
	 *
	 * @return DateTime|null
	 */
	public function get_end_date() {
		return $this->end_date;
	}

	/**
	 * Check if there is a start date.
	 *
	 * @return bool
	 */
	public function has_start_date(): bool {
		return (bool) $this->get_start_date();
	}

	/**
	 * Check if there is an end date.
	 *
	 * @return bool
	 */
	public function has_end_date(): bool {
		return (bool) $this->get_end_date();
	}

	/**
	 * Set access period start date.
	 *
	 * @param DateTime|null $start_date
	 */
	public function set_start_date( $start_date ) {
		$this->start_date = $start_date;
	}

	/**
	 * Set access period end date.
	 *
	 * @param DateTime|null $end_date
	 */
	public function set_end_date( $end_date ) {
		$this->end_date = $end_date;
	}

	/**
	 * Returns access period status.
	 *
	 * @param DateTime|null $at Return status at given date and time.
	 *
	 * @return string
	 */
	public function get_status( DateTime $at = null ): string {
		if ( is_null( $this->get_end_date() ) ) {
			return 'active';
		}

		if ( is_null( $at ) ) {
			$at = new DateTime();
		}

		if ( $this->has_ended( $at ) ) {
			return 'expired';
		}

		return 'active';
	}

	/**
	 * Check if the access period has ended.
	 *
	 * @since 1.4.0
	 *
	 * @param DateTime|null $at The DateTime. Defaults to the current time.
	 *
	 * @return bool
	 */
	public function has_ended( DateTime $at = null ): bool {
		if ( is_null( $at ) ) {
			$at = new DateTime();
		}

		return $this->get_end_date()
			&& $at->getTimestamp() > $this->get_end_date()->getTimestamp();
	}

	/**
	 * Check if the access period is in the future.
	 *
	 * @since 1.4.0
	 *
	 * @param DateTime|null $at The DateTime. Defaults to the current time.
	 *
	 * @return bool
	 */
	public function is_future( DateTime $at = null ): bool {
		if ( is_null( $at ) ) {
			$at = new DateTime();
		}

		return $this->get_start_date()
			&& $at->getTimestamp() < $this->get_start_date()->getTimestamp();
	}

	/**
	 * Check if the access period is active.
	 *
	 * @since 1.4.0
	 *
	 * @param DateTime|null $at The DateTime. Defaults to the current time.
	 *
	 * @return bool
	 */
	public function is_active( DateTime $at = null ): bool {
		return ! $this->is_future( $at ) && ! $this->has_ended( $at );
	}
}
