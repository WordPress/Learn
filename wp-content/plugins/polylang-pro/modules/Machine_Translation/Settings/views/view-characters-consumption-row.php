<?php
/**
 * @package Polylang-Pro
 *
 * @param string $ajax_action
 * @param string $slug
 */

defined( 'ABSPATH' ) || exit;

?>
<tr id="pll-<?php echo esc_attr( $atts['slug'] ); ?>-data-consumption">
	<td><?php esc_html_e( 'Data used', 'polylang-pro' ); ?></td>
	<td>
		<?php
		require __DIR__ . '/view-progress-bar.php';
		?>
		<p class="description">&nbsp;</p>
	</td>
</tr>
