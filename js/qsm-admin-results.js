/**
 * QSM - Admin results pages
 */

var QSMAdminResults;
(function ($) {
	QSMAdminResults = {
		saveResults: function() {
			alert('saving...');
			var pages = [];
			var page = {};
			var redirect_value = '';
			$( '.results-page' ).each( function() {
				page = {
					'conditions': [],
					'page':  $( this ).find( '.results-page-template' ).val(),
					'redirect': false,
				};
				redirect_value = $( this ).find( '.results-page-redirect' ).val();
				if ( '' != redirect_value ) {
					page.redirect = redirect_value;
				}
				$( this ).find( '.results-page-condition' ).each( function() {
					page.conditions.push({
						'criteria': $( this ).children( '.results-page-condition-criteria' ).val(),
						'operator': $( this ).children( '.results-page-condition-operator' ).val(),
						'value': $( this ).children( '.results-page-condition-value' ).val()
					});
				});
				pages.push( page );
			});
			var data = {
				'pages': pages
			}
			$.ajax({
				url: wpApiSettings.root + 'quiz-survey-master/v1/quizzes/' + qsmResultsObject.quizID + '/results',
				method: 'POST',
				data: data,
				headers: { 'X-WP-Nonce': qsmResultsObject.nonce },
			})
				.done(function( results ) {
					if ( results.status ) {
						alert( 'Saved!' );
					} else {
						alert( 'Not Saved!' );
					}
				});
		},
		loadResults: function() {
			$.ajax({
				url: wpApiSettings.root + 'quiz-survey-master/v1/quizzes/' + qsmResultsObject.quizID + '/results',
				headers: { 'X-WP-Nonce': qsmResultsObject.nonce },
			})
				.done(function( pages ) {
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
					$( '.results-page:last-child' ), 
					condition.criteria,
					condition.operator,
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
		$( '#results-pages' ).on( 'click', '.delete-page-button', function( event ) {
			event.preventDefault();
			$( this ).closest( '.results-page' ).remove();
		});
		$( '#results-pages' ).on( 'click', '.delete-condition-button', function( event ) {
			event.preventDefault();
			$( this ).closest( '.results-page-condition' ).remove();
		});
	});
}(jQuery));
