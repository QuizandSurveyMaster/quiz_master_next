/**
 * QSM - Admin emails
 */

var QSMAdminEmails;
(function ($) {
	QSMAdminEmails = {
		saveEmails: function() {
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
				url: wpApiSettings.root + 'quiz-survey-master/v1/quizzes/' + qsmEmailsObject.quizID + '/results',
				method: 'POST',
				data: data,
				headers: { 'X-WP-Nonce': qsmEmailsObject.nonce },
			})
				.done(function( results ) {
					if ( results.status ) {
						alert( 'Saved!' );
					} else {
						alert( 'Not Saved!' );
					}
				});
		},
		loadEmails: function() {
			$.ajax({
				url: wpApiSettings.root + 'quiz-survey-master/v1/quizzes/' + qsmEmailsObject.quizID + '/results',
				headers: { 'X-WP-Nonce': qsmEmailsObject.nonce },
			})
				.done(function( pages ) {
					pages.forEach( function( page, i, pages ) {
						QSMAdminEmails.addEmail( page.conditions, page.page, page.redirect );
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
			QSMAdminEmails.addCondition( $page, 'score', 'equal', 0 );
		},
		addEmail: function( conditions, page, redirect ) {
			var template = wp.template( 'results-page' );
			$( '#results-pages' ).append( template( { page: page, redirect: redirect } ) );
			conditions.forEach( function( condition, i, conditions) {
				QSMAdminEmails.addCondition( 
					$( '.results-page:last-child' ), 
					condition.criteria,
					condition.operator,
					condition.value
				);
			});
		},
		newEmail: function() {
			var conditions = [{
				'criteria': 'score',
				'operator': 'greater',
				'value': '0'
			}];
			var page = '%QUESTIONS_ANSWERS%';
			QSMAdminEmails.addEmail( conditions, page );
		}
	};
	$(function() {
		QSMAdminEmails.loadEmails();

		$( '.add-new-page' ).on( 'click', function( event ) {
			event.preventDefault();
			QSMAdminEmails.newEmail();
		});
		$( '.save-pages' ).on( 'click', function( event ) {
			event.preventDefault();
			QSMAdminEmails.saveEmails();
		});
		$( '#results-pages' ).on( 'click', '.new-condition', function( event ) {
			event.preventDefault();
			$page = $( this ).closest( '.results-page' );
			QSMAdminEmails.newCondition( $page );
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
