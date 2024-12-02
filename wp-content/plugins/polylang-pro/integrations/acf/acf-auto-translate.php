<?php
/**
 * @package Polylang-Pro
 */

/**
 * This class is part of the ACF compatibility.
 * Manages the automatic translation of posts and terms in custom fields.
 *
 * @since 2.7
 */
class PLL_ACF_Auto_Translate {
	/**
	 * ACF fields storage, used to remember which fields are currently handled.
	 *
	 * @var array
	 */
	private $fields;

	/**
	 * Constructor.
	 * Setups actions and filters.
	 *
	 * @since 2.7
	 */
	public function __construct() {
		add_filter( 'acf/update_value', array( $this, 'store_updated_field' ), 10, 3 );
		add_filter( 'acf/delete_value', array( $this, 'store_updated_field' ), 10, 3 );
		add_action( 'pll_save_term', array( $this, 'store_term_fields' ), 5 ); // Before PLL_Sync_Metas.
		add_action( 'pll_duplicate_term', array( $this, 'store_term_fields' ), 5 ); // Before PLL_Sync_Metas.

		add_filter( 'acf/load_value', array( $this, 'load_value' ), 10, 3 );
		add_filter( 'acf/load_value/type=repeater', array( $this, 'load_value' ), 20, 3 );
		add_filter( 'acf/load_value/type=flexible_content', array( $this, 'load_value' ), 20, 3 );

		add_filter( 'pll_translate_post_meta', array( $this, 'translate_meta' ), 10, 5 );
		add_filter( 'pll_translate_term_meta', array( $this, 'translate_meta' ), 10, 4 );
	}

	/**
	 * Stores updated or deleted fields for future usage.
	 *
	 * @since 2.3
	 *
	 * @param mixed $value   Not used.
	 * @param mixed $post_id Not used.
	 * @param array $field   Custom field.
	 * @return mixed Unmodified custom field value.
	 */
	public function store_updated_field( $value, $post_id, $field ) {
		$this->fields[ $field['name'] ] = $field;
		return $value;
	}

	/**
	 * Store fields when saving a term or when duplicating a term.
	 *
	 * @since 2.3
	 *
	 * @param int $term_id Id of the term being saved.
	 * @return void
	 */
	public function store_term_fields( $term_id ) {
		$this->fields = get_field_objects( 'term_' . $term_id );
	}

	/**
	 * Copies and possibly translates custom fields when creating a new term translation.
	 *
	 * @since 2.2
	 *
	 * @param mixed  $value   Custom field value of the source term.
	 * @param string $post_id Expects term_{$term_id} for a term.
	 * @param array  $field   Custom field.
	 * @return mixed
	 */
	public function load_value( $value, $post_id, $field ) {
		if ( 'term_0' === $post_id && isset( $_GET['taxonomy'], $_GET['from_tag'], $_GET['new_lang'] ) && taxonomy_exists( sanitize_key( $_GET['taxonomy'] ) ) && $lang = PLL()->model->get_language( sanitize_key( $_GET['new_lang'] ) ) ) { // phpcs:ignore WordPress.Security.NonceVerification

			$from_tag = (int) $_GET['from_tag']; // phpcs:ignore WordPress.Security.NonceVerification
			$tr_id    = 'term_' . $from_tag; // Converts to ACF internal id.
			$fields   = get_field_objects( $tr_id );

			if ( ! empty( $fields ) ) {
				$keys = array_keys( $fields );

				/** This filter is documented in /polylang/modules/sync/admin-sync.php */
				$keys = array_unique( apply_filters( 'pll_copy_term_metas', $keys, false, $from_tag, 0, $lang->slug ) );

				// Second test to load the values of subfields of accepted fields.
				if ( in_array( $field['name'], $keys ) || preg_match( '#^(' . implode( '|', $keys ) . ')_(.+)#', $field['name'] ) ) {
					$value = acf_get_value( $tr_id, $field ); // Since ACF 5.0.0.
					$empty = null; // Parameter 1 is useless in this context.
					$value = $this->translate_fields( $empty, $value, $field['name'], $field, $lang->slug );

					if ( pll_is_translated_post_type( 'acf-field-group' ) ) {
						$references = $this->translate_fields_references( $tr_id, $lang->slug );
						$this->translate_references_in_value( $value, $references );
					}
				}
			}
		}
		return $value;
	}

	/**
	 * Translates a custom field before it is copied or synchronized.
	 *
	 * @since 2.3
	 * @since 2.4 Added parameter $to.
	 *
	 * @param mixed  $value Meta value.
	 * @param string $key   Meta key.
	 * @param string $lang  Language of target.
	 * @param int    $from  Id of the object from which we copy informations.
	 * @param int    $to    Id of the object to which we copy informations.
	 * @return mixed
	 */
	public function translate_meta( $value, $key, $lang, $from, $to = 0 ) {
		if ( ! empty( $value ) && $field = isset( $this->fields[ $key ] ) ? $this->fields[ $key ] : $this->get_field_object( $key, $from ) ) {
			$create_if_not_exists = false;

			// Check if we should create translations if they don't exist.
			// $to is not empty only when translating posts.
			if ( ! empty( $to ) && ( $post_type = get_post_type( $to ) ) && pll_is_translated_post_type( $post_type ) ) {
				$duplicate_options    = get_user_meta( get_current_user_id(), 'pll_duplicate_content', true );
				$active               = ! empty( $duplicate_options ) && ! empty( $duplicate_options[ $post_type ] );
				$create_if_not_exists = $active || PLL()->sync_post_model->are_synchronized( $from, $to );
			}

			$value = $this->translate_field( $value, $lang, $field, $create_if_not_exists );
		}

		if ( pll_is_translated_post_type( 'acf-field-group' ) && is_string( $value ) && acf_is_field_key( $value ) ) {
			$references = $this->translate_fields_references( $from, $lang );

			if ( isset( $references[ $value ] ) ) {
				$value = $references[ $value ];
			}
		}

		return $value;
	}

	/**
	 * Returns an array containing all the field data for a given field name.
	 * Unlike the original ACF function, it works for clone fields.
	 *
	 * @since 2.6.2
	 *
	 * @param string     $key     The field name or key.
	 * @param string|int $post_id The post_id of which the value is saved against.
	 * @return array|false
	 */
	protected function get_field_object( $key, $post_id ) {
		$field = get_field_object( $key, $post_id );

		if ( $field ) {
			return $field;
		}

		$post_id   = acf_get_valid_post_id( $post_id );
		$field_key = acf_get_reference( $key, $post_id ); // Since ACF 5.6.5.

		if ( ! is_string( $field_key ) ) {
			return false;
		}

		$field_key = substr( $field_key, -19 ); // Keep the last key in field_xxx_field_yyy for clone fields.

		if ( ! acf_is_field_key( $field_key ) ) {
			return false;
		}

		$field = acf_get_field( $field_key );

		if ( empty( $field ) ) {
			return false;
		}

		$field['value'] = acf_get_value( $post_id, $field );
		$field['value'] = acf_format_value( $field['value'], $post_id, $field );
		return $field;
	}

	/**
	 * Translates a CPT archive link in a page link field.
	 *
	 * @since 2.3.6
	 *
	 * @param string $link CPT archive link.
	 * @param string $lang Language slug.
	 * @return string Modified link.
	 */
	protected function translate_cpt_archive_link( $link, $lang ) {
		$lang = PLL()->model->get_language( $lang );
		$link = PLL()->links_model->switch_language_in_link( $link, $lang );

		foreach ( array_keys( PLL()->translate_slugs->slugs_model->get_translatable_slugs() ) as $type ) {
			// Unfortunately ACF does not pass the post type, so let's try with all post type archives.
			if ( 0 === strpos( $type, 'archive_' ) ) {
				$link = PLL()->translate_slugs->slugs_model->switch_translated_slug( $link, $lang, $type );
			}
		}
		return $link;
	}

	/**
	 * Translates a custom field value.
	 *
	 * @since 2.3
	 * @since 2.4 Added parameter $create_if_not_exists.
	 *
	 * @param mixed  $value                Custom field value.
	 * @param string $lang                 Language slug.
	 * @param array  $field                Custom field.
	 * @param bool   $create_if_not_exists Should we create the translation if it does not exist.
	 * @return mixed
	 */
	protected function translate_field( $value, $lang, $field, $create_if_not_exists = false ) {
		$return = $value;

		switch ( $field['type'] ) {
			case 'image':
			case 'file':
				if ( PLL()->options['media_support'] ) {
					$return = 0;
					if ( $tr_id = pll_get_post( $value, $lang ) ) {
						$return = $tr_id;
					} elseif ( $create_if_not_exists ) {
						$return = PLL()->posts->create_media_translation( $value, $lang );
					}
				}
				break;

			case 'gallery':
				if ( PLL()->options['media_support'] && is_array( $value ) ) {
					$return = array();
					foreach ( $value as $img ) {
						if ( $tr_id = pll_get_post( $img, $lang ) ) {
							$return[] = $tr_id;
						} elseif ( $create_if_not_exists ) {
							$return[] = PLL()->posts->create_media_translation( $img, $lang );
						}
					}
					$return = array_map( 'strval', $return ); // See acf_field_gallery::update_value().
				}
				break;

			case 'post_object':
			case 'relationship':
				if ( is_numeric( $value ) ) {
					$return = 0;
					if ( $tr_id = pll_get_post( $value, $lang ) ) {
						$return = $tr_id;
					}
				} elseif ( is_array( $value ) ) {
					$return = array();
					foreach ( $value as $p ) {
						if ( $tr_id = pll_get_post( $p, $lang ) ) {
							$return[] = $tr_id;
						}
					}
					$return = array_map( 'strval', $return ); // See the method update_value() for these fields.
				}
				break;

			case 'page_link':
				if ( is_numeric( $value ) ) {
					// Unique translated post.
					$return = 0;
					if ( $tr_id = pll_get_post( $value, $lang ) ) {
						$return = $tr_id;
					}
				} elseif ( is_array( $value ) ) {
					// Multiple choice.
					$return = array();
					foreach ( $value as $p ) {
						if ( is_numeric( $p ) && $tr_id = pll_get_post( $p, $lang ) ) {
							$return[] = $tr_id;
						} else {
							$return[] = $this->translate_cpt_archive_link( $p, $lang ); // Archive.
						}
					}
					$return = array_map( 'strval', $return ); // See acf_field_page_link::update_value().
				} else {
					$return = $this->translate_cpt_archive_link( $value, $lang ); // Archive.
				}
				break;

			case 'taxonomy':
				if ( pll_is_translated_taxonomy( $field['taxonomy'] ) ) {
					if ( is_numeric( $value ) ) {
						$return = 0;
						if ( $tr_id = pll_get_term( $value, $lang ) ) {
							$return = $tr_id;
						}
					} elseif ( is_array( $value ) ) {
						$return = array();
						foreach ( $value as $t ) {
							if ( $tr_id = pll_get_term( $t, $lang ) ) {
								$return[] = $tr_id;
							}
						}
					}
				}
				break;
		}

		return $return;
	}

	/**
	 * Translates repeater and flexible content sub fields (for recursive translation of this fields).
	 *
	 * @since 2.2
	 *
	 * @param array  $r     Reference to a flat list of translated custom fields.
	 * @param mixed  $value Custom field value.
	 * @param string $name  Custom field name.
	 * @param array  $field ACF field or subfield.
	 * @param string $lang  Language slug.
	 * @return array Hierarchical list of custom fields values.
	 */
	protected function translate_sub_fields( &$r, $value, $name, $field, $lang ) {
		$return = array();

		foreach ( $value as $row => $sub_fields ) {
			$sub = array();
			foreach ( $sub_fields as $id => $sub_value ) {
				$field = acf_get_field( substr( $id, -19 ) ); // Keep the last key in field_xxx_field_yyy for clone fields.
				if ( $field ) {
					$sub[ $id ] = $this->translate_fields( $r, $sub_value, $name . '_' . $row . '_' . $field['name'], $field, $lang );
				} else {
					$sub[ $id ] = $sub_value;
				}
			}
			$return[] = $sub;
		}

		return $return;
	}

	/**
	 * Recursively translates custom group, repeater and flexible content fields.
	 *
	 * @since 2.0
	 *
	 * @param array  $r     Reference to a flat list of translated custom fields.
	 * @param mixed  $value Custom field value.
	 * @param string $name  Custom field name.
	 * @param array  $field ACF field or subfield.
	 * @param string $lang  Language slug.
	 * @return array Hierarchical list of custom fields values.
	 */
	protected function translate_fields( &$r, $value, $name, $field, $lang ) {
		if ( empty( $value ) ) {
			return;
		}

		$r[ '_' . $name ] = $field['key'];

		$return = array();

		switch ( $field['type'] ) {
			case 'group':
				$sub = array();
				foreach ( $value as $id => $sub_value ) {
					if ( $field = acf_get_field( $id ) ) {
						$sub[ $id ] = $this->translate_fields( $r, $sub_value, $name . '_' . $field['name'], $field, $lang );
					} else {
						$sub[ $id ] = $sub_value;
					}
				}
				$return[] = $sub;
				break;

			case 'repeater':
			case 'flexible_content':
				$return = $this->translate_sub_fields( $r, $value, $name, $field, $lang );
				break;

			default:
				$return = $this->translate_field( $value, $lang, $field );
				break;
		}

		return empty( $return ) ? $value : $return;
	}

	/**
	 * Translated field groups:
	 * Recursively translates the references in value for repeaters and flexible content.
	 *
	 * @since 2.2
	 *
	 * @param array $value      Reference to a custom field value.
	 * @param array $references List of custom fields references with source as key and translation as value.
	 * @return void
	 */
	protected function translate_references_in_value( &$value, $references ) {
		if ( is_array( $value ) ) {
			foreach ( $value as $row => $sub_fields ) {
				if ( is_array( $sub_fields ) ) {
					foreach ( $sub_fields as $id => $sub_value ) {
						if ( is_array( $sub_value ) ) {
							$this->translate_references_in_value( $sub_value, $references );
						}
						if ( isset( $references[ $id ] ) ) {
							$value[ $row ][ $references[ $id ] ] = $sub_value;
							unset( $value[ $row ][ $id ] );
						}
					}
				}
			}
		}
	}

	/**
	 * Translated field groups:
	 * Searches for fields having the same name in translated posts.
	 *
	 * @since 2.2
	 *
	 * @param int|string $from Source post id.
	 * @param string     $lang Target language code.
	 * @return array
	 */
	protected function translate_fields_references( $from, $lang ) {
		$keys   = array();
		$fields = get_field_objects( $from );

		if ( is_array( $fields ) ) {
			foreach ( $fields as $field ) {
				if ( $tr_group = pll_get_post( $field['parent'], $lang ) ) {
					$tr_fields = acf_get_fields( $tr_group );
					$this->translate_field_references( $keys, $field, $tr_fields );
				}
			}
		}

		return $keys;
	}

	/**
	 * Translated field groups:
	 * Loops through sub fields in the recursive search for fields
	 * having the same name among translated fields groups.
	 *
	 * @since 2.2
	 *
	 * @param array $keys      Reference to an array mapping the fields keys of the translated post to the field keys of the currentpost.
	 * @param array $fields    ACF Custom fields of the current post.
	 * @param array $tr_fields ACF Custom fields of a translation.
	 * @return void
	 */
	protected function translate_sub_fields_references( &$keys, $fields, $tr_fields ) {
		foreach ( $fields as $field ) {
			$this->translate_field_references( $keys, $field, $tr_fields );
		}
	}

	/**
	 * Translated field groups:
	 * Recursively searches for fields having the same name among translated fields groups.
	 *
	 * @since 2.2
	 *
	 * @param array $keys      Reference to an array mapping the fields keys of the translated post to the field keys of the currentpost.
	 * @param array $field     ACF Custom fields of the current post.
	 * @param array $tr_fields ACF Custom fields of a translation.
	 * @return void
	 */
	protected function translate_field_references( &$keys, $field, $tr_fields ) {
		$k = array_search( $field['name'], wp_list_pluck( $tr_fields, 'name' ) );
		if ( false !== $k ) {
			$keys[ $field['key'] ] = $tr_fields[ $k ]['key'];
			if ( ! empty( $field['sub_fields'] ) ) {
				$this->translate_sub_fields_references( $keys, $field['sub_fields'], $tr_fields[ $k ]['sub_fields'] );
			}

			if ( ! empty( $field['layouts'] ) ) {
				foreach ( $field['layouts'] as $row => $layout ) {
					if ( ! empty( $layout['sub_fields'] ) ) {
						$this->translate_sub_fields_references( $keys, $layout['sub_fields'], $tr_fields[ $k ]['layouts'][ $row ]['sub_fields'] );
					}
				}
			}
		}
	}
}
