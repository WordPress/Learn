/**
 * WordPress dependencies
 */
import { forwardRef, createContext } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
/**
 * External dependencies
 */
import classNames from 'classnames';
import { motion } from 'framer-motion';
/**
 * Internal dependencies
 */
import useAnimation from './hooks/use-animation';
import useSectionManager from './hooks/use-section-manager';
import { CompletedStatus } from '../../shared/supports-required/elements';

const BLOCK_NAME = 'wp-block-sensei-lms-accordion-section';

export const SectionContext = createContext( {} );

const Section = ( props, ref ) => {
	const { blockProps, attributes = {} } = props;
	const { openOnLoad } = attributes;

	const blockId = props.id || props.blockId;

	const {
		isEditor,
		isOpen,
		toggleCurrentSection,
		isComplete,
		isRequired,
	} = useSectionManager( blockId, openOnLoad );

	const { animationState, variants, initial } = useAnimation( ref, isOpen );

	const detailsClasses = classNames(
		blockProps?.className || props.className,
		{
			[ `${ BLOCK_NAME }--completed` ]: ! isEditor && isComplete,
			[ `${ BLOCK_NAME }--open` ]: isOpen,
		}
	);

	return (
		<div className={ `${ BLOCK_NAME }__wrapper` }>
			{ isRequired && (
				<CompletedStatus
					message={ __(
						'Required - Open the section to complete',
						'sensei-pro'
					) }
					className={ `${ BLOCK_NAME }__status` }
					completed={ isComplete }
				/>
			) }

			<SectionContext.Provider
				value={ {
					toggleCurrentSection,
					isEditor,
					isOpen,
				} }
			>
				<motion.details
					ref={ ref }
					animate={ animationState }
					initial={ initial }
					variants={ isEditor ? null : variants }
					className={ detailsClasses }
					open={ isEditor ? isOpen : true }
					style={ {
						...blockProps?.style,
						...props.style,
					} }
					aria-expanded={ isOpen }
				>
					{ props.children }
				</motion.details>
			</SectionContext.Provider>
		</div>
	);
};

export default forwardRef( Section );
