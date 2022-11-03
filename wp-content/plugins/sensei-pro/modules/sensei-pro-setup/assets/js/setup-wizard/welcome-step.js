/**
 * WordPress dependencies
 */
import { addFilter } from '@wordpress/hooks';
import { __ } from '@wordpress/i18n';

/**
 * Returns the new title for the Setup Wizard when Sensei Pro is installed.
 *
 * @return {string} The new title for the Welcome Step of the Setup Wizard
 */
function changeSetupProSetupWizardTitle() {
	return __( 'Welcome to Sensei Pro', 'sensei-pro' );
}

/**
 * Returns the new paragraph for the Setup Wizard when Sensei Pro is installed.
 *
 * @return {string} The new paragraph for the Welcome Step of the Setup Wizard
 */
function changeSetupProSetupWizardParagraph() {
	return __(
		"Sensei Pro unlocks all the features you need to create and run a professional course. Let's setup your first Sensei Pro course.",
		'sensei-pro'
	);
}

addFilter(
	'sensei.setupWizard.welcomeTitle',
	'sensei-pro/setup-wizard/welcome-title',
	changeSetupProSetupWizardTitle
);

addFilter(
	'sensei.setupWizard.welcomeParagraph',
	'sensei-pro/setup-wizard/welcome-paragraph',
	changeSetupProSetupWizardParagraph
);
