/**
 * QSM Question Tab
 */

var QSMQuestion;
var import_button;
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
		/**
		 * Counts the total number of questions and then updates #total-questions span.
		 */
		countTotal: function() {
			var total = 0;

			// Cycles through each page.
			_.each( jQuery( '.page' ), function( page ) {

				// If page is empty, continue to the next.
				if( 0 == jQuery( page ).children( '.question' ).length ) {
					return;
				}
				// Cycle through each question and add to our total.
				_.each( jQuery( page ).children( '.question' ), function( question ){
					total += 1;
				});
			});
			$( '#total-questions' ).text( total );
		},
		openQuestionBank: function( pageID ) {
			QSMQuestion.loadQuestionBank();
			$( '#add-question-bank-page' ).val( pageID );
			MicroModal.show( 'modal-2' );
		},
		loadQuestionBank: function() {
			$( '#question-bank' ).empty();
			$( '#question-bank' ).append( '<div class="qsm-spinner-loader"></div>' );
			$.ajax( {
				url: wpApiSettings.root + 'quiz-survey-master/v1/questions',
				method: 'GET',
				beforeSend: function ( xhr ) {
					xhr.setRequestHeader( 'X-WP-Nonce', qsmQuestionSettings.nonce );
				},
				data: {
					'quizID' : 0
				},
				success: QSMQuestion.questionBankLoadSuccess
			});
		},
		questionBankLoadSuccess: function( questions ) {
			$( '#question-bank' ).empty();
                        var category_arr = [];
			for ( var i = 0; i < questions.length; i++) {
				QSMQuestion.addQuestionToQuestionBank( questions[i] );
                                if(category_arr.indexOf(questions[i].category) == -1 && questions[i].category != ''){                                    
                                    category_arr.push(questions[i].category);                                    
                                }
			}                        
                        if(category_arr.length > 0){
                            $cat_html = '<select name="question-bank-cat" id="question-bank-cat">';
                            $cat_html += '<option value="">All Questions</option>';
                            $.each(category_arr, function(index, value){
                                $cat_html += '<option value="'+ value +'">'+ value +' Questions</option>';
                            });
                            $cat_html += '</select>';
                            $( '#question-bank' ).prepend($cat_html);
                        }
		},
		addQuestionToQuestionBank: function( question ) {
			var questionText = QSMQuestion.prepareQuestionText( question.name );
			var template = wp.template( 'single-question-bank-question' );
			$( '#question-bank' ).append( template( { id: question.id, question: questionText, category: question.category, quiz_name: question.quiz_name  } ) );
		},
		addQuestionFromQuestionBank: function( questionID ) {
			//MicroModal.close( 'modal-2' );
			//QSMAdmin.displayAlert( 'Adding question...', 'info' );
			var model = new QSMQuestion.question( { id: questionID } );
			model.fetch({ 
				headers: { 'X-WP-Nonce': qsmQuestionSettings.nonce },
				url: wpApiSettings.root + 'quiz-survey-master/v1/questions/' + questionID,
				success: QSMQuestion.questionBankSuccess,
				error: QSMAdmin.displayError
			});	
		},
		questionBankSuccess: function( model ) {
			var page = parseInt( $( '#add-question-bank-page' ).val(), 10 );
			model.set( 'page', page );
			//QSMAdmin.displayAlert( 'Question added!', 'success' );
			QSMQuestion.questions.add( model );
			QSMQuestion.addQuestionToPage( model );
                        $('.import-button').removeClass('disable_import');
                        import_button.html('').html('Add Question');
		},
		prepareCategories: function() {
			QSMQuestion.categories = [];
			QSMQuestion.questions.each(function( question ) {
				var category = question.get( 'category' );
				if ( 0 !== category.length && ! _.contains( QSMQuestion.categories, category ) ) {
					QSMQuestion.categories.push( category );
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
			QSMAdmin.displayAlert( 'Loading questions...', 'info' );
			QSMQuestion.questions.fetch({ 
				headers: { 'X-WP-Nonce': qsmQuestionSettings.nonce },
				data: { quizID: qsmQuestionSettings.quizID },
				success: QSMQuestion.loadSuccess,
				error: QSMAdmin.displayError
			});
		},
		loadSuccess: function() {
			QSMAdmin.clearAlerts();
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
			QSMQuestion.countTotal();
		},
		savePages: function() {
			QSMAdmin.displayAlert( 'Saving pages and questions...', 'info' );
			var pages = [];

			// Cycles through each page and add page + questions to pages variable
			_.each( jQuery( '.page' ), function( page ) {

				// If page is empty, do not add it.
				if( 0 == jQuery( page ).children( '.question' ).length ) {
					return;
				}
				var singlePage = [];
				// Cycle through each question and add to the page.
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
				error: QSMAdmin.displayjQueryError
			});
		},
		savePagesSuccess: function() {
			QSMAdmin.displayAlert( 'Questions and pages were saved!', 'success' );
		},
		addNewPage: function() {
			var template = wp.template( 'page' );
			$( '.questions' ).append( template() );
			$( '.page' ).sortable({
				items: '.question',
				opacity: 70,
				cursor: 'move',
				placeholder: "ui-state-highlight",
				connectWith: '.page'
			});
			setTimeout( QSMQuestion.removeNew, 250 );
		},
		addNewQuestion: function( model ) {
			QSMAdmin.displayAlert( 'Question created!', 'success' );
			QSMQuestion.addQuestionToPage( model );
			QSMQuestion.openEditPopup( model.id );
			QSMQuestion.countTotal();
		},
		addQuestionToPage: function( model ) {
			var page = model.get( 'page' ) + 1;
			var template = wp.template( 'question' );
			var page_exists = $( '.page:nth-child(' + page + ')' ).length;
			var count = 0;
			while ( ! page_exists ) {
				QSMQuestion.addNewPage();
				page_exists = $( '.page:nth-child(' + page + ')' ).length;
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
			QSMAdmin.displayAlert( 'Creating question...', 'info' );
			QSMQuestion.questions.create( 
				{ 
					quizID: qsmQuestionSettings.quizID,
					page: page
				},
				{
					headers: { 'X-WP-Nonce': qsmQuestionSettings.nonce },
					success: QSMQuestion.addNewQuestion,
					error: QSMAdmin.displayError
				}
			);
		},
		duplicateQuestion: function( questionID ) {
			QSMAdmin.displayAlert( 'Duplicating question...', 'info' );
			var model = QSMQuestion.questions.get( questionID );
			var newModel = _.clone(model.attributes);
			newModel.id = null;
			QSMQuestion.questions.create( 
				newModel,
				{
					headers: { 'X-WP-Nonce': qsmQuestionSettings.nonce },
					success: QSMQuestion.addNewQuestion,
					error: QSMAdmin.displayError
				}
			);
		},
		saveQuestion: function( questionID ) {
			QSMAdmin.displayAlert( 'Saving question...', 'info' );
			var model = QSMQuestion.questions.get( questionID );
			var hint = $( '#hint' ).val();
			var name = wp.editor.getContent( 'question-text' );
                        if(name == ''){
                            alert('Enter question title');
                            return false;
                        }
			var answerInfo = $( '#correct_answer_info' ).val();
			var type = $( "#question_type" ).val();
			var comments = $( "#comments" ).val();
			var required = $( "#required" ).val();
			var category = $( ".category-radio:checked" ).val();
                        var autofill =  $( "#hide_autofill" ).val();
                        var limit_text =  $( "#limit_text" ).val();
                        var limit_multiple_response =  $( "#limit_multiple_response" ).val();
                        var file_upload_limit =  $( "#file_upload_limit" ).val();
                        var type_arr = [];
                        $.each($("input[name='file_upload_type[]']:checked"), function(){
                            type_arr.push($(this).val());
                        });
			if ( 'new_category' == category ) {
				category = $( '#new_category' ).val();
			}
			if ( ! category ) {
				category = '';
			}
                        var answerType = $('#change-answer-editor').val();
			var answers = [];
			var $answers = jQuery( '.answers-single');
			_.each( $answers, function( answer ) {
				var $answer = jQuery( answer );
                                var answer = '';
                                if(answerType == 'rich'){
                                    var ta_id = $answer.find('textarea').attr('id')
                                    answer = wp.editor.getContent( ta_id );                                    
                                }else{
                                    answer = $answer.find( '.answer-text' ).val();
                                }
				
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
                                        answerEditor: answerType,
                                        autofill: autofill,
                                        limit_text: limit_text,
                                        limit_multiple_response: limit_multiple_response,
                                        file_upload_limit: file_upload_limit,
                                        file_upload_type: type_arr.join(","),
				}, 
				{ 
					headers: { 'X-WP-Nonce': qsmQuestionSettings.nonce },
					success: QSMQuestion.saveSuccess,
					error: QSMAdmin.displayError,
					type: 'POST'
				} 
			);
			MicroModal.close('modal-1');
		},
		saveSuccess: function( model ) {
			QSMAdmin.displayAlert( 'Question was saved!', 'success' );
			var template = wp.template( 'question' );
			var page = model.get( 'page' ) + 1;
			$( '.question[data-question-id=' + model.id + ']' ).replaceWith( template( { id: model.id, type : model.get('type'), category : model.get('category'), question: model.get('name') } ) );
			setTimeout( QSMQuestion.removeNew, 250 );
		},
		addNewAnswer: function( answer ) {
                        
			var answerTemplate = wp.template( 'single-answer' );                        
			$( '#answers' ).append( answerTemplate( { answer: decodeEntities( answer[0] ), points: answer[1], correct: answer[2], count: answer[3], question_id: answer[4], answerType: answer[5] } ) );
                        if(answer[5] == 'rich' && qsmQuestionSettings.qsm_user_ve === 'true'){
                            var textarea_id = 'answer-' + answer[4] + '-' + answer[3];
                            wp.editor.remove( textarea_id );
                            var settings = {
                                mediaButtons: true,
                                tinymce:      {
                                        forced_root_block : '',
                                        toolbar1: 'formatselect,bold,italic,bullist,numlist,blockquote,alignleft,aligncenter,alignright,link,strikethrough,hr,forecolor,pastetext,removeformat,codeformat,charmap,undo,redo'
                                },
                                quicktags:    true,
                            };
                            wp.editor.initialize( textarea_id, settings );
                            var anser = QSMQuestion.prepareQuestionText(answer[0]);
                            $( textarea_id ).val(anser);
                            tinyMCE.get( textarea_id ).setContent( anser );
                        }
		},
		openEditPopup: function( questionID ) {
			QSMQuestion.prepareCategories();
			QSMQuestion.processCategories();
			var question = QSMQuestion.questions.get( questionID );                        
			var questionText = QSMQuestion.prepareQuestionText( question.get( 'name' ) );
			$( '#edit_question_id' ).val( questionID );
                        var question_editor = ''
                        if(qsmQuestionSettings.qsm_user_ve === 'true'){
                            question_editor = tinyMCE.get( 'question-text' );
                        }			
			if ($('#wp-question-text-wrap').hasClass('html-active')) {
				jQuery( "#question-text" ).val( questionText );
			} else if ( question_editor ) {
				tinyMCE.get( 'question-text' ).setContent( questionText );
			} else {
				jQuery( "#question-text" ).val( questionText );
			}

			$( '#answers' ).empty();
			var answers = question.get( 'answers' );
                        var answerEditor = question.get( 'answerEditor' );                        
                        if( answerEditor === null || typeof answerEditor === "undefined" ){
                            answerEditor = 'text';
                        }
                        //Check autofill setting
                        var disableAutofill = question.get( 'autofill' );
                        if( disableAutofill === null || typeof disableAutofill === "undefined" ){
                            disableAutofill = '0';
                        }
                        //Get text limit value
                        var get_limit_text = question.get( 'limit_text' );                        
                        if( get_limit_text === null || typeof get_limit_text === "undefined" ){
                            get_limit_text = '0';
                        }
                        //Get limit multiple response value
                        var get_limit_mr = question.get( 'limit_multiple_response' );
                        if( get_limit_mr === null || typeof get_limit_mr === "undefined" ){
                            get_limit_mr = '0';
                        }
                        //Get file upload limit
                        var get_limit_fu = question.get( 'file_upload_limit' );
                        if( get_limit_fu === null || typeof get_limit_fu === "undefined" ){
                            get_limit_fu = '0';
                        }
                        //Get checked question type
                        var get_file_upload_type = question.get( 'file_upload_type' );
                        $("input[name='file_upload_type[]']:checkbox").attr("checked",false);
                        if( get_file_upload_type === null || typeof get_file_upload_type === "undefined" ){                            
                        }else{
                            var fut_arr = get_file_upload_type.split(",");
                            $.each(fut_arr,function(i){
                                $("input[name='file_upload_type[]']:checkbox[value='"+ fut_arr[i] +"']").attr("checked","true");
                            });
                        }
                        var al = 0;
			_.each( answers, function( answer ) {
                            answer.push(al + 1);
                            answer.push(questionID);
                            answer.push(answerEditor);
                            QSMQuestion.addNewAnswer( answer );
                            al++;
			});
                        //Hide the question settings based on question type
                        if(question.get( 'type' ) == 11){
                            jQuery('#file-upload-type-div').show();
                            jQuery('#file-upload-limit').show();
                        }else{
                            jQuery('#file-upload-type-div').hide();
                            jQuery('#file-upload-limit').hide();
                        }
			$( '#hint' ).val( question.get( 'hint' ) );
			$( '#correct_answer_info' ).val( question.get( 'answerInfo' ) );
			$( "#question_type" ).val( question.get( 'type' ) );
			$( "#comments" ).val( question.get( 'comments' ) );
			$( "#required" ).val( question.get( 'required' ) );
			$( "#hide_autofill" ).val( disableAutofill );
			$( "#limit_text" ).val( get_limit_text );
			$( "#limit_multiple_response" ).val( get_limit_mr );
			$( "#file_upload_limit" ).val( get_limit_fu );
			$( "#change-answer-editor" ).val( answerEditor );
			$( ".category-radio" ).removeAttr( 'checked' );
			$( "#edit-question-id" ).text('').text(questionID);
			if ( 0 !== question.get( 'category' ).length ) {
				$( ".category-radio" ).val( [question.get( 'category' )] );
			}
			MicroModal.show( 'modal-1' );
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
					forced_root_block : '',
					toolbar1: 'formatselect,bold,italic,bullist,numlist,blockquote,alignleft,aligncenter,alignright,link,strikethrough,hr,forecolor,pastetext,removeformat,codeformat,charmap,undo,redo'
				},
				quicktags:    true,
			};
			wp.editor.initialize( 'question-text', settings );
		}
	};

	$(function() {
		QSMQuestion.questionCollection = Backbone.Collection.extend({
			url: wpApiSettings.root + 'quiz-survey-master/v1/questions',
			model: QSMQuestion.question
		});
		QSMQuestion.questions = new QSMQuestion.questionCollection();
		$( '.new-page-button' ).on( 'click', function( event ) {
			event.preventDefault();
			QSMQuestion.addNewPage();
		});

		$( '.questions' ).on( 'click', '.new-question-button', function( event ) {
			event.preventDefault();
			QSMQuestion.createQuestion( $( this ).parents( '.page' ).index() );
		});
		
		$( '.questions' ).on( 'click', '.add-question-bank-button', function( event ) {
			event.preventDefault();
			QSMQuestion.openQuestionBank( $( this ).parents( '.page' ).index() );
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
			QSMQuestion.countTotal();
                        $('.save-page-button').trigger('click');
		});
		$( '.questions' ).on( 'click', '.delete-page-button', function( event ) {
			event.preventDefault();
			$( this ).parents( '.page' ).remove();
		});
		$( '#answers' ).on( 'click', '.delete-answer-button', function( event ) {
			event.preventDefault();
			$( this ).parents( '.answers-single' ).remove();
		});
		$( '#save-popup-button' ).on( 'click', function( event ) {
			event.preventDefault();
			QSMQuestion.saveQuestion( $( this ).parent().siblings( 'main' ).children( '#edit_question_id' ).val() );
                        $('.save-page-button').trigger('click');
		});
		$( '#new-answer-button' ).on( 'click', function( event ) {
			event.preventDefault();
                        var answer_length = $( '#answers' ).find('.answers-single').length;
                        var question_id = $('#edit_question_id').val();
                        var answerType = $('#change-answer-editor').val();
			var answer = [ '', '', 0, answer_length + 1, question_id, answerType];
			QSMQuestion.addNewAnswer( answer );                        
		});
                
		$( '.qsm-popup-bank' ).on( 'click', '.import-button', function( event) {
			event.preventDefault();
                        $(this).text('').text('Adding Question');                        
                        import_button = $(this);
			QSMQuestion.addQuestionFromQuestionBank( $( this ).parents( '.question-bank-question' ).data( 'question-id' ) );
                        $('.import-button').addClass('disable_import');
		});

		$( '.save-page-button' ).on( 'click', function( event ) {
			event.preventDefault();
			QSMQuestion.savePages();
		});
                
                $( document ).on( 'change', '#change-answer-editor', function( event ) {                    
                    var newVal = $(this).val();
                    if(confirm('All answer will be reset, Do you want to still continue?')){
                        $('#answers').find( '.answers-single' ).remove();
                    }else{
                        if(newVal == 'rich'){
                            $(this).val('text');
                        }else{
                            $(this).val('rich');
                        }   
                        return false;
                    }
		});
                
		// Adds event handlers for searching questions
		$( '#question_search' ).on( 'keyup', function() {
			$( '.question' ).each(function() {
				if ( $(this).text().toLowerCase().indexOf( $( '#question_search' ).val().toLowerCase()) === -1 ) {
					$(this).hide();
				} else {
					$(this).show();
				}
			});
			$( '.page' ).each(function() {
				if ( 0 === $(this).children( '.question:visible' ).length ) {
					$(this).hide();
				} else {
					$(this).show();
				}
			});
			if ( 0 === $( '#question_search' ).val().length ) {
				$( '.page' ).show();
				$( '.question' ).show();
			}
		});

		$( '.questions' ).sortable({
			opacity: 70,
			cursor: 'move',
			placeholder: "ui-state-highlight"
		});
		$( '.page' ).sortable({
			items: '.question',
			opacity: 70,
			cursor: 'move',
			placeholder: "ui-state-highlight",
			connectWith: '.page'
		});
                if(qsmQuestionSettings.qsm_user_ve === 'true'){
                    QSMQuestion.prepareEditor();
                }		
		QSMQuestion.loadQuestions();
                
                /**
                 * Hide/show advanced option
                 */
                $(document).on('click','#show-advanced-option',function(){
                    var $this = $(this);
                    $(this).next('div.advanced-content').slideToggle('slow',function(){
                        if ($(this).is(':visible')) {
                            $this.text('').html('Hide advance options &laquo;');                
                        } else {
                            $this.text('').html('Show advance options &raquo;');
                        }  
                    });
                });
                $(document).on('change','#question-bank-cat', function(){
                    var val = $(this).val();
                    if(val == ''){
                        $('.question-bank-question').show();
                    }else{
                        $('.question-bank-question').each(function (){
                            if($(this).attr("data-category-name") == val){
                                $(this).show();
                            }else{
                                $(this).hide();
                            }
                        });
                    }                    
                });
                //Hide the question settings based on question type
                $(document).on('change','#question_type', function(){
                    var question_val = $(this).val();
                    if(question_val == 11){
                        jQuery('#file-upload-type-div').show();
                        jQuery('#file-upload-limit').show();
                    }else{
                        jQuery('#file-upload-type-div').hide();
                        jQuery('#file-upload-limit').hide();
                    }
                });                
	});
        var decodeEntities = (function () {
                //create a new html document (doesn't execute script tags in child elements)
                var doc = document.implementation.createHTMLDocument("");
                var element = doc.createElement('div');

                function getText(str) {
                    element.innerHTML = str;
                    str = element.textContent;
                    element.textContent = '';
                    return str;
                }

                function decodeHTMLEntities(str) {
                    if (str && typeof str === 'string') {
                        var x = getText(str);
                        while (str !== x) {
                            str = x;
                            x = getText(x);
                        }
                        return x;
                    }
                }
                return decodeHTMLEntities;
            })();
}(jQuery));
