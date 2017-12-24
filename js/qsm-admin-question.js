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
				page: 0
			}
		}),
		questions: null,
		questionCollection: null,
		loadQuestions: function() {
			QSMQuestion.displayAlert( 'Loading questions...', 'info' );
			QSMQuestion.questions.fetch({ 
				headers: { 'X-WP-Nonce': qsmQuestionSettings.nonce },
				data: { quizID: qsmQuestionSettings.quizID },
				success: QSMQuestion.loadSuccess,
				error: QSMQuestion.displayError
			});
		},
		loadSuccess: function() {
			QSMQuestion.clearAlerts();
			QSMQuestion.questions.each( QSMQuestion.addQuestionToPage )
		},
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
			QSMQuestion.displayAlert( 'Question created!', 'success' );
			QSMQuestion.addQuestionToPage( model );
			QSMQuestion.openEditPopup( model.id );
		},
		addQuestionToPage: function( model ) {
			var page = model.get( 'page' ) + 1;
			var template = wp.template( 'question' );
			var page_exists = $( '.page:nth-child(' + page + ')' ).length;
			var count = 0;
			while ( ! page_exists ) {
				QSMQuestion.addNewPage();
				page_exists = $( '.page:nth-child(' + page + ')' );
				count++;
				if ( count > 5 ) {
					page_exists = true;
					console.log( 'count reached' );
				}
			}
			var questionName = QSMQuestion.prepareQuestionText( model.get( 'name' ) );
			$( '.page:nth-child(' + page + ')' ).append( template( { id: model.id, type : model.get('type'), category : model.get('category'), question: questionName } ) );
			setTimeout( QSMQuestion.removeNew, 250 );
		},
		createQuestion: function( page ) {
			QSMQuestion.displayAlert( 'Creating question...', 'info' );
			QSMQuestion.questions.create( 
				{ 
					quizID: qsmQuestionSettings.quizID,
					page: page
				},
				{
					headers: { 'X-WP-Nonce': qsmQuestionSettings.nonce },
					success: QSMQuestion.addNewQuestion,
					error: QSMQuestion.displayError
				}
			);
		},
		duplicateQuestion: function( questionID ) {
			QSMQuestion.displayAlert( 'Duplicating question...', 'info' );
			var model = QSMQuestion.questions.get( questionID );
			var newModel = _.clone(model.attributes);
			newModel.id = null;
			QSMQuestion.questions.create( 
				newModel,
				{
					headers: { 'X-WP-Nonce': qsmQuestionSettings.nonce },
					success: QSMQuestion.addNewQuestion,
					error: QSMQuestion.displayError
				}
			);
		},
		saveQuestion: function( questionID ) {
			QSMQuestion.displayAlert( 'Saving question...', 'info' );
			var model = QSMQuestion.questions.get( questionID );
			var hint = $( '#hint' ).val();
			var name = wp.editor.getContent( 'question-text' );
			model.save( { name: name, hint: hint }, 
				{ 
					headers: { 'X-WP-Nonce': qsmQuestionSettings.nonce },
					success: QSMQuestion.saveSuccess,
					error: QSMQuestion.displayError
				} 
			);
			MicroModal.close('modal-1');
		},
		saveSuccess: function( model ) {
			QSMQuestion.displayAlert( 'Question was saved!', 'success' );
			var template = wp.template( 'question' );
			var page = model.get( 'page' ) + 1;
			$( '.question[data-question-id=' + model.id + ']' ).replaceWith( template( { id: model.id, type : model.get('type'), category : model.get('category'), question: model.get('name') } ) );
			setTimeout( QSMQuestion.removeNew, 250 );
		},
		openEditPopup: function( questionID ) {
			var question = QSMQuestion.questions.get( questionID );
			var questionText = QSMQuestion.prepareQuestionText( question.get( 'name' ) );
			$( '#edit_question_id' ).val( questionID );
			var question_editor = tinyMCE.get( 'question-text' );
			if ( question_editor ) {
				tinyMCE.get( 'question-text' ).setContent( questionText );
			} else {
				jQuery( "#question-text" ).val( questionText );
			}
			$( '#hint' ).val( question.get( 'hint' ) );
			MicroModal.show( 'modal-1' );
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
			$( '.questions-messages' ).append( template( data ) );
		},
		clearAlerts: function() {
			$( '.questions-messages' ).empty();
		},
		removeNew: function() {
			$( '.page-new' ).removeClass( 'page-new' );
			$( '.question-new' ).removeClass( 'question-new' );
		},
		prepareQuestionText: function( question ) {
			return jQuery('<textarea />').html( question ).text();
		},
		prepareEditor: function() {
			var settings = {
				'tinymce': true,
				'quicktags': true
			};
			wp.editor.initialize( 'question-text', settings );
		}
	};

	$(function() {
		QSMQuestion.questionCollection = Backbone.Collection.extend({
			url: '/wp-json/quiz-survey-master/v1/questions',
			model: QSMQuestion.question
		});
		QSMQuestion.questions = new QSMQuestion.questionCollection();
		$( '.new-page-button' ).on( 'click', function( event ) {
			event.preventDefault();
			QSMQuestion.addNewPage();
		});

		$( '.questions' ).on( 'click', '.new-question-button', function( event ) {
			event.preventDefault();
			QSMQuestion.createQuestion( $( this ).parent().index() );
		});

		$( '.questions' ).on( 'click', '.edit-question-button', function( event ) {
			event.preventDefault();
			QSMQuestion.openEditPopup( $( this ).parents( '.question' ).data( 'question-id' ) );
		});

		$( '.questions' ).on( 'click', '.duplicate-question-button', function( event ) {
			event.preventDefault();
			QSMQuestion.duplicateQuestion( $( this ).parents( '.question' ).data( 'question-id' ) );
		});

		$( '.questions' ).on( 'click', '.delete-question-button', function( event ) {
			event.preventDefault();
			$( this ).parents( '.question' ).remove();
		});

		$( '#save-popup-button' ).on( 'click', function( event ) {
			event.preventDefault();
			QSMQuestion.saveQuestion( $( this ).parent().siblings( 'main' ).children( '#edit_question_id' ).val() );
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
		QSMQuestion.prepareEditor();
		QSMQuestion.loadQuestions();
	});
}(jQuery));
