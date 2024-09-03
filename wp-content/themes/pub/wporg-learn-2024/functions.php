<?php

namespace WordPressdotorg\Theme\Learn_2024;

use WP_HTML_Tag_Processor;
use function WPOrg_Learn\Sensei\{get_lesson_has_published_course};
use function WordPressdotorg\Theme\Learn_2024\Template_Helpers\{get_my_courses_page_url};

// Block files
require_once __DIR__ . '/src/card-featured-image-a11y/index.php';
require_once __DIR__ . '/src/code/index.php';
require_once __DIR__ . '/src/course-grid/index.php';
require_once __DIR__ . '/src/course-outline/index.php';
require_once __DIR__ . '/src/learning-pathway-cards/index.php';
require_once __DIR__ . '/src/learning-pathway-header/index.php';
require_once __DIR__ . '/src/lesson-course-info/index.php';
require_once __DIR__ . '/src/lesson-grid/index.php';
require_once __DIR__ . '/src/lesson-standalone/index.php';
require_once __DIR__ . '/src/search-results-context/index.php';
require_once __DIR__ . '/src/sensei-progress-bar/index.php';
require_once __DIR__ . '/src/sidebar-meta-list/index.php';
require_once __DIR__ . '/src/upcoming-online-workshops/index.php';
require_once __DIR__ . '/inc/block-config.php';
require_once __DIR__ . '/inc/block-hooks.php';
require_once __DIR__ . '/inc/head.php';
require_once __DIR__ . '/inc/query.php';
require_once __DIR__ . '/inc/template-helpers.php';

/**
 * Actions and filters.
 */
add_action( 'after_setup_theme', __NAMESPACE__ . '\setup' );
add_action( 'sensei_quiz_question_inside_after', __NAMESPACE__ . '\sensei_question_add_closing_fieldset' );
// Attached at 50 to inject after title, description, etc, so that only answers are in the fieldset.
add_action( 'sensei_quiz_question_inside_before', __NAMESPACE__ . '\sensei_question_add_opening_fieldset', 50 );
add_action( 'wp', __NAMESPACE__ . '\dequeue_lesson_archive_video_scripts', 20 );
add_action( 'wp_enqueue_scripts', __NAMESPACE__ . '\enqueue_assets' );
add_action( 'wp_enqueue_scripts', __NAMESPACE__ . '\maybe_enqueue_sensei_assets', 100 );
// Attached at 11 to run after scripts are registered, but before they are enqueued.
add_action( 'wp_enqueue_scripts', __NAMESPACE__ . '\defer_scripts', 11 );

// Remove Jetpack CSS on frontend
add_filter( 'jetpack_implode_frontend_css', '__return_false', 99 );
add_filter( 'post_thumbnail_html', __NAMESPACE__ . '\set_default_featured_image', 10, 5 );
add_filter( 'search_template_hierarchy', __NAMESPACE__ . '\modify_search_template' );
add_filter( 'sensei_learning_mode_lesson_status_icon', __NAMESPACE__ . '\modify_lesson_status_icon_add_aria', 10, 2 );
add_filter( 'sensei_register_post_type_course', function( $args ) {
	$args['has_archive'] = 'courses';
	return $args;
} );
add_filter( 'sensei_register_post_type_lesson', function( $args ) {
	$args['has_archive'] = 'lessons';
	return $args;
} );
add_filter( 'single_template_hierarchy', __NAMESPACE__ . '\modify_single_template' );
add_filter( 'taxonomy_template_hierarchy', __NAMESPACE__ . '\modify_taxonomy_template_hierarchy' );
add_filter( 'wp_calculate_image_sizes', __NAMESPACE__ . '\modify_grid_image_sizes', 10, 5 );
add_filter( 'wp_get_attachment_image_attributes', __NAMESPACE__ . '\eager_load_first_card_rows_images', 10, 3 );
add_filter( 'wporg_block_navigation_menus', __NAMESPACE__ . '\add_site_navigation_menus' );
add_filter( 'wporg_block_site_breadcrumbs', __NAMESPACE__ . '\set_site_breadcrumbs' );

remove_filter( 'template_include', array( 'Sensei_Templates', 'template_loader' ), 10, 1 );

/**
 * Modify the single template hierarchy to use customised copies of the Sensei Course Theme templates.
 *
 * @param array $templates Array of template files.
 * @return array
 */
function modify_single_template( $templates ) {
	if ( is_singular( 'lesson' ) ) {
		array_unshift( $templates, 'single-lesson.html' );
	} elseif ( is_singular( 'quiz' ) ) {
		array_unshift( $templates, 'single-quiz.html' );
	}

	return $templates;
}

/**
 * Modify the search template hierarchy to use search-all templates.
 *
 * @param array $templates Array of template files.
 * @return array
 */
function modify_search_template( $templates ) {
	// Should not change the search result template of course, lesson, and learning-pathway.
	// Currently, they each use their specific templates: archive-course, archive-lesson, and taxonomy-learning-pathway,
	// which have their own dedicated UI and filters.
	if (
		is_search() &&
		! ( is_post_type_archive( 'course' ) || is_post_type_archive( 'lesson' ) ) &&
		! is_tax( 'learning-pathway' )
		) {
			array_unshift( $templates, 'search-all' );
	}

	return $templates;
}

/**
 * Sets up theme defaults and registers support for various WordPress features.
 *
 * Note that this function is hooked into the after_setup_theme hook, which
 * runs before the init hook. The init hook is too late for some features, such
 * as indicating support for post thumbnails.
 */
function setup() {
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'sensei' );
	add_theme_support( 'responsive-embeds' );

	add_filter( 'mkaz_code_syntax_force_loading', '__return_true' );
	add_filter( 'mkaz_prism_css_path', __NAMESPACE__ . '\update_prism_css_path' );
}

/**
 * Enqueue scripts and styles.
 */
function enqueue_assets() {
	// The parent style is registered as `wporg-parent-2021-style`, and will be loaded unless
	// explicitly unregistered. We can load any child-theme overrides by declaring the parent
	// stylesheet as a dependency.
	$style_path = get_stylesheet_directory() . '/build/style/style-index.css';
	$style_uri = get_stylesheet_directory_uri() . '/build/style/style-index.css';
	wp_enqueue_style(
		'wporg-learn-2024-style',
		$style_uri,
		array( 'wporg-parent-2021-style', 'wporg-global-fonts' ),
		filemtime( $style_path )
	);
	wp_style_add_data( 'wporg-learn-2024-style', 'path', $style_path );

	$rtl_file = str_replace( '.css', '-rtl.css', $style_path );
	if ( is_rtl() && file_exists( $rtl_file ) ) {
		wp_style_add_data( 'wporg-learn-2024-style', 'rtl', 'replace' );
		wp_style_add_data( 'wporg-learn-2024-style', 'path', $rtl_file );
	}

	// Preload the heading font(s).
	if ( is_callable( 'global_fonts_preload' ) ) {
		/* translators: Subsets can be any of cyrillic, cyrillic-ext, greek, greek-ext, vietnamese, latin, latin-ext. */
		$subsets = _x( 'Latin', 'Heading font subsets, comma separated', 'wporg-learn' );
		// All headings.
		global_fonts_preload( 'EB Garamond, Inter', $subsets );
	}
}

/**
 * Eagerly load the images for the first row of cards, for performance (LCP metric).
 *
 * @param array   $attr       The image attributes.
 * @param WP_Post $attachment The attachment post object.
 * @param string  $size       The image size.
 * @return array The modified image attributes.
 */
function eager_load_first_card_rows_images( $attr, $attachment, $size ) {
	static $image_count = 0;

	if ( is_front_page() || is_archive() || is_search() || is_page( 'my-courses' ) ) {
		$image_count++;

		if ( $image_count <= 3 ) {
			$attr['loading'] = 'eager';
			$attr['fetchpriority'] = 'high';
		}
	}

	return $attr;
}

/**
 * Modify the image sizes attribute to improve performance by loading the size that is closest to the grid column width.
 */
function modify_grid_image_sizes( $sizes, $size, $image_src, $image_meta, $attachment_id ) {
	if ( is_front_page() || is_archive() || is_search() || is_page( 'my-courses' ) ) {
		return '(max-width: 768px) 100vw, (max-width: 1270px) 50vw, 33vw';
	}

	return $sizes;
}

/**
 * Sensei doesn't enqueue learning mode styles for Lessons which are not part of a course.
 * Enqueue the styles and add the required body class if needed.
 */
function maybe_enqueue_sensei_assets() {
	if ( ( is_singular( 'lesson' ) || is_singular( 'quiz' ) ) && ! wp_style_is( 'sensei-course-theme-style', 'enqueued' ) ) {
		wp_enqueue_style( 'sensei-learning-mode' );

		add_filter( 'body_class', function( $classes ) {
			$sensei_body_class = 'sensei-course-theme';

			if ( ! in_array( $sensei_body_class, $classes, true ) ) {
				$classes[] = $sensei_body_class;
			}

			return $classes;
		} );
	}
}

/**
 * Defer frontend script loading for performance optimization.
 */
function defer_scripts() {
	if ( is_admin() ) {
		return;
	}

	// Attempt to defer loading of all these scripts which are in the head.
	// This is not guaranteed as it depends on the loading strategy of their dependant script.
	$scripts = array(
		'jetpack-block-subscriptions',
		'jquery-core',
		'jquery-migrate',
		'lodash',
		'moment',
		'react',
		'react-dom',
		'react-jsx-runtime',
		'utils',
		'wp-a11y',
		'wp-compose',
		'wp-data',
		'wp-date',
		'wp-deprecated',
		'wp-dom',
		'wp-dom-ready',
		'wp-element',
		'wp-escape-html',
		'wp-hooks',
		'wp-html-entities',
		'wp-i18n',
		'wp-is-shallow-equal',
		'wp-keycodes',
		'wp-polyfill',
		'wp-primitives',
		'wp-priority-queue',
		'wp-private-apis',
		'wp-redux-routine',
		'wp-rich-text',
		'wp-url',
		'wp-warning',
		'wporg-calendar-script',
	);

	foreach ( $scripts as $script ) {
		wp_script_add_data( $script, 'strategy', 'defer' );
		wp_script_add_data( $script, 'group', 0 );
	}
}

/**
 * Dequeue Sensei video scripts loaded on lessons archive.
 * Sensei LMS and Sensei Pro both enqueue video player scripts for lesson posts,
 * but these are not needed on archives and cause performance issues.
 *
 * See class Sensei_Pro_Interactive_Blocks\Interactive_Blocks::enqueue_frontend_assets().
 * See class Sensei_Course_Video_Settings::enqueue_frontend_scripts().
 */
function dequeue_lesson_archive_video_scripts() {
	if ( is_admin() || ! is_post_type_archive( 'lesson' ) ) {
		return;
	}

	global $wp_filter;

	if ( isset( $wp_filter['wp_enqueue_scripts'] ) ) {
		foreach ( $wp_filter['wp_enqueue_scripts']->callbacks as $priority => $callbacks ) {
			foreach ( $callbacks as $key => $callback ) {
				if ( is_array( $callback['function'] ) ) {
					$caller = $callback['function'][0];
					$name = $callback['function'][1];

					if (
						( 'enqueue_frontend_scripts' === $name || 'enqueue_frontend_assets' === $name )
						&& is_object( $caller )
						&& ( get_class( $caller ) === 'Sensei_Course_Video_Settings' || get_class( $caller ) === 'Sensei_Pro_Interactive_Blocks\Interactive_Blocks' )
					) {
						remove_action( 'wp_enqueue_scripts', $callback['function'], $priority );
					}
				}
			}
		}
	}
}

/**
 * Customize the syntax highlighter style.
 * See https://github.com/PrismJS/prism-themes.
 *
 * @param string $path Path to the file to override, relative to the theme.
 * @return string
 */
function update_prism_css_path( $path ) {
	return '/build/prism/style-index.css';
}

/**
 * Provide a list of local navigation menus.
 */
function add_site_navigation_menus( $menus ) {
	$menu = array(
		array(
			'label' => __( 'Courses', 'wporg-learn' ),
			'url'   => '/courses/',
		),
		array(
			'label' => __( 'Lessons', 'wporg-learn' ),
			'url'   => '/lessons/',
		),
		array(
			'label' => __( 'Online Workshops', 'wporg-learn' ),
			'url'   => '/online-workshops/',
		),
		array(
			'label'     => __( 'My courses', 'wporg-learn' ),
			'url'       => get_my_courses_page_url(),
			'className' => 'has-separator',
		),
	);

	$learning_pathways = get_terms(
		array(
			'taxonomy'   => 'learning-pathway',
			'hide_empty' => true,
		)
	);

	if ( empty( $learning_pathways ) || is_wp_error( $learning_pathways ) ) {
		$menus['learn'] = $menu;

		return $menus;
	}

	$learning_pathways_menu = array(
		'label'   => __( 'Learning Pathways', 'wporg-learn' ),
		'submenu' => array_map( function( $term ) {
			return array(
				'label' => $term->name,
				'url'   => get_term_link( $term ),
			);
		}, $learning_pathways ),
	);

	array_unshift( $menu, $learning_pathways_menu );

	$menus['learn'] = $menu;

	return $menus;
}

/**
 * Get the titles and descriptions for the learning pathway levels.
 *
 * @param string $learning_pathway The learning pathway name.
 * @return array The content for the learning pathway levels.
 */
function get_learning_pathway_level_content( $learning_pathway ) {
	$content = array(
		'user' => array(
			'beginner' => array(
				'title' => __( 'Beginner WordPress users', 'wporg-learn' ),
				'description' => __( 'You’re new to WordPress, or building websites, and want the essentials.', 'wporg-learn' ),
				'see_all_aria_label' => 'See all beginner WordPress user learning pathways',
			),
			'intermediate' => array(
				'title' => __( 'Intermediate WordPress users', 'wporg-learn' ),
				'description' => __( 'You’re comfortable setting up your site and making small changes or you’ve already completed the Beginner course.', 'wporg-learn' ),
				'see_all_aria_label' => 'See all intermediate WordPress user learning pathways',
			),
			'advanced' => array(
				'title' => __( 'Advanced WordPress users', 'wporg-learn' ),
				'description' => __( 'You’re confident using multiple plugins and know how to customize a Block theme, or you’ve already completed the Intermediate course.', 'wporg-learn' ),
				'see_all_aria_label' => 'See all advanced WordPress user learning pathways',
			),
		),
		'developer' => array(
			'beginner' => array(
				'title' => __( 'Beginner development concepts', 'wporg-learn' ),
				'description' => __( 'You’re new to development or have experience using WordPress’s no-code features and want to do more.', 'wporg-learn' ),
				'see_all_aria_label' => 'See all beginner development concepts learning pathways',
			),
			'intermediate' => array(
				'title' => __( 'Intermediate development concepts', 'wporg-learn' ),
				'description' => __( 'You’re comfortable writing code and want to extend WordPress with your own plugin or theme.', 'wporg-learn' ),
				'see_all_aria_label' => 'See all intermediate development concepts learning pathways',
			),
			'advanced' => array(
				'title' => __( 'Advanced development concepts', 'wporg-learn' ),
				'description' => __( 'You’re confident in the WordPress development environment or have already built your own plugin or theme.', 'wporg-learn' ),
				'see_all_aria_label' => 'See all advanced development concepts learning pathways',
			),
		),
	);

	return $content[ $learning_pathway ];
}

/**
 * Filters breadcrumb items for the site-breadcrumb block.
 *
 * @param array $breadcrumbs The current breadcrumbs.
 *
 * @return array The modified breadcrumbs.
 */
function set_site_breadcrumbs( $breadcrumbs ) {
	if ( isset( $breadcrumbs[0] ) ) {
		// Change the title of the first breadcrumb to 'Home'.
		$breadcrumbs[0]['title'] = 'Home';
	}

	$post_id = get_the_ID();
	$post_type = get_post_type();

	if ( is_singular() && 'page' !== $post_type && 'post' !== $post_type ) {
		// CPT single page: Insert the archive breadcrumb into the second position.
		$post_type_object = get_post_type_object( $post_type );
		$archive_title = $post_type_object->labels->name;
		$archive_url = get_post_type_archive_link( $post_type );

		$archive_breadcrumb = array(
			'url' => $archive_url,
			'title' => $archive_title,
		);

		array_splice( $breadcrumbs, 1, 0, array( $archive_breadcrumb ) );

		// If it's a lesson single page, change the second breadcrumb to the course archive
		// and insert the lesson course breadcrumb into the third position.
		if ( is_singular( 'lesson' ) ) {
			if ( ! get_lesson_has_published_course( $post_id ) ) {
				return $breadcrumbs;
			}

			$post_type_object = get_post_type_object( 'course' );
			$archive_title = $post_type_object->labels->name;
			$archive_url = get_post_type_archive_link( 'course' );

			$archive_breadcrumb = array(
				'url' => $archive_url,
				'title' => $archive_title,
			);

			$lesson_course_id = get_post_meta( $post_id, '_lesson_course', true );
			$lesson_course_title = get_the_title( $lesson_course_id );
			$lesson_course_link = get_permalink( $lesson_course_id );
			$lesson_course_breadcrumb = array(
				'url' => $lesson_course_link,
				'title' => $lesson_course_title,
			);

			$breadcrumbs[1] = $archive_breadcrumb;
			array_splice( $breadcrumbs, 2, 0, array( $lesson_course_breadcrumb ) );
		}
	} else {
		// Add the ancestors of the current page to the breadcrumbs.
		$ancestors = get_post_ancestors( $post_id );

		if ( ! empty( $ancestors ) ) {
			foreach ( $ancestors as $ancestor ) {
				$ancestor_post = get_post( $ancestor );

				$ancestor_breadcrumb = array(
					'url' => get_permalink( $ancestor_post ),
					'title' => get_the_title( $ancestor_post ),
				);

				array_splice( $breadcrumbs, 1, 0, array( $ancestor_breadcrumb ) );
			}
		}
	}

	// Ensure breadcrumbs are displayed only when there are at least 3 levels.
	$breadcrumb_level = count( $breadcrumbs );
	if ( $breadcrumb_level < 3 ) {
		$breadcrumbs = array();
	}

	return $breadcrumbs;
}

/**
 * Set the default featured image.
 *
 * @param string       $html The HTML for the featured image.
 * @param int          $post_id The post ID.
 * @param int          $post_thumbnail_id The post thumbnail ID.
 * @param string|array $size The image size.
 * @param string|array $attr The image attributes.
 * @return string The modified HTML for the featured image.
 */
function set_default_featured_image( $html, $post_id, $post_thumbnail_id, $size, $attr ) {
	if ( ! $html ) {
		return '<img src="https://s.w.org/images/learn-thumbnail-fallback.jpg?v=5" alt="' . esc_attr( get_the_title( $post_id ) ) . '" />';
	}

	return $html;
}

/**
 * Count the number of courses for a given learning pathway and level.
 *
 * @param int $learning_pathway_id The ID of the learning pathway.
 * @param int $level_id The ID of the level.
 * @return int The number of courses.
 */
function count_courses( $learning_pathway_id, $level_id ) {
	if ( ! $learning_pathway_id || ! $level_id ) {
		return 0;
	}

	$args = array(
		'post_type' => 'course',
		'post_status' => 'publish',
		'fields' => 'ids',
		'tax_query' => array(
			'relation' => 'AND',
			array(
				'taxonomy' => 'learning-pathway',
				'field'    => 'term_id',
				'terms'    => $learning_pathway_id,
			),
			array(
				'taxonomy' => 'level',
				'field'    => 'term_id',
				'terms'    => $level_id,
			),
		),
	);

	$query = new \WP_Query( $args );
	return $query->found_posts;
}

/**
 * Modify the taxonomy template hierarchy
 * Only use the the custom Learning Pathway template with level sections if there are enough learning pathways to fill the sections.
 * Minimum 3 learning pathways in one of the sections, and minimum 2 learning pathways in all sections.
 *
 * @param array $templates Array of template files.
 * @return array $templates Modified array of template files.
 */
function modify_taxonomy_template_hierarchy( $templates ) {
	if ( is_tax( 'learning-pathway' ) ) {
		$learning_pathway_id = get_queried_object_id();

		$beginner_course_count = count_courses( $learning_pathway_id, get_term_by( 'slug', 'beginner', 'level' )->term_id );
		$intermediate_course_count = count_courses( $learning_pathway_id, get_term_by( 'slug', 'intermediate', 'level' )->term_id );
		$advanced_course_count = count_courses( $learning_pathway_id, get_term_by( 'slug', 'advanced', 'level' )->term_id );

		$all_sections_have_2 = $beginner_course_count >= 2 && $intermediate_course_count >= 2 && $advanced_course_count >= 2;
		$a_section_has_3 = $beginner_course_count >= 3 || $intermediate_course_count >= 3 || $advanced_course_count >= 3;

		$should_use_learning_pathway_sections = $all_sections_have_2 && $a_section_has_3;

		if ( ! $should_use_learning_pathway_sections ) {
			// Leave only the last template, which is the base taxonomy template.
			$templates = array( array_pop( $templates ) );
		}
	}

	return $templates;
}

/**
 * Filter the lesson status icon.
 *
 * @param string $icon   The icon HTML.
 * @param string $status The lesson status.
 *
 * @return string The updated icon HTML with aria data.
 */
function modify_lesson_status_icon_add_aria( $icon, $status ) {
	// These statuses have been copied from Sensei\Blocks\Course_Theme\Course_Navigation\ICONS.
	$labels = array(
		'not-started' => __( 'Not started', 'wporg-learn' ),
		'in-progress' => __( 'In progress', 'wporg-learn' ),
		'ungraded'    => __( 'Ungraded', 'wporg-learn' ),
		'completed'   => __( 'Completed', 'wporg-learn' ),
		'failed'      => __( 'Failed', 'wporg-learn' ),
		'locked'      => __( 'Locked', 'wporg-learn' ),
		'preview'     => __( 'Preview', 'wporg-learn' ),
	);

	if ( ! isset( $labels[ $status ] ) ) {
		return $icon;
	}

	$html = new WP_HTML_Tag_Processor( $icon );
	$html->next_tag( 'svg' );
	$html->set_attribute( 'aria-label', $labels[ $status ] );
	$html->set_attribute( 'role', 'img' );
	return $html->get_updated_html();
}

/**
 * Use the "before question" hook to open a fieldset and add a ledgend to label the input options.
 *
 * @param int $question_id The question ID.
 */
function sensei_question_add_opening_fieldset( $question_id ) {
	$title = strip_tags( get_the_title( $question_id ) );
	?>
	<fieldset>
		<legend class="screen-reader-text"><?php echo esc_html( $title ); ?></legend>
	<?php
}

/**
 * Use the "after question" hook to close the fieldset opened in `sensei_question_add_opening_fieldset`.
 */
function sensei_question_add_closing_fieldset() {
	echo '</fieldset>';
}
