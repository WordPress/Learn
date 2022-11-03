/**
 * External dependencies
 */
import classnames from 'classnames';

/**
 * WordPress dependencies
 */
import {
	useBlockProps,
	useInnerBlocksProps as stableUseInnerBlocksProps,
	// eslint-disable-next-line @wordpress/no-unsafe-wp-apis, import/no-unresolved
	__experimentalUseInnerBlocksProps,
} from '@wordpress/block-editor';
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import meta from './block.json';
import { Card, CardWrapper } from './elements';
import { Flip } from './flip';
import { useSyncHeight } from './use-sync-height';
import { ReactComponent as icon } from '../icons/flashcard-block.svg';
import { example } from './example';
import { CompletedStatus } from '../shared/supports-required/elements';

const useInnerBlocksProps =
	stableUseInnerBlocksProps ?? __experimentalUseInnerBlocksProps;
/**
 * Card Front/Card Back label
 *
 * @param {Object} props
 * @param {string} props.label
 * @param {string} props.side
 */
const SideLabel = ( { label, side } ) => (
	<span
		className={ classnames(
			Card.bem( '__label' ),
			Card.bem( `__label--${ side }` )
		) }
	>
		{ label }
	</span>
);

/**
 * Allowed content in the cover block.
 */
const allowedBlocks = [
	'core/paragraph',
	'core/heading',
	'core/quote',
	'core/pullquote',
	'core/code',
	'core/list',
	'core/image',
	'core/audio',
	'core/video',
	'core/embed',
];

/**
 * Flashcard block definition.
 */
export const CardBlock = {
	...meta,
	example,
	title: __( 'Flashcard', 'sensei-pro' ),
	icon,

	description: __(
		'Add a two-sided flashcard that can be flipped by the reader.',
		'sensei-pro'
	),
	keywords: [
		__( 'sensei', 'sensei-pro' ),
		__( 'flash card', 'sensei-pro' ),
		__( 'flip card', 'sensei-pro' ),
		__( 'flip box', 'sensei-pro' ),
	],
	edit: function EditCard( props ) {
		useSyncHeight( props );
		const blockProps = useBlockProps();
		const { children, ...innerBlocks } = useInnerBlocksProps(
			{},
			{
				template: [
					CoverBlock( {
						placeholder: __(
							'Add flash card question',
							'sensei-pro'
						),
					} ),
					CoverBlock( {
						placeholder: __(
							'Add flash card answer',
							'sensei-pro'
						),
					} ),
				],
				templateLock: 'insert',
			}
		);

		return (
			<CardWrapper { ...blockProps }>
				{ props.attributes.required && (
					<CompletedStatus
						className="sensei-lms-flashcards__completed-status"
						completed={ false }
						showTooltip={ false }
					/>
				) }
				<Flip { ...innerBlocks }>{ children }</Flip>
				<SideLabel
					side="front"
					label={ __( 'Card Front', 'sensei-pro' ) }
				/>
				<SideLabel
					side="back"
					label={ __( 'Card Back', 'sensei-pro' ) }
				/>
			</CardWrapper>
		);
	},
	save: ( { children, blockProps } ) => {
		return (
			<CardWrapper { ...blockProps }>
				<Flip.Save>{ children }</Flip.Save>
			</CardWrapper>
		);
	},
};

/**
 * Cover block template.
 *
 * @param {Object} props
 * @param {string} props.placeholder
 */
const CoverBlock = ( { placeholder } ) => {
	return [
		'core/cover',
		{
			customOverlayColor: '#ffffff',
			templateLock: false,
			allowedBlocks,
		},
		[
			[
				'core/paragraph',
				{
					align: 'center',
					placeholder,
					fontSize: 'large',
					style: { color: { text: '#000000' } },
				},
			],
		],
	];
};
