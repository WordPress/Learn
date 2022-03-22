/**
 * External dependencies
 */
import { debounce } from 'lodash';

/**
 * WordPress dependencies
 */
import { useSelect } from '@wordpress/data';
import { useState, useCallback } from '@wordpress/element';

/**
 * Internal dependencies
 */
import { COURSE_PRODUCTS_STORE } from './store';

/**
 * @typedef SearchHookReturn
 *
 * @property {Object[]} products              Found products list.
 * @property {boolean}  isLoadingProducts     Searched text.
 * @property {boolean}  hasAssignableProducts Whether site has assignable products.
 * @property {Function} onSearch              Search handler.
 */
/**
 * Products search hook.
 *
 * @return {SearchHookReturn} Hook object.
 */
const useSearch = () => {
	const [ search, setSearch ] = useState( '' );

	const onSearch = useCallback(
		debounce( ( value ) => {
			setSearch( value );
		}, 250 ),
		[]
	);

	const { products, isLoadingProducts, hasAssignableProducts } = useSelect(
		( select ) => {
			const store = select( COURSE_PRODUCTS_STORE );

			return {
				products: store.getAssignableProducts( search ),
				isLoadingProducts: ! store.hasFinishedResolution(
					'getAssignableProducts',
					[ search ]
				),
				hasAssignableProducts: store.getHasAssignableProducts(),
			};
		},
		[ search ]
	);

	return { products, isLoadingProducts, hasAssignableProducts, onSearch };
};

export default useSearch;
