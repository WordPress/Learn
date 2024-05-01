<?php
/**
 * Tutor AI block.
 *
 * @package sensei-pro
 */

namespace Sensei_Pro_Interactive_Blocks;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_filter(
	'sensei_default_feature_flag_settings',
	function ( $settings ) {
		$settings['tutor_ai'] = true;
		return $settings;
	}
);

/**
 * Tutor AI block class.
 */
class Tutor_AI_Block {
	/**
	 * Tutor_AI_Block constructor.
	 */
	public function __construct() {
		if ( function_exists( 'Sensei' ) && ! Sensei()->feature_flags->is_enabled( 'tutor_ai' ) ) {
			return;
		}

		add_action( 'init', [ $this, 'init' ] );
		add_filter( 'render_block_core/avatar', [ $this, 'maybe_change_with_user_avatar' ], 10, 2 );
	}

	/**
	 * Initialize block.
	 */
	public function init() {
		register_block_type_from_metadata(
			SENSEI_IB_PLUGIN_DIR_PATH . 'assets/tutor-ai/',
			[
				'editor_script' => 'interactive-blocks-tutor-ai',
				'view_script'   => 'sensei-interactive-blocks-frontend-script',
				'style'         => 'sensei-interactive-blocks-styles',
			]
		);
	}

	/**
	 * Change the src of the avatar inside Tutor AI block to show the image of the chatting user instead of
	 * the post author's.
	 *
	 * @param string $block_content Block content.
	 * @param array  $block Block.
	 *
	 * @return string
	 */
	public function maybe_change_with_user_avatar( $block_content, $block ) {
		if ( 'sensei-pro-tutor-ai__user-avatar' === ( $block['attrs']['className'] ?? '' ) ) {
			// Change image attribute's src value to logged in user's gravatar link.
			$dom = new \DomDocument();
			$dom->loadHTML( $block_content );

			$parent_node = $dom->getElementsByTagName( 'div' )->length > 0 ? $dom->getElementsByTagName( 'div' )[0] : '';

			if ( ! $parent_node || ! $parent_node->hasAttributes() ) {
				return $block_content;
			}

			$images = $dom->getElementsByTagName( 'img' );
			$image  = $images->length ? $images[0] : '';

			if ( ! $image ) {
				return $block_content;
			}

			$src = get_avatar_url( get_current_user_id(), [ 'size' => $block['attrs']['size'] ] );
			$image->setAttribute( 'src', $src );
			$image->setAttribute( 'srcset', $src );
			$block_content = $dom->saveHTML( $parent_node );
		}
		return $block_content;
	}
}
