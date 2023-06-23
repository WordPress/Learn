/**
 * WordPress dependencies
 */
import { BlockControls } from '@wordpress/block-editor';

/**
 * Internal dependencies
 */
import HeadingLevelDropdown from './heading-level-dropdown';

const Settings = ( props ) => {
	const { setAttributes, attributes } = props;
	const { level } = attributes;

	return (
		<BlockControls group="block">
			<HeadingLevelDropdown
				selectedLevel={ level }
				onChange={ ( newLevel ) =>
					setAttributes( { level: newLevel } )
				}
			/>
		</BlockControls>
	);
};

export default Settings;
