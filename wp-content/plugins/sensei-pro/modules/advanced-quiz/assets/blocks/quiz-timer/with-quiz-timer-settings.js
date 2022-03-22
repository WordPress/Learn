import { InspectorControls } from '@wordpress/block-editor';
import {
	PanelBody,
	PanelRow,
	ToggleControl,
	Fill,
} from '@wordpress/components';
import { useCallback, useState, useEffect } from '@wordpress/element';
import { useSelect, useDispatch } from '@wordpress/data';
import NumberControl from '../../sensei/number-control';
import { __ } from '@wordpress/i18n';

/**
 * This Wrapper checks if the component was rendered and updates the parent state. If it wasn't rendered,
 * the parent can then display a fallback in the settings panel for quiz timer settings.
 * Note: this is only necessary while minimum Sensei version is 4.0.0. Once we bump it to > 4.0.1,
 * we can remove this completely. The aim is to make quiz timer settings available for Sensei 4.0.0 users.
 **/
const FillWrapper = ( { children, fillRendered, setFillRendered } ) => {
	useEffect( () => {
		if ( ! fillRendered ) {
			setFillRendered( true );
		}
	} );

	return children;
};

export default ( BlockEdit ) => ( props ) => {
	const [ fillRendered, setFillRendered ] = useState( false );

	const quizBlock = useSelect( ( select ) => {
		const quizBlockId = select( 'sensei/quiz-structure' ).getBlock();
		return select( 'core/block-editor' ).getBlock( quizBlockId );
	}, [] );

	const { updateBlockAttributes } = useDispatch( 'core/block-editor' );

	const updateTimerValue = useCallback(
		( newValue ) => {
			updateBlockAttributes( quizBlock.clientId, {
				...quizBlock.attributes,
				options: {
					...quizBlock.attributes.options,
					timerValue: newValue ? newValue * 60 : null,
				},
			} );
		},
		[ updateBlockAttributes, quizBlock ]
	);
	const updateEnableQuizTimer = useCallback(
		( newValue ) => {
			updateBlockAttributes( quizBlock.clientId, {
				...quizBlock.attributes,
				options: {
					...quizBlock.attributes.options,
					enableQuizTimer: newValue,
				},
			} );
		},
		[ updateBlockAttributes, quizBlock ]
	);

	const enableQuizTimer =
		quizBlock?.attributes?.options?.enableQuizTimer || false;
	const timerValue = quizBlock?.attributes?.options?.timerValue || null;

	/**
	 * This Component is to be displayed as a `Fill` for the named `Slot`: <Slot name="SenseiQuizSettings"></Slot>
	 * from Sensei Core. However to add backward compatibility with Sensei Core older than 4.0.2,
	 * we abstract this component and add a fallback to still display the quiz timer settings. Here, FillWrapper
	 * helps us determine whether the slot has been filled, and if not display the fallback.
	 **/
	const quizPanelControls = (
		<>
			<PanelRow>
				<ToggleControl
					checked={ enableQuizTimer }
					onChange={ updateEnableQuizTimer }
					label={ __( 'Quiz Timer', 'sensei-pro' ) }
				/>
			</PanelRow>
			{ enableQuizTimer && (
				<PanelRow>
					<NumberControl
						className="sensei-lms-quiz-timer__input"
						min="1"
						value={
							timerValue
								? Math.floor( timerValue / 60 )
								: timerValue
						}
						onChange={ updateTimerValue }
						suffix={ __( 'minutes', 'sensei-pro' ) }
						allowReset={ true }
						resetLabel={ __( 'Clear', 'sensei-pro' ) }
						help={ __(
							'Specify how much time a student will have to complete the quiz.',
							'sensei-pro'
						) }
					/>
				</PanelRow>
			) }
		</>
	);

	return (
		<>
			<BlockEdit { ...props } />
			<InspectorControls>
				<Fill name="SenseiQuizSettings">
					<FillWrapper
						fillRendered={ fillRendered }
						setFillRendered={ setFillRendered }
					>
						{ quizPanelControls }
					</FillWrapper>
				</Fill>
				{ ! fillRendered && (
					<PanelBody
						title={ __( 'Quiz Timer Settings', 'sensei-pro' ) }
					>
						{ quizPanelControls }
					</PanelBody>
				) }
			</InspectorControls>
		</>
	);
};
