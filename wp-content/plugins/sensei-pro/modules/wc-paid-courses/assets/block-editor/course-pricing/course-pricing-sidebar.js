/**
 * WordPress dependencies
 */
import { useState, RawHTML } from '@wordpress/element';
import { PluginDocumentSettingPanel } from '@wordpress/edit-post';
import { useSelect, useDispatch } from '@wordpress/data';
import { __, _n, sprintf } from '@wordpress/i18n';
import {
	TextControl,
	CheckboxControl,
	Button,
	Spinner,
} from '@wordpress/components';

/**
 * Internal dependencies
 */
import NewProductSidebar from './new-product-sidebar';
import LinkedProducts from './linked-products';
import EnrollmentUpdatesNotice from './enrollment-updates-notice';
import useSearch from './use-search';
import { COURSE_PRODUCTS_STORE } from './store';

/**
 * Course pricing component.
 */
const CoursePricingSidebar = () => {
	const {
		products,
		isLoadingProducts,
		hasAssignableProducts,
		onSearch,
	} = useSearch();

	const { selectedProductIds } = useSelect( ( select ) => ( {
		selectedProductIds: select( 'core/editor' ).getEditedPostAttribute(
			'meta'
		)?._course_woocommerce_product,
	} ) );

	const { courseId } = useSelect( ( select ) => ( {
		courseId: select( 'core/editor' ).getCurrentPost()?.id,
	} ) );

	const { toggleProduct } = useDispatch( COURSE_PRODUCTS_STORE );

	const [ isNewProductFormActive, setNewProductFormActive ] = useState(
		false
	);

	let searchContent;

	if ( isLoadingProducts || ! selectedProductIds ) {
		searchContent = (
			<div className="sensei-wcpc-course-pricing__loading">
				<Spinner />
			</div>
		);
	} else if ( ! hasAssignableProducts ) {
		searchContent = ! isNewProductFormActive && (
			<div>
				<p>
					{ __(
						'You don’t have any products yet. Get started by creating a new WooCommerce product.',
						'sensei-pro'
					) }
				</p>
				<Button
					isSecondary
					isSmall
					onClick={ () => setNewProductFormActive( true ) }
				>
					{ __( 'Create a product', 'sensei-pro' ) }
				</Button>
			</div>
		);
	} else if ( products.length === 0 ) {
		searchContent = __( 'No products found.', 'sensei-pro' );
	} else {
		searchContent = (
			<ul className="sensei-wcpc-course-pricing__selection-list">
				{ products.map( ( product ) => (
					<li
						key={ product.id }
						className="sensei-wcpc-course-pricing__selection-list__item"
					>
						<CheckboxControl
							label={
								<>
									<span className="sensei-wcpc-course-pricing__selection-list__item__name">
										{ product.name }
									</span>
									<span className="sensei-wcpc-course-pricing__selection-list__item__details">
										{ product.price_html && (
											<RawHTML className="sensei-wcpc-course-pricing__selection-list__item__price">
												{ product.price_html }
											</RawHTML>
										) }
										<span>
											{ product.linked_courses.length ===
											0
												? __(
														'No linked courses',
														'sensei-pro'
												  )
												: sprintf(
														// translators: placeholder is number of linked courses.
														_n(
															'%d linked course',
															'%d linked courses',
															product
																.linked_courses
																.length,
															'sensei-pro'
														),
														product.linked_courses
															.length
												  ) }
										</span>
									</span>
								</>
							}
							checked={ selectedProductIds.includes(
								product.id
							) }
							onChange={ ( checked ) => {
								toggleProduct( product.id, checked );
								window.sensei_log_event(
									'course_pricing_product_select',
									{
										course_id: courseId,
										product_id: product.id,
										enabled: checked ? 1 : 0,
									}
								);
							} }
						/>
					</li>
				) ) }
			</ul>
		);
	}

	return (
		<PluginDocumentSettingPanel
			className="sensei-wcpc-course-pricing"
			name="sensei-wcpc-pricing"
			title={ __( 'Pricing', 'sensei-pro' ) }
		>
			<p>
				{ __(
					'To access this course, learners will need to purchase one of the assigned products.',
					'sensei-pro'
				) }
			</p>

			<LinkedProducts toggleProduct={ toggleProduct } />

			<EnrollmentUpdatesNotice />

			{ hasAssignableProducts && (
				<TextControl
					type="search"
					label={ __(
						'Link one or more products to this course to set the price.',
						'sensei-pro'
					) }
					placeholder={ __( 'Search for a product', 'sensei-pro' ) }
					onChange={ onSearch }
				/>
			) }

			{ searchContent && (
				<div className="sensei-wcpc-course-pricing__selection-list-wrapper">
					{ searchContent }
				</div>
			) }

			<NewProductSidebar
				showCreateButton={ hasAssignableProducts }
				isFormActive={ isNewProductFormActive }
				setFormActive={ setNewProductFormActive }
			/>
		</PluginDocumentSettingPanel>
	);
};

export default CoursePricingSidebar;
