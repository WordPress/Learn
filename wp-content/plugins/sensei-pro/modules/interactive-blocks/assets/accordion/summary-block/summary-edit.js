/**
 * WordPress dependencies
 */
import { RichText, useBlockProps } from '@wordpress/block-editor';
import { useEffect } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { cleanForSlug } from '@wordpress/url';
/**
 * Internal dependencies
 */
import Summary from '../elements/summary';
import Settings from './summary-settings';
import { INITIAL_BLOCK } from '../content-block/content-edit';
import useForceContentSelection from '../elements/hooks/use-force-content-selection';

export const Edit = ( props ) => {
	const { attributes, setAttributes, clientId } = props;
	const { summary, level } = attributes;
	const blockProps = useBlockProps();

	useEffect(
		() =>
			setAttributes( {
				anchor: summary ? cleanForSlug( summary ) : '',
			} ),
		[ clientId, setAttributes, summary ]
	);

	const selectContent = useForceContentSelection( clientId );

	return (
		<Summary
			{ ...blockProps }
			onEnter={ () => selectContent( INITIAL_BLOCK ) }
			attributes={ attributes }
		>
			<Settings { ...props } />
			<RichText
				placeholder={ __( 'Add the section title…', 'sensei-pro' ) }
				identifier="summary"
				tagName={ `h${ level }` }
				value={ summary }
				multiline={ false }
				className="wp-block-sensei-lms-accordion-summary__title"
				onChange={ ( value ) =>
					setAttributes( {
						summary: value,
					} )
				}
			/>
		</Summary>
	);
};

export default Edit;
