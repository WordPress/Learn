/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { createInterpolateElement } from '@wordpress/element';
import { ExternalLink } from '@wordpress/components';
import { store as editPostStore } from '@wordpress/edit-post';
import { select, dispatch } from '@wordpress/data';

/**
 * External dependencies
 */
import { TourStep } from 'sensei/assets/admin/tour/types';
import {
	performStepActionsAsync,
	highlightElementsWithBorders,
} from 'sensei/assets/admin/tour/helper';

export const beforeEach = () => {
	// Run the clean up.
	performStepActionsAsync( [] );

	// Close sidebar if's a mobile viewport.
	const viewportWidth =
		window.innerWidth || document.documentElement.clientWidth;

	if ( viewportWidth < 782 ) {
		const { closeGeneralSidebar } = dispatch( editPostStore );
		closeGeneralSidebar();
	}
};

function getSidebarOpeningStepsIfRequired() {
	const openSidebarName = select(
		editPostStore
	).getActiveGeneralSidebarName();

	if (
		'sensei-lms-course-settings-sidebar/sensei-lms-course-settings-sidebar' ===
		openSidebarName
	) {
		return [];
	}

	return [
		( () => {
			if ( 'edit-post/document' !== openSidebarName ) {
				return {
					action: () => {
						const { openGeneralSidebar } = dispatch(
							editPostStore
						);

						openGeneralSidebar( 'edit-post/document' );
					},
				};
			}
		} )(),
		{
			action: () => {
				const sidebarCourseSettingsSectionSelector =
					'.sensei-plugin-document-setting-panel';
				highlightElementsWithBorders(
					[ sidebarCourseSettingsSectionSelector ],
					'inset'
				);
			},
			delay: 400,
		},
		{
			action: () => {
				const { openGeneralSidebar } = dispatch( editPostStore );

				openGeneralSidebar(
					'sensei-lms-course-settings-sidebar/sensei-lms-course-settings-sidebar'
				);
			},
			delay: 500,
		},
		{
			action: () => {},
			delay: 400,
		},
	];
}

/**
 * Returns the tour steps for the Course Outline block.
 *
 * @return {Array.<TourStep>} An array containing the tour steps.
 */
export default function getTourSteps() {
	return [
		{
			slug: 'welcome',
			meta: {
				heading: __( 'Selling courses with WooCommerce', 'sensei-pro' ),
				descriptions: {
					desktop: __(
						'Take this short tour to learn the fundamentals of selling courses with WooCommerce.',
						'sensei-pro'
					),
				},
			},
		},
		{
			slug: 'selling-a-course',
			meta: {
				heading: __( 'Selling a course', 'sensei-pro' ),
				descriptions: {
					desktop: __(
						'Click a product to use to sell this course. Click it again to remove it. You can select as many products as you like.',
						'sensei-pro'
					),
					mobile: null,
				},
				referenceElements: {
					desktop: '',
				},
			},
			options: {
				classNames: {
					desktop: '',
					mobile: '',
				},
			},
			referenceElements: {
				desktop: '',
			},

			action: () => {
				performStepActionsAsync( [
					...getSidebarOpeningStepsIfRequired(),
					{
						action: () => {
							const pricingSectionSelector =
								'.sensei-wcpc-course-pricing';

							highlightElementsWithBorders(
								[ pricingSectionSelector ],
								'inset'
							);
						},
					},
				] );
			},
		},
		{
			slug: 'adding-a-product',
			meta: {
				heading: __( 'Adding a product', 'sensei-pro' ),
				descriptions: {
					desktop: __(
						'Click the Create a new product link to add a product. After entering the details, click the Create product button.',
						'sensei-pro'
					),
					mobile: null,
				},
				referenceElements: {
					desktop: '',
				},
			},
			options: {
				classNames: {
					desktop: '',
					mobile: '',
				},
			},
			referenceElements: {
				desktop: '',
			},
			action: () => {
				performStepActionsAsync( [
					...getSidebarOpeningStepsIfRequired(),
					{
						action: () => {
							const pricingSectionSelector =
								'.sensei-wcpc-course-pricing';

							highlightElementsWithBorders(
								[ pricingSectionSelector ],
								'inset'
							);
						},
					},
					{
						action: () => {
							const sidebarCreateNewProductButtonSelector =
								'.sensei-wcpc-new-product__create-new-product-button';

							highlightElementsWithBorders( [
								sidebarCreateNewProductButtonSelector,
							] );
						},
						delay: 300,
					},
					{
						action: () => {
							const sidebarCreateNewProductButtonSelector =
								'.sensei-wcpc-new-product__create-new-product-button';

							const sidebarCreateNewProductButton = document.querySelector(
								sidebarCreateNewProductButtonSelector
							);

							if ( sidebarCreateNewProductButton ) {
								sidebarCreateNewProductButton.click();
							}
						},
						delay: 400,
					},
					{
						action: () => {
							const createProductFormSelector =
								'.sensei-wcpc-course-pricing form';

							highlightElementsWithBorders( [
								createProductFormSelector,
							] );
						},
						delay: 400,
					},
				] );
			},
		},
		{
			slug: 'purchasing-a-course',
			meta: {
				heading: __( 'Purchasing a course', 'sensei-pro' ),
				descriptions: {
					desktop: __(
						'Use the Course Signup block to enable people to purchase your course.',
						'sensei-pro'
					),
					mobile: null,
				},
				referenceElements: {
					desktop: '',
				},
			},
			options: {
				classNames: {
					desktop: '',
					mobile: '',
				},
			},
			referenceElements: {
				desktop: '',
			},
			action: () => {
				const takeCourseButtonSelector =
					'.wp-block-sensei-lms-button-take-course .wp-block-button__link';
				const takeCourseButton = document.querySelector(
					takeCourseButtonSelector
				);

				if ( takeCourseButton ) {
					performStepActionsAsync( [
						{
							action: () => {
								highlightElementsWithBorders( [
									takeCourseButtonSelector,
								] );
							},
						},
						{
							action: () => {
								takeCourseButton.scrollIntoView( {
									behavior: 'smooth',
									block: 'center',
								} );
							},
						},
					] );
					return;
				}

				performStepActionsAsync( [
					{
						action: () => {
							const { setIsInserterOpened } = dispatch(
								editPostStore
							);

							setIsInserterOpened( true );
						},
					},
					{
						action: () => {
							const courseSignupBlockSelector =
								'.editor-block-list-item-sensei-lms-button-take-course';

							highlightElementsWithBorders(
								[ courseSignupBlockSelector ],
								'inset'
							);

							const courseSignupBlock = document.querySelector(
								courseSignupBlockSelector
							);

							if ( courseSignupBlock ) {
								courseSignupBlock.scrollIntoView( {
									behavior: 'smooth',
									block: 'center',
								} );
							}
						},
						delay: 400,
					},
				] );
			},
		},
		{
			slug: 'congratulations',
			meta: {
				heading: __( 'Congratulations!', 'sensei-pro' ),
				descriptions: {
					desktop: createInterpolateElement(
						__(
							"You've mastered the basics. View the <link_to_doc>docs</link_to_doc> to learn more about selling courses. Restart tour",
							'sensei-pro'
						),
						{
							link_to_doc: (
								<ExternalLink
									href="https://senseilms.com/documentation/getting-started-with-woocommerce-paid-courses/#link"
									children={ null }
								/>
							),
						}
					),
					mobile: null,
				},
				referenceElements: {
					desktop: '',
				},
			},
			options: {
				classNames: {
					desktop: '',
					mobile: '',
				},
			},
			referenceElements: {
				desktop: '',
			},
		},
	];
}
