<?php

namespace WPOrg_Learn\Blocks;

use Error;
use Sensei_Lesson;
use Sensei_Utils;
use Sensei_Reports_Overview_Service_Courses;
use function WordPressdotorg\Locales\get_locale_name_from_code;
use function WPOrg_Learn\{get_build_path, get_build_url, get_js_path, get_views_path};
use function WPOrg_Learn\Form\render_workshop_application_form;
use function WPOrg_Learn\Post_Meta\get_workshop_duration;

defined( 'WPINC' ) || die();

/**
 * Views.
 */
require_once get_views_path() . 'block-course-status.php';
require_once get_views_path() . 'block-learning-duration.php';
require_once get_views_path() . 'block-lesson-count.php';

/**
 * Actions and filters.
 */
add_action( 'init', __NAMESPACE__ . '\register_types' );
add_action( 'enqueue_block_editor_assets', __NAMESPACE__ . '\enqueue_block_style_assets' );
add_action( 'enqueue_block_editor_assets', __NAMESPACE__ . '\enqueue_course_grid_assets' );
add_action( 'wp_enqueue_scripts', __NAMESPACE__ . '\enqueue_block_style_assets' );

/**
 * Register block types.
 *
 * @return void
 */
function register_types() {
	register_course_data();
	register_course_status();
	register_learning_duration();
	register_lesson_count();
	register_lesson_plan_actions();
	register_lesson_plan_details();
	register_workshop_details();
	register_workshop_application_form();
}

/**
 * Register Lesson Plan Actions block type and related assets.
 *
 * @throws Error If the build files are not found.
 */
function register_lesson_plan_actions() {
	$script_asset_path = get_build_path() . 'lesson-plan-actions.asset.php';
	if ( ! is_readable( $script_asset_path ) ) {
		throw new Error(
			'You need to run `npm start` or `npm run build` for the "wporg-learn/lesson-plan-actions" block first.'
		);
	}

	$script_asset = require $script_asset_path;
	wp_register_script(
		'lesson-plan-actions-editor-script',
		get_build_url() . 'lesson-plan-actions.js',
		$script_asset['dependencies'],
		$script_asset['version'],
		true
	);

	wp_register_style(
		'lesson-plan-actions-style',
		get_build_url() . 'style-lesson-plan-actions.css',
		array(),
		filemtime( get_build_path() . 'style-lesson-plan-actions.css' )
	);

	register_block_type( 'wporg-learn/lesson-plan-actions', array(
		'editor_script'   => 'lesson-plan-actions-editor-script',
		'style'           => 'lesson-plan-actions-style',
		'render_callback' => __NAMESPACE__ . '\lesson_plan_actions_render_callback',
	) );
}

/**
 * Render the block content (html) on the frontend of the site.
 *
 * @param array  $attributes
 * @param string $content
 * @return string HTML output used by the block
 */
function lesson_plan_actions_render_callback( $attributes, $content ) {
	if ( get_post_type() !== 'lesson-plan' ) {
		return;
	}

	$post = get_post();

	ob_start();
	require get_views_path() . 'block-lesson-plan-actions.php';

	return ob_get_clean();
}

/**
 * Register Lesson Plan Details block type and related assets.
 *
 * @throws Error If the build files are not found.
 */
function register_lesson_plan_details() {
	$script_asset_path = get_build_path() . 'lesson-plan-details.asset.php';
	if ( ! is_readable( $script_asset_path ) ) {
		throw new Error(
			'You need to run `npm start` or `npm run build` for the "wporg-learn/lesson-plan-details" block first.'
		);
	}

	$script_asset = require $script_asset_path;
	wp_register_script(
		'lesson-plan-details-editor-script',
		get_build_url() . 'lesson-plan-details.js',
		$script_asset['dependencies'],
		$script_asset['version'],
		true,
	);

	wp_register_style(
		'lesson-plan-details-style',
		get_build_url() . 'style-lesson-plan-details.css',
		array(),
		filemtime( get_build_path() . 'style-lesson-plan-details.css' )
	);

	register_block_type( 'wporg-learn/lesson-plan-details', array(
		'editor_script'   => 'lesson-plan-details-editor-script',
		'style'           => 'lesson-plan-details-style',
		'render_callback' => __NAMESPACE__ . '\lesson_plan_details_render_callback',
	) );
}

/**
 * Render the block content (html) on the frontend of the site.
 *
 * @param array  $attributes
 * @param string $content
 * @return string HTML output used by the block
 */
function lesson_plan_details_render_callback( $attributes, $content ) {
	if ( get_post_type() !== 'lesson-plan' ) {
		return;
	}

	$details = wporg_learn_get_lesson_plan_taxonomy_data( get_the_ID(), 'single' );

	ob_start();
	require get_views_path() . 'block-lesson-plan-details.php';

	return ob_get_clean();
}

/**
 * Register Course Data block type and related assets.
 *
 * @throws Error If the build files are not found.
 */
function register_course_data() {
	$script_asset_path = get_build_path() . 'course-data.asset.php';
	if ( ! is_readable( $script_asset_path ) ) {
		throw new Error(
			'You need to run `npm start` or `npm run build` for the "wporg-learn/course-data" block first.'
		);
	}

	$script_asset = require $script_asset_path;
	wp_register_script(
		'course-data-editor-script',
		get_build_url() . 'course-data.js',
		$script_asset['dependencies'],
		$script_asset['version'],
		true,
	);

	wp_register_style(
		'course-data-style',
		get_build_url() . 'style-course-data.css',
		array(),
		filemtime( get_build_path() . 'style-course-data.css' )
	);

	register_block_type( 'wporg-learn/course-data', array(
		'editor_script'   => 'course-data-editor-script',
		'style'           => 'course-data-style',
		'render_callback' => __NAMESPACE__ . '\course_data_render_callback',
	) );
}

/**
 * Render the block content (html) on the frontend of the site.
 *
 * @param array  $attributes
 * @param string $content
 * @return string HTML output used by the block
 */
function course_data_render_callback( $attributes, $content ) {
	if ( get_post_type() !== 'course' ) {
		return;
	}

	$course_service = new Sensei_Reports_Overview_Service_Courses();
	$post           = get_post();
	$course_id      = $post->ID;

	// Get the total number of learners enrolled in the course
	$learners = Sensei_Utils::sensei_check_for_activity(
		array(
			'type'     => 'sensei_course_status',
			'status'   => 'in-progress',
			'post__in' => $course_id,
		)
	);

	// Get the average grade scross all learners
	$average_grade = round( $course_service->get_courses_average_grade( array( $course_id ) ), 0 );

	// Get the average number of days it takes to complete a course
	$average_days = $course_service->get_average_days_to_completion( array( $course_id ) );

	// Set up array of data to be used
	$data = array(
		'learners' => array(
			'label' => __( 'Enrolled learners', 'wporg-learn' ),
			'value' => $learners,
		),
		'grade' => array(
			'label' => __( 'Average final grade', 'wporg-learn' ),
			'value' => $average_grade . '%',
		),
		'days' => array(
			'label' => __( 'Average days to completion', 'wporg-learn' ),
			'value' => $average_days,
		),
	);

	ob_start();
	require get_views_path() . 'block-course-data.php';

	return ob_get_clean();
}

/**
 * Register Workshop Details block type and related assets.
 *
 * @throws Error If the build files are not found.
 */
function register_workshop_details() {
	$script_asset_path = get_build_path() . 'workshop-details.asset.php';
	if ( ! is_readable( $script_asset_path ) ) {
		throw new Error(
			'You need to run `npm start` or `npm run build` for the "wporg-learn/workshop-details" block first.'
		);
	}

	$script_asset = require $script_asset_path;
	wp_register_script(
		'workshop-details-editor-script',
		get_build_url() . 'workshop-details.js',
		$script_asset['dependencies'],
		$script_asset['version'],
		true,
	);

	wp_register_style(
		'workshop-details-style',
		get_build_url() . 'style-workshop-details.css',
		array(),
		filemtime( get_build_path() . 'style-workshop-details.css' )
	);

	register_block_type( 'wporg-learn/workshop-details', array(
		'editor_script'   => 'workshop-details-editor-script',
		'style'           => 'workshop-details-style',
		'render_callback' => __NAMESPACE__ . '\workshop_details_render_callback',
	) );
}

/**
 * Render the block content (html) on the frontend of the site.
 *
 * @param array  $attributes
 * @param string $content
 * @return string HTML output used by the block
 */
function workshop_details_render_callback( $attributes, $content ) {
	if ( get_post_type() !== 'wporg_workshop' ) {
		return;
	}

	$post      = get_post();
	$topic_ids = wp_get_post_terms( $post->ID, 'topic', array( 'fields' => 'ids' ) );
	$level     = wp_get_post_terms( $post->ID, 'level', array( 'fields' => 'names' ) );
	$captions  = get_post_meta( $post->ID, 'video_caption_language' );

	$version_ids    = wp_get_post_terms( $post->ID, 'wporg_wp_version', array( 'fields' => 'ids' ) );
	$version_names  = wp_get_post_terms( $post->ID, 'wporg_wp_version', array( 'fields' => 'names' ) );

	$topic_names = array();
	foreach ( $topic_ids as $id ) {
		$topic_names[] = get_term( $id )->name;
	}

	$fields = array(
		'length' => array(
			'label' => __( 'Length', 'wporg-learn' ),
			'param' => array(),
			'value' => array( get_workshop_duration( $post, 'string' ) ),
		),
		'topic' => array(
			'label' => __( 'Topic', 'wporg-learn' ),
			'param' => $topic_ids,
			'value' => $topic_names,
		),
		'wp_version' => array(
			'label' => __( 'Related Version', 'wporg-learn' ),
			'param' => $version_ids,
			'value' => $version_names,
		),
		'level' => array(
			'label' => __( 'Level', 'wporg-learn' ),
			'param' => array(),
			'value' => $level,
		),
		'language' => array(
			'label' => __( 'Language', 'wporg-learn' ),
			'param' => array( $post->language ),
			'value' => array( esc_html( get_locale_name_from_code( $post->language, 'native' ) ) ),
		),
		'captions' => array(
			'label' => __( 'Subtitles', 'wporg-learn' ),
			'param' => $captions,
			'value' => array_map(
				function( $caption_lang ) {
					return esc_html( get_locale_name_from_code( $caption_lang, 'native' ) );
				},
				$captions
			),
		),
	);

	// Remove fields with empty values.
	$fields = array_filter( $fields, function( $data ) {
		return $data['value'];
	} );

	$lesson_id = get_post_meta( $post->ID, 'linked_lesson_id', true );
	$quiz_url = '';
	if ( $lesson_id && Sensei_Lesson::lesson_quiz_has_questions( $lesson_id ) ) {
		$quiz_id = Sensei()->lesson->lesson_quizzes( $lesson_id );
		if ( $quiz_id ) {
			$quiz_url = get_permalink( $quiz_id );
		}
	}

	ob_start();
	require get_views_path() . 'block-workshop-details.php';

	return ob_get_clean();
}

/**
 * Register Workshop Application Form block type and related assets.
 *
 * @throws Error If the build files are not found.
 */
function register_workshop_application_form() {
	$script_asset_path = get_build_path() . 'workshop-application-form.asset.php';
	if ( ! is_readable( $script_asset_path ) ) {
		throw new Error(
			'You need to run `npm start` or `npm run build` for the "wporg-learn/workshop-application-form" block first.'
		);
	}

	$script_asset = require $script_asset_path;
	wp_register_script(
		'workshop-application-form-editor-script',
		get_build_url() . 'workshop-application-form.js',
		$script_asset['dependencies'],
		$script_asset['version']
	);

	$script_asset_path = get_build_path() . 'form.asset.php';
	if ( ! is_readable( $script_asset_path ) ) {
		throw new Error(
			'You need to run `npm start` or `npm run build` first.'
		);
	}

	$script_asset = require $script_asset_path;
	wp_register_script(
		'workshop-application-form-script',
		get_build_url() . 'form.js',
		array_merge( $script_asset['dependencies'], array( 'jquery', 'select2' ) ),
		$script_asset['version'],
		true
	);

	register_block_type( 'wporg-learn/workshop-application-form', array(
		'editor_script'   => 'workshop-application-form-editor-script',
		'script'          => 'workshop-application-form-script',
		'style'           => 'select2',
		'render_callback' => __NAMESPACE__ . '\workshop_application_form_render_callback',
	) );
}

/**
 * Render the Workshop Application Form block markup.
 *
 * @return string
 */
function workshop_application_form_render_callback() {
	return render_workshop_application_form();
}

/**
 * Enqueue scripts and stylesheets for custom block styles.
 *
 * @throws Error If the build files are not found.
 */
function enqueue_block_style_assets() {
	if ( is_admin() ) {
		$script_asset_path = get_build_path() . 'block-styles.asset.php';
		if ( ! file_exists( $script_asset_path ) ) {
			throw new Error(
				'You need to run `npm start` or `npm run build` for block styles first.'
			);
		}

		$script_asset = require $script_asset_path;
		wp_enqueue_script(
			'wporg-learn-block-styles',
			get_build_url() . 'block-styles.js',
			$script_asset['dependencies'],
			$script_asset['version']
		);
	}

	wp_enqueue_style(
		'wporg-learn-block-styles',
		get_build_url() . 'style-block-styles.css',
		array(),
		filemtime( get_build_path() . 'style-block-styles.css' )
	);
}

/**
 * Enqueue course grid assets.
 *
 * @throws Error If the build files are not found.
 */
function enqueue_course_grid_assets() {
	$script_asset_path = get_build_path() . 'course-grid.asset.php';
	if ( ! is_readable( $script_asset_path ) ) {
		throw new Error(
			'You need to run `npm start` or `npm run build` for the "wporg-learn/course-grid" block first.'
		);
	}

	$script_asset = require $script_asset_path;
	wp_enqueue_script(
		'wporg-learn-course-grid',
		get_build_url() . 'course-grid.js',
		$script_asset['dependencies'],
		$script_asset['version'],
		true
	);
}

/**
 * Register the learning duration block.
 */
function register_learning_duration() {
	register_block_type(
		get_js_path() . 'learning-duration/',
		array(
			'render_callback' => function( $attributes, $content, $block ) {
				return \WPOrg_Learn\View\Blocks\Learning_Duration\render( $attributes, $content, $block );
			},
		)
	);
}

/**
 * Register the lesson count block.
 */
function register_lesson_count() {
	register_block_type(
		get_js_path() . 'lesson-count/',
		array(
			'render_callback' => function( $attributes, $content, $block ) {
				return \WPOrg_Learn\View\Blocks\Lesson_Count\render( $attributes, $content, $block );
			},
		)
	);
}

/**
 * Register the course status block.
 */
function register_course_status() {
	register_block_type(
		get_js_path() . 'course-status/',
		array(
			'render_callback' => function( $attributes, $content, $block ) {
				return \WPOrg_Learn\View\Blocks\Course_Status\render( $attributes, $content, $block );
			},
		)
	);
}

