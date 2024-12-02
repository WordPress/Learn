<?php
/**
 * @package Polylang-Pro
 */

/**
 * Initialize the Xliff Exporter Module
 *
 * @since 2.7
 */
class PLL_Import_Export {
	const TYPE_POST = 'post';
	const TYPE_TERM = 'term';

	const TERM_NAME = 'name';
	const TERM_DESCRIPTION = 'description';
	const TERM_META = 'termmeta';

	const POST_TITLE = 'post_title';
	const POST_CONTENT = 'post_content';
	const POST_EXCERPT = 'post_excerpt';
	const POST_META = 'postmeta';

	const STRINGS_TRANSLATIONS = 'strings-translations';

	/**
	 * Name of the app that generates the export files.
	 *
	 * @var string
	 */
	const APP_NAME = 'Polylang';

	/**
	 * @since 2.7
	 *
	 * @var PLL_Model
	 */
	private $model;

	/**
	 * Constructor
	 * Registers the hooks
	 *
	 * @since 2.7
	 *
	 * @param PLL_Base $polylang Current instance of the Polylang context.
	 */
	public function __construct( &$polylang ) {
		$this->model = &$polylang->model;

		if ( $polylang instanceof PLL_Admin && class_exists( 'PLL_Export_Bulk_Option' ) ) {
			add_action( 'pll_bulk_translate_options_init', array( $this, 'add_bulk_translate_options' ) );
		}

		if ( $polylang instanceof PLL_Settings ) {
			add_action( 'load-languages_page_mlang_strings', array( $this, 'add_meta_boxes' ) );
			add_action( 'load-languages_page_mlang_strings', array( $this, 'admin_enqueue_style' ) );

			/*
			 * See hook `mlang_action_{$action}` in `PLL_Settings::handle_actions()`.
			 * Security: if the two following callbacks are not hooked to `mlang_action_import-translations` and
			 * `mlang_action_export-translations` anymore, make sure to verify the current user's capabilities.
			 */
			add_action(
				'mlang_action_import-translations',
				// Lazyload `PLL_Import_Action`'s instantiation.
				function () use ( &$polylang ) {
					( new PLL_Import_Action(
						$this->model,
						array(
							self::TYPE_POST            => new PLL_Import_Posts( new PLL_Translation_Post_Model( $polylang ) ),
							self::TYPE_TERM            => new PLL_Import_Terms( new PLL_Translation_Term_Model( $polylang ) ),
							self::STRINGS_TRANSLATIONS => new PLL_Import_Strings(),
						),
						new PLL_File_Format_Factory()
					) )->import_action();
				}
			);
			add_action(
				'mlang_action_export-translations',
				function () {
					( new PLL_Export_Strings_Action( $this->model, new PLL_Export_Download() ) )->export_action();
				}
			);
		}
	}

	/**
	 * Adds 'pll_export_post' bulk option in Translate bulk action {@see PLL_Bulk_Translate::register_options()}
	 *
	 * @since 2.7
	 * @since 3.6.5 Added `$bulk_translate` parameter.
	 *
	 * @param PLL_Bulk_Translate $bulk_translate Instance of `PLL_Bulk_Translate`.
	 * @return void
	 */
	public function add_bulk_translate_options( $bulk_translate ): void {
		$bulk_translate->register_options(
			array(
				new PLL_Export_Bulk_Option(
					$this->model,
					new PLL_Export_Download()
				),
			)
		);
	}

	/**
	 * Add Metaboxes, shown only if there is more than one language registered.
	 *
	 * @since 2.7
	 *
	 * @return void
	 */
	public function add_meta_boxes() {
		if ( 1 < count( $this->model->get_languages_list() ) ) {
			add_meta_box(
				'pll-export-strings-box',
				__( 'Export string translations', 'polylang-pro' ),
				array( $this, 'metabox_export_strings' ),
				'languages_page_mlang_strings',
				'normal'
			);

			add_meta_box(
				'pll-import-translations-box',
				__( 'Import translations', 'polylang-pro' ),
				array( $this, 'metabox_import_translation' ),
				'languages_page_mlang_strings',
				'normal'
			);
		}
	}

	/**
	 * Metabox export strings.
	 *
	 * @since 2.7
	 *
	 * @return void
	 */
	public function metabox_export_strings() {
		include POLYLANG_PRO_DIR . '/modules/import-export/export/view-tab-export-strings.php';
	}

	/**
	 * Metabox import translations.
	 *
	 * @since 2.7
	 *
	 * @return void
	 */
	public function metabox_import_translation() {
		include POLYLANG_PRO_DIR . '/modules/import-export/import/view-tab-import-translations.php';
	}

	/**
	 * Enqueue stylesheet for import/export on admin side.
	 *
	 * @since 3.1
	 *
	 * @return void
	 */
	public function admin_enqueue_style() {
		$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
		wp_enqueue_style( 'pll-admin-export-import', plugins_url( '/css/build/admin-export-import' . $suffix . '.css', POLYLANG_ROOT_FILE ), array( 'colors' ), POLYLANG_VERSION );
	}
}
