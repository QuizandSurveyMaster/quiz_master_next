/**
 * QSM - Admin results pages
 */

var QSMAdminResults;
(function ($) {
	QSMAdminResults = {
		loadResults: function() {

		},
		addResultsPage: function( conditions, page ) {
			var template = wp.template( 'results-page' );
			$( '#results-pages' ).append( template() );
		},
		newResultsPage: function() {
			var conditions = array({
				'criteria': 'score',
				'operator': 'greater',
				'value': '0'
			});
			var page = '%QUESTIONS_ANSWERS%';
			QSMAdminResults.addResultsPage( conditions, page );
		}
	};
	$(function() {
		QSMAdminResults.loadResults();

		$( '.add-new-page' ).on( 'click', function( event ) {
			event.preventDefault();
			QSMAdminResults.newResultsPage();
		});
	});
}(jQuery));
