/**
 * QSM - Admin results pages
 */

var QSMAdminResults;
(function ($) {
	QSMAdminResults = {
		total: 0,
		saveResults: function() {
			QSMAdmin.displayAlert( 'Saving results pages...', 'info' );
			var pages = [];
			var page = {};
			var redirect_value = '';
			$( '.results-page' ).each( function() {
				page = {
					'conditions': [],
					'page':  wp.editor.getContent( $( this ).find( '.results-page-template' ).attr( 'id' ) ),
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
					QSMAdmin.displayAlert( 'Results pages were saved!', 'success' );
				} else {
					QSMAdmin.displayAlert( 'There was an error when saving the results pages. Please try again.', 'error' );
				}                                
			})
			.fail(QSMAdmin.displayjQueryError);
		},
		loadResults: function() {
			//QSMAdmin.displayAlert( 'Loading results pages...', 'info' );
			$.ajax({
				url: wpApiSettings.root + 'quiz-survey-master/v1/quizzes/' + qsmResultsObject.quizID + '/results',
				headers: { 'X-WP-Nonce': qsmResultsObject.nonce },
			})
				.done(function( pages ) {
                                        $( '#results-pages' ).find( '.qsm-spinner-loader' ).remove();
					pages.forEach( function( page, i, pages ) {
						QSMAdminResults.addResultsPage( page.conditions, page.page, page.redirect );
					});
					QSMAdmin.clearAlerts();
				})
				.fail(QSMAdmin.displayjQueryError);
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
		addResultsPage: function( conditions, page, redirect ) {
			QSMAdminResults.total += 1;
			var template = wp.template( 'results-page' );
			$( '#results-pages' ).append( template( { id: QSMAdminResults.total, page: page, redirect: redirect } ) );
			conditions.forEach( function( condition, i, conditions) {
				QSMAdminResults.addCondition( 
					$( '.results-page:last-child' ), 
					condition.criteria,
					condition.operator,
					condition.value
				);
			});
			var settings = {
				mediaButtons: true,
				tinymce:      {
					forced_root_block : '',
					toolbar1: 'formatselect,bold,italic,bullist,numlist,blockquote,alignleft,aligncenter,alignright,link,strikethrough,hr,forecolor,pastetext,removeformat,codeformat,charmap,undo,redo'
				},
				quicktags:    true,
			};
			wp.editor.initialize( 'results-page-' + QSMAdminResults.total, settings );
			jQuery(document).trigger('qsm_after_add_result_block', [conditions, page, redirect]);
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
