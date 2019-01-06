/**
 * QSM - Admin emails
 */

var QSMAdminEmails;
(function ($) {
	QSMAdminEmails = {
		saveEmails: function() {
			var pages = [];
			var page = {};
			$( '.email' ).each( function() {
				page = {
					'conditions': [],
				};
				$( this ).find( '.email-condition' ).each( function() {
					page.conditions.push({
						'criteria': $( this ).children( '.email-condition-criteria' ).val(),
						'operator': $( this ).children( '.email-condition-operator' ).val(),
						'value': $( this ).children( '.email-condition-value' ).val()
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
			var template = wp.template( 'email-condition' );
			$page.find( '.email-when-conditions' ).append( template({
				'criteria': criteria,
				'operator': operator,
				'value': value
			}));
		},
		newCondition: function( $page ) {
			QSMAdminEmails.addCondition( $page, 'score', 'equal', 0 );
		},
		addEmail: function( conditions, page, redirect ) {
			var template = wp.template( 'email' );
			$( '#emails' ).append( template( { page: page, redirect: redirect } ) );
			conditions.forEach( function( condition, i, conditions) {
				QSMAdminEmails.addCondition( 
					$( '.email:last-child' ), 
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
		$( '#emails' ).on( 'click', '.new-condition', function( event ) {
			event.preventDefault();
			$page = $( this ).closest( '.email' );
			QSMAdminEmails.newCondition( $page );
		});
		$( '#emails' ).on( 'click', '.delete-page-button', function( event ) {
			event.preventDefault();
			$( this ).closest( '.email' ).remove();
		});
		$( '#emails' ).on( 'click', '.delete-condition-button', function( event ) {
			event.preventDefault();
			$( this ).closest( '.email-condition' ).remove();
		});
	});
}(jQuery));
