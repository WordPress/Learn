/**
 * Retrieves the license activation state root.
 *
 * @param {Object} state The state
 * @return {Object} The license activation state root.
 */
export const getLicenseActivate = ( state ) => state.licenseActivate;

/**
 * Tells if the license is activated or not.
 *
 * @param {Object} state The state
 * @return {boolean} Is the license is activated or not.
 */
export const isLicenseActivated = ( state ) => state.licenseActivate.activated;

/**
 * Retrieves the sensei installation state root.
 *
 * @param {Object} state The state
 * @return {Object} The sensei installation state root.
 */
export const getSenseiInstall = ( state ) => state.senseiInstall;
