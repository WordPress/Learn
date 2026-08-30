<?php
/**
 * Plugin Name:       Learn Language Bridge
 * Description:       Keeps the site interface locale (Locale Detection / global-header language switcher) and Polylang's content language in sync, in both directions. The switcher changes the interface AND jumps to the matching content; Polylang content drives the interface locale.
 * Version:           1.0.0
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * Author:            Learn WordPress
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       wporg-learn
 *
 * Why this exists:
 *  - "Locale Detection" owns the interface locale via ?locale= / the `wporg_locale`
 *    cookie (it short-circuits `pre_determine_locale`).
 *  - Polylang owns which content is shown via the language directory (e.g. /pl/).
 *  Left alone the two fight over WordPress's locale: the interface won't follow the
 *  content, and the switcher won't change the content. This bridge makes them agree
 *  on a single language per request.
 *
 * @package LearnWP\LanguageBridge
 */

namespace LearnWP\LanguageBridge;

use WP_Block_Supports;

/**
 * Build slug <-> locale maps from Polylang's registered languages.
 *
 * Returns [ 's2l' => [ slug => locale ], 'l2s' => [ locale => slug ] ].
 * Returns empty maps (uncached) until Polylang is loaded, so callers can bail
 * safely on very early requests.
 *
 * @return array{s2l: array<string,string>, l2s: array<string,string>}
 */
function lang_maps() {
	static $maps = null;

	if ( null !== $maps ) {
		return $maps;
	}

	$s2l = array();
	if ( function_exists( 'PLL' ) && isset( PLL()->model ) ) {
		foreach ( PLL()->model->get_languages_list() as $language ) {
			$s2l[ $language->slug ] = $language->locale;
		}
	}

	if ( empty( $s2l ) ) {
		// Don't cache an empty result: Polylang may not be loaded yet.
		return array(
			's2l' => array(),
			'l2s' => array(),
		);
	}

	$maps = array(
		's2l' => $s2l,
		'l2s' => array_flip( $s2l ),
	);
	return $maps;
}

/**
 * Determine the Polylang language slug for the current front-end request.
 *
 * Prefers Polylang's resolved current language; falls back to parsing the URL
 * for early calls that happen before Polylang sets the current language (e.g.
 * the first `pre_determine_locale`/`locale` calls while text domains load).
 *
 * @return string Language slug, or '' if it can't be determined.
 */
function current_slug() {
	if ( function_exists( 'pll_current_language' ) ) {
		$slug = pll_current_language( 'slug' );
		if ( $slug ) {
			return $slug;
		}
	}

	return slug_from_url();
}

/**
 * Guess the language slug from the request path (the /{slug}/ directory prefix).
 *
 * Uses get_option( 'home' ) rather than home_url() to avoid re-entering
 * Polylang's URL filters (which can call get_locale()).
 *
 * @return string Language slug, the default language slug for the un-prefixed
 *                root, or '' if unknown.
 */
function slug_from_url() {
	$maps = lang_maps();
	if ( empty( $maps['s2l'] ) ) {
		return '';
	}

	$home_path   = trim( (string) wp_parse_url( get_option( 'home' ), PHP_URL_PATH ), '/' );
	$request_uri = isset( $_SERVER['REQUEST_URI'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '/';
	$req_path    = trim( (string) wp_parse_url( $request_uri, PHP_URL_PATH ), '/' );

	if ( '' !== $home_path && 0 === strpos( $req_path, $home_path ) ) {
		$req_path = trim( substr( $req_path, strlen( $home_path ) ), '/' );
	}

	$first = strtok( $req_path, '/' );
	if ( $first && isset( $maps['s2l'][ $first ] ) ) {
		return $first;
	}

	// No language prefix -> the (hidden) default language.
	return function_exists( 'pll_default_language' ) ? (string) pll_default_language() : '';
}

/**
 * PART A - Interface follows content.
 *
 * Force WordPress's locale to the current content language's locale on the front
 * end. Priority 99 puts it last: Locale Detection and Polylang's front-end filter
 * both hook `locale` at 10 (and Polylang's sanitization filters at 20), which is
 * why they are decided by load order rather than by intent. Admin/AJAX are left
 * untouched so Polylang keeps managing them.
 *
 * Used for both `locale` and `pre_determine_locale`. For `pre_determine_locale`
 * the incoming value is null; returning a non-empty locale short-circuits
 * WordPress's locale detection with the content locale.
 *
 * @param string|null $locale Incoming locale (null for pre_determine_locale).
 * @return string|null Content locale on the front end, otherwise unchanged.
 */
function filter_locale( $locale ) {
	static $in_progress = false;

	if ( is_admin() || $in_progress ) {
		return $locale;
	}

	$in_progress = true;
	$slug        = current_slug();
	$maps        = lang_maps();
	$in_progress = false;

	if ( $slug && isset( $maps['s2l'][ $slug ] ) ) {
		return $maps['s2l'][ $slug ];
	}

	return $locale;
}
add_filter( 'locale', __NAMESPACE__ . '\filter_locale', 99 );
add_filter( 'pre_determine_locale', __NAMESPACE__ . '\filter_locale', 99 );

/**
 * PART B - Content follows the switcher.
 *
 * The global-header language switcher (via Locale Detection) navigates with a
 * `?locale=xx_XX` query arg. When that arg is present, send the visitor to the
 * same content in the matching Polylang language, so switching the interface
 * language also switches the content.
 *
 * Triggered only by the explicit `?locale=` arg (not the persisted cookie) so
 * that directly visiting a language URL such as /pl/ is never fought.
 *
 * @return void
 */
function redirect_to_content_language() {
	if ( is_admin() || wp_doing_ajax() || ( defined( 'REST_REQUEST' ) && REST_REQUEST ) ) {
		return;
	}
	$method = isset( $_SERVER['REQUEST_METHOD'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_METHOD'] ) ) : 'GET';
	if ( 'GET' !== $method ) {
		return;
	}
	if ( empty( $_GET['locale'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		return;
	}

	$maps = lang_maps();
	if ( empty( $maps['l2s'] ) ) {
		return;
	}

	$requested_locale = preg_replace( '/[^a-zA-Z_]/', '', (string) wp_unslash( $_GET['locale'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
	$target_slug      = isset( $maps['l2s'][ $requested_locale ] ) ? $maps['l2s'][ $requested_locale ] : '';
	if ( ! $target_slug ) {
		return;
	}

	$current = current_slug();

	if ( $current === $target_slug ) {
		// Already on the right language: just drop the now-redundant query arg.
		wp_safe_redirect( remove_query_arg( 'locale' ), 302 );
		exit;
	}

	$url = translated_url_for_current( $target_slug );
	if ( $url ) {
		// Drop our own switch arg so the destination URL stays clean.
		wp_safe_redirect( remove_query_arg( 'locale', $url ), 302 );
		exit;
	}
}
add_action( 'template_redirect', __NAMESPACE__ . '\redirect_to_content_language', 1 );

/**
 * Resolve the URL of the current view in the target language.
 *
 * Uses Polylang's own translation-URL resolver, which handles singular content,
 * pages, taxonomies, post type archives, search and the home page. Falls back
 * to the target language's home when the current view has no translation.
 *
 * @param string $slug Target language slug.
 * @return string Destination URL, or '' when there is nowhere sensible to go.
 */
function translated_url_for_current( $slug ) {
	if ( function_exists( 'PLL' ) && isset( PLL()->links ) && isset( PLL()->model ) ) {
		$language = PLL()->model->get_language( $slug );
		if ( $language ) {
			$url = PLL()->links->get_translation_url( $language );
			if ( $url ) {
				return $url;
			}
		}
	}

	// No matching translation for this view: fall back to the language home.
	return function_exists( 'pll_home_url' ) ? (string) pll_home_url( $slug ) : '';
}
/**
 * PART C - Keep the admin bar free of language controls.
 *
 * Polylang adds a "content language filter" menu to the admin bar (the node
 * sitting immediately after "New"). The language switcher lives next to the
 * navigation instead (Part D), so Polylang's menu is suppressed here. Returning
 * an empty item list makes Polylang skip the menu entirely rather than render an
 * empty one.
 *
 * @param array $items Admin languages filter items.
 * @return array Always empty, so the menu is not rendered.
 */
function hide_polylang_admin_bar_menu( $items ) {
	return array();
}
add_filter( 'pll_admin_languages_filter', __NAMESPACE__ . '\hide_polylang_admin_bar_menu', 99 );

/**
 * PART D - Language switcher next to the navigation.
 *
 * Logged-out visitors never see the admin bar, so with no language control there
 * (Part C) they would have no way to switch language. This builds a submenu of
 * Polylang's languages - labelled with the current one - which the filter below
 * appends as the last item of the navigation block, next to the menu items.
 *
 * It is made of core's own `core/navigation-submenu` / `core/navigation-link`
 * blocks, so it inherits the theme's navigation styling, the mobile overlay and
 * core's submenu open/close behaviour without any extra CSS or JS. The items
 * point at the current URL with `?locale=xx_XX`, exactly what the admin bar
 * switcher used to do: Locale Detection stores the choice in the `wporg_locale`
 * cookie, and Part B moves the visitor to that language's content.
 *
 * @return string Block markup, or '' when there is nothing to switch between.
 */
function language_switcher_markup() {
	if ( ! function_exists( 'PLL' ) || ! isset( PLL()->model ) ) {
		return '';
	}

	$languages = PLL()->model->get_languages_list();
	if ( count( $languages ) < 2 ) {
		return '';
	}

	$current = current_slug();
	$label   = '';
	$items   = '';

	foreach ( $languages as $language ) {
		// The current language is the submenu's own label, not one of its items.
		if ( $language->slug === $current ) {
			$label = $language->name;
			continue;
		}

		$items .= sprintf(
			'<!-- wp:navigation-link %s /-->',
			wp_json_encode(
				array(
					'label' => $language->name,
					'url'   => esc_url_raw( add_query_arg( 'locale', $language->locale ) ),
					'kind'  => 'custom',
				)
			)
		);
	}

	if ( '' === $label || '' === $items ) {
		return '';
	}

	return sprintf(
		'<!-- wp:navigation-submenu %s -->%s<!-- /wp:navigation-submenu -->',
		wp_json_encode(
			array(
				'label'     => $label,
				'url'       => '#',
				'kind'      => 'custom',
				'className' => 'learn-language-switcher',
			)
		),
		$items
	);
}

/**
 * Append the language switcher to the navigation block's items.
 *
 * Runs at priority 20, after the wporg navigation block has built its items from
 * the dynamic menu, so the switcher ends up last. Parsed block arrays are added
 * to the list rather than WP_Block objects, so they get instantiated with the
 * navigation's own context (colors, submenu icons, nesting level).
 *
 * Only navigations with a `menuSlug` are touched - that is the theme's local
 * navigation bar (`<!-- wp:navigation {"menuSlug":"learn"} -->`), both its normal
 * and its always-collapsed copy, so the switcher is there at any viewport. The
 * global header and footer render their own navigations, which are left alone.
 *
 * @param \WP_Block_List $inner_blocks The navigation's inner blocks.
 * @return \WP_Block_List Inner blocks, with the switcher appended.
 */
function append_language_switcher_to_navigation( $inner_blocks ) {
	if ( is_user_logged_in() || is_admin() || wp_is_json_request() ) {
		return $inner_blocks;
	}

	// The navigation currently rendering, to tell the local nav bar from the
	// global header's own navigations.
	$block = WP_Block_Supports::$block_to_render;
	if ( empty( $block['attrs']['menuSlug'] ) ) {
		return $inner_blocks;
	}

	$markup = language_switcher_markup();
	if ( ! $markup ) {
		return $inner_blocks;
	}

	$blocks = parse_blocks( $markup );
	if ( function_exists( 'block_core_navigation_filter_out_empty_blocks' ) ) {
		$blocks = block_core_navigation_filter_out_empty_blocks( $blocks );
	}
	if ( empty( $blocks ) ) {
		return $inner_blocks;
	}

	foreach ( $blocks as $switcher_block ) {
		$inner_blocks[] = $switcher_block;
	}

	return $inner_blocks;
}
add_filter( 'block_core_navigation_render_inner_blocks', __NAMESPACE__ . '\append_language_switcher_to_navigation', 20 );

/**
 * Mark the switcher with a translation icon.
 *
 * A submenu's label is a block attribute and so can only be text; the icon is put
 * into the rendered markup instead, in front of the label. It is Gutenberg's own
 * `language` icon - the glyph the admin bar switcher used to show through
 * `dashicons-translation` - so it reads as "translations" rather than standing in
 * for any one language.
 *
 * @param string $block_content The rendered submenu.
 * @param array  $block         The parsed block.
 * @return string The submenu, with an icon in front of its label.
 */
function add_language_switcher_icon( $block_content, $block ) {
	if ( empty( $block['attrs']['className'] ) || false === strpos( $block['attrs']['className'], 'learn-language-switcher' ) ) {
		return $block_content;
	}

	// The first label is the submenu's own; the languages below it stay plain.
	$label    = '<span class="wp-block-navigation-item__label">';
	$position = strpos( $block_content, $label );
	if ( false === $position ) {
		return $block_content;
	}

	$icon = '<svg class="learn-language-switcher__icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="20" height="20" fill="currentColor" aria-hidden="true" focusable="false"><path d="M17.5 10h-1.7l-3.7 10.5h1.7l.9-2.6h3.9l.9 2.6h1.7L17.5 10zm-2.2 6.3 1.4-4 1.4 4h-2.8zm-4.8-3.8c1.6-1.8 2.9-3.6 3.7-5.7H16V5.2h-5.8V3H8.8v2.2H3v1.5h9.6c-.7 1.6-1.8 3.1-3.1 4.6C8.6 10.2 7.8 9 7.2 8H5.6c.6 1.4 1.7 2.9 2.9 4.4l-2.4 2.4c-.3.4-.7.8-1.1 1.2l1 1 1.2-1.2c.8-.8 1.6-1.5 2.3-2.3.8.9 1.7 1.7 2.5 2.5l.6-1.5c-.7-.6-1.4-1.3-2.1-2z" /></svg>';

	return substr_replace( $block_content, $icon . $label, $position, strlen( $label ) );
}
add_filter( 'render_block_core/navigation-submenu', __NAMESPACE__ . '\add_language_switcher_icon', 10, 2 );

/**
 * Line the switcher's icon up with its label.
 *
 * A handful of declarations, so they are printed inline instead of shipped as a
 * stylesheet. The size is in `em` because the icon shows both in the nav bar and
 * in the (larger) mobile overlay; `vertical-align` covers the inline case and
 * `flex-shrink` the flex one, since the navigation renders its item content
 * either way depending on its attributes.
 *
 * @return void
 */
function print_language_switcher_styles() {
	if ( is_user_logged_in() ) {
		return;
	}

	wp_register_style( 'learn-language-switcher', false, array(), '1.0.0' );
	wp_enqueue_style( 'learn-language-switcher' );
	wp_add_inline_style(
		'learn-language-switcher',
		'.learn-language-switcher__icon { width: 1.2em; height: 1.2em; flex-shrink: 0; margin-inline-end: 4px; vertical-align: text-bottom; }'
	);
}
add_action( 'wp_enqueue_scripts', __NAMESPACE__ . '\print_language_switcher_styles' );
