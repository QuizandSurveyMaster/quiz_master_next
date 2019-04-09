/**
 * Main admin file for functions to be used across many QSM admin pages.
 */
var QSMAdmin;
(function ($) {

	QSMAdmin = {
		/**
		 * Catches an error from a jQuery function (i.e. $.ajax())
		 */
		displayjQueryError: function( jqXHR, textStatus, errorThrown ) {
			QSMAdmin.displayAlert( 'Error: ' + errorThrown + '! Please try again.', 'error' );
		},
		/**
		 * Catches an error from a BackBone function (i.e. model.save())
		 */
		displayError: function( jqXHR, textStatus, errorThrown ) {
			QSMAdmin.displayAlert( 'Error: ' + errorThrown.errorThrown + '! Please try again.', 'error' );
		},
		/**
		 * Displays an alert within the "Quiz Settings" page
		 *
		 * @param string message The message of the alert
		 * @param string type The type of alert. Choose from 'error', 'info', 'success', and 'warning'
		 */
		displayAlert: function( message, type ) {
			QSMAdmin.clearAlerts();
			var template = wp.template( 'notice' );
			var data = {
				message: message,
				type: type
			};
			$( '.qsm-alerts' ).append( template( data ) );
		},
		clearAlerts: function() {
			$( '.qsm-alerts' ).empty();
		},
		selectTab: function( tab ) {
			$( '.qsm-tab' ).removeClass( 'nav-tab-active' );
			$( '.qsm-tab-content' ).hide();
			tab.addClass( 'nav-tab-active' );
			tabID = tab.data( 'tab' );
			$( '.tab-' + tabID ).show();
		}
	};
	$(function() {
		$( '.qsm-tab' ).on( 'click', function( event ) {
			event.preventDefault();
			QSMAdmin.selectTab( $( this ) );
		});

		$( '#qmn_check_all' ).change( function() {
			$( '.qmn_delete_checkbox' ).prop( 'checked', jQuery( '#qmn_check_all' ).prop( 'checked' ) );
		});
                
                $( '.edit-quiz-name' ).click( function(e){
                    e.preventDefault();
                    MicroModal.show( 'modal-3' );
                });
                $( '#edit-name-button' ).on( 'click', function( event ) {
                    event.preventDefault();
                    $( '#edit-name-form' ).submit();
                });
	});
}(jQuery));
