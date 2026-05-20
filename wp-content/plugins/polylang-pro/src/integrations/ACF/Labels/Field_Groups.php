<?php
/**
 * @package Polylang-Pro
 */

namespace WP_Syntex\Polylang_Pro\Integrations\ACF\Labels;

use PLL_Import_Action;
use PLL_Admin_Base;
use PLL_Settings;
use PLL_Format_Util;

/**
 * This class is part of the ACF compatibility.
 * Registers and translates ACF's fields labels.
 *
 * @since 3.7
 *
 * @phpstan-type ToRegister array{
 *     name: string,
 *     string: string,
 *     multiline?: bool
 * }
 * @phpstan-type LabelsMap array<
 *     non-empty-string,
 *     string|array<non-empty-string, string|array<non-empty-string, string>>
 * >
 * @phpstan-type LabelsByType array<non-empty-string, LabelsMap>
 */
class Field_Groups {
	public const CACHE_KEY = 'pll_acf_field_labels';

	/**
	 * @var array|null
	 *
	 * @phpstan-var LabelsByType|null
	 */
	private $labels;

	/**
	 * Setups actions and filters.
	 *
	 * @since 3.7
	 *
	 * @return void
	 */
	public function on_acf_init(): void {
		add_action( 'acf/updated_field', array( self::class, 'clear_cache' ) );
		add_action( 'acf/delete_field', array( self::class, 'clear_cache' ) );
		add_action( 'acf/update_field_group', array( self::class, 'clear_cache' ) );
		add_action( 'acf/delete_field_group', array( self::class, 'clear_cache' ) );

		if ( PLL() instanceof PLL_Admin_Base ) {
			// `pll_register_string()` does nothing outside `PLL_Admin_Base`. No need to loop the fields then.
			$this->register_field_groups();
		}

		if ( ! $this->can_translate_labels() ) {
			return;
		}

		if ( PLL() instanceof PLL_Admin_Base ) {
			// Clear the fields cache (filled by `$this->register_field_groups()`) to be able to translate the field labels via the filters.
			acf_get_store( 'fields' )->reset();
		}

		add_filter( 'acf/load_field', array( $this, 'translate_field_labels' ), 20 ); // After `child_of_acf_field::load_field()` (prio 10).
		add_filter( 'acf/load_field_groups', array( $this, 'translate_field_groups_labels' ), 25 ); // After `_acf_apply_get_local_internal_posts()` (prio 20).
	}

	/**
	 * Clears label translations cache.
	 *
	 * @since 3.8
	 *
	 * @return void
	 */
	public static function clear_cache(): void {
		delete_transient( self::CACHE_KEY );
	}

	/**
	 * Registers the labels of all field groups.
	 *
	 * @since 3.7
	 *
	 * @return void
	 */
	public function register_field_groups() {
		$to_register = get_transient( self::CACHE_KEY );

		if ( ! is_array( $to_register ) ) {
			$to_register = array();
			acf_disable_filter( 'clone' ); // To allow seamless clone fields to appear in acf_get_fields().

			foreach ( acf_get_field_groups() as $field_group ) {
				$to_register[] = array(
					'name'   => 'Title',
					'string' => $field_group['title'],
				);
				$to_register = $this->register_fields( $to_register, acf_get_fields( $field_group ) );
			}

			acf_enable_filter( 'clone' );
			set_transient( self::CACHE_KEY, $to_register );
		}

		foreach ( $to_register as $register_string ) {
			pll_register_string( $register_string['name'], $register_string['string'], 'ACF', $register_string['multiline'] ?? false );
		}
	}

	/**
	 * Recursively translates labels that are not originally translated by ACF for the given field.
	 *
	 * @since 3.7
	 *
	 * @param array $field The field array.
	 * @return array
	 */
	public function translate_field_labels( $field ) {
		if ( ! is_array( $field ) || ! isset( $field['type'] ) ) {
			return $field;
		}

		$matcher = new PLL_Format_Util();

		foreach ( $this->get_field_labels_to_translate() as $field_type => $field_labels ) {
			if ( ! $matcher->matches( $field['type'], $field_type ) ) {
				continue;
			}

			$field = $this->translate_field_labels_recursive( $field_labels, $field );
		}

		return $field;
	}

	/**
	 * Tells if the labels should be translated in the current context.
	 *
	 * @since 3.7
	 *
	 * @return bool
	 */
	private function can_translate_labels(): bool {
		global $pagenow;

		if ( 'admin.php' === $pagenow && isset( $_POST['pll_action'], $_POST[ PLL_Import_Action::NONCE_NAME ] )
			&& PLL_Import_Action::ACTION_VALUE === $_POST['pll_action']
			&& wp_verify_nonce( $_POST[ PLL_Import_Action::NONCE_NAME ], PLL_Import_Action::ACTION_NAME ) ) {
			// Need to translate the labels during import to compare the default values.
			return true;
		}

		// Polylang's settings pages.
		if ( PLL() instanceof PLL_Settings ) {
			return false;
		}

		// ACF's settings pages.
		$acf_post_types = array( 'acf-field-group', 'acf-post-type', 'acf-taxonomy', 'acf-ui-options-page' );

		if ( 'edit.php' === $pagenow && isset( $_GET['post_type'] ) && in_array( $_GET['post_type'], $acf_post_types, true ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			return false;
		}

		if ( 'post.php' === $pagenow && isset( $_GET['post'] ) && in_array( get_post_type( (int) $_GET['post'] ), $acf_post_types, true ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			return false;
		}

		return true;
	}

	/**
	 * Recursively translates the given labels for the given field or subset of a field (e.g subfields or layouts).
	 *
	 * @since 3.7
	 *
	 * @param array $labels An array of labels to translate with label keys as array keys and label names as array values.
	 * @param array $field  A custom field definition.
	 * @return array The translated labels.
	 *
	 * @phpstan-param LabelsMap $labels
	 */
	private function translate_field_labels_recursive( array $labels, array $field ) {
		$matcher = new PLL_Format_Util();

		if ( isset( $field['default_value'] ) ) {
			// Stores the untranslated default value for later use.
			$field['pll_default_value'] = $field['default_value'];
		}

		foreach ( $labels as $field_key => $sub_labels ) {
			$field_filtered = $matcher->filter_list( $field, $field_key );

			foreach ( $field_filtered as $key => $field_value ) {
				if ( is_array( $sub_labels ) ) {
					/**
					 * `$sub_labels` is like:
					 * array(
					 *     'default_value' => _x( 'Default value', 'ACF field setting label', 'polylang-pro' ),
					 *     'placeholder'   => _x( 'Placeholder', 'ACF field setting label', 'polylang-pro' ),
					 * )
					 */
					if ( is_array( $field_value ) ) {
						$field[ $key ] = $this->translate_field_labels_recursive( $sub_labels, $field_value );
					}
				} elseif ( '' !== $field_value ) {
					$field[ $key ] = pll__( $field_value );
				}
			}
		}

		return $field;
	}

	/**
	 * Translates all the ACF field groups labels.
	 *
	 * We hook to 'acf/load_field_groups' instead 'acf/translate_field_group',
	 * not to be overridden by ACF when its local store is enabled.
	 *
	 * @see _acf_apply_get_local_internal_posts()
	 *
	 * @since 3.7
	 *
	 * @param array[] $posts An array of ACF posts.
	 * @return array[] The array of ACF posts with the translated post title.
	 */
	public function translate_field_groups_labels( $posts ) {
		foreach ( $posts as $key => $post ) {
			$posts[ $key ]['title'] = pll__( $post['title'] );
		}
		return $posts;
	}

	/**
	 * Registers the labels of fields.
	 * The method is recursive for layout fields.
	 *
	 * @since 3.7
	 *
	 * @param array $to_register List of strings to register.
	 * @param array $fields      An array of Custom field definitions.
	 * @return array
	 *
	 * @phpstan-return list<ToRegister>
	 */
	private function register_fields( array $to_register, array $fields ): array {
		foreach ( $fields as $field ) {
			if ( ! acf_is_field_type( $field['type'] ) ) {
				// Avoid error if, for example, a repeater is created in ACF Pro and used later in ACF.
				continue;
			}

			switch ( $field['type'] ) {
				case 'group':
				case 'repeater':
					$to_register = $this->register_fields( $to_register, $field['sub_fields'] );
					break;
				case 'flexible_content':
					foreach ( $field['layouts'] as $layout ) {
						$to_register = $this->register_fields( $to_register, $layout['sub_fields'] );
					}
					break;
			}

			$matcher = new PLL_Format_Util();
			foreach ( $this->get_field_labels_to_translate() as $field_type => $field_labels ) {
				if ( ! is_string( $field['type'] ) || ! $matcher->matches( $field['type'], (string) $field_type ) ) {
					continue;
				}

				$to_register = $this->register_field( $to_register, $field_labels, $field );
			}
		}

		return $to_register;
	}

	/**
	 * Registers the labels of a field recursively.
	 *
	 * @since 3.7
	 *
	 * @param array $to_register List of strings to register.
	 * @param array $labels      An array of labels to register with label keys as array keys and label names as array values.
	 * @param array $field       A custom field definition.
	 * @return array
	 *
	 * @phpstan-param LabelsMap $labels
	 * @phpstan-return list<ToRegister>
	 */
	private function register_field( array $to_register, array $labels, array $field ): array {
		$matcher = new PLL_Format_Util();

		foreach ( $labels as $field_key => $label ) {
			$field_filtered = $matcher->filter_list( $field, $field_key );

			foreach ( $field_filtered as $field_value ) {
				if ( is_array( $label ) ) {
					/**
					 * `$label` is like:
					 * array(
					 *     'default_value' => _x( 'Default value', 'ACF field setting label', 'polylang-pro' ),
					 *     'placeholder'   => _x( 'Placeholder', 'ACF field setting label', 'polylang-pro' ),
					 * )
					 */
					if ( is_array( $field_value ) ) {
						$to_register = $this->register_field( $to_register, $label, $field_value );
					}
				} else {
					$to_register[] = array(
						'name'      => $label,
						'string'    => $field_value,
						'multiline' => true,
					);
				}
			}
		}

		return $to_register;
	}

	/**
	 * Returns the list of field labels to translate, by field type and label key.
	 * Wildcards are supported for the field types and label keys.
	 *
	 * @since 3.7
	 *
	 * @return array {
	 *     An array with field types as array keys, and recursive sub-arrays as array values.
	 *     These sub-arrays have label keys as array keys, and label names as array values.
	 *
	 *     Ex:
	 *     array(
	 *         'custom_field_type' => array(
	 *             'custom_field_key'  => 'Custom field key',
	 *             'another_field_key' => array(
	 *                 'first_choice'  => 'First choice',
	 *                 'second_choice' => 'Second choice',
	 *             ),
	 *         ),
	 *     )
	 * }
	 *
	 * @phpstan-return LabelsByType
	 */
	private function get_field_labels_to_translate(): array {
		if ( is_array( $this->labels ) ) {
			return $this->labels;
		}

		$labels = array(
			'default_value' => _x( 'Default value', 'ACF field setting label', 'polylang-pro' ),
			'placeholder'   => _x( 'Placeholder', 'ACF field setting label', 'polylang-pro' ),
			'prepend'       => _x( 'Prefix', 'ACF field setting label', 'polylang-pro' ),
			'append'        => _x( 'Suffix', 'ACF field setting label', 'polylang-pro' ),
			'message'       => _x( 'Message', 'ACF field setting label', 'polylang-pro' ),
			'ui_on_text'    => _x( 'ON text', 'ACF field setting label', 'polylang-pro' ),
			'ui_off_text'   => _x( 'OFF text', 'ACF field setting label', 'polylang-pro' ),
			'choice'        => _x( 'Choice', 'ACF field setting label', 'polylang-pro' ),
			'label'         => _x( 'Label', 'ACF field setting label', 'polylang-pro' ),
		);

		$this->labels = array(
			'*'                => array(
				'label'        => $labels['label'],
				'instructions' => _x( 'Instructions', 'ACF field setting label', 'polylang-pro' ),
			),
			'button_group'     => array(
				'choices' => array(
					'*' => $labels['choice'],
				),
			),
			'checkbox'         => array(
				'choices' => array(
					'*' => $labels['choice'],
				),
			),
			'email'            => array(
				'default_value' => $labels['default_value'],
				'placeholder'   => $labels['placeholder'],
				'prepend'       => $labels['prepend'],
				'append'        => $labels['append'],
			),
			'flexible_content' => array(
				'layouts' => array(
					'*' => array(
						'label' => $labels['label'],
					),
				),
			),
			'number'           => array(
				'placeholder' => $labels['placeholder'],
				'prepend'     => $labels['prepend'],
				'append'      => $labels['append'],
			),
			'message'          => array(
				'message' => $labels['message'],
			),
			'password'         => array(
				'placeholder' => $labels['placeholder'],
				'prepend'     => $labels['prepend'],
				'append'      => $labels['append'],
			),
			'radio'            => array(
				'choices' => array(
					'*' => $labels['choice'],
				),
			),
			'range'            => array(
				'prepend' => $labels['prepend'],
				'append'  => $labels['append'],
			),
			'select'           => array(
				'choices' => array(
					'*' => $labels['choice'],
				),
			),
			'text'             => array(
				'default_value' => $labels['default_value'],
				'placeholder'   => $labels['placeholder'],
				'prepend'       => $labels['prepend'],
				'append'        => $labels['append'],
			),
			'textarea'         => array(
				'default_value' => $labels['default_value'],
				'placeholder'   => $labels['placeholder'],
			),
			'true_false'       => array(
				'message'     => $labels['message'],
				'ui_on_text'  => $labels['ui_on_text'],
				'ui_off_text' => $labels['ui_off_text'],
			),
			'url'              => array(
				'default_value' => $labels['default_value'],
				'placeholder'   => $labels['placeholder'],
			),
			'wysiwyg'          => array(
				'default_value' => $labels['default_value'],
			),
		);

		/**
		 * Filters the list of field keys to translate, by field type.
		 * Wildcards are supported for the field types.
		 *
		 * @var array $labels array {
		 *     An array with field types as array keys, and recursive sub-arrays as array values.
		 *     These sub-arrays have label keys as array keys, and label names as array values.
		 *
		 *     Ex:
		 *     array(
		 *         'custom_field_type' => array(
		 *             'custom_field_key'  => 'Custom field key',
		 *             'another_field_key' => array(
		 *                 'first_choice'  => 'First choice',
		 *                 'second_choice' => 'Second choice',
		 *             ),
		 *         ),
		 *     )
		 * }
		 */
		$labels = apply_filters( 'pll_acf_field_labels_to_translate', $this->labels );

		if ( is_array( $labels ) ) {
			$this->labels = $labels;
		}

		return $this->labels;
	}
}
