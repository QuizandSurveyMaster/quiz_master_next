/**
 * QSM admin: the quiz Questions tab and the Question Bank page (QSMQuestion).
 *
 * Moved out of qsm-admin.js (handle qsm_admin_questions_js, depends on qsm_admin_js) so it loads only on those two
 * screens. QSM_Admin_Assets prints it right after qsm-admin.js, where this code used to run, so add-on scripts that
 * read or extend QSMQuestion when they load find it as before.
 */
var QSMQuestion;
var import_button;
var qsm_link_button;
var QSM_Quiz_Broadcast_Channel;
(function ($) {
    var is_question_bank_page = jQuery('body').hasClass('qsm_page_qsm_question_bank');
    if (jQuery('body').hasClass('admin_page_mlw_quiz_options') || is_question_bank_page) {
        if (is_question_bank_page || window.location.href.indexOf('&tab') == -1 || window.location.href.indexOf('tab=questions') > 0) {

            $.QSMSanitize = function (input) {
                return input.replace(/<(|\/|[^>\/bi]|\/[^>bi]|[^\/>][^>]+|\/[^>][^>]+)>/g, '');
            };
            QSM_Quiz_Broadcast_Channel = {
                channel: new BroadcastChannel('qsm_quiz_data_channel'),
                questionData: [], // Initialize as an array

                // Initialize the event listeners and set up channel
                init: function () {
                    this.setupChannelListener();
                },

                // Listen for messages on the channel
                setupChannelListener: function () {
                    this.channel.onmessage = (event) => {
                        const receivedData = event.data;
                        const { requestType, ...questions } = receivedData;
                        if (requestType == 'update') {
                            this.updateQuestionData(questions);
                        } else if (requestType == 'unlink') {
                            this.afterUpdateUnlinkedQuestion(questions);
                        }
                    };
                },

                insertQuestionToChannel: function (question) {
                    let linkQuizzesArray = Object.values(question.get('link_quizzes'));

                    let sendDataObject = {
                        [question.id]: {
                            merged_question: question.get('merged_question'),
                            link_quizzes: linkQuizzesArray
                        }
                    };
                    this.questionData.push(sendDataObject);
                },

                // Function to send the linked question data to other tabs
                sendQuestionData: function (questionID, mergedQuestion, linkedQuizzes = []) {
                    let sendDataObject = {
                        [questionID]: {
                            merged_question: mergedQuestion,
                            link_quizzes: linkedQuizzes
                        },
                        requestType: 'update'
                    };
                    // update channel with new question
                    this.questionData.push(sendDataObject);
                    this.channel.postMessage(sendDataObject);
                },

                updateQuestionData: function (data){
                    const receivedData = data;
                    for (let questonId in receivedData) {
                        const mergedQuestions = receivedData[questonId].merged_question;
                        const receivedQuizzes = receivedData[questonId].link_quizzes;
                        if (mergedQuestions && typeof mergedQuestions == 'string') {
                            // Check if there's a colon and extract the part after it
                            let idsArray = [];
                            if (mergedQuestions != '') {
                                idsArray = mergedQuestions.split(',').map(id => id.trim());
                            }
                            if (idsArray.length > 0) {
                                // Loop through each ID in idsArray
                                idsArray.forEach(eachId => {
                                    // Loop through this.questionData to find matches
                                    let currentquestionData = this.questionData;
                                    currentquestionData.forEach((item, index) => {
                                        // Check if the current item's key matches the current eachId
                                        if (item[eachId]) {
                                            let quizNamesToCheck = this.questionData[index][eachId].link_quizzes;
                                            receivedQuizzes.forEach(quizName => {
                                                if (!quizNamesToCheck.includes(quizName)) {
                                                    quizNamesToCheck.push(quizName); // Add the quiz name to the array
                                                }
                                            });
                                            quizNamesToCheck = quizNamesToCheck.filter(function(quizName) {
                                                return quizName !== jQuery(document).find('#edit_quiz_name').val();
                                            });
                                            // Update channel data
                                            this.questionData[index][eachId].link_quizzes = quizNamesToCheck;
                                            this.questionData[index][eachId].merged_question = mergedQuestions;
                                            // Update current quiz question data
                                            let currentInQuizQuestion = QSMQuestion.questions.get(eachId);
                                            currentInQuizQuestion.set('merged_question', mergedQuestions);
                                            currentInQuizQuestion.set('link_quizzes', quizNamesToCheck);
                                        }
                                    });
                                });
                            }
                        }
                    }
                    // Console log updated questionData
                    console.log("Updated quiz data:", this.questionData);
                },

                unlinkQuestionData: function (model, currentQuestionId) {
                    let mergedQuestionIdString = model.get('merged_question');
                    let mergedQuestionIdArray = mergedQuestionIdString ? mergedQuestionIdString.split(',') : [];
                    // Filter out the questionId
                    let removedDeletedQuestionArray = mergedQuestionIdArray.filter(id => id !== String(currentQuestionId));
                    // Create a new string from the filtered array
                    let removedDeletedQuestionString = removedDeletedQuestionArray.join(',');
                    let sendDataObject = {
                        [currentQuestionId]: {
                            merged_question: removedDeletedQuestionString,
                            link_quizzes: jQuery(document).find('#edit_quiz_name').val(),
                        },
                        requestType: 'unlink'
                    };
                    this.channel.postMessage(sendDataObject);
                    model.set('merged_question', '');
                    model.set('link_quizzes', []);
                },

                afterUpdateUnlinkedQuestion: function (data) {
                    const receivedData = data;
                    for (let questonId in receivedData) {
                        const mergedQuestions = receivedData[questonId].merged_question;
                        const singleQuizName = receivedData[questonId].link_quizzes;
                        this.questionData.forEach((item) => {
                            Object.keys(item).forEach(key => {
                                if (item[key] && item[key].merged_question && item[key].link_quizzes) {
									if (mergedQuestions.split(',').includes(key)) {
										item[key].merged_question = mergedQuestions;
										item[key].link_quizzes = item[key].link_quizzes.filter(quiz => quiz != singleQuizName);
                                        // Update current quiz question data
                                        let currentInQuizQuestion = QSMQuestion.questions.get(key);
                                        currentInQuizQuestion.set('merged_question', mergedQuestions);
                                        currentInQuizQuestion.set('link_quizzes', item[key].link_quizzes.filter(quiz => quiz != singleQuizName));
									}
                                }
                            });
                        });
                    }
                },
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
                        question_limit: 0,
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
                countTotal: function () {
                    var total = 0;

                    // Cycles through each page.
                    _.each(jQuery('.page'), function (page) {

                        // If page is empty, continue to the next.
                        if (0 == jQuery(page).children('.question').length) {
                            return;
                        }
                        // Cycle through each question and add to our total.
                        _.each(jQuery(page).children('.question'), function (question) {
                            total += 1;
                        });
                    });
                    $('#total-questions').text(total);
                },
                openQuestionBank: function (pageID) {
                    QSMQuestion.loadQuestionBank();
                    $('#add-question-bank-page').val(pageID);
                    MicroModal.show('modal-2');
                },
                // Kit bank popup (O-01): footer buttons work only with a selection, "Select all" follows the list.
                syncBankFooter: function () {
                    const $boxes = $('#question-bank .qsm-question-checkbox');
                    const checked = $boxes.filter(':checked').length;
                    $('#qsm-import-selected-question, #qsm-delete-selected-question').prop('disabled', 0 === checked);
                    // Link needs at least one checked question that isn't in this quiz already (its Link button is disabled then).
                    $('#qsm-link-selected-question').prop('disabled', 0 === $boxes.filter(':checked').closest('.question-bank-question').find('.link-question:not(.disabled)').length);
                    $('#qsm_select_all_question').prop('checked', $boxes.length > 0 && checked === $boxes.length);
                    const i18n = qsmQuestionSettings.board_i18n || {};
                    $('.qsm-ui-qb-shown').text(i18n.shown ? i18n.shown.replace('%d', $('#question-bank .question-bank-question').length) : '');
                },
                syncBankFilterCount: function () {
                    const active = $('#question-bank-cat, #question-bank-quiz, #question-bank-type').filter(function () {
                        return '' !== $(this).val() && null !== $(this).val();
                    }).length;
                    $('.qsm-ui-filter-count').text(active).prop('hidden', 0 === active);
                },
                loadQuestionBank: function (action = '') {
                    if (action == 'change') {
                        $('#question-bank').empty();
                        $('#question-bank').append('<div style="top: 70px;position: relative;left: calc(50% - 20px);" class="qsm-spinner-loader"></div>');
                    } else if ($('.qb-load-more-wrapper').length > 0) {
                        $('.qb-load-more-question').hide();
                        $('.qb-load-more-wrapper').append('<div class="qsm-spinner-loader"></div>');
                    } else {
                        $('#question-bank').empty();
                        $('#question-bank').append('<div style="top: 70px;position: relative;left: calc(50% - 20px);" class="qsm-spinner-loader"></div>');
                    }
                    $.ajax({
                        url: wpApiSettings.root + 'quiz-survey-master/v1/bank_questions/0/',
                        method: 'GET',
                        beforeSend: function (xhr) {
                            xhr.setRequestHeader('X-WP-Nonce', qsmQuestionSettings.nonce);
                        },
                        data: {
                            'quizID': $('#question-bank-quiz').val(),
                            'type': $('#question-bank-type').val(),
                            'page': $('#question_back_page_number').length > 0 ? parseInt($('#question_back_page_number').val()) + 1 : 1,
                            'category': $('#question-bank-cat').val(),
                            'search': $('#question-bank-search-input').val()
                        },
                        success: QSMQuestion.questionBankLoadSuccess
                    });
                },
                questionBankLoadSuccess: function (response) {
                    var pagination = response.pagination;
                    var questions = response.questions;
                    if ($('.qb-load-more-wrapper').length > 0) {
                        $('.qb-load-more-wrapper').remove();
                    } else {
                        $('#question-bank').empty();
                    }
                    for (var i = 0; i < questions.length; i++) {
                        QSMQuestion.addQuestionToQuestionBank(questions[i]);
                    }
                    if (pagination.total_pages > pagination.current_page) {
                        var pagination_html = '<div class="qb-load-more-wrapper" style="text-align: center;margin: 20px 0 10px 0;"><input type="hidden" id="question_back_page_number" value="' + pagination.current_page + '"/>';
                        pagination_html += '<input type="hidden" id="question_back_total_pages" value="' + pagination.total_pages + '"/>';
                        pagination_html += '<a href="javascript:void(0)" class="qb-load-more-question qsm-ui-btn qsm-ui-btn-sm">' + qsm_admin_messages.load_more_quetions + '</a></div>';
                        $('#question-bank').append(pagination_html);
                    }
                    if (pagination.current_page == 1) {
                        if (qsmQuestionSettings.categories.length > 0) {
                            // Same values as before; sub-categories now sit indented under their parent (F-25).
                            let $cat_html = QSMQuestion.categoryOptionsHtml(qsm_admin_messages.all_categories);
                            $('#question-bank-cat').html($cat_html);
                            $('#question-bank-cat').val(pagination.category);
                            jQuery(document).trigger('qsm_question_bank_options', $cat_html);
                        }
                    }
                    if ( 1 > questions.length ) {
                        $('#question-bank').append('<div style="margin-top: 70px;text-align: center;" >' + qsm_admin_messages.questions_not_found + '</div>');
                    } else {
                        $('.question-bank-question').each(function () {
                            let questionId = $(this).data('question-id');
                            if (QSMQuestion.questions.some(q => q.get('id') == questionId)) {
                                let $linkButton = $(this).find('.link-question');
                                if ($linkButton.length) {
                                    $linkButton.prop('disabled', true).addClass('disabled');
                                }
                            }
                            QSMQuestion.questions.each(function (question) {
                                let merged_questions = question.get('merged_question');
                                let questionsArray = merged_questions ? merged_questions.split(',').map(q => q.trim()) : [];
                                questionsArray.forEach(function (id) {
                                    let parentElement = $('.question-bank-question[data-question-id="' + id + '"]');
                                    if (parentElement.length) {
                                        parentElement.remove(); // Remove the element if it exists
                                    }
                                });
                            });
                        });
                    }
                    QSMQuestion.syncBankFooter();
                    QSMQuestion.syncBankFilterCount();
                },
                addQuestionToQuestionBank: function (question) {
                    var questionText = QSMQuestion.prepareQuestionText(question.name);
                    var template = wp.template('single-question-bank-question');
                    if (question.question_title !== "undefined" && question.question_title !== "") {
                        questionText = question.question_title;
                    }
                    $('#question-bank').append(template({
                        id: question.id,
                        type: question.type,
                        typeName: (qsmQuestionSettings.question_type_names || {})[question.type] || '',
                        question: questionText,
                        category: question.category,
                        // The REST bank list joins a question's categories with commas.
                        categories: String(question.category || '').split(',').map(function (c) { return c.trim(); }).filter(Boolean),
                        quiz_name: question.quiz_name,
                        linked_question: question.linked_question.join(',')
                    }));
                },
                addQuestionFromQuestionBank: function (questionID, is_linking = 0) {
                    QSMAdmin.displayAlert(is_linking == 0 ? qsm_admin_messages.adding_question : qsm_admin_messages.linking_question, 'info');
                    let isLinkingData = is_linking == 1 ? questionID : 0;
                    var model = new QSMQuestion.question({
                        id: questionID,
                        is_linking: isLinkingData
                    });
                    model.fetch({
                        headers: {
                            'X-WP-Nonce': qsmQuestionSettings.nonce
                        },
                        url: wpApiSettings.root + 'quiz-survey-master/v1/questions/' + questionID + '?is_linking=' + isLinkingData,
                        success: QSMQuestion.questionBankSuccess,
                        error: QSMAdmin.displayError
                    });
                },
                questionBankSuccess: function (model) {
                    var newModel = _.clone(model.attributes);
                    newModel.question_id = newModel.id;
                    newModel.quizID = qsmTextTabObject.quiz_id;
                    newModel.id = null;
                    QSMQuestion.questions.create(
                        newModel, {
                        headers: {
                            'X-WP-Nonce': qsmQuestionSettings.nonce
                        },
                        success: QSMQuestion.addNewQuestionFromQuestionBank,
                        error: QSMAdmin.displayError
                    }
                    );
                },
                prepareCategories: function () {
                    QSMQuestion.categories = [];
                    QSMQuestion.questions.each(function (question) {
                        var category = question.get('category');
                        if (0 !== category.length && !_.contains(QSMQuestion.categories, category)) {
                            QSMQuestion.categories.push(category);
                        }
                    });
                },
                processCategories: function () {
                    $('.category').remove();
                    _.each(QSMQuestion.categories, function (category) {
                        QSMQuestion.addCategory(category);
                    });
                },
                addCategory: function (category) {
                    var template = wp.template('single-category');
                    $('#categories').prepend(template({
                        category: category
                    }));
                },
                loadQuestions: function () {
                    QSMAdmin.displayAlert(qsm_admin_messages.loading_question, 'info');
                    QSMQuestion.questions.fetch({
                        headers: {
                            'X-WP-Nonce': qsmQuestionSettings.nonce
                        },
                        data: {
                            quizID: qsmQuestionSettings.quizID
                        },
                        success: QSMQuestion.loadSuccess,
                        error: QSMAdmin.displayError
                    });
                },
                loadSuccess: function () {
                    QSMAdmin.clearAlerts();
                    $('.qsm-showing-loader').remove();
                    var question;
                    var pages = (qsmQuestionSettings && Array.isArray(qsmQuestionSettings.pages)) ? qsmQuestionSettings.pages : [];
                    var qpages = (qsmQuestionSettings && Array.isArray(qsmQuestionSettings.qpages)) ? qsmQuestionSettings.qpages : [];

                    _.each(qpages, function (page) {
                        QSMQuestion.qpages.add(page);
                    });

                    if (pages.length > 0) {
                        for (var i = 0; i < pages.length; i++) {
                            for (var j = 0; j < pages[i].length; j++) {
                                question = QSMQuestion.questions.get(pages[i][j]);
                                if ('undefined' !== typeof question) {
                                    QSMQuestion.addQuestionToPage(question);
                                    QSM_Quiz_Broadcast_Channel.insertQuestionToChannel(question);
                                }
                            }
                        }
                    } else {
                        //We have removed this code in  7.0.0 because not allow to delete the single page.
                        QSMQuestion.questions.each(QSMQuestion.addQuestionToPage);
                    }
                    //Create Default pages and one question.
                    if (pages.length == 0 && QSMQuestion.questions.length == 0) {
                        $('.new-page-button').trigger('click');
                        $('.questions .page:first .new-question-button:first').trigger('click');
                    }
                    QSMQuestion.countTotal();
                },
                updateQPage: function (pageID) {
                    var pageInfo = QSMQuestion.qpages.get(pageID);
                    pageInfo.set("update_name", 1);
                    jQuery('#page-options').find(':input, select, textarea, :checkbox').each(function (i, field) {
                        pageInfo.set(field.name, field.value);
                        if (field.type === 'checkbox') {
                            pageInfo.set(field.name, field.checked ? '1' : '0');
                        }
                        if (field.type === 'number') {
                            pageInfo.set(field.name, field.value == "" ? 0 : field.value);
                        }
                    });
                },
                shuffleQuestions: function (pageID) {
                    let $targetPage = jQuery('.page[data-page-id="' + pageID + '"]');
                    let $questions = $targetPage.children('.question');
                    if ($questions.length <= 1) {
                        return;
                    }
                    // Each row moves with its open editor (.questionElements, the next sibling) so the two stay together.
                    let groups = $questions.map(function () {
                        let $row = jQuery(this);
                        return $row.add($row.next('.questionElements'));
                    }).get();
                    for (let i = groups.length - 1; i > 0; i--) {
                        let j = crypto.getRandomValues(new Uint32Array(1))[0] % (i + 1);
                        let temp = groups[i];
                        groups[i] = groups[j];
                        groups[j] = temp;
                    }
                    // All rows at once, then one short fade, instead of a 1s loader and 0.5s per question.
                    // Only rows already showing fade back in, so a search filter keeps its hidden rows hidden.
                    let $shown = $questions.filter(':visible');
                    groups.forEach(function ($group) {
                        $group.appendTo($targetPage);
                    });
                    $shown.hide().fadeIn(QSMQuestion.slideSpeed() ? 200 : 0);
                    QSMQuestion.finalizeQuestionShuffle(pageID);
                },
                finalizeQuestionShuffle: function(pageID) {
                    QSMQuestion.updateQuestionModelsForPage(pageID);
                    QSMQuestion.countTotal();
                    QSMQuestion.savePages();
                    jQuery('.qsm-admin-randomize-page-questions[data-page-id="' + pageID + '"]').prop('disabled', false);
                    QSMAdmin.displayAlert(qsm_admin_messages.question_shuffle, 'success');
                },
                updateQuestionModelsForPage: function(pageID) {
                    jQuery('.page[data-page-id="' + pageID + '"]').each(function() {
                        let $page = jQuery(this);
                        let pageId = $page.data('page-id');
                        
                        $page.children('.question').each(function() {
                            let questionId = jQuery(this).data('question-id');
                            let model = QSMQuestion.questions.get(questionId);
                            if (model) {
                                model.set('page', pageId - 1);
                            }
                        });
                    });
                },
                savePages: function () {
                    var pages = [];
                    var qpages = [];
                    var pageInfo = null;
                    var post_id = jQuery('#edit_quiz_post_id').val();

                    // Cycles through each page and add page + questions to pages variable
                    _.each(jQuery('.page'), function (page) {

                        // If page is empty, do not add it.
                        if (0 == jQuery(page).children('.question').length) {
                            return;
                        }
                        var singlePage = [];
                        // Cycle through each question and add to the page.
                        _.each(jQuery(page).children('.question'), function (question) {
                            singlePage.push(jQuery(question).data('question-id'))
                        });
                        pages.push(singlePage);
                        /**
                         * Prepare qpages Object
                         */
                        pageInfo = QSMQuestion.qpages.get(jQuery(page).data('page-id'));
                        pageInfo.set('questions', singlePage);
                        qpages.push(pageInfo.attributes);
                    });
                    var data = {
                        action: 'qsm_save_pages',
                        quiz_id: qsmQuestionSettings.quizID,
                        nonce: qsmQuestionSettings.saveNonce,
                        pages: pages,
                        qpages: qpages,
                        post_id: post_id,
                    };

                    jQuery.ajax(ajaxurl, {
                        data: data,
                        method: 'POST',
                        success: QSMQuestion.savePagesSuccess,
                        error: QSMAdmin.displayjQueryError
                    });
                },
                savePagesSuccess: function () {
                    $('#save-edit-quiz-pages').removeClass('is-active');
                },
                addNewPage: function (pageID) {
                    var template = wp.template('page');
                    if (typeof pageID == 'undefined' || pageID == '') {
                        var newPageID = QSMQuestion.qpages.length + 1;
                        var pageID = newPageID;
                        var pageInfo = QSMQuestion.qpages.add({
                            id: newPageID,
                            quizID: qsmQuestionSettings.quizID,
                            pagekey: qsmRandomID(8),
                            hide_prevbtn: 0,
                            question_limit: 0
                        });
                    }
                    var pageInfo = QSMQuestion.qpages.get(pageID);
                    $('.questions').append(template(pageInfo));
                    var page = $('.questions').find('.page').length;
                    $('.page:nth-child(' + page + ')').find('.page-number').text('Page ' + page);
                    $('.page').sortable({
                        items: '.question',
                        opacity: 70,
                        cursor: 'move',
                        handle: '.qsm-ui-question-handle',
                        placeholder: "ui-state-highlight qsm-ui-sort-placeholder",
                        // The kit handles are <button>s, which jQuery UI's default `cancel` refuses to drag from.
                        cancel: 'input, textarea, select, option',
                        forcePlaceholderSize: true,
                        connectWith: '.page',
                        over: QSMQuestion.markDropTarget,
                        out: QSMQuestion.unmarkDropTarget,
                        stop: function (evt, ui) {
                            $('.page').removeClass('qsm-ui-drop-target');
                            let question_id =ui.item.data("question-id");
                            // The page's position on the board, like addQuestionToPage() and savePages() (data-page-id goes stale once pages are reordered).
                            let parent_page = $("div.question[data-question-id='" + question_id + "']").parent('.page').index();
                            let model = QSMQuestion.questions.get(question_id);
                            model.set('page', parent_page);
                            setTimeout(
                                function () {
                                    $('.save-page-button').trigger('click');
                                },
                                200
                            )
                        }
                    });
                    setTimeout(QSMQuestion.removeNew, 250);
                },
                addNewQuestionFromQuestionBank: function (model) {
                    var page = parseInt($('#add-question-bank-page').val(), 10);
                    model.set('page', page);
                    if (qsm_link_button && !model.get('merged_question').split(',').includes(model.id)) {
                        // Retrieve the current value
                        let mergedQuestion = model.get('merged_question');
                        // Update the merged question by adding the new ID
                        mergedQuestion += `,${model.id}`;
                        // Set the updated value back to the model
                        model.set('merged_question', mergedQuestion);

                        let quizId = model.get('quizID');
                        let quizName = '';

                        for (const record of qsm_admin_messages.qsmQuizzesObject) {
                            if (record.quiz_id == quizId) { quizName = record.quiz_name; break; }
                        }
                        const linkQuizzes = [...model.get('link_quizzes')]; // Created a shallow copy of the array
                        if (quizName !== '') {
                            linkQuizzes.push(quizName);
                        }
                        QSM_Quiz_Broadcast_Channel.sendQuestionData(model.id, model.get('merged_question'), linkQuizzes);
                    }
                    QSMQuestion.questions.add(model);
                    QSMQuestion.addQuestionToPage(model);
                    QSMQuestion.savePages();

                    $('.import-button, .link-question').removeClass('disable_import');
                    QSMQuestion.countTotal();
                    if(import_button){
                        import_button.html(qsm_admin_messages.add_question);
                    }
                    if(qsm_link_button) {
                        qsm_link_button.html(qsm_admin_messages.link_question);
                    }
                    if(import_button){
                        import_button.attr("onclick", "return confirm('" + qsm_admin_messages.confirm_message + " " + qsm_admin_messages.import_question_again + "');");
                    }
                    QSMQuestion.openEditPopup(model.id, $('.question[data-question-id=' + model.id + ']').find('.edit-question-button'));
                    if(qsm_link_button == ''){
                        $(document).find('.qsm-linked-list-inside').hide().empty();
                        $(document).find('.qsm-linked-list-div-block').hide();
                    }
                },
                addNewQuestion: function (model) {
                    if (typeof is_question_bank_page !== 'undefined' && is_question_bank_page) {
                        return;
                    }
                    var default_answers = parseInt(qsmQuestionSettings.default_answers);
                    var count = 0;
                    if ( !qsmShouldSuppressCreationAlerts() ) {
                        QSMAdmin.displayAlert(qsm_admin_messages.question_created, 'success');
                    }
                    QSMQuestion.addQuestionToPage(model);
                    QSMQuestion.openEditPopup(model.id, $('.question[data-question-id=' + model.id + ']').find('.edit-question-button'));
                    QSMQuestion.countTotal();
                    if ($('#answers').find('.answers-single').length < default_answers) {
                        while (count < default_answers) {
                            $('#new-answer-button').trigger('click');
                            count++;
                        }
                    }
                    // When a default Question Type other than the base type is inherited, run the
                    // question-type change logic so the type-specific fields/answers are set up
                    // exactly as if the author had picked the type themselves (task CU-86d3t3jcn).
                    const inherited_type = model.get('type');
                    if (inherited_type && '0' != inherited_type) {
                        $('#question_type').trigger('change');
                    }
                    $('.save-page-button').trigger('click');
                },
                addQuestionToPage: function (model) {
                    var page = model.get('page') + 1;
                    var page_exists = $('.page:nth-child(' + page + ')').length;
                    var count = 0;
                    while (!page_exists) {
                        QSMQuestion.addNewPage(page);
                        page_exists = $('.page:nth-child(' + page + ')').length;
                        count++;
                        if (count > 5) {
                            page_exists = true;
                            console.log('count reached');
                        }
                    }
                    var questionName = QSMQuestion.prepareQuestionText(model.get('name'));
                    var new_question_title = model.get('question_title');
                    if (new_question_title === null || typeof new_question_title === "undefined" || new_question_title === "") {
                        //Do nothing
                    } else {
                        questionName = new_question_title;
                    }
                    if (questionName == '') {
                        questionName = qsm_admin_messages.new_question;
                    }
                    $('.page:nth-child(' + page + ')').append(QSMQuestion.renderQuestionRow(model, questionName, model.get('category')));
                    setTimeout(QSMQuestion.removeNew, 250);
                },
                /**
                 * Returns the board row HTML for a question.
                 *
                 * The Questions tab uses the kit row (tmpl-qsm-ui-question): the question type
                 * once and every category with its full path. The Question Bank page has no kit
                 * row template and keeps tmpl-question with the legacy category label.
                 */
                renderQuestionRow: function (model, questionName, legacyCategory) {
                    if (!is_question_bank_page && document.getElementById('tmpl-qsm-ui-question')) {
                        const typeNames = qsmQuestionSettings.question_type_names || {};
                        return wp.template('qsm-ui-question')({
                            id: model.id,
                            type: model.get('type'),
                            typeName: typeNames[model.get('type')] || '',
                            categories: QSMQuestion.getCategoryPaths(model),
                            question: questionName
                        });
                    }
                    return wp.template('question')({
                        id: model.id,
                        type: model.get('type'),
                        category: legacyCategory,
                        question: questionName
                    });
                },
                // Category labels for a question, with the parent path for sub-categories ("Service › Support").
                getCategoryPaths: function (model) {
                    var multicategories = model.get('multicategories');
                    if (!Number.parseInt(qsmQuestionSettings.multiple_category_enabled, 10) || !Array.isArray(multicategories)) {
                        const single = model.get('category');
                        return single ? [single] : [];
                    }
                    const terms = {};
                    _.each(qsmQuestionSettings.categories || [], function (term) {
                        terms[String(term.cat_id)] = term;
                    });
                    const paths = [];
                    multicategories.forEach(function (id) {
                        let term = terms[String(id)];
                        if (!term) {
                            // Not in the localized list (e.g. created in another tab): use the tree's label.
                            const label = $.trim($('input#in-qsm_category-' + id).first().closest('label').text());
                            if (label) {
                                paths.push(label);
                            }
                            return;
                        }
                        const names = [term.category];
                        const seen = {};
                        seen[String(id)] = true;
                        while (term.parent && terms[String(term.parent)] && !seen[String(term.parent)]) {
                            seen[String(term.parent)] = true;
                            term = terms[String(term.parent)];
                            names.unshift(term.category);
                        }
                        paths.push(names.join(' \u203A '));
                    });
                    return paths;
                },
                /**
                 * "Advanced options" in the kit editor (P-06): always shown, Core's options first, then one titled
                 * group per add-on. The rows stay direct .qsm-row children of .advanced-content, so saving and add-ons
                 * read them as before; the group titles sit between them.
                 */
                initAdvancedToggle: function ($container) {
                    const $adv = $container && $container.length ? $container.find('.qsm-ui-qe-adv') : $();
                    if (!$adv.length) {
                        return;
                    }
                    $adv.addClass('is-open');
                    QSMQuestion.updateAdvancedCount($adv);
                },
                // Same speed as the inline editor's slideDown/slideUp; none when the user asked for reduced motion.
                // Duration follows the distance: a fixed 'slow' (600ms) is fine for the tall editor but drags on short panels.
                slideSpeed: function (px) {
                    if (window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
                        return 0;
                    }
                    return Math.round(Math.min(400, Math.max(180, 150 + (px || 0) * 0.5)));
                },
                // Hides a group title when none of its rows apply to the current question type (the type switch hides them inline),
                // and marks each group's first and last shown row so the CSS can draw one box per group around flat rows.
                updateAdvancedCount: function ($adv) {
                    $adv.each(function () {
                        let $title = $();
                        let $shown = $();
                        const flush = function () {
                            $title.toggle($shown.length > 0);
                            // An add-on group's name is its box header; Core's group (under the section title) closes its own top.
                            $shown.first().toggleClass('qsm-ui-qe-adv-top', !$title.is('h4'));
                            $shown.last().addClass('qsm-ui-qe-adv-last');
                        };
                        $(this).children('.qsm-row').addClass('qsm-ui-qe-adv-row').removeClass('qsm-ui-qe-adv-top qsm-ui-qe-adv-last');
                        $(this).children().each(function () {
                            const $child = $(this);
                            if ($child.hasClass('qsm-ui-qe-adv-group')) {
                                flush();
                                $title = $child;
                                $shown = $();
                            } else if (($child.hasClass('qsm-row') || $child.hasClass('qsm-ui-qe-adv-more')) && 'none' !== this.style.display) {
                                $shown = $shown.add($child);
                            }
                        });
                        flush();
                        // The section title also covers every add-on group below it.
                        const $section = $(this).children('.qsm-ui-qe-adv-title');
                        if ($section.length && 'none' === $section[0].style.display) {
                            $section.toggle($(this).children('.qsm-ui-qe-adv-group').not($section).filter(function () {
                                return 'none' !== this.style.display;
                            }).length > 0);
                        }
                    });
                },
                /**
                 * Small destructive confirm (O-02) in #qsm-ui-confirm-dialog. A native <dialog>, so it can
                 * open above a micromodal popup; falls back to window.confirm() where the dialog isn't printed.
                 */
                confirmDanger: function (opts, onConfirm) {
                    const dialog = document.getElementById('qsm-ui-confirm-dialog');
                    if (!dialog || !window.QSMAdminUI || 'function' !== typeof dialog.showModal) {
                        if (window.confirm(opts.title + (opts.text ? '\n\n' + opts.text : ''))) {
                            onConfirm();
                        }
                        return;
                    }
                    const $dialog = $(dialog);
                    // MicroModal listens for Esc/Tab on document: keep them in the dialog, or Esc would also close
                    // the popup underneath and Tab would pull focus back into it.
                    $dialog.off('keydown.qsmConfirm').on('keydown.qsmConfirm', function (e) {
                        if ('Escape' === e.key || 'Tab' === e.key) {
                            e.stopPropagation();
                        }
                    });
                    $dialog.find('#qsm-ui-confirm-dialog-title').text(opts.title);
                    $dialog.find('#qsm-ui-confirm-dialog-text').text(opts.text || '');
                    $dialog.find('.qsm-ui-confirm-ok').text(opts.button).off('click.qsmConfirm').on('click.qsmConfirm', function () {
                        window.QSMAdminUI.closeDialog(dialog);
                        onConfirm();
                    });
                    window.QSMAdminUI.openDialog(dialog);
                    $dialog.find('[data-qsm-ui-close][autofocus]').trigger('focus');
                },
                // Opens the delete options popup (#modal-7) for one question, the selected ones or all of them.
                openDeleteOptions: function (iid) {
                    const i18n = qsmQuestionSettings.board_i18n || {};
                    let title = i18n.delOne;
                    if ('selected-questions' === iid) {
                        const selected = $('.qsm-admin-select-question-input:checked').length;
                        title = 1 === selected ? i18n.delSelOne : (i18n.delSelected ? i18n.delSelected.replace('%d', selected) : '');
                    } else if ('all-questions' === iid) {
                        title = i18n.delAll ? i18n.delAll.replace('%d', $('.qsm-admin-select-question-input').length) : '';
                    }
                    if (title) {
                        $('#modal-7-title').text(title);
                    }
                    $('#unlink-question-button').attr('data-question-iid', iid);
                    $('#delete-question-button').attr('data-question-iid', iid);
                    MicroModal.show('modal-7');
                    // Focus starts on Cancel (O-02), not on the close icon.
                    $('#modal-7 .qsm-ui-confirm-cancel').trigger('focus');
                },
                /**
                 * Category tree (F-25): adds a new category to every copy of the editor's tree (the inline
                 * editor and its #modal-1-content template), ticked with its parents like the old
                 * Add New Category popup, and to the parent pickers and the localized list.
                 */
                addCategoryToTree: function (id, name, parent) {
                    const $label = $('<label class="selectit qsm-ui-check"></label>')
                        .append($('<input type="checkbox" name="tax_input[qsm_category][]">').attr({ value: id, id: 'in-qsm_category-' + id }))
                        .append($('<span></span>').text(name));
                    $('.qsm-ui-cat-tree .qsm_category_checklist').each(function () {
                        const $list = $(this);
                        const $li = $('<li></li>').attr('id', 'qsm_category-' + id)
                            .append('<span class="qsm-ui-tree-spacer"></span>')
                            .append($label.clone())
                            .append('<span class="qsm-ui-tree-count">0</span>');
                        const $parentLi = parent > 0 ? $list.find('li#qsm_category-' + parent) : $();
                        if ($parentLi.length) {
                            let $children = $parentLi.children('ul.children');
                            if (!$children.length) {
                                $children = $('<ul class="children"></ul>').appendTo($parentLi);
                                const parentName = $.trim($parentLi.children('label').text());
                                const collapseLabel = (qsmQuestionSettings.board_i18n || {}).catCollapse || 'Collapse %s';
                                $parentLi.children('.qsm-ui-tree-spacer').replaceWith(
                                    $('<button type="button" class="qsm-ui-tree-toggle" aria-expanded="true"></button>')
                                        .attr('aria-label', collapseLabel.replace('%s', parentName))
                                        .html('<svg class="qsm-ui-icon" aria-hidden="true" focusable="false"><use href="#qsm-ui-i-chev"></use></svg>')
                                );
                            }
                            $children.prop('hidden', false).append($li);
                            $parentLi.children('.qsm-ui-tree-toggle').attr('aria-expanded', 'true');
                        } else {
                            $list.prepend($li);
                        }
                        $li.find('input').prop('checked', true);
                        $li.parents('li').children('label').children('input').prop('checked', true);
                    });
                    let depth = 0;
                    const $parentOption = $('.qsm-ui-cat-add-parent option[value="' + parent + '"]').first();
                    if ($parentOption.length) {
                        depth = ($parentOption.text().match(/— /g) || []).length + 1;
                    }
                    const $option = $('<option></option>').val(id).text(new Array(depth + 1).join('— ') + name);
                    $('.qsm-ui-cat-add-parent').each(function () {
                        const $after = $(this).find('option[value="' + parent + '"]');
                        if ($after.length) {
                            $after.after($option.clone());
                        } else {
                            $(this).append($option.clone());
                        }
                    });
                    $('#qsm-parent-category').append($('<option class="level-0"></option>').val(id).text(name));
                    if (Array.isArray(qsmQuestionSettings.categories)) {
                        qsmQuestionSettings.categories.push({ category: name, cat_id: id, parent: parent > 0 ? parent : 0 });
                    }
                },
                // Category <option>s for the question bank filter, indented under their parents (F-25).
                categoryOptionsHtml: function (allLabel) {
                    const byParent = {};
                    let html = '<option value="">' + allLabel + '</option>';
                    // _.escape also escapes quotes: values go into value="…" (legacy mode uses the category name).
                    const esc = function (text) {
                        return _.escape(String(text));
                    };
                    _.each(qsmQuestionSettings.categories || [], function (term) {
                        if ('' === term.category) {
                            return;
                        }
                        const parent = String(term.parent || 0);
                        (byParent[parent] = byParent[parent] || []).push(term);
                    });
                    const walk = function (parent, depth, seen) {
                        _.each(byParent[parent] || [], function (term) {
                            const value = (typeof term.cat_id !== 'undefined' && term.cat_id !== '') ? term.cat_id : term.category;
                            html += '<option value="' + esc(value) + '">' + new Array(depth + 1).join('&nbsp;&nbsp;&nbsp;') + (depth ? '— ' : '') + esc(term.category) + '</option>';
                            if (typeof term.cat_id !== 'undefined' && !seen[term.cat_id]) {
                                seen[term.cat_id] = true;
                                walk(String(term.cat_id), depth + 1, seen);
                            }
                        });
                    };
                    walk('0', 0, {});
                    return html;
                },
                markDropTarget: function () {
                    $(this).addClass('qsm-ui-drop-target');
                },
                unmarkDropTarget: function () {
                    $(this).removeClass('qsm-ui-drop-target');
                },
                // Moves a question row (and its open inline editor) to another page, before $before or at the end.
                moveQuestionRow: function ($question, $page, $before) {
                    const $editor = $question.next('.questionElements');
                    if ($before && $before.length) {
                        $before.before($question);
                    } else {
                        $page.append($question);
                    }
                    if ($editor.length) {
                        $question.after($editor);
                    }
                    const model = QSMQuestion.questions.get($question.data('question-id'));
                    if (model) {
                        model.set('page', $page.index());
                    }
                },
                // Fills the bulk bar's "Move to page" menu with the current pages.
                refreshBulkMoveMenu: function () {
                    const $menu = $('.qsm-ui-bulk-move-menu').empty();
                    const label = qsmQuestionSettings.board_i18n ? qsmQuestionSettings.board_i18n.page : 'Page %d';
                    $('.questions > .page').each(function (index) {
                        $('<li></li>').append(
                            $('<button type="button" role="menuitem" class="qsm-ui-bulk-move-to"></button>')
                                .attr('data-page-index', index)
                                .text(label.replace('%d', index + 1))
                        ).appendTo($menu);
                    });
                },
                bulkMoveQuestions: function (pageIndex) {
                    const $page = $('.questions > .page').eq(pageIndex);
                    const $checked = $('.questions .qsm-admin-select-question-input:checked');
                    if (!$page.length || !$checked.length) {
                        return;
                    }
                    $checked.each(function () {
                        QSMQuestion.moveQuestionRow($(this).closest('.question'), $page);
                    });
                    const moved = $checked.length;
                    QSMQuestion.clearQuestionSelection();
                    QSMQuestion.savePages();
                    if (window.QSMAdminUI && qsmQuestionSettings.board_i18n) {
                        window.QSMAdminUI.toast(qsmQuestionSettings.board_i18n.bulkMoved.replace('%d', moved));
                    }
                },
                clearQuestionSelection: function () {
                    $('.qsm-admin-select-question-input, .qsm-admin-select-page-question').prop('checked', false);
                    $('.qsm-selected-question-count').html(0);
                    $('.qsm-admin-bulk-actions').fadeOut();
                },
                // Alt + Up/Down on a row's handle: one step within the page, or onto the next/previous page.
                keyboardMoveQuestion: function ($question, direction) {
                    const $page = $question.parent('.page');
                    const $target = direction < 0 ? $question.prevAll('.question').first() : $question.nextAll('.question').first();
                    if ($target.length) {
                        if (direction < 0) {
                            QSMQuestion.moveQuestionRow($question, $page, $target);
                        } else {
                            QSMQuestion.moveQuestionRow($question, $page, $target.nextAll('.question').first());
                        }
                    } else {
                        const $otherPage = direction < 0 ? $page.prev('.page') : $page.next('.page');
                        if (!$otherPage.length) {
                            return;
                        }
                        QSMQuestion.moveQuestionRow($question, $otherPage, direction > 0 ? $otherPage.children('.question').first() : null);
                        if (window.QSMAdminUI && qsmQuestionSettings.board_i18n) {
                            window.QSMAdminUI.toast(qsmQuestionSettings.board_i18n.moved.replace('%d', $otherPage.index() + 1));
                        }
                    }
                    $question.find('.qsm-ui-question-handle').trigger('focus');
                    // One save after a run of key presses, not one request per press.
                    clearTimeout(QSMQuestion.keyboardSaveTimer);
                    QSMQuestion.keyboardSaveTimer = setTimeout(QSMQuestion.flushKeyboardSave, 600);
                },
                // Saves a pending keyboard move now (also when the page is left before the delay ran out).
                flushKeyboardSave: function () {
                    if (QSMQuestion.keyboardSaveTimer) {
                        clearTimeout(QSMQuestion.keyboardSaveTimer);
                        QSMQuestion.keyboardSaveTimer = null;
                        QSMQuestion.savePages();
                    }
                },
                // Returns the saved per-quiz "Default Question Settings" (type/category/required)
                // or false when none are configured. See task CU-86d3t3jcn.
                getDefaultQuestionSettings: function () {
                    const defaults = qsmQuestionSettings.default_question_settings;
                    if (!defaults || typeof defaults !== 'object' || $.isEmptyObject(defaults)) {
                        return false;
                    }
                    return defaults;
                },
                createQuestion: function (page) {
                    if ( !qsmShouldSuppressCreationAlerts() ) {
                        QSMAdmin.displayAlert(qsm_admin_messages.creating_question, 'info');
                    }
                    const attributes = {
                        quizID: qsmQuestionSettings.quizID,
                        page: page
                    };
                    // Apply the quiz's Default Question Settings so a newly added question
                    // inherits the configured Question Type, Category and Required status.
                    const defaults = QSMQuestion.getDefaultQuestionSettings();
                    if (defaults) {
                        if (defaults.type !== undefined && '' !== defaults.type) {
                            attributes.type = String(defaults.type);
                        }
                        if (defaults.required !== undefined && '' !== defaults.required) {
                            attributes.required = Number.parseInt(defaults.required, 10);
                        }
                        if (defaults.category !== undefined && '' !== defaults.category) {
                            if (Number.parseInt(qsmQuestionSettings.multiple_category_enabled, 10)) {
                                attributes.multicategories = [String(defaults.category)];
                                attributes.category = '';
                            } else {
                                attributes.category = String(defaults.category);
                            }
                        }
                    }
                    QSMQuestion.questions.create(attributes, {
                        headers: {
                            'X-WP-Nonce': qsmQuestionSettings.nonce
                        },
                        success: QSMQuestion.addNewQuestion,
                        error: QSMAdmin.displayError
                    });
                },
                duplicateQuestion: function (questionID) {
                    QSMAdmin.displayAlert(qsm_admin_messages.duplicating_question, 'info');
                    var model = QSMQuestion.questions.get(questionID);
                    var newModel = _.clone(model.attributes);
                    newModel.id = null;
                    newModel.merged_question = '';
                    newModel.link_quizzes = '';
                    QSMQuestion.questions.create(
                        newModel, {
                        headers: {
                            'X-WP-Nonce': qsmQuestionSettings.nonce
                        },
                        success: QSMQuestion.addNewQuestion,
                        error: QSMAdmin.displayError
                    }
                    );
                },
                saveQuestion: function (questionID, CurrentElement) {
                    var $context = $('.questionElements');
                    if (CurrentElement && CurrentElement.length) {
                        var $closest = CurrentElement.closest('.questionElements');
                        if ($closest.length) {
                            $context = $closest;
                        }
                    }
                    var model = QSMQuestion.questions.get(questionID);
                    var hint = $context.find('#hint').val();
                    var name = wp.editor.getContent('question-text');
                    //Save new question title
                    var question_title = $context.find('#question_title').val();
                    if (name == '' && question_title == '') {
                        alert(qsm_admin_messages.enter_question_title);
                        setTimeout(function () {
                            $context.find('#save-edit-question-spinner').removeClass('is-active');
                        }, 250);
                        return false;
                    }
                    var advanced_option = {};
                    var answerInfo = wp.editor.getContent('correct_answer_info');
                    var quizID = parseInt(qsmTextTabObject.quiz_id);
                    if ( typeof qsmQuestionBankAdapter !== 'undefined' ) {
                        quizID = $context.find("input[name='edit_quiz_id']").val()
                    }
                    var type = $context.find("#question_type").val();
                    var comments = $context.find("#comments").val();
                    let required = $context.find("input[name='required']").is(":checked") ? 0 : 1;
                    var isQuestionBankPage = jQuery('body').hasClass('qsm_page_qsm_question_bank');
                    // The Question Bank editor shows the status as a read-only label, so only the
                    // quiz Questions tab has a toggle to read.
                    const $questionStatus = isQuestionBankPage ? $() : $context.find("input[name='question_status']");
                    let isPublished;
                    if ( $questionStatus.length ) {
                        isPublished = $questionStatus.is(":checked") ? 1 : 0;
                    } else {
                        // No status control in this editor context - keep the status the question
                        // already has instead of silently unpublishing it.
                        const currentSettings = ( model && model.get('settings') ) || {};
                        isPublished = typeof currentSettings.isPublished !== 'undefined'
                            ? ( parseInt(currentSettings.isPublished, 10) ? 1 : 0 )
                            : 1;
                    }
                    advanced_option['required'] = required;
                    var category = $context.find(".category-radio:checked").val();
                    var type_arr = [];
                    $.each($context.find("input[name='file_upload_type[]']:checked"), function () {
                        type_value = $(this).val().replace(/,/g, '');
                        type_arr.push(type_value);
                    });
                    if ('new_category' == category) {
                        category = $context.find('#new_category').val();
                    }
                    if (!category) {
                        category = '';
                    }

                    //polar question validation
                    if (13 == type) {
                        let polar_error = 0;
                        let polar_required_error = 0;
                        let old_value = "";
                        $context.find('.answers-single .answer-points').each(function () {
                            $(this).css('border-color', '');
                            if ("" != old_value && $(this).val() == old_value) {
                                alert(qsm_admin_messages.polar_q_range_error);
                                polar_error++;
                            }
                            if ("" == $(this).val()) {
                                $(this).css('border-color', 'red');
                                polar_error++;
                                polar_required_error++;
                            }
                            old_value = $(this).val();
                        });
                        if (0 < polar_required_error) {
                            alert(qsm_admin_messages.range_fields_required);
                        }
                        if (0 < polar_error) {
                            setTimeout(function () {
                                $context.find('#save-edit-question-spinner').removeClass('is-active');
                            }, 250);
                            return false;
                        }
                    }

                    var multicategories = [];
                    $.each($context.find("input[name='tax_input[qsm_category][]']:checked"), function () {
                        multicategories.push($(this).val());
                    });
                    var featureImageID = $context.find('.qsm-feature-image-id').val();
                    var featureImageSrc = $context.find('.qsm-feature-image-src').val();
                    var answerType = $context.find('#change-answer-editor').val();
                    var matchAnswer = $context.find('#match-answer').val();

                    var intcnt = 1;
                    var answers = [];
                    var $answersElement = $context.find('.answers-single');
                    _.each($answersElement, function (answer) {
                        var $answer = jQuery(answer);
                        var answer = '';
                        var caption = '';
                        if (answerType == 'rich') {
                            var ta_id = $answer.find('textarea').attr('id')
                            answer = wp.editor.getContent(ta_id);
                        } else if (answerType == 'image') {
                            answer = $answer.find('.answer-text').val().trim();
                            answer = $.QSMSanitize(answer);
                            caption = $answer.find('.answer-caption').val().trim();
                            caption = $.QSMSanitize(caption);
                        } else {
                            answer = $answer.find('.answer-text').val().trim();
                            answer = $.QSMSanitize(answer);
                        }

                        var points = $answer.find('.answer-points').val();
                        var correct = 0;
                        if ($answer.find('.answer-correct').prop('checked')) {
                            correct = 1;
                        }

						var ansData = [answer, points, correct];
                        if (answerType == 'image') {
							ansData.push(caption);
                        }
						ansData = QSMQuestion.answerFilter(ansData, $answer, answerType);
						answers.push(ansData);
						intcnt++
                    });
					model.set('answers', answers);
					model.set('required', required);
					model.set('is_published', isPublished);
					// Keep the local copy of the question settings in step, so re-opening the
					// editor before a page reload shows the status that was just saved.
					model.set('settings', _.extend({}, model.get('settings') || {}, { isPublished: isPublished }));
                    jQuery(document).trigger('qsm_save_question_before', [questionID, CurrentElement, model, advanced_option]);
                    $('.questionElements .advanced-content > .qsm-row:not(.core-option)').each(function () {
                        if ($(this).find('input[type="text"]').length > 0) {
                            $($(this).find('input[type="text"]')).each(function () {
                                let element_id = $(this).attr('id');
                                advanced_option[element_id] = $(this).val();
                            });
                        } else if ($(this).find('input[type="number"]').length > 0) {
                            let element_id = $(this).find('input[type="number"]').attr('id');
                            advanced_option[element_id] = $(this).find('input[type="number"]').val();
                        } else if ($(this).find('select').length > 0) {
                            let element_id = $(this).find('select').attr('id');
                            advanced_option[element_id] = $(this).find('select').val();
                        } else if ($(this).find('input[type="checkbox"]').length > 0) {
                            let element_id = $(this).find('input[type="checkbox"]').attr('name');
                            let multi_value = $(this).find('input[type="checkbox"]:checked').map(function () {
                                return this.value;
                            }).get().join(',');
                            element_id = element_id.replace('[]', '');
                            advanced_option[element_id] = multi_value;
                        }
                    });

                    model.save({
                        quizID: quizID,
                        type: type,
                        name: name,
                        question_title: question_title,
                        answerInfo: answerInfo,
                        comments: comments,
                        hint: hint,
                        category: category,
                        multicategories: multicategories,
                        featureImageID: featureImageID,
                        featureImageSrc: featureImageSrc,
                        answers: answers,
                        answerEditor: answerType,
                        matchAnswer: matchAnswer,
                        other_settings: advanced_option,
                        rest_nonce: qsmQuestionSettings.rest_user_nonce
                    }, {
                        headers: {
                            'X-WP-Nonce': qsmQuestionSettings.nonce
                        },
                        success: QSMQuestion.saveSuccess,
                        error: QSMAdmin.displayError,
                        type: 'POST'
                    });
                    jQuery(document).trigger('qsm_save_question', [questionID, CurrentElement]);
                },
                answerFilter: function (ansData, $answer, answerType) {
					return ansData;
				},
                closeEditPopup: function(useModal){
                    if (typeof useModal === 'undefined') {
                        useModal = !!QSMQuestion.lastEditUsedModal;
                    }
                    var isQuestionBankPage = typeof is_question_bank_page !== 'undefined' && is_question_bank_page;
                    var shouldUseModal = Boolean(useModal);
                    if ( shouldUseModal ) {
                        if ( typeof window.MicroModal !== 'undefined' ) {
                            window.MicroModal.close('modal-1');
                        } else {
                            $('#modal-1').attr('aria-hidden', 'true').removeClass('is-visible');
                        }
                        $('.qsm_tab_content .question').removeClass('opened');
                        return;
                    }
                    var $questionElements = $('.questions .questionElements:visible');
                    if ( !$questionElements.length ) {
                        return;
                    }
                    $questionElements.slideUp('slow', function(){
                        $(this).remove();
                    });
                    $('.questions').sortable('enable');
                    $('.page').sortable('enable');
                    $('.qsm_tab_content .question').removeClass('opened');
                },
                saveSuccess: function (model) {
                    var page = model.get('page') + 1;
                    var questionName = model.get('name');
                    var new_question_title = model.get('question_title');
                    if (new_question_title !== '') {
                        questionName = $.QSMSanitize(new_question_title);
                    }
                    var category = [];
                    var multicategories = model.get('multicategories');
                    if (multicategories) {
                        multicategories.forEach(val => {
                            const categoryLabel = $(".qsm-popup__content #in-qsm_category-" + val + "-1 > label").text();
                            if (categoryLabel) {
                                category.push(categoryLabel);
                            }
                        });
                    }
                    const $oldRow = $('.question[data-question-id=' + model.id + ']');
                    const wasOpened = $oldRow.hasClass('opened');
                    const wasChecked = $oldRow.find('.qsm-admin-select-question-input').prop('checked');
                    const $newRow = $(QSMQuestion.renderQuestionRow(model, questionName, category.join(', ')));
                    $oldRow.replaceWith($newRow);
                    $newRow.toggleClass('opened', wasOpened).find('.qsm-admin-select-question-input').prop('checked', Boolean(wasChecked));
                    setTimeout(function () {
                        $('#save-edit-question-spinner').removeClass('is-active');
                    }, 250);
                    setTimeout(QSMQuestion.removeNew, 250);
                    if ( is_question_bank_page ) {
                        QSMAdmin.displayAlert(qsm_admin_messages.question_saved, 'success');
                        QSMQuestion.closeEditPopup();
                    }
					jQuery(document).trigger('qsm_admin_question_saved_success', [model]);
                },
                addNewAnswer: function (answer, questionType = false, $insertAfter = null) {
                    if (!questionType) {
                        questionType = $('#question_type').val();
                    }
                    var answerTemplate = wp.template('single-answer');
					var ansTemp = {
						answer: decodeEntities(answer[0]),
						points: answer[1],
						correct: answer[2],
						count: answer['index'],
						question_id: answer['question_id'],
						answerType: answer['answerType'],
						form_type: qsmQuestionSettings.form_type,
						quiz_system: qsmQuestionSettings.quiz_system,
                        question_type: questionType,
                        answer_label: answer.answer_label || {},
                        inside_key: answer.inside_key || 0,
					};
                    if (answer['answerType'] == 'image') {
						ansTemp = {
                            answer: decodeEntities(answer[0]),
                            points: answer[1],
                            correct: answer[2],
                            caption: answer[3],
                            count: answer['index'],
							question_id: answer['question_id'],
							answerType: answer['answerType'],
                            form_type: qsmQuestionSettings.form_type,
                            quiz_system: qsmQuestionSettings.quiz_system,
                            question_type: questionType,
                            answer_label: answer.answer_label || {},
                            inside_key: answer.inside_key || 0,
                        };
                    }
					jQuery(document).trigger('qsm_new_answer_template', [ansTemp, answer, questionType]);

                    // Create the new answer HTML
                    var $newAnswer = $(answerTemplate(ansTemp));

                    // Insert after specific element if provided, otherwise append to #answers
                    if ($insertAfter && $insertAfter.length) {
                        $insertAfter.after($newAnswer);
                    } else {
                        $('#answers').append($newAnswer);
                    }

                    var answerCount = $('#answers').find('.answers-single').length;
                    if (answerCount === 0) {
                        $('#new-answer-button').show();
                    } else {
                        if (questionType != 13 || answerCount > 1) {
                            $('#new-answer-button').hide();
                        }
                    }

                    // show points field only for polar in survey and simple form
                    if (qsmQuestionSettings.form_type != 0) {
                        if (questionType == 13) {
                            $('#answers .answer-points').show();
                        } else {
                            $('#answers .answer-points').val('').hide();
                        }
                    }
                    if (qsmQuestionSettings.form_type == 0) {
                        if (questionType == 14) {
                            $('.answer-correct-div').hide();
                        } else {
                            $('.answer-correct-div').show();
                        }
                    }

                    if (answer['answerType'] == 'rich' && qsmQuestionSettings.qsm_user_ve === 'true') {
                        var textarea_id = 'answer-' + answer['question_id'] + '-' + answer['index'];
                        wp.editor.remove(textarea_id);
                        let settings = QSMQuestion.getEditorSettings();
                        jQuery(document).trigger('qsm_tinyMCE_settings_after', [settings]);
                        wp.editor.initialize(textarea_id, settings);
                        var anser = QSMQuestion.prepareQuestionText(answer[0]);
                        $(textarea_id).val(anser);
                        tinyMCE.get(textarea_id).setContent(anser);
                    }
                },
                openEditPopup: function (questionID, CurrentElement, useModal) {
                    var shouldUseModal = Boolean(useModal);
                    QSMQuestion.lastEditUsedModal = shouldUseModal;
                    var $question = $(CurrentElement || []);
                    if ($question.length && !$question.hasClass('question')) {
                        $question = $question.closest('.question');
                    }
                    if (!$question.length) {
                        $question = $('.question[data-question-id="' + questionID + '"]').first();
                    }
                    var $inlineContainer = null;
                    var questionElements = null;

                    jQuery('.qsm_tab_content').find('.question').removeClass('opened');

                    if (!shouldUseModal) {
                        if (!$question.length) {
                            return;
                        }
                        if ($question.next('.questionElements').length > 0) {
                            if ($question.next('.questionElements').is(":visible")) {
                                $question.next('.questionElements').slideUp('slow');
                                $('.questions').sortable('enable');
                                $('.page').sortable('enable');
                            } else {
                                $question.addClass('opened');
                                $question.next('.questionElements').slideDown('slow');
                            }
                            return;
                        } else {
                            $question.addClass('opened');
                            $('.questions .questionElements').slideDown('slow');
                            $('.questions .questionElements').remove();
                        }
                        questionElements = $('#modal-1-content').html();
                        $('#modal-1-content').children().remove();
                        // qsm-ui-qe-card: the kit editor card (P-06); the editor stays a sibling right after its row.
                        $question.after("<div style='display: none;' class='questionElements qsm-ui-qe-card'>" + questionElements + "</div>");
                        $inlineContainer = $question.next('.questionElements');
                    } else {
                        if (typeof window.MicroModal !== 'undefined') {
                            window.MicroModal.show('modal-1', { awaitOpenAnimation: true, awaitCloseAnimation: true });
                        } else {
                            $('#modal-1').attr('aria-hidden', 'false').addClass('is-visible');
                        }
                        $('#modal-1-content').scrollTop(0);
                    }

                    //Show question id on question edit screen
                    $('#qsm-question-id').text('ID: ' + questionID);
                    QSMQuestion.prepareCategories();
                    QSMQuestion.processCategories();
                    var question = QSMQuestion.questions.get(questionID);
                    var questionText = QSMQuestion.prepareQuestionText(question.get('name'));
                    $('#edit_question_id').val(questionID);
                    if ( typeof qsmQuestionBankAdapter !== 'undefined' ) {
                        $('#edit_quiz_id').val(question.get('quizID'));
                    }
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
                    if ('' != questionText) {
                        jQuery('.qsm-show-question-desc-box').trigger('click');
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
                    //Get image width value
                    let image_width = question.get('img_width');
                    if (image_width === null || typeof image_width === "undefined") {
                        image_width = '';
                    }
                    //Get image height value
                    let image_height = question.get('img_height');
                    if (image_height === null || typeof image_height === "undefined") {
                        image_height = '';
                    }
                    //Get file upload limit
                    var get_limit_fu = question.get('file_upload_limit');
                    if (get_limit_fu === null || typeof get_limit_fu === "undefined") {
                        get_limit_fu = '4';
                    }
                    //Get checked question type
                    var multicategories = question.get('multicategories');
                    $("input[name='tax_input[qsm_category][]']:checkbox").attr("checked", false);
                    if (multicategories === null || typeof multicategories === "undefined") {
                        //No Action Require
                    } else {
                        $.each(multicategories, function (i, val) {
                            $("input[name='tax_input[qsm_category][]'][value='" + val + "']").prop("checked", true);
                        });
                    }
                    //Get featured image
                    var get_featureImageSrc = question.get('featureImageSrc');
                    var get_featureImageID = question.get('featureImageID');
                    if (get_featureImageSrc === null || typeof get_featureImageSrc === "undefined") {
                        get_featureImageSrc = get_featureImageID = '';
                    }
                    //Get checked question type
                    var get_file_upload_type = question.get('file_upload_type');
                    if (get_file_upload_type === null || typeof get_file_upload_type === "undefined") { } else {
                        $("input[name='file_upload_type[]']:checkbox").attr("checked", false);
                        var fut_arr = get_file_upload_type.split(",");
                        $.each(fut_arr, function (i) {
                            $("input[name='file_upload_type[]']:checkbox[value='" + fut_arr[i] + "']").attr("checked", "true");
                        });
                    }
                    var al = 1;
					_.each(answers, function (answer) {
                        answer['index'] = al;
                        answer['question_id'] = questionID;
                        answer['answerType'] = answerEditor;
                        QSMQuestion.addNewAnswer(answer, question.get('type'));
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
                    $('#image_size-width').val(image_width);
                    $('#image_size-height').val(image_height);
                    $("#question_type").val(question.get('type'));
                    $("#comments").val(question.get('comments'));
                    //Changed checked logic based on new structure for required.
                    $("input#required[value='" + question.get('required') + "']").prop('checked', true);

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
                    //Append feature image
                    if (get_featureImageSrc) {
                        var button = $('.qsm-feature-image-upl');
                        button.empty().append($('<img>', { src: get_featureImageSrc, style: 'width:150px' }));
                        button.next('.qsm-feature-image-rmv').show();
                        button.next().next('.qsm-feature-image-id').val(get_featureImageID);
                        button.next().next().next('.qsm-feature-image-src').val(get_featureImageSrc);
                    }
                    //Append extra settings
                    var all_setting = question.get('settings');
                    var isQuestionBankPage = jQuery('body').hasClass('qsm_page_qsm_question_bank');
                    if ( isQuestionBankPage && all_setting && all_setting.isPublished !== undefined ) {
                        const statusLabel = parseInt(all_setting.isPublished, 10) === 1 ? 'Published' : 'Draft';
                        $(document).find('#qsm-question-status-text').text(statusLabel);
                    }
                    if (!isQuestionBankPage && all_setting?.isPublished === undefined) {
                        $('#qsm-question-status').prop('checked', true).trigger('change');
                    }
                    if (all_setting === null || typeof all_setting === "undefined") { } else {
                        $.each(all_setting, function (index, value) {
                            if ($('#' + index + '_area').length > 0) {
                                if (1 == $('#' + index + '_area').find('input[type=checkbox]').length) {
                                    $(".questionElements input[name='" + index + "'][value='" + value + "']").attr("checked", "true").prop('checked', true);
                                } else if ($('#' + index + '_area').find('input[type=checkbox]').length > 1) {
                                    var fut_arr = value.split(",");
                                    $.each(fut_arr, function (i) {
                                        $(".questionElements input[name='" + index + "[]']:checkbox[value='" + fut_arr[i] + "']").attr("checked", "true").prop('checked', true);
                                    });
                                } else if (value != null) {
                                    $('#' + index).val(value);
                                }
                            }
                            if (index == 'matchAnswer') {
                                $('#match-answer').val(value);
                            }
                            if (index == 'isPublished') {
                                if ( all_setting.isPublished == 1 ) {
                                    $('#qsm-question-status').prop('checked', true).trigger('change');
                                } else {
                                    $('#qsm-question-status').prop('checked', false).trigger('change');
                                }
                            }
                        });
                        jQuery(document).trigger('qsm_all_question_setting_after', [all_setting]);
                    }
                    if (!shouldUseModal && $inlineContainer) {
                        $inlineContainer.slideDown('slow');
                        $('#modal-1-content').html(questionElements);
                        $('.questions').sortable('disable');
                        $('.page').sortable('disable');
                    }

                    if (13 == question.get('type')) {
                        QSMQuestion.prepareEditPolarQuestion(question.get('type'));
                    }
                    QSMQuestion.sync_child_parent_category(questionID);

                    $('#image_size_area').hide();
                    if ('image' === answerEditor) {
                        $('#image_size_area').show();
                    }

                    let link_quizzes_object = question.get('link_quizzes');

                    $('.qsm-linked-list-inside').hide().empty();
                    $('.qsm-linked-list-div-block').hide();
                    if (typeof link_quizzes_object == 'object' && link_quizzes_object != null && Object.keys(link_quizzes_object).length > 0) {
                        Object.values(link_quizzes_object).forEach(function(quizName) {
                            // Ensure each quizName is a valid non-empty string
                            if (quizName && typeof quizName == 'string' && quizName.trim().length > 0) {
                                let link = $('<span></span>')
                                    .attr('class', 'qsm-linked-list-item qsm-ui-linked-item')
                                    .attr('title', quizName)
                                    .text(quizName.length > 25 ? quizName.substring(0, 25) + '...' : quizName);

                                $('.qsm-linked-list-div-block').show();
                                $('.qsm-linked-list-inside').append(link);
                            }
                        });

                        // Add an "Unlink" link at the end
                        let unlink = $('<button type="button"></button>')
                            .attr('class', 'qsm-unlink-the-question qsm-ui-btn qsm-ui-btn-sm qsm-ui-btn-danger-ghost')
                            .attr('data-question-id', questionID)
                            .text(qsm_admin_messages.unlink_question);
                            $('.qsm-linked-list-inside').append(unlink);
                    }
                    QSMQuestion.initAdvancedToggle($inlineContainer);
                    jQuery(document).trigger('qsm_open_edit_popup', [questionID, CurrentElement]);
                },
                openEditPagePopup: function (pageID) {
                    var page = QSMQuestion.qpages.get(pageID);
                    $('#edit_page_id').val(pageID);
                    $("#edit-page-id").text('').text(pageID);
                    jQuery('#page-options').find(':input, select, textarea, :checkbox').each(function (i, field) {
                        field.value = page.get(field.name);
                        if ('undefined' == field.value) {
                            field.value = "";
                        }
                        if (field.type === 'checkbox') {
                            field.value = page.get(field.name);
                            field.checked = field.value === '1';
                        }
                    });
                    MicroModal.show('modal-page-1');
                },
                removeNew: function () {
                    $('.page-new').removeClass('page-new');
                    $('.question-new').removeClass('question-new');
                },
                prepareQuestionText: function (question) {
                    return jQuery('<textarea />').html(question).text();
                },
                prepareEditor: function () {
                    // Fresh, deep-cloned settings per initialize() so the two editors and
                    // the teardown/re-init on each popup open don't share a mutated object.
                    let settings = QSMQuestion.getEditorSettings();
                    jQuery(document).trigger('qsm_tinyMCE_settings_after', [settings]);
                    wp.editor.initialize('question-text', settings);
                    wp.editor.initialize('correct_answer_info', QSMQuestion.getEditorSettings());
                },
                getEditorSettings: function () {
                    // Settings come from PHP ( qsm_get_question_editor_settings() ) via
                    // wp_localize_script, which exposes the `qsm_question_editor_settings`
                    // filter. Fall back to the historical literals for defensiveness.
                    let defaults = {
                        mediaButtons: true,
                        tinymce: {
                            forced_root_block: '',
                            toolbar1: 'formatselect,bold,italic,underline,bullist,numlist,blockquote,alignleft,aligncenter,alignright,alignjustify,link,wp_more,fullscreen,wp_adv',
                            toolbar2: 'strikethrough,hr,forecolor,pastetext,removeformat,charmap,outdent,indent,undo,redo,wp_help,wp_code'
                        },
                        quicktags: true,
                    };
                    let localized = (typeof qsmQuestionSettings !== 'undefined' && qsmQuestionSettings.editor_settings) ? qsmQuestionSettings.editor_settings : defaults;
                    return jQuery.extend(true, {}, localized);
                },
                sync_child_parent_category: function (questionID) {
                    $('.qsm_category_checklist').find('input').each(function (index, input) {
                        $(input).bind('change', function () {
                            var checkbox = $(this);
                            var is_checked = $(checkbox).is(':checked');
                            if (is_checked) {
                                $(checkbox).parents('li').children('label').children('input').prop("checked", true);
                            } else {
                                $(checkbox).parentsUntil('ul').find('input').prop("checked", false);
                            }
                            jQuery(document).trigger('qsm_sync_child_parent_category', [checkbox, questionID]);
                        });
                    });
                },
                question_type_change: function (previous_question_val, questionID) {
                    //you can override this object
                    var ans_type = $('#change-answer-editor').val();
                    if (($.inArray( parseInt( questionID ), [0, 1, 4, 10, 15, 18]) === -1 || 2 == parseInt( questionID )) && 'text' !== ans_type) {
                        $('#change-answer-editor').val('text');
                        $('.answers-single').remove();
                    }
                    if (13 != questionID) {
                        if (!$('#answers').find('.answers-single').length) {
                            $('.new-answer-button').show();
                        }
                        $('.remove-answer-icon').show();
                        $('.answer-points').css('border-color', '');
                        let ans_placeholder = qsm_admin_messages.your_answer;
                        "image" == ans_type && (ans_placeholder = qsm_admin_messages.insert_image_url), $("#answers").find(".answers-single input.answer-text").attr("placeholder", ans_placeholder), $("#answers").find(".answers-single input.answer-points").attr("placeholder", qsm_admin_messages.points);
                    }
                },
                prepareEditPolarQuestion: function (question_val) {
                    var answerType = $('#change-answer-editor').val();
                    if (13 == question_val) {
                        if ($('#answers').find('.answers-single').length < 2) {
                            $('#new-answer-button').trigger('click');
                            if ($('#answers').find('.answers-single').length < 2) {
                                $('#new-answer-button').trigger('click');
                            }
                        }
                        if ($('#answers').find('.answers-single').length > 2) {
                            jQuery('#answers').find('.answers-single').slice(2).remove();
                        }
                        $('.new-answer-button').hide();
                        $('#answers').find('.answers-single .remove-answer-icon').hide();

                        let ans_l_placeholder = qsm_admin_messages.left_label;
                        "image" == answerType && (ans_l_placeholder = "Insert left image URL"), $("#answers").find(".answers-single:first-child input.answer-text").attr("placeholder", ans_l_placeholder);
                        let ans_r_placeholder = qsm_admin_messages.right_label;
                        "image" == answerType && (ans_r_placeholder = "Insert right image URL"), $("#answers").find(".answers-single:last-child input.answer-text").attr("placeholder", ans_r_placeholder), $("#answers").find(".answers-single:first-child input.answer-points").attr("placeholder", qsm_admin_messages.left_range), $("#answers").find(".answers-single:last-child input.answer-points").attr("placeholder", qsm_admin_messages.right_range);
                        if ( "" == $("#answers").find(".answers-single:first-child input.answer-points").val() ) {
                            $("#answers").find(".answers-single:first-child input.answer-points").val(0);
                        }
                        if ( "" == $("#answers").find(".answers-single:last-child input.answer-points").val() ) {
                            $("#answers").find(".answers-single:last-child input.answer-points").val(5);
                        }
                    } else {
                        if (!$('#answers').find('.answers-single').length) {
                            $('.new-answer-button').show();
                        }
                        $('.remove-answer-icon').show();
                        let ans_placeholder = qsm_admin_messages.your_answer;
                        "image" == answerType && (ans_placeholder = qsm_admin_messages.insert_image_url), $("#answers").find(".answers-single input.answer-text").attr("placeholder", ans_placeholder), $("#answers").find(".answers-single input.answer-points").attr("placeholder", qsm_admin_messages.points);
                    }
                }
            };

            $(function () {
                QSMQuestion.pageCollection = Backbone.Collection.extend({
                    model: QSMQuestion.page
                });
                QSMQuestion.qpages = new QSMQuestion.pageCollection();
                QSMQuestion.questionCollection = Backbone.Collection.extend({
                    url: wpApiSettings.root + 'quiz-survey-master/v1/questions',
                    model: QSMQuestion.question
                });
                QSMQuestion.questions = new QSMQuestion.questionCollection();
                $('.new-page-button').on('click', function (event) {
                    event.preventDefault();
                    QSMQuestion.addNewPage();
                });

                $('.questions').on('click', '.move-question-button', function (e) {
                    e.preventDefault();
                    $(this).parents('.question').next('.questionElements').slideUp('slow');
                    MicroModal.show('modal-10');
                    $("#changed_question_page_no, #current_question_page_no").val($(this).parents('.page').data("page-id"));
                    $("#changed_question_position, #current_question_position").val($(this).closest('.question').prevAll('.question').length + 1);
                    $("#current_question_id, #current_question_id").val($(this).parents('.question').data("question-id"));
                });

                //  Confirm move question button
                $("#move-question-button").on('click', function (e) {
                    e.preventDefault();
                    $(this).prop("disabled", true);
                    $("#move-question-error").html("");

                    let changedQuestionPosition = $("#changed_question_position").val();
                    let changedQuestionPageNo = $("#changed_question_page_no").val();
                    let currentQuestionPosition = $("#current_question_position").val();
                    let currentQuestionPageNo = $("#current_question_page_no").val();

                    if (changedQuestionPosition > 0 && changedQuestionPageNo > 0) {
                        let newPageSection = $(".qsm_tab_content .page").eq(changedQuestionPageNo - 1);
                        if (newPageSection.length > 0) {
                            let newElement = newPageSection.find(".question").eq(changedQuestionPosition - 1);
                            let currentPageSection = $(".qsm_tab_content .page").eq(currentQuestionPageNo - 1);
                            let currentElement = currentPageSection.find(".question").eq(currentQuestionPosition - 1);
                            if (newElement.length === 0) {
                                newPageSection.append(currentElement.clone());
                            } else if (currentQuestionPosition == 1 && changedQuestionPageNo == currentQuestionPageNo) {
                                newElement.after(currentElement.clone());
                            } else {
                                newElement.before(currentElement.clone());
                            }

                            currentElement.remove();

                            let questionId = $("#current_question_id").val();
                            let parentPage = $("#changed_question_page_no").val();
                            let model = QSMQuestion.questions.get(questionId);
                            model.set('page', parentPage - 1);
                            QSMQuestion.savePages();
                            clear_move_form_values();
                        } else {
                            $("#move-question-error").html("Page is not available");
                        }
                    } else {
                        $("#move-question-error").html("Please enter positive numbers.");
                    }

                    setTimeout(function () {
                        $("#move-question-error").html("");
                        $("#move-question-button").prop("disabled", false);
                    }, 3000);
                });


                //  Cancel move question button
                $("#cancel-question-button").on('click', function () {
                    clear_move_form_values();
                });

                //  Clear form values
                function clear_move_form_values() {
                    MicroModal.close('modal-10');
                    $("#move-question-form input").val("");
                }

                $('.questions').on('click', '.new-question-button', function (event) {
                    event.preventDefault();
                    if (jQuery('.questionElements').is(':visible')) {
                        $('#save-popup-button').trigger('click');
                    }
                    QSMQuestion.createQuestion($(this).parents('.page').index());
                });

                $('.questions').on('click', '.add-question-bank-button', function (event) {
                    event.preventDefault();
                    QSMQuestion.openQuestionBank($(this).parents('.page').index());
                });

                // ---- Default Question Settings popup (task CU-86d3t3jcn) ----
                // Fills the popup fields from the currently saved defaults.
                function qsmPopulateDefaultQuestionSettings() {
                    let defaults = qsmQuestionSettings.default_question_settings;
                    if (!defaults || typeof defaults !== 'object') {
                        defaults = {};
                    }
                    $('#qsm-default-question-type').val((defaults.type !== undefined && '' !== defaults.type) ? String(defaults.type) : '0');
                    $('#qsm-default-question-category').val((defaults.category !== undefined && '' !== defaults.category) ? String(defaults.category) : '');
                    // required: 0 = required (checkbox on), 1/unset = not required.
                    $('#qsm-default-question-required').prop('checked', defaults.required !== undefined && 0 === Number.parseInt(defaults.required, 10));
                }

                // Open the popup.
                $('.question-controls').on('click', '.qsm-default-question-settings-button', function (event) {
                    event.preventDefault();
                    qsmPopulateDefaultQuestionSettings();
                    MicroModal.show('modal-default-question-settings');
                });

                // Save the defaults.
                $(document).on('click', '#qsm-save-default-question-settings', function (event) {
                    event.preventDefault();
                    var $button = $(this);
                    var settings = {
                        type: $('#qsm-default-question-type').val(),
                        category: $('#qsm-default-question-category').val(),
                        required: $('#qsm-default-question-required').is(':checked') ? 1 : 0
                    };
                    $button.prop('disabled', true);
                    $.ajax({
                        url: ajaxurl,
                        method: 'POST',
                        data: {
                            action: 'qsm_save_default_question_settings',
                            nonce: qsmQuestionSettings.default_question_settings_nonce,
                            quiz_id: qsmQuestionSettings.quizID,
                            type: settings.type,
                            category: settings.category,
                            required: settings.required
                        },
                        success: function (response) {
                            if (response?.status === 'success') {
                                qsmQuestionSettings.default_question_settings = response.settings;
                                QSMAdmin.displayAlert(qsm_admin_messages.default_question_settings_saved, 'success');
                                MicroModal.close('modal-default-question-settings');
                            } else {
                                QSMAdmin.displayAlert(qsm_admin_messages.default_question_settings_error, 'error');
                            }
                        },
                        error: function () {
                            QSMAdmin.displayAlert(qsm_admin_messages.default_question_settings_error, 'error');
                        },
                        complete: function () {
                            $button.prop('disabled', false);
                        }
                    });
                });

                // Reset the defaults back to the system defaults.
                $(document).on('click', '#qsm-reset-default-question-settings', function (event) {
                    event.preventDefault();
                    $('#qsm-default-question-type').val('0');
                    $('#qsm-default-question-category').val('');
                    $('#qsm-default-question-required').prop('checked', false);
                    $.ajax({
                        url: ajaxurl,
                        method: 'POST',
                        data: {
                            action: 'qsm_save_default_question_settings',
                            nonce: qsmQuestionSettings.default_question_settings_nonce,
                            quiz_id: qsmQuestionSettings.quizID,
                            reset: '1'
                        },
                        success: function (response) {
                            if (response?.status === 'success') {
                                qsmQuestionSettings.default_question_settings = response.settings;
                                QSMAdmin.displayAlert(qsm_admin_messages.default_question_settings_reset, 'success');
                            } else {
                                QSMAdmin.displayAlert(qsm_admin_messages.default_question_settings_error, 'error');
                            }
                        },
                        error: function () {
                            QSMAdmin.displayAlert(qsm_admin_messages.default_question_settings_error, 'error');
                        }
                    });
                });

                //Show more question on load more
                $(document).on('click', '.qb-load-more-question', function (event) {
                    event.preventDefault();
                    QSMQuestion.loadQuestionBank();
                });

                //Show searched question
                let qbSearchTimer;
                $(document).on('submit', '#question-bank-search-form', function (event) {
                    event.preventDefault();
                    clearTimeout(qbSearchTimer);
                    QSMQuestion.loadQuestionBank('change');
                });

                if (!jQuery('body').hasClass('qsm_page_qsm_question_bank')) {
                    $('.questions').on('click', '.edit-question-button', function (event) {
                        event.preventDefault();
                        $('.qsm-category-filter').trigger('keyup');
                        QSMQuestion.openEditPopup($(this).parents('.question').data('question-id'), $(this));
                    });
                }
                $('.questions').on('click', '.edit-page-button', function (event) {
                    event.preventDefault();
                    QSMQuestion.openEditPagePopup($(this).parents('.page').data('page-id'));
                });

                $(document).on('click', '.questions .duplicate-question-button', function (event) {
                    event.preventDefault();
                    QSMQuestion.duplicateQuestion($(this).parents('.question').data('question-id'));
                });
                $('.questions').on('click', '.delete-question-button', function (event) {
                    event.preventDefault();
                    remove = $(this);
                    $(this).parents('.question').next('.questionElements').slideUp('slow');
                    // opens-up question-delete modal
                    QSMQuestion.openDeleteOptions($(this).data('question-iid'));
                });
                // removes question from database
                $(document).on('click', '.qsm-delete-question-button-btn', function (e) {
                    e.preventDefault();
                    let question_id = $(this).attr('data-question-iid');
                    let checkedValues = "";
                    if ("selected-questions" == question_id || "all-questions" == question_id) {
                        if ("all-questions" == question_id) {
                            checkedValues = $('.qsm-admin-select-question-input')
                                .map(function () {
                                    return $(this).val();
                                })
                                .get();
                        } else {
                            checkedValues = $('.qsm-admin-select-question-input:checked')
                                .map(function () {
                                    return $(this).val();
                                })
                                .get();
                        }
                        let question_ids = checkedValues.join(',');
                        if (question_ids == undefined || question_ids == null || question_ids == '') {
                            return;
                        }
                        $.ajax({
                            url: ajaxurl,
                            method: 'POST',
                            data: {
                                'action': 'qsm_bulk_delete_question_from_database',
                                'question_id': question_ids,
                                'nonce': qsmQuestionSettings.single_question_nonce
                            },
                            success: function (response) {
                                if (response.success) {
                                    checkedValues.forEach(function (questionId) {
                                        $('.question[data-question-id="' + questionId + '"]').remove();
                                        let model = QSMQuestion.questions.get(questionId);
                                        QSM_Quiz_Broadcast_Channel.unlinkQuestionData(model, questionId);
                                    });
                                    jQuery('.qsm-admin-select-page-question').prop('checked',false);
                                    QSMQuestion.countTotal();
                                    $('.save-page-button').trigger('click');
                                    QSMAdmin.displayAlert(response.data, 'success');
                                } else {
                                    QSMAdmin.displayAlert(response.data, 'error');
                                }
                                jQuery('.qsm-admin-bulk-actions').fadeOut();
                            }
                        });
                    } else {
                        $.ajax({
                            url: ajaxurl,
                            method: 'POST',
                            data: {
                                'action': 'qsm_delete_question_from_database',
                                'question_id': question_id,
                                'nonce': qsmQuestionSettings.single_question_nonce
                            },
                            success: function (response) {
                                if (response.success) {
                                    remove.parents('.question').remove();
                                    let model = QSMQuestion.questions.get(question_id);
                                    QSM_Quiz_Broadcast_Channel.unlinkQuestionData(model, question_id);
                                    QSMQuestion.countTotal();
                                    $('.save-page-button').trigger('click');
                                    QSMAdmin.displayAlert(response.data, 'success');
                                } else {
                                    QSMAdmin.displayAlert(response.data, 'error');
                                }
                            }
                        });
                    }
                    MicroModal.close('modal-7');
                });
                // delete bulk question from database
                $(document).on('click', '#qsm-bulk-delete-question', function (event) {
                    event.preventDefault();
                    QSMQuestion.openDeleteOptions('selected-questions');
                });
                // remove bulk question from quiz
                $(document).on('click', '#qsm-bulk-delete-all-question', function (event) {
                    event.preventDefault();
                    QSMQuestion.openDeleteOptions('all-questions');
                });

                $(document).on('click', '.qsm-admin-select-page-question', function () {
                    let isChecked = $(this).prop('checked');
                    let checkboxesToToggle = $(this).closest('.page').find('.qsm-admin-select-question-input');
                    checkboxesToToggle.prop('checked', isChecked);
                    if (!isChecked) {
                        $(this).prop('checked', false);
                    } else {
                        let allCheckboxesChecked = checkboxesToToggle.length === checkboxesToToggle.filter(':checked').length;
                        $(this).prop('checked', allCheckboxesChecked);
                    }
                    let count = jQuery('.qsm-admin-select-question-input:checked').length;
                    jQuery('.qsm-selected-question-count').html(count);
                    if (count) {
                        jQuery('.qsm-admin-bulk-actions').fadeIn();
                    } else {
                        jQuery('.qsm-admin-bulk-actions').fadeOut();
                    }
                });

                // Kit board bar (G-02): fill "Move to page" before the kit opens the menu.
                $(document).on('mousedown keydown', '.qsm-ui-bulk-move-toggle', function () {
                    QSMQuestion.refreshBulkMoveMenu();
                });
                $(document).on('click', '.qsm-ui-bulk-move-to', function () {
                    QSMQuestion.bulkMoveQuestions(parseInt($(this).attr('data-page-index'), 10));
                });
                $(document).on('click', '.qsm-ui-board-clear', function () {
                    QSMQuestion.clearQuestionSelection();
                });
                $(window).on('pagehide', function () {
                    QSMQuestion.flushKeyboardSave();
                });
                $(document).on('keydown', '.questions .qsm-ui-question-handle', function (event) {
                    if (!event.altKey || ('ArrowUp' !== event.key && 'ArrowDown' !== event.key)) {
                        return;
                    }
                    event.preventDefault();
                    QSMQuestion.keyboardMoveQuestion($(this).closest('.question'), 'ArrowUp' === event.key ? -1 : 1);
                });

                // Add-ons that build the inline editor themselves (Advance Question types 15-20, Flashcards type 18
                // wrap openEditPopup and insert their own .questionElements) fire this event too: give every
                // inline editor the kit card and the Advanced options fold. Safe to run twice on Core's own path.
                $(document).on('qsm_open_edit_popup', function (event, questionID, CurrentElement) {
                    if (is_question_bank_page) {
                        return;
                    }
                    let $question = $(CurrentElement || []).closest('.question');
                    if (!$question.length) {
                        $question = $('.questions .question[data-question-id="' + questionID + '"]').first();
                    }
                    const $editor = $question.next('.questionElements');
                    if ($editor.length) {
                        $editor.addClass('qsm-ui-qe-card');
                        QSMQuestion.initAdvancedToggle($editor);
                        // Again once add-ons' own qsm_open_edit_popup handlers have shown/hidden their rows.
                        setTimeout(function () {
                            QSMQuestion.updateAdvancedCount($editor.find('.qsm-ui-qe-adv'));
                        }, 0);
                    }
                });
                // After the type switch, or an add-on's own control (QSM Ultimate's User Response Mode), has shown/hidden the rows.
                $(document).on('change', '.questionElements :input', function () {
                    const $adv = $(this).closest('.questionElements').find('.qsm-ui-qe-adv');
                    setTimeout(function () {
                        QSMQuestion.updateAdvancedCount($adv);
                    }, 0);
                });
                // Copy a question's ID from its row.
                $(document).on('click', '.qsm-ui-q-id-copy', function (event) {
                    event.preventDefault();
                    event.stopPropagation();
                    const $button = $(this);
                    const id = String($button.data('copy'));
                    const i18n = qsmQuestionSettings.board_i18n || {};
                    const done = function () {
                        clearTimeout($button.data('copyTimer'));
                        $button.addClass('is-copied').attr('title', i18n.copied || 'Copied');
                        wp.a11y && wp.a11y.speak && wp.a11y.speak(i18n.copied || 'Copied');
                        $button.data('copyTimer', setTimeout(function () {
                            $button.removeClass('is-copied').attr('title', i18n.copyId || '');
                        }, 1500));
                    };
                    if (navigator.clipboard && window.isSecureContext) {
                        navigator.clipboard.writeText(id).then(done);
                        return;
                    }
                    const $temp = $('<textarea readonly></textarea>').val(id).css({ position: 'fixed', top: 0, opacity: 0 }).appendTo('body');
                    $temp[0].select();
                    try {
                        if (document.execCommand('copy')) {
                            done();
                        }
                    } catch (e) {}
                    $temp.remove();
                });

                $(document).on('click', '.qsm-admin-randomize-page-questions', function () {
                    let pageId = $(this).data('page-id');
                    QSMQuestion.shuffleQuestions(pageId);
                });

                $(document).on('click', '.qsm-admin-select-question-input', function () {
                    if (!$(this).prop('checked')) {
                        $(this).closest('.page').find('.qsm-admin-select-page-question').prop('checked', false);
                    } else {
                        let allCheckboxesChecked = $(this).closest('.page').find('.qsm-admin-select-question-input:checked').length === $(this).closest('.page').find('.qsm-admin-select-question-input').length;
                        $(this).closest('.page').find('.qsm-admin-select-page-question').prop('checked', allCheckboxesChecked);
                    }
                    if ( $(this).closest('.page').find('.qsm-admin-select-question-input:checked').length ) {
                        $(this).closest('.page').find('.qsm-admin-select-page-question-label').fadeIn();
                    } else {
                        $(this).closest('.page').find('.qsm-admin-select-page-question-label').fadeOut();
                    }
                    let count = jQuery('.qsm-admin-select-question-input:checked').length;
                    jQuery('.qsm-selected-question-count').html(count);
                    if (count) {
                        jQuery('.qsm-admin-bulk-actions').fadeIn();
                    } else {
                        jQuery('.qsm-admin-bulk-actions').fadeOut();
                    }
                });

                // unlink question from  a particular quiz.
                $(document).on('click', '.qsm-unlink-question-button-btn', function (event) {
                    event.preventDefault();
                    let question_id = $(this).attr('data-question-iid');
                    let checkedValues = "";
                    if ("selected-questions" == question_id || "all-questions" == question_id) {
                        if ("all-questions" == question_id) {
                            checkedValues = $('.qsm-admin-select-question-input')
                                .map(function () {
                                    return $(this).val();
                                })
                                .get();
                        } else {
                            checkedValues = $('.qsm-admin-select-question-input:checked')
                                .map(function () {
                                    return $(this).val();
                                })
                                .get();
                        }
                        let question_ids = checkedValues.join(',');
                        if (undefined != question_ids && null != question_ids && '' != question_ids) {
                            checkedValues.forEach(function (questionId) {
                                $('.question[data-question-id="' + questionId + '"]').remove();
                            });
                            QSMQuestion.countTotal();
                            $('.save-page-button').trigger('click');
                            jQuery('.qsm-admin-bulk-actions').fadeOut();
                            jQuery('.qsm-admin-select-page-question').prop('checked',false);
                        }
                    } else {
                        remove.parents('.question').remove();
                        QSMQuestion.countTotal();
                        $('.save-page-button').trigger('click');
                    }
                    MicroModal.close('modal-7');
                });

                $(document).on('click', '.delete-page-button', function (event) {
                    event.preventDefault();
                    var pageID = $(this).parent().siblings('main').children('#edit_page_id').val();
                    const i18n = qsmQuestionSettings.board_i18n || {};
                    QSMQuestion.confirmDanger({
                        title: i18n.delPage || qsm_admin_messages.confirm_message,
                        text: i18n.delPageText,
                        button: i18n.delPageBtn
                    }, function () {
                        $('.page[data-page-id=' + pageID + ']').remove();
                        $('.save-page-button').trigger('click');
                        QSMQuestion.countTotal();
                        MicroModal.close('modal-page-1');
                        location.reload();
                    });
                });
                $(document).on('click', '#answers .delete-answer-button', function (event) {
                    event.preventDefault();
                    $(this).parents('.answers-single').remove();

                    // Check if there are any answers left
                    var answerCount = $('#answers').find('.answers-single').length;
                    if (answerCount === 0) {
                        // If no answers left, show the new-answer-button
                        $('#new-answer-button').show();
                    }
                });
                $(document).on('click', '#delete-action .deletion', function (event) {
                    event.preventDefault();
                    const $questionElements = $(this).parents('.questionElements');
                    $questionElements.slideUp('slow');
                    // Cancel used to leave the board locked (drag handles dead) and the row marked open until Edit was clicked again.
                    $questionElements.prev('.question').removeClass('opened');
                    if (!$('.questions .questionElements:visible').not($questionElements).length) {
                        $('.questions, .page').filter(':data(ui-sortable)').sortable('enable');
                    }
                });
                $(document).on('change', '#qsm-question-status', function (event) {
                    event.preventDefault();
                    if($(this).is(':checked')){
                        $(document).find('#qsm-question-status-text').html('Published');
                    } else {
                        $(document).find('#qsm-question-status-text').html('Draft');
                    }
                });
                $(document).on('click', '#save-popup-button', function (event) {
                    event.preventDefault();
                    var $button = $(this);
                    var $questionElements = $button.closest('.questionElements');
                    if (!$questionElements.length) {
                        return false;
                    }
                    if (6 == $questionElements.find('#question_type').val()) {
                        var question_description = wp.editor.getContent('question-text').trim();
                        if (question_description == '' || question_description == null) {
                            alert(qsm_admin_messages.html_section_empty);
                            return false;
                        }
                    }
                    if (14 == $questionElements.find('#question_type').val()) {
                        var question_description = wp.editor.getContent('question-text').trim();
                        var blanks = question_description.match(/%BLANK%/g);
                        var options_length = $('.answer-text-div').length
                        if ($('#match-answer').val() == 'sequence') {
                            if (blanks == null || blanks.length != options_length) {
                                $('.modal-8-table').html(qsm_admin_messages.blank_number_validation);
                                MicroModal.show('modal-8');
                                return false;
                            }
                        } else if (blanks == null || options_length === 0) {
                            $('.modal-8-table').html(qsm_admin_messages.blank_required_validation);
                            MicroModal.show('modal-8');
                            return false;
                        }
                    }
                    $('#save-edit-question-spinner').addClass('is-active');
                    var isModalContext = $questionElements.is('#modal-1-content');
                    var modalTemplate = null;
                    if (!isModalContext) {
                        modalTemplate = $('#modal-1-content').html();
                        $('#modal-1-content').children().remove();
                    }

                    var questionId = $questionElements.find('#edit_question_id').val();
                    QSMQuestion.saveQuestion(questionId, $button);
                    $('.save-page-button').trigger('click');
                    if (!isModalContext && modalTemplate !== null) {
                        $('#modal-1-content').html(modalTemplate);
                    }
                    jQuery(document).trigger('qsm_save_popup_button_after', [$questionElements]);
                });
                $(document).on('click', '#new-answer-button', function (event) {
                    event.preventDefault();
                    var question_id = $('#edit_question_id').val();
                    var questionType = $('#question_type').val();
                    var answer_length = $('#answers').find('.answers-single').length;
                    var answerType = $('#change-answer-editor').val();
                    let isMultiPolar = {
                        isActive: false,
                    }
                    jQuery(document).trigger('qsm_new_answer_button_before', [isMultiPolar, question_id]);
                    if (answer_length > 1 && $('#question_type').val() == 13 && !isMultiPolar.isActive) {
                        alert(qsm_admin_messages.polar_options_validation);
                        return;
                    }
                    var answer = ['', '', 0];
					answer['index'] = answer_length + 1;
					answer['question_id'] = question_id;
					answer['answerType'] = answerType;
                    QSMQuestion.addNewAnswer(answer, questionType);
                });

                $(document).on('click', '.qsm-add-answer-button', function (event) {
                    event.preventDefault();
                    var question_id = $('#edit_question_id').val();
                    var questionType = $('#question_type').val();
                    var answer_length = $('#answers').find('.answers-single').length;
                    var answerType = $('#change-answer-editor').val();
                    let isMultiPolar = {
                        isActive: false,
                    }
                    jQuery(document).trigger('qsm_new_answer_button_before', [isMultiPolar, question_id]);
                    if (answer_length > 1 && $('#question_type').val() == 13 && !isMultiPolar.isActive) {
                        alert(qsm_admin_messages.polar_options_validation);
                        return;
                    }
                    var answer = ['', '', 0];
                    answer['index'] = answer_length + 1;
                    answer['question_id'] = question_id;
                    answer['answerType'] = answerType;

                    // Find the parent answer section to insert after
                    var $parentAnswer = $(this).closest('.answers-single');
                    QSMQuestion.addNewAnswer(answer, questionType, $parentAnswer);
                });

                $(document).on('click', '.qsm-popup-bank .import-button', function (event) {
                    event.preventDefault();
                    qsm_link_button = '';
                    $(this).text(qsm_admin_messages.adding_question);
                    import_button = $(this);
                    $('.import-button').addClass('disable_import');
                    QSMQuestion.addQuestionFromQuestionBank($(this).data('question-id'));
                    MicroModal.close('modal-2');
                });


                $(document).on('click', '.qsm-popup-bank .link-question', function (event) {
                    event.preventDefault();
                    $(this).text(qsm_admin_messages.linking_question);
                    qsm_link_button = $(this);
                    $('.link-question').addClass('disable_import');
                    // 1 for the linking the questions default is 0
                    QSMQuestion.addQuestionFromQuestionBank($(this).data('question-id'), 1);
                    MicroModal.close('modal-2');
                });

                jQuery(document).on('click', '.qsm-linked-list-div-block .qsm-linked-list-view-button', function () {
                    let $this = jQuery(this);
                    let $inside = $this.parents('.qsm-linked-list-div-block').find('.qsm-linked-list-inside');
                    $inside.toggle();
                    $inside.is(':visible') ? $this.text(qsmQuestionSettings.linked_close) : $this.text(qsmQuestionSettings.linked_view);
                    $this.attr('aria-expanded', $inside.is(':visible') ? 'true' : 'false');
                });

                jQuery(document).on('click', '.qsm-linked-list-div-block .qsm-unlink-the-question', function () {
                    var questionIdToUnlink = jQuery(this).data('question-id');
                    $.ajax({
                        url: ajaxurl,
                        method: 'POST',
                        data: {
                            action: 'qsm_unlink_question_from_list',
                            question_id: jQuery(this).data('question-id'),
                            nonce: qsmQuestionSettings.unlinkNonce
                        },
                        success: function (response) {
                            $(document).find('.qsm-linked-list-div-block').remove();
                            let model = QSMQuestion.questions.get(questionIdToUnlink);
                            QSM_Quiz_Broadcast_Channel.unlinkQuestionData(model, questionIdToUnlink);
                        }
                    });
                });
                //Click on selected question button.
                $('.qsm-popup-bank').on('click', '#qsm-import-selected-question', function (event) {
                    var $total_selction = $('#question-bank').find('[name="qsm-question-checkbox[]"]:checked').length;
                    qsm_link_button = '';
                    if ($total_selction === 0) {
                        alert(qsm_admin_messages.no_question_selected);
                    } else {
                        $.fn.reverse = [].reverse;
                        $($('#question-bank').find('[name="qsm-question-checkbox[]"]:checked').parents('.question-bank-question').reverse()).each(function () {
                            $(this).find('.import-button').text('').text(qsm_admin_messages.adding_question);
                            import_button = $(this).find('.import-button');
                            QSMQuestion.countTotal();
                            QSMQuestion.addQuestionFromQuestionBank($(this).data('question-id'));
                            $(this).find('.import-button').text('').text(qsm_admin_messages.add_question);
                        });
                        $('.import-button').addClass('disable_import');
                        $('#question-bank').find('[name="qsm-question-checkbox[]"]').attr('checked', false);
                        MicroModal.close('modal-2');
                    }
                });
                // Link the checked questions, like each row's Link button. Questions already in this quiz are skipped.
                $('.qsm-popup-bank').on('click', '#qsm-link-selected-question', function () {
                    const $links = $($('#question-bank').find('[name="qsm-question-checkbox[]"]:checked').closest('.question-bank-question').get().reverse()).find('.link-question:not(.disabled)');
                    if (!$links.length) {
                        alert(qsm_admin_messages.no_question_selected);
                        return;
                    }
                    import_button = '';
                    qsm_link_button = $links;
                    $links.text(qsm_admin_messages.linking_question);
                    $('.link-question').addClass('disable_import');
                    $links.each(function () {
                        QSMQuestion.addQuestionFromQuestionBank($(this).data('question-id'), 1);
                    });
                    $('#question-bank').find('[name="qsm-question-checkbox[]"]').prop('checked', false);
                    MicroModal.close('modal-2');
                });
                //Delete question from question bank
                $('.qsm-popup-bank').on('click', '#qsm-delete-selected-question', function (event) {
                    const i18n = qsmQuestionSettings.board_i18n || {};
                    const selectedCount = $('#question-bank').find('[name="qsm-question-checkbox[]"]:checked').length;
                    // Kit confirm (O-02) above the bank popup; same request as before once confirmed.
                    QSMQuestion.confirmDanger({
                        title: (1 === selectedCount ? i18n.delBankOne : (i18n.delBank ? i18n.delBank.replace('%d', selectedCount) : '')) || qsm_admin_messages.confirm_message,
                        text: i18n.delBankText,
                        button: i18n.delBankBtn
                    }, function () {
                        var $total_selction = $('#question-bank').find('[name="qsm-question-checkbox[]"]:checked').length;
                        if ($total_selction === 0) {
                            alert(qsm_admin_messages.no_question_selected);
                        } else {
                            $.fn.reverse = [].reverse;
                            var question_ids = $($('#question-bank').find('[name="qsm-question-checkbox[]"]:checked').parents('.question-bank-question').reverse()).map(function () {
                                return $(this).data('question-id');
                            }).get().join(',');
                            if (question_ids) {
                                $.ajax({
                                    url: ajaxurl,
                                    method: 'POST',
                                    data: {
                                        'action': 'qsm_delete_question_question_bank',
                                        'question_ids': question_ids,
                                        'nonce': qsmQuestionSettings.question_bank_nonce
                                    },
                                    success: function (response) {
                                        var data = jQuery.parseJSON(response);
                                        if (data.success === true) {
                                            $('#question-bank').find('[name="qsm-question-checkbox[]"]:checked').parents('.question-bank-question').slideUp('slow', function () {
                                                $(this).remove();
                                                QSMQuestion.syncBankFooter();
                                            });
                                            alert(data.message);
                                        } else {
                                            alert(data.message);
                                        }
                                    }
                                });
                            }
                        }
                    });
                });

                //Select all button.
                $(document).on('change', '.qsm-question-checkbox', function (event) {
                    event.preventDefault();
                    if ($('.qsm-question-checkbox:checked').length > 0) {
                        $('.qsm-question-bank-footer').addClass('opened');
                    } else {
                        $('.qsm-question-bank-footer').removeClass('opened');
                    }
                });
                $(document).on('change', '#qsm_select_all_question', function (event) {
                    event.preventDefault();
                    $('.qsm-question-checkbox').prop('checked', jQuery('#qsm_select_all_question').prop('checked'));
                    if ($('.qsm-question-checkbox:checked').length > 0) {
                        $('.qsm-question-bank-footer').addClass('opened');
                    } else {
                        $('.qsm-question-bank-footer').removeClass('opened');
                    }
                    QSMQuestion.syncBankFooter();
                });
                $(document).on('change', '#question-bank .qsm-question-checkbox', function () {
                    QSMQuestion.syncBankFooter();
                });
                // Kit bank popup (O-01): the filters panel holds the same three selects.
                $(document).on('click', '.qsm-ui-qb-filters-toggle', function () {
                    const $panel = $('#qsm-question-bank-search');
                    const open = $panel.prop('hidden') || $panel.hasClass('is-closing');
                    $('.qsm-ui-qb-toolbar .qsm-ui-qb-filters-toggle').attr('aria-expanded', open ? 'true' : 'false');
                    // Slide like the inline editor. The panel has no inline style of its own: drop what jQuery leaves.
                    $panel.stop(true).removeClass('is-closing').removeAttr('style');
                    if (open) {
                        $panel.prop('hidden', false);
                        const height = $panel.outerHeight();
                        $panel.hide().slideDown(QSMQuestion.slideSpeed(height), function () {
                            $panel.removeAttr('style');
                        });
                    } else {
                        $panel.addClass('is-closing').slideUp(QSMQuestion.slideSpeed($panel.outerHeight()), function () {
                            $panel.removeClass('is-closing').prop('hidden', true).removeAttr('style');
                        });
                    }
                });
                // The selects only take effect on Apply filters, which also closes the panel.
                $(document).on('click', '.qsm-ui-qb-apply', function () {
                    QSMQuestion.loadQuestionBank('change');
                    if (!$('#qsm-question-bank-search').prop('hidden')) {
                        $('.qsm-ui-qb-toolbar .qsm-ui-qb-filters-toggle').trigger('click');
                    }
                });
                $(document).on('click', '.qsm-ui-qb-clear', function () {
                    $('#question-bank-cat, #question-bank-quiz, #question-bank-type').val('');
                    QSMQuestion.loadQuestionBank('change');
                });
                // Search as you type (Enter still submits the form); clearing the box brings the full list back.
                $(document).on('input', '#question-bank-search-input', function () {
                    clearTimeout(qbSearchTimer);
                    qbSearchTimer = setTimeout(function () {
                        QSMQuestion.loadQuestionBank('change');
                    }, 400);
                });

                $('.save-page-button').on('click', function (event) {
                    event.preventDefault();
                    $('#save-edit-quiz-pages').addClass('is-active');
                    QSMQuestion.savePages();
                });
                $('#save-page-popup-button').on('click', function (event) {
                    event.preventDefault();
                    var pageID = $(this).parent().siblings('main').children('#edit_page_id').val();
                    var pageKey = jQuery('#pagekey').val();
                    if (pageKey.replace(/^\s+|\s+$/g, "").length == 0) {
                        alert(qsm_admin_messages.page_name_required);
                        return false;
                    } else {
                        QSMQuestion.updateQPage(pageID);
                        QSMQuestion.savePages();
                        MicroModal.close('modal-page-1');
                    }
                });

                $(document).on('change', '#change-answer-editor', function (event) {
                    var newVal = $(this).val();
                    if (confirm(qsm_admin_messages.question_reset_message)) {
                        $('#answers').find('.answers-single').remove();
                        $('#new-answer-button').show();
                        $('#image_size_area').hide();
                        if ('image' === newVal) {
                            $('#image_size_area').show();
                        }
                    } else {
                        if (newVal == 'rich') {
                            $(this).val('text');
                        } else {
                            $(this).val('rich');
                        }
                        return false;
                    }
                    var question_val = $('#question_type').val();
                    if (13 == question_val) {
                        QSMQuestion.prepareEditPolarQuestion(question_val);
                    }
                    if (18 == question_val) {
                        jQuery(document).trigger('qsm-change-answer-editor-after');
                    }
                });

                // Adds event handlers for searching questions
                $('#question_search').on('keyup', function () {
                    $('.question').each(function () {
                        if ($(this).text().toLowerCase().indexOf($('#question_search').val().toLowerCase()) === -1) {
                            $(this).hide();
                        } else {
                            $(this).show();
                            $(this).parent('.page').show();

                        }
                    });
                    $('.page').each(function () {
                        if (0 === $(this).children('.question:visible').length) {
                            $(this).hide();
                        } else {
                            $(this).show();
                        }
                    });
                    if (0 === $('#question_search').val().length) {
                        $('.page').show();
                        $('.question').show();
                    }
                });

                qsm_init_sortable();

                if (qsmQuestionSettings.qsm_user_ve === 'true') {
                    QSMQuestion.prepareEditor();
                }

                // Initialize the QSM_Quiz_Broadcast_Channel
                QSM_Quiz_Broadcast_Channel.init();

                if (!jQuery('body').hasClass('qsm_page_qsm_question_bank') && !(window.qsmQuestionBankAdapter && window.qsmQuestionBankAdapter.disableAutoLoad)) {
                    QSMQuestion.loadQuestions();
                }

                /**
                 * Hide/show advanced option
                 */
                $(document).on('click', '#show-advanced-option', function () {
                    var $this = $(this);
                    $(this).next('div.advanced-content').slideToggle('slow', function () {
                        if ($(this).is(':visible')) {
                            $this.text('').html(qsm_admin_messages.hide_advance_options);
                        } else {
                            $this.text('').html(qsm_admin_messages.show_advance_options);
                        }
                    });
                });

                //Hide the question settings based on question type
                var previous_question_val;
                $(document).on('focus', '#question_type', function () {
                    previous_question_val = this.value;
                })
                $(document).on('change', '#question_type', function () {
                    var question_val = $('#question_type').val();
                    QSMQuestion.question_type_change(previous_question_val, question_val);
                    if (6 == question_val) {
                        var question_description = wp.editor.getContent('question-text');
                        if (question_description == 'Add description here!') {
                            tinyMCE.get('question-text').setContent('');
                        }
                    }
                    if (14 == question_val) {
                        $('.answer-correct-div').hide();
                    } else {
                        $('.answer-correct-div').show();
                    }
                    if (15 == question_val || 16 == question_val || 17 == question_val) {
                        if (document.getElementById('modal-advanced-question-type')) {
                            MicroModal.show('modal-advanced-question-type');
                            $('#question_type').val(previous_question_val);
                            return false;
                        }
                    }
                    // show points field only for polar in survey and simple form
                    if (qsmQuestionSettings.form_type != 0) {
                        if (13 == question_val) {
                            $('.answer-points').show();
                        } else {
                            $('.answer-points').val('').hide();
                        }
                    }

                    $('.qsm_hide_for_other').hide();
                    if ($('.qsm_show_question_type_' + question_val).length > 0) {
                        $('.qsm_show_question_type_' + question_val).show();
                    }
                    qsm_hide_show_question_desc(question_val);
                    if (13 == question_val) {
                        QSMQuestion.prepareEditPolarQuestion(question_val);
                    }
                    var answerType = $('#change-answer-editor').val();
                    $('#image_size_area').hide();
                    if ('image' === answerType) {
                        $('#image_size_area').show();
                    }
                    jQuery(document).trigger('qsm_question_type_change_after', [question_val]);
                });

                //Add new category
                $(document).on('click', '#qsm-category-add-toggle', function () {
                    if ($('#qsm-category-add').is(":visible")) {
                        $('.questionElements #new_category_new').attr('checked', false);
                        $('#qsm-category-add').slideUp('slow');
                    } else {
                        $('.questionElements #new_category_new').attr('checked', true).prop('checked', 'checked');
                        $('#qsm-category-add').slideDown('slow');
                    }
                });

                //Hide/show quesion description
                $(document).on('click', '.qsm-show-question-desc-box', function (e) {
                    e.preventDefault();
                    $(this).hide();
                    var question_description = wp.editor.getContent('question-text');
                    if (question_description == '' || question_description == null) {
                        var questionElements = $(this).parents('.questionElements');
                        if (6 == questionElements.find('#question_type').val()) {
                            tinyMCE.get('question-text').setContent('');
                        }
                    }
                    $(this).next('.qsm-row').show();
                });
                $(document).on('click', '.qsm-hide-question-desc-box', function (e) {
                    e.preventDefault();
                    $(this).parents('.qsm-row.qsm-editor-wrap').hide();
                    $('.qsm-show-question-desc-box').show();
                });

                //Open file upload on feature image
                $('body').on('click', '.qsm-feature-image-upl', function (e) {
                    e.preventDefault();
                    var button = $(this),
                        custom_uploader = wp.media({
                            title: qsm_admin_messages.insert_img,
                            library: {
                                // uploadedTo : wp.media.view.settings.post.id, // attach to the current post?
                                type: 'image'
                            },
                            button: {
                                text: qsm_admin_messages.use_img // button label text
                            },
                            multiple: false
                        }).on('select', function () { // it also has "open" and "close" events
                            var attachment = custom_uploader.state().get('selection').first().toJSON();
                            button.empty().append($('<img>', { src: attachment.url, style: 'width:150px' }));
                            button.next('.qsm-feature-image-rmv').show();
                            button.next().next('.qsm-feature-image-id').val(attachment.id);
                            button.next().next().next('.qsm-feature-image-src').val(attachment.url);
                        }).open();
                });

                // on remove button click
                $('body').on('click', '.qsm-feature-image-rmv', function (e) {
                    e.preventDefault();
                    var button = $(this);
                    button.next().val(''); // emptying the hidden field
                    button.next().next().val(''); // emptying the hidden field
                    button.hide().prev().html(qsm_admin_messages.upload_img);
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

            function qsm_hide_show_question_desc(question_type) {
                $('.question-type-description').hide();
                if ($('#question_type_' + question_type + '_description').length > 0) {
                    $('#question_type_' + question_type + '_description').show();
                }
            }

            function qsm_init_sortable() {
                $('.questions').sortable({
                    opacity: 70,
                    cursor: 'move',
                    items: '> .page',
                    handle: '.qsm-ui-page-handle',
                    placeholder: "ui-state-highlight qsm-ui-sort-placeholder",
                    // The kit handles are <button>s, which jQuery UI's default `cancel` refuses to drag from.
                    cancel: 'input, textarea, select, option',
                    forcePlaceholderSize: true,
                    stop: function (evt, ui) {
                        $('.questions > .page').each(function () {
                            var page = parseInt($(this).index()) + 1;
                            $(this).find('.page-number').text('Page ' + page);
                        });
                        setTimeout(
                            function () {
                                $('.save-page-button').trigger('click');
                            },
                            200
                        )
                    }
                });
                $('.page').sortable({
                    items: '.question',
                    handle: '.qsm-ui-question-handle',
                    opacity: 70,
                    cursor: 'move',
                    placeholder: "ui-state-highlight qsm-ui-sort-placeholder",
                    // The kit handles are <button>s, which jQuery UI's default `cancel` refuses to drag from.
                    cancel: 'input, textarea, select, option',
                    forcePlaceholderSize: true,
                    connectWith: '.page',
                    over: QSMQuestion.markDropTarget,
                    out: QSMQuestion.unmarkDropTarget,
                    stop: function (evt, ui) {
                        $('.page').removeClass('qsm-ui-drop-target');
                        setTimeout(
                            function () {
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
            $(document).on('keyup', '.qsm-category-filter', function () {
                search_term = $.trim($(this).val());
                if (search_term == '') {
                    $('.qsm_category_checklist li').each(function () {
                        $(this).show()
                    });
                } else {
                    search_term = new RegExp(search_term.replace(/[.*+?^${}()|[\]\\]/g, '\\$&'), 'i'); // literal match: "(" used to throw
                    $('.qsm_category_checklist li').each(function () {
                        search_string = $(this).children('label').text();
                        result = search_string.search(search_term);
                        if (result > -1) {
                            $(this).show();
                            if ($(this).parent('ul').hasClass('children')) {
                                $(this).parents('li').show();
                            }
                        } else {
                            $(this).hide();
                        }
                    });

                }
            });

            // Category tree (F-25): matches inside a collapsed branch must be visible while searching.
            $(document).on('keyup', '.qsm-ui-cat-tree .qsm-category-filter', function () {
                if ('' !== $.trim($(this).val())) {
                    const $tree = $(this).closest('.qsm-ui-cat-tree');
                    $tree.find('.qsm-ui-tree-toggle').attr('aria-expanded', 'true');
                    $tree.find('ul.children').prop('hidden', false);
                }
            });
            $(document).on('click', '.qsm-ui-cat-add-toggle', function () {
                const $form = $(this).siblings('.qsm-ui-cat-add');
                const open = $form.prop('hidden');
                $form.prop('hidden', !open);
                $(this).attr('aria-expanded', open ? 'true' : 'false');
                if (open) {
                    // Start from what was typed in the search box, like the old Add New Category popup.
                    const typed = $.trim($(this).closest('.qsm-ui-cat-tree').find('.qsm-category-filter').val());
                    $form.find('.qsm-ui-cat-add-name').val(typed).trigger('focus');
                }
            });
            $(document).on('keydown', '.qsm-ui-cat-add-name', function (e) {
                if ('Enter' === e.key) {
                    e.preventDefault();
                    $(this).siblings('.qsm-ui-cat-add-save').trigger('click');
                }
            });
            // Same request as the old popup (save_new_category); the server and its checks are unchanged.
            $(document).on('click', '.qsm-ui-cat-add-save', function () {
                const $form = $(this).closest('.qsm-ui-cat-add');
                const $btn = $(this);
                const $msg = $form.find('.qsm-ui-cat-add-msg');
                const name = $.trim($form.find('.qsm-ui-cat-add-name').val());
                const parent = parseInt($form.find('.qsm-ui-cat-add-parent').val(), 10) || -1;
                const showError = function (text) {
                    $msg.text(text).addClass('is-error');
                };
                $msg.text('').removeClass('is-error');
                if ('' === name) {
                    showError(qsm_admin_messages.category_not_empty);
                    return;
                }
                const exists = _.some(qsmQuestionSettings.categories || [], function (term) {
                    return String(term.category).toLowerCase() === name.toLowerCase();
                });
                if (exists) {
                    showError(qsm_admin_messages.category + ' ' + name + ' ' + qsm_admin_messages.already_exists_in_database);
                    return;
                }
                const i18n = qsmQuestionSettings.board_i18n || {};
                $btn.prop('disabled', true);
                jQuery.ajax(ajaxurl, {
                    data: {
                        action: 'save_new_category',
                        name: name,
                        nonce: qsmQuestionSettings.saveNonce,
                        parent: parent
                    },
                    method: 'POST',
                    dataType: 'json',
                    success: function (response) {
                        $btn.prop('disabled', false);
                        if (response && response.term_id > 0) {
                            QSMQuestion.addCategoryToTree(parseInt(response.term_id, 10), name, parent);
                            $form.find('.qsm-ui-cat-add-name').val('');
                            $form.closest('.qsm-ui-cat-tree').find('.qsm-category-filter').val('').trigger('keyup');
                            if (window.QSMAdminUI) {
                                window.QSMAdminUI.toast(i18n.catAdded || name);
                            }
                        } else if (response && false === response.success) {
                            showError(i18n.catNoAccess);
                        } else {
                            showError(i18n.catError);
                        }
                    },
                    error: function () {
                        $btn.prop('disabled', false);
                        showError(i18n.catError);
                    }
                });
            });

            $(document).on('click', '.add-multiple-category', function (e) {
                e.preventDefault();
                // Guard: if user lost capability after page load, silently bail.
                if (qsmQuestionSettings.can_manage_categories !== 'true') {
                    return false;
                }
                MicroModal.show('modal-9', {
                    onShow: function () {
                        $('#new-category-name').val($('.qsm-category-filter').val());
                        $('.qsm-category-filter').val('').trigger('keyup');
                    },
                    onClose: function () {
                        $('#modal-9-content .info').html('').removeClass('info--success info--error');
                        $('#new-category-name').val('');
                        $('#qsm-parent-category').val(-1);
                        // Re-enable button and restore label on close.
                        let $btn = $('#save-multi-category-button');
                        $btn.prop('disabled', false).text($btn.data('original-text') || 'Save');
                    }
                });
            });

            $(document).on('click', '#save-multi-category-button', function (e) {
                e.preventDefault();

                // Capability guard on the client side.
                if (qsmQuestionSettings.can_manage_categories !== 'true') {
                    $('#modal-9-content .info')
                        .html(qsm_admin_messages.you_cannot_manage_categories || 'You do not have permission to manage categories.')
                        .removeClass('info--success')
                        .addClass('info--error');
                    return false;
                }

                let $btn = $(this);

                // Store original label once.
                if (!$btn.data('original-text')) {
                    $btn.data('original-text', $btn.text().trim());
                }

                duplicate = false;
                new_category = $('#new-category-name').val().trim();
                parent_category = $('#qsm-parent-category option:selected').val();

                let $info = $('#modal-9-content .info');

                if (new_category == '') {
                    $info.html(qsm_admin_messages.category_not_empty)
                        .removeClass('info--success')
                        .addClass('info--error');
                    return false;
                } else {
                    $('#qsm-parent-category option').each(function () {
                        if ($(this).text().toLowerCase() == new_category.toLowerCase()) {
                            duplicate = true;
                            $info.html(qsm_admin_messages.category + ' ' + new_category + ' ' + qsm_admin_messages.already_exists_in_database)
                                .removeClass('info--success')
                                .addClass('info--error');
                            return false;
                        }
                    });

                    if (!duplicate) {
                        // Disable button and show saving state.
                        $btn.prop('disabled', true).text(qsm_admin_messages.creating_category || 'Creating…');
                        $info.html('').removeClass('info--success info--error');

                        var new_category_data = {
                            action: 'save_new_category',
                            name: new_category,
                            nonce: qsmQuestionSettings.saveNonce,
                            parent: parent_category
                        };

                        jQuery.ajax(ajaxurl, {
                            data: new_category_data,
                            method: 'POST',
                            dataType: 'json',
                            success: function (response) {
                                // Re-enable button regardless of outcome.
                                $btn.prop('disabled', false).text($btn.data('original-text'));

                                // Handle wp_send_json_error() (permission denied).
                                if (response?.success === false) {
                                    $info.html(qsm_admin_messages.category_permission_error || 'You do not have permission to create categories.')
                                        .removeClass('info--success')
                                        .addClass('info--error');
                                    return;
                                }

                                if (response?.term_id > 0) {
                                    // Update dropdowns and checklists.
                                    $('#qsm-parent-category').append('<option class="level-0" value="' + response.term_id + '">' + new_category + '</option>');
                                    if (parent_category == -1) {
                                        $('.qsm_category_checklist').prepend('<li id="qsm_category-' + response.term_id + '"><label class="selectit"><input value="' + response.term_id + '" type="checkbox" checked="checked" name="tax_input[qsm_category][]"  id="in-qsm_category-' + response.term_id + '"> ' + new_category + '</label></li>');
                                    } else {
                                        if ($('.qsm_category_checklist li#qsm_category-' + parent_category).children('ul').length > 0) {
                                            $('.qsm_category_checklist li#qsm_category-' + parent_category).children('ul').append('<li id="qsm_category-' + response.term_id + '"><label class="selectit"><input value="' + response.term_id + '" type="checkbox" name="tax_input[qsm_category][]"  id="in-qsm_category-' + response.term_id + '"> ' + new_category + '</label></li>');
                                        } else {
                                            $('.qsm_category_checklist li#qsm_category-' + parent_category).append('<ul class="children"><li id="qsm_category-' + response.term_id + '"><label class="selectit"><input value="' + response.term_id + '" type="checkbox" name="tax_input[qsm_category][]"  id="in-qsm_category-' + response.term_id + '"> ' + new_category + '</label></li></ul>');
                                        }
                                        $('.qsm_category_checklist li#qsm_category-' + response.term_id).children('label').children('input').prop('checked', true);
                                        $('.qsm_category_checklist li#qsm_category-' + response.term_id).parents('li').each(function () {
                                            $(this).children('label').children('input').prop('checked', true);
                                        });
                                    }

                                    // Show success message and auto-close after 2 s.
                                    $info.html(qsm_admin_messages.category_created_success || 'Category "' + new_category + '" created successfully!')
                                        .removeClass('info--error')
                                        .addClass('info--success');

                                    setTimeout(function () {
                                        MicroModal.close('modal-9');
                                    }, 2000);
                                } else {
                                    $info.html(qsm_admin_messages.category_save_error || 'Failed to create category. Please try again.')
                                        .removeClass('info--success')
                                        .addClass('info--error');
                                }
                            },
                            error: function () {
                                $btn.prop('disabled', false).text($btn.data('original-text'));
                                $info.html(qsm_admin_messages.category_save_error || 'An error occurred. Please try again.')
                                    .removeClass('info--success')
                                    .addClass('info--error');
                            }
                        });
                    }
                }
            });
        }
    }
}(jQuery));
