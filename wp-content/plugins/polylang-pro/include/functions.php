<?php
/**
 * @package Polylang
 */

/**
 * Tells if the given REST request is in the edit context.
 *
 * @since 3.5
 *
 * @param WP_REST_Request $request A REST request.
 * @return bool
 */
function pll_is_edit_rest_request( WP_REST_Request $request ): bool {
	if ( in_array( $request->get_method(), array( 'PATCH', 'POST', 'PUT' ), true ) ) {
		return true;
	}

	return 'GET' === $request->get_method() && 'edit' === $request->get_param( 'context' );
}
