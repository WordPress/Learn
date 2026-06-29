import { registerPlugin } from '@wordpress/plugins';
import { PluginDocumentSettingPanel } from '@wordpress/edit-post';
import { useDispatch, useSelect } from '@wordpress/data';
import { store as coreStore } from '@wordpress/core-data';
import { BaseControl, Button, RadioControl, TextControl } from '@wordpress/components';
import { MediaUpload, MediaUploadCheck } from '@wordpress/block-editor';
import { __ } from '@wordpress/i18n';
import { useEffect } from '@wordpress/element';

function ActivityKitDetailsPanel() {
	const postMeta = useSelect( ( select ) => select( 'core/editor' ).getEditedPostAttribute( 'meta' ) || {} );
	const { editPost } = useDispatch( 'core/editor' );

	const setMeta = ( key, value ) => editPost( { meta: { ...postMeta, [ key ]: value } } );

	const duration = postMeta._activity_duration || '';
	const guidePdfId = postMeta._activity_guide_pdf_id || 0;
	const slidesPdfId = postMeta._activity_slides_pdf_id || 0;
	const zipId = postMeta._activity_zip_id || 0;
	const feedbackUrl = postMeta._activity_feedback_url || '';

	const guideTitle = useSelect(
		( select ) => ( guidePdfId ? select( coreStore ).getMedia( guidePdfId )?.title?.rendered : null ),
		[ guidePdfId ]
	);
	const slidesTitle = useSelect(
		( select ) => ( slidesPdfId ? select( coreStore ).getMedia( slidesPdfId )?.title?.rendered : null ),
		[ slidesPdfId ]
	);
	const zipTitle = useSelect(
		( select ) => ( zipId ? select( coreStore ).getMedia( zipId )?.title?.rendered : null ),
		[ zipId ]
	);

	return (
		<PluginDocumentSettingPanel name="activity-kit-details" title={ __( 'Activity Kit Details', 'wporg-learn' ) }>
			<TextControl
				label={ __( 'Duration (minutes)', 'wporg-learn' ) }
				value={ duration }
				type="number"
				min="1"
				onChange={ ( value ) => setMeta( '_activity_duration', value ) }
				placeholder={ __( '60', 'wporg-learn' ) }
			/>

			<BaseControl id="activity-kit-guide-pdf" label={ __( 'Facilitator Guide PDF', 'wporg-learn' ) }>
				<div style={ { marginTop: '8px' } }>
					{ guidePdfId ? (
						<>
							<span>{ guideTitle || `Attachment #${ guidePdfId }` }</span>
							<Button isDestructive isSmall onClick={ () => setMeta( '_activity_guide_pdf_id', 0 ) }>
								{ __( 'Remove', 'wporg-learn' ) }
							</Button>
						</>
					) : (
						<MediaUploadCheck>
							<MediaUpload
								onSelect={ ( media ) => setMeta( '_activity_guide_pdf_id', media.id ) }
								allowedTypes={ [ 'application/pdf' ] }
								render={ ( { open } ) => (
									<Button isSecondary onClick={ open }>
										{ __( 'Select PDF', 'wporg-learn' ) }
									</Button>
								) }
							/>
						</MediaUploadCheck>
					) }
				</div>
			</BaseControl>

			<BaseControl id="activity-kit-slides-pdf" label={ __( 'Slide Deck PDF', 'wporg-learn' ) }>
				<div style={ { marginTop: '8px' } }>
					{ slidesPdfId ? (
						<>
							<span>{ slidesTitle || `Attachment #${ slidesPdfId }` }</span>
							<Button isDestructive isSmall onClick={ () => setMeta( '_activity_slides_pdf_id', 0 ) }>
								{ __( 'Remove', 'wporg-learn' ) }
							</Button>
						</>
					) : (
						<MediaUploadCheck>
							<MediaUpload
								onSelect={ ( media ) => setMeta( '_activity_slides_pdf_id', media.id ) }
								allowedTypes={ [ 'application/pdf' ] }
								render={ ( { open } ) => (
									<Button isSecondary onClick={ open }>
										{ __( 'Select PDF', 'wporg-learn' ) }
									</Button>
								) }
							/>
						</MediaUploadCheck>
					) }
				</div>
			</BaseControl>

			<BaseControl id="activity-kit-zip" label={ __( 'Download ZIP', 'wporg-learn' ) }>
				<div style={ { marginTop: '8px' } }>
					{ zipId ? (
						<>
							<span>{ zipTitle || `Attachment #${ zipId }` }</span>
							<Button isDestructive isSmall onClick={ () => setMeta( '_activity_zip_id', 0 ) }>
								{ __( 'Remove', 'wporg-learn' ) }
							</Button>
						</>
					) : (
						<MediaUploadCheck>
							<MediaUpload
								onSelect={ ( media ) => setMeta( '_activity_zip_id', media.id ) }
								allowedTypes={ [ 'application/zip', 'application/x-zip-compressed' ] }
								render={ ( { open } ) => (
									<Button isSecondary onClick={ open }>
										{ __( 'Select ZIP', 'wporg-learn' ) }
									</Button>
								) }
							/>
						</MediaUploadCheck>
					) }
				</div>
			</BaseControl>

			<TextControl
				label={ __( 'Feedback Form URL (optional)', 'wporg-learn' ) }
				help={ __( 'Overrides the global feedback URL for this kit only.', 'wporg-learn' ) }
				value={ feedbackUrl }
				type="url"
				onChange={ ( value ) => setMeta( '_activity_feedback_url', value ) }
				placeholder="https://..."
			/>
		</PluginDocumentSettingPanel>
	);
}

function ActivityKitLevelPanel() {
	const { removeEditorPanel } = useDispatch( 'core/editor' );

	const levelTermIds = useSelect( ( select ) => select( 'core/editor' ).getEditedPostAttribute( 'level' ) || [] );
	const { editPost } = useDispatch( 'core/editor' );

	const levelTerms = useSelect(
		( select ) =>
			select( coreStore ).getEntityRecords( 'taxonomy', 'level', {
				per_page: 100,
				orderby: 'id',
				order: 'asc',
			} ),
		[]
	);

	useEffect( () => {
		removeEditorPanel( 'taxonomy-panel-level' );
	}, [ removeEditorPanel ] );

	const options = [
		{ label: __( '— None —', 'wporg-learn' ), value: '' },
		...( levelTerms || [] ).map( ( term ) => ( {
			label: term.name,
			value: String( term.id ),
		} ) ),
	];

	const selectedValue = levelTermIds && levelTermIds.length > 0 ? String( levelTermIds[ 0 ] ) : '';

	return (
		<PluginDocumentSettingPanel name="activity-kit-level" title={ __( 'Experience Level', 'wporg-learn' ) }>
			<RadioControl
				options={ options }
				selected={ selectedValue }
				onChange={ ( value ) =>
					editPost( {
						level: value ? [ parseInt( value, 10 ) ] : [],
					} )
				}
			/>
		</PluginDocumentSettingPanel>
	);
}

function ActivityKitSidebar() {
	return (
		<>
			<ActivityKitDetailsPanel />
			<ActivityKitLevelPanel />
		</>
	);
}

registerPlugin( 'wporg-activity-kit-details', {
	render: ActivityKitSidebar,
} );
