/**
 * Internal dependencies.
 */
import {
	FETCH_FROM_API,
	ACTIVATE_LICENSE_START,
	ACTIVATE_LICENSE_FAIL,
	ACTIVATE_LICENSE_SUCCESS,
	INSTALL_SENSEI_CORE_START,
	INSTALL_SENSEI_CORE_FAIL,
	INSTALL_SENSEI_CORE_SUCCESS,
} from './constants';

export const fetchFromApi = ( payload = {} ) => ( {
	type: FETCH_FROM_API,
	...payload,
} );

export function* activateLicense( payload = {} ) {
	yield activateLicenseStart( payload );
	const licenseKey = payload?.licenseKey || '';
	try {
		const res = yield fetchFromApi( {
			request: {
				path: '/activate-license',
				method: 'POST',
				data: { license_key: payload?.licenseKey || '' },
			},
		} );
		if ( res?.success === true ) {
			yield activateLicenseSuccess( { licenseKey } );
		} else {
			yield activateLicenseFail( { error: res.message } );
		}
	} catch ( err ) {
		yield activateLicenseFail();
	}
}

export const activateLicenseStart = ( payload = {} ) => ( {
	type: ACTIVATE_LICENSE_START,
	...payload,
} );

export const activateLicenseFail = ( payload = {} ) => ( {
	type: ACTIVATE_LICENSE_FAIL,
	...payload,
} );

export const activateLicenseSuccess = ( payload = {} ) => ( {
	type: ACTIVATE_LICENSE_SUCCESS,
	...payload,
} );

export function* installSenseiCore( payload = {} ) {
	yield installSenseiCoreStart( payload );
	try {
		const res = yield fetchFromApi( {
			request: {
				path: '/install-sensei',
				method: 'POST',
			},
		} );
		if ( res?.success === true ) {
			if ( res.activate_sensei_url ) {
				try {
					window.location = res.activate_sensei_url;
				} catch {
					yield installSenseiCoreSuccess( {
						activateUrl: res.activate_sensei_url,
					} );
				}
			} else {
				yield installSenseiCoreSuccess();
			}
		} else {
			yield installSenseiCoreFail( { error: res.message || '' } );
		}
	} catch ( err ) {
		yield installSenseiCoreFail();
	}
}

export const installSenseiCoreStart = ( payload = {} ) => ( {
	type: INSTALL_SENSEI_CORE_START,
	...payload,
} );

export const installSenseiCoreFail = ( payload = {} ) => ( {
	type: INSTALL_SENSEI_CORE_FAIL,
	...payload,
} );

export const installSenseiCoreSuccess = ( payload = {} ) => ( {
	type: INSTALL_SENSEI_CORE_SUCCESS,
	...payload,
} );
