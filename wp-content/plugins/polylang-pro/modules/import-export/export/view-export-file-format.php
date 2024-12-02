<?php
/**
 * Outputs the export file format dropdown in the bulk translate form.
 *
 * @package Polylang-Pro
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Don't access directly.
}

$selected = array_key_first( $supported_formats );
?>
<label for="pll-select-format">
	<span><?php esc_html_e( 'File format', 'polylang-pro' ); ?></span>
	<select name="filetype" id="pll-select-format">
		<?php
		foreach ( $supported_formats as $key => $format ) {
			?>
			<option value="<?php echo esc_attr( $key ); ?>"<?php selected( $key, $selected ); ?>>
				<?php echo esc_html( PLL_File_Format_Factory::get_format_label( $key ) ); ?>
			</option>
			<?php
		}
		?>
	</select>
</label>
