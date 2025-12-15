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

        initProgressBar: function(quizId, $container, $form, $initial_page = 1) {
            var data = window.qmn_quiz_data && window.qmn_quiz_data[quizId] ? window.qmn_quiz_data[quizId] : {};
            if (!data.progress_bar || data.progress_bar == 0) return;
            
            // Look for progress bar in container first, then form (for new structure)
            let $bar = jQuery('.qsm-progress-bar-' + quizId);

            if (!$bar.length) return;

            // Determine render mode: 'auto' | 'svg' | 'simple' (provided via data-progress-mode)
            let mode = $bar.data('progress-mode') || 'auto';

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
                currentPage: $initial_page,
                mode: mode,
                bar: null
            };
			
            jQuery(document).trigger('qsm_init_progressbar_before', [quizId, qmn_quiz_data]);

            // Initialize ProgressBar.js Line only when library is available
            if (window.ProgressBar && typeof ProgressBar.Line === 'function') {
                window.qmn_quiz_data[quizId].bar = new ProgressBar.Line('#qsm_progress_bar_' + quizId, {
                    strokeWidth: 2,
                    easing: 'easeInOut',
                    duration: 1400,
                    color: '#3498db',
                    trailColor: '#eee',
                    trailWidth: 1,
                    svgStyle: { width: '100%', height: '100%' },
                    text: {
                        style: {
                            'position': 'absolute',
                            'right': '10px',
                            'font-size': '13px',
                            'font-weight': 'bold'
                        },
                        autoStyleContainer: false
                    },
                    from: { color: '#3498db' },
                    to: { color: '#ED6A5A' }
                });
            }
			
            jQuery(document).trigger('qsm_init_progressbar_after', [quizId, qmn_quiz_data]);

            $bar.show();
            this.updateProgress(quizId, $initial_page);
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
            // Use SVG ProgressBar.js when in svg mode and instance exists, otherwise fallback to simple width animation
            let animate_value = progress / 100;
            if (animate_value <= 1 && window.qmn_quiz_data && qmn_quiz_data[quizId] && qmn_quiz_data[quizId].bar) {
                qmn_quiz_data[quizId].bar.animate(animate_value);
                let old_text = currentQuiz.$text.text().replace(' %', '');
                let new_text = Math.round(animate_value * 100);
                if (!old_text || isNaN(parseInt(old_text, 10))) {
                    old_text = 0;
                }
                jQuery({
                    Counter: parseInt(old_text, 10)
                }).animate({
                    Counter: new_text
                }, {
                    duration: 1000,
                    easing: 'swing',
                    step: function () {
                        currentQuiz.$text.text(Math.round(this.Counter) + ' %');
                    }
                });
            }
        }

    };

    $(document).ready(function() {
        QSMPagination.ProgressBar.init();
    });

})(jQuery);