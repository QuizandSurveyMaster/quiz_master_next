/**
 * QSM - Admin results pages
 */

var QSMAdminResults;
(function ($) {
	QSMAdminResults = {
		saveResults: function() {
			alert('saving...');
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
		addCondition: function( $page, criteria, operator, value ) {
			var template = wp.template( 'results-page-condition' );
			$page.find( '.results-page-when-conditions' ).append( template({
				'criteria': criteria,
				'operator': operator,
				'value': value
			}));
		},
		newCondition: function( $page ) {
			QSMAdminResults.addCondition( $page, 'score', 'equal', 0 );
		},
		addResultsPage: function( conditions, page ) {
			var template = wp.template( 'results-page' );
			$( '#results-pages' ).append( template( { page: page } ) );
			conditions.forEach( function( condition, i, conditions) {
				QSMAdminResults.addCondition( 
					$( '.results-page' ), 
					condition.criteria,
					condition.operater,
					condition.value
				);
			});
		},
		newResultsPage: function() {
			var conditions = [{
				'criteria': 'score',
				'operator': 'greater',
				'value': '0'
			}];
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
		$( '#results-pages' ).on( 'click', '.new-condition', function( event ) {
			event.preventDefault();
			$page = $( this ).closest( '.results-page' );
			QSMAdminResults.newCondition( $page );
		});
	});
}(jQuery));
