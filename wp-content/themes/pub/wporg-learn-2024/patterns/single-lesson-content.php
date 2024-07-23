<?php
/**
 * Title: Single Lesson content
 * Slug: wporg-learn-2024/single-lesson-content
 * Inserter: no
 */

$course_id = get_post_meta( get_the_ID(), '_lesson_course', true );
$course_status = get_post_status( $course_id );
$has_parent_course = ! empty( $course_id ) && 'publish' === $course_status;

if ( $has_parent_course ) { ?>

	<!-- wp:pattern {"slug":"wporg-learn-2024/sensei-lesson-header"} /-->
	<!-- wp:pattern {"slug":"wporg-learn-2024/sensei-lesson-columns"} /-->

<?php } else { ?>
	
	<!-- wp:pattern {"slug":"wporg-learn-2024/sensei-lesson-standalone"} /-->

<?php } ?>
