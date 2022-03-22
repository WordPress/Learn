/**
 * WordPress dependencies.
 */
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import {
	ACTIVATE_LICENSE_START,
	ACTIVATE_LICENSE_FAIL,
	ACTIVATE_LICENSE_SUCCESS,
	INSTALL_SENSEI_CORE_START,
	INSTALL_SENSEI_CORE_FAIL,
	INSTALL_SENSEI_CORE_SUCCESS,
	UNKNOWN_ERROR_MESSAGE,
} from './constants';

const LICENSE_ACTIVATION_FAILED = __(
	'License activation failed. Please try again.',
	'sensei-pro'
);

const suppliedState = window.senseiProSetup || {};
export const initialState = {
	licenseActivate: {
		activated: suppliedState.licenseActivated || false,
		licenseKey: suppliedState.licenseKey || '',
		licenseDomain: suppliedState.licenseDomain || '',
		inProgress: false,
		error: '',
	},
	senseiInstall: {
		installed: suppliedState.senseiInstalled || false,
		activated: suppliedState.senseiActivated || false,
		activateUrl: suppliedState.senseiActivateUrl || '',
		inProgress: false,
		error: '',
	},
};

const handlers = {
	[ ACTIVATE_LICENSE_START ]: ( state ) => {
		return {
			...state,
			licenseActivate: {
				...state.licenseActivate,
				inProgress: true,
				error: '',
			},
		};
	},
	[ ACTIVATE_LICENSE_FAIL ]: ( state, { error } ) => {
		return {
			...state,
			licenseActivate: {
				...state.licenseActivate,
				inProgress: false,
				error: error || LICENSE_ACTIVATION_FAILED,
			},
		};
	},
	[ ACTIVATE_LICENSE_SUCCESS ]: ( state, { licenseKey } ) => {
		return {
			...state,
			licenseActivate: {
				...state.licenseActivate,
				inProgress: false,
				activated: true,
				licenseKey: licenseKey || '',
			},
		};
	},
	[ INSTALL_SENSEI_CORE_START ]: ( state ) => {
		return {
			...state,
			senseiInstall: {
				...state.senseiInstall,
				inProgress: true,
				error: '',
			},
		};
	},
	[ INSTALL_SENSEI_CORE_FAIL ]: (
		state,
		{ error = '', installed = false }
	) => {
		return {
			...state,
			senseiInstall: {
				...state.senseiInstall,
				inProgress: false,
				error: error || UNKNOWN_ERROR_MESSAGE,
				installed,
			},
		};
	},
	[ INSTALL_SENSEI_CORE_SUCCESS ]: ( state, { activateUrl } ) => {
		return {
			...state,
			senseiInstall: {
				...state.senseiInstall,
				inProgress: false,
				installed: true,
				activateUrl: activateUrl || '',
			},
		};
	},
};

export default ( state = initialState, action ) => {
	if ( typeof handlers[ action.type ] === 'function' ) {
		return handlers[ action.type ]( state, action );
	}
	return state;
};
