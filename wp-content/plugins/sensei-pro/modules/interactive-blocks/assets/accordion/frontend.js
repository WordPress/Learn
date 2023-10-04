/**
 * WordPress dependencies
 */
import { RawHTML, useContext, useRef } from '@wordpress/element';
/**
 * External dependencies
 */
import { sanitize } from 'dompurify';

/**
 * Internal dependencies
 */
import { registerBlockFrontend } from '../shared/block-frontend';
import { Accordion as FrontEndAccordion } from './elements/accordion';
import Section, { SectionContext } from './elements/section';
import Summary from './elements/summary';
import Content from './elements/content';

function FrontEndSection( props ) {
	const ref = useRef();

	const { children, blockProps, attributes } = props;
	return (
		<Section
			{ ...blockProps }
			attributes={ attributes }
			blockId={ props.blockId }
			ref={ ref }
		>
			{ children }
		</Section>
	);
}

function FrontEndContent( props ) {
	const { children, blockProps } = props;
	const ref = useRef();
	return (
		<Content { ...blockProps } ref={ ref }>
			{ children }
		</Content>
	);
}

function FrontEndSummary( props ) {
	const { blockProps, attributes } = props;
	const { level, summary } = attributes;

	const TagName = `h${ level }`;
	const ref = useRef();

	const { toggleCurrentSection } = useContext( SectionContext );

	return (
		<Summary
			{ ...blockProps }
			ref={ ref }
			onEnter={ () => toggleCurrentSection() }
			attributes={ attributes }
		>
			<TagName
				className={ 'wp-block-sensei-lms-accordion-summary__title' }
			>
				<RawHTML>{ sanitize( summary ) }</RawHTML>
			</TagName>
		</Summary>
	);
}

registerBlockFrontend( {
	name: 'sensei-lms/accordion-content',
	run: FrontEndContent,
} );

registerBlockFrontend( {
	name: 'sensei-lms/accordion-summary',
	run: FrontEndSummary,
} );

registerBlockFrontend( {
	name: 'sensei-lms/accordion-section',
	run: FrontEndSection,
} );

registerBlockFrontend( {
	name: 'sensei-lms/accordion',
	run: FrontEndAccordion,
} );
