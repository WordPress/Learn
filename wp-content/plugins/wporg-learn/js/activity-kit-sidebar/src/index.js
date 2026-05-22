import { registerPlugin } from '@wordpress/plugins';
import { PluginDocumentSettingPanel } from '@wordpress/edit-post';
import { useSelect } from '@wordpress/data';
import { useEntityProp, store as coreStore } from '@wordpress/core-data';
import { TextControl, BaseControl, Button } from '@wordpress/components';
import { MediaUpload, MediaUploadCheck } from '@wordpress/block-editor';
import { __ } from '@wordpress/i18n';

function ActivityKitDetailsPanel() {
	const postType = useSelect(
		( select ) => select( coreStore ).getCurrentPostType(),
		[]
	);

	const [ duration, setDuration ] = useEntityProp(
		'postType',
		'activity_kit',
		'_activity_duration'
	);
	const [ guidePdfId, setGuidePdfId ] = useEntityProp(
		'postType',
		'activity_kit',
		'_activity_guide_pdf_id'
	);
	const [ slidesPdfId, setSlidesPdfId ] = useEntityProp(
		'postType',
		'activity_kit',
		'_activity_slides_pdf_id'
	);
	const [ zipUrl, setZipUrl ] = useEntityProp(
		'postType',
		'activity_kit',
		'_activity_zip_url'
	);
	const views = useEntityProp(
		'postType',
		'activity_kit',
		'_view_count'
	)[ 0 ];
	const downloads = useEntityProp(
		'postType',
		'activity_kit',
		'_download_count'
	)[ 0 ];

	const guideTitle = useSelect(
		( select ) =>
			guidePdfId
				? select( coreStore ).getMedia( guidePdfId )?.title?.rendered
				: null,
		[ guidePdfId ]
	);
	const slidesTitle = useSelect(
		( select ) =>
			slidesPdfId
				? select( coreStore ).getMedia( slidesPdfId )?.title?.rendered
				: null,
		[ slidesPdfId ]
	);

	if ( postType !== 'activity_kit' ) {
		return null;
	}

	return (
		<PluginDocumentSettingPanel
			name="activity-kit-details"
			title={ __( 'Activity Kit Details', 'wporg-learn' ) }
		>
			<TextControl
				label={ __( 'Duration', 'wporg-learn' ) }
				value={ duration || '' }
				onChange={ setDuration }
				placeholder={ __( 'e.g. 60–90 minutes', 'wporg-learn' ) }
			/>

			<BaseControl
				id="activity-kit-guide-pdf"
				label={ __( 'Facilitator Guide PDF', 'wporg-learn' ) }
			>
				{ guidePdfId ? (
					<>
						<span>
							{ guideTitle || `Attachment #${ guidePdfId }` }
						</span>
						<Button
							isDestructive
							isSmall
							onClick={ () => setGuidePdfId( 0 ) }
						>
							{ __( 'Remove', 'wporg-learn' ) }
						</Button>
					</>
				) : (
					<MediaUploadCheck>
						<MediaUpload
							onSelect={ ( media ) => setGuidePdfId( media.id ) }
							allowedTypes={ [ 'application/pdf' ] }
							render={ ( { open } ) => (
								<Button isSecondary onClick={ open }>
									{ __( 'Select PDF', 'wporg-learn' ) }
								</Button>
							) }
						/>
					</MediaUploadCheck>
				) }
			</BaseControl>

			<BaseControl
				id="activity-kit-slides-pdf"
				label={ __( 'Slide Deck PDF', 'wporg-learn' ) }
			>
				{ slidesPdfId ? (
					<>
						<span>
							{ slidesTitle || `Attachment #${ slidesPdfId }` }
						</span>
						<Button
							isDestructive
							isSmall
							onClick={ () => setSlidesPdfId( 0 ) }
						>
							{ __( 'Remove', 'wporg-learn' ) }
						</Button>
					</>
				) : (
					<MediaUploadCheck>
						<MediaUpload
							onSelect={ ( media ) => setSlidesPdfId( media.id ) }
							allowedTypes={ [ 'application/pdf' ] }
							render={ ( { open } ) => (
								<Button isSecondary onClick={ open }>
									{ __( 'Select PDF', 'wporg-learn' ) }
								</Button>
							) }
						/>
					</MediaUploadCheck>
				) }
			</BaseControl>

			<TextControl
				label={ __( 'Download ZIP URL', 'wporg-learn' ) }
				value={ zipUrl || '' }
				onChange={ setZipUrl }
				type="url"
			/>

			<p>
				<strong>{ __( 'Views:', 'wporg-learn' ) }</strong>{ ' ' }
				{ views ?? 0 }
				{ '  ' }
				<strong>{ __( 'Downloads:', 'wporg-learn' ) }</strong>{ ' ' }
				{ downloads ?? 0 }
			</p>
		</PluginDocumentSettingPanel>
	);
}

registerPlugin( 'wporg-activity-kit-details', {
	render: ActivityKitDetailsPanel,
} );
