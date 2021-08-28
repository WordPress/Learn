/**
 * WordPress dependencies
 */
import {
	Button,
	DateTimePicker,
	Dropdown,
	PanelRow,
} from '@wordpress/components';
import { useDispatch, useSelect } from '@wordpress/data';
import { format, __experimentalGetSettings } from '@wordpress/date';
import { PluginPostStatusInfo } from '@wordpress/edit-post';
import { useState, useRef } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { registerPlugin } from '@wordpress/plugins';

function ExpirationLabel( { date } ) {
	const settings = __experimentalGetSettings();
	return date
		? format(
				`${ settings.formats.date } ${ settings.formats.time }`,
				date
		  )
		: __( 'No expiration date', 'wporg-learn' );
}

const ExpirationDate = () => {
	const postMetaData = useSelect(
		( select ) =>
			select( 'core/editor' ).getEditedPostAttribute( 'meta' ) || {}
	);
	const { editPost } = useDispatch( 'core/editor' );
	const [ expDate, setExpDate ] = useState( postMetaData?.expiration_date );
	const anchorRef = useRef();
	const pickerRef = useRef();

	return (
		<PluginPostStatusInfo>
			<PanelRow className="edit-post-post-schedule" ref={ anchorRef }>
				<span>{ __( 'Expiration', 'wporg-learn' ) }</span>
				<Dropdown
					popoverProps={ { anchorRef: anchorRef.current } }
					position="bottom left"
					contentClassName="edit-post-post-schedule__dialog"
					renderToggle={ ( { onToggle, isOpen } ) => (
						<>
							<Button
								className="edit-post-post-schedule__toggle"
								onClick={ onToggle }
								aria-expanded={ isOpen }
								variant="tertiary"
							>
								<ExpirationLabel date={ expDate } />
							</Button>
						</>
					) }
					renderContent={ () => {
						return (
							<>
								<p style={ { padding: '0 16px' } }>
									{ __(
										'A date when the content in this post might become obsolete.',
										'wporg-learn'
									) }
								</p>
								<DateTimePicker
									ref={ pickerRef }
									currentDate={ expDate }
									onChange={ ( newDate ) => {
										setExpDate( newDate );

										editPost( {
											meta: {
												...postMetaData,
												expiration_date: newDate,
											},
										} );

										const {
											ownerDocument,
										} = pickerRef.current;
										ownerDocument.activeElement.blur();
									} }
								/>
							</>
						);
					} }
				/>
			</PanelRow>
		</PluginPostStatusInfo>
	);
};

const PluginWrapper = () => {
	return <ExpirationDate />;
};

registerPlugin( 'wporg-learn-expiration-date', {
	render: PluginWrapper,
} );
