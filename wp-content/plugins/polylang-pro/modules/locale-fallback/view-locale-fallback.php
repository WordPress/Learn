<?php
/**
 * Display the locale fallbacks field in the language page.
 *
 * @package Polylang-Pro
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Don't access directly.
}
?>
<div class="form-field">
	<label for="lang_fallback"><?php esc_html_e( 'Locale fallback', 'polylang-pro' ); ?></label>
	<input name="fallback" id="lang_fallback" type="text" value="<?php echo esc_attr( $fallbacks_list ); ?>" size="40" aria-required="true" />
	<p><?php esc_html_e( 'WordPress locale to use if a translation file is not available in the main locale.', 'polylang-pro' ); ?></p>
</div>
