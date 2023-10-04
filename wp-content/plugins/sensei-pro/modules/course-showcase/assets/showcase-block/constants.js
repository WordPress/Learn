/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';

/**
 * Variables added through PHP.
 */

const { showcaseEditor } = window.sensei;

export const CATEGORY_OPTIONS = [
	{
		label: __( 'Select a category', 'sensei-pro' ),
		value: '',
		disabled: true,
	},
	...showcaseEditor.categoryOptions,
];

export const LANGUAGE_OPTIONS = showcaseEditor.languageOptions;

export const SITE_LANGUAGE = showcaseEditor.siteLanguage;

export const STUDENTS_NUMBER = showcaseEditor.studentsNumber;
