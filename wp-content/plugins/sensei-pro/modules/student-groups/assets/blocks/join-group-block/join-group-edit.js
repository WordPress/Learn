/**
 * WordPress dependencies
 */
import { useBlockProps, InnerBlocks } from '@wordpress/block-editor';

/**
 * Internal dependencies
 */
import buttonAttributes from '../join-group-button-variation/attributes';

const TEMPLATE = [
	[
		'core/columns',
		{
			className: 'wp-block-sensei-pro-join-group__columns',
			style: {
				spacing: {
					margin: {
						bottom: '0',
					},
				},
			},
		},
		[
			[
				'core/column',
				{ width: '70%' },
				[
					[
						'sensei-pro/group-name',
						{
							style: {
								spacing: {
									padding: {
										top: '0',
										bottom: '0',
									},
									margin: {
										bottom: '10px',
									},
								},
							},
						},
					],
					[
						'sensei-pro/group-members-count',
						{
							style: {
								spacing: {
									margin: {
										top: '10px',
									},
								},
							},
						},
					],
					[
						'sensei-pro/group-members-list',
						{
							style: {
								spacing: {
									margin: {
										top: '18px',
									},
								},
							},
						},
					],
				],
			],
			[
				'core/column',
				{ verticalAlignment: 'bottom' },
				[
					[
						'core/buttons',
						{ layout: { type: 'flex', justifyContent: 'right' } },
						[ [ 'core/button', buttonAttributes ] ],
					],
				],
			],
		],
	],
];

const Edit = () => {
	const blockProps = useBlockProps();

	return (
		<div { ...blockProps }>
			<InnerBlocks template={ TEMPLATE } />
		</div>
	);
};

export default Edit;
