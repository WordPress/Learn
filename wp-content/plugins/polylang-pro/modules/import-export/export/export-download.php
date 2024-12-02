<?php
/**
 * @package Polylang-Pro
 */

/**
 * Handles export results as a file to download.
 *
 * @since 3.6
 */
class PLL_Export_Download {

	/**
	 * Name of the file.
	 *
	 * @var string
	 */
	private $filename;

	/**
	 * Content of the file.
	 *
	 * @var string
	 */
	private $content;

	/**
	 * The content type of the file.
	 *
	 * @var string
	 */
	private $content_type;

	/**
	 * Creates the file to download.
	 *
	 * Multiple files are archived in a zip file.
	 *
	 * @since 3.6
	 *
	 * @see https://www.php.net/manual/class.ziparchive.php PHP ZipArchive library
	 *
	 * @param PLL_Export_Container $container List of exported data.
	 * @return WP_Error Returned errors. Empty when there are no errors.
	 */
	public function create( PLL_Export_Container $container ): WP_Error {
		$content = '';

		if ( count( $container ) === 1 ) {
			$export = $container->getIterator()->current();

			if ( $export instanceof PLL_Export_File ) {
				$this->filename     = $export->get_filename();
				$this->content_type = 'text/plain';

				$content = $export->get();
			}
		} else {
			$this->filename     = 'pll_export_' . time() . '.zip';
			$this->content_type = 'application/zip';

			$content = $this->get_zip_archive( $container );
		}

		if ( is_wp_error( $content ) ) {
			return $content;
		}

		$this->content = $content;

		add_action( 'wp_redirect', array( $this, 'send_response' ) );

		return new WP_Error();
	}

	/**
	 * Creates a zip archive with all files to download.
	 *
	 * @since 3.6
	 *
	 * @see https://www.php.net/manual/class.ziparchive.php PHP ZipArchive library
	 *
	 * @param PLL_Export_Container $container List of exported data.
	 * @return string|WP_Error
	 */
	private function get_zip_archive( PLL_Export_Container $container ) {
		if ( ! class_exists( 'ZipArchive' ) ) {
			return new WP_Error( 'pll_ziparchive_not_exists', __( 'Error: ZipArchive is required to download multiple files. Please contact your host provider.', 'polylang-pro' ) );
		}

		$upload_dir = wp_upload_dir()['path'];


		if ( ! is_writable( $upload_dir ) ) { // phpcs:ignore WordPressVIPMinimum.Functions.RestrictedFunctions.file_ops_is_writable
			return new WP_Error( 'pll_upload_dir_not_writable', __( 'Error: Upload directory must be writable. Please contact your host provider.', 'polylang-pro' ) );
		}

		$zip = new ZipArchive();

		$filepath = $upload_dir . '/' . $this->filename;

		if ( ! $zip->open( $filepath, ZipArchive::CREATE ) ) {
			return new WP_Error( 'pll_zip_file_error', __( 'Error: Impossible to create a Zip file.', 'polylang-pro' ) );
		}

		foreach ( $container as $export ) {
			if ( ! $export instanceof PLL_Export_File ) {
				continue;
			}

			$export_content = $export->get();

			if ( ! $export_content ) {
				continue;
			}

			$zip->addFromString( $export->get_filename(), $export_content );
		}

		if ( ! $zip->close() ) {
			return new WP_Error( 'pll_zip_file_error', __( 'Error: Impossible to create a Zip file.', 'polylang-pro' ) );
		}

		$content = (string) file_get_contents( $filepath );
		wp_delete_file( $filepath );

		return $content;
	}

	/**
	 * Sends the file to download.
	 *
	 * @since 3.6
	 *
	 * @return void
	 */
	public function send_response() {
		header( 'Content-Disposition: attachment; filename=' . $this->filename );
		header( 'Content-Type: ' . $this->content_type );
		header( 'Content-Length: ' . strlen( $this->content ) );

		if ( ob_get_length() > 0 ) {
			ob_clean();
		}

		echo $this->content; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		exit;
	}
}
