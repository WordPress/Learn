/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { compose } from '@wordpress/compose';
import { addFilter } from '@wordpress/hooks';
import {
	useBlockProps,
	InnerBlocks,
	InspectorControls,
} from '@wordpress/block-editor';
import { createContext, useContext, useEffect } from '@wordpress/element';
import {
	PanelBody,
	TextareaControl,
	ExternalLink,
} from '@wordpress/components';

export const TutorAIContext = createContext( {} );

const TEMPLATE = [
	[
		'core/heading',
		{
			slot: 'title',
			placeholder: __( 'Question title', 'sensei-pro' ),
			level: 4,
			style: {
				spacing: {
					padding: {
						top: '0px',
						bottom: '20px',
						left: '0px',
						right: '0px',
					},
				},
			},
		},
	],
	[
		'sensei-pro/ai-answer',
		{
			slot: 'answer',
			style: {
				border: {
					radius: '30px',
				},
				spacing: {
					padding: {
						top: '14px',
						bottom: '14px',
						left: '14px',
						right: '14px',
					},
					margin: {
						top: '0px',
					},
				},
			},
			backgroundColor: 'primary',
			textColor: 'background',
		},
	],
	[
		'sensei-pro/ai-student-response',
		{
			slot: 'student-answer',
			style: {
				border: {
					radius: '30px',
					width: '1px',
					color: 'primary',
				},
				spacing: {
					padding: {
						top: '14px',
						bottom: '14px',
						left: '14px',
						right: '14px',
					},
					margin: {
						top: '20px',
					},
				},
			},
		},
	],
];

const ALLOWED_BLOCKS = [
	'core/heading',
	'sensei-pro/ai-answer',
	'sensei-pro/ai-student-response',
];

const TutorAiEdit = ( props ) => {
	const {
		attributes: { question, correctAnswer, reason },
		setAttributes,
	} = props;

	const blockProps = useBlockProps();

	return (
		<div { ...blockProps }>
			<InspectorControls>
				<PanelBody
					title={ __( 'Tutor AI Documentation', 'sensei-pro' ) }
				>
					<ExternalLink href="https://senseilms.com/sensei-pro/?utm_source=plugin_sensei&utm_medium=upsell&utm_campaign=tutor-ai">
						{ __( 'Read how the Tutor AI works', 'sensei-pro' ) }
					</ExternalLink>
				</PanelBody>
				<PanelBody
					title={ __( 'Give Tutor AI better context', 'sensei-pro' ) }
					initialOpen={ true }
				>
					<p>
						{ __(
							'To help Tutor AI better understand how to guide your students to the correct answer, please provide here a short explanation of why the answer above is the correct one.',
							'sensei-pro'
						) }
					</p>
					<TextareaControl
						label={ __( 'Why is your answer correct?' ) }
						value={ reason }
						onChange={ ( newValue ) =>
							setAttributes( {
								reason: newValue,
							} )
						}
						className="sensei-lms-interactive-block-tutor-ai__reason"
						maxLength={ 200 }
					/>
					<p>Characters: { reason.length }/200</p>
				</PanelBody>
			</InspectorControls>
			<TutorAIContext.Provider
				value={ {
					question,
					correctAnswer,
					reason,
					setAttributes,
				} }
			>
				<InnerBlocks
					template={ TEMPLATE }
					templateLock={ true }
					insertBlocksAfter={ false }
					allowedBlocks={ ALLOWED_BLOCKS }
				></InnerBlocks>
			</TutorAIContext.Provider>
		</div>
	);
};

addFilter(
	'blocks.registerBlockType',
	'sensei-pro/tutor-ai',
	addWrapperAroundHeaderBlockToPassQuestionToParent
);

const withWrapperComponent = ( BlockEdit ) => ( props ) => {
	const { setAttributes, question } = useContext( TutorAIContext );

	useEffect( () => {
		if ( setAttributes ) {
			if (
				props.attributes.content &&
				props.attributes.content.length > 100
			) {
				props.setAttributes( { content: question } );
			} else {
				setAttributes( { question: props.attributes.content } );
			}
		}
	}, [ props.attributes.content ] );

	return <BlockEdit { ...props } />;
};

/**
 * Pass question text to parent property.
 *
 * @param {Object} settings Block settings.
 * @param {string} name     Block name.
 */
export function addWrapperAroundHeaderBlockToPassQuestionToParent(
	settings,
	name
) {
	if ( 'core/heading' !== name ) {
		return settings;
	}

	settings = {
		...settings,
		edit: compose( withWrapperComponent )( settings.edit ),
	};
	return settings;
}

export default TutorAiEdit;
