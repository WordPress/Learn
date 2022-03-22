/**
 * WordPress dependencies
 */
import { __, sprintf, _n } from '@wordpress/i18n';
import { useSelect, useDispatch } from '@wordpress/data';
import { Button } from '@wordpress/components';
import { useEffect, RawHTML } from '@wordpress/element';

/**
 * Internal dependencies
 */
import { COURSE_PRODUCTS_STORE } from './store';
import WCPCModal from '../../editor-components/wcpc-modal';

/**
 * Enrollment updates modal.
 *
 * @param {Object}   props
 * @param {Function} props.onBack  Back callback.
 * @param {Function} props.onClose Close callback.
 */
const EnrollmentUpdatesModal = ( { onBack, onClose } ) => {
	const { toggledProductsWithSales } = useSelect( ( select ) => ( {
		toggledProductsWithSales: select(
			COURSE_PRODUCTS_STORE
		).getToggledProductsWithSales(),
	} ) );

	const { toggleProduct } = useDispatch( COURSE_PRODUCTS_STORE );

	const totalAdded = toggledProductsWithSales.added.length;
	const totalRemoved = toggledProductsWithSales.removed.length;

	// Closes the modal when reverting all products.
	useEffect( () => {
		if ( totalAdded === 0 && totalRemoved === 0 ) {
			onClose();
		}
	}, [ totalAdded, totalRemoved ] );

	const tableThead = (
		<thead>
			<tr>
				<th>{ __( 'Product', 'sensei-pro' ) }</th>
				<th>{ __( 'Price', 'sensei-pro' ) }</th>
				<th>{ __( 'Linked courses', 'sensei-pro' ) }</th>
				<th>{ __( 'Purchases', 'sensei-pro' ) }</th>
				<th>{ __( 'Action', 'sensei-pro' ) }</th>
			</tr>
		</thead>
	);

	const createMapProducts = ( actionLabel, actionOnClick ) => ( product ) => (
		<tr key={ product.id }>
			<td className="sensei-wcpc-enrollment-updates-modal__table__product-name">
				{ product.name }
			</td>
			<td>
				<RawHTML>{ product.price_html }</RawHTML>
			</td>
			<td>
				{ product.linked_courses.length === 0
					? __( 'No linked courses', 'sensei-pro' )
					: sprintf(
							// translators: placeholder is number of linked courses.
							_n(
								'%d linked course',
								'%d linked courses',
								product.linked_courses.length,
								'sensei-pro'
							),
							product.linked_courses.length
					  ) }
			</td>
			<td>
				{ sprintf(
					// translators: placeholder is the total sales.
					_n(
						'%d purchase',
						'%d purchases',
						product.total_sales,
						'sensei-pro'
					),
					product.total_sales
				) }
			</td>
			<td className="sensei-wcpc-enrollment-updates-modal__table__action">
				<Button isTertiary onClick={ () => actionOnClick( product ) }>
					{ actionLabel }
				</Button>
			</td>
		</tr>
	);

	const modalActions = [
		{
			id: 'proceed',
			label: __( 'Proceed with updates', 'sensei-pro' ),
			buttonProps: {
				isPrimary: true,
				onClick: onClose,
			},
		},
	];

	if ( onBack ) {
		modalActions.unshift( {
			id: 'back',
			label: __( '< Back', 'sensei-pro' ),
			inverted: true,
			buttonProps: {
				isSecondary: true,
				onClick: onBack,
			},
		} );
	}

	return (
		<WCPCModal
			contentLabel={ __( 'Enrollment updates', 'sensei-pro' ) }
			title={ __(
				'Are you sure you want to make these enrollment updates?',
				'sensei-pro'
			) }
			actions={ modalActions }
			onClose={ onClose }
		>
			{ totalAdded > 0 && (
				<>
					<p>
						{ _n(
							'Linking this product may enroll all learners who have already purchased it.',
							'Linking these products may enroll all learners who have already purchased any of them.',
							totalAdded,
							'sensei-pro'
						) }
					</p>

					<table className="sensei-wcpc-enrollment-updates-modal__table">
						{ tableThead }
						<tbody>
							{ toggledProductsWithSales.added.map(
								createMapProducts(
									__( 'Remove product', 'sensei-pro' ),
									( product ) => {
										toggleProduct( product.id, false );
									}
								)
							) }
						</tbody>
					</table>
				</>
			) }

			{ totalRemoved > 0 && (
				<>
					<p>
						{ _n(
							'Unlinking this product may unenroll all the learners who have already purchased it.',
							'Unlinking these products may unenroll all the learners who have already purchased any of them.',
							totalRemoved,
							'sensei-pro'
						) }
					</p>

					<table className="sensei-wcpc-enrollment-updates-modal__table">
						{ tableThead }
						<tbody>
							{ toggledProductsWithSales.removed.map(
								createMapProducts(
									__( 'Add product', 'sensei-pro' ),
									( product ) => {
										toggleProduct( product.id, true );
									}
								)
							) }
						</tbody>
					</table>
				</>
			) }
		</WCPCModal>
	);
};

export default EnrollmentUpdatesModal;
