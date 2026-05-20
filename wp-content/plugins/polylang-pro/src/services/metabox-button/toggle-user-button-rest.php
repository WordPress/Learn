<?php
/**
 * @package Polylang-Pro
 */

/**
 * Template class holding logic for user button toggling in REST API.
 *
 * @since 3.8
 */
abstract class PLL_Toggle_User_Button_REST {
	/**
	 * @var PLL_Model
	 */
	private $model;

	/**
	 * @var PLL_Toggle_User_Meta
	 */
	private $user_meta;

	/**
	 * Constructor.
	 *
	 * @param PLL_Model            $model The model instance.
	 * @param PLL_Toggle_User_Meta $user_meta The user meta instance.
	 *
	 * @since 3.8
	 */
	public function __construct( PLL_Model $model, PLL_Toggle_User_Meta $user_meta ) {
		$this->model     = $model;
		$this->user_meta = $user_meta;

		add_action( 'rest_api_init', array( $this, 'register_rest_field' ) );
	}

	/**
	 * Returns the description of the schema for the current user meta.
	 *
	 * @since 3.8
	 *
	 * @return string The description of the schema.
	 */
	abstract protected function get_schema_description(): string;

	/**
	 * Register the REST field for the current user meta.
	 * Hooked on 'rest_api_init' to prevent load text domain notice.
	 *
	 * @since 3.8
	 *
	 * @return void
	 */
	public function register_rest_field(): void {
		register_rest_field(
			'user',
			$this->user_meta->get_meta_name(),
			array(
				'get_callback'    => array( $this->user_meta, 'get' ),
				'update_callback' => array( $this->user_meta, 'update' ),
				'schema'          => $this->get_schema(),
			)
		);
	}

	/**
	 * Returns the schema for the current user meta.
	 *
	 * @since 3.8
	 *
	 * @return array JSON schema.
	 */
	private function get_schema(): array {
		return array(
			'description' => $this->get_schema_description(),
			'type'        => 'object',
			'properties'  => array_fill_keys(
				$this->model->post->get_translated_object_types(),
				array(
					'type' => 'boolean',
				)
			),
		);
	}
}
