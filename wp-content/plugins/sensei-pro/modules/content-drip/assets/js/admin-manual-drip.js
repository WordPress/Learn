/**
 * The admin manual drip management script takes care of the drip override functionality learner
 * management page. It control the functionality of showing the give and remove action buttons
 * depending on the user current users manual drip data.
 */
(function( $, _, Backbone  ) {

	ManualContentDrip = Backbone.View.extend( {
		el:     '.postbox.manual-content-drip',
		events: {
			'change #scd_select_learner':'setUserID',
			'change #scd_select_course_lesson':'setLessonID'
		},

		initialize: function() {
			// Set the state holding properties
			this.selectedUserID   = ''; // int values
			this.selectedLessonID = ''; // int values
			this.manualDripState  = ''; // string active or inactive for the given user and lesson combo

			// Get the elements
			this.learnerSelect = this.$( '#scd_select_learner' );
			this.lessonSelect  = this.$( '#scd_select_course_lesson' );
			this.button        = this.$( '#scd_log_learner_lesson_manual_drip_submit' );

			// Initialize select2
			if ( ! _.isUndefined( $.fn.select2 ) ) {
				this.learnerSelect.select2();
				this.lessonSelect.select2().hide();
			}

			// Add event listener
			this.listenTo( this, 'lessonSelected' , this.getManualDripStatus );

			// Set the initial state
			this.render();
		},

		/*
		 * Listener for the user select drop down
		 */
		setUserID: function( e ) {
			// Confirm this is the right checkbox
			if ( 'change' !== e.type || 'scd_select_learner' !== e.target.id ||  _.isEmpty( e.target.value ) ) {
				this.selectedUserID = '';
			} else {
				// Set the id depending on the selection
				this.selectedUserID = parseInt( e.target.value );
			}

			this.render();
			return this;
		},

		/*
		 * Listener for the lesson select drop down
		 */
		setLessonID: function( e ) {
			// Confirm this is the right checkbox
			if ( 'change' !== e.type || 'scd_select_course_lesson' !== e.target.id ||  _.isEmpty( e.target.value ) ) {
				this.selectedLessonID = '';
			} else {
				// Set the id depending on the selection
				this.selectedLessonID = parseInt( e.target.value );
				this.trigger( 'lessonSelected', this.selectedLessonID  );
			}

			this.render();
			return this;
		},

		/**
		 * Checks if there is a manual drip status set for the current user and lesson combination
		 */
		getManualDripStatus: function( lessonId ) {
			// Validate selection
			if (  _.isEmpty( lessonId + '' ) ) {
				return;
			}

			// Setup ajax post data
			var data = {
				'action':   'get_manual_drip_status',
				'nonce':    scdManualDrip.nonce,
				'userId':   this.selectedUserID,
				'lessonId': lessonId,
			};

			// Start the loader
			this.$( 'img.loading' ).removeClass( 'hidden' );

			// Ajax
			var mcdThis = this;
			$.post( ajaxurl, data, function( response ) {
				if ( response.success ) {
					scdManualDrip.nonce     = response.data.newNonce;
					mcdThis.manualDripState = response.data.manualDripStatus;
				} else {
					console.log( response.data.notice );
				}

				// Disable the loading screen
				mcdThis.render();

				// Hide the loader
				mcdThis.$( 'img.loading' ).addClass( 'hidden' );
			});
		},

		render: function() {
			// Hide elements intialy
			this.lessonSelect.hide();
			this.button.hide();

			// Check the for the user id selected
			// adding '' as the _.isEmpty always shows true for integer values
			if ( ! _.isEmpty( this.selectedUserID + '' ) ) {
				this.lessonSelect.show();
			}

			if ( ! _.isUndefined( this.manualDripState ) && ! _.isEmpty( this.selectedLessonID + '' ) ) {
				// Change button value to remove access and change button class
				if ( this.manualDripState ) {
					this.button.attr( 'value', scdManualDrip.removeAccessValue );
					this.button.html( scdManualDrip.removeAccessText );
					this.button.removeClass( 'button-primary' );
					this.button.addClass( 'button-secondary' );
				} else {
					this.button.attr( 'value', scdManualDrip.giveAccessValue );
					this.button.html( scdManualDrip.giveAccessText );
					this.button.removeClass( 'button-secondary' );
					this.button.addClass( 'button-primary' );
				}

				this.button.show();
			}

			return this;
		}
	} );

	window.manualContentDrip = new ManualContentDrip();
}( jQuery, _, Backbone ));
