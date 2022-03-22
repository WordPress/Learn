/**
 * WordPress dependencies.
 */
import { __ } from '@wordpress/i18n';

/**
 * Sensei Pro Setup data store name.
 */
export const DATA_STORE_NAME = 'sensei-pro/setup';

/**
 * Generic actions
 */
export const FETCH_FROM_API = 'FETCH_FROM_API';

/**
 * Rest api endpoint for the sensei-pro-setup
 */
export const REST_API_BASE_PATH = '/sensei-pro-internal/v1/sensei-pro-setup';

/**
 * License Activatoin
 */
export const ACTIVATE_LICENSE = 'ACTIVATE_LICENSE';
export const ACTIVATE_LICENSE_START = 'ACTIVATE_LICENSE_START';
export const ACTIVATE_LICENSE_FAIL = 'ACTIVATE_LICENSE_FAIL';
export const ACTIVATE_LICENSE_SUCCESS = 'ACTIVATE_LICENSE_SUCCESS';

/**
 * Sensei Core Installation
 */
export const INSTALL_SENSEI_CORE = 'INSTALL_SENSEI_CORE';
export const INSTALL_SENSEI_CORE_START = 'INSTALL_SENSEI_CORE_START';
export const INSTALL_SENSEI_CORE_FAIL = 'INSTALL_SENSEI_CORE_FAIL';
export const INSTALL_SENSEI_CORE_SUCCESS = 'INSTALL_SENSEI_CORE_SUCCESS';

/**
 * Unknown error placeholder.
 */
export const UNKNOWN_ERROR_MESSAGE = __(
	'Something went wrong. Please try again.',
	'sensei-pro'
);
