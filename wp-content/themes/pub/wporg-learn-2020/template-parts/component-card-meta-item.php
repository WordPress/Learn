<?php
/**
 * List item for meta data displayed in the footer of a post card.
 */

/** @var array $args */
?>
<li class="card-meta-item">
	<span class="card-meta-item-icon dashicons dashicons-<?php echo esc_attr( $args['icon'] ); ?>"></span>
	<span class="card-meta-item-label"><?php echo esc_html( $args['label'] ); ?></span>
	<span class="card-meta-item-value"><?php echo esc_html( $args['value'] ); ?></span>
</li>
