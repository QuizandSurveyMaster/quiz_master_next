/**
 * QSM - Admin emails
 */

var QSMAdminEmails;
(function ($) {
	QSMAdminEmails = {
		total: 0,
		saveEmails: function() {
			QSMAdmin.displayAlert( 'Saving emails...', 'info' );
			var emails = [];
			var email = {};
			$( '.qsm-email' ).each( function() {
                                var email_content = '';
                                if( $( this ).find( '.email-template' ).parent('.wp-editor-container').length > 0 ){
                                    email_content = wp.editor.getContent( $( this ).find( '.email-template' ).attr( 'id' ) );
                                } else {
                                    email_content = $( this ).find( '.email-template' ).val()
                                }                                    
				email = {
					'conditions': [],
					'to': $( this ).find( '.to-email' ).val(),
					'subject': $( this ).find( '.subject' ).val(),
					'content': email_content,
					'replyTo': $( this ).find( '.reply-to' ).prop( 'checked' ),
				};
				$( this ).find( '.email-condition' ).each( function() {
					email.conditions.push({
						'criteria': $( this ).children( '.email-condition-criteria' ).val(),
						'operator': $( this ).children( '.email-condition-operator' ).val(),
						'value': $( this ).children( '.email-condition-value' ).val()
					});
				});
				emails.push( email );
			});
			var data = {
				'emails': emails
			}
			$.ajax({
				url: wpApiSettings.root + 'quiz-survey-master/v1/quizzes/' + qsmEmailsObject.quizID + '/emails',
				method: 'POST',
				data: data,
				headers: { 'X-WP-Nonce': qsmEmailsObject.nonce },
			})
				.done(function( results ) {                                        
					if ( results.status ) {
						QSMAdmin.displayAlert( 'Emails were saved!', 'success' );
					} else {
						QSMAdmin.displayAlert( 'There was an error when saving the emails. Please try again.', 'error' );
					}                                        
				})
				.fail(QSMAdmin.displayjQueryError);
		},
		loadEmails: function() {
			//QSMAdmin.displayAlert( 'Loading emails...', 'info' );
			$.ajax({
				url: wpApiSettings.root + 'quiz-survey-master/v1/quizzes/' + qsmEmailsObject.quizID + '/emails',
				headers: { 'X-WP-Nonce': qsmEmailsObject.nonce },
			})
				.done(function( emails ) {
                                        $( '#qsm_emails' ).find( '.qsm-spinner-loader' ).remove();
					emails.forEach( function( email, i, emails ) {
						QSMAdminEmails.addEmail( email.conditions, email.to, email.subject, email.content, email.replyTo );
					});
					QSMAdmin.clearAlerts();
				})
				.fail(QSMAdmin.displayjQueryError);
		},
		addCondition: function( $email, criteria, operator, value ) {
			var template = wp.template( 'email-condition' );
			$email.find( '.email-when-conditions' ).append( template({
				'criteria': criteria,
				'operator': operator,
				'value': value
			}));
		},
		newCondition: function( $email ) {
			QSMAdminEmails.addCondition( $email, 'score', 'equal', 0 );
		},
		addEmail: function( conditions, to, subject, content, replyTo ) {
			QSMAdminEmails.total += 1;
			var template = wp.template( 'email' );
			$( '#qsm_emails' ).append( template( { id: QSMAdminEmails.total, to: to, subject: subject, content: content, replyTo: replyTo } ) );
			conditions.forEach( function( condition, i, conditions) {
				QSMAdminEmails.addCondition( 
					$( '.qsm-email:last-child' ), 
					condition.criteria,
					condition.operator,
					condition.value
				);
			});
                        if(qsmEmailsObject.qsm_user_ve === 'true'){
                            var settings = {
                                    mediaButtons: true,
                                    tinymce:      {
                                            forced_root_block : '',
                                            toolbar1: 'formatselect,bold,italic,bullist,numlist,blockquote,alignleft,aligncenter,alignright,link,strikethrough,hr,forecolor,pastetext,removeformat,codeformat,charmap,undo,redo'
                                    },
                                    quicktags:    true,
                            };
                            wp.editor.initialize( 'email-template-' + QSMAdminEmails.total, settings );
                        }
			jQuery(document).trigger('qsm_after_add_email_block', [conditions, to, subject, content, replyTo]);
		},
		newEmail: function() {
			var conditions = [{
				'criteria': 'score',
				'operator': 'greater',
				'value': '0'
			}];
			var to = '%USER_EMAIL%';
			var subject = 'Quiz Results For %QUIZ_NAME%';
			var content = '%QUESTIONS_ANSWERS_EMAIL%';
			var replyTo = false;
			QSMAdminEmails.addEmail( conditions, to, subject, content, replyTo );
		}
	};
	$(function() {
		QSMAdminEmails.loadEmails();

		$( '.add-new-email' ).on( 'click', function( event ) {
			event.preventDefault();
			QSMAdminEmails.newEmail();
		});
		$( '.save-emails' ).on( 'click', function( event ) {
			event.preventDefault();
			QSMAdminEmails.saveEmails();
		});
		$( '#qsm_emails' ).on( 'click', '.new-condition', function( event ) {
			event.preventDefault();
			$page = $( this ).closest( '.qsm-email' );
			QSMAdminEmails.newCondition( $page );
		});
		$( '#qsm_emails' ).on( 'click', '.delete-email-button', function( event ) {
			event.preventDefault();
			$( this ).closest( '.qsm-email' ).remove();
		});
		$( '#qsm_emails' ).on( 'click', '.delete-condition-button', function( event ) {
			event.preventDefault();
			$( this ).closest( '.email-condition' ).remove();
		});
	});
}(jQuery));
