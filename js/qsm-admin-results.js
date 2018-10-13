/**
 * QSM - Admin results pages
 */

var QSMAdminResults;
(function ($) {
	QSMAdminResults = {
		saveResults: function() {
			$( '.results-page' ).each();
		},
		loadResults: function() {
			$.ajax({
				url: wpApiSettings.root + 'quiz-survey-master/v1/quizzes/' + qsmResultsObject.quizID + '/results'
			})
				.done(function( data ) {
					var pages = JSON.parse( data );
					pages.forEach( function( page, i, pages ) {
						QSMAdminResults.addResultsPage( page.conditions, page.page );
					});
				});
		},
		addResultsPage: function( conditions, page ) {
			var template = wp.template( 'results-page' );
			$( '#results-pages' ).append( template( { page: page } ) );
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
		$( '.save-pages' ).on( 'click', function( event ) {
			event.preventDefault();
			QSMAdminResults.saveResults();
		});
	});
}(jQuery));
