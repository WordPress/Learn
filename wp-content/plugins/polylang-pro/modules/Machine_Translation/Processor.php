<?php
/**
 * @package Polylang Pro
 */

namespace WP_Syntex\Polylang_Pro\Modules\Machine_Translation;

use PLL_Base;
use WP_Error;
use PLL_Admin;
use PLL_Settings;
use PLL_Import_Export;
use PLL_Export_Container;
use PLL_Translation_Post_Model;
use PLL_Translation_Term_Model;
use PLL_Translation_Object_Model_Interface;
use WP_Syntex\Polylang_Pro\Modules\Machine_Translation\Data;
use WP_Syntex\Polylang_Pro\Modules\Machine_Translation\Clients\Client_Interface;

/**
 * Class to create/update entities with translated data from machine translation.
 */
class Processor {
	/**
	 * Manages entities translation.
	 *
	 * @var PLL_Translation_Object_Model_Interface[]
	 */
	private $translation_models;


	/**
	 * Machine translation client.
	 *
	 * @var Client_Interface
	 */
	private $client;

	/**
	 * Constructor.
	 *
	 * @since 3.6
	 *
	 * @param PLL_Admin|PLL_Settings $polylang Polylang main object.
	 * @param Client_Interface       $client   Machine translation client to use.
	 * @return void
	 */
	public function __construct( PLL_Base &$polylang, Client_Interface $client ) {
		$this->translation_models[ PLL_Import_Export::TYPE_POST ] = new PLL_Translation_Post_Model( $polylang );
		$this->translation_models[ PLL_Import_Export::TYPE_TERM ] = new PLL_Translation_Term_Model( $polylang );
		$this->client = $client;
	}

	/**
	 * Translates all data from the given container.
	 *
	 * @since 3.6
	 *
	 * @param PLL_Export_Container $container Container with data to translate.
	 * @return WP_Error Error object.
	 */
	public function translate( PLL_Export_Container $container ): WP_Error {
		$error = new WP_Error();

		foreach ( $container as &$data ) {
			if ( ! $data instanceof Data ) {
				continue;
			}

			foreach ( $data->get() as $entities ) {
				foreach ( $entities as &$translations ) {
					$result = $this->client->translate( $translations, $data->get_target_language(), $data->get_source_language() );

					if ( \is_wp_error( $result ) ) {
						// Abort if an error occured.
						return $result;
					}

					$translations = $result;
				}
			}
		}

		return $error;
	}

	/**
	 * Saves all translated data from the given container into corresponding entities.
	 *
	 * @since 3.6
	 *
	 * @param PLL_Export_Container $container Container with data to save.
	 * @return WP_Error Error object.
	 */
	public function save( PLL_Export_Container $container ): WP_Error {
		$error = new WP_Error();

		foreach ( $container as $translations ) {
			if ( ! $translations instanceof Data ) {
				continue;
			}

			foreach ( $translations->get() as $type => $entities ) {
				if ( ! isset( $this->translation_models[ $type ] ) ) {
					continue;
				}

				foreach ( $entities as $id => $data ) {
					$entry = array(
						'id'     => $id,
						'data'   => $data,
						'fields' => array(
							'post_status' => 'draft',
						),
					);

					$tr_id = $this->translation_models[ $type ]->translate( $entry, $translations->get_target_language() );

					if ( 0 === $tr_id ) {
						$error->add(
							'pll_machine_translation_no_translate',
							sprintf(
								/* translators: %1$s is a type of content, post or term, %2$s is a numeric ID. */
								__( '%1$s with ID %2$d could not be translated.', 'polylang-pro' ),
								$type,
								$id
							)
						);
					}
				}
			}
		}

		return $error;
	}
}
