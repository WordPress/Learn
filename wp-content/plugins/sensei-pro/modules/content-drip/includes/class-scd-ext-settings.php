<?php
/**
 * File containing the class Scd_Ext_Settings.
 *
 * @package sensei-pro
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Sensei Content Drip ( scd ) Email Settings class
 *
 * This class handles all of the functionality for the plugins email functionality.
 *
 * @package WordPress
 * @subpackage Sensei Content Drip
 * @category Core
 * @author WooThemes
 * @since 1.0.0
 *
 * TABLE OF CONTENTS
 * - __construct
 * - get_setting
 * - register_settings_tab
 * - register_settings_fields
 * todo go through all functions to make sure theyr doc info is correct
 */
class Scd_Ext_Settings {
	/**
	 * Constructor function
	 */
	public function __construct() {
		if ( is_admin() ) {
			add_filter( 'sensei_settings_tabs', [ $this, 'register_settings_tab' ] );
			add_filter( 'sensei_settings_fields', [ $this, 'register_settings_fields' ] );
		}
	}

	/**
	 * Get setting value wrapper
	 *
	 * @param string $setting_token
	 * @return string
	 */
	public function get_setting( $setting_token ) {
		global $woothemes_sensei;

		// Get all settings from sensei.
		$settings = $woothemes_sensei->settings->get_settings();

		if ( empty( $settings ) || ! isset( $settings[ $setting_token ] ) ) {
			return '';
		}

		return $settings[ $setting_token ];
	}

	/**
	 * Attaches the the contend drip settings to the sensei admin settings tabs
	 *
	 * @param  array $sensei_settings_tabs
	 * @return array
	 */
	public function register_settings_tab( $sensei_settings_tabs ) {
		$scd_tab = [
			'name'        => esc_html__( 'Content Drip', 'sensei-pro' ),
			'description' => esc_html__( 'Optional settings for the Content Drip extension', 'sensei-pro' ),
		];

		$sensei_settings_tabs['sensei-content-drip-settings'] = $scd_tab;

		return $sensei_settings_tabs;
	}

	/**
	 * Includes the content drip settings fields
	 *
	 * @param  array $sensei_settings_fields
	 * @return array
	 */
	public function register_settings_fields( $sensei_settings_fields ) {
		$sensei_settings_fields['scd_drip_message'] = [
			'name'        => esc_html__( 'Drip Message', 'sensei-pro' ),
			'description' => esc_html__( 'The user will see this when the content is not yet available. The [date] shortcode will be replaced by the actual date', 'sensei-pro' ),
			'type'        => 'textarea',
			'default'     => esc_html__( 'This lesson will become available on [date].', 'sensei-pro' ),
			'section'     => 'sensei-content-drip-settings',
		];

		$sensei_settings_fields['scd_drip_quiz_message'] = [
			'name'        => esc_html__( 'Quiz Drip Message', 'sensei-pro' ),
			'description' => esc_html__( 'The user will see this on the lesson quiz when the lesson is not yet available. The [date] shortcode will be replaced by the actual date', 'sensei-pro' ),
			'type'        => 'textarea',
			'default'     => esc_html__( 'This quiz will become available on [date].', 'sensei-pro' ),
			'section'     => 'sensei-content-drip-settings',
		];

		// Email related settings.
		$sensei_settings_fields['scd_disable_email_notifications'] = [
			'name'        => __( 'Email Notifications', 'sensei-pro' ),
			'description' => __( 'Disable Email Notifications', 'sensei-pro' ),
			'type'        => 'checkbox',
			'default'     => 'false',
			'section'     => 'sensei-content-drip-settings',
		];

		$sensei_settings_fields['scd_email_body_notice_html'] = [
			'name'        => esc_html__( 'Email Before Lessons', 'sensei-pro' ),
			'description' => esc_html__( 'The text before the list of lessons dripping today.', 'sensei-pro' ),
			'type'        => 'textarea',
			'default'     => esc_html__( 'The following lessons will become available today:', 'sensei-pro' ),
			'section'     => 'sensei-content-drip-settings',
		];

		$sensei_settings_fields['scd_email_footer_html'] = [
			'name'        => esc_html__( 'Email Footer', 'sensei-pro' ),
			'description' => esc_html__( 'The text below the list of lessons dripping today', 'sensei-pro' ),
			'type'        => 'textarea',
			'default'     => esc_html__( 'Visit the online course today to start taking the lessons: [home_url]', 'sensei-pro' ),
			'section'     => 'sensei-content-drip-settings',
		];

		return $sensei_settings_fields;
	}
}
