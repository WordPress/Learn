import { registerBlockVariation } from '@wordpress/blocks';
import { addFilter } from '@wordpress/hooks';
import { InspectorControls } from '@wordpress/block-editor';
import { CheckboxControl, PanelBody } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

const VARIATION_NAME = 'wporg-learn/course-grid';

registerBlockVariation( 'core/query', {
	name: VARIATION_NAME,
	title: __( 'Learn Course Grid', 'wporg-learn' ),
	icon: {
		src: (
			<svg width="24" height="24" fill="none" xmlns="http://www.w3.org/2000/svg" size="24">
				<path
					fillRule="evenodd"
					clipRule="evenodd"
					d="M9 9.5H6a.5.5 0 0 1-.5-.5V6a.5.5 0 0 1 .5-.5h3a.5.5 0 0 1 .5.5v3a.5.5 0 0 1-.5.5ZM6 11h3a2 2 0 0 0 2-2V6a2 2 0 0 0-2-2H6a2 2 0 0 0-2 2v3a2 2 0 0 0 2 2Zm12-1.5h-3a.5.5 0 0 1-.5-.5V6a.5.5 0 0 1 .5-.5h3a.5.5 0 0 1 .5.5v3a.5.5 0 0 1-.5.5ZM15 11h3a2 2 0 0 0 2-2V6a2 2 0 0 0-2-2h-3a2 2 0 0 0-2 2v3a2 2 0 0 0 2 2ZM4 14.5h7V16H4v-1.5Zm16 0h-7V16h7v-1.5Zm-16 4h5V20H4v-1.5Zm14 0h-5V20h5v-1.5Z"
					fill="#1E1E1E"
				></path>
			</svg>
		),
	},
	description: __( 'Displays a cards grid of courses.', 'wporg-learn' ),
	attributes: {
		className: 'wporg-learn-course-grid',
		namespace: VARIATION_NAME,
		query: {
			perPage: 6,
			postType: 'course',
			courseFeatured: false,
		},
		align: 'wide',
	},
	isActive: ( { namespace, query } ) => namespace === VARIATION_NAME && query.postType === 'course',
	innerBlocks: [
		[
			'core/post-template',
			{
				style: { spacing: { blockGap: 'var:preset|spacing|50' } },
				layout: { type: 'grid', columnCount: null, minimumColumnWidth: '330px' },
			},
			[
				[
					'core/group',
					{
						style: {
							border: { width: '1px', color: 'var:preset|color|light-grey-1', radius: '2px' },
							spacing: { blockGap: '0' },
							dimensions: { minHeight: '100%' },
						},
						backgroundColor: 'white',
						layout: { type: 'flex', orientation: 'vertical' },
					},
					[
						[ 'core/post-featured-image', { style: { spacing: { margin: { bottom: '0' } } } } ],
						[
							'core/group',
							{
								style: {
									spacing: {
										padding: {
											top: 'var:preset|spacing|20',
											bottom: 'var:preset|spacing|20',
											left: '20px',
											right: '20px',
										},
									},
								},
								layout: { type: 'constrained' },
							},
							[
								[
									'core/post-title',
									{
										level: 3,
										isLink: true,
										style: {
											typography: {
												fontStyle: 'normal',
												fontWeight: '600',
												lineHeight: 1.6,
											},
											spacing: { margin: { bottom: '0' } },
											elements: {
												link: { color: { text: 'var:preset|color|blueberry-1' } },
											},
										},
										fontSize: 'normal',
										fontFamily: 'inter',
									},
								],
								[
									'core/post-excerpt',
									{
										showMoreOnNewLine: false,
										excerptLength: 16,
										style: {
											spacing: { margin: { top: 'var:preset|spacing|10' } },
											typography: { lineHeight: 1.6 },
										},
									},
								],
								[
									'core/group',
									{ layout: { type: 'flex', flexWrap: 'wrap', justifyContent: 'left' } },
									[
										[
											'wporg-learn/learning-duration',
											{
												style: {
													elements: {
														link: { color: { text: 'var:preset|color|charcoal-4' } },
													},
												},
												textColor: 'charcoal-4',
												fontSize: 'small',
											},
										],
										[
											'wporg-learn/lesson-count',
											{
												style: { layout: { selfStretch: 'fill', flexSize: null } },
												fontSize: 'extra-small',
											},
										],
										[ 'wporg-learn/course-status', { fontSize: 'extra-small' } ],
									],
								],
							],
						],
					],
				],
			],
		],
		[ 'core/query-no-results' ],
	],
} );

const isCourseGridVariation = ( { attributes: { namespace } } ) => namespace && namespace === VARIATION_NAME;

const CourseGridControls = ( {
	props: {
		attributes: { query },
		setAttributes,
	},
} ) => (
	<PanelBody title={ __( 'Featured', 'wporg-learn' ) }>
		<CheckboxControl
			label={ __( 'Featured only', 'wporg-learn' ) }
			checked={ query.courseFeatured || false }
			onChange={ ( checked ) => {
				setAttributes( {
					query: {
						...query,
						courseFeatured: checked,
					},
				} );
			} }
		/>
	</PanelBody>
);

export const withCourseGridControls = ( BlockEdit ) => ( props ) => {
	return isCourseGridVariation( props ) ? (
		<>
			<BlockEdit { ...props } />
			<InspectorControls>
				<CourseGridControls props={ props } />
			</InspectorControls>
		</>
	) : (
		<BlockEdit { ...props } />
	);
};

addFilter( 'editor.BlockEdit', 'core/query', withCourseGridControls );
