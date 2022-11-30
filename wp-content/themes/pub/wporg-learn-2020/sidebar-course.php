<?php
/**
 * The sidebar containing the courses widget area.
 *
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
 *
 * @package WordPressdotorg\Theme
 */

namespace WordPressdotorg\Theme;

// Setup necessary Sensei classes for course data
use Sensei_Utils;
use Sensei_Reports_Overview_Service_Courses;

?>
<aside class="lp-sidebar">
	<div class="lp-details">
		<?php

		if ( isset( $post->ID ) ) {
			$course_id = $post->ID;

			// Get the course service class
			$course_service = new Sensei_Reports_Overview_Service_Courses();

			// Get the total number of learners enrolled in the course
			$learners = Sensei_Utils::sensei_check_for_activity(
				array(
					'type'     => 'sensei_course_status',
					'status'   => 'in-progress',
					'post__in' => $course_id,
				)
			);

			// Get the total number of learners who have completed the course
			$completions = Sensei_Utils::sensei_check_for_activity(
				array(
					'type'     => 'sensei_course_status',
					'status'   => 'complete',
					'post__in' => $course_id,
				)
			);

			// Calculate the percentage of learners who have completed the course, rounded off to 1 decimal point
			$percent_complete = round( ( $completions / $learners * 100 ), 1 );

			// Get the average grade scross all learners
			$average_grade = $course_service->get_courses_average_grade( array( $course_id ) );

			// Get the average number of days it takes to complete a course
			$avergage_days = $course_service->get_average_days_to_completion( array( $course_id ) );

			// Set up array of data to be output
			$course_data = array(
				'learners' => array(
					'label' => __( 'Number of enrolled learners', 'wporg-learn' ),
					'value' => $learners,
				),
				'completions' => array(
					'label' => __( 'Completion rate', 'wporg-learn' ),
					'value' => $percent_complete . '%',
				),
				'grade' => array(
					'label' => __( 'Average grade', 'wporg-learn' ),
					'value' => $average_grade,
				),
				'days' => array(
					'label' => __( 'Average days to complete', 'wporg-learn' ),
					'value' => $avergage_days,
				),
			);

			?>

			<div id="course-data" class="widget course_data">
				<p><strong><?php esc_html_e( 'Course data', 'wporg-learn' ); ?></strong></p>
				<ul>
					<?php
					foreach ( $course_data as $k => $data ) {
						echo '<li>' . esc_attr( $data['label'] ) . ': ' . esc_html( $data['value'] ) . '</li>';
					}
					?>
				</ul>
			</div>

			<?php
		}

		if ( is_active_sidebar( 'wporg-learn-courses' ) ) :
			dynamic_sidebar( 'wporg-learn-courses' );
		endif;
		?>
	</div>
</aside>
