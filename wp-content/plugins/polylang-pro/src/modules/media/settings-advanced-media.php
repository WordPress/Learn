<?php
/**
 * @package Polylang-Pro
 */

/**
 * Settings class for media language and translation management
 * Advanced version
 *
 * @since 1.9
 */
class PLL_Settings_Advanced_Media extends PLL_Settings_Module {
	/**
	 * Stores the display order priority.
	 *
	 * @var int
	 */
	public $priority = 30;

	/**
	 * Constructor
	 *
	 * @since 1.9
	 *
	 * @param PLL_Settings $polylang Polylang object.
	 */
	public function __construct( &$polylang ) {
		parent::__construct(
			$polylang,
			array(
				'module'        => 'advanced_media',
				'title'         => __( 'Media', 'polylang-pro' ),
				'description'   => __( 'Activate languages and translations for media. Provides options for multilingual media management.', 'polylang-pro' ),
				'active_option' => 'media_support',
			)
		);
	}

	/**
	 * Displays the settings form
	 *
	 * @since 1.9
	 *
	 * @return void
	 */
	protected function form() {
		printf(
			'<label for="duplicate-media"><input id="duplicate-media" name="media[duplicate]" type="checkbox" value="1" %s /> %s</label>',
			checked( empty( $this->options['media']['duplicate'] ), false, false ),
			esc_html__( 'Automatically duplicate media in all languages when uploading a new file.', 'polylang-pro' )
		);
	}

	/**
	 * Prepare the received data before saving.
	 *
	 * @since 3.7
	 *
	 * @param array $options Raw values to save.
	 * @return array
	 */
	protected function prepare_raw_data( array $options ): array {
		$newoptions = array( 'media' => array( 'duplicate' => ! empty( $options['media']['duplicate'] ) ? 1 : 0 ) );
		return $newoptions; // Take care to return only validated options.
	}

	/**
	 * Get the row actions
	 *
	 * @since 1.9
	 *
	 * @return string[]
	 */
	protected function get_actions() {
		return empty( $this->options['media_support'] ) ? array( 'activate' ) : array( 'configure', 'deactivate' );
	}
}
