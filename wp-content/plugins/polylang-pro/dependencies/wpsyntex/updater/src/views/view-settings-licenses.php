<?php
/**
 * Displays the licenses table.
 *
 * @package Polylang Updater
 *
 * @since 1.0
 *
 * @var array $atts {
 *     @type string $action AJAX action.
 *     @type string $nonce  AJAX nonce.
 * }
 */

defined( 'ABSPATH' ) || exit;

?>
<table id="pllu-licenses-table" class="form-table pll-table-top" data-action="<?php echo esc_attr( $atts['action'] ); ?>" data-nonce="<?php echo esc_attr( $atts['nonce'] ); ?>"></table>
