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
		categories: [],
		prepareCategories: function() {
			QSMQuestion.categories = [];
			QSMQuestion.questions.each(function( question ) {
				if ( 0 !== question.get( 'category' ).length ) {
					QSMQuestion.categories.push( question.get( 'category' ) );
				}
			});
		},
		processCategories: function() {
			$( '.category' ).remove();
			_.each( QSMQuestion.categories, function( category ) {
				QSMQuestion.addCategory( category );
			});
		},
		addCategory: function( category ) {
			var template = wp.template( 'single-category' );
			$( '#categories' ).prepend( template( { category: category } ) );
		},
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
			var question;
			if ( qsmQuestionSettings.pages.length > 0 ) {
				for ( var i = 0; i < qsmQuestionSettings.pages.length; i++ ) {
					for ( var j = 0; j < qsmQuestionSettings.pages[ i ].length; j++ ) {
						question = QSMQuestion.questions.get( qsmQuestionSettings.pages[ i ][ j ] );
						QSMQuestion.addQuestionToPage( question );
					}
				}
			} else {
				QSMQuestion.questions.each( QSMQuestion.addQuestionToPage );
			}
		},
		savePages: function() {
			var pages = [];
			_.each( jQuery( '.page' ), function( page ) {
				var singlePage = [];
				_.each( jQuery( page ).children( '.question' ), function( question ){
					singlePage.push( jQuery( question ).data( 'question-id' ) )
				});
				pages.push( singlePage );
			});
			var data = {
				action: 'qsm_save_pages',
				pages: pages,
				quiz_id : qsmQuestionSettings.quizID
			};
	
			jQuery.ajax( ajaxurl, {
				data: data,
				method: 'POST',
				success: QSMQuestion.savePagesSuccess,
				error: QSMQuestion.displayjQueryError
			});
		},
		savePagesSuccess: function() {
			QSMQuestion.displayAlert( 'Questions and pages were saved!', 'success' );
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
			$( '.page:nth-child(' + page + ')' ).append( template( { id: model.id, category : model.get('category'), question: questionName } ) );
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
			var answerInfo = $( '#correct_answer_info' ).val();
			var type = $( "#question_type" ).val();
			var comments = $( ".comments-radio:checked" ).val();
			var required = $( "#required" ).val();
			var category = $( ".category-radio:checked" ).val();
			if ( 'new_category' == category ) {
				category = $( '#new_category' ).val();
			}
			if ( ! category ) {
				category = '';
			}
			var answers = [];
			var $answers = jQuery( '.answers-single');
			_.each( $answers, function( answer ) {
				var $answer = jQuery( answer );
				var answer = $answer.find( '.answer-text' ).val();
				var points = $answer.find( '.answer-points' ).val();
				var correct = 0;
				if ( $answer.find( '.answer-correct' ).prop( 'checked' ) ) {
					correct = 1;
				}
				answers.push( [ answer, points, correct ] );
			});
			model.save( 
				{ 
					type: type,
					name: name,
					answerInfo: answerInfo,
					comments: comments,
					hint: hint,
					category: category,
					required: required,
					answers: answers,
				}, 
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
		addNewAnswer: function( answer ) {
			var answerTemplate = wp.template( 'single-answer' );
			$( '#answers' ).append( answerTemplate( { answer: answer[0], points: answer[1], correct: answer[2] } ) );
		},
		openEditPopup: function( questionID ) {
			QSMQuestion.prepareCategories();
			QSMQuestion.processCategories();
			var question = QSMQuestion.questions.get( questionID );
			var questionText = QSMQuestion.prepareQuestionText( question.get( 'name' ) );
			$( '#edit_question_id' ).val( questionID );
			var question_editor = tinyMCE.get( 'question-text' );
			if ( question_editor ) {
				tinyMCE.get( 'question-text' ).setContent( questionText );
			} else {
				jQuery( "#question-text" ).val( questionText );
			}

			$( '#answers' ).empty();
			var answers = question.get( 'answers' );
			_.each( answers, function( answer ) {
				QSMQuestion.addNewAnswer( answer );
			});
			$( '#hint' ).val( question.get( 'hint' ) );
			$( '#correct_answer_info' ).val( question.get( 'answerInfo' ) );
			$( "#question_type" ).val( question.get( 'type' ) );
			$( ".comments-radio" ).val( [question.get( 'comments' )] );
			$( "#required" ).val( question.get( 'required' ) );
			$( ".category-radio" ).removeAttr( 'checked' );
			if ( 0 !== question.get( 'category' ).length ) {
				$( ".category-radio" ).val( [question.get( 'category' )] );
			}
			MicroModal.show( 'modal-1' );
		},
		displayjQueryError: function( jqXHR, textStatus, errorThrown ) {
			QSMQuestion.displayAlert( 'Error: ' + errorThrown + '! Please try again.', 'error' );
		},
		displayError: function( jqXHR, textStatus, errorThrown ) {
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
				mediaButtons: true,
				tinymce:      {
					toolbar1: 'formatselect,bold,italic,bullist,numlist,link,blockquote,alignleft,aligncenter,alignright,strikethrough,hr,forecolor,pastetext,removeformat,codeformat,undo,redo'
				},
				quicktags:    true,
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
		$( '#answers' ).on( 'click', '.delete-answer-button', function( event ) {
			event.preventDefault();
			$( this ).parents( '.answers-single' ).remove();
		});
		$( '#save-popup-button' ).on( 'click', function( event ) {
			event.preventDefault();
			QSMQuestion.saveQuestion( $( this ).parent().siblings( 'main' ).children( '#edit_question_id' ).val() );
		});
		$( '#new-answer-button' ).on( 'click', function( event ) {
			event.preventDefault();
			var answer = [ '', '', 0 ];
			QSMQuestion.addNewAnswer( answer );
		});

		$( '.save-page-button' ).on( 'click', function( event ) {
			event.preventDefault();
			QSMQuestion.savePages();
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
