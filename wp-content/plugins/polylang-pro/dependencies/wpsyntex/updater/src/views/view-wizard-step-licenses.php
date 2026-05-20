<?php
/**
 * Displays the wizard licenses step.
 *
 * @package Polylang Updater
 *
 * @since 1.0
 *
 * @var array $atts {
 *     @type string[] $license_rows The license fields.
 *     @type bool     $is_error     Whether there is an error with a license key.
 * }
 */

defined( 'ABSPATH' ) || exit;

?>
<p>
	<?php esc_html_e( 'You are using plugins which require a license key.', 'polylang-pro' ); ?>
	<?php echo esc_html( _n( 'Please enter your license key:', 'Please enter your license keys:', count( $atts['license_rows'] ), 'polylang-pro' ) ); ?>
</p>
<h2><?php esc_html_e( 'Licenses', 'polylang-pro' ); ?></h2>
<div id="messages">
	<?php if ( $atts['is_error'] ) : ?>
		<p class="error"><?php esc_html_e( 'There is an error with a license key.', 'polylang-pro' ); ?></p>
	<?php endif; ?>
</div>
<div class="form-field">
	<table id="pllu-licenses-table" class="form-table pll-table-top">
		<tbody>
		<?php
		// Escaping is already done in get_form_field method.
		echo implode( "\n", $atts['license_rows'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		?>
		</tbody>
	</table>
</div>
