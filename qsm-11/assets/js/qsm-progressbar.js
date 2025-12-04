/**
 * QSM Progress Bar - Minimized
 * @package QSM
 */
(function($) {
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
            var data = window.qmn_quiz_data && window.qmn_quiz_data[quizId] ? window.qmn_quiz_data[quizId] : {};
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
                $text: $bar.find('.progressbar-text'),
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
            var currentQuiz = this.quizObjects[quizId];
            if (!currentQuiz) {
                return;
            }

            // If currentPage is not provided, try to get it from navigation system
            if (typeof currentPage === 'undefined' && window.QSMPagination && window.QSMPagination.Navigation) {
                var navInstance = window.QSMPagination.Navigation.quizObjects[quizId];
                if (navInstance) {
                    currentPage = navInstance.currentPage;
                }
            }

            // Fallback to instance currentPage if still undefined
            if (typeof currentPage === 'undefined') {
                currentPage = currentQuiz.currentPage;
            }

            currentQuiz.currentPage = currentPage;
            
            // Calculate progress based on question pages only (exclude first page)
            var effectiveCurrentPage = 0;
            var effectiveTotalPages = currentQuiz.questionPages;
            
            if (currentQuiz.hasFirstPage) {
                // First page exists - only count progress from page 2 onwards
                if (currentPage == 1) {
                    // On start page (welcome page) - 0% progress
                    effectiveCurrentPage = 0;
                } else {
                    // On question pages - calculate progress
                    effectiveCurrentPage = currentPage - 1;
                }
            } else {
                // No first page - all pages are question pages
                effectiveCurrentPage = currentPage;
            }
            
            // Safeguard against division by zero and ensure valid percentage
            var progress = 0;
            if (effectiveTotalPages > 0) {
                progress = Math.max(0, Math.min(100, (effectiveCurrentPage / effectiveTotalPages) * 100));
            }
            
            // Ensure progress bar stays visible during all page transitions
            // This is critical for single-page quizzes where bar might hide on navigation
            currentQuiz.$bar.show();
            
            currentQuiz.$fill.animate({ width: progress + '%' }, 1400);
            
            if (currentQuiz.$text.length) {
                let $el = currentQuiz.$text;
                let oldVal = parseInt($el.text()) || 0;
                let newVal = Math.round(progress);

                $({val: oldVal}).animate({val: newVal}, {
                    duration: 1400,
                    easing: 'swing',
                    step: function(now) {
                        $el.text(Math.floor(now) + '%');
                    }
                });
            }
        }

    };

    $(document).ready(function() {
        QSMPagination.ProgressBar.init();
    });

})(jQuery);