<?php

namespace WPOrg_Learn\Utils;

/**
 * Ensures that the given value is a float.
 *
 * @param mixed $value The value to ensure as a float.
 * @return float|string The float value if conversion is successful, otherwise an empty string.
 */
function ensure_float( $value ) {
	// Check if the value is already a float
	if ( is_float( $value ) ) {
		return $value;
	}

	// Check if the value is numeric and can be converted to a float
	if ( is_numeric( $value ) ) {
		return floatval( $value );
	}

	return '';
}
