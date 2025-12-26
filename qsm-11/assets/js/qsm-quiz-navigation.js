/**
 * QSM-11 Quiz Navigation JavaScript
 * 
 * Handles navigation, pagination, and validation functionality
 * for the new QSM-11 rendering system
 * 
 * @package QSM
 * @version 1.0.0
 */
var show_result_validation = true;
(function($) {

    // Global namespace for QSM New Navigation
    window.QSMPagination = window.QSMPagination || {};

    /**
     * Main QSM New Navigation Object
     */
    QSMPagination.Navigation = {
        quizObjects: {},
        timeTakenIntervals: {},
        videoAttributePatterns: [
            /\ssrc="([^"]+)"/,
            /\smp4="([^"]+)"/,
            /\sm4v="([^"]+)"/,
            /\swebm="([^"]+)"/,
            /\sogv="([^"]+)"/,
            /\swmv="([^"]+)"/,
            /\sflv="([^"]+)"/,
            /\swidth="(\d+)"/,
            /\sheight="(\d+)"/
        ],
        config: {
            selectors: {
                quizContainer: '.qsm-quiz-container',
                form: '.qsm-quiz-form',
                page: '.qsm-page',
                pagination: '.qsm-pagination',
                previousBtn: '.qsm-previous-btn, .qsm-previous',
                nextBtn: '.qsm-next-btn, .qsm-next',
                submitBtn: '.qsm-submit-btn',
                startBtn: '.qsm-start-btn',
                pageCounter: '.qsm-page-counter',
                errorMessage: '.qsm-error-message',
                modalSubmitBtn: '.submit-the-form',
                timerField: '#timer',
                timerMsField: 'input[name="timer_ms"]'
            },
            classes: {
                hidden: 'qsm-hidden',
                disabled: 'qsm-disabled',
                loading: 'qsm-loading',
                error: 'qsm-error',
                current: 'qsm-current-page'
            },
            animation: {
                duration: 1000,
                easing: 'swing'
            }
        },

        submit_status: true,

        /**
         * Get quiz data from either legacy or new variable
         * Provides backward compatibility
         */
        getQuizData: function (quizId) {
            var data = {};

            if (window.qmn_quiz_data && window.qmn_quiz_data[quizId] && typeof window.qmn_quiz_data[quizId] === 'object') {
                return window.qmn_quiz_data[quizId];
            }
            
            var el = jQuery('#qsm-quiz-json-' + quizId);

            if (el.length && typeof atob === 'function') {
                try {
                    var encoded = el.text().trim();

                    if (encoded) {
                        var decoded = atob(encoded);
                        var parsed = JSON.parse(decoded);

                        if (parsed && parsed.quiz_data) {
                            return parsed.quiz_data;
                        }
                    }
                } catch (e) {
                    console.warn('QSM: Failed to load quiz JSON fallback for quiz:', quizId, e);
                }
            }

            return data;
        },


        /**
         * Initialize navigation for all quizzes on page
         */
        init: function(initialPage = 1) {
            let self = this;
            
            // Initialize each quiz form found
            $(this.config.selectors.quizContainer).each(function() {
                
                let $quizContainer = $(this);
                let quizId = self.getQuizId($quizContainer);
                
                if (quizId) {
                    self.initQuiz(quizId, $quizContainer,initialPage);
                }
            });
        },
        initQuizObject: function(quizId, $quizContainer,initialPage) {

            // Get quiz data from backend config
            let quizData = this.getQuizData(quizId);

            // Find all pages for this quiz
            let $pages = $quizContainer.find(this.config.selectors.page);

            // Determine if first page is disabled
            let hasFirstPage = (quizData.disable_first_page != 1);

            // Question pages count (excluding intro page)
            let questionPages = hasFirstPage ? $pages.length - 1 : $pages.length;

            // Build quiz object
            this.quizObjects[quizId] = {
                id: quizId,
                quizContainer: $quizContainer,
                form: $quizContainer.find(this.config.selectors.form),
                pagination: jQuery('.qsm-pagination-' + quizId),
                nonceValue: $quizContainer.find('#qsm_nonce_' + quizId).val() 
                            || $quizContainer.find('input[name="qsm_nonce"]').val(),

                pages: $pages,
                currentPage: initialPage,
                totalPages: $pages.length,
                questionPages: questionPages,
                hasFirstPage: hasFirstPage,
                data: quizData,

                // Validation state
                validation: {
                    enabled: true,
                    errors: []
                },

                // Runtime values (extendable later)
                runtime: {
                    initialized: false,
                    started: false,
                    completed: false,
                    timeTakenSeconds: 0,
                }
            };

            return this.quizObjects[quizId];
        },


        /**
         * Initialize a specific quiz
         */
        initQuiz: function(quizId, $quizContainer,initialPage) {
            

            // Create quiz object
            let quizObj = this.initQuizObject(quizId, $quizContainer,initialPage);

            // Initialize pagination UI
            this.initPagination(quizId);

            // Show first page
            this.showPage(quizId, quizObj.currentPage);
            //this.showPage(quizId, 1);

            // Timer
            let self = this;
            setInterval(function() {
                self.qsmQuizTimeTakenTimer(quizId);
            }, 1000);

            // Progress Bar
            if (window.QSMPagination && window.QSMPagination.ProgressBar) {
                window.QSMPagination.ProgressBar.initProgressBar(
                    quizId,
                    $quizContainer,
                    quizObj.form
                );
            }

            // Bind events
            this.bindEvents(quizId);

            // Mark as initialized
            quizObj.runtime.initialized = true;

            // Fire event
            $(document).trigger('qsm_quiz_initialized', [quizId, quizObj]);
        },


        qsmQuizTimeTakenTimer: function(quizId) {
            let currentQuiz = this.quizObjects[quizId];
            if (!currentQuiz) return;
            
            let $timerField = currentQuiz.quizContainer.find(this.config.selectors.timerField);
            if ($timerField.length) {
                let timerValue = parseInt($timerField.val()) || 0;
                if (isNaN(timerValue)) {
                    timerValue = 0;
                }
                timerValue++;
                $timerField.val(timerValue);
            }
        },

        /**
         * Initialize pagination for a quiz
         */
        initPagination: function(quizId) {
            let currentQuiz = this.quizObjects[quizId];
            if (!currentQuiz) return;

            // Hide all pages initially using jQuery hide()
            currentQuiz.pages.hide();
            
            // Initialize navigation buttons and show page counter
            this.updateNavigationButtons(quizId);
            this.updatePageCounter(quizId);
        },

        /**
         * Initialize validation
         */
        initValidation: function(quizId) {
            let currentQuiz = this.quizObjects[quizId];
            if (!currentQuiz) return;

            // Set up validation rules based on quiz configuration
            currentQuiz.validation.rules = this.getValidationRules(quizId);
        },

        /**
         * Bind events for a quiz instance
         */
        bindEvents: function(quizId) {
            let self = this;
            let currentQuiz = this.quizObjects[quizId];
            let $form = currentQuiz.form;
            let $container = currentQuiz.quizContainer;
            let $pagination_container = jQuery('.qsm-pagination-'+quizId);
            let $start_btn = '.qsm-start-btn-'+quizId;
            let $prev_btn = '.qsm-previous-btn-'+quizId;
            let $next_btn = '.qsm-next-btn-'+quizId;
            let $submit_btn = '.qsm-submit-btn-'+quizId;
            
            // =========================================
            // 🔥 Prevent duplicate click handlers
            // =========================================
            jQuery(document).off('click', $start_btn);
            jQuery(document).off('click', $prev_btn);
            jQuery(document).off('click', $next_btn);
            jQuery(document).off('click', $submit_btn);
            
            // Navigation button clicks - bind to container since navigation is outside form
           jQuery(document).on('click', $prev_btn, function(e) {
                e.preventDefault();
                console.log('Previous button clicked for quiz:', quizId);
                self.previousPage(quizId);
            });

           jQuery(document).on('click', $next_btn, function(e) {
                e.preventDefault();
                self.nextPage(quizId);
                console.log('Next button clicked for quiz:', quizId);
            });
            // Start button click - bind to container (multiple selectors for compatibility)
            jQuery(document).on('click', $start_btn, function(e) {
                e.preventDefault();
                console.log('Start button clicked for quiz:', quizId);
                self.startQuiz(quizId);
                // Validate current page before proceeding
                if (!self.validateCurrentPage(quizId)) {
                    return;
                }
                self.nextPage(quizId);
            });

            // Submit button click - bind to container since submit button is in navigation
           jQuery(document).on('click', $submit_btn, function(e) {
                e.preventDefault();
                console.log('Submit button clicked for quiz:', quizId);
                if (!self.validateForm(quizId)) {
                    return false;
                }
                
                self.submitForm(quizId);
                return false;
            });
            
            $(document).on('click', this.config.selectors.modalSubmitBtn, function(e) {
                e.preventDefault();
                
                if (!self.validateForm(quizId)) {
                    return false;
                }
                
                self.submitForm(quizId);
                return false;
            });

            // Form submission
            $form.on('submit', function(e) {
                e.preventDefault();
                
                if (!self.validateForm(quizId)) {
                    return false;
                }
                
                self.submitForm(quizId);
                return false;
            });

            // Input change events for real-time validation
            $form.on('change input', 'input, select, textarea', function() {
                self.clearFieldError($(this));
            });

            // Keyboard navigation
            $form.on('keydown', function(e) {
                self.handleKeyboardNavigation(e, quizId);
            });
            
            // Answer selection event handlers
            self.bindAnswerEvents(quizId);
        },
        
        /**
         * Bind answer selection event handlers (matching legacy qsm-quiz.js)
         */
        bindAnswerEvents: function(quizId) {
            let self = this;
            let currentQuiz = this.quizObjects[quizId];
            let $container = currentQuiz.quizContainer;

            // Multiple choice radio buttons and dropdowns (matching legacy: .qmn-multiple-choice-input, .qsm_dropdown, .mlw_answer_date)
            $container.on('change', '.qmn-multiple-choice-input, .qsm_dropdown, .mlw_answer_date', function(e) {
                let $i_this = $(this);
                let value = $i_this.val();
                let $this = $i_this.closest('.quiz_section, .qsm-question');
                let question_id = $i_this.attr('name').split('question')[1];
                let inputType;
                
                if ($i_this.hasClass('mlw_answer_date')) {
                    inputType = 'input';
                } else {
                    inputType = 'radio';
                }
                
                let quizData = self.getQuizData(quizId);
                
                // Quick result / inline feedback
                if (quizData.enable_quick_result_mc == 1) {
                    self.qsmShowInlineResult(quizId, question_id, value, $this, inputType, $i_this);
                } else if (quizData.enable_quick_correct_answer_info != 0) {
                    let data = self.qsmQuestionQuickResultJs(question_id, value, inputType, quizData.enable_quick_correct_answer_info, quizId);
                    $this.find('.quick-question-res-p, .qsm-inline-correct-info').remove();
                    if (value && value.length > 0 && data.success != '') {
                        $this.append('<div class="qsm-inline-correct-info">' + self.qsmCheckShortcode(data.message) + '</div>');
                    }
                }
                
                // Trigger after select answer event (for addons)
                $(document).trigger('qsm_after_select_answer', [quizId, question_id, value, $this, inputType]);

                // Auto-submit if answer is wrong
                if (quizData.end_quiz_if_wrong > 0 && !$container.find('.qsm-next-btn:visible').length) {
                    self.qsmSubmitQuizIfAnswerWrong(quizId, question_id, value, $this, currentQuiz.form);
                }
            });
            
            // Multiple response checkboxes (matching legacy: .qsm-multiple-response-input)
            $container.on('change', '.qsm-multiple-response-input', function(e) {
                let $i_this = $(this);
                let question_id = $i_this.attr('name').split('question')[1];
                let $this = $i_this.closest('.quiz_section, .qsm-question');
                let parent = $i_this.closest('.qmn_check_answers, .qsm-answer-options');
                let checkedValues = parent.find('input[type="checkbox"]:checked').map(function() {
                    return $(this).val();
                }).get();
                
                let quizData = self.getQuizData(quizId);

                // Auto-submit if answer is wrong
                if (quizData.end_quiz_if_wrong > 0 && !$container.find('.qsm-next-btn:visible').length) {
                    self.qsmSubmitQuizIfAnswerWrong(quizId, question_id, checkedValues, $this, currentQuiz.form, 'checkbox');
                }
                
                // Quick result / inline feedback
                if (quizData.enable_quick_result_mc == 1) {
                    self.qsmShowInlineResult(quizId, question_id, checkedValues, $this, 'checkbox', $i_this);
                } else if (quizData.enable_quick_correct_answer_info != 0) {
                    let data = self.qsmQuestionQuickResultJs(question_id, checkedValues, 'checkbox', quizData.enable_quick_correct_answer_info, quizId);
                    $this.find('.quick-question-res-p, .qsm-inline-correct-info').remove();
                    if (checkedValues.length > 0 && data.success != '') {
                        $this.append('<div class="qsm-inline-correct-info">' + self.qsmCheckShortcode(data.message) + '</div>');
                    }
                }
                
                // Trigger after select answer event (for addons)
                $(document).trigger('qsm_after_select_answer', [quizId, question_id, checkedValues, $this, 'checkbox']);
            });
            
            // File upload functionality (matching legacy: .mlw_answer_file_upload)
            self.bindFileUploadEvents(quizId);
            
            // Text, number, and fill-blank inputs (matching legacy: .mlw_answer_open_text, .mlw_answer_number, .qmn_fill_blank)
            let qsm_inline_result_timer;
            $container.on('keyup', '.mlw_answer_open_text, .mlw_answer_number, .qmn_fill_blank', function(e) {
                let $i_this = $(this);
                let question_id = $i_this.attr('name').split('question')[1];
                let $this = $i_this.closest('.quiz_section, .qsm-question');
                let value;
                
                if ($i_this.hasClass('qmn_fill_blank')) {
                    value = $this.find('.qmn_fill_blank').map(function() {
                        let val = $(this).val();
                        return val ? val : null;
                    }).get().filter(function(v) { return v !== null; });
                } else {
                    value = $i_this.val();
                }
                
                let sendValue;
                if (typeof value === 'string') {
                    sendValue = value.trim();
                } else if (value.length) {
                    sendValue = value[value.length - 1];
                } else {
                    sendValue = '';
                }
                
                let quizData = self.getQuizData(quizId);
                
                clearTimeout(qsm_inline_result_timer);
                qsm_inline_result_timer = setTimeout(function() {
                    let showFeedback = true;
                    
                    // For fill-blank, only show feedback when all blanks are filled
                    if ($i_this.hasClass('qmn_fill_blank')) {
                        let $allBlanks = $this.find('.qmn_fill_blank');
                        let totalBlanks = $allBlanks.length;
                        let filledBlanks = $allBlanks.filter(function() {
                            return $(this).val().trim() !== '';
                        }).length;
                        showFeedback = (totalBlanks > 0 && filledBlanks === totalBlanks);
                        
                        if (!showFeedback) {
                            $this.find('.quick-question-res-p, .qsm-inline-correct-info').remove();
                        }
                    }
                    
                    if (showFeedback) {
                        if (quizData.enable_quick_result_mc == 1) {
                            self.qsmShowInlineResult(quizId, question_id, sendValue, $this, 'input', $i_this, $this.find('.qmn_fill_blank').index($i_this));
                        } else if (quizData.enable_quick_correct_answer_info != 0) {
                            let data = self.qsmQuestionQuickResultJs(question_id, sendValue, 'input', quizData.enable_quick_correct_answer_info, quizId, $this.find('.qmn_fill_blank').index($i_this));
                            $this.find('.quick-question-res-p, .qsm-inline-correct-info').remove();
                            if (value.length > 0 && data.success != '') {
                                $this.append('<div class="qsm-inline-correct-info">' + self.qsmCheckShortcode(data.message) + '</div>');
                            }
                        }
                    }
                    
                    // Trigger after select answer event (for addons)
                    $(document).trigger('qsm_after_select_answer', [quizId, question_id, value, $this, 'input', $this.find('.qmn_fill_blank').index($i_this)]);
                }, 2000);
            });
        },
        
        /**
         * Bind file upload event handlers (matching legacy file upload functionality)
         */
        bindFileUploadEvents: function(quizId) {
            let self = this;
            let currentQuiz = this.quizObjects[quizId];
            let $container = currentQuiz.quizContainer;
            
            // File input change event - validation and upload
            $container.on('change', '.quiz_section .mlw_answer_file_upload, .qsm-question .mlw_answer_file_upload', async function() {
                let $this = $(this);
                let file_data = $this.prop('files')[0];
                
                if (!file_data) {
                    await self.qsmRemoveUploadedFile($this.parent('.quiz_section, .qsm-question').find('.qsm-file-upload-container').find('.remove-uploaded-file'));
                    return false;
                }
                
                let question_id = $this.parent('.quiz_section, .qsm-question').find('.mlw_answer_file_upload').attr("name").replace('qsm_file_question', '');
                let quizData = self.getQuizData(quizId);
                let file_upload_type = quizData.questions_settings && quizData.questions_settings[question_id] ? quizData.questions_settings[question_id].file_upload_type : '';
                let file_upload_limit = quizData.questions_settings && quizData.questions_settings[question_id] ? quizData.questions_settings[question_id].file_upload_limit : 1;
                file_upload_limit = file_upload_limit || 1; // Default 1MB
                
                let $file_upload_status = $this.parent('.quiz_section, .qsm-question').find('.qsm-file-upload-status');
                $file_upload_status.removeClass('qsm-error qsm-success qsm-processing');
                $file_upload_status.addClass('qsm-processing');
                
                // Get processing message
                let processingMsg = (typeof qmn_ajax_object !== 'undefined' && qmn_ajax_object.validate_process) ? qmn_ajax_object.validate_process : 'Processing...';
                $file_upload_status.text(processingMsg).show();
                
                // Build allowed mime types array
                let allowed_mime_types = [];
                if (file_upload_type) {
                    let types = file_upload_type.split(',');
                    types.forEach(function(type) {
                        type = type.trim();
                        if (type === 'image') {
                            allowed_mime_types.push('image/jpeg', 'image/png', 'image/x-icon', 'image/gif', 'image/webp');
                        } else if (type === 'doc') {
                            allowed_mime_types.push('application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document');
                        } else if (type === 'excel') {
                            allowed_mime_types.push('application/excel', 'application/vnd.ms-excel', 'application/x-excel', 'application/x-msexcel', 
                            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'text/csv');
                        } else if (type) {
                            allowed_mime_types.push(type);
                        }
                    });
                }
                
                let trigger_message, trigger_type = '';
                
                // Validate file type and size
                if (!allowed_mime_types.includes(file_data.type) || (file_upload_limit > 0 && file_data.size > file_upload_limit * 1024 * 1024)) {
                    let warning_message = '';
                    if (file_upload_limit > 0 && file_data.size > file_upload_limit * 1024 * 1024) {
                        warning_message = (typeof qmn_ajax_object !== 'undefined' && qmn_ajax_object.invalid_file_size) ? 
                                        qmn_ajax_object.invalid_file_size + file_upload_limit + 'MB' : 
                                        'File size must not exceed ' + file_upload_limit + 'MB';
                    } else {
                        warning_message = (typeof qmn_ajax_object !== 'undefined' && qmn_ajax_object.invalid_file_type) ? 
                                        qmn_ajax_object.invalid_file_type + file_upload_type : 
                                        'Invalid file type. Allowed: ' + file_upload_type;
                    }
                    $this.val('');
                    $file_upload_status.removeClass('qsm-processing');
                    $file_upload_status.addClass('qsm-error').text(warning_message).show();
                    trigger_message = warning_message;
                    trigger_type = 'error';
                } else {
                    // Validation passed - update UI to show success
                    $file_upload_status.removeClass('qsm-error qsm-processing');
                    $file_upload_status.addClass('qsm-success');
                    $this.parent('.quiz_section, .qsm-question').find('.qsm-file-upload-name').html($this[0].files[0].name).show();
                    $this.parent('.quiz_section, .qsm-question').find('.qsm-file-upload-container').find('.remove-uploaded-file').show();
                    
                    let successMsg = (typeof qmn_ajax_object !== 'undefined' && qmn_ajax_object.validate_success) ? qmn_ajax_object.validate_success : 'File uploaded successfully';
                    $file_upload_status.text(successMsg).show();
                    trigger_message = successMsg;
                    trigger_type = 'success';
                }
                
                // Trigger event for any listeners
                const obj = {
                    type: trigger_type,
                    message: trigger_message,
                    file_name: file_data.name,
                    file_type: file_data.type,
                    file_size: file_data.size,
                    file_path: '',
                    file_url: '',
                };
                $(document).trigger('qsm_after_file_upload', [$this.parent(), obj]);
                
                return false;
            });
            
            // Remove file click event
            $container.on('click', '.quiz_section .remove-uploaded-file, .qsm-question .remove-uploaded-file', async function() {
                await self.qsmRemoveUploadedFile($(this));
                return false;
            });
            
            // Click on upload container to trigger file input
            $container.on('click', '.quiz_section .qsm-file-upload-container, .qsm-question .qsm-file-upload-container', function(e) {
                e.preventDefault();
                // Don't trigger file upload if clicking on remove button
                if (!$(e.target).hasClass('remove-uploaded-file')) {
                    $(this).prev('.mlw_answer_file_upload').trigger('click');
                }
            });
            
            // Drag and drop events
            $container.on('dragover', '.quiz_section .qsm-file-upload-container, .qsm-question .qsm-file-upload-container', function(e) {
                e.preventDefault();
                e.stopPropagation();
                $(this).addClass('file-hover');
            });
            
            $container.on('dragenter', '.quiz_section .qsm-file-upload-container, .qsm-question .qsm-file-upload-container', function(e) {
                e.preventDefault();
                e.stopPropagation();
            });
            
            $container.on('dragleave', '.quiz_section .qsm-file-upload-container, .qsm-question .qsm-file-upload-container', function(e) {
                e.preventDefault();
                e.stopPropagation();
                $(this).removeClass('file-hover');
            });
            
            $container.on('drop', '.quiz_section .qsm-file-upload-container, .qsm-question .qsm-file-upload-container', function(e) {
                $(this).removeClass('file-hover');
                if (e.originalEvent.dataTransfer) {
                    if (e.originalEvent.dataTransfer.files.length) {
                        e.preventDefault();
                        e.stopPropagation();
                        $(this).find('.qsm-file-upload-name').html(e.originalEvent.dataTransfer.files[0].name).fadeIn();
                        $(this).prev('.mlw_answer_file_upload').prop('files', e.originalEvent.dataTransfer.files);
                        $(this).prev('.mlw_answer_file_upload').trigger('change');
                    }
                }
            });
            
            $container.on('mouseleave', '.quiz_section .qsm-file-upload-container, .qsm-question .qsm-file-upload-container', function() {
                $(this).removeClass('file-hover');
            });

            //Deselect all answer on select
            jQuery('.qsm-deselect-answer').click(function (e) {
                e.preventDefault();
                jQuery(this).parents('.quiz_section').find('input[type="radio"]').prop('checked', false);
                jQuery(this).parents('.quiz_section').find('input[type="radio"]:hidden').prop('checked', true);
            });

            //Submit the form on popup click
            jQuery(document).on('click', '.submit-the-form', function (e) {
                e.preventDefault();
                // Triggger the click event on the quiz form's submit button.
                jQuery('.qsm-submit-btn').trigger('click');
                jQuery('#modal-3').removeClass('is-open');
            });

            jQuery('.pagetime-goto-nextpage').click(function (e) {
                e.preventDefault();
                var quiz_id = jQuery(this).data('quiz_id');
                var $container = jQuery('#quizForm' + quiz_id).closest('.qmn_quiz_container');
                if(!$container.find('.qsm-submit-btn').is(':visible')) {
                    this.nextPage(quiz_id);
                    this.scrollToQuiz(quiz_id);
                }else{
                    $container.find(".mlw_custom_next").hide();
                }
            });

            jQuery(document).on('keyup', '.mlwPhoneNumber', function (e) {
                this.value = this.value.replace(/[^- +()0-9\.]/g, '');
            });

            jQuery(document).on('click', '.qsm_social_share_link', function (e) {
                e.preventDefault();
                var network = jQuery(this).attr('data-network');
                var share_url = jQuery(this).attr('data-link');
                var social_text = jQuery(this).attr('data-text');
                var social_id = jQuery(this).attr('data-id');
                var url = '';
                if (network == 'facebook') {
                    url = "https://www.facebook.com/dialog/feed?" + "display=popup&" + "app_id=" + social_id +
                        "&" + "link=" + encodeURIComponent(share_url) + "&" + "name=" + social_text;
                }
                if (network == 'twitter') {
                    url = "https://twitter.com/intent/tweet?text=" + social_text;
                }
                if (network == 'linkedin') {
                    url = "https://www.linkedin.com/feed/?text=" + social_text;
                }
                var sTop = window.screen.height / 2 - (218);
                var sLeft = window.screen.width / 2 - (313);
                var sqShareOptions = "height=400,width=580,toolbar=0,status=0,location=0,menubar=0,directories=0,scrollbars=0,top=" + sTop + ",left=" + sLeft;
                window.open(url, "Share", sqShareOptions);
                return false;
            });
        },
        
        /**
         * Remove uploaded file (matching legacy qsm_remove_uploaded_file_fd_question)
         */
        qsmRemoveUploadedFile: async function($removeButton) {
            let parents_section = $removeButton.parents('.quiz_section, .qsm-question');
            parents_section.find('.qsm-file-upload-status').removeClass('qsm-processing qsm-success');
            parents_section.find('.qsm-file-upload-status').addClass('qsm-error');
            
            let removeMsg = (typeof qmn_ajax_object !== 'undefined' && qmn_ajax_object.remove_file) ? qmn_ajax_object.remove_file : 'Removing file...';
            parents_section.find('.qsm-file-upload-status').html(removeMsg).show();
            parents_section.find('.qsm-file-upload-name').html('').show();
            $removeButton.hide();
            parents_section.find('.mlw_answer_file_upload').val('');
            
            let successMsg = (typeof qmn_ajax_object !== 'undefined' && qmn_ajax_object.remove_file_success) ? qmn_ajax_object.remove_file_success : 'File removed successfully';
            parents_section.find('.qsm-file-upload-status').text(successMsg);
            
            // Trigger after file remove event
            $(document).trigger('qsm_after_file_remove', [$removeButton.parent(), {type: 'success', message: successMsg}]);
        },

        /**
         * Start the quiz (handle start button click)
         */
        startQuiz: function(quizId) {
            let quizData = this.quizObjects[quizId];
            if (!quizData) return;
            
            // Start timer if available
            if (window.QSMPagination && window.QSMPagination.Timer) {
                window.QSMPagination.Timer.start(quizId);
            }
            
            // Trigger quiz started event
            $(document).trigger('qsm_quiz_started', [quizId, quizData]);
        },

        /**
         * Navigate to specific page (1-based indexing)
         */
        goToPage: function(quizId, pageNumber, updateHistory) {
            let self = this;
            let quizData = self.quizObjects[quizId];
            if (!quizData || pageNumber < 1 || pageNumber > quizData.totalPages) {
                return;
            }

            jQuery('.qsm-multiple-response-input:checked, .qmn-multiple-choice-input:checked , .qsm_select:visible').each(function () {
                if (quizData.data.end_quiz_if_wrong > 0 && jQuery(this).parents().is(':visible') && jQuery(this).is('input, select')) {
                    if (jQuery(this).parents('.qmn_radio_answers, .qsm_check_answer')) {
                        console.log(jQuery(this));
                        let question_id = jQuery(this).attr('name').split('question')[1],
                        value = jQuery(this).val(),
                        $this = jQuery(this).parents('.quiz_section');
                        console.log(value);
                        if (value != "" && value != null) {
                            self.qsmSubmitQuizIfAnswerWrong(quizId, question_id, value, $this, quizData.form);
                        }
                    }
                }
            })

            updateHistory = updateHistory !== false; // Default to true

            // Trigger before page change event
            $(document).trigger('qsm_before_page_change', [quizId, quizData.currentPage, pageNumber]);

            // Update current page
            quizData.currentPage = pageNumber;
            
            // Show the target page
            this.showPage(quizId, pageNumber);
            
            // Update UI components
            this.updateNavigationButtons(quizId);
            this.updatePageCounter(quizId);

            // Scroll to top of quiz
            if (quizData.data.disable_scroll_next_previous_click != 1) {
                this.scrollToQuiz(quizId);
            }

            // Focus management for accessibility
            this.manageFocus(quizId);

            // Trigger after page change event
            $(document).trigger('qsm_after_page_change', [quizId, pageNumber, quizData]);
        },
        
        /**
         * Show specific page (helper method)
         */
        showPage: function(quizId, pageNumber) {
            let quizData = this.quizObjects[quizId];
            if (!quizData) return;
            
            // Hide all pages
            quizData.pages.hide();
            
            // Show target page (convert to 0-based index for DOM)
            let $targetPage = quizData.pages.eq(pageNumber - 1);
            currentPage = pageNumber - 1;
            console.log( " pageNumber ", pageNumber );   
            console.log( " currentPage ", currentPage );   
            jQuery('.pages_count').hide();
            if ($targetPage.length > 0) {
                $targetPage.show();
                jQuery('.page_count_'+ currentPage).show();
                // Check if this page needs lazy loading
                if ($targetPage.hasClass('qsm-lazy-load-page') && $targetPage.attr('data-lazy-load') === '1') {
                    this.loadPageQuestions(quizId, $targetPage, pageNumber);
                }
                
                // Preemptively load next pages (load 2 pages ahead)
                this.preloadNextPages(quizId, pageNumber);
            }
        },
        
        /**
         * Preload next pages (load 2 pages ahead of current page)
         * This ensures smooth navigation by loading pages before user reaches them
         */
        preloadNextPages: function(quizId, currentPage) {
            let self = this;
            let quizData = this.quizObjects[quizId];
            if (!quizData) return;
            
            // Calculate which pages to preload (next 2 pages)
            let pagesToPreload = [];
            
            // Preload page currentPage + 1 and currentPage + 2
            for (let i = 1; i <= 2; i++) {
                let nextPageNum = currentPage + i;
                if (nextPageNum <= quizData.totalPages) {
                    pagesToPreload.push(nextPageNum);
                }
            }
            
            // Load each page that needs preloading
            pagesToPreload.forEach(function(pageNum) {
                let $nextPage = quizData.pages.eq(pageNum - 1);
                
                // Only preload if:
                // 1. Page exists
                // 2. Page is marked for lazy loading
                // 3. Page hasn't been loaded yet
                // 4. Page is not currently being loaded
                if ($nextPage.length > 0 && 
                    $nextPage.hasClass('qsm-lazy-load-page') && 
                    $nextPage.attr('data-lazy-load') === '1' &&
                    !$nextPage.hasClass('qsm-loading')) {
                    
                    console.log('QSM: Preloading page ' + pageNum + ' (user currently on page ' + currentPage + ')');
                    self.loadPageQuestions(quizId, $nextPage, pageNum);
                }
            });
        },
        
        /**
         * Load questions for a lazy-loaded page via AJAX
         */
        loadPageQuestions: function(quizId, $page, pageNumber) {
            let self = this;
            let quizData = this.quizObjects[quizId];
            if (!quizData) return;
            
            // Check if already loading or loaded
            if ($page.hasClass('qsm-loading') || $page.hasClass('qsm-loaded-page')) {
                return;
            }
            
            // Mark as loading
            $page.addClass('qsm-loading');
            
            // Show loading spinner
            $page.find('.qsm-lazy-load-spinner').show();
            
            // Get question IDs and other data from page attributes
            let questionIds = $page.attr('data-question-ids') || '';
            let questionStartNumber = parseInt($page.attr('data-question-start-number')) || 1;
            console.log( quizData.data.ajax_url );
            // Prepare AJAX data
            let ajaxData = {
                action: 'qsm_load_page_questions',
                nonce: quizData.data.lazy_load_nonce || quizData.data.nonce || (typeof qmn_ajax_object !== 'undefined' ? qmn_ajax_object.security : ''),
                quiz_id: quizId,
                page_number: pageNumber,
                question_ids: questionIds,
                question_start_number: questionStartNumber,
                randomness_order: JSON.stringify(quizData.data.randomness_order || [])
            };
            
            // Trigger before lazy load event
            $(document).trigger('qsm_before_lazy_load', [quizId, pageNumber, $page]);
            
            // Make AJAX request
            $.ajax({
                url: quizData.data.ajax_url || (typeof qmn_ajax_object !== 'undefined' ? qmn_ajax_object.ajaxurl : '/wp-admin/admin-ajax.php'),
                type: 'POST',
                data: ajaxData,
                success: function(response) {
                    if (response.success && response.data.html) {
                        // Remove placeholder
                        $page.find('.qsm-lazy-load-placeholder').remove();
                        
                        // Insert questions HTML
                        $page.prepend(response.data.html);
                        
                        // Mark page as loaded
                        $page.removeClass('qsm-lazy-load-page qsm-loading');
                        $page.addClass('qsm-loaded-page');
                        $page.attr('data-lazy-load', '0');
                        
                        // Re-bind events for newly loaded questions
                        self.bindAnswerEvents(quizId);
                        
                        // Initialize any new file upload fields
                        self.bindFileUploadEvents(quizId);
                        
                        // Trigger after lazy load event
                        $(document).trigger('qsm_after_lazy_load', [quizId, pageNumber, $page, response.data]);
                        
                        console.log('QSM: Successfully loaded ' + response.data.question_count + ' questions for page ' + pageNumber);
                    } else {
                        self.handleLazyLoadError(quizId, $page, response.data ? response.data.message : 'Unknown error');
                    }
                },
                error: function(xhr, status, error) {
                    self.handleLazyLoadError(quizId, $page, 'AJAX error: ' + error);
                }
            });
        },
        
        /**
         * Handle lazy load errors
         */
        handleLazyLoadError: function(quizId, $page, errorMessage) {
            console.error('QSM Lazy Load Error:', errorMessage);
            
            // Hide spinner
            $page.find('.qsm-lazy-load-spinner').hide();
            
            // Show error message
            let errorHtml = '<div class="qsm-error-message" style="padding: 20px; color: #d63638;">' +
                '<strong>Error loading questions:</strong><br>' + errorMessage +
                '<br><button class="qsm-retry-load" style="margin-top: 10px;">Retry</button>' +
                '</div>';
            $page.find('.qsm-lazy-load-placeholder').html(errorHtml);
            
            // Remove loading class
            $page.removeClass('qsm-loading');
            
            // Bind retry button
            let self = this;
            $page.find('.qsm-retry-load').on('click', function() {
                $page.find('.qsm-error-message').remove();
                $page.find('.qsm-lazy-load-placeholder').html('<div class="qsm-lazy-load-spinner" style="display: none;"></div>');
                self.loadPageQuestions(quizId, $page, parseInt($page.attr('data-page')));
            });
            
            // Trigger error event
            $(document).trigger('qsm_lazy_load_error', [quizId, $page, errorMessage]);
        },

        /**
         * Go to next page
         */
        nextPage: function(quizId) {
            let quizData = this.quizObjects[quizId];
            console.log(quizData);
            if (!quizData) return;

            // Validate current page before proceeding
            if (!this.validateCurrentPage(quizId)) {
                return;
            }

            // Check if this is the last page
            if (quizData.currentPage >= quizData.totalPages) {
                // Submit form if on last page
                quizData.form.trigger('submit');
                return;
            }

            // Go to next page
            this.goToPage(quizId, quizData.currentPage + 1);

            // Trigger next button click event (for legacy compatibility)
            $(document).trigger('qsm_next_button_click_after', [quizId]);
        },

        /**
         * Go to previous page
         */
        previousPage: function(quizId) {
            let quizData = this.quizObjects[quizId];
            if (!quizData) return;
            
            // Check minimum page based on first page setting
            let minPage;
            if (quizData.hasFirstPage) {
                // First page enabled: Can't go back past page 2 (first question page, not welcome page)
                minPage = 2;
            } else {
                // First page disabled: Can't go back past page 1 (first question page)
                minPage = 1;
            }
            
            if (quizData.currentPage < minPage) return;
            this.goToPage(quizId, quizData.currentPage - 1);

            // Trigger previous button click event (for legacy compatibility)
            $(document).trigger('qsm_previous_button_click_after', [quizId]);
        },

        /**
         * Update navigation buttons visibility and state
         */
        updateNavigationButtons: function(quizId) {
            let quizData = this.quizObjects[quizId];
            if (!quizData) return;
            
            let $pagination = quizData.pagination;

            // let $previousBtn = $pagination.find(this.config.selectors.previousBtn);
            // let $nextBtn = $pagination.find(this.config.selectors.nextBtn);
            // let $submitBtn = $pagination.find(this.config.selectors.submitBtn);
            // let $startBtn = $pagination.find(this.config.selectors.startBtn);

            let $previousBtn = jQuery('.qsm-previous-btn-'+quizId);
            let $nextBtn = jQuery('.qsm-next-btn-'+quizId);
            let $submitBtn = jQuery('.qsm-submit-btn-'+quizId);
            let $startBtn = jQuery('.qsm-start-btn-'+quizId);
            
            let currentPage = quizData.currentPage;
            let isFirstPage = (currentPage == 1);
            let isLastPage = (currentPage == quizData.totalPages);
            let showStartButton = (isFirstPage && quizData.hasFirstPage);
            
            // Simple button visibility logic
            if (showStartButton) {
                console.log($previousBtn);
                // First page (welcome page) with start button
                $startBtn.show();
                $previousBtn.hide();
                $nextBtn.hide();
                $submitBtn.hide();
            } else {
                // Regular navigation pages (question pages)
                $startBtn.hide();

                
                
                
                // Previous button logic:
                // - Hide on first page when no welcome page
                // - Hide on page 2 when there IS a welcome page (can't go back from first question page to welcome)
                // - Show otherwise
                if (currentPage < 2) {
                    $previousBtn.hide();
                } else {
                    $previousBtn.show();
                }

                // Next/Submit buttons: submit on last page, next otherwise
                if (isLastPage) {
                    $nextBtn.hide();
                    $submitBtn.show();
                    
                } else {
                    $nextBtn.show();
                    $submitBtn.hide();
                    
                }
            }
        },

        /**
         * Update page counter display
         */
        updatePageCounter: function(quizId) {
            let quizData = this.quizObjects[quizId];
            if (!quizData) return;

            let $counter = quizData.pagination.find(this.config.selectors.pageCounter);
            if ($counter.length) {
                // Don't show page counter for start page (welcome page)
                if (quizData.currentPage === 1 && quizData.hasFirstPage) {
                    $counter.hide();
                } else {
                    // Calculate display numbers for question pages only
                    let displayPage, displayTotal;
                    
                    if (quizData.hasFirstPage) {
                        // First page exists, so question pages start from page 2
                        displayPage = Math.max(1, quizData.currentPage - 1);
                        displayTotal = quizData.questionPages;
                    } else {
                        // No first page, so all pages are question pages
                        displayPage = quizData.currentPage;
                        displayTotal = quizData.questionPages;
                    }
                    
                    let text = 'Page ' + displayPage + ' of ' + displayTotal;
                    $counter.text(text);
                }
            }
        },


        /**
         * Validate current page
         */
        validateCurrentPage: function(quizId) {
            let quizData = this.quizObjects[quizId];
            if (!quizData || !quizData.validation.enabled) return true;

            let $currentPage = quizData.pages.eq(quizData.currentPage - 1);
            if (!$currentPage.length) {
                return true;
            }
            
            return this.validateElements($currentPage.find('*'), quizId);
        },

        /**
         * Validate entire form (similar to qmnValidation)
         */
        validateForm: function(quizId) {
            let quizData = this.quizObjects[quizId];
            if (!quizData) return false;
            
            $(document).trigger('qsm_before_validation', [quizData.form, 'quizForm' + quizId]);
            
            // Skip validation if timer limit is enabled and time expired
            if (quizData.data.timer_limit_val > 0 && quizData.data.hasOwnProperty('skip_validation_time_expire') && quizData.data.skip_validation_time_expire != 1) {
                return true;
            }

            return this.validateElements(quizData.form.find('*'), quizId);
        },

        /**
         * Core validation logic (based on qmnValidation)
         */
        validateElements: function($elements, quizId) {
            let quizData = this.quizObjects[quizId];
            if (!quizData) return false;
            
            show_result_validation = true;
	        jQuery(document).trigger('qsm_before_validation', [$elements, 'quizForm' + quizId]);
            let data = quizData.data;
            let errorMessages = data.error_messages || {};
            
            // Clear previous errors
            this.resetErrors(quizId);
            
            let self = this;
            $elements.each(function() {
                let $field = $(this);
                let fieldClass = $field.attr('class') || '';
                let fieldValue = $field.val() || '';
                
                // Skip if field is not visible or doesn't have validation classes
                if (!fieldClass || (!$field.is(':visible') && fieldClass.indexOf('mlwRequiredAccept') === -1)) {
                    return;
                }
                
                // Email validation
                if (fieldClass.indexOf('mlwEmail') !== -1 && fieldValue !== '') {
                    let email = $.trim(fieldValue);
                    if (!self.isValidEmail(email)) {
                        self.displayError(errorMessages.email_error_text || 'Please enter a valid email address.', $field, quizId);
                        show_result_validation = false;
                    }
                }
                
                // URL validation
                if (fieldClass.indexOf('mlwUrl') !== -1 && fieldValue !== '') {
                    if (!self.isValidUrl($.trim(fieldValue))) {
                        self.displayError(errorMessages.url_error_text || 'Please enter a valid URL.', $field, quizId);
                        show_result_validation = false;
                    }
                }
                
                // Min length validation
                if (fieldClass.indexOf('mlwMinLength') !== -1 && fieldValue !== '') {
                    let minLength = parseInt($field.attr('minlength')) || 0;
                    if ($.trim(fieldValue).length < minLength) {
                        let message = (errorMessages.minlength_error_text || 'Minimum %minlength% characters required.').replace('%minlength%', minLength);
                        self.displayError(message, $field, quizId);
                        show_result_validation = false;
                    }
                }
                
                // Max length validation
                if (fieldClass.indexOf('mlwMaxLength') !== -1 && fieldValue !== '') {
                    let maxLength = parseInt($field.attr('maxlength')) || 0;
                    if ($.trim(fieldValue).length > maxLength) {
                        let message = (errorMessages.maxlength_error_text || 'Maximum %maxlength% characters allowed.').replace('%maxlength%', maxLength);
                        self.displayError(message, $field, quizId);
                        show_result_validation = false;
                    }
                }
                
                // Required field validations
                if (fieldClass.indexOf('mlwRequiredText') !== -1 && $.trim(fieldValue) === '') {
                    self.displayError(errorMessages.empty_error_text || 'This field is required.', $field, quizId);
                    show_result_validation = false;
                }
                
                if (fieldClass.indexOf('mlwRequiredNumber') !== -1 && (fieldValue === '' || isNaN(fieldValue))) {
                    self.displayError(errorMessages.number_error_text || 'Please enter a valid number.', $field, quizId);
                    show_result_validation = false;
                }
                
                if (fieldClass.indexOf('mlwRequiredDate') !== -1 && fieldValue === '') {
                    self.displayError(errorMessages.empty_error_text || 'This field is required.', $field, quizId);
                    show_result_validation = false;
                }
                
                if (fieldClass.indexOf('mlwRequiredAccept') !== -1 && !$field.prop('checked')) {
                    self.displayError(errorMessages.empty_error_text || 'You must accept this.', $field, quizId);
                    show_result_validation = false;
                }
                
                if (fieldClass.indexOf('mlwRequiredRadio') !== -1) {
                    let checkedVal = $field.find('input:checked').val();
                    if (!checkedVal) {
                        self.displayError(errorMessages.empty_error_text || 'Please select an option.', $field, quizId);
                        show_result_validation = false;
                    }
                }
                
                if (fieldClass.indexOf('mlwRequiredFileUpload') !== -1) {
                    let files = $field.get(0).files;
                    if (!files || files.length === 0) {
                        self.displayError(errorMessages.empty_error_text || 'Please select a file.', $field, quizId);
                        show_result_validation = false;
                    }
                }
            });
	        jQuery(document).trigger('qsm_after_validation', [$elements, 'quizForm' + quizId]);
            return show_result_validation;
        },


        /**
         * Display validation error (similar to qmnDisplayError)
         */
        displayError: function(message, $field, quizId) {
            let quizData = this.quizObjects[quizId];
            if (!quizData) return;
            
            // Add error class to field
            $field.addClass('qsm-error');
            
            // Add error class to parent section
            $field.closest('.qsm-question, .quiz_section').addClass('qsm-error');
            
            // Create error message element
            let $errorMsg = $('<div class="qsm-error-message">' + message + '</div>');
            
            // Insert error message after field or its container
            let $container = $field.closest('.qsm-question');
            if ($container.length) {
                $container.after($errorMsg);
            } else {
                $field.after($errorMsg);
            }
            
            // Focus on first error field
            if (!quizData.firstErrorFocused) {
                $field.focus();
                quizData.firstErrorFocused = true;
            }
        },

        /**
         * Reset all errors (similar to qmnResetError)
         */
        resetErrors: function(quizId) {
            let quizData = this.quizObjects[quizId];
            if (!quizData) return;
            
            quizData.firstErrorFocused = false;
            
            // Remove error messages
            quizData.form.find('.qsm-error-message').remove();
            
            // Remove error classes
            quizData.form.find('.qsm-error').removeClass('qsm-error');
        },

        clearPageErrors: function(quizId) {
            let quizData = this.quizObjects[quizId];
            if (!quizData) return;

            let $currentPage = quizData.pages.eq(quizData.currentPage - 1);
            $currentPage.find('.qsm-error').removeClass('qsm-error');
            $currentPage.find('.qsm-error-message').remove();
        },

        clearFieldError: function($field) {
            $field.removeClass('qsm-error');
            $field.siblings('.qsm-error-message').remove();
        },

        /**
         * Validation helper methods
         */
        isValidEmail: function(email) {
            let regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return regex.test(email);
        },

        isValidUrl: function(url) {
            let regex = /^(https?|ftp):\/\/[^\s/$.?#].[^\s]*$/i;
            return regex.test(url);
        },

        /**
         * Handle keyboard navigation
         */
        handleKeyboardNavigation: function(e, quizId) {
            // Allow Ctrl+Enter to submit form
            if (e.ctrlKey && e.keyCode === 13) {
                e.preventDefault();
                this.quizObjects[quizId].form.trigger('submit');
                return;
            }

            // Allow Enter to go to next page (except in textareas)
            if (e.keyCode === 13 && !$(e.target).is('textarea')) {
                e.preventDefault();
                this.nextPage(quizId);
                return;
            }
        },

        /**
         * Scroll to quiz form
         */
        scrollToQuiz: function(quizId) {
            let quizData = this.quizObjects[quizId];
            if (!quizData) return;

            $('html, body').animate({
                scrollTop: quizData.form.offset().top - 50
            }, this.config.animation.duration);
        },

        /**
         * Manage focus for accessibility
         */
        manageFocus: function(quizId) {
            let quizData = this.quizObjects[quizId];
            if (!quizData) return;

            let $currentPage = quizData.pages.eq(quizData.currentPage - 1);
            let $firstInput = $currentPage.find('input, select, textarea').first();
            
            if ($firstInput.length) {
                setTimeout(function() {
                    $firstInput.focus();
                }, this.config.animation.duration + 50);
            }
        },

        /**
         * Submit the form (similar to qmnFormSubmit)
         */
        submitForm: function(quizId) {
            let quizData = this.quizObjects[quizId];
            if (!quizData) return;
            
            let self = this;
            
            // Trigger before submit event
            $(document).trigger('qsm_before_quiz_submit', ['quizForm' + quizId]);
            
            // Disable submit button to prevent double submission
            quizData.form.find('input[type="submit"], .qsm-submit').prop('disabled', true);
            quizData.quizContainer.find('input[type="submit"], .qsm-submit').prop('disabled', true);
            
            // Enable all form fields for submission (except submit buttons)
            quizData.form.find('input:not([type="submit"]), select, textarea').prop('disabled', false);
            
            // Prepare form data - ensure all form fields are included
            let formData = new FormData();
            
            // Add all form inputs, selects, and textareas
            quizData.form.find('input, select, textarea').each(function() {
                let $field = $(this);
                let name = $field.attr('name');
                let type = $field.attr('type');
                
                if (!name) return; // Skip fields without names
                
                if (type === 'radio' || type === 'checkbox') {
                    if ($field.is(':checked')) {
                        formData.append(name, $field.val());
                    }
                } else if (type === 'file') {
                    let files = this.files;
                    if (files && files.length > 0) {
                        formData.append(name, files[0]);
                    }
                } else if (type !== 'submit' && type !== 'button') {
                    formData.append(name, $field.val() || '');
                }
            });
            
            // Add quiz ID
            formData.append('quiz_id', quizId);
            
            // Add quiz start date
            let startDate = localStorage.getItem('qsm_quiz_start_date_' + quizId);
            if (startDate) {
                formData.append('quiz_start_date', startDate);
            }
            
            // Add timer data if available
            let timerInfo = window.QSMPagination && window.QSMPagination.Timer ? 
                           window.QSMPagination.Timer.getTimerInfo(quizId) : null;
            if (timerInfo) {
                formData.append('timer_ms', (timerInfo.totalTime - timerInfo.remainingTime) * 1000);
            }
            
            // Add required fields for QSM processing (matching legacy exactly)
            formData.append('action', 'qmn_process_quiz');
            formData.append('nonce', quizData.form.find('#qsm_nonce_' + quizId).val() || quizData.nonceValue || '');
            formData.append('qsm_unique_key', quizData.form.find('#qsm_unique_key_' + quizId).val() || '');
            formData.append('currentuserTime', Math.round(new Date().getTime() / 1000));
            formData.append('currentuserTimeZone', Intl.DateTimeFormat().resolvedOptions().timeZone);
            
            // Add timer field if it exists
            let timerField = quizData.form.find('#timer').length ? 
                           quizData.form.find('#timer') : 
                           quizData.quizContainer.find('#timer');
            if (timerField.length) {
                formData.append('timer', timerField.val() || '0');
            }
            
            // Trigger after form data process event (like legacy)
            $(document).trigger('qsm_after_form_data_process', ['qsmForm' + quizId, formData]);
            
            // End timer (matching legacy behavior)
            if (window.QSMPagination && window.QSMPagination.Timer) {
                window.QSMPagination.Timer.endTimer(quizId);
            }
            
            // Show loading state
            this.showLoading(quizId);
            
            // Hide all micromodal popups
            $('.qsm-popup').removeClass('is-open');

            // Get AJAX URL from quiz data or global
            let ajaxUrl = quizData.data.ajax_url || 
                         (typeof qmn_ajax_object !== 'undefined' ? qmn_ajax_object.ajaxurl : '') ||
                         '/wp-admin/admin-ajax.php';
            
            // Submit via AJAX
            $.ajax({
                url: ajaxUrl,
                data: formData,
                contentType: false,
                processData: false,
                type: 'POST',
                success: function(response) {
                    try {
                        if (typeof response === 'string') {
                            response = JSON.parse(response);
                        }
                        
                        if (response.quizExpired) {
                            if (typeof MicroModal !== 'undefined') {
                                MicroModal.show('modal-4');
                            }
                            return;
                        }
                        
                        // Check if response has expected structure
                        if (!response || typeof response !== 'object') {
                            throw new Error('Invalid response format received from server');
                        }
                        
                        // Display results
                        self.displayResults(response, quizId);
                        
                        // Trigger after submit event
                        $(document).trigger('qsm_after_quiz_submit', ['quizForm' + quizId]);
                    
                    } catch (e) {
                        self.displayError('Error processing quiz results: ' + e.message + '. Please check console for details.', quizId);
                    }
                },
                error: function(xhr, status, error) {
                    // Re-enable submit button on error
                    quizData.quizContainer.find('input[type="submit"], .qsm-submit').prop('disabled', false);
                    
                    // Show error message
                    let errorMessage = 'Error submitting quiz: ' + error + ' (Status: ' + xhr.status + ')';
                    if (xhr.responseText) {
                        errorMessage += ' - Response: ' + xhr.responseText.substring(0, 200);
                    }
                    self.displayError(errorMessage, quizId);
                }
            });
        },

        /**
         * Display results
         */
        displayResults: function(response, quizId) {
            let quizData = this.quizObjects[quizId];
            if (!quizData) return;
            
            let $quizContainer = quizData.quizContainer;
            jQuery(document).trigger('qsm_before_display_result', [response, 'quizForm' + quizId, $quizContainer]);
            // Clear existing content
            $quizContainer.find('.qsm_results_page').remove();
            
            // Create results container
            let $resultDiv = $('<div class="qsm_results_page qmn_results_page">');
            
            $quizContainer.find('.qsm-quiz-processing-box').remove();
            $resultDiv.html(response.display);
            $quizContainer.append($resultDiv);
            $resultDiv.slideDown();
            
            // Clean up localStorage
            localStorage.removeItem('qsm_quiz_start_date_' + quizId);
            
            // Scroll to results
            $('html, body').animate({
                scrollTop: $resultDiv.offset().top - 50
            }, 500);
        },

        /**
         * Display error message
         */
        displayError: function(message, quizId) {
            let quizData = this.quizObjects[quizId];
            if (!quizData) {
                alert('Error: ' + message);
                return;
            }
            
            let $quizContainer = quizData.quizContainer;
            
            // Remove existing error messages
            $quizContainer.find('.qsm-error-display').remove();
            
            // Create error message element
            let $errorDiv = $('<div class="qsm-error-display">')
                .html('<strong>Error:</strong> ' + message)
                .css({
                    'background-color': '#f8d7da',
                    'color': '#721c24',
                    'border': '1px solid #f5c6cb',
                    'border-radius': '4px',
                    'padding': '12px',
                    'margin': '10px 0',
                    'display': 'none'
                });
            
            // Insert error message
            $quizContainer.prepend($errorDiv);
            $errorDiv.slideDown();
            
            // Auto-hide after 10 seconds
            setTimeout(function() {
                $errorDiv.slideUp(function() {
                    $errorDiv.remove();
                });
            }, 10000);
        },

        /**
         * Show loading state
         */
        showLoading: function(quizId) {
            let quizData = this.quizObjects[quizId];
            if (!quizData) return;
            
            let loadingHtml = '<div class="qsm-quiz-processing-box">';
            loadingHtml += '<div class="qsm-spinner-loader"></div>';
            loadingHtml += '<div class="qsm-processing-message">Processing your quiz...</div>';
            loadingHtml += '</div>';
            
            quizData.quizContainer.html(loadingHtml);
        },

        /**
         * Handle window resize
         */
        handleResize: function() {
            Object.keys(this.quizObjects).forEach(function(quizId) {
                $(document).trigger('qsm_navigation_resize', [quizId, this.quizObjects[quizId]]);
            }.bind(this));
        },


        /**
         * Get quiz ID from container or form element
         */
        getQuizId: function($element) {
            // Try data attribute first (for containers)
            let id = $element.data('quiz-id');
            if (id) {
                return id;
            }
            
            // Try form ID (if element is a form)
            id = $element.attr('id');
            if (id && (id.indexOf('qsmForm') === 0 || id.indexOf('qsmNewForm') === 0)) {
                return id.replace(/qsm(New)?Form/, '');
            }
            
            // Try finding form within container
            let $form = $element.find('.qsm-quiz-form');
            if ($form.length) {
                id = $form.attr('id');
                if (id && (id.indexOf('qsmForm') === 0 || id.indexOf('qsmNewForm') === 0)) {
                    return id.replace(/qsm(New)?Form/, '');
                }
            }
            
            // Try parent container data attribute
            let $container = $element.closest('[data-quiz-id]');
            if ($container.length) {
                return $container.data('quiz-id');
            }
            
            console.log('Could not determine quiz ID for element:', $element);
            return null;
        },

        /**
         * Show inline result feedback (matching legacy qsm_show_inline_result)
         */
        qsmShowInlineResult: function(quizId, question_id, value, $this, answer_type, $i_this, index) {
            index = index || null;
            
            $('.qsm-spinner-loader').remove();
            this.addSpinnerLoader($this, $i_this);
            
            let quizData = this.getQuizData(quizId);
            let data = this.qsmQuestionQuickResultJs(question_id, value, answer_type, quizData.enable_quick_correct_answer_info, quizId, index);
            
            $this.find('.quick-question-res-p, .qsm-inline-correct-info').remove();
            $this.find('.qmn_radio_answers, .qsm-answer-options').children().removeClass('data-correct-answer');
            
            if (value && value.length > 0 && data.success == 'correct') {
                $this.append('<div style="color: green" class="quick-question-res-p qsm-correct-answer-info">' + quizData.quick_result_correct_answer_text + '</div>');
                $this.append('<div class="qsm-inline-correct-info">' + this.qsmCheckShortcode(data.message) + '</div>');
            } else if (value && value.length > 0 && data.success == 'incorrect') {
                $this.find('.qmn_radio_answers input[value="' + data.correct_index + '"], .qsm-answer-options input[value="' + data.correct_index + '"]').parent().addClass('data-correct-answer');
                $this.append('<div style="color: red" class="quick-question-res-p qsm-incorrect-answer-info">' + quizData.quick_result_wrong_answer_text + '</div>');
                $this.append('<div class="qsm-inline-correct-info">' + this.qsmCheckShortcode(data.message) + '</div>');
            }
            
            // Render MathJax if enabled
            if (quizData.disable_mathjax != 1 && typeof MathJax !== 'undefined' && MathJax.typesetPromise) {
                MathJax.typesetPromise();
            }
            
            $('.qsm-spinner-loader').remove();
            $(document).trigger('qsm_show_inline_result_after', [quizId, question_id, value, $this, answer_type, $i_this, index]);
        },
        
        /**
         * Add spinner loader (matching legacy addSpinnerLoader)
         */
        addSpinnerLoader: function($this, $i_this) {
            if ($this.find('.mlw_answer_open_text').length) {
                $this.find('.mlw_answer_open_text').after('<div class="qsm-spinner-loader" style="font-size: 2.5px;margin-left:10px;"></div>');
            } else if ($this.find('.mlw_answer_number').length) {
                $this.find('.mlw_answer_number').after('<div class="qsm-spinner-loader" style="font-size: 2.5px;margin-left:10px;"></div>');
            } else {
                $i_this.next('.qsm-input-label').after('<div class="qsm-spinner-loader" style="font-size: 2.5px;"></div>');
            }
        },
        
        /**
         * Check answer correctness using encrypted data (matching legacy qsm_question_quick_result_js)
         */
        qsmQuestionQuickResultJs: function(question_id, answer, answer_type, show_correct_info, quiz_id, ans_index) {
            answer_type = answer_type || '';
            show_correct_info = show_correct_info || '';
            ans_index = ans_index || null;
            
            if (typeof encryptedData === 'undefined' || typeof encryptedData[quiz_id] === 'undefined') {
                return { correct_index: 0, success: '', message: '' };
            }
            
            try {
                let decryptedBytes = CryptoJS.AES.decrypt(encryptedData[quiz_id], encryptionKey[quiz_id]);
                let decryptedData = decryptedBytes.toString(CryptoJS.enc.Utf8);
                let decrypt = JSON.parse(decryptedData);
                
                question_id = typeof question_id !== 'undefined' ? parseInt(question_id) : 0;
                answer = typeof answer !== 'undefined' ? answer : '';
                
                let answer_array = decrypt[question_id].answer_array;
                let settings = decrypt[question_id].settings;
                let correct_info_text = decrypt[question_id].correct_info_text;
                let correct_answer_logic = decrypt.correct_answer_logic;
                show_correct_info = typeof show_correct_info !== 'undefined' && show_correct_info != 0 ? show_correct_info : '';
                
                let got_ans = false;
                let correct_answer = false;
                let count = 0;
                let correct_index = 0;
                let answer_count = 0;
                let total_correct_answer = 0;
                
                if (answer_array && false === got_ans) {
                    for (let key in answer_array) {
                        let value = answer_array[key];
                        
                        if ('input' === answer_type) {
                            if (!settings['case_sensitive']) {
                                answer = answer.toUpperCase();
                                value[0] = value[0].toUpperCase();
                            }
                            
                            if (answer == value[0] && (1 === parseInt(value[2]) || 14 === parseInt(decrypt[question_id].question_type_new)) && (!settings['matchAnswer'] || 'random' === settings['matchAnswer'] || key == ans_index)) {
                                got_ans = true;
                                correct_answer = true;
                                break;
                            }
                        } else if ('checkbox' === answer_type) {
                            if (0 == correct_answer_logic) {
                                for (let anskey in answer) {
                                    let ansvalue = answer[anskey];
                                    if (parseInt(ansvalue) === parseInt(key) && 1 == value[2]) {
                                        got_ans = true;
                                        correct_answer = true;
                                        break;
                                    }
                                }
                            } else {
                                if (answer_array[answer[key]] !== undefined) {
                                    if (1 == answer_array[answer[key]][2]) {
                                        answer_count++;
                                    } else if (answer[key] !== undefined) {
                                        answer_count--;
                                    }
                                }
                                if (1 == value[2]) {
                                    total_correct_answer++;
                                }
                            }
                        } else if (parseInt(answer) === parseInt(key) && 1 === parseInt(value[2])) {
                            got_ans = true;
                            correct_answer = true;
                            break;
                        }
                    }
                    
                    for (let key in answer_array) {
                        let value = answer_array[key];
                        if (false == correct_answer) {
                            if (1 == value[2]) {
                                correct_index = count;
                            }
                            count++;
                        }
                    }
                    
                    if ('checkbox' === answer_type) {
                        if (1 == correct_answer_logic) {
                            if (0 != answer_count && 0 != total_correct_answer && total_correct_answer == answer_count) {
                                got_ans = true;
                                correct_answer = true;
                            }
                        }
                    }
                }
                
                if (2 == show_correct_info) {
                    got_ans = true;
                }
                
                let returnObject = {
                    "correct_index": correct_index,
                    "success": correct_answer ? 'correct' : 'incorrect',
                    "message": show_correct_info && got_ans ? correct_info_text : ""
                };
                
                $(document).trigger('qsm_question_quick_result_js_after', [returnObject, correct_answer, answer, answer_array, answer_type, settings, decrypt, question_id]);
                return returnObject;
            } catch (e) {
                console.error('Error decrypting quiz data:', e);
                return { correct_index: 0, success: '', message: '' };
            }
        },
        
        /**
         * Submit quiz if answer is wrong (matching legacy qsm_submit_quiz_if_answer_wrong)
         */
        qsmSubmitQuizIfAnswerWrong: function(quizId, question_id, value, $this, $quizForm, answer_type = '') {
            answer_type = answer_type || '';
            let quizData = this.getQuizData(quizId);
            
            let data = this.qsmQuestionQuickResultJs(question_id, value, answer_type, quizData.enable_quick_correct_answer_info, quizId);
            
            this.changes(data, question_id.replace(/\D/g, ""), quizId);
            
            if (data.success == 'incorrect') {
                $quizForm.closest('.qsm-quiz-container').find('[class*="Required"]').removeClass();
                $quizForm.closest('.qsm-quiz-container').find('.qsm-submit-btn').trigger('click');
            }
            
            // Render MathJax if enabled
            if (quizData.disable_mathjax != 1 && typeof MathJax !== 'undefined' && MathJax.typesetPromise) {
                MathJax.typesetPromise();
            }
        },
        
        /**
         * Initialize q_counter model (matching legacy q_counter)
         */
        q_counter: Backbone.Model.extend({
			defaults: {
				answers: []
			}
		}),

        /**
         * Changes function (matching legacy changes)
         */
        changes: function(data, question_id, quiz_id) {
            let quizData = this.getQuizData(quiz_id);
            answers = qsmLogicModel.get('answers');
			
            answers.push({
				'q_id': question_id,
				'incorrect': data.success == 'correct' ? 0 : 1,
			});
			
            qsmLogicModel.set({ 'answers': this.filter_question(qsmLogicModel.get('answers')) });
			
            let update_answers = qsmLogicModel.get('answers');
			let incorrect = 0;

			update_answers.forEach(function(obj){
                if(obj.incorrect == 1){
                    incorrect++;
                }
			});
            console.log(quizData, incorrect);
			if( quizData.end_quiz_if_wrong <= incorrect ) {
				this.submit_status = true;
			}else{
				this.submit_status = false;
			}
        },

        filter_question: function(arr) {
			let result = {};
			arr.forEach(function(obj) {
				if (obj.q_id) {
					result[obj.q_id] = obj;
				}
			});
			return Object.values(result);
		},

        /**
         * Process shortcodes in message (matching legacy qsm_check_shortcode)
         */
        qsmCheckShortcode: function(message) {
            if (!message) return '';
            
            const videoContentRegex = /\[video(?:\s(?:src|mp4|m4v|webm|ogv|wmv|flv|width|height)="[^"]*")*\](.*?)\[\/video\]/g;
            let videoMatch = message.match(videoContentRegex);

            if (videoMatch) {
                let videoHTML = message.replace(videoContentRegex, function(match, content) {
                    const { src, width, height } = this.parseAttributes(match);
                    const videoTag = this.generateVideoTag(src, width, height, content);
                    return `<div class="video-content">${videoTag}</div>`;
                });
                return videoHTML;
            }

            // Check if message contains an image shortcode
            let imageRegex = /\[img(?:(?:\ssrc="([^"]+)")|(?:\salt="([^"]+)")|(?:\swidth="(\d+)")|(?:\sheight="(\d+)")){0,4}\s*\]/g;
            let imageMatch = message.match(imageRegex);

            if (imageMatch) {
                let imageHTML = message.replace(imageRegex, function(match, src, alt, width, height) {
                    return '<img src="' + (src || '') + '" alt="' + (alt || '') + '" width="' + (width || '') + '" height="' + (height || '') + '">';
                });
                return '<div class="image-content">' + imageHTML + '</div>';
            }

            return message;
        },

        /**
         * Parse attributes from video shortcode
         */
        parseAttributes: function(match) {
            let videoAttrs = { src: '', width: '', height: '' };

            videoAttributePatterns.forEach(pattern => {
                const attrMatch = match.match(pattern);
                if (attrMatch) {
                    const value = attrMatch[1] || '';
                    if (pattern.toString().includes('width')) {
                        videoAttrs.width = value;
                    } else if (pattern.toString().includes('height')) {
                        videoAttrs.height = value;
                    } else {
                        videoAttrs.src = value;
                    }
                }
            });

            return videoAttrs;
        },

        /**
         * Generate video tag from shortcode
         */
        generateVideoTag: function(src, width, height, content) {
            return `<video src="${src}" width="${width}" height="${height}" controls>${content}</video>`;
        },

        /**
         * Get validation rules for quiz
         */
        getValidationRules: function(quizId) {
            // This can be extended based on quiz configuration
            return {
                required: true,
                email: true,
                url: true,
                number: true
            };
        },

        /**
         * Destroy quiz instance
         */
        destroy: function(quizId) {
            
            if (this.quizObjects[quizId]) {
                // Remove event listeners
                this.quizObjects[quizId].form.off();
                

                // Delete instance
                delete this.quizObjects[quizId];
                console.log('destroy')
                
                // Trigger destroy event
                $(document).trigger('qsm_navigation_destroyed', [quizId]);
            }
        }
    };

    var qsmLogicModel = new QSMPagination.Navigation.q_counter({});

    /**
     * jQuery debounce function
     */
    $.debounce = function(delay, fn) {
        let timer = null;
        return function() {
            let context = this, args = arguments;
            clearTimeout(timer);
            timer = setTimeout(function() {
                fn.apply(context, args);
            }, delay);
        };
    };

    /**
     * Initialize on document ready
     */
    $(document).ready(function() {
        QSMPagination.Navigation.init();
    });

    /**
     * Re-initialize on AJAX content load
     */
    $(document).on('qsm_content_loaded', function() {
        QSMPagination.Navigation.init();
    });

})(jQuery);