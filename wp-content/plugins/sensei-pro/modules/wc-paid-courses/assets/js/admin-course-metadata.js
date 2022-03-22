/* global jQuery, sensei_admin_course_metadata */
const selectProducts = jQuery( 'select#course-woocommerce-product-options' );
const modal = jQuery( '#user-confirmation-modal' );

/**
 * Setup a modal which requests confirmation from the user.
 *
 * @since      2.0.0
 *
 * The modal is displayed when the user modifies adds or remove a product to a course and the course has at least one
 * user who bought it. The modal will be show only once per refresh.
 */
const userConfirmationModal = () => {
	const dateConfirmedStorageKey = 'sensei-wcpc-date-confirmed';
	const oneWeeksInMiliseconds = 7 * 24 * 60 * 60 * 1000;
	let originalSelection = [];
	let isModalOpen = false;

	const showModal = ( element ) => {
		const dateConfirmed = window.localStorage.getItem(
			dateConfirmedStorageKey
		);
		const confirmedLessThanOneWeeksAgo =
			dateConfirmed && Date.now() - dateConfirmed < oneWeeksInMiliseconds;

		if (
			! confirmedLessThanOneWeeksAgo &&
			element.attributes[ 'data-total-sales' ].value > 0
		) {
			modal.modal( {
				fadeDuration: 250,
				showClose: false,
				clickClose: false,
				escapeClose: false,
				blockerClass: 'user-confirmation-modal__overlay',
			} );

			isModalOpen = true;
		}
	};

	const closeModal = ( confirmed ) => {
		isModalOpen = false;

		if ( confirmed ) {
			window.localStorage.setItem( dateConfirmedStorageKey, Date.now() );
		}

		jQuery.modal.close();
	};

	selectProducts.on( 'select2:select', ( event ) => {
		const newSelection = event.params.data.element.attributes.value.value;
		originalSelection = selectProducts
			.val()
			.filter( ( product ) => product !== newSelection );

		showModal( event.params.data.element );
	} );

	selectProducts.on( 'select2:unselect', ( event ) => {
		originalSelection = selectProducts.val() ? selectProducts.val() : [];
		originalSelection.push(
			event.params.data.element.attributes.value.value
		);

		showModal( event.params.data.element );
	} );

	// If the modal is open, we need to stop the dropdown from opening as it will is always be displayed on top.
	selectProducts.on( 'select2:opening', ( event ) => {
		if ( isModalOpen ) {
			event.preventDefault();
		}
	} );

	jQuery( '#user-confirmation-modal-confirm' ).on( 'click', () => {
		closeModal( true );
	} );

	jQuery( '#user-confirmation-modal-cancel' ).on( 'click', () => {
		closeModal( false );

		selectProducts.val( originalSelection );
		selectProducts.trigger( 'change' );
	} );
};

jQuery( document ).ready( function () {
	/**
	 * Initialize select2 drop-downs.
	 */
	if ( selectProducts.length > 0 ) {
		selectProducts.select2( {
			width: 'resolve',
			multiple: true,
			placeholder:
				sensei_admin_course_metadata.product_options_placeholder,
		} );

		userConfirmationModal();
	}
} );
