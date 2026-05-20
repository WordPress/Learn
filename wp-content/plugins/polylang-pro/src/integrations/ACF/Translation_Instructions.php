<?php
/**
 * @package Polylang-Pro
 */

namespace WP_Syntex\Polylang_Pro\Integrations\ACF;

use WP_Syntex\Polylang_Pro\Integrations\ACF\Strategy\Copy;
use WP_Syntex\Polylang_Pro\Integrations\ACF\Field_Settings;
use WP_Syntex\Polylang_Pro\Integrations\ACF\Strategy\Synchronize;
use WP_Syntex\Polylang_Pro\Integrations\ACF\Location_Language;

/**
 * This class is part of the ACF compatibility.
 * Manages the field translation instructions and their fields group settings.
 *
 * @since 3.7.2
 */
class Translation_Instructions {
	const TAB_NAME    = 'pll-instructions';
	const SETTING_KEY = 'pll_display_field_instructions';

	/**
	 * Initializes the instructions and their settings.
	 *
	 * @since 3.7.2
	 *
	 * @return void
	 */
	public function on_acf_init() {
		add_filter( 'acf/field_group/additional_group_settings_tabs', array( $this, 'add_field_instructions_setting_tab' ) );
		add_action( 'acf/field_group/render_group_settings_tab/' . self::TAB_NAME, array( $this, 'add_field_instructions_setting' ) );
		add_filter( 'acf/pre_render_fields', array( static::class, 'append_translation_instructions' ) );
	}

	/**
	 * Adds the field instructions setting tab in fields group edit page.
	 *
	 * @since 3.7.2
	 *
	 * @param array $tabs The tabs.
	 * @return array The tabs.
	 */
	public function add_field_instructions_setting_tab( $tabs ) {
		$tabs[ self::TAB_NAME ] = __( 'Translations Settings', 'polylang-pro' );

		return $tabs;
	}

	/**
	 * Adds the field instructions setting in fields group edit page.
	 *
	 * @since 3.7.2
	 *
	 * @param array $field_group The field group.
	 * @return void
	 */
	public function add_field_instructions_setting( $field_group ) {
		$is_legacy_translated_field_group = Field_Settings::is_legacy_translated_field_group( (int) $field_group['ID'] );

		if ( Location_Language::has_language_location_rule( $field_group ) && ! $is_legacy_translated_field_group ) {
			$field_group[ self::SETTING_KEY ] = 0;

			$no_translations_settings = array(
				'id'           => 'no_translations_settings',
				'required'     => 0,
				'label'        => esc_html__( 'No translations settings', 'polylang-pro' ),
				'instructions' => esc_html__( 'No translations settings are available for field group with language location rules.', 'polylang-pro' ),
			);

			acf_render_field_instructions( $no_translations_settings );
			return;
		}

		if ( $is_legacy_translated_field_group && ! isset( $field_group[ self::SETTING_KEY ] ) ) {
			$field_group[ self::SETTING_KEY ] = 1;
		}

		acf_render_field_wrap(
			array(
				'label'        => esc_html__( 'Display translation field instructions', 'polylang-pro' ),
				'instructions' => esc_html__( 'When enabled, the translation field instructions will be displayed below the field label.', 'polylang-pro' ),
				'type'         => 'true_false',
				'name'         => self::SETTING_KEY,
				'prefix'       => 'acf_field_group',
				'value'        => $field_group[ self::SETTING_KEY ] ?? 1,
				'ui'           => 1,
			)
		);
	}

	/**
	 * Appends the translation instructions to the field label using `acf/prepare_field` hook.
	 * Hooked to `acf/pre_render_fields` only to ensure instructions are displayed in the editor fields metabox.
	 *
	 * @since 3.7
	 * @since 3.7.2 Moved from Dispatcher.
	 *
	 * @param array $fields The fields being rendered.
	 * @return array The fields.
	 */
	public static function append_translation_instructions( $fields ) {
		add_filter( 'acf/prepare_field', array( static::class, 'get_field_instructions' ) );

		return $fields;
	}

	/**
	 * Returns the instructions for the given field.
	 *
	 * @since 3.7
	 * @since 3.7.2 Moved from Dispatcher.
	 *
	 * @param array|false $field The field array or false.
	 * @return array|false The field array or false.
	 */
	public static function get_field_instructions( $field ) {
		if ( ! is_array( $field ) ) {
			return $field;
		}

		$field_group = acf_get_field_group( $field['parent'] );
		if ( ! $field_group || ! isset( $field_group[ self::SETTING_KEY ] ) || ! $field_group[ self::SETTING_KEY ] ) {
			return $field;
		}

		$instructions = '<span style="font-size: 1.2em; vertical-align: middle;" class="dashicons dashicons-translation"></span> '
			. self::get_field_instruction( $field );

		$field['instructions'] = ! empty( $field['instructions'] ) ? $field['instructions'] . '<br>' . $instructions : $instructions;

		return $field;
	}

	/**
	 * Returns the instruction for the given field.
	 *
	 * @since 3.7
	 * @since 3.7.2 Moved from Dispatcher.
	 * @since 3.7.5 Changed visibility from private to public.
	 *
	 * @param array $field The field.
	 * @return string The instruction.
	 */
	public static function get_field_instruction( array $field ): string {
		if ( empty( $field ) ) {
			return '';
		}

		if ( empty( $field['translations'] ) ) {
			if ( in_array( $field['type'], array( 'group', 'repeater', 'clone', 'flexible_content' ), true ) ) {
				$copy_strategy = new Copy();
				$sync_strategy = new Synchronize( $copy_strategy );

				// Sync strategy first, otherwise the copy strategy will catch everything except `ignore`.
				if ( $sync_strategy->can_execute( $field ) ) {
					return __( 'This field is synchronized.', 'polylang-pro' );
				}

				if ( $copy_strategy->can_execute( $field ) ) {
					return __( 'This field is copied.', 'polylang-pro' );
				}
			}
		} else {
			switch ( $field['translations'] ) {
				case 'copy_once':
					return __( 'This field is copied once.', 'polylang-pro' );
				case 'sync':
					return __( 'This field is synchronized.', 'polylang-pro' );
				case 'translate':
					return __( 'This field is translated.', 'polylang-pro' );
				case 'translate_once':
					return __( 'This field is translated once.', 'polylang-pro' );
			}
		}

		return __( 'This field is ignored.', 'polylang-pro' );
	}
}
