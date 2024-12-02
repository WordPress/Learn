<?php
/**
 * Outputs the bulk translate option part in the bulk translate form.
 *
 * @package Polylang-Pro
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Don't access directly
}
	$option_name = $this->get_name();
?>
	<label>
		<span class="option"><input name="translate" type="radio" value="<?php echo esc_attr( $option_name ); ?>"<?php checked( $option_name, $selected ); ?>/></span>
		<span class="description"><?php echo esc_html( $bulk_translate_option->get_description() ); ?></span>
	</label>
