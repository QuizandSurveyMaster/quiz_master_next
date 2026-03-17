/**
 * QSM Timer - Enhanced with LocalStorage, Resume Functionality, and Conditional Popups
 * @package QSM
 */
var qsmTimerInterval = [];
(function($) {
    if (typeof qsm_timer_consumed_obj === 'undefined') {
        window.qsm_timer_consumed_obj = {
            qmn_count_upward_status : false
        };
    }
    
    QSMPagination.Timer = {
        quizObjects: {},
        intervals: {},
        originalTitle: document.title,
        warningThresholds: {
            ninety: 90,  // 90% time consumed
            ten: 10      // 10% time remaining
        },

        init: function() {
            const self = this;
            // Look for both new and legacy form selectors
            $('.qsm-quiz-container').each(function() {
                const $container = $(this);
                const $form = $container.find('.qsm-quiz-form');
                const quizId = $container.data('quiz-id');
                if (quizId && !self.quizObjects[quizId]) {
                    self.initTimer(quizId, $form);
                }
            });
            this.bindEvents();
        },

        initTimer: function(quizId, $form) {
            const data = this.getQuizData(quizId);
            
            if (!data.timer_limit || data.timer_limit <= 0) {
                return;
            }

            const $timer = $form.find('.mlw_qmn_timer');
            const totalTime = data.timer_limit * 60;
            const consumedTime = Number.parseInt(localStorage.getItem('mlw_time_consumed_quiz' + quizId), 10) || 1;
            const remainingTime = this.calculateInitialTime(quizId, totalTime, consumedTime);
            
            this.quizObjects[quizId] = {
                form: $form,
                timer: $timer.length ? $timer : null,
                totalTime: totalTime,
                remainingTime: remainingTime,
                consumedTime: consumedTime,
                isActive: false,
                data: data,
                warnings: {
                    ninety: false,
                    ten: false,
                    expired: false
                },
                timerStatus: false,
                timerInterval: null
            };

            // Update qmn_quiz_data for legacy compatibility
            this.updateLegacyQuizData(quizId);
            $form.find(".hiddentimer").html(remainingTime);
            
            if ($timer && $timer.length) {
                $timer.show();
            }
            this.updateDisplay(quizId);
            
            // Auto-start conditions matching legacy behavior
            if (this.shouldAutoStart(quizId, data)) {
                this.start(quizId);
            }
        },

        bindEvents: function() {
            const self = this;
            
            // QSM-11 events
            $(document).on('qsm_quiz_initialized', function(e, quizId, instance) {
                if (!self.quizObjects[quizId]) {
                    self.initTimer(quizId, instance.form);
                }
            });
            
            $(document).on('qsm_after_page_change', function(e, quizId, pageNumber) {
                if (pageNumber > 1 && self.quizObjects[quizId] && !self.quizObjects[quizId].isActive) {
                    self.start(quizId);
                }
            });
            
            $(document).on('qsm_quiz_started', function(e, quizId) {
                if (self.quizObjects[quizId] && !self.quizObjects[quizId].isActive) {
                    self.start(quizId);
                }
            });
            
            $(document).on('qsm_form_submit', function(e, quizId) {
                self.endTimer(quizId);
            });
            
            // Legacy events compatibility
            $(document).on('qsm_activate_time_before qsm_activate_time_after', function(e, quizId, qmn_quiz_data) {
                if (e.type === 'qsm_activate_time_before') {
                    self.start(quizId);
                }
            });
        },

        start: function(quizId) {
            const currentQuiz = this.quizObjects[quizId];
            if (!currentQuiz || currentQuiz.isActive) return;
            
            currentQuiz.isActive = true;
            currentQuiz.timerStatus = true;
            
            // Update legacy data
            this.updateLegacyQuizData(quizId);
            
            // Mark quiz as started in localStorage (legacy keys)
            localStorage.setItem('mlw_started_quiz' + quizId, 'yes');
            
            // Set initial time in localStorage if not set
            if (!localStorage.getItem('mlw_quiz_start_date' + quizId)) {
                localStorage.setItem('mlw_quiz_start_date' + quizId, new Date().getTime());
            }
            
            // Start the interval
            currentQuiz.timerInterval = setInterval(function() {
                this.tick(quizId);
            }.bind(this), 1000);
            
            this.intervals[quizId] = currentQuiz.timerInterval;
            
            // Show stop timer element if available
            $('.qsm-quiz-container-' + quizId).find('.stoptimer-p').show();
            
            // Trigger legacy events
            $(document).trigger('qsm_activate_time_after', [quizId, qmn_quiz_data]);
        },

        stop: function(quizId) {
            const currentQuiz = this.quizObjects[quizId];
            if (!currentQuiz) return;
            
            if (this.intervals[quizId]) {
                clearInterval(this.intervals[quizId]);
                delete this.intervals[quizId];
            }
            
            if (currentQuiz.timerInterval) {
                clearInterval(currentQuiz.timerInterval);
                currentQuiz.timerInterval = null;
            }
            
            currentQuiz.isActive = false;
            currentQuiz.timerStatus = false;
            
            // Update legacy data
            this.updateLegacyQuizData(quizId);
        },

        expire: function(quizId) {
            const currentQuiz = this.quizObjects[quizId];
            if (!currentQuiz || currentQuiz.warnings.expired) return;
            
            currentQuiz.warnings.expired = true;
            this.stop(quizId);
            currentQuiz.remainingTime = 0;
            this.updateDisplay(quizId);
            
            // Add visual indicators
            currentQuiz.timer?.addClass('qsm-timer-expired qsm-timer--danger');
            
            // Disable form inputs (matching legacy behavior)
            const $quizForm = currentQuiz.form;
            $quizForm.find('.mlw_qmn_quiz input:radio').attr('disabled', true);
            $quizForm.find('.mlw_qmn_quiz input:checkbox').attr('disabled', true);
            $quizForm.find('.mlw_qmn_quiz select').attr('disabled', true);
            $quizForm.find('.mlw_qmn_question_comment').attr('disabled', true);
            $quizForm.find('.mlw_answer_open_text').attr('disabled', true);
            $quizForm.find('.mlw_answer_number').attr('readonly', true);
            
            // Add timer ended class and error message
            const $container = $quizForm.closest('.qmn_quiz_container, .qsm-quiz-container');
            $container.addClass('qsm_timer_ended');
            
            // Add error message if not already present
            if (!$container.find('.qmn_error_message').length) {
                const errorMsg = currentQuiz.data.quiz_time_over || 'Time is up!';
                $container.prepend('<p class="qmn_error_message" style="color: red;">' + errorMsg + '</p>');
            }
            
            // Handle auto-submit or show modal
            if (Number(currentQuiz.data.enable_result_after_timer_end) === 1) {
                // Auto-submit the form
                setTimeout(function() {
                    $container.find('.qsm-submit-btn, .qsm_submit_btn').trigger('click');
                }, 1000);
            } else {
                // Show timer expired modal
                $('.qsm-quiz-container-' + quizId).find('.stoptimer-p').hide();
                MicroModal.show('modal-3');
            }
            
            // Trigger events
            $(document).trigger('qsm_timer_expired', [quizId, currentQuiz]);
            $(document).trigger('qsm_timer_ended', [quizId, qmn_quiz_data, {
                qmn_count_upward_status: false
            }]);
        },

        tick: function(quizId) {
            const currentQuiz = this.quizObjects[quizId];
            if (!currentQuiz || currentQuiz.warnings.expired) return;
            
            // Check for scheduled time expiration
            if (this.checkScheduledTimeExpiration(quizId)) {
                return; // Exit if quiz expired due to scheduled time
            }
            
            currentQuiz.remainingTime--;
            currentQuiz.consumedTime++;
            
            if (currentQuiz.remainingTime < 0) {
                currentQuiz.remainingTime = 0;
            }
            
            // Update legacy data
            this.updateLegacyQuizData(quizId);
            
            // Save current state to localStorage (both new and legacy keys)
            localStorage.setItem('mlw_time_consumed_quiz' + quizId, currentQuiz.consumedTime);
            localStorage.setItem('mlw_time_quiz' + quizId, currentQuiz.remainingTime);
            
            this.updateDisplay(quizId);
            this.checkWarnings(quizId);
            
            // Trigger events
            $(document).trigger('qsm_timer_tick', [quizId, currentQuiz.remainingTime, currentQuiz.consumedTime]);
            $(document).trigger('qmn_timer_consumed_seconds', [quizId, qmn_quiz_data, qsm_timer_consumed_obj]);
            $(document).trigger('load_timer_faces', [quizId, currentQuiz.remainingTime, currentQuiz.totalTime, this.secondsToTimer(currentQuiz.remainingTime)]);
            
            if (currentQuiz.remainingTime <= 0) {
                this.expire(quizId);
            }
        },

        updateDisplay: function(quizId) {
            const currentQuiz = this.quizObjects[quizId];
            if (!currentQuiz || currentQuiz.warnings.expired) return;
            
            const display = this.secondsToTimer(currentQuiz.remainingTime);
            
            if (currentQuiz.timer) {
                currentQuiz.timer.text(display);
            }

            // Update browser tab title
            if (currentQuiz.remainingTime > 0) {
                document.title = display + ' - ' + this.originalTitle;
            }
        },

        calculateInitialTime: function(quizId, totalTime, consumedTime) {
            const timerStarted = localStorage.getItem('mlw_started_quiz' + quizId) || localStorage.getItem('qsm_started_quiz_' + quizId);
            const storedConsumed = Number.parseInt(localStorage.getItem('mlw_time_consumed_quiz' + quizId), 10) || consumedTime || 1;
            let remainingTime = totalTime;
            
            if (timerStarted === 'yes' && storedConsumed > 1) {
                remainingTime = totalTime - storedConsumed + 1;
                if (remainingTime < 0) remainingTime = 0;
            }
            
            return remainingTime;
        },

        checkWarnings: function(quizId) {
            const currentQuiz = this.quizObjects[quizId];
            if (!currentQuiz) return;
            
            const percentConsumed = ((currentQuiz.totalTime - currentQuiz.remainingTime) / currentQuiz.totalTime) * 100;
            const percentRemaining = (currentQuiz.remainingTime / currentQuiz.totalTime) * 100;
            
            // Check for 90% time consumed warning (matching legacy behavior)
            const ninetyPercent = currentQuiz.totalTime - (currentQuiz.totalTime * 90 / 100);
            if (currentQuiz.remainingTime <= ninetyPercent && !currentQuiz.warnings.ninety) {
                currentQuiz.warnings.ninety = true;
                this.showNinetyPercentWarning(quizId);
            }
            
            // Check for 10% remaining warning
            if (percentRemaining <= 10 && percentRemaining > 0 && !currentQuiz.warnings.ten) {
                currentQuiz.warnings.ten = true;
                currentQuiz.timer?.addClass('qsm-timer--warning');
                
                // Trigger warning event
                $(document).trigger('qsm_timer_warning', [quizId, currentQuiz.remainingTime, percentRemaining]);
            }
        },

        secondsToTimer: function(seconds) {
            seconds = Number.parseInt(seconds, 10);
            if (seconds < 0) seconds = 0;

            const safeSeconds = seconds;
            
            const hours = Math.floor(safeSeconds / 3600);
            const minutes = Math.floor((safeSeconds % 3600) / 60);
            const secs = safeSeconds % 60;
            
            let formattedTime = '';
            
            // Hours
            if (hours === 0) {
                formattedTime = '00:';
            } else if (hours < 10) {
                formattedTime = '0' + hours + ':';
            } else {
                formattedTime = hours + ':';
            }
            
            // Minutes
            if (minutes === 0) {
                formattedTime += '00:';
            } else if (minutes < 10) {
                formattedTime += '0' + minutes + ':';
            } else {
                formattedTime += minutes + ':';
            }
            
            // Seconds
            if (secs === 0) {
                formattedTime += '00';
            } else if (secs < 10) {
                formattedTime += '0' + secs;
            } else {
                formattedTime += secs;
            }
            
            return formattedTime;
        },

        endTimer: function(quizId) {
            this.stop(quizId);
            
            // Clean up localStorage (both new and legacy keys)
            localStorage.setItem('mlw_time_quiz' + quizId, 'completed');
            localStorage.setItem('mlw_started_quiz' + quizId, 'no');
            localStorage.removeItem('mlw_time_consumed_quiz' + quizId);
            
            // Restore original title
            document.title = this.originalTitle;
            
            // Trigger events
            $(document).trigger('qsm_end_timer', [quizId, qmn_quiz_data]);
        },

        // Public API methods for external access
        getTimerInfo: function(quizId) {
            const currentQuiz = this.quizObjects[quizId];
            if (!currentQuiz || currentQuiz.warnings.expired) return null;
            
            return {
                quizId: quizId,
                totalTime: currentQuiz.totalTime,
                remainingTime: currentQuiz.remainingTime,
                consumedTime: currentQuiz.consumedTime,
                isActive: currentQuiz.isActive,
                formattedTime: this.secondsToTimer(currentQuiz.remainingTime),
                percentRemaining: (currentQuiz.remainingTime / currentQuiz.totalTime) * 100
            };
        },

        getAllTimers: function() {
            const timers = {};
            const self = this;
            Object.keys(this.quizObjects).forEach(function(quizId) {
                timers[quizId] = self.getTimerInfo(quizId);
            });
            return timers;
        },
        
        // New methods for enhanced functionality
        getQuizData: function(quizId) {
            // Try multiple data sources for compatibility
            let data = {};
            
            if (qmn_quiz_data && qmn_quiz_data[quizId]) {
                data = qmn_quiz_data[quizId];
            }
            
            return data;
        },
        
        updateLegacyQuizData: function(quizId) {
            const currentQuiz = this.quizObjects[quizId];
            if (!currentQuiz || currentQuiz.warnings.expired) return;
            
            // Ensure qmn_quiz_data exists and update it
            if (typeof qmn_quiz_data === 'undefined') {
                qmn_quiz_data = {};
            }
            
            if (!qmn_quiz_data[quizId]) {
                qmn_quiz_data[quizId] = currentQuiz.data;
            }
            
            // Update timer-specific properties
            qmn_quiz_data[quizId].timerStatus = currentQuiz.timerStatus;
            qmn_quiz_data[quizId].timerRemaning = currentQuiz.remainingTime;
            qmn_quiz_data[quizId].timerConsumed = currentQuiz.consumedTime;
            qmn_quiz_data[quizId].timerInterval = currentQuiz.timerInterval;
        },
        
        shouldAutoStart: function(quizId, data) {
            // Check if timer should auto-start based on various conditions
            if (data.timer_auto_start) return true;
            if (!data.first_page && !data.disable_first_page) return true;
            if (Number(data.disable_first_page) === 1) return true;
            
            return false;
        },
        
        checkScheduledTimeExpiration: function(quizId) {
            const currentQuiz = this.quizObjects[quizId];
            if (!currentQuiz || currentQuiz.warnings.expired) return false;
            
            const data = currentQuiz.data;
            if (data.not_allow_after_expired_time === '1' && data.scheduled_time_end) {
                const systemTime = Math.round(new Date().getTime() / 1000);
                if (systemTime > data.scheduled_time_end) {
                    MicroModal.show('modal-4');
                    return true;
                }
            }
            
            return false;
        },
        
        showNinetyPercentWarning: function(quizId) {
            const currentQuiz = this.quizObjects[quizId];
            if (!currentQuiz || currentQuiz.warnings.expired) return;
            
            const $container = currentQuiz.form.closest('.qmn_quiz_container, .qsm-quiz-container');
            $container.find('.qsm_ninety_warning').fadeIn();
        },
    };

    // Initialize timer on document ready
    $(document).ready(function() {
        QSMPagination.Timer.init();
    });

})(jQuery);
