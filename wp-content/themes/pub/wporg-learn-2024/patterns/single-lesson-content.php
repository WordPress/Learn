<?php
/**
 * Title: Single Lesson content
 * Slug: wporg-learn-2024/single-lesson-content
 * Inserter: no
 */

use function WPOrg_Learn\Sensei\{get_lesson_has_published_course};

if ( get_lesson_has_published_course( get_the_ID() ) ) { ?>

	<!-- wp:pattern {"slug":"wporg-learn-2024/sensei-lesson-header"} /-->
	<!-- wp:pattern {"slug":"wporg-learn-2024/sensei-lesson-columns"} /-->

<?php } else { ?>
	
	<!-- wp:pattern {"slug":"wporg-learn-2024/sensei-lesson-standalone"} /-->

<?php } ?>
