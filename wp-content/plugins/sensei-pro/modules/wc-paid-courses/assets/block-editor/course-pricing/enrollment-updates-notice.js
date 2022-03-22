/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { useState } from '@wordpress/element';
import { Notice } from '@wordpress/components';
import { useSelect } from '@wordpress/data';

/**
 * Internal dependencies
 */
import EnrollmentUpdatesModal from './enrollment-updates-modal';
import { COURSE_PRODUCTS_STORE } from './store';

/**
 * Enrollment updates notice.
 */
const EnrollmentUpdatesNotice = () => {
	const [ isDismissedNotice, setDismissedNotice ] = useState( false );
	const [ isModalOpen, setModalOpen ] = useState( false );

	const { toggledProductsWithSales } = useSelect( ( select ) => ( {
		toggledProductsWithSales: select(
			COURSE_PRODUCTS_STORE
		).getToggledProductsWithSales(),
	} ) );

	const totalAdded = toggledProductsWithSales.added.length;
	const totalRemoved = toggledProductsWithSales.removed.length;

	return (
		<>
			{ ! isDismissedNotice && ( totalAdded > 0 || totalRemoved > 0 ) && (
				<Notice
					className="sensei-wcpc-course-pricing__notice"
					status="warning"
					isDismissible
					onRemove={ () => setDismissedNotice( true ) }
					actions={ [
						{
							label: __( 'Review', 'sensei-pro' ),
							onClick: () => setModalOpen( true ),
						},
					] }
				>
					<p>
						{ __(
							'You have some enrollment updates',
							'sensei-pro'
						) }
					</p>
				</Notice>
			) }

			{ isModalOpen && (
				<EnrollmentUpdatesModal
					onClose={ () => setModalOpen( false ) }
				/>
			) }
		</>
	);
};

export default EnrollmentUpdatesNotice;
