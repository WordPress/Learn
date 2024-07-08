<?php
/**
 * Blocks hooks.
 *
 * @package wporg-learn-2024
 */

use function DevHub\is_parsed_post_type;

add_filter( 'render_block_data', __NAMESPACE__ . '\modify_header_template_part' );

/**
 * Update header template based on current query.
 *
 * @param array $parsed_block The block being rendered.
 *
 * @return array The updated block.
 */
function modify_header_template_part( $parsed_block ) {
	if (
		'core/template-part' === $parsed_block['blockName'] &&
		! empty( $parsed_block['attrs']['slug'] ) &&
		str_starts_with( $parsed_block['attrs']['slug'], 'header' ) &&
		is_search()
	) {
		$parsed_block['attrs']['slug'] = 'header-second-archive-title';
	}
	return $parsed_block;
}
