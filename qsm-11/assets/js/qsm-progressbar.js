/**
 * QSM Progress Bar - Minimized
 * @package QSM
 */
(function($) {
    'use strict';
    
    window.QSMPagination = window.QSMPagination || {};
    
    QSMPagination.ProgressBar = {
        quizObjects: {},

        init: function() {
            var self = this;
            // Look for quiz containers first (new structure)
            $('.qsm-quiz-container').each(function() {
                var $container = $(this);
                var $form = $container.find('.qsm-quiz-form');
                var quizId = $container.data('quiz-id');
                if (quizId && !self.quizObjects[quizId]) {
                    self.initProgressBar(quizId, $container, $form);
                }
            });
            
            this.bindEvents();
        },

        initProgressBar: function(quizId, $container, $form) {
            var data = window.qsmQuizData && window.qsmQuizData[quizId] ? window.qsmQuizData[quizId] : {};
            if (!data.progress_bar || data.progress_bar == 0) return;

            // Look for progress bar in container first, then form (for new structure)
            var $bar = $container.find('.qsm-progress-bar');

            if (!$bar.length) return;

            // Count pages - use same selectors as navigation system
            var $pages = $container.find('.qsm-page');
            var totalPages = Math.max($pages.length, 1);
            
            // Calculate question pages (exclude first page if present)
            var hasFirstPage = (data.disable_first_page != 1);
            var questionPages = hasFirstPage ? totalPages - 1 : totalPages;
            
            this.quizObjects[quizId] = {
                $container: $container,
                $form: $form,
                $bar: $bar,
                $fill: $bar.find('.qsm-progress-fill'),
                $text: $bar.find('.qsm-progress-text'),
                totalPages: totalPages,
                questionPages: questionPages,
                hasFirstPage: hasFirstPage,
                currentPage: 1
            };
            $bar.show();
            this.updateProgress(quizId, 1);
        },

        bindEvents: function() {
            var self = this;
            $(document).on('qsm_after_page_change', function(e, quizId, pageNumber) {
                self.updateProgress(quizId, pageNumber);
            });
            $(document).on('qsm_quiz_initialized', function(e, quizId, instance) {
                if (!self.quizObjects[quizId]) {
                    // Use container and form from navigation instance
                    self.initProgressBar(quizId, instance.quizContainer, instance.$form);
                }
            });
        },

        updateProgress: function(quizId, currentPage) {
            var instance = this.quizObjects[quizId];
            if (!instance) {
                return;
            }

            // If currentPage is not provided, try to get it from navigation system
            if (typeof currentPage === 'undefined' && window.QSMPagination && window.QSMPagination.Navigation) {
                var navInstance = window.QSMPagination.Navigation.quizObjects[quizId];
                if (navInstance) {
                    currentPage = navInstance.currentPage;
                    // Also sync total pages if navigation has more accurate count
                    if (navInstance.totalPages && navInstance.totalPages !== instance.totalPages) {
                        instance.totalPages = navInstance.totalPages;
                    }
                }
            }

            if (typeof currentPage === 'undefined') {
                currentPage = instance.currentPage;
            }

            instance.currentPage = currentPage;
            
            // Calculate progress based on question pages only (exclude first page)
            var effectiveCurrentPage = 0;
            var effectiveTotalPages = instance.questionPages;
            
            if (instance.hasFirstPage) {
                // First page exists - only count progress from page 2 onwards
                if (currentPage === 1) {
                    // On start page - 0% progress
                    effectiveCurrentPage = 0;
                } else {
                    // On question pages - calculate progress
                    effectiveCurrentPage = currentPage - 1;
                }
            } else {
                // No first page - all pages are question pages
                effectiveCurrentPage = currentPage;
            }
            
            var progress = Math.max(0, Math.min(100, (effectiveCurrentPage / effectiveTotalPages) * 100));
            
            
            instance.$fill.animate({ width: progress + '%' }, 300);
            if (instance.$text.length) {
                instance.$text.text(Math.round(progress) + '%');
            }
        },
    };

    $(document).ready(function() {
        QSMPagination.ProgressBar.init();
    });

})(jQuery);