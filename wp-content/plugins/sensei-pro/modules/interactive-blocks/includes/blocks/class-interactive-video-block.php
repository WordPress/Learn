<?php
/**
 * Interactive Video block.
 *
 * @package sensei-pro
 */

namespace Sensei_Pro_Interactive_Blocks;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Interactive Video block class.
 */
class Interactive_Video_Block {
	/**
	 * Interactive_Video_Block constructor.
	 */
	public function __construct() {
		add_action( 'init', [ $this, 'init' ], 11 );
		add_action( 'save_post', [ $this, 'log_on_save_post' ], 10, 2 );
		add_filter( 'embed_oembed_html', [ $this, 'maybe_replace_iframe_youtube_url' ], 12, 2 );
	}

	/**
	 * Initialize block.
	 *
	 * @access private
	 */
	public function init() {
		register_block_type_from_metadata(
			SENSEI_IB_PLUGIN_DIR_PATH . 'assets/interactive-video/interactive-video-block',
			[
				'editor_script' => 'sensei-interactive-blocks-editor-script',
				'view_script'   => 'sensei-interactive-blocks-frontend-script',
				'style'         => 'sensei-interactive-blocks-styles',
			]
		);
	}

	/**
	 * Log Interactive Video block data on post save.
	 *
	 * @access private
	 *
	 * @param int      $post_id Post ID.
	 * @param \WP_Post $post    Post object.
	 */
	public function log_on_save_post( $post_id, $post ) {
		// Skip when log function does not exist (created by Sensei LMS).
		if ( ! function_exists( 'sensei_log_event' ) ) {
			return;
		}

		// Skip post revision.
		if ( wp_is_post_revision( $post_id ) ) {
			return;
		}

		// Skip meta box saving.
		// phpcs:ignore WordPress.Security.NonceVerification
		if ( isset( $_REQUEST['meta-box-loader'] ) && '1' === $_REQUEST['meta-box-loader'] ) {
			return;
		}

		// Skip if `rest_get_route_for_post_type_items` does not exist.
		if ( ! function_exists( 'rest_get_route_for_post_type_items' ) ) {
			return;
		}

		// Skip if it's not a post type save.
		$post_type_endpoint = rest_get_route_for_post_type_items( $post->post_type );
		$uri                = isset( $_SERVER['REQUEST_URI'] ) ? esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '';

		if ( ! $post_type_endpoint || false === strpos( $uri, $post_type_endpoint ) ) {
			return;
		}

		$content                 = $post->post_content;
		$interactive_video_count = substr_count( $content, '<!-- wp:sensei-pro/interactive-video' );
		$break_point_count       = substr_count( $content, '<!-- wp:sensei-pro/break-point' );

		$videopress_count = preg_match_all( '/<!-- wp:sensei-pro\/interactive-video.*?{.*?"videoType":"videopress".*?}.*?-->/', $content );
		$youtube_count    = preg_match_all( '/<!-- wp:sensei-pro\/interactive-video.*?{.*?"videoType":"youtube".*?}.*?-->/', $content );
		$vimeo_count      = preg_match_all( '/<!-- wp:sensei-pro\/interactive-video.*?{.*?"videoType":"vimeo".*?}.*?-->/', $content );

		$default_count    = preg_match_all( '/<!-- wp:sensei-pro\/interactive-video(?![^(-->)]+"videoType").*?-->/', $content );
		$video_file_count = preg_match_all( '/<!-- wp:sensei-pro\/interactive-video.*?{.*?"videoType":"video-file".*?}.*?-->/', $content );
		$video_file_count = $video_file_count + $default_count;

		// Count required blocks inside break points.
		$break_points_with_required_blocks        = 0;
		$required_blocks_inside_break_point_count = 0;
		preg_match_all( '/<!-- wp:sensei-pro\/break-point(.*?)<!-- \/wp:sensei-pro\/break-point -->/s', $content, $matches, PREG_PATTERN_ORDER );

		// Loops through break points content.
		foreach ( $matches[0] as $match ) {
			$required_count = substr_count( $match, '"required":true' );

			if ( $required_count > 0 ) {
				$break_points_with_required_blocks++;
			}

			$required_blocks_inside_break_point_count += $required_count;
		}

		$event_properties = [
			'post_type'                                => $post->post_type,
			'interactive_video_count'                  => $interactive_video_count,
			'break_point_count'                        => $break_point_count,
			'break_points_with_required_blocks'        => $break_points_with_required_blocks,
			'required_blocks_inside_break_point_count' => $required_blocks_inside_break_point_count,
			'videopress_count'                         => $videopress_count,
			'youtube_count'                            => $youtube_count,
			'vimeo_count'                              => $vimeo_count,
			'video_file_count'                         => $video_file_count,
		];

		sensei_log_event( 'save_post_interactive_video', $event_properties );
	}

	/**
	 * Replace YouTube iframe URL enabling JS API and providing origin if it not being done by
	 * Sensei LMS.
	 *
	 * @param string $html
	 * @param string $url
	 *
	 * @return string
	 */
	public function maybe_replace_iframe_youtube_url( $html, $url ) {
		add_filter( 'deprecated_function_trigger_error', [ $this, 'disable_deprecated_warnings' ] );
		$check_deprecated_replace_iframe_url = class_exists( '\Sensei_Course_Video_Blocks_Youtube_Extension' ) && has_filter( 'embed_oembed_html', [ \Sensei_Course_Video_Blocks_Youtube_Extension::instance(), 'replace_iframe_url' ] );
		remove_filter( 'deprecated_function_trigger_error', [ $this, 'disable_deprecated_warnings' ] );

		// Skip if it is already being handled by Sensei LMS.
		if (
			$check_deprecated_replace_iframe_url || (
				class_exists( '\Sensei_Course_Video_Settings' )
				&& has_filter( 'embed_oembed_html', [ \Sensei_Course_Video_Settings::instance(), 'enable_youtube_api' ] )
			)
		) {
			return $html;
		}

		$host = wp_parse_url( $url, PHP_URL_HOST );

		// Skip if it's not an youtube embed.
		if ( strpos( $host, 'youtu.be' ) === false && strpos( $host, 'youtube.com' ) === false ) {
			return $html;
		}

		return preg_replace_callback(
			'/src="(.*?)"/',
			function ( $matches ) {
				$modified_url = add_query_arg(
					[
						'enablejsapi' => 1,
						'origin'      => esc_url( home_url() ),
					],
					$matches[1]
				);

				return 'src="' . $modified_url . '"';
			},
			$html
		);
	}

	/**
	 * Method to disable deprecated warnings through a filter.
	 *
	 * @access private
	 *
	 * @return false
	 */
	public function disable_deprecated_warnings() {
		return false;
	}
}
