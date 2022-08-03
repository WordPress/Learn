/**
 * WordPress dependencies
 */
import { createBlock, getDefaultBlockName } from '@wordpress/blocks';
import { createHigherOrderComponent } from '@wordpress/compose';
import { createContext, useEffect, useContext } from '@wordpress/element';
import { useDispatch } from '@wordpress/data';
import { __ } from '@wordpress/i18n';

/**
 * A HOC for the block edit component, which checks if it's a descendant of the same block type.
 * If it's a descendant, it will remoove the block and show a notice. Otherwise, it renders the
 * component with a provider (allowing it to catch the descendants).
 *
 * @param {Object} RecursionContext The recursion context object to check or add the provider.
 *
 * @return {Function} The HOC function to create the enhanced component.
 */
const withRecursionNotAllowed = createHigherOrderComponent(
	( WrappedComponent ) => {
		const RecursionContext = createContext( false );

		return ( props ) => {
			const { clientId } = props;

			const {
				replaceBlock,
				__unstableMarkNextChangeAsNotPersistent: markNextChangeAsNotPersistent = () => {},
			} = useDispatch( 'core/block-editor' );
			const { createWarningNotice } = useDispatch( 'core/notices' );

			const insideRecursion = useContext( RecursionContext );

			useEffect( () => {
				if ( insideRecursion ) {
					markNextChangeAsNotPersistent();
					replaceBlock(
						clientId,
						createBlock( getDefaultBlockName() )
					);
					createWarningNotice(
						__(
							'Block cannot be added inside itself.',
							'sensei-pro'
						),
						{ type: 'snackbar' }
					);
				}
			}, [ insideRecursion ] );

			if ( insideRecursion ) {
				return null;
			}

			return (
				<RecursionContext.Provider value={ true }>
					<WrappedComponent { ...props } />
				</RecursionContext.Provider>
			);
		};
	},
	'withRecursionNotAllowed'
);

export default withRecursionNotAllowed;
