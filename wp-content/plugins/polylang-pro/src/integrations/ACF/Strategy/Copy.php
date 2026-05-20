<?php
/**
 * @package  Polylang-Pro
 */

namespace WP_Syntex\Polylang_Pro\Integrations\ACF\Strategy;

use PLL_Language;
use WP_Syntex\Polylang_Pro\Integrations\ACF\Entity\Abstract_Object;

/**
 * This class is part of the ACF compatibility.
 * Copy strategy.
 * Copies the custom field value from the source object to the target object.
 * Honors translations settings.
 *
 * @since 3.7
 */
class Copy extends Abstract_Strategy {
	/**
	 * Executes the strategy on a given field.
	 *
	 * @since 3.7
	 *
	 * @param Abstract_Object $object ACF object.
	 * @param mixed           $value  Custom field value of the source object.
	 * @param array           $field  Custom field definition.
	 * @param array           $args   {
	 *      Array of arguments.
	 *
	 *      @type PLL_Language $target_language Optional. The language object of the target object.
	 *      @type PLL_Language $source_language Optional. The language object of the source object.
	 * }
	 * @return mixed Custom field value of the target object.
	 */
	protected function apply( Abstract_Object $object, $value, array $field, array $args = array() ) {
		if ( ! isset( $args['target_language'] ) || ! $args['target_language'] instanceof PLL_Language ) {
			return $value;
		}

		switch ( $field['type'] ) {
			case 'image':
			case 'file':
				if ( PLL()->options['media_support'] && is_numeric( $value ) ) {
					$value = $this->translate_media( (int) $value, $args['target_language'] );
				}
				break;
			case 'gallery':
				if ( PLL()->options['media_support'] && is_array( $value ) ) {
					$value = $this->translate_gallery( $value, $args['target_language'] );
				}
				break;
			case 'post_object':
			case 'relationship':
				$value = $this->translate_post( $value, $args['target_language'] );
				break;
			case 'taxonomy':
				if ( pll_is_translated_taxonomy( $field['taxonomy'] ) ) {
					$value = $this->translate_term( $value, $args['target_language'] );
				}
				break;
			case 'page_link':
				if ( is_array( $value ) || is_int( $value ) || is_string( $value ) ) {
					$value = $this->translate_page_link( $value, $args['target_language'] );
				}
				break;
			case 'wysiwyg':
				if ( is_string( $value ) ) {
					$value = PLL()->sync_content->translate_content(
						$value,
						null,
						$args['target_language']
					);

				}
				break;
		}

		return $this->maybe_translate_field_default_value( $value, $field, $args );
	}

	/**
	 * Recursively checks if a field can be copied.
	 *
	 * @since 3.7
	 *
	 * @param array $field Custom field definition.
	 * @return bool
	 */
	protected function can_execute_recursive( array $field ): bool {
		if ( isset( $field['translations'] ) && 'ignore' !== $field['translations'] ) {
			return true;
		}

		return parent::can_execute_recursive( $field );
	}

	/**
	 * Translates a media field.
	 *
	 * @since 3.7
	 *
	 * @param int          $value Custom field value of the source object.
	 * @param PLL_Language $lang  Language object of the target object.
	 * @return int Custom field value of the target object.
	 */
	protected function translate_media( int $value, PLL_Language $lang ): int {
		$tr_id = pll_get_post( $value, $lang );

		if ( $tr_id ) {
			return $tr_id;
		}

		return PLL()->model->post->create_media_translation( $value, $lang );
	}

	/**
	 * Translates media ids in a gallery field.
	 *
	 * @since 3.7
	 *
	 * @param int[]        $values Custom field value of the source object.
	 * @param PLL_Language $lang   Language object of the target object.
	 * @return string[] Custom field value of the target object.
	 *
	 * @phpstan-param array<int|numeric-string> $values
	 * @phpstan-return list<numeric-string>
	 */
	protected function translate_gallery( array $values, PLL_Language $lang ): array {
		$return = array();

		foreach ( $values as $value ) {
			$return[] = $this->translate_media( (int) $value, $lang );
		}

		/** @phpstan-var list<numeric-string> */
		return array_map( 'strval', $return ); // See `acf_field_gallery::update_value()`.
	}

	/**
	 * Translates post IDs relationship and post object fields.
	 *
	 * @since 3.7
	 *
	 * @param mixed        $value Custom field value of the source object.
	 * @param PLL_Language $lang  Language object of the target object.
	 * @return int|string[] Custom field value of the target object.
	 *
	 * @phpstan-return (
	 *     $value is array ? list<numeric-string> : int
	 * )
	 */
	protected function translate_post( $value, PLL_Language $lang ) {
		if ( is_numeric( $value ) ) {
			$value     = (int) $value;
			$post_type = get_post_type( $value );

			if ( ! $post_type || ! pll_is_translated_post_type( $post_type ) ) {
				// Same ID for not-translated languages.
				return $value;
			}

			return (int) pll_get_post( $value, $lang );
		}

		if ( is_array( $value ) ) {
			$return = array();
			foreach ( $value as $id ) {
				$return[] = (int) $this->translate_post( $id, $lang );
			}

			/** @phpstan-var list<numeric-string> */
			return array_map( 'strval', $return ); // See the method update_value() for these fields.
		}

		// Something went wrong.
		return 0;
	}

	/**
	 * Translates term ids in a taxonomy field.
	 *
	 * @since 3.7
	 *
	 * @param mixed        $value Custom field value of the source object.
	 * @param PLL_Language $lang  Language object of the target object.
	 * @return int|int[] Custom field value of the target object.
	 *
	 * @phpstan-return (
	 *     $value is array ? list<int> : int
	 * )
	 */
	protected function translate_term( $value, PLL_Language $lang ) {
		if ( is_numeric( $value ) ) {
			return (int) pll_get_term( (int) $value, $lang );
		}

		if ( is_array( $value ) ) {
			$return = array();
			foreach ( $value as $id ) {
				$return[] = (int) $this->translate_term( $id, $lang );
			}

			return $return;
		}

		// Something went wrong.
		return 0;
	}

	/**
	 * Translates a page link field.
	 *
	 * @since 3.7
	 *
	 * @param int|string|(int|string)[] $value Custom field value of the source object.
	 * @param PLL_Language              $lang  Language slug of the target object.
	 * @return int|string|string[] Custom field value of the target object.
	 */
	protected function translate_page_link( $value, PLL_Language $lang ) {
		if ( is_numeric( $value ) ) {
			return (int) pll_get_post( (int) $value, $lang );
		}

		if ( is_array( $value ) ) {
			// Multiple choices.
			$return = array();
			foreach ( $value as $p ) {
				if ( is_numeric( $p ) ) {
					$return[] = (int) pll_get_post( (int) $p, $lang );
				} elseif ( is_string( $p ) ) {
					$return[] = $this->translate_cpt_archive_link( $p, $lang ); // Archive.
				}
			}
			return array_map( 'strval', $return ); // See `acf_field_page_link::update_value()`.
		}

		return $this->translate_cpt_archive_link( $value, $lang ); // Archive.
	}

	/**
	 * Translates a CPT archive link in a page link field.
	 *
	 * @since 2.3.6
	 * @since 3.7 `$lang` is a `PLL_Language` instead of a string.
	 *
	 * @param string       $link CPT archive link.
	 * @param PLL_Language $lang Language object of the target object.
	 * @return string Modified link.
	 */
	protected function translate_cpt_archive_link( string $link, PLL_Language $lang ): string {
		/*
		 * ACF doesn't use correctly `home_url()` function. It makes this URL not end with a trailing slash.
		 * It makes our `PLL_Links_Model::switch_language_in_link()` not work correctly in this case.
		 * @see https://github.com/AdvancedCustomFields/acf/blob/6.3.8/includes/fields/class-acf-field-page_link.php#L174
		 */
		if ( home_url() === $link ) {
			$link = home_url( '/' );
		}

		$show_on_front  = get_option( 'show_on_front' );
		$page_for_posts = get_option( 'page_for_posts' );

		if ( 'page' === $show_on_front && is_numeric( $page_for_posts ) ) {
			// Gets `page_for_posts` URL of the target language.
			$post_archive_link = get_permalink( $lang->page_for_posts );
			return ! empty( $post_archive_link ) ? $post_archive_link : $link;
		}

		$link = PLL()->links_model->switch_language_in_link( $link, $lang );

		if ( ! isset( PLL()->translate_slugs ) ) {
			return $link;
		}

		foreach ( PLL()->translate_slugs->slugs_model->get_translatable_slugs() as $type => $data ) {
			// Unfortunately ACF does not pass the post type, so let's try with all post type archives.
			if ( 0 === strpos( $type, 'archive_' ) ) {
				$link = PLL()->translate_slugs->slugs_model->switch_translated_slug( $link, $lang, $type );
			}
		}

		return $link;
	}
}
