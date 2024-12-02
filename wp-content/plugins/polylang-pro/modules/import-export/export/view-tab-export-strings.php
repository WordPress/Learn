<?php
/**
 * Displays the strings translations export form
 *
 * @package Polylang-Pro
 * @since 2.7
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Don't access directly.
}

$url = admin_url( 'admin.php?page=mlang_strings&translate=export&noheader=true' );
$languages = $this->model->get_languages_list();
$default_language = $this->model->get_default_language();
$strings = PLL_Admin_Strings::get_strings();
$groups = array_unique( wp_list_pluck( $strings, 'context' ) );
$supported_formats = ( new PLL_File_Format_Factory() )->get_supported_formats( 'strings' ); // phpcs:ignore VariableAnalysis.CodeAnalysis.VariableAnalysis.UnusedVariable
?>
<div class="form-wrap">
	<form id="export-string-translation" method="post" action="<?php echo esc_url( $url ); ?>">
		<div class="form-field">
			<fieldset>
				<?php wp_nonce_field( PLL_Export_Strings_Action::ACTION_NAME, PLL_Export_Strings_Action::NONCE_NAME ); ?>
				<input type="hidden" name="pll_action" value="export-translations" />
				<legend class="pll-legend"><?php esc_html_e( 'Target languages', 'polylang-pro' ); ?></legend>
				<?php
				foreach ( $languages as $language ) {
					if ( $default_language !== $language ) {
						printf(
							'<label><input name="target-lang[]" type="checkbox" value="%1$s" /><span class="pll-translation-flag">%3$s</span>%2$s</label>',
							esc_attr( $language->slug ),
							esc_html( $language->name ),
							$language->flag // phpcs:ignore WordPress.Security.EscapeOutput
						);
					}
				}
				?>
			</fieldset>
		</div>
		<div class="form-field">
			<label for="select-groups"><?php esc_html_e( 'Filter group', 'polylang-pro' ); ?></label>
			<select name="group" id="select-groups">
				<option selected value="-1">
					<?php esc_html_e( 'Export all groups', 'polylang-pro' ); ?>
				</option>
				<?php
				foreach ( $groups as $group ) {
					?>
					<option value="<?php echo esc_attr( $group ); ?>">
						<?php echo esc_html( $group ); ?>
					</option>
					<?php
				}
				?>
			</select>
		</div>
		<div class="form-field">
			<?php
			// Uses $supported_formats variable.
			include __DIR__ . '/view-export-file-format.php'; // phpcs:ignore PEAR.Files.IncludingFile.UseRequire
			?>
		</div>
		<p class="submit">
			<button type="submit" name="action" class="button button-primary" value="download"><?php echo esc_html__( 'Download', 'polylang-pro' ); ?></button>
		</p>
	</form>
</div>
