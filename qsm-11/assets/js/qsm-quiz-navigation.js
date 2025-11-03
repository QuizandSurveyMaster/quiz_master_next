/**
 * QSM-11 Quiz Navigation JavaScript
 * 
 * Handles navigation, pagination, and validation functionality
 * for the new QSM-11 rendering system
 * 
 * @package QSM
 * @version 1.0.0
 */

(function($) {

    // Global namespace for QSM New Navigation
    window.QSMPagination = window.QSMPagination || {};

    /**
     * Main QSM New Navigation Object
     */
    QSMPagination.Navigation = {
        quizObjects: {},
        timeTakenIntervals: {},
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
                duration: 300,
                easing: 'swing'
            }
        },

        // Quiz instances storage
        quizObjects: {},

        /**
         * Get quiz data from either legacy or new variable
         * Provides backward compatibility
         */
        getQuizData: function(quizId) {
            // Try new variable first, then legacy
            if (window.qsmQuizData && window.qsmQuizData[quizId]) {
                return window.qsmQuizData[quizId];
            }
            if (window.qmn_quiz_data && window.qmn_quiz_data[quizId]) {
                return window.qmn_quiz_data[quizId];
            }
            return {};
        },

        /**
         * Initialize navigation for all quizzes on page
         */
        init: function() {
            let self = this;
            
            // Initialize each quiz form found
            $(this.config.selectors.quizContainer).each(function() {
                let $quizContainer = $(this);
                let quizId = self.getQuizId($quizContainer);
                
                if (quizId && !self.quizObjects[quizId]) {
                    self.initQuiz(quizId, $quizContainer);
                }
            });
        },

        /**
         * Initialize a specific quiz
         */
        initQuiz: function(quizId, $quizContainer) {
            let quizData = this.getQuizData(quizId);
            let $pages = $quizContainer.find(this.config.selectors.page);
            
            // Determine if first page should be shown
            let hasFirstPage = (quizData.disable_first_page != 1);
                        
            // Calculate question pages (exclude first page if present)
            let questionPages = hasFirstPage ? $pages.length - 1 : $pages.length;
            
            this.quizObjects[quizId] = {
                id: quizId,
                quizContainer: $quizContainer,
                $form: $quizContainer.find(this.config.selectors.form),
                $pagination: $quizContainer.find(this.config.selectors.pagination),
                nonceValue: $quizContainer.find('#qsm_nonce_' + quizId).val() || $quizContainer.find('input[name="qsm_nonce"]').val(),
                $pages: $pages,
                currentPage: 1,
                totalPages: $pages.length,
                questionPages: questionPages, // Pages that count for progress
                hasFirstPage: hasFirstPage,
                data: quizData,
                validation: {
                    enabled: true,
                    errors: []
                }
            };
            
            // Initialize components
            this.initPagination(quizId);
            
            // Show initial page
            this.showPage(quizId, 1);
            
            // Initialize time taken timer
            var self = this;
            setInterval(function () { self.qsmQuizTimeTakenTimer(quizId) }, 1000);
            
            // Initialize progress bar if available
            if (window.QSMPagination && window.QSMPagination.ProgressBar) {
                window.QSMPagination.ProgressBar.initProgressBar(quizId, $quizContainer, this.quizObjects[quizId].$form);
            }
            
            // Bind events
            this.bindEvents(quizId);
            
            // Trigger initialization complete event
            $(document).trigger('qsm_quiz_initialized', [quizId, this.quizObjects[quizId]]);
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
            currentQuiz.$pages.hide();
            
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
            let $form = currentQuiz.$form;
            let $container = currentQuiz.quizContainer;

            // Navigation button clicks - bind to container since navigation is outside form
            $container.on('click', this.config.selectors.previousBtn, function(e) {
                e.preventDefault();
                self.previousPage(quizId);
            });

            $container.on('click', this.config.selectors.nextBtn, function(e) {
                e.preventDefault();
                self.nextPage(quizId);
            });

            // Start button click - bind to container (multiple selectors for compatibility)
            $container.on('click', this.config.selectors.startBtn, function(e) {
                e.preventDefault();
                console.log('Start button clicked for quiz:', quizId);
                self.startQuiz(quizId);
            });

            // Submit button click - bind to container since submit button is in navigation
            $container.on('click', this.config.selectors.submitBtn, function(e) {
                e.preventDefault();
                
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
        },

        /**
         * Start the quiz (handle start button click)
         */
        startQuiz: function(quizId) {
            let quizData = this.quizObjects[quizId];
            if (!quizData) return;
            
            // Set quiz start time
            let startDate = new Date();
            localStorage.setItem('qsm_quiz_start_date_' + quizId, startDate.getTime());
            
            // Start timer if available
            if (window.QSMPagination && window.QSMPagination.Timer) {
                window.QSMPagination.Timer.start(quizId);
            }
            
            // Go to first question page (next page after start page)
            let nextPage = 2; // Always go to page 2 after clicking start
            this.goToPage(quizId, nextPage);
            
            // Trigger quiz started event
            $(document).trigger('qsm_quiz_started', [quizId, quizData]);
        },

        /**
         * Navigate to specific page (1-based indexing)
         */
        goToPage: function(quizId, pageNumber, updateHistory) {
            let quizData = this.quizObjects[quizId];
            if (!quizData || pageNumber < 1 || pageNumber > quizData.totalPages) {
                return;
            }
            
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
            
            // Update progress bar if available
            if (window.QSMPagination && window.QSMPagination.ProgressBar) {
                window.QSMPagination.ProgressBar.updateProgress(quizId, pageNumber);
            }

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
            quizData.$pages.hide();
            
            // Show target page (convert to 0-based index for DOM)
            let $targetPage = quizData.$pages.eq(pageNumber - 1);
            if ($targetPage.length > 0) {
                $targetPage.show();
            }
        },

        /**
         * Go to next page
         */
        nextPage: function(quizId) {
            let quizData = this.quizObjects[quizId];
            if (!quizData) return;

            // Validate current page before proceeding
            if (!this.validateCurrentPage(quizId)) {
                return;
            }

            // Check if this is the last page
            if (quizData.currentPage >= quizData.totalPages) {
                // Submit form if on last page
                quizData.$form.trigger('submit');
                return;
            }

            // Trigger next button click event (for legacy compatibility)
            $(document).trigger('qsm_next_button_click_after', [quizId]);

            // Go to next page
            this.goToPage(quizId, quizData.currentPage + 1);
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
                // First page enabled: Can go back to page 1 (start page)
                minPage = 1;
            } else {
                // First page disabled: Can't go back past page 2 (first question)
                minPage = 2;
            }
            
            if (quizData.currentPage < minPage) return;
            this.goToPage(quizId, quizData.currentPage - 1);
        },

        /**
         * Update navigation buttons visibility and state
         */
        updateNavigationButtons: function(quizId) {
            let quizData = this.quizObjects[quizId];
            if (!quizData) return;

            let $pagination = quizData.$pagination;
            let $previousBtn = $pagination.find(this.config.selectors.previousBtn);
            let $nextBtn = $pagination.find(this.config.selectors.nextBtn);
            let $submitBtn = $pagination.find(this.config.selectors.submitBtn);
            let $startBtn = $pagination.find(this.config.selectors.startBtn);
            
            let currentPage = quizData.currentPage;
            let isFirstPage = (currentPage == 1);
            let isLastPage = (currentPage == quizData.totalPages);
            let showStartButton = (isFirstPage && quizData.hasFirstPage);
            
            // Simple button visibility logic
            if (showStartButton) {
                // First page with start button
                $startBtn.show();
                $previousBtn.hide();
                $nextBtn.hide();
                $submitBtn.hide();
            } else {
                // Regular navigation pages
                $startBtn.hide();
                
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

            let $counter = quizData.$pagination.find(this.config.selectors.pageCounter);
            if ($counter.length) {
                // Don't show page counter for start page
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

            let $currentPage = quizData.$pages.eq(quizData.currentPage - 1);
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
            
            $(document).trigger('qsm_before_validation', [quizData.$form, quizId]);
            
            return this.validateElements(quizData.$form.find('*'), quizId);
        },

        /**
         * Core validation logic (based on qmnValidation)
         */
        validateElements: function($elements, quizId) {
            let quizData = this.quizObjects[quizId];
            if (!quizData) return false;
            
            let isValid = true;
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
                        isValid = false;
                    }
                }
                
                // URL validation
                if (fieldClass.indexOf('mlwUrl') !== -1 && fieldValue !== '') {
                    if (!self.isValidUrl($.trim(fieldValue))) {
                        self.displayError(errorMessages.url_error_text || 'Please enter a valid URL.', $field, quizId);
                        isValid = false;
                    }
                }
                
                // Min length validation
                if (fieldClass.indexOf('mlwMinLength') !== -1 && fieldValue !== '') {
                    let minLength = parseInt($field.attr('minlength')) || 0;
                    if ($.trim(fieldValue).length < minLength) {
                        let message = (errorMessages.minlength_error_text || 'Minimum %minlength% characters required.').replace('%minlength%', minLength);
                        self.displayError(message, $field, quizId);
                        isValid = false;
                    }
                }
                
                // Max length validation
                if (fieldClass.indexOf('mlwMaxLength') !== -1 && fieldValue !== '') {
                    let maxLength = parseInt($field.attr('maxlength')) || 0;
                    if ($.trim(fieldValue).length > maxLength) {
                        let message = (errorMessages.maxlength_error_text || 'Maximum %maxlength% characters allowed.').replace('%maxlength%', maxLength);
                        self.displayError(message, $field, quizId);
                        isValid = false;
                    }
                }
                
                // Required field validations
                if (fieldClass.indexOf('mlwRequiredText') !== -1 && $.trim(fieldValue) === '') {
                    self.displayError(errorMessages.empty_error_text || 'This field is required.', $field, quizId);
                    isValid = false;
                }
                
                if (fieldClass.indexOf('mlwRequiredNumber') !== -1 && (fieldValue === '' || isNaN(fieldValue))) {
                    self.displayError(errorMessages.number_error_text || 'Please enter a valid number.', $field, quizId);
                    isValid = false;
                }
                
                if (fieldClass.indexOf('mlwRequiredDate') !== -1 && fieldValue === '') {
                    self.displayError(errorMessages.empty_error_text || 'This field is required.', $field, quizId);
                    isValid = false;
                }
                
                if (fieldClass.indexOf('mlwRequiredAccept') !== -1 && !$field.prop('checked')) {
                    self.displayError(errorMessages.empty_error_text || 'You must accept this.', $field, quizId);
                    isValid = false;
                }
                
                if (fieldClass.indexOf('mlwRequiredRadio') !== -1) {
                    let checkedVal = $field.find('input:checked').val();
                    if (!checkedVal) {
                        self.displayError(errorMessages.empty_error_text || 'Please select an option.', $field, quizId);
                        isValid = false;
                    }
                }
                
                if (fieldClass.indexOf('mlwRequiredFileUpload') !== -1) {
                    let files = $field.get(0).files;
                    if (!files || files.length === 0) {
                        self.displayError(errorMessages.empty_error_text || 'Please select a file.', $field, quizId);
                        isValid = false;
                    }
                }
            });
            
            return isValid;
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
            quizData.$form.find('.qsm-error-message').remove();
            
            // Remove error classes
            quizData.$form.find('.qsm-error').removeClass('qsm-error');
        },

        clearPageErrors: function(quizId) {
            let quizData = this.quizObjects[quizId];
            if (!quizData) return;

            let $currentPage = quizData.$pages.eq(quizData.currentPage - 1);
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
                this.quizObjects[quizId].$form.trigger('submit');
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
                scrollTop: quizData.$form.offset().top - 50
            }, this.config.animation.duration);
        },

        /**
         * Manage focus for accessibility
         */
        manageFocus: function(quizId) {
            let quizData = this.quizObjects[quizId];
            if (!quizData) return;

            let $currentPage = quizData.$pages.eq(quizData.currentPage - 1);
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
            $(document).trigger('qsm_before_quiz_submit', [quizId]);
            
            // Disable submit button to prevent double submission
            quizData.$form.find('input[type="submit"], .qsm-submit').prop('disabled', true);
            quizData.quizContainer.find('input[type="submit"], .qsm-submit').prop('disabled', true);
            
            // Enable all form fields for submission (except submit buttons)
            quizData.$form.find('input:not([type="submit"]), select, textarea').prop('disabled', false);
            
            // Prepare form data - ensure all form fields are included
            let formData = new FormData();
            
            // Add all form inputs, selects, and textareas
            quizData.$form.find('input, select, textarea').each(function() {
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
            formData.append('nonce', quizData.$form.find('#qsm_nonce_' + quizId).val() || quizData.nonceValue || '');
            formData.append('qsm_unique_key', quizData.$form.find('#qsm_unique_key_' + quizId).val() || '');
            formData.append('currentuserTime', Math.round(new Date().getTime() / 1000));
            formData.append('currentuserTimeZone', Intl.DateTimeFormat().resolvedOptions().timeZone);
            
            // Add timer field if it exists
            let timerField = quizData.$form.find('#timer').length ? 
                           quizData.$form.find('#timer') : 
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
                        $(document).trigger('qsm_after_quiz_submit', [quizId, response]);
                        
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
            
            // Clear existing content
            $quizContainer.find('.qsm_results_page').remove();
            
            // Create results container
            let $resultDiv = $('<div class="qsm_results_page">');
            
            $resultDiv.html(response.display);
            $quizContainer.append($resultDiv);
            $resultDiv.slideDown();
            $quizContainer.find('.qsm-quiz-processing-box').remove();
            
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
                this.quizObjects[quizId].$form.off();

                // Delete instance
                delete this.quizObjects[quizId];
                
                // Trigger destroy event
                $(document).trigger('qsm_navigation_destroyed', [quizId]);
            }
        }
    };

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