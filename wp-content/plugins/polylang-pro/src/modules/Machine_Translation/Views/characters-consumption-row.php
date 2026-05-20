<?php
/**
 * @package Polylang-Pro
 *
 * @var array $atts {
 *   @type string $ajax_action Name of the Ajax action to pass to the progress bar view to update it.
 *   @type string $slug        Slug of the machine translation service.
 * }
 */

defined( 'ABSPATH' ) || exit;

?>
<tr id="pll-<?php echo esc_attr( $atts['slug'] ); ?>-data-consumption">
	<td><?php esc_html_e( 'Data used', 'polylang-pro' ); ?></td>
	<td>
		<?php
		require __DIR__ . '/progress-bar.php';
		?>
		<p class="description">&nbsp;</p>
	</td>
</tr>
