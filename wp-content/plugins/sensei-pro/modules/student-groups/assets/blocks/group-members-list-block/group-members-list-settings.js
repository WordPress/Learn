/**
 * WordPress dependencies
 */
import { InspectorControls } from '@wordpress/block-editor';
import { __ } from '@wordpress/i18n';
import { PanelBody, RangeControl } from '@wordpress/components';

/**
 * Settings component for a Group Members List block.
 *
 * @param {Object}   props
 * @param {Object}   props.attributes
 * @param {Function} props.setAttributes
 */
const GroupMembersListSettings = ( { attributes, setAttributes } ) => {
	return (
		<InspectorControls>
			<PanelBody>
				<RangeControl
					label={ __( 'Number of members displayed', 'sensei-pro' ) }
					value={ attributes.numberOfMembers }
					min={ 1 }
					onChange={ ( value ) =>
						setAttributes( {
							numberOfMembers: value,
						} )
					}
				/>
			</PanelBody>
		</InspectorControls>
	);
};

export default GroupMembersListSettings;
