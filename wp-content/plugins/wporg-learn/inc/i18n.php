<?php

namespace WPOrg_Learn\I18n;

use WP_Term;

defined( 'WPINC' ) || die();

/**
 * Actions and filters.
 */
add_filter( 'get_term', __NAMESPACE__ . '\translate_term', 10, 2 );
add_filter( 'get_terms', __NAMESPACE__ . '\translate_terms', 10, 2 );

/**
 * Translate a term's name and description in certain contexts.
 *
 * @param WP_Term|string $term
 *
 * @return WP_Term
 */
function translate_term( $term, $taxonomy_slug ) {
	if ( 'en_US' === get_locale() ) {
		return $term;
	}

	// Terms shouldn't be translated in the UI for editing them.
	if ( is_admin() ) {
		return $term;
	}

	$taxonomy = get_taxonomy( $taxonomy_slug );
	$valid_post_types = array(
		'lesson-plan',
		'wporg_workshop',
		'course',
		'lesson',
	);
	$supported_types = $taxonomy->object_type;

	if ( count( array_intersect( $supported_types, $valid_post_types ) ) < 1 ) {
		return $term;
	}

	if ( $term instanceof WP_Term ) {
		$term->name = esc_html( translate_with_gettext_context(
			html_entity_decode( $term->name ), // phpcs:ignore WordPress.WP.I18n.NonSingularStringLiteralText
			"{$taxonomy->label} term name", // phpcs:ignore WordPress.WP.I18n.InterpolatedVariableContext
			'wporg-learn'
		) );

		if ( $term->description ) {
			$term->description = wp_kses_post( translate_with_gettext_context(
				html_entity_decode( $term->description ), // phpcs:ignore WordPress.WP.I18n.NonSingularStringLiteralText
				"{$taxonomy->label} term description", // phpcs:ignore WordPress.WP.I18n.InterpolatedVariableContext
				'wporg-learn'
			) );
		}
	} elseif ( is_string( $term ) ) {
		$term = esc_html( translate_with_gettext_context(
			html_entity_decode( $term ), // phpcs:ignore WordPress.WP.I18n.NonSingularStringLiteralText
			"{$taxonomy->label} term name", // phpcs:ignore WordPress.WP.I18n.InterpolatedVariableContext
			'wporg-learn'
		) );
	}

	return $term;
}

/**
 * Translate a group of terms.
 *
 * @param WP_Term[] $terms
 *
 * @return WP_Term[]
 */
function translate_terms( array $terms, ?array $taxonomies ) {
	if ( 'en_US' === get_locale() ) {
		return $terms;
	}

	// Terms shouldn't be translated in the UI for editing them.
	if ( is_admin() ) {
		return $terms;
	}

	$first_term = reset( $terms );
	if ( ! $first_term instanceof WP_Term && ! is_string( $first_term ) ) {
		return $terms;
	}

	// If the terms query has multiple (or no) taxonomies, we don't know which one a term will belong to.
	if ( ! $taxonomies || count( $taxonomies ) > 1 ) {
		return $terms;
	}

	$taxonomy = reset( $taxonomies );

	foreach ( $terms as $index => $term ) {
		$terms[ $index ] = translate_term( $term, $taxonomy );
	}

	return $terms;
}
