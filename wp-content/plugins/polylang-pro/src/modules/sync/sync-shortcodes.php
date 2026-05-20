<?php
/**
 * @package Polylang-Pro
 */

/**
 * Translates shortcodes to keep them synchronized across pieces of content.
 *
 * @since 3.7
 */
class PLL_Sync_Shortcodes {
	/**
	 * Translates object IDs.
	 *
	 * @var PLL_Sync_Ids
	 */
	public $ids;

	/**
	 * Stores the plugin options.
	 *
	 * @var \WP_Syntex\Polylang\Options\Options
	 */
	private $options;

	/**
	 * Language of the target post.
	 *
	 * @var PLL_Language
	 */
	private $target_language;

	/**
	 * Main target post object.
	 *
	 * @var WP_Post|null
	 */
	private $target_post;

	/**
	 * Constructor.
	 *
	 * @since 3.7
	 *
	 * @param PLL_Sync_Ids $ids Ids translator.
	 */
	public function __construct( PLL_Sync_Ids $ids ) {
		$this->ids     = $ids;
		$this->options = $ids->model->options;
	}

	/**
	 * Translates shortcodes from a given content.
	 *
	 * @since 3.7
	 *
	 * @global array $shortcode_tags
	 *
	 * @param string       $content         Content to translate.
	 * @param PLL_Language $target_language Target language.
	 * @param WP_Post|null $target_post     Main target post object.
	 * @return string Content with its shortcodes translated.
	 */
	public function translate( string $content, PLL_Language $target_language, ?WP_Post $target_post ): string {
		global $shortcode_tags;

		if ( ! $this->options['media_support'] ) {
			return $content;
		}

		$this->target_language = $target_language;
		$this->target_post     = $target_post;

		// Hack shortcodes.
		$backup = $shortcode_tags;
		$shortcode_tags = array(); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited

		// Add our own shorcode actions.
		add_shortcode( 'gallery', array( $this, 'ids_list_shortcode' ) );
		add_shortcode( 'playlist', array( $this, 'ids_list_shortcode' ) );
		add_shortcode( 'caption', array( $this, 'caption_shortcode' ) );
		add_shortcode( 'wp_caption', array( $this, 'caption_shortcode' ) );

		$content = do_shortcode( $content ); // Translate shortcodes.

		// Get the shorcodes back.
		$shortcode_tags = $backup; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited

		return $content;
	}

	/**
	 * Translates the 'gallery' and 'playlist' shortcodes
	 *
	 * @since 1.9
	 * @since 3.7 Moved from `PLL_Sync_Content` to `PLL_Sync_Shortcodes`.
	 *
	 * @param string[]    $attr Shortcode attributes.
	 * @param string|null $null Shortcode content, not used.
	 * @param string      $tag  Shortcode tag (either 'gallery' or 'playlist').
	 * @return string Translated shortcode.
	 */
	public function ids_list_shortcode( $attr, $null, $tag ) {
		$out = array();

		foreach ( $attr as $k => $v ) {
			if ( 'ids' === $k ) {
				$ids    = explode( ',', $v );
				$tr_ids = array();
				foreach ( $ids as $id ) {
					$tr_ids[] = $this->ids->translate( (int) $id, 'attachment', $this->target_language, $this->target_post );
				}
				$v = implode( ',', $tr_ids );
			}
			$out[] = $k . '="' . $v . '"';
		}

		return '[' . $tag . ' ' . implode( ' ', $out ) . ']';
	}

	/**
	 * Translates the caption shortcode
	 * Compatible only with the new style introduced in WP 3.4
	 *
	 * @since 1.9
	 * @since 3.7 Moved from `PLL_Sync_Content` to `PLL_Sync_Shortcodes`.
	 *
	 * @param string[]    $attr    Shortcode attribute.
	 * @param string|null $content Shortcode content.
	 * @param string      $tag     Shortcode tag (either 'caption' or 'wp-caption').
	 * @return string Translated shortcode.
	 */
	public function caption_shortcode( $attr, $content, $tag ) {
		// Translate the caption id.
		$out = array();

		foreach ( $attr as $k => $v ) {
			if ( 'id' === $k ) {
				$idarr    = explode( '_', $v );
				$id       = $idarr[1]; // Remember this.
				$tr_id    = $this->ids->translate( (int) $id, 'attachment', $this->target_language, $this->target_post );
				$idarr[1] = $tr_id;
				$v        = implode( '_', $idarr );
			}
			$out[] = $k . '="' . $v . '"';
		}

		// Translate the caption content.
		if ( ! empty( $id ) && ! empty( $tr_id ) ) {
			$p    = get_post( (int) $id );
			$tr_p = get_post( (int) $tr_id );
			if ( $p && $tr_p ) {
				$content = str_replace( $p->post_excerpt, $tr_p->post_excerpt, (string) $content );
			}
		}

		return '[' . $tag . ' ' . implode( ' ', $out ) . ']' . $content . '[/' . $tag . ']';
	}
}
