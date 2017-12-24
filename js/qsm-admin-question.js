/**
 * QSM Question Tab
 */

var QSMQuestion;
(function ($) {
	QSMQuestion = {
		question: Backbone.Model.extend({
			defaults: {
				id: null,
				quizID: 1,
				type: '0',
				name: 'Your new question!',
				answerInfo: '',
				comments: '1',
				hint: '',
				category: '',
				required: 1,
				answers: [],
				page: 1
			}
		}),
		questions: null,
		questionCollection: null,
		addNewPage: function() {
			var template = wp.template( 'page' );
			$( '.questions' ).append( template() );
			$( '.page' ).sortable({
				opacity: 70,
				cursor: 'move',
				placeholder: "ui-state-highlight",
				connectWith: '.page'
			});
			setTimeout( QSMQuestion.removeNew, 250 );
		},
		addNewQuestion: function( model ) {
			QSMQuestion.clearAlerts();
			QSMQuestion.displayAlert( 'Question created!', 'success' );
			var template = wp.template( 'question' );
			$( '.page:nth-child(' + model.page + ')' ).append( template( { id: model.id, type : model.type, category : model.category, question: model.name } ) );
			setTimeout( QSMQuestion.removeNew, 250 );
		},
		createQuestion: function( page ) {
			QSMQuestion.displayAlert( 'Creating question...', 'info' );
			QSMQuestion.questions.create( 
				{ page: page },
				{
					headers: { 'X-WP-Nonce': qsmQuestionSettings.nonce },
					success: QSMQuestion.addNewQuestion,
					error: QSMQuestion.displayError
				}
			);
		},
		editQuestion: function( $question ) {
			MicroModal.show( 'modal-1' );
			settings = {
				'tinymce': true,
				'quicktags': true
			}
			wp.editor.initialize( 'question-text', settings );
		},
		displayError: function( jqXHR, textStatus, errorThrown ) {
			QSMQuestion.clearAlerts();
			QSMQuestion.displayAlert( 'Error: ' + errorThrown.errorThrown + '! Please try again.', 'error' );
		},
		displayAlert: function( message, type ) {
			QSMQuestion.clearAlerts();
			var template = wp.template( 'notice' );
			var data = {
				message: message,
				type: type
			};
			$( '.alert-messages' ).append( template( data ) );
		},
		clearAlerts: function() {
			$( '.alert-messages' ).empty();
		},
		removeNew: function() {
			$( '.page-new' ).removeClass( 'page-new' );
			$( '.question-new' ).removeClass( 'question-new' );
		}
	};

	$(function() {
		QSMQuestion.questionCollection = Backbone.Collection.extend({
			url: '/wp-json/qsm-simple-popups/v1/popups',
			model: QSMQuestion.question
		});
		QSMQuestion.questions = new QSMQuestion.questionCollection();
		$( '.new-page-button' ).on( 'click', function( event ) {
			event.preventDefault();
			QSMQuestion.addNewPage();
		});

		$( '.questions' ).on( 'click', '.new-question-button', function( event ) {
			event.preventDefault();
			QSMQuestion.createQuestion( $( this ).parent() );
		});

		$( '.questions' ).on( 'click', '.question', function( event ) {
			event.preventDefault();
			QSMQuestion.editQuestion( $( this ) );
		});

		$( '.questions' ).sortable({
			opacity: 70,
			cursor: 'move',
			placeholder: "ui-state-highlight"
		});
		$( '.page' ).sortable({
			opacity: 70,
			cursor: 'move',
			placeholder: "ui-state-highlight",
			connectWith: '.page'
		});
	});
}(jQuery));
