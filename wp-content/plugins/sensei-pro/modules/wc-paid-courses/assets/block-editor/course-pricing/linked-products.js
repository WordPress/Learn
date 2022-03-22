/**
 * WordPress dependencies
 */
import { useSelect } from '@wordpress/data';
import { RawHTML } from '@wordpress/element';
import { closeSmall } from '@wordpress/icons';
import { Button } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import { COURSE_PRODUCTS_STORE } from './store';

/**
 * Linked products component.
 *
 * @param {Object}   props
 * @param {Function} props.toggleProduct Toogle product callback.
 */
const LinkedProducts = ( { toggleProduct } ) => {
	const { products } = useSelect( ( select ) => ( {
		products: select( COURSE_PRODUCTS_STORE ).getLinkedProducts(),
	} ) );

	return (
		<ul className="sensei-wcpc-course-pricing__selected-list">
			{ products.map( ( product ) => (
				<li
					key={ product.id }
					className="sensei-wcpc-course-pricing__selected-list__item"
				>
					<span className="sensei-wcpc-course-pricing__selected-list__product-text">
						{ product.name }
						{ product.price_html && (
							<RawHTML className="sensei-wcpc-course-pricing__selected-list__price">
								{ ' — ' + product.price_html }
							</RawHTML>
						) }
					</span>
					<Button
						className="sensei-wcpc-course-pricing__selected-list__remove-button"
						onClick={ () => toggleProduct( product.id, false ) }
						icon={ closeSmall }
						label={ __( 'Remove product', 'sensei-pro' ) }
					/>
				</li>
			) ) }
		</ul>
	);
};

export default LinkedProducts;
