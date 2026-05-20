<?php
/**
 * @package Polylang Pro
 */

namespace WP_Syntex\Polylang_Pro\Modules\Machine_Translation;

use PLL_Base;
use WP_Error;
use PLL_Admin;
use PLL_Language;
use PLL_Settings;
use PLL_Import_Export;
use PLL_Export_Container;
use PLL_Translation_Post_Model;
use PLL_Translation_Term_Model;
use PLL_Translation_Strings_Model;
use PLL_Translation_Data_Model_Interface;
use WP_Syntex\Polylang_Pro\Modules\Machine_Translation\Data;
use WP_Syntex\Polylang_Pro\Modules\Machine_Translation\Clients\Client_Interface;

/**
 * Class to create/update entities with translated data from machine translation.
 */
class Processor {
	/**
	 * Manages entities translation.
	 *
	 * @var PLL_Translation_Data_Model_Interface[]
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
		$this->translation_models = array(
			PLL_Import_Export::TYPE_POST            => new PLL_Translation_Post_Model( $polylang ),
			PLL_Import_Export::TYPE_TERM            => new PLL_Translation_Term_Model( $polylang ),
			PLL_Import_Export::STRINGS_TRANSLATIONS => new PLL_Translation_Strings_Model(),
		);

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
						// Abort if an error occurred.
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

			$target_language = $translations->get_target_language();

			foreach ( $translations->get() as $type => $entities ) {
				if ( ! isset( $this->translation_models[ $type ] ) ) {
					continue;
				}

				$ids = array();
				foreach ( $entities as $id => $data ) {
					$entry = array(
						'id'     => $id,
						'data'   => $data,
						'fields' => array(
							'post_status' => 'draft',
						),
					);

					$result = $this->translation_models[ $type ]->translate( $entry, $target_language );
					if ( is_wp_error( $result ) ) {
						$error->merge_from( $result );
						continue;
					}

					$ids[] = $id;
				}

				$this->translation_models[ $type ]->do_after_process( $ids, $target_language );

				/**
				 * Fires after objects have been saved with machine translated data.
				 *
				 * @since 3.7
				 *
				 * @param PLL_Language   $target_language The targeted language for import.
				 * @param int[]|string[] $ids             The imported object ids of the import.
				 */
				do_action( "pll_after_{$type}_machine_translation", $target_language, $ids );
			}
		}

		return $error;
	}
}
