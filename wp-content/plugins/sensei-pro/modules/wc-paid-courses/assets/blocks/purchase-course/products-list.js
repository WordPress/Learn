import { RawHTML } from '@wordpress/element';

/**
 * Products list component.
 *
 * @param {Object}   props
 * @param {Array}    props.products          List of course product IDs.
 * @param {Function} props.onChange          Change callback.
 * @param {number}   props.selectedProductId The id of the currently selected product.
 */
const ProductsList = ( { products, onChange, selectedProductId } ) => (
	<div className="wp-block-sensei-lms-purchase-course__products">
		<form>
			<ul className="wp-block-sensei-lms-purchase-course__products__list">
				{ products.map( ( product ) => (
					<li
						key={ product.id }
						className="wp-block-sensei-lms-purchase-course__products__item"
					>
						{ /* eslint-disable-next-line jsx-a11y/label-has-for */ }
						<label>
							<input
								className="wp-block-sensei-lms-purchase-course__products__radio"
								name="wcpc-radio-product"
								type="radio"
								onChange={ () => onChange( product.id ) }
								checked={ product.id === selectedProductId }
							/>
							<span className="wp-block-sensei-lms-purchase-course__products__label">
								<strong className="wp-block-sensei-lms-purchase-course__products__product-title">
									{ product.name }
								</strong>
								<span className="wp-block-sensei-lms-purchase-course__products__product-description">
									<RawHTML>{ product.description }</RawHTML>
								</span>
								<span className="wp-block-sensei-lms-purchase-course__products__price">
									<RawHTML>{ product.price_html }</RawHTML>
								</span>
							</span>
						</label>
					</li>
				) ) }
			</ul>
		</form>
	</div>
);

export default ProductsList;
