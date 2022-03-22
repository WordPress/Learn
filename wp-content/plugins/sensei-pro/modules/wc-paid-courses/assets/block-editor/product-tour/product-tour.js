/**
 * WordPress dependencies
 */
import { useDispatch, useSelect } from '@wordpress/data';
import { Guide } from '@wordpress/components';
import { __, sprintf } from '@wordpress/i18n';
import { useState, useEffect, useLayoutEffect } from '@wordpress/element';

const imagesPath = `${ window.senseiWcPaidCoursesBlockEditorData.assets_url }images`;
const userDisplayName =
	window.senseiWcPaidCoursesBlockEditorData.user_display_name;
const completedFeatureName = 'senseiCourseProductTourCompleted';

/**
 * A React Hook to observe if a modal is open based on the body class.
 *
 * @param {boolean} shouldObserve If it should observe the changes.
 *
 * @return {boolean|undefined} Whether a modal is open, or `undefined` if it's not initialized yet.
 */
const useObserveOpenModal = ( shouldObserve ) => {
	const [ hasOpenModal, setHasOpenModal ] = useState();

	useEffect( () => {
		if ( ! shouldObserve ) {
			return;
		}

		// Initialize state after modals are open or not.
		setTimeout( () => {
			setHasOpenModal( document.body.classList.contains( 'modal-open' ) );
		}, 1 );

		const observer = new MutationObserver( () => {
			setHasOpenModal( document.body.classList.contains( 'modal-open' ) );
		} );
		observer.observe( document.body, {
			attributes: true,
			attributeFilter: [ 'class' ],
		} );

		return () => {
			observer.disconnect();
		};
	}, [ shouldObserve ] );

	return hasOpenModal;
};

/**
 * A React Hook to control the onboarding open state.
 *
 * @return {boolean} Whether the onboarding is open.
 */
const useOnboardingOpen = () => {
	const { onboardingCompleted } = useSelect( ( select ) => ( {
		onboardingCompleted: select( 'core/edit-post' ).isFeatureActive(
			completedFeatureName
		),
	} ) );

	const hasOpenModal = useObserveOpenModal( ! onboardingCompleted );
	const [ isOnboardingOpen, setOnboardingOpen ] = useState( false );

	useLayoutEffect( () => {
		if ( onboardingCompleted ) {
			setOnboardingOpen( false );
		} else if ( false === hasOpenModal ) {
			// If no modal is open, it's time to open.
			setOnboardingOpen( true );
		}
	}, [ onboardingCompleted, hasOpenModal, isOnboardingOpen ] );

	return isOnboardingOpen;
};

/**
 * Products tour component.
 */
const ProductTour = () => {
	const { toggleFeature } = useDispatch( 'core/edit-post' );
	const isOnboardingOpen = useOnboardingOpen();

	if ( ! isOnboardingOpen ) {
		return null;
	}

	return (
		<Guide
			className="sensei-product-tour"
			contentLabel={ __( 'How to sell courses.', 'sensei-pro' ) }
			finishButtonText={ __( 'Done', 'sensei-pro' ) }
			onFinish={ () => {
				toggleFeature( 'senseiCourseProductTourCompleted' );
			} }
			pages={ [
				{
					image: (
						<div className="sensei-product-tour__image-container">
							<img
								className="sensei-product-tour__image"
								src={ `${ imagesPath }/product-tour-1.gif` }
								alt={ __(
									'Image showing the toolbar.',
									'sensei-pro'
								) }
							/>
						</div>
					),
					content: (
						<>
							<h1 className="sensei-product-tour__heading">
								{ sprintf(
									/* translators: User display name. */
									__(
										'Hi%s! Ready to sell your first course?',
										'sensei-pro'
									),
									userDisplayName ? ' ' + userDisplayName : ''
								) }
							</h1>
							<p className="sensei-product-tour__text">
								{ __(
									'Simply select “Paid” from the Take Course button toolbar, which will open an overlay.',
									'sensei-pro'
								) }
							</p>
						</>
					),
				},
				{
					image: (
						<div className="sensei-product-tour__image-container">
							<img
								className="sensei-product-tour__image"
								src={ `${ imagesPath }/product-tour-2.gif` }
								alt={ __(
									'Image showing how to select a product.',
									'sensei-pro'
								) }
							/>
						</div>
					),
					content: (
						<>
							<h1 className="sensei-product-tour__heading">
								{ __(
									'Next, select a product.',
									'sensei-pro'
								) }
							</h1>
							<p className="sensei-product-tour__text">
								{ __(
									'A product is used to sell the course at your desired price.',
									'sensei-pro'
								) }
							</p>
						</>
					),
				},
				{
					image: (
						<div className="sensei-product-tour__image-container">
							<img
								className="sensei-product-tour__image"
								src={ `${ imagesPath }/product-tour-3.gif` }
								alt={ __(
									'Image showing how it will look like in the frontend.',
									'sensei-pro'
								) }
							/>
						</div>
					),
					content: (
						<>
							<h1 className="sensei-product-tour__heading">
								{ __(
									'Nice work! You’re ready to start selling.',
									'sensei-pro'
								) }
							</h1>
							<p className="sensei-product-tour__text">
								{ __(
									'Learners will have to purchase one of the assigned products to access your course.',
									'sensei-pro'
								) }
							</p>
						</>
					),
				},
			] }
		/>
	);
};

export default ProductTour;
