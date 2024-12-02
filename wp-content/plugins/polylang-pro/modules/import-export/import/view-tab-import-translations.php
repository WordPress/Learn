<?php
/**
 * Displays the import translations form
 *
 * @package Polylang-Pro
 * @since 2.7
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Don't access directly.
}

$url = admin_url( 'admin.php?page=mlang_strings&noheader=true' );
?>
<div class="form-wrap">
	<form id="import-translation" method="POST" enctype="multipart/form-data" action="<?php echo esc_url( $url ); ?>">
		<div class="form-field">
			<?php wp_nonce_field( PLL_Import_Action::ACTION_NAME, PLL_Import_Action::NONCE_NAME ); ?>
			<input type="hidden" name="pll_action" value="import-translations" />
			<label class="screen-reader-text" for="importFileToUpload">
				<?php esc_html_e( 'Upload a translation file', 'polylang-pro' ); ?>
			</label>
			<input type="file" name="importFileToUpload" id="importFileToUpload">
		</div>
		<div class="form-field">
			<label for="status-select"><?php echo esc_html__( 'Select the status of the imported posts', 'polylang-pro' ); ?></label>
			<select id="status-select" name="post-status">
				<option value="draft"><?php echo esc_html__( 'Draft', 'polylang-pro' ); ?></option>
				<option value="publish"><?php echo esc_html__( 'Publish', 'polylang-pro' ); ?></option>
			</select>
		</div>
		<p class="submit">
			<button class="button button-primary" type="submit" name="importFileToUpload" value="submit"> <?php echo esc_html__( 'Upload', 'polylang-pro' ); ?> </button>
		</p>
	</form>
</div>
