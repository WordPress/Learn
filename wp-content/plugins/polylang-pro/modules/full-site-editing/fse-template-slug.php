<?php
/**
 * @package Polylang-Pro
 */

defined( 'ABSPATH' ) || exit;

/**
 * A class to work with template slugs (and their language suffix).
 *
 * @since 3.2
 */
class PLL_FSE_Template_Slug {

	/**
	 * Separator used for the language suffix.
	 *
	 * @var string
	 */
	const SEPARATOR = '___';

	/**
	 * Pattern that matches a language slug.
	 *
	 * @var string
	 */
	private $lang_pattern = '[a-z_-]+';

	/**
	 * The raw template slug.
	 *
	 * @var string
	 */
	private $slug = '';

	/**
	 * The template slug after regex matching.
	 *
	 * @var string[] {
	 *     @type string $slug The template slug without language suffix.
	 *     @type string $lang The language slug.
	 * }
	 */
	private $slug_arr = array();

	/**
	 * Constructor.
	 *
	 * @since 3.2
	 *
	 * @param string   $slug       A template slug.
	 * @param string[] $lang_slugs List of language slugs to use.
	 * @return void
	 */
	public function __construct( $slug, array $lang_slugs = array() ) {
		if ( ! is_string( $slug ) ) {
			return;
		}

		$this->slug = $slug;
		$lang_slugs = array_filter(
			$lang_slugs,
			function ( $slug ) {
				return is_string( $slug ) && preg_match( "@^{$this->lang_pattern}$@", $slug );
			}
		);

		if ( ! empty( $lang_slugs ) ) {
			$this->lang_pattern = implode( '|', $lang_slugs );
		}
	}

	/**
	 * Returns the template slug.
	 *
	 * @since 3.2
	 *
	 * @return string
	 */
	public function get() {
		return $this->slug;
	}

	/**
	 * Get the template slug without its language suffix.
	 *
	 * @since 3.2
	 *
	 * @return string
	 */
	public function get_template_slug() {
		$this->match();

		return $this->slug_arr['slug'];
	}

	/**
	 * Get the language slug from the template slug.
	 *
	 * @since 3.2
	 *
	 * @return string
	 */
	public function get_language() {
		$this->match();

		return $this->slug_arr['lang'];
	}

	/**
	 * Tells if the template slug has a language suffix already.
	 *
	 * @since 3.2
	 *
	 * @return bool
	 */
	public function has_language() {
		$this->match();

		return ! empty( $this->slug_arr['lang'] );
	}

	/**
	 * Adds a language suffix to the template slug.
	 *
	 * @since 3.2
	 *
	 * @param string $language_slug A language slug.
	 * @return string The template slug with its new language suffix.
	 */
	public function update_language( $language_slug ) {
		if ( ! is_string( $language_slug ) || '' === $language_slug ) {
			return $this->slug;
		}

		$this->match();

		$this->slug_arr['lang'] = $language_slug;
		$this->slug             = $this->slug_arr['slug'] . self::SEPARATOR . $this->slug_arr['lang'];

		return $this->slug;
	}

	/**
	 * Removes the language suffix from a shared slug.
	 *
	 * @since 3.2
	 *
	 * @return string The template slug without the language suffix.
	 */
	public function remove_language() {
		$this->match();

		$this->slug_arr['lang'] = '';
		$this->slug             = $this->slug_arr['slug'];

		return $this->slug;
	}

	/**
	 * Performs a regex match to separate the template slug and the language suffix.
	 * This will fill in the property `$this->slug_arr`.
	 *
	 * @since 3.2
	 *
	 * @return void
	 */
	private function match() {
		if ( ! empty( $this->slug_arr ) ) {
			// Already ran.
			return;
		}

		$pattern        = sprintf( '@^(?<slug>[a-zA-Z0-9_-]+)(?:%s(?<lang>%s))?$@U', self::SEPARATOR, $this->lang_pattern );
		$result         = preg_match( $pattern, $this->slug, $matches );
		$this->slug_arr = array(
			'slug' => '',
			'lang' => '',
		);

		if ( empty( $result ) ) {
			return;
		}

		$this->slug_arr['slug'] = $matches['slug'];
		$this->slug_arr['lang'] = isset( $matches['lang'] ) ? $matches['lang'] : '';
	}
}
