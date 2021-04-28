/**
 * QSM Question Tab
 */

var QSMQuestion;
var import_button;
(function ($) {
        $.QSMSanitize = function(input) {        
            return input.replace(/<(|\/|[^>\/bi]|\/[^>bi]|[^\/>][^>]+|\/[^>][^>]+)>/g, '');
        };
	QSMQuestion = {
		question: Backbone.Model.extend({
			defaults: {
				id: null,
				quizID: 1,
				type: '0',
				name: '',
				question_title: '',
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
		page: Backbone.Model.extend({
			defaults: {
				id: null,
				quizID: 1,
				pagekey: qsmRandomID(8),
				hide_prevbtn: 0,
				questions: null,
			}
		}),
		qpages: null,
		questionCollection: null,
		pageCollection: null,
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
			MicroModal.show( 'modal-2',{
                            onClose: function(){                                
                                $('.save-page-button').trigger('click');
                            }
                        });
		},
		loadQuestionBank: function( action = '' ) {
                        if( action == 'change' ){
                            $( '.qb-load-more-wrapper' ).remove();
                            $( '#question-bank' ).find( '.question-bank-question' ).remove();
                            $( '#question-bank' ).append( '<div style="top: 45px;position: relative;" class="qsm-spinner-loader"></div>' );
                        }else if($('.qb-load-more-wrapper').length > 0){
                            $( '.qb-load-more-question' ).hide();
                            $( '.qb-load-more-wrapper' ).append( '<div class="qsm-spinner-loader"></div>' );
                        }else{
                            $( '#question-bank' ).empty();
                            $( '#question-bank' ).append( '<div class="qsm-spinner-loader"></div>' );
                        }			
			$.ajax( {
				url: wpApiSettings.root + 'quiz-survey-master/v1/bank_questions/0/',
				method: 'GET',
				beforeSend: function ( xhr ) {
					xhr.setRequestHeader( 'X-WP-Nonce', qsmQuestionSettings.nonce );
				},
				data: {
					'quizID' : 0,
                                        'page' : $('#question_back_page_number').length > 0 ? parseInt( $('#question_back_page_number').val() ) + 1 : 1,
                                        'category' : $('#question-bank-cat').val()
				},
				success: QSMQuestion.questionBankLoadSuccess
			});
		},
		questionBankLoadSuccess: function( questions ) {
                        var pagination = questions.pagination;
                        var questions = questions.questions;
                        if($('.qb-load-more-wrapper').length > 0){
                            $('.qb-load-more-wrapper').remove();
                        }else{
                            $( '#question-bank' ).empty();
                        }                        
			for ( var i = 0; i < questions.length; i++) {
				QSMQuestion.addQuestionToQuestionBank( questions[i] );                                
			}
                        if( pagination.total_pages > pagination.current_page){
                            var pagination_html = '<div class="qb-load-more-wrapper" style="text-align: center;margin: 20px 0 10px 0;"><input type="hidden" id="question_back_page_number" value="' + pagination.current_page + '"/>';
                            pagination_html += '<input type="hidden" id="question_back_total_pages" value="'+ pagination.total_pages +'"/>';
                            pagination_html += '<a href="#" class="button button-primary qb-load-more-question">Load More Questions</a></div>';
                            $( '#question-bank' ).append( pagination_html );
                        }                        
                        if(pagination.current_page == 1 && qsmQuestionSettings.categories.length > 0){
                            var category_arr = qsmQuestionSettings.categories;                            
                            $cat_html = '<select name="question-bank-cat" id="question-bank-cat">';
                            $cat_html += '<option value="">All Questions</option>';
                            $.each(category_arr, function(index, value){
                                if(value.category !== '')
                                    $cat_html += '<option value="'+ value.category +'">'+ value.category +' Questions</option>';
                            });
                            $cat_html += '</select>';
                            $( '#question-bank' ).prepend($cat_html);
                            $('#question-bank-cat').val(pagination.category);
                        }
                        if(pagination.current_page == 1){                            
                            $( '#question-bank' ).prepend('<button class="button button-primary" id="qsm-import-selected-question">Import All Selected Questions</button>');
                            $( '#question-bank' ).prepend('<button class="button button-default" id="qsm-delete-selected-question">Delete Selected Question from Bank</button>');
                            $( '#question-bank' ).prepend('<label class="qsm-select-all-label"><input type="checkbox" id="qsm_select_all_question" /> Select All Question</button>');
                        }                        
		},
		addQuestionToQuestionBank: function( question ) {                    
			var questionText = QSMQuestion.prepareQuestionText( question.name );
			var template = wp.template( 'single-question-bank-question' );
                        if( question.question_title !== "undefined" && question.question_title !== "" ){
                            questionText = question.question_title;
                        }    
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
			$('.qsm-showing-loader').remove();
			var question;
			_.each(qsmQuestionSettings.qpages, function( page ){
				QSMQuestion.qpages.add(page);
			});
			if ( qsmQuestionSettings.pages.length > 0 ) {
				for ( var i = 0; i < qsmQuestionSettings.pages.length; i++ ) {
					for ( var j = 0; j < qsmQuestionSettings.pages[ i ].length; j++ ) {
						question = QSMQuestion.questions.get( qsmQuestionSettings.pages[ i ][ j ] );
						QSMQuestion.addQuestionToPage( question );
					}
				}
			} else {
                            //We have removed this code in  7.0.0 because not allow to delete the single page.
                            QSMQuestion.questions.each( QSMQuestion.addQuestionToPage );
			}
                        //Create Default pages and one question.
                        if( qsmQuestionSettings.pages.length == 0 && QSMQuestion.questions.length == 0){
                            $('.new-page-button').trigger('click');
                            $('.questions .new-question-button').trigger('click');
                        }
			QSMQuestion.countTotal();
		},
		updateQPage: function(pageID) {
			QSMAdmin.displayAlert( 'Saving page info', 'info' );
			var pageInfo = QSMQuestion.qpages.get(pageID);
			jQuery('#page-options').find(':input, select, textarea').each(function(i, field){
				pageInfo.set(field.name, field.value);
			});
		},
		savePages: function() {
			QSMAdmin.displayAlert( 'Saving pages and questions...', 'info' );
			var pages = [];
			var qpages = [];
			var pageInfo = null;

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
				/**
				 * Prepare qpages Object
				 */
				pageInfo = QSMQuestion.qpages.get(jQuery( page ).data('page-id'));
				pageInfo.set('questions', singlePage);
				qpages.push(pageInfo.attributes);
			});
                        console.log(pages);
			var data = {
				action: 'qsm_save_pages',
				pages: pages,
				qpages: qpages,
				quiz_id : qsmQuestionSettings.quizID,
				nonce : qsmQuestionSettings.saveNonce,
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
			$('#save-edit-quiz-pages').removeClass('is-active');
		},
		addNewPage: function(pageID) {
			var template = wp.template( 'page' );
			if (typeof pageID == 'undefined' || pageID == '') {
				var newPageID = QSMQuestion.qpages.length + 1;
				var pageID = newPageID;
				var pageInfo = QSMQuestion.qpages.add({id: newPageID, quizID: qsmQuestionSettings.quizID, pagekey: qsmRandomID(8), hide_prevbtn: 0});
			}
			var pageInfo = QSMQuestion.qpages.get(pageID);
			$( '.questions' ).append( template(pageInfo) );
			var page = $( '.questions' ).find('.page').length;
			$('.page:nth-child(' + page + ')').find('.page-number').text('Page ' + page);
			$( '.page' ).sortable({
				items: '.question',
				opacity: 70,
				cursor: 'move',
				placeholder: "ui-state-highlight",
				connectWith: '.page',
				stop: function(evt, ui) {
					setTimeout(
						function(){
							$('.save-page-button').trigger('click');
						},
						200
					)
				}
			});
			setTimeout( QSMQuestion.removeNew, 250 );
		},
		addNewQuestion: function( model ) {
			QSMAdmin.displayAlert( 'Question created!', 'success' );
			QSMQuestion.addQuestionToPage( model );                        
			QSMQuestion.openEditPopup( model.id, $( '.question[data-question-id=' + model.id + ']' ).find('.edit-question-button') );
			QSMQuestion.countTotal();
                        if( $('#answers').find('.answers-single').length == 0  ){
                            $('#new-answer-button').trigger('click');
                        }                        
		},
		addQuestionToPage: function( model ) {
			var page = model.get( 'page' ) + 1;
			var template = wp.template( 'question' );
			var page_exists = $( '.page:nth-child(' + page + ')' ).length;
			var count = 0;
			while ( ! page_exists ) {
				QSMQuestion.addNewPage(page);                                
				page_exists = $( '.page:nth-child(' + page + ')' ).length;
				count++;
				if ( count > 5 ) {
					page_exists = true;
					console.log( 'count reached' );
				}
			}
                        var questionName = QSMQuestion.prepareQuestionText( model.get( 'name' ) );                        
                        var new_question_title = model.get('question_title');
                        if( new_question_title === null || typeof new_question_title === "undefined" || new_question_title === "" ){
                            //Do nothing
                        }else{
                            questionName = new_question_title;
                        }
                        
                        if( questionName == '' )
                            questionName = 'Your new question!';
                        
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
		saveQuestion: function( questionID, CurrentElement ) {
			QSMAdmin.displayAlert('Saving question...', 'info');
			var model = QSMQuestion.questions.get(questionID);
			var hint = $('#hint').val();
			var name = wp.editor.getContent('question-text');
			//Save new question title
			var question_title = $('#question_title').val();
			if (name == '' && question_title == '') {
				alert('Enter Question title or description');
				return false;
			}
			var advanced_option = {};
			var answerInfo = wp.editor.getContent('correct_answer_info');
			var type = $("#question_type").val();
			var comments = $("#comments").val();
			advanced_option['required'] = $(".questionElements input[name='required']").is(":checked") ? 0 : 1;
			var category = $(".category-radio:checked").val();
			var autofill = $("#hide_autofill").val();
			var limit_text = $("#limit_text").val();
			var limit_multiple_response = $("#limit_multiple_response").val();
			var file_upload_limit = $("#file_upload_limit").val();
			var type_arr = [];
			$.each($("input[name='file_upload_type[]']:checked"), function () {
				type_arr.push($(this).val());
			});
			if ('new_category' == category) {
				category = $('#new_category').val();
			}
			if (!category) {
				category = '';
			}
			var answerType = $('#change-answer-editor').val();
			var answers = [];
			var $answers = jQuery('.answers-single');
			_.each($answers, function (answer) {
				var $answer = jQuery(answer);
				var answer = '';
				if (answerType == 'rich') {
					var ta_id = $answer.find('textarea').attr('id')
					answer = wp.editor.getContent(ta_id);
				} else {
					answer = $answer.find('.answer-text').val().trim();
					answer = $.QSMSanitize(answer);
				}

				var points = $answer.find('.answer-points').val();
				var correct = 0;
				if ($answer.find('.answer-correct').prop('checked')) {
					correct = 1;
				}
				answers.push([answer, points, correct]);
			});
			$('.questionElements .advanced-content > .qsm-row ').each(function () {
				if ($(this).find('input[type="text"]').length > 0) {
					var element_id = $(this).find('input[type="text"]').attr('id');
					advanced_option[element_id] = $(this).find('input[type="text"]').val();
				} else if ($(this).find('input[type="number"]').length > 0) {
					var element_id = $(this).find('input[type="number"]').attr('id');
					advanced_option[element_id] = $(this).find('input[type="number"]').val();
				} else if ($(this).find('select').length > 0) {
					var element_id = $(this).find('select').attr('id');
					advanced_option[element_id] = $(this).find('select').val();
				} else if ($(this).find('input[type="checkbox"]').length > 0) {
					var element_id = $(this).find('input[type="checkbox"]').attr('name');
					var multi_value = $(this).find('input[type="checkbox"]:checked').map(function () {
						return this.value;
					}).get().join(',');
					element_id = element_id.replace('[]', '');
					advanced_option[element_id] = multi_value;
				}
			});
			model.save({
				type: type,
				name: name,
				question_title: question_title,
				answerInfo: answerInfo,
				comments: comments,
				hint: hint,
				category: category,
				answers: answers,
				answerEditor: answerType,
				other_settings: advanced_option
			}, {
				headers: {'X-WP-Nonce': qsmQuestionSettings.nonce},
				success: QSMQuestion.saveSuccess,
				error: QSMAdmin.displayError,
				type: 'POST'
			});
			//CurrentElement.parents('.questionElements').slideUp('slow');                        
		},
		saveSuccess: function( model ) {                        
			QSMAdmin.displayAlert( 'Question was saved!', 'success' );
			var template = wp.template( 'question' );
			var page = model.get( 'page' ) + 1;
                        var questionName = model.get('name');
                        var new_question_title = model.get('question_title');                        
                        if( new_question_title !== '' ){
                            questionName = $.QSMSanitize(new_question_title);
                        }
			$( '.question[data-question-id=' + model.id + ']' ).replaceWith( template( { id: model.id, type : model.get('type'), category : model.get('category'), question: questionName } ) );
			setTimeout(function () {
				$('#save-edit-question-spinner').removeClass('is-active');
			}, 250);
			setTimeout(QSMQuestion.removeNew, 250);
		},
		addNewAnswer: function( answer ) {                        
			var answerTemplate = wp.template( 'single-answer' );                        
			$( '#answers' ).append( answerTemplate( { answer: decodeEntities( answer[0] ), points: answer[1], correct: answer[2], count: answer[3], question_id: answer[4], answerType: answer[5], form_type: qsmQuestionSettings.form_type, quiz_system: qsmQuestionSettings.quiz_system } ) );
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
		openEditPopup: function (questionID, CurrentElement) {
			if (CurrentElement.parents('.question').next('.questionElements').length > 0) {
				if (CurrentElement.parents('.question').next('.questionElements').is(":visible")) {
					CurrentElement.parents('.question').next('.questionElements').slideUp('slow');
					$('.questions').sortable('enable');
					$('.page').sortable('enable');
				} else {
					CurrentElement.parents('.question').next('.questionElements').slideDown('slow');
				}
				return;
			} else {
				$('.questions .questionElements').slideDown('slow');
				$('.questions .questionElements').remove();
			}
			//Copy and remove popup div
			var questionElements = $('#modal-1-content').html();
			$('#modal-1-content').children().remove();
			CurrentElement.parents('.question').after("<div style='display: none;' class='questionElements'>" + questionElements + "</div>");

			//Show question id on question edit screen
			$('#qsm-question-id').text('ID: ' + questionID);
			QSMQuestion.prepareCategories();
			QSMQuestion.processCategories();
			var question = QSMQuestion.questions.get(questionID);
			var questionText = QSMQuestion.prepareQuestionText(question.get('name'));
			$('#edit_question_id').val(questionID);
			var answerInfo = question.get('answerInfo');
			var CAI_editor = '';
			var question_editor = ''
			if (qsmQuestionSettings.qsm_user_ve === 'true') {
				wp.editor.remove('question-text');
				wp.editor.remove('correct_answer_info');
				QSMQuestion.prepareEditor();
				question_editor = tinyMCE.get('question-text');
				CAI_editor = tinyMCE.get('correct_answer_info');
			}
			if ($('#wp-question-text-wrap').hasClass('html-active')) {
				jQuery("#question-text").val(questionText);
			} else if (question_editor) {
				tinyMCE.get('question-text').setContent(questionText);
			} else {
				jQuery("#question-text").val(questionText);
			}
			if ($('#wp-correct_answer_info-wrap').hasClass('html-active')) {
				jQuery("#correct_answer_info").val(answerInfo);
			} else if (CAI_editor) {
				tinyMCE.get('correct_answer_info').setContent(answerInfo);
			} else {
				jQuery("#correct_answer_info").val(answerInfo);
			}

			$('#answers').empty();
			var answers = question.get('answers');
			var answerEditor = question.get('answerEditor');
			if (answerEditor === null || typeof answerEditor === "undefined") {
				answerEditor = 'text';
			}
			//Check autofill setting
			var disableAutofill = question.get('autofill');
			if (disableAutofill === null || typeof disableAutofill === "undefined") {
				disableAutofill = '0';
			}
			//Get text limit value
			var get_limit_text = question.get('limit_text');
			if (get_limit_text === null || typeof get_limit_text === "undefined") {
				get_limit_text = '0';
			}
			//Get limit multiple response value
			var get_limit_mr = question.get('limit_multiple_response');
			if (get_limit_mr === null || typeof get_limit_mr === "undefined") {
				get_limit_mr = '0';
			}
			//Get file upload limit
			var get_limit_fu = question.get('file_upload_limit');
			if (get_limit_fu === null || typeof get_limit_fu === "undefined") {
				get_limit_fu = '0';
			}
			//Get checked question type
			var get_file_upload_type = question.get('file_upload_type');
			$("input[name='file_upload_type[]']:checkbox").attr("checked", false);
			if (get_file_upload_type === null || typeof get_file_upload_type === "undefined") {
			} else {
				var fut_arr = get_file_upload_type.split(",");
				$.each(fut_arr, function (i) {
					$("input[name='file_upload_type[]']:checkbox[value='" + fut_arr[i] + "']").attr("checked", "true");
				});
			}
			var al = 0;
			_.each(answers, function (answer) {
				answer.push(al + 1);
				answer.push(questionID);
				answer.push(answerEditor);
				QSMQuestion.addNewAnswer(answer);
				al++;
			});
			//get new question type
			var get_question_title = question.get('question_title');
			if (get_question_title === null || typeof get_question_title === "undefined") {
				get_question_title = '';
			}

			//Hide the question settings based on question type
			$('.qsm_hide_for_other').hide();
			if ($('.qsm_show_question_type_' + question.get('type')).length > 0) {
				$('.qsm_show_question_type_' + question.get('type')).show();
			}
			qsm_hide_show_question_desc(question.get('type'));
			$('#hint').val(question.get('hint'));
			$("#question_type").val(question.get('type'));
			$("#comments").val(question.get('comments'));
			//Changed checked logic based on new structure for required.
			$("input#required[value='" + question.get('required') + "']").prop('checked', true);

			$("#hide_autofill").val(disableAutofill);
			$("#limit_text").val(get_limit_text);
			$("#limit_multiple_response").val(get_limit_mr);
			$("#file_upload_limit").val(get_limit_fu);
			$("#change-answer-editor").val(answerEditor);
			$(".category-radio").removeAttr('checked');
			$("#edit-question-id").text('').text(questionID);
			$("#question_title").val(get_question_title);
			if (0 !== question.get('category').length) {
				$(".category-radio").val([question.get('category')]);
			}
			//Append extra settings
			var all_setting = question.get('settings');
			if (all_setting === null || typeof all_setting === "undefined") {
			} else {
				$.each(all_setting, function (index, value) {
					if ($('#' + index + '_area').length > 0) {
						if ($('#' + index + '_area').find('input[type="checkbox"]').length > 1) {
							var fut_arr = value.split(",");
							$.each(fut_arr, function (i) {
								$(".questionElements input[name='" + index + "[]']:checkbox[value='" + fut_arr[i] + "']").attr("checked", "true").prop('checked', true);
							});
						} else {
							if (value != null)
								$('#' + index).val(value);
						}
					}
				});
			}
			CurrentElement.parents('.question').next('.questionElements').slideDown('slow');
			$('#modal-1-content').html(questionElements);
			//MicroModal.show( 'modal-1' );
			$('.questions').sortable('disable');
			$('.page').sortable('disable');
		},
		openEditPagePopup: function( pageID ) {
			var page = QSMQuestion.qpages.get(pageID);
			$('#edit_page_id').val(pageID);
			$("#edit-page-id").text('').text(pageID);
			jQuery('#page-options').find(':input, select, textarea').each(function(i, field){
				field.value = page.get(field.name);
			});
			MicroModal.show('modal-page-1');
		},
		removeNew: function() {
			$( '.page-new' ).removeClass( 'page-new' );
			$( '.question-new' ).removeClass( 'question-new' );
		},
		prepareQuestionText: function( question ) {
			return jQuery('<textarea />').html( question ).text();
		},
		prepareEditor: function () {
			var settings = {
				mediaButtons: true,
				tinymce: {
					forced_root_block: '',
					toolbar1: 'formatselect,bold,italic,bullist,numlist,blockquote,alignleft,aligncenter,alignright,link,strikethrough,hr,forecolor,pastetext,removeformat,codeformat,charmap,undo,redo'
				},
				quicktags: true,
			};
			wp.editor.initialize('question-text', settings);
			wp.editor.initialize('correct_answer_info', settings);
		}
	};

	$(function() {
		QSMQuestion.pageCollection = Backbone.Collection.extend({model: QSMQuestion.page});
		QSMQuestion.qpages = new QSMQuestion.pageCollection();
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
                        $('.save-page-button').trigger('click');
		});
		
		$( '.questions' ).on( 'click', '.add-question-bank-button', function( event ) {
			event.preventDefault();
			QSMQuestion.openQuestionBank( $( this ).parents( '.page' ).index() );
		});
                
                //Show more question on load more
                $( document ).on( 'click', '.qb-load-more-question', function( event ) {
			event.preventDefault();
			QSMQuestion.loadQuestionBank();
		});
                
                //Show category related question
                $( document ).on( 'change', '#question-bank-cat', function( event ) {
			event.preventDefault();
			QSMQuestion.loadQuestionBank('change');
		});

		$( '.questions' ).on( 'click', '.edit-question-button', function( event ) {
			event.preventDefault();
			QSMQuestion.openEditPopup( $( this ).parents( '.question' ).data( 'question-id' ), $(this) );
		});
		$('.questions').on('click', '.edit-page-button', function (event) {
			event.preventDefault();
			QSMQuestion.openEditPagePopup($(this).parents('.page').data('page-id'));
		});

		$( document ).on( 'click', '.questions .duplicate-question-button', function( event ) {
			event.preventDefault();
			QSMQuestion.duplicateQuestion( $( this ).parents( '.question' ).data( 'question-id' ) );
                        $('.save-page-button').trigger('click');
		});
			$( '.questions' ).on( 'click', '.delete-question-button', function( event ) {
                        event.preventDefault();
						remove = $(this);
						// opens-up question-delete modal
						MicroModal.show('modal-7');
						$('#unlink-question-button').attr('data-question-iid', $(this).data('question-iid'));
						$('#delete-question-button').attr('data-question-iid', $(this).data('question-iid'));
						
						// removes question from database
						$('#delete-question-button').click(function(event){
							event.preventDefault();	
                        if( confirm('Are you sure?') ){
							 var question_id = $(this).data('question-iid');
							 console.log(question_id);
                                $.ajax( {
                                    url: ajaxurl,
                                    method: 'POST',
                                    data: {
                                        'action' : 'qsm_delete_question_from_database',
                                        'question_id': question_id,
                                        'nonce': qsmQuestionSettings.single_question_nonce
                                    },
                                    success: function(response) {
                                        var data = jQuery.parseJSON( response );
                                        if( data.success === true ){
                                
                                            console.log( data.message );
                                        } else {
                                           console.log( data.message );
                                        }
                                    }
                                } );
                            remove.parents( '.question' ).remove();
                            QSMQuestion.countTotal();
                            $('.save-page-button').trigger('click');
                        }
						MicroModal.close('modal-7');
						});

						// unlink question from  a particular quiz.
						$('#unlink-question-button').click(function(event){
							event.preventDefault();
                        if( confirm('Are you sure?') ){
							var question_id = $(this).data('question-iid');
							
							console.log(question_id);
                            remove.parents( '.question' ).remove();
                            QSMQuestion.countTotal();
                            $('.save-page-button').trigger('click');
                        }
						MicroModal.close('modal-7');
						});
		});
		$( '.questions' ).on( 'click', '.delete-page-button', function( event ) {
			event.preventDefault();
                        if( confirm('Are you sure?') ){
                            $( this ).parents( '.page' ).remove();
                            $('.save-page-button').trigger('click');
                        }
		});
		$( document ).on( 'click', '#answers .delete-answer-button', function( event ) {
			event.preventDefault();
			$( this ).parents( '.answers-single' ).remove();
		});
		$( document ).on( 'click', '#delete-action .deletion', function( event ) {
			event.preventDefault();
			$( this ).parents( '.questionElements' ).slideUp('slow');
		});
		$( document ).on( 'click', '#save-popup-button', function( event ) {
			event.preventDefault();
                        $('#save-edit-question-spinner').addClass('is-active');
                        var model_html = $('#modal-1-content').html();
                        $('#modal-1-content').children().remove();
			QSMQuestion.saveQuestion( $( this ).parents('.questionElements').children( '#edit_question_id' ).val(), $(this) );
                        $('.save-page-button').trigger('click');
                        $('#modal-1-content').html( model_html );                        
		});                
		$( document ).on( 'click', '#new-answer-button', function( event ) {
			event.preventDefault();
                        var answer_length = $( '#answers' ).find('.answers-single').length;
                        if( answer_length > 1 && $('#question_type').val() == 13 ){
                            alert('You can not add more than 2 answer for Polar Question type');
                            return;
                        }
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
                
                //Click on selected question button.
                $( '.qsm-popup-bank' ).on( 'click', '#qsm-import-selected-question', function( event) {
                    var $total_selction = $('#question-bank').find('[name="qsm-question-checkbox[]"]:checked').length;
                    if($total_selction === 0){
                        alert('No question is selected.');
                    }else{
                        $.fn.reverse = [].reverse;                    	
                        $($('#question-bank').find('[name="qsm-question-checkbox[]"]:checked').parents('.question-bank-question').reverse()).each(function(){
                            $(this).find('.import-button').text('').text('Adding Question');
                            import_button = $(this).find('.import-button');
                            QSMQuestion.addQuestionFromQuestionBank( $( this ).data( 'question-id' ) );
                            $(this).find('.import-button').text('').text('Add Question');
                        });
                        $('.import-button').addClass('disable_import');                        
                        $('#question-bank').find('[name="qsm-question-checkbox[]"]').attr('checked',false);
                    }
                });
                //Delete question from question bank
                $( '.qsm-popup-bank' ).on( 'click', '#qsm-delete-selected-question', function( event) {
                    if( confirm( 'are you sure?' ) ){
                        var $total_selction = $('#question-bank').find('[name="qsm-question-checkbox[]"]:checked').length;
                        if($total_selction === 0){
                            alert('No question is selected.');
                        }else{
                            $.fn.reverse = [].reverse;                    	
                            var question_ids = $($('#question-bank').find('[name="qsm-question-checkbox[]"]:checked').parents('.question-bank-question').reverse()).map(function() { 
                                return $( this ).data( 'question-id' ); 
                            }).get().join(',');
                            if( question_ids ){
                                $.ajax( {
                                    url: ajaxurl,
                                    method: 'POST',
                                    data: {
                                        'action' : 'qsm_delete_question_question_bank',
                                        'question_ids': question_ids,
                                        'nonce': qsmQuestionSettings.question_bank_nonce
                                    },
                                    success: function(response) {
                                        var data = jQuery.parseJSON( response );
                                        if( data.success === true ){
                                            $('#question-bank').find('[name="qsm-question-checkbox[]"]:checked').parents('.question-bank-question').slideUp('slow');
                                            alert( data.message );
                                        } else {
                                            alert( data.message );
                                        }
                                    }
                                } );
                            }
                        }
                    }
                });
                
                //Select all button.
                $( document ).on( 'change', '#qsm_select_all_question', function( event) {
                    $('.qsm-question-checkbox').prop('checked', jQuery('#qsm_select_all_question').prop('checked'));
                });

		$( '.save-page-button' ).on( 'click', function( event ) {
			event.preventDefault();
                        $('#save-edit-quiz-pages').addClass('is-active');
			QSMQuestion.savePages();
		});
		$('#save-page-popup-button').on('click', function (event) {
			event.preventDefault();
			var pageID = $(this).parent().siblings('main').children('#edit_page_id').val();
			var pageKey = jQuery('#pagekey').val();
			if (pageKey.replace(/^\s+|\s+$/g, "").length == 0) {
				alert('Page Name is required!');
				return false;
			} else if (null == pageKey.match(/^[A-Za-z0-9\-\s]+$/)) {
				alert('Please use only Alphanumeric characters.');
				return false;
			} else {
				QSMQuestion.updateQPage(pageID);
				QSMQuestion.savePages();
				MicroModal.close('modal-page-1');
			}
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

                qsm_init_sortable();
                
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
                
                //Hide the question settings based on question type
                $(document).on('change','#question_type', function(){
                    var question_val = $(this).val();
                    $('.qsm_hide_for_other').hide();
                    if( $('.qsm_show_question_type_' + question_val).length > 0 ){
                        $('.qsm_show_question_type_' + question_val).show();
                    }
                    qsm_hide_show_question_desc( question_val );                    
                });
                
                //Add new category
                $( document ).on('click', '#qsm-category-add-toggle', function(){
                    if( $( '#qsm-category-add' ).is(":visible") ){
                        $('.questionElements #new_category_new').attr('checked', false);
                        $( '#qsm-category-add' ).slideUp('slow');
                    }else{
                        $('.questionElements #new_category_new').attr('checked', true).prop('checked', 'checked');
                        $( '#qsm-category-add' ).slideDown('slow');
                    }
                });
                
                //Hide/show quesion description
                $( document ).on('click', '.qsm-show-question-desc-box', function(e){
                    e.preventDefault();
                    if( $(this).next('.qsm-row').is(':visible') ){
                        $(this).html('').html('<span class="dashicons dashicons-plus-alt2"></span> ' + qsmQuestionSettings.show_desc_text);
                        $(this).next('.qsm-row').slideUp();
                    }else{
                        $(this).hide();
                        var question_description = wp.editor.getContent( 'question-text' );
                        if( question_description == '' || question_description == null ){
                            tinyMCE.get( 'question-text' ).setContent( 'Add description here!' );
                        }
                        $(this).next('.qsm-row').slideDown();
                    }                    
                });

				//Hide/show correct answer info
                $( document ).on('click', '.qsm-show-correct-info-box', function(e){
                    e.preventDefault();
                    if( $(this).next('.qsm-row').is(':visible') ){
                        $(this).html('').html('<span class="dashicons dashicons-plus-alt2"></span> ' + qsmQuestionSettings.show_correct_info_text);
                        $(this).next('.qsm-row').slideUp();
                    }else{
                        $(this).hide();
                        $(this).next('.qsm-row').slideDown();
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
            
            function qsm_hide_show_question_desc(question_type){                
                $('.question-type-description').hide();
                if( $('#question_type_' + question_type + '_description').length > 0 ){
                    $('#question_type_' + question_type + '_description').show();
                }
            }
            
            function qsm_init_sortable(){
                $( '.questions' ).sortable({
			opacity: 70,
			cursor: 'move',
			placeholder: "ui-state-highlight",
                        stop: function(evt, ui) {                            
                            $('.questions > .page').each(function(){
                                var page = parseInt($(this).index()) + 1;
                                $(this).find('.page-number').text( 'Page ' + page );
                            });
                            setTimeout(
                                function(){
                                    $('.save-page-button').trigger('click');
                                },
                                200
                            )
                        }
		});
		$( '.page' ).sortable({
			items: '.question',
			opacity: 70,
			cursor: 'move',
			placeholder: "ui-state-highlight",
			connectWith: '.page',
                        stop: function(evt, ui) {
                            setTimeout(
                                function(){
                                    $('.save-page-button').trigger('click');
                                },
                                200
                            )
                        }
		});
            }
	function qsmRandomID(length) {
		var result = '';
		var characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
		var charactersLength = characters.length;
		for (var i = 0; i < length; i++) {
			result += characters.charAt(Math.floor(Math.random() * charactersLength));
		}
		return result;
	}
}(jQuery));
