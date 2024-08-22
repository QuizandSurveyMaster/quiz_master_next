/**************************
 * Quiz And Survey Master
 *************************/

/**************************
 * This object contains the newer functions. All global functions under are slowly
 * being deprecated and replaced with rewritten newer functions
 **************************/

var QSM;
var qsmTimerInterval = [];
(function ($) {
	QSM = {
		/**
		 * Initializes all quizzes or surveys on the page
		 */
		init: function () {
			// Makes sure we have quizzes on this page
			if (typeof qmn_quiz_data != 'undefined' && qmn_quiz_data) {
				// hide the recaptcha by default
				$('.g-recaptcha').hide();
				// Cycle through all quizzes
				_.each(qmn_quiz_data, function (quiz) {
					let quizID = parseInt(quiz.quiz_id);
					if ( !qmn_quiz_data[quizID].hasOwnProperty('timer_limit') && null !== localStorage.getItem('mlw_time_quiz' + quizID) ) {
						localStorage.removeItem('mlw_time_quiz' + quizID);
					}
					if ( null == localStorage.getItem('mlw_quiz_start_date' + quizID) ) {
						localStorage.setItem('mlw_quiz_start_date' + quizID, qmn_ajax_object.start_date);
						localStorage.setItem('mlw_time_consumed_quiz' + quizID, 1);
					}
					jQuery.ajax({
						url: qmn_ajax_object.ajaxurl,
						data: {
							action: "qsm_create_quiz_nonce",
							quiz_id: quizID,
						},
						type: 'POST',
						success: function (response) {
							jQuery('.qsm-quiz-container-' + quizID + ' #qsm_unique_key_'+quizID).val(response.data.unique_key);
							jQuery('.qsm-quiz-container-' + quizID + ' #qsm_nonce_'+quizID).val(response.data.nonce);
						}
					});
					QSM.initPagination(quizID);
					if ( ( quiz.hasOwnProperty('pagination') || ( _.keys(quiz.qpages).length > 1 && !jQuery('.qsm-quiz-container-'+quizID+' .qsm-auto-page-row').length ) ) ) {
						qsmEndTimeTakenTimer(quizID);
						jQuery('.qsm-quiz-container-' + quizID + ' #timer').val(0);
						jQuery(".qsm-quiz-container-" + quizID + " input[name='timer_ms']").val(0);
						quizType = 'paginated';
						jQuery(document).trigger('start_stop_page_timer', [quizID]);
					}
					if (quiz.hasOwnProperty('timer_limit') && 0 != quiz.timer_limit) {
						QSM.initTimer(quizID);
						quizType = 'timer';
					} else {
						qsmTimerInterval[quizID] = setInterval(function () { qmnTimeTakenTimer(quizID) }, 1000);
					}
					if (jQuery('.qsm-quiz-container-' + quizID + ' .qsm-submit-btn').is(':visible') && !jQuery('.qsm-quiz-container-' + quizID).hasClass('qsm_auto_pagination_enabled') ) {
						jQuery('.qsm-quiz-container-' + quizID + ' .qsm-quiz-comment-section').fadeIn();
					}
				});
			}
		},

		/**
		 * Sets up timer for a quiz
		 *
		 * @param int quizID The ID of the quiz
		 */
		initTimer: function (quizID) {

			jQuery(document).trigger('qsm_init_timer_before', [quizID]);

			// Gets our form
			var $quizForm = QSM.getQuizForm(quizID);

			// Creates timer status key.
			qmn_quiz_data[quizID].timerStatus = false;

			// If we are using the newer pagination system...
			if (0 < $quizForm.children('.qsm-page').length) {
				// If there is a first page...
				if (!qmn_quiz_data[quizID].hasOwnProperty('first_page') || !qmn_quiz_data[quizID].first_page) {
					QSM.activateTimer(quizID);
					$('.qsm-quiz-container-' + quizID).find('.stoptimer-p').show();
				}
				// ...else, we must be using the questions per page option.
			} else if (qmn_quiz_data[quizID].hasOwnProperty('pagination') && qmn_quiz_data[quizID].first_page) {
				$(document).on('click', '.qsm-quiz-container-' + quizID + ' .mlw_next', function (event) {
					event.preventDefault();
					if ( qmn_quiz_data[quizID].hasOwnProperty('advanced_timer') && qmn_quiz_data[quizID].advanced_timer.hasOwnProperty('show_stop_timer') ) {
						var start_timer = parseInt(qmn_quiz_data[quizID].advanced_timer.start_timer_page);
						if ($('.qsm-quiz-container-' + quizID).find('.qmn_pagination > .slide_number_hidden').val() == start_timer) {
							QSM.activateTimer(quizID);
							$('.qsm-quiz-container-' + quizID).find('.stoptimer-p').show();
						}
					} else if (!qmn_quiz_data[quizID].timerStatus && (0 == $('.quiz_begin:visible').length || (1 == $('.quiz_begin:visible').length && qmnValidatePage('quizForm' + quizID)))) {
						QSM.activateTimer(quizID);
						$('.qsm-quiz-container-' + quizID).find('.stoptimer-p').show();
					}
				});
			} else {
				QSM.activateTimer(quizID);
				$('.qsm-quiz-container-' + quizID).find('.stoptimer-p').show();
			}
			jQuery(document).trigger('qsm_init_timer_after', [quizID]);
		},
		/**
		 * Starts the timer for the quiz.
		 *
		 * @param int quizID The ID of the quiz.
		 */
		activateTimer: function (quizID) {
			var timer_ms = jQuery(".qsm-quiz-container-" + quizID + " input[name='timer_ms']").val();
			if (timer_ms == 0) {
				jQuery('.qsm-quiz-container-' + quizID + ' #timer').val(0);
				qsmTimerInterval[quizID] = setInterval(function () { qmnTimeTakenTimer(quizID) }, 1000);
				jQuery(".qsm-quiz-container-" + quizID + " input[name='timer_ms']").each(function () {
					var timems = qsmTimeInMS();
					jQuery(this).val(timems);
				});
			}

			jQuery(document).trigger('qsm_activate_time_before', [quizID, qmn_quiz_data]);
			// Gets our form.
			var $timer = QSM.getTimer(quizID);

			// Sets up our variables.
			qmn_quiz_data[quizID].timerStatus = true;
			var seconds = 0;

			// Calculates starting time.
			var timerTotal = parseFloat(qmn_quiz_data[quizID].timer_limit) * 60;
			var timerStarted = localStorage.getItem('mlw_started_quiz' + quizID);
			var timerConsumed = parseInt(localStorage.getItem('mlw_time_consumed_quiz' + quizID));
			var timerRemaning = timerTotal - timerConsumed;
			if ('yes' == timerStarted && 0 < timerRemaning) {
				seconds = parseInt(timerRemaning);
			} else {
				seconds = parseFloat(qmn_quiz_data[quizID].timer_limit) * 60;
			}
			qmn_quiz_data[quizID].timerRemaning = seconds;
			qmn_quiz_data[quizID].timerConsumed = timerConsumed;

			//hidden timer
			jQuery(".hiddentimer").html(seconds);

			// Makes the timer appear.
			$timer.show();
			$timer.text(QSM.secondsToTimer(seconds));

			// Sets up timer interval.
			if (!isNaN(qmn_quiz_data[quizID].timerRemaning)) {
				clearInterval(qmn_quiz_data[quizID].timerInterval);
				qmn_quiz_data[quizID].timerInterval = setInterval(QSM.timer, 1000, quizID);
			}
			jQuery(document).trigger('qsm_activate_time_after', [quizID, qmn_quiz_data]);
		},
		/**
		 * Reduces the timer by one second and checks if timer is 0
		 *
		 * @param int quizID The ID of the quiz.
		 */
		timer: function (quizID) {
			qmn_quiz_data[quizID].timerRemaning -= 1;
			qmn_quiz_data[quizID].timerConsumed += 1;
			if (0 > qmn_quiz_data[quizID].timerRemaning) {
				qmn_quiz_data[quizID].timerRemaning = 0;
			}
			var secondsRemaining = qmn_quiz_data[quizID].timerRemaning;
			var secondsConsumed = qmn_quiz_data[quizID].timerConsumed;
			if (localStorage.getItem('mlw_time_quiz' + quizID) != null ) {
				secondsRemaining = (parseFloat(qmn_quiz_data[quizID].timer_limit) * 60) - secondsConsumed + 1;
				if(secondsRemaining < 0) {
					secondsRemaining = 0;
				}
			}
			var display = QSM.secondsToTimer(secondsRemaining);
			var systemTime = new Date().getTime() / 1000;
			systemTime = Math.round(systemTime);
			if ('1' === qmn_quiz_data[quizID].not_allow_after_expired_time && systemTime > qmn_quiz_data[quizID].scheduled_time_end) {
				MicroModal.show('modal-4');
				return false;
			}
			// Sets our local storage values for the timer being started and current timer value.
			localStorage.setItem('mlw_time_consumed_quiz' + quizID, secondsConsumed );
			localStorage.setItem('mlw_time_quiz' + quizID, secondsRemaining);
			localStorage.setItem('mlw_started_quiz' + quizID, "yes");

			// Updates timer element and title on browser tab.
			var $timer = QSM.getTimer(quizID);
			$timer.text(display);
			if (0 < qmn_quiz_data[quizID].timer_limit) {
				document.title = display + ' ' + qsmTitleText;
			}

			var $quizForm = QSM.getQuizForm(quizID);
			var total_seconds = parseFloat(qmn_quiz_data[quizID].timer_limit) * 60;
			var ninety_sec = total_seconds - (total_seconds * 90 / 100);
			/*CUSTOM TIMER*/
			jQuery(document).trigger('load_timer_faces', [quizID, secondsRemaining, total_seconds, display]);
			if (ninety_sec == secondsRemaining) {
				$quizForm.closest('.qmn_quiz_container').find('.qsm_ninety_warning').fadeIn();
			}

			// If timer is run out, disable fields.
			if (0 >= secondsRemaining && 0 < qmn_quiz_data[quizID].timer_limit) {
				clearInterval(qmn_quiz_data[quizID].timerInterval);
				$(".mlw_qmn_quiz input:radio").attr('disabled', true);
				$(".mlw_qmn_quiz input:checkbox").attr('disabled', true);
				$(".mlw_qmn_quiz select").attr('disabled', true);
				$(".mlw_qmn_question_comment").attr('disabled', true);
				$(".mlw_answer_open_text").attr('disabled', true);
				$(".mlw_answer_number").attr('readonly', true);

				$quizForm.closest('.qmn_quiz_container').addClass('qsm_timer_ended');
				$quizForm.closest('.qmn_quiz_container').prepend('<p class="qmn_error_message" style="color: red;">' + qmn_ajax_object.quiz_time_over + '</p>');
				if (qmn_quiz_data[quizID].enable_result_after_timer_end == 1) {
					$quizForm.closest('.qmn_quiz_container').find('.qsm-submit-btn').trigger('click');
				} else {
					$('.qsm-quiz-container-' + quizID).find('.stoptimer-p').hide();
					MicroModal.show('modal-3');
				}
				return;
			}
		},
		/**
		 * Clears timer interval
		 *
		 * @param int quizID The ID of the quiz
		 */
		endTimer: function (quizID) {
			localStorage.setItem('mlw_time_quiz' + quizID, 'completed');
			localStorage.setItem('mlw_started_quiz' + quizID, 'no');
			localStorage.removeItem('mlw_time_consumed_quiz' + quizID);
			document.title = qsmTitleText;
			if (typeof qmn_quiz_data[quizID].timerInterval != 'undefined') {
				clearInterval(qmn_quiz_data[quizID].timerInterval);
			}
			jQuery(document).trigger('qsm_end_timer', [quizID, qmn_quiz_data]);
		},
		/**
		 * Converts seconds to 00:00:00 format
		 *
		 * @param int seconds The number of seconds
		 * @return string A string in H:M:S format
		 */
		secondsToTimer: function (seconds) {
			var formattedTime = '';
			seconds = parseInt(seconds);

			// Prepares the hours part.
			var hours = Math.floor(seconds / 3600);
			if (0 === hours) {
				formattedTime = '00:';
			} else if (10 > hours) {
				formattedTime = '0' + hours + ':';
			} else {
				formattedTime = hours + ':';
			}

			// Prepares the minutes part.
			var minutes = Math.floor((seconds % 3600) / 60);
			if (0 === minutes) {
				formattedTime = formattedTime + '00:';
			} else if (10 > minutes) {
				formattedTime = formattedTime + '0' + minutes + ':';
			} else {
				formattedTime = formattedTime + minutes + ':';
			}

			// Prepares the seconds part.
			var remainder = Math.floor((seconds % 60));
			if (0 === remainder) {
				formattedTime = formattedTime + '00';
			} else if (10 > remainder) {
				formattedTime = formattedTime + '0' + remainder;
			} else {
				formattedTime = formattedTime + remainder;
			}
			return formattedTime;
		},
		/**
		 * Gets the jQuery object for the timer
		 */
		getTimer: function (quizID) {
			var $quizForm = QSM.getQuizForm(quizID);
			return $quizForm.children('.mlw_qmn_timer');
		},

		/**
		 * Sets up pagination for a quiz
		 *
		 * @param int quizID The ID of the quiz.
		 */
		initPagination: function (quizID) {
			var $quizForm = QSM.getQuizForm(quizID);
			/**
			 *
			 * CHecking if the quiz is random
			 */
			$is_random = $('.qmn_quiz_container').hasClass('random');
			if (0 < $quizForm.children('.qsm-page').length) {
				$quizForm.children('.qsm-page').hide();
				template = wp.template('qsm-pagination-' + quizID);
				$quizForm.append(template());
				if ($quizForm.find('.qsm-pagination > .current_page_hidden').length == 0) {
					$quizForm.find('.qsm-pagination').append('<input type="hidden" value="0" name="current_page" class="current_page_hidden" />');
				}
				if ('1' == qmn_quiz_data[quizID].progress_bar) {
					jQuery(document).trigger('qsm_init_progressbar_before', [quizID, qmn_quiz_data]);
					$('#quizForm' + quizID).find('.qsm-progress-bar').show();
					qmn_quiz_data[quizID].bar = createQSMProgressBar(quizID, '#quizForm' + quizID + ' .qsm-progress-bar');
					jQuery(document).trigger('qsm_init_progressbar_after', [quizID, qmn_quiz_data]);
				}
				QSM.goToPage(quizID, 1);
				jQuery(document).on('click', '.qsm-quiz-container-' + quizID + ' .qsm-next', function (event) {
					jQuery(document).trigger('qsm_next_button_click_before', [quizID]);
					event.preventDefault();
					let $quizForm = QSM.getQuizForm(quizID);
					jQuery('.qsm-quiz-container-' + quizID + ' .mlw_custom_next').addClass('qsm-disabled-btn');
					jQuery('.qsm-quiz-container-' + quizID + ' .mlw_custom_next').append('<div class="qsm-spinner-loader" style="font-size: 3.5px;margin-right: -5px;margin-left: 10px;"></div>');
					jQuery('.qsm-multiple-response-input:checked, .qmn-multiple-choice-input:checked , .qsm_select:visible').each(function () {
						if (qmn_quiz_data[quizID].end_quiz_if_wrong > 0 && jQuery(this).parents().is(':visible') && jQuery(this).is('input, select')) {
							if (jQuery(this).parents('.qmn_radio_answers, .qsm_check_answer')) {
								let question_id = jQuery(this).attr('name').split('question')[1],
								value = jQuery(this).val(),
								$this = jQuery(this).parents('.quiz_section');
								if (value !== "") {
									qsm_submit_quiz_if_answer_wrong(question_id, value, $this, $quizForm);
								}
							}
						}
					})
					jQuery('.qsm-quiz-container-' + quizID + ' .mlw_custom_next').removeClass('qsm-disabled-btn');
					jQuery('.qsm-quiz-container-' + quizID + ' .qsm-spinner-loader').remove();
					QSM.nextPage(quizID);
					var $container = jQuery('.qsm-quiz-container-' + quizID);
					if (qmn_quiz_data[quizID].disable_scroll_next_previous_click != 1) {
						qsmScrollTo($container);
					}
					jQuery(document).trigger('qsm_next_button_click_after', [quizID]);
				});
				jQuery(document).on('click', '.qsm-quiz-container-' + quizID + ' .qsm-previous', function (event) {
					jQuery(document).trigger('qsm_previous_button_click_before', [quizID]);
					event.preventDefault();
					QSM.prevPage(quizID);
					var $container = jQuery('.qsm-quiz-container-' + quizID);
					if (qmn_quiz_data[quizID].disable_scroll_next_previous_click != 1) {
						qsmScrollTo($container);
					}
					jQuery(document).trigger('qsm_previous_button_click_after', [quizID]);
				});
			}
			// Gets timer element.
			let $timer = QSM.getTimer(quizID);

			// Calculates starting time.
			let timerStarted = localStorage.getItem('mlw_started_quiz' + quizID);
			let timerConsumed = parseInt(localStorage.getItem('mlw_time_consumed_quiz' + quizID));
			let seconds = parseFloat(qmn_quiz_data[quizID].timer_limit) * 60;
			let timerRemaning = seconds - timerConsumed;
			if ('yes' == timerStarted && 0 < timerRemaning) {
				seconds = parseInt(timerRemaning);
			}
			$timer.text(QSM.secondsToTimer(seconds));
		},
		/**
		 * Navigates quiz to specific page
		 *
		 * @param int pageNumber The number of the page
		 */
		goToPage: function (quizID, pageNumber) {
			jQuery(document).trigger('qsm_go_to_page_before', [quizID, pageNumber]);
			var $quizForm = QSM.getQuizForm(quizID);
			var $pages = $quizForm.children('.qsm-page');
			var $currentPage = $quizForm.children('.qsm-page:nth-of-type(' + pageNumber + ')');
			var $container = jQuery( '.qsm-quiz-container-' + quizID );
			$pages.hide();
			$currentPage.show();
			jQuery(document).trigger('end_page_timer_init_page_timer', [quizID, $currentPage]);
			$quizForm.find('.current_page_hidden').val(pageNumber - 1);
			$quizForm.find('.qsm-previous').hide();
			$quizForm.find('.qsm-next').hide();
			$quizForm.find('.qsm-submit-btn').hide();
			$quizForm.find('.g-recaptcha').hide();
			if (pageNumber < $pages.length) {
				$quizForm.find('.qsm-next').show();
				check_if_show_start_quiz_button($container, $pages.length, pageNumber);
			} else {
				$quizForm.find('.qsm-submit-btn').show();
				if ( !jQuery('.qsm-quiz-container-'+ quizID +'.random') || !qmn_quiz_data[quizID].hasOwnProperty('pagination') ) {
					$quizForm.find('.g-recaptcha').show();
				}
			}
			if (1 < pageNumber) {
				$quizForm.find('.qsm-previous').show();
			}
			if (1 == $currentPage.data('prevbtn')) {
				$quizForm.find('.qsm-previous').hide();
			}
			if ('1' == qmn_quiz_data[quizID].disable_first_page) {
				if (pageNumber == 1) {
					$quizForm.find(".mlw_previous").hide();
					$quizForm.find('.qsm-page-' + (parseInt(pageNumber))).show();
				}
			}
			if ('1' == qmn_quiz_data[quizID].progress_bar) {
				var current_page = jQuery('#quizForm' + quizID).find('.current_page_hidden').val();
				var total_page_length = $pages.length - 1;
				if (qmn_quiz_data[quizID].contact_info_location == 0) {
					//Do nothing.
				} else if (qmn_quiz_data[quizID].contact_info_location == 1) {
					if ($quizForm.children('.qsm-page').find('.qsm_contact_div ').length > 0) {
						//total_page_length = total_page_length - 1;
					}
				}
				var animate_value = current_page / total_page_length;
				if (animate_value <= 1) {
					if (!qmn_quiz_data[quizID].bar) {
						jQuery( '#quizForm' + quizID + ' .qsm-progress-bar svg' ).remove();
						qmn_quiz_data[quizID].bar =  createQSMProgressBar(quizID, '#quizForm' + quizID + ' .qsm-progress-bar');
					}
					qmn_quiz_data[quizID].bar.animate(animate_value);
					var old_text = jQuery('#quizForm' + quizID).find('.progressbar-text').text().replace(' %', '');
					var new_text = Math.round(animate_value * 100);
					jQuery({
						Counter: old_text
					}).animate({
						Counter: new_text
					}, {
						duration: 1000,
						easing: 'swing',
						step: function () {
							jQuery('#quizForm' + quizID).find('.progressbar-text').text(Math.round(this.Counter) + ' %');
						}
					});
				}
			}
			QSM.savePage(quizID, pageNumber);
			jQuery(document).trigger('qsm_go_to_page_after', [quizID, pageNumber]);
		},
		/**
		 * Moves forward or backwards through the pages
		 *
		 * @param int quizID The ID of the quiz
		 * @param int difference The number of pages to forward or back
		 */
		changePage: function (quizID, difference) {
			var page = QSM.getPage(quizID);
			if (qmn_quiz_data[quizID].hasOwnProperty('first_page') && qmn_quiz_data[quizID].first_page) {
				if (qmn_quiz_data[quizID].hasOwnProperty('advanced_timer') && qmn_quiz_data[quizID].advanced_timer.hasOwnProperty('show_stop_timer') ) {
					var start_timer = parseInt(qmn_quiz_data[quizID].advanced_timer.start_timer_page);
					if (page == start_timer) { // check current page
						QSM.activateTimer(quizID);
						$('.qsm-quiz-container-' + quizID).find('.stoptimer-p').show();
					}
				} else if (!qmn_quiz_data[quizID].timerStatus) {
					QSM.activateTimer(quizID);
					$('.qsm-quiz-container-' + quizID).find('.stoptimer-p').show();
				}

			}
			page += difference;
			QSM.goToPage(quizID, page);
		},
		nextPage: function (quizID) {
			if (qmnValidatePage('quizForm' + quizID)) {
				QSM.changePage(quizID, 1);
			}
		},
		prevPage: function (quizID) {
			QSM.changePage(quizID, -1);
		},
		savePage: function (quizID, pageNumber) {
			sessionStorage.setItem('quiz' + quizID + 'page', pageNumber);
		},
		getPage: function (quizID) {
			pageNumber = parseInt(sessionStorage.getItem('quiz' + quizID + 'page'));
			if (isNaN(pageNumber) || null == pageNumber) {
				pageNumber = 1;
			}
			return pageNumber;
		},
		/**
		 * Scrolls to the top of supplied element
		 *
		 * @param jQueryObject The jQuery version of an element. i.e. $('#quizForm3')
		 */
		scrollTo: function ($element) {
			jQuery('html, body').animate(
				{
					scrollTop: $element.offset().top - 150
				},
				1000);
		},
		/**
		 * Gets the jQuery object of the quiz form
		 */
		getQuizForm: function (quizID) {
			return $('#quizForm' + quizID);
		},
		q_counter: Backbone.Model.extend({
			defaults: {
				answers: []
			}
		}),
		changes: function (data, question_id, quiz_id) {
			answers = qsmLogicModel.get('answers');
			answers.push({
				'q_id': question_id,
				'incorrect': data.success == 'correct' ? 0 : 1,
			});
			qsmLogicModel.set({ 'answers': QSM.filter_question(qsmLogicModel.get('answers')) });
			let update_answers = qsmLogicModel.get('answers');
			let incorrect = 0;

			update_answers.forEach(function(obj){
			if(obj.incorrect == 1){
				incorrect++;
			}
			});
			if( qmn_quiz_data[quiz_id].end_quiz_if_wrong <= incorrect ) {
				submit_status = true;
			}else{
				submit_status = false;
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
	};
	// On load code
	$(function () {
		qmnDoInit();
	});
}(jQuery));

// Global Variables
var qsmTitleText = document.title;
var qsmLogicModel = new QSM.q_counter({});

/**
 * Validates an email ID.
 *
 * @param email The Email Id to validate.
 * @returns Boolean
 */
function isEmail(email) {
	var regex = /^([a-zA-Z0-9_\.\-\+])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/;
	if (!regex.test(email)) {
		return false;
	} else {
		return true;
	}
}
/**
 * Validates an email ID from specific domains.
 *
 * @param email The Email Id to validate.
 * @returns Boolean
 */
function isValidDomains(email, domains) {
	if ('undefined' == domains) {
		return true;
	}
	if (0 == domains.length) {
		return true;
	}
	for (let i = 0; i < domains.length; i++) {
		if (email.indexOf(domains[i]) != -1) {
			return true;
		}
	}
	return false;
}
function isBlockedDomain(email, blockdomains) {
    if (typeof blockdomains === 'undefined') {
        return false;
    }
    if (blockdomains.length === 0) {
        return false;
    }
    for (let i = 0; i < blockdomains.length; i++) {
        if (email.indexOf(blockdomains[i]) !== -1) {
            return true;
        }
    }
    return false;
}

/**
 * Validates a URL.
 *
 * @param url URL to validate.
 * @returns Boolean
 */
function isUrlValid(url) {
	return /^(http|https|ftp):\/\/[a-z0-9]+([\-\.]{1}[a-z0-9]+)*\.[a-z]{2,5}(:[0-9]{1,5})?(\/.*)?$/i.test(url);
}

/**
 * Create QSM progress bar
 * @returns
 */
function createQSMProgressBar(quizID, elementID) {
	return new ProgressBar.Line(elementID, {
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

/**
 * Limit multiple response based on question limit
 * @returns
 */
function qsmCheckMR(event, limit) {
	var checked = jQuery(event).parents('.quiz_section').find(':checkbox:checked').length;
	if (checked > limit) {
		event.checked = false;
		if (jQuery(event).parents('.quiz_section').find('.multi-checkbox-limit-reach').length == 0) {
			jQuery(event).parents('.quiz_section').append('<span class="multi-checkbox-limit-reach">' + qmn_ajax_object.multicheckbox_limit_reach + '</span>');
		}
	} else {
		jQuery(event).parents('.quiz_section').find('.multi-checkbox-limit-reach').remove();
	}
}

function qmnDoInit() {
	// Legacy init.
	qmnInit();

	// Call main initialization.
	qsminstance = QSM.init();

	jQuery(document).on("qsm_next_button_click_after", function (_event, _quiz_id) {
		if (quizType == 'paginated') {
			var timer_ms = jQuery(".qsm-quiz-container-" + _quiz_id + " input[name='timer_ms']").val();
			if (timer_ms == 0) {
				jQuery('.qsm-quiz-container-' + _quiz_id + ' #timer').val(0);
				qsmTimerInterval[_quiz_id] = setInterval(function () { qmnTimeTakenTimer(_quiz_id) }, 1000);
				jQuery(".qsm-quiz-container-" + _quiz_id + " input[name='timer_ms']").each(function () {
					var timems = qsmTimeInMS();
					jQuery(this).val(timems);
				});
			}
		}
		if ( jQuery('.qsm-quiz-container-' + _quiz_id + ' .qsm-submit-btn').is(':visible') || jQuery('.qsm-quiz-container-' + _quiz_id + ' .qsm-quiz-comment-section').closest('.qsm-page').is(':visible') ) {
			jQuery('.qsm-quiz-container-' + _quiz_id + ' .qsm-quiz-comment-section').fadeIn();
		}
	});
}

function qmnTimeTakenTimer(quizID) {
	var x = +jQuery('.qsm-quiz-container-' + quizID + ' #timer').val();
	if (NaN === x) {
		x = 0;
	}
	x = x + 1;
	jQuery('.qsm-quiz-container-' + quizID + ' #timer').val(x);
}

function qsmEndTimeTakenTimer(quiz_id) {
	clearInterval(qsmTimerInterval[quiz_id]);
}

function qsmTimeInMS() {
	var d = new Date();
	var n = d.getTime();
	return n;
}

function qmnClearField(field) {
	if (field.defaultValue == field.value) field.value = '';
}

var qsmPagescrolling = false;
function qsmScrollTo($element) {
    if ($element.length > 0 && !qsmPagescrolling) {
        qsmPagescrolling = true;
        jQuery(document).trigger('qsm_scroll_to_top_before', [$element]);
        jQuery('html, body').animate({ scrollTop: $element.offset().top - 150 }, 1000, function() {
            qsmPagescrolling = false;
            jQuery(document).trigger('qsm_scroll_to_top_after', [$element]);
        });
    }
}


function qmnDisplayError(message, field, quiz_form_id) {
	jQuery('#' + quiz_form_id + ' .qmn_error_message_section').addClass('qmn_error_message');
	jQuery('#' + quiz_form_id + ' .qmn_error_message').text(message);
	if (field.parents('.qsm_contact_div').length) {
		field.parents('.qsm_contact_div').addClass('qmn_error');
	} else {
		field.closest('.quiz_section').addClass('qmn_error');
	}
}

function qmnResetError(quiz_form_id) {
	jQuery('#' + quiz_form_id + ' .qmn_error_message').text('');
	jQuery('#' + quiz_form_id + ' .qmn_error_message_section').removeClass('qmn_error_message');
	jQuery('#' + quiz_form_id + ' .qsm_contact_div').removeClass('qmn_error');
	jQuery('#' + quiz_form_id + ' .quiz_section').removeClass('qmn_error');
}
var show_result_validation = true;
function qmnValidation(element, quiz_form_id) {
	show_result_validation = true;
	jQuery(document).trigger('qsm_before_validation', [element, quiz_form_id]);
	let quiz_id = quiz_form_id.replace('quizForm', '');
	var error_messages = qmn_quiz_data[quiz_id].error_messages;
	qmnResetError(quiz_form_id);
	jQuery(element).each(function () {
		if ( jQuery(this).attr('class') && ( jQuery(this).is(':visible') || jQuery(this).attr('class').indexOf('mlwRequiredAccept') || ( jQuery(this).attr('class').indexOf('mlwRequiredPolar') > -1 && jQuery(this).parent().is(':visible') ) ) ) {
			if (jQuery(this).attr('class').indexOf('mlwEmail') !== -1 && this.value !== "") {
				// Remove any trailing and preceeding space.
				var x = jQuery.trim(this.value);
				if (!isEmail(x)) {
					qmnDisplayError(error_messages.email_error_text, jQuery(this), quiz_form_id);
					show_result_validation = false;
				}
				/**
				 * Validate email from allowed domains.
				 */
				var domains = jQuery(this).attr('data-domains');
				if ('undefined' != typeof domains) {
					if (!isValidDomains(x, domains.split(","))) {
						qmnDisplayError(error_messages.email_error_text, jQuery(this), quiz_form_id);
						show_result_validation = false;
					}
				}
				/**
				 * Validate email from blocked domains.
				 */
				let blockdomains = jQuery(this).attr('data-blockdomains');
				if (typeof blockdomains !== 'undefined') {
					if (isBlockedDomain(x, blockdomains.split(","))) {
						qmnDisplayError(error_messages.email_error_text, jQuery(this), quiz_form_id);
						show_result_validation = false;
					}
				}
			}
			if (jQuery(this).attr('class').indexOf('mlwUrl') !== -1 && this.value !== "") {
				// Remove any trailing and preceeding space.
				if (!isUrlValid(jQuery.trim(this.value))) {
					qmnDisplayError(error_messages.url_error_text, jQuery(this), quiz_form_id);
					show_result_validation = false;
				}
			}
			if (jQuery(this).attr('class').indexOf('mlwMinLength') !== -1 && this.value !== "") {
				// Remove any trailing and preceeding space.
				if (jQuery.trim(this.value).length < jQuery(this).attr('minlength')) {
					var minlength_error = error_messages.minlength_error_text;
					minlength_error = minlength_error.replace('%minlength%', jQuery(this).attr('minlength'));
					qmnDisplayError(minlength_error, jQuery(this), quiz_form_id);
					show_result_validation = false;
				}
			}
			if ( jQuery(this).hasClass('mlw_answer_open_text') || jQuery(this).hasClass('qmn_fill_blank') || jQuery(this).hasClass('mlw_answer_number')) {
				if (jQuery.trim(this.value).length < jQuery(this).attr('minlength')) {
					let minCharError = error_messages.minlength_error_text;
					minCharError = minCharError.replace('%minlength%', jQuery(this).attr('minlength'));
					qmnDisplayError(minCharError, jQuery(this), quiz_form_id);
					show_result_validation = false;
				}
			}
			if (jQuery(this).attr('class').indexOf('mlwMaxLength') !== -1 && this.value !== "") {
				// Remove any trailing and preceeding space.
				if (jQuery.trim(this.value).length > jQuery(this).attr('maxlength')) {
					var maxlength_error = error_messages.maxlength_error_text;
					maxlength_error = maxlength_error.replace('%maxlength%', jQuery(this).attr('maxlength'));
					qmnDisplayError(maxlength_error, jQuery(this), quiz_form_id);
					show_result_validation = false;
				}
			}
			var by_pass = true;
			if (qmn_quiz_data[quiz_id].timer_limit_val > 0 && qmn_quiz_data[quiz_id].hasOwnProperty('skip_validation_time_expire') && qmn_quiz_data[quiz_id].skip_validation_time_expire != 1) {
				by_pass = false;
			}
			if (localStorage.getItem('mlw_time_quiz' + quiz_id) === null || (0 == localStorage.getItem('mlw_time_quiz' + quiz_id) && by_pass == false) || localStorage.getItem('mlw_time_quiz' + quiz_id) > 0.08 || by_pass === false) {
				if (jQuery(this).attr('class').indexOf('mlwRequiredNumber') > -1 && this.value === "" && +this.value != NaN) {
					qmnDisplayError(error_messages.number_error_text, jQuery(this), quiz_form_id);
					show_result_validation = false;
				}
				if (jQuery(this).attr('class').indexOf('mlwRequiredDate') > -1 && this.value === "") {
					qmnDisplayError(error_messages.empty_error_text, jQuery(this), quiz_form_id);
					show_result_validation = false;
				}
				if (jQuery(this).attr('class').indexOf('mlwRequiredText') > -1 && jQuery.trim(this.value) === "") {
					qmnDisplayError(error_messages.empty_error_text, jQuery(this), quiz_form_id);
					show_result_validation = false;
				}
				if (jQuery(this).attr('class').indexOf('mlwRequiredCaptcha') > -1 && this.value != mlw_code) {
					qmnDisplayError(error_messages.incorrect_error_text, jQuery(this), quiz_form_id);
					show_result_validation = false;
				}
				if (jQuery(this).attr('class').indexOf('mlwRequiredAccept') > -1 && !jQuery(this).prop('checked')) {
					qmnDisplayError(error_messages.empty_error_text, jQuery(this), quiz_form_id);
					show_result_validation = false;
				}
				if (jQuery(this).attr('class').indexOf('mlwRequiredRadio') > -1) {
					check_val = jQuery(this).find('input:checked').val();
					if (check_val == "" || check_val == undefined) {
						qmnDisplayError(error_messages.empty_error_text, jQuery(this), quiz_form_id);
						show_result_validation = false;
					}
				}
				if (jQuery(this).attr('class').indexOf('mlwRequiredFileUpload') > -1) {
					var selected_file = jQuery(this).get(0).files.length;
					if (selected_file === 0) {
						qmnDisplayError(error_messages.empty_error_text, jQuery(this), quiz_form_id);
						show_result_validation = false;
					}
				}
				if (jQuery(this).attr('class').indexOf('qsmRequiredSelect') > -1) {
					check_val = jQuery(this).val();
					if (check_val == "" || check_val == null) {
						qmnDisplayError(error_messages.empty_error_text, jQuery(this), quiz_form_id);
						show_result_validation = false;
					}
				}
				if (jQuery(this).attr('class').indexOf('mlwRequiredCheck') > -1) {
					if (!jQuery(this).find('input:checked').length) {
						qmnDisplayError(error_messages.empty_error_text, jQuery(this), quiz_form_id);
						show_result_validation = false;
					}
				}
				//Google recaptcha validation
				if (jQuery(this).attr('class').indexOf('g-recaptcha-response') > -1) {
					let recaptcha_id = jQuery(this).attr('id');
					let recaptcha_index = 'g-recaptcha-response' === recaptcha_id ? 0 : recaptcha_id.replace("g-recaptcha-response-", "");
					if (grecaptcha.getResponse(recaptcha_index) == "") {
						alert(error_messages.recaptcha_error_text);
						show_result_validation = false;
					}
				}
			}
		}
	});
	jQuery(document).trigger('qsm_after_validation', [element, quiz_form_id]);
	return show_result_validation;
}

function getFormData($form) {
	var unindexed_array = $form.serializeArray();
	var indexed_array = {};

	jQuery.map(unindexed_array, function (n, i) {
		indexed_array[n['name']] = n['value'];
	});

	return indexed_array;
}

function qmnFormSubmit(quiz_form_id, $this) {
	var quiz_id = +jQuery('#' + quiz_form_id).find('.qmn_quiz_id').val();
	let $container = jQuery($this).closest('.qmn_quiz_container');
	let result = qmnValidation( $container.find('*'), quiz_form_id);
	if (!result) { return result; }

	/**
	 * Update Timer in MS
	 */
	var timer_ms = jQuery('#' + quiz_form_id).find(".qsm-quiz-container-" + quiz_id + " input[name='timer_ms']").val();
	var new_timer_ms = qsmTimeInMS();
	jQuery('#' + quiz_form_id).find(".qsm-quiz-container-" + quiz_id + " input[name='timer_ms']").val(Math.abs(new_timer_ms - timer_ms));

	jQuery('.mlw_qmn_quiz input:radio').attr('disabled', false);
	jQuery('.mlw_qmn_quiz input:checkbox').attr('disabled', false);
	jQuery('.mlw_qmn_quiz select').attr('disabled', false);
	jQuery('.mlw_qmn_question_comment').attr('disabled', false);
	jQuery('.mlw_answer_open_text').attr('disabled', false);
	//Convert serialize data into index array
	var unindexed_array = jQuery('#' + quiz_form_id).serializeArray();
	unindexed_array.push(
		{
			name: 'quiz_start_date',
			value: localStorage.getItem('mlw_quiz_start_date' + quiz_id)
		}
	)
	jQuery(document).trigger('qsm_before_form_data_process', [quiz_form_id, unindexed_array]);
	var fd = new FormData();
	jQuery.each(unindexed_array, function (key, input) {
		fd.append(input.name, input.value);
	});
	fd.append("action", 'qmn_process_quiz');
	fd.append("nonce", jQuery('#qsm_nonce_' + quiz_id ).val() );
	fd.append("qsm_unique_key", jQuery('#qsm_unique_key_' + quiz_id ).val() );
	fd.append("currentuserTime", Math.round(new Date().getTime() / 1000));
	fd.append("currentuserTimeZone", Intl.DateTimeFormat().resolvedOptions().timeZone);

	qsmEndTimeTakenTimer(quiz_id);

	if (qmn_quiz_data[quiz_id].hasOwnProperty('timer_limit')) {
		QSM.endTimer(quiz_id);
	}
	jQuery(document).trigger('qsm_before_quiz_submit', [quiz_form_id]);
	jQuery('#' + quiz_form_id + ' input[type=submit]').attr('disabled', 'disabled');
	qsmDisplayLoading($container, quiz_id);
	jQuery.ajax({
		url: qmn_ajax_object.ajaxurl,
		data: fd,
		contentType: false,
		processData: false,
		type: 'POST',
		success: function (response) {
			response = JSON.parse(response);
			if (window.qsm_results_data === undefined) {
				window.qsm_results_data = new Object();
			}
			window.qsm_results_data[quiz_id] = {
				'save_response': response.result_status['save_response'],
				'id': response.result_status['id']
			};

			if (response.quizExpired) {
				MicroModal.show('modal-4');
				return false;
			} else {
				qmnDisplayResults(response, quiz_form_id, $container, quiz_id);
				// run MathJax on the new content
				if (1 != qmn_quiz_data[quiz_id].disable_mathjax) {
					MathJax.typesetPromise();
				}
				jQuery(document).trigger('qsm_after_quiz_submit_load_chart');
				jQuery(document).trigger('qsm_after_quiz_submit', [quiz_form_id]);
			}
		},
		error: function (errorThrown) {
			let response = { display: errorThrown.responseText + "<br/> Error:" + errorThrown.statusText};
			qmnDisplayResults(response, quiz_form_id, $container, quiz_id);
			console.log(errorThrown);
		}
	});

	return false;
}

jQuery(document).on('qsm_after_quiz_submit', function(e, quiz_form_id) {
	let quiz_id = quiz_form_id.replace("quizForm", "");
	if( localStorage.getItem('mlw_quiz_start_date' + quiz_id) ) {
		localStorage.removeItem('mlw_quiz_start_date' + quiz_id);
	}
})

function qsmDisplayLoading($container, quiz_id) {
	jQuery(document).trigger('qsm_before_loader_init', [$container,quiz_id]);
	var loader_html = '<div class="qsm_quiz_processing_box">';
	loader_html += '<div class="qsm-spinner-loader qsm_quiz_processing_loader"></div>';
	if (qmn_quiz_data[quiz_id].hasOwnProperty('quiz_processing_message') && qmn_quiz_data[quiz_id].quiz_processing_message != '') {
		loader_html += '<div class="qsm_quiz_processing_message">' + qmn_quiz_data[quiz_id].quiz_processing_message + '</div>';
	}
	loader_html += '</div>';
	$container.empty();
	$container.append(loader_html);
	if (qmn_quiz_data[quiz_id].hasOwnProperty('disable_scroll_next_previous_click') && qmn_quiz_data[quiz_id].disable_scroll_next_previous_click != 1) {
		qsmScrollTo($container);
	}
	jQuery(document).trigger('qsm_after_loader_init', [$container,quiz_id]);
}

function qmnDisplayResults(results, quiz_form_id, $container, quiz_id) {
	$container.empty();
	jQuery(document).trigger('qsm_before_display_result', [results, quiz_form_id, $container]);
	if (results.redirect) {
		window.location.replace(results.redirect);
	} else {
		$container.append('<div class="qmn_results_page"></div>');
		$container.find('.qmn_results_page').html(results.display);
		if (qmn_quiz_data[quiz_id].hasOwnProperty('disable_scroll_next_previous_click') && qmn_quiz_data[quiz_id].disable_scroll_next_previous_click != 1) {
			qsmScrollTo($container);
		}
		// Fires after result is populates via ajax
		jQuery(document).trigger('qsm_after_display_result', [results, quiz_form_id, $container]);
	}
}

function qmnInit() {
	if (typeof qmn_quiz_data != 'undefined' && qmn_quiz_data) {
		_.each(qmn_quiz_data, function (quiz) {
			let key = parseInt(quiz.quiz_id);
			if (qmn_quiz_data[key].ajax_show_correct === '1') {
				jQuery('.qmn-multiple-choice-input, .qsm_dropdown, .qsm-multiple-response-input').change(function () {
					let $this = jQuery(this);
					let value = $this.val();
					let quiz_form_id = $this.parents('.qsm-quiz-form').attr('id');
					let quiz_id = quiz_form_id.replace('quizForm', '');
					let question_id = $this.attr('name').replace(/question/i, '');
					let data = qsm_question_quick_result_js(question_id, value, '', qmn_quiz_data[quiz_id].enable_quick_correct_answer_info,quiz_id);
					if (data.success == 'correct') {
						$this.parent().addClass("qmn_correct_answer");
					} else if (data.success == 'incorrect') {
						$this.parent().addClass("qmn_incorrect_answer");
					}
				});
			}

			if (qmn_quiz_data[key].disable_answer === '1') {

				jQuery('#quizForm' + qmn_quiz_data[key].quiz_id + ' .qmn_quiz_radio').change(function () {
					var radio_group = jQuery(this).attr('name');
					jQuery('input[type=radio][name=' + radio_group + ']').prop('disabled', true);
					let radio_value = jQuery(this).val();
					let disableAnswer = {};
					if (localStorage.getItem("disable_answer")) {
						disableAnswer = JSON.parse(localStorage.getItem("disable_answer"));
					}
					if (!disableAnswer[key]) {
						disableAnswer[key] = [];
					}
					let disabledQuestions = disableAnswer[key].map(element => element[0]);
					if (!disabledQuestions.includes(radio_group)) {
						disableAnswer[key].push([radio_group, radio_value]);
					}
					localStorage.setItem("disable_answer", JSON.stringify(disableAnswer));
				});

				if (localStorage.getItem("disable_answer")) {
					let disabledAnswer = JSON.parse(localStorage.getItem("disable_answer"));
					if (disabledAnswer[key]) {
						disabledAnswer[key].forEach(element => {
							let element1 = element[1].replaceAll(' ', '-');
							jQuery('#' + element[0] + '-' + element1 + ' input').prop('checked', true).trigger('change');
						});
					}
				}
				jQuery(document).on('qsm_after_quiz_submit', function (event, quiz_form_id) {
					event.preventDefault();
					if (localStorage.getItem("disable_answer")) {
						let disabledAnswer2 = JSON.parse(localStorage.getItem("disable_answer"));
						if (disabledAnswer2[key]) {
							delete disabledAnswer2[key];
							localStorage.setItem("disable_answer", JSON.stringify(disabledAnswer2));
						}
					}
				});
			}

			if (qmn_quiz_data[key].hasOwnProperty('pagination')) {
				qmnInitPagination(qmn_quiz_data[key].quiz_id);
			}
		});
	}
}

//Function to validate the answers provided in quiz
function qmnValidatePage(quiz_form_id) {
	var result = qmnValidation('#' + quiz_form_id + ' .quiz_section:visible *', quiz_form_id);
	return result;
}

// Show start quiz button if first page is visible
function check_if_show_start_quiz_button(container, total_pages, page_number) {
	if(container.find('.quiz_begin').is(':visible')){
		container.find(".mlw_custom_start").show();
		container.find(".mlw_custom_next").hide();
	}else{
		container.find(".mlw_custom_start").hide();
		let numberToAdd = 2;
		// Fixed Missing Next Button in single question quiz created with text after quiz
		if ( '3' == total_pages && 0 < jQuery('.quiz_end .mlw_qmn_message_end').length ) {
			numberToAdd = 1;
		}
		if(total_pages != parseInt(page_number) + numberToAdd){ // check if not last page based on condition (1140)
			container.find(".mlw_custom_next").show();
			if (jQuery('.quiz_end').is(':visible')) {
				container.find(".mlw_custom_next").hide();
			}
		}
	}
}

//Function to advance quiz to next page
function qmnNextSlide(pagination, go_to_top, quiz_form_id) {
	jQuery(document).trigger('qsm_next_button_click_before', [quiz_form_id]);
	var quiz_id = +jQuery(quiz_form_id).find('.qmn_quiz_id').val();
	var $container = jQuery(quiz_form_id).closest('.qmn_quiz_container');
	var slide_number = +$container.find('.slide_number_hidden').val();
	var page_number = +$container.find('.current_page_hidden').val();
	var section_totals = +$container.find('.total_sections_hidden').val();
	if (pagination == 1) {
		section_totals = section_totals - 1;
	}
	var total_pages = $container.find('.total_pages_hidden').val();

	jQuery(quiz_form_id + " .qsm-auto-page-row").hide();
	if (page_number >= total_pages) {
		alert('Next page not found.');
		$container.find(".mlw_next").hide();
		$container.find(".qsm-submit-btn").show();
		jQuery(quiz_form_id + " .qsm-auto-page-row.quiz_end").show();
		return true;
	}
	if (slide_number == 0 && page_number == 0) {
		jQuery(quiz_form_id + " .qsm-auto-page-row.quiz_begin").show();
		$container.find(".mlw_previous").hide();
		$container.find('.current_page_hidden').val('1');
	} else if (total_pages == parseInt(page_number) + 1) { //Last page
		$container.find(".mlw_next").hide();
		$container.find(".qsm-submit-btn").show();
		jQuery(quiz_form_id + " .qsm-auto-page-row.quiz_end").show();
		$container.find('.slide_number_hidden').val(parseInt(slide_number) + 1);
		$container.find('.current_page_hidden').val(parseInt(page_number) + 1);
		$container.find(".mlw_previous").show();
		$container.find('.g-recaptcha').show();
	} else if (slide_number >= 0 && page_number >= 1) {
		if (total_pages == parseInt(page_number) + 2) { // if last page empty
			if (jQuery(quiz_form_id + " .qsm-auto-page-row.empty_quiz_end").length) {
				jQuery(quiz_form_id + " .qsm-auto-page-row.empty_quiz_end").show();
				$container.find(".qsm-submit-btn").show();
				$container.find(".mlw_next").hide();
				$container.find('.g-recaptcha').show();
			}
			if (qmn_quiz_data[quiz_id].contact_info_location == 1) {
				$container.find(".qsm-submit-btn").hide();
				$container.find(".mlw_next").show();
			}
		}
		$container.find('.qsm-auto-page-row.qsm-apc-' + page_number).show();
		$container.find('.slide_number_hidden').val(parseInt(slide_number) + 1);
		$container.find('.current_page_hidden').val(parseInt(page_number) + 1);
		$container.find(".mlw_previous").show();
	}
	check_if_show_start_quiz_button($container, total_pages, page_number);
	if (jQuery(quiz_form_id + " .quiz_section.quiz_end").is(':visible')) {
		var button_width = jQuery(quiz_form_id + ' .qsm-submit-btn').width();
		var progress_width = jQuery(quiz_form_id).parent().find('.qmn_pagination').width();
		jQuery(quiz_form_id).parent().find('.qmn_pagination').css('width', progress_width - button_width - 40);
	} else {
		jQuery(quiz_form_id).parent().find('.qmn_pagination').css('width', '100%');
	}
	if (go_to_top == 1 && qmn_quiz_data[quiz_id].disable_scroll_next_previous_click != 1) {
		qsmScrollTo($container);
	}
	if (!qmn_quiz_data[quiz_id].hasOwnProperty('first_page') || !qmn_quiz_data[quiz_id].first_page) {
		if (slide_number == 0 && page_number == 0) {
			$container.find(".qsm-auto-page-row.quiz_begin").hide();
			$container.find('.qsm-auto-page-row.qsm-apc-' + (parseInt(page_number) + 1)).show();
			$container.find('.slide_number_hidden').val(parseInt(slide_number) + 1);
			$container.find('.current_page_hidden').val(parseInt(page_number) + 2);
			$container.find(".mlw_previous").hide();
		}
	}
	//Show the page counts
	if (page_number > 0 && jQuery(quiz_form_id).closest('.qmn_quiz_container').find('.pages_count').length > 0) {
		var actual_pages = total_pages - 2;
		if (page_number <= actual_pages) {
			jQuery(quiz_form_id).closest('.qmn_quiz_container').find('.pages_count').text('').text(page_number + qmn_ajax_object.out_of_text + actual_pages);
			jQuery(quiz_form_id).closest('.qmn_quiz_container').find('.pages_count').show();
		} else {
			jQuery(quiz_form_id).closest('.qmn_quiz_container').find('.pages_count').hide();
		}
	} else {
		jQuery(quiz_form_id).closest('.qmn_quiz_container').find('.pages_count').hide();
	}
	qmnInitProgressbarOnClick(quiz_id, page_number, total_pages);
	jQuery(document).trigger('qsm_auto_next_button_click_after', [quiz_form_id]);
}

function qmnPrevSlide(pagination, go_to_top, quiz_form_id) {
	jQuery(document).trigger('qsm_previous_button_click_before', [quiz_form_id]);
	var quiz_id = +jQuery(quiz_form_id).find('.qmn_quiz_id').val();
	var $container = jQuery(quiz_form_id).closest('.qmn_quiz_container');
	var slide_number = +$container.find('.slide_number_hidden').val();
	var previous = +$container.find('.previous_amount_hidden').val();
	var section_totals = +$container.find('.total_sections_hidden').val();
	var page_number = +$container.find('.current_page_hidden').val();
	var total_pages = $container.find('.total_pages_hidden').val();
	jQuery(quiz_form_id + " .qsm-auto-page-row").hide();
	jQuery(quiz_form_id + " .g-recaptcha").hide();
	var slide_original_val = parseInt(slide_number) - 1;
	if (slide_original_val == 0) {
		$container.find(".mlw_next").show();
		$container.find(".qsm-submit-btn").hide();
		jQuery(quiz_form_id + " .qsm-auto-page-row.quiz_begin").show();
		$container.find('.slide_number_hidden').val(slide_original_val);
		$container.find('.current_page_hidden').val(parseInt(page_number) - 1);
		$container.find(".mlw_previous").hide();
	} else {
		$container.find('.qsm-auto-page-row.qsm-apc-' + slide_original_val).show();
		$container.find('.slide_number_hidden').val(slide_original_val);
		$container.find('.current_page_hidden').val(parseInt(page_number) - 1);
		$container.find(".mlw_next").show();
		$container.find(".qsm-submit-btn").hide();
	}
	check_if_show_start_quiz_button($container, total_pages, page_number);
	if (go_to_top == 1 && qmn_quiz_data[quiz_id].disable_scroll_next_previous_click != 1) {
		qsmScrollTo($container);
	}
	if (qmn_quiz_data[quiz_id].disable_first_page == 1) {
		if (page_number == 3) {
			$container.find(".qsm-auto-page-row.quiz_begin").hide();
			$container.find(".mlw_previous").hide();
		}
	}
	if (page_number > 0 && jQuery(quiz_form_id).closest('.qmn_quiz_container').find('.pages_count').length > 0) {
		var actual_pages = total_pages - 2;
		if (slide_original_val <= actual_pages) {
			jQuery(quiz_form_id).closest('.qmn_quiz_container').find('.pages_count').text('').text(slide_original_val + qmn_ajax_object.out_of_text + actual_pages);
			if (slide_original_val == 0) {
				jQuery(quiz_form_id).closest('.qmn_quiz_container').find('.pages_count').hide();
			} else {
				jQuery(quiz_form_id).closest('.qmn_quiz_container').find('.pages_count').show();
			}
		} else {
			jQuery(quiz_form_id).closest('.qmn_quiz_container').find('.pages_count').hide();
		}
	}
	jQuery(quiz_form_id).parent().find('.qmn_pagination').css('width', '100%');
	qmnInitProgressbarOnClick(quiz_id, slide_original_val, total_pages);
	jQuery(document).trigger('qsm_auto_previous_button_click_after', [quiz_form_id]);
}

/**
 * @since 6.4.11
 * @param {int} quiz_id
 * @param {int} page_number
 * @param {int} total_page_number
 * @returns Change progress bar on next and previous button click
 */
function qmnInitProgressbarOnClick(quiz_id, page_number, total_page_number) {
	if ('1' == qmn_quiz_data[quiz_id].progress_bar) {
		if ( ( !qmn_quiz_data[quiz_id].hasOwnProperty('first_page') || !qmn_quiz_data[quiz_id].first_page ) && 0 == page_number ) {
			page_number++;
		}
		var qmn_total_questions = qmn_quiz_data[quiz_id].pagination.total_questions;
		var pagination = qmn_quiz_data[quiz_id].pagination.amount;
		total_page_number = Math.ceil(qmn_total_questions / pagination);
		if (!jQuery('#quizForm' + quiz_id).closest('.qmn_quiz_container').find('.empty_quiz_end').length) {
			total_page_number = total_page_number + 1; //Increase for quiz end section
		}
		var animate_value = page_number / total_page_number;
		if (animate_value <= 1) {
			qmn_quiz_data[quiz_id].bar.animate(animate_value);
			var old_text = jQuery('#qsm_progress_bar_' + quiz_id).find('.progressbar-text').text().replace(' %', '');
			var new_text = Math.round(animate_value * 100);
			jQuery({
				Counter: old_text
			}).animate({
				Counter: new_text
			}, {
				duration: 500,
				easing: 'swing',
				step: function () {
					jQuery('#qsm_progress_bar_' + quiz_id).find('.progressbar-text').text(Math.round(this.Counter) + ' %');
				}
			});
		}
	}
}

function qmnUpdatePageNumber(amount, quiz_form_id) {
	var current_page = +jQuery(quiz_form_id).closest('.qmn_quiz_container').find('.current_page_hidden').val();
	var total_pages = jQuery(quiz_form_id).closest('.qmn_quiz_container').find('.total_pages_hidden').val();
	current_page += amount;
	//jQuery( quiz_form_id ).siblings( '.qmn_pagination' ).find( " .qmn_page_counter_message" ).text( current_page + "/" + total_pages );
}

function qmnInitPagination(quiz_id) {
	var qmn_section_total = +qmn_quiz_data[quiz_id].pagination.total_questions;
	var qmn_total_questions = jQuery('#quizForm' + quiz_id).find('#qmn_all_questions_count').val();
	var qmn_total_pages = Math.ceil(qmn_total_questions / +qmn_quiz_data[quiz_id].pagination.amount);

	qmn_total_pages = qmn_total_pages + 1; //quiz begin
	qmn_total_pages = qmn_total_pages + 1; //quiz end


	jQuery('#quizForm' + quiz_id).closest('.qmn_quiz_container').append('<div class="qmn_pagination border margin-bottom"></div>');
	jQuery('#quizForm' + quiz_id).closest('.qmn_quiz_container').find('.qmn_pagination').append('<input type="hidden" value="0" name="slide_number" class="slide_number_hidden" />')
		.append('<input type="hidden" value="0" name="current_page" class="current_page_hidden" />')
		.append('<input type="hidden" value="' + qmn_total_pages + '" name="total_pages" class="total_pages_hidden" />')
		.append('<input type="hidden" value="' + qmn_section_total + '" name="total_sections" class="total_sections_hidden" />')
		.append('<input type="hidden" value="0" name="previous_amount" class="previous_amount_hidden" />')
		.append('<a class="qmn_btn mlw_qmn_quiz_link mlw_previous" href="javascript:void(0)">' + qmn_quiz_data[quiz_id].pagination.previous_text + '</a>')
		.append('<span class="qmn_page_message"></span>')
		.append('<div class="qmn_page_counter_message"></div>')
		.append('<div class="qsm-progress-bar" id="qsm_progress_bar_' + quiz_id + '" style="display:none;"><div class="progressbar-text"></div></div>')
		.append('<a class="qmn_btn mlw_qmn_quiz_link mlw_next mlw_custom_start" href="javascript:void(0)">' + qmn_quiz_data[quiz_id].pagination.start_quiz_survey_text + '</a>')
		.append('<input type="submit" value="' + qmn_quiz_data[quiz_id].pagination.submit_quiz_text + '" class="qsm-btn qsm-submit-btn qmn_btn" style="display:none;"/>')
		.append('<a class="qmn_btn mlw_qmn_quiz_link mlw_next mlw_custom_next" href="javascript:void(0)">' + qmn_quiz_data[quiz_id].pagination.next_text + '</a>');

	if ('1' == qmn_quiz_data[quiz_id].progress_bar) {
		jQuery(document).trigger('qsm_init_progressbar_before', [quiz_id, qmn_quiz_data]);
		jQuery('#quizForm' + quiz_id).closest('.qmn_quiz_container').find('.qsm-progress-bar').show();
		qmn_quiz_data[quiz_id].bar = createQSMProgressBar(quiz_id, '#qsm_progress_bar_' + quiz_id);
		jQuery(document).trigger('qsm_init_progressbar_after', [quiz_id, qmn_quiz_data]);
	}

	jQuery(document).on("click", ".qsm-quiz-container-" + quiz_id + " .mlw_next", function (event) {
		let quiz_id = +jQuery(this).closest('.qmn_quiz_container').find('.qmn_quiz_id').val();
		jQuery(document).trigger('qsm_auto_next_button_click_before', [quiz_id]);
		event.preventDefault();
		let $quizForm = QSM.getQuizForm(quiz_id);
		jQuery('.qsm-quiz-container-' + quiz_id + ' .mlw_custom_next').addClass('qsm-disabled-btn');
		jQuery('.qsm-quiz-container-' + quiz_id + ' .mlw_custom_next').append('<div class="qsm-spinner-loader" style="font-size: 3.5px;margin-right: -5px;margin-left: 10px;"></div>');

		jQuery('.qsm-multiple-response-input:checked, .qmn-multiple-choice-input:checked , .qsm_select:visible').each(function () {
			if (qmn_quiz_data[quiz_id].end_quiz_if_wrong > 0 && jQuery(this).parents().is(':visible') && jQuery(this).is('input, select')) {
				if (jQuery(this).parents('.qmn_radio_answers, .qsm_check_answer')) {
					let question_id = jQuery(this).attr('name').split('question')[1],
					value = jQuery(this).val(),
					$this = jQuery(this).parents('.quiz_section');
					if (value !== "") {
						qsm_submit_quiz_if_answer_wrong(question_id, value, $this, $quizForm);
					}
				}
			}
		});

		jQuery('.qsm-quiz-container-' + quiz_id + ' .mlw_custom_next').removeClass('qsm-disabled-btn');
		jQuery('.qsm-quiz-container-' + quiz_id + ' .qsm-spinner-loader').remove();
		if (qmnValidatePage('quizForm' + quiz_id)) {
			qmnNextSlide(qmn_quiz_data[quiz_id].pagination.amount, 1, '#quizForm' + quiz_id);
		}

		jQuery(document).trigger('qsm_next_button_click_after', [quiz_id]);
	});

	jQuery(document).on("click", ".qsm-quiz-container-" + quiz_id + " .mlw_previous", function (event) {
		event.preventDefault();
		var quiz_id = +jQuery(this).closest('.qmn_quiz_container').find('.qmn_quiz_id').val();
		qmnPrevSlide(qmn_quiz_data[quiz_id].pagination.amount, 1, '#quizForm' + quiz_id);
		jQuery(document).trigger('qsm_previous_button_click_after', [quiz_id]);
	});

	if (qmn_quiz_data[quiz_id].first_page) {
		qmnNextSlide(1, 0, '#quizForm' + quiz_id);
	} else {
		qmnNextSlide(qmn_quiz_data[quiz_id].pagination.amount, 0, '#quizForm' + quiz_id);
	}

	jQuery(document).trigger('qsm_init_pagination_after', [quiz_id, qmn_quiz_data]);
}
jQuery(document).on('qsm_next_button_click_after qsm_previous_button_click_after', function(event, quiz_id) {
	jQuery(document).trigger('qsm_before_iframe_section',[quiz_id]);
	let video_sections = jQuery('.qsm-quiz-container-' + quiz_id + '.qmn_quiz_container').find('video');
	let iframeVideos = jQuery('.qsm-quiz-container-' + quiz_id + '.qmn_quiz_container .qsm-page, .qsm-quiz-container-' + quiz_id + '.qmn_quiz_container .qsm-auto-page-row').find('iframe');
	let audio_sections = jQuery('.qsm-quiz-container-' + quiz_id + '.qmn_quiz_container').find('audio');
	iframeVideos.each(function() {
		let src = this.src;
		jQuery(this).attr('src', src);
	});
	video_sections.each(function() {
		if (!this.paused) {
			this.pause();
		}
	});
	audio_sections.each(function() {
		if (!this.paused) {
			this.pause();
		}
	});
	jQuery(document).trigger('qsm_after_iframe_section',[quiz_id]);
});
function qmnSocialShare(network, mlw_qmn_social_text, mlw_qmn_title, facebook_id, share_url) {
	var sTop = window.screen.height / 2 - (218);
	var sLeft = window.screen.width / 2 - (313);
	var sqShareOptions = "height=400,width=580,toolbar=0,status=0,location=0,menubar=0,directories=0,scrollbars=0,top=" + sTop + ",left=" + sLeft;
	var pageUrl = window.location.href;
	var pageUrlEncoded = encodeURIComponent(share_url);
	var url = '';
	if (network == 'facebook') {
		url = "https://www.facebook.com/dialog/feed?" + "display=popup&" + "app_id=" + facebook_id +
			"&" + "link=" + pageUrlEncoded + "&" + "name=" + encodeURIComponent(mlw_qmn_social_text) +
			"&" + "description=";
	}
	if (network == 'twitter') {
		url = "https://twitter.com/intent/tweet?text=" + encodeURIComponent(mlw_qmn_social_text);
	}
	window.open(url, "Share", sqShareOptions);
	return false;
}

function maxLengthCheck(object) {
	if (object.value.length > object.maxLength) {
		object.value = object.value.slice(0, object.maxLength)
	}
}

jQuery(function () {
	jQuery('.qmn_quiz_container').tooltip({
		position: {
		  my: "center top+10",
		  at: "center bottom",
		  classes: {
			"ui-tooltip": "hint-qsm-tooltip"
		  },
		  using: function( position, feedback ) {
			jQuery( this ).css( position );
			jQuery( "<div>" )
			  .addClass( "qsm-tooltip-arrow" )
			  .addClass( feedback.vertical )
			  .addClass( feedback.horizontal )
			  .appendTo( this );
		  }
		}
	  });

	jQuery('.qmn_quiz_container input').on('keypress', function (e) {
		if (e.which === 13) {
			e.preventDefault();
		}
	});

	jQuery(document).on('click', ".qsm-submit-btn", function (event) {
		event.preventDefault();
		let $this = jQuery(this);
		let quiz_id = +jQuery(this).closest('.qmn_quiz_container').find('.qmn_quiz_id').val();
		let form_id = "quizForm"+quiz_id;
		jQuery(document).trigger('qsm_quiz_submit_trigger', [quiz_id]);
		let recaptcha = jQuery('#' + form_id).find("#qsm_grecaptcha_v3");
		if (!recaptcha.length) {
			qmnFormSubmit(form_id, $this);
			return false;
		}

		// Proceed reCaptcha v3
		let site_key = jQuery('#' + form_id).find("#qsm_grecaptcha_v3_sitekey").val();
		let submit_action = jQuery('#' + form_id).find("#qsm_grecaptcha_v3_nonce").val();
		grecaptcha.ready(function () {
			grecaptcha.execute(site_key, { action: submit_action }).then(function (token) {
				jQuery('#' + form_id).find("#qsm_grecaptcha_v3_response").val(token);
				qmnFormSubmit(form_id, $this);
			});
		});
	});

	jQuery(document).on('click', '.btn-reload-quiz', function (e) {
		e.preventDefault();
		var quiz_id = jQuery(this).data('quiz_id');
		var parent_div = jQuery(this).parents('.qsm-quiz-container');
		qsmDisplayLoading(parent_div, quiz_id);
		jQuery.ajax({
			type: 'POST',
			url: qmn_ajax_object.ajaxurl,
			data: {
				action: "qsm_get_quiz_to_reload",
				quiz_id: quiz_id,
			},
			success: function (response) {
				parent_div.replaceWith(response);
				//Reload the timer and pagination
				qmnDoInit();

				if (1 != qmn_quiz_data[quiz_id].disable_mathjax) {
					MathJax.typesetPromise();
				}

				// trigger fired on successfull retake quiz
				jQuery(document).trigger('qsm_retake_quiz', [quiz_id]);
			},
			error: function (errorThrown) {
				console.log('error');
			}
		});
	});

	jQuery(document).on('change', '.qmn-multiple-choice-input, .qsm_dropdown, .mlw_answer_date ' , function (e) {
		let $i_this = jQuery(this);
		var quizID = jQuery(this).parents('.qsm-quiz-container').find('.qmn_quiz_id').val();
		var $quizForm = QSM.getQuizForm(quizID);
		let value = jQuery(this).val();
		let $this = jQuery(this).parents('.quiz_section');
		let question_id = $i_this.attr('name').split('question')[1];
		if (qmn_quiz_data[quizID].enable_quick_result_mc == 1) {
			qsm_show_inline_result(quizID, question_id, value, $this, 'radio', $i_this)
		} else if (qmn_quiz_data[quizID].enable_quick_correct_answer_info != 0) {
			let data = qsm_question_quick_result_js(question_id, value, 'radio', qmn_quiz_data[quizID].enable_quick_correct_answer_info, quizID);
			$this.find('.quick-question-res-p, .qsm-inline-correct-info').remove();
			if (0 < value.length && data.success != '') {
				$this.append('<div class="qsm-inline-correct-info">' + qsm_check_shortcode(data.message) + '</div>');
			}
		}
		jQuery(document).trigger('qsm_after_select_answer', [quizID, question_id, value, $this, 'radio']);
		if (qmn_quiz_data[quizID].end_quiz_if_wrong > 0 && !jQuery(this).parents('.qsm-quiz-container').find('.mlw_next:visible').length ) {
			qsm_submit_quiz_if_answer_wrong(question_id, value, $this, $quizForm);
		}
	});
	let qsm_inline_result_timer;
	jQuery(document).on('keyup', '.mlw_answer_open_text, .mlw_answer_number, .qmn_fill_blank ', function (e) {
		let $i_this = jQuery(this);
		let quizID = jQuery(this).parents('.qsm-quiz-container').find('.qmn_quiz_id').val();
		let question_id = $i_this.attr('name').split('question')[1];
		let value = $i_this.val();
		let $this = $i_this.parents('.quiz_section');
		let sendValue;
		if (typeof value === 'string') {
			sendValue = value.trim();
		} else if (value.length) {
			sendValue = value[value.length - 1];
		} else {
			sendValue = '';
		}
		clearTimeout(qsm_inline_result_timer);
		qsm_inline_result_timer = setTimeout(() => {
			if (qmn_quiz_data[quizID].enable_quick_result_mc == 1) {
				qsm_show_inline_result(quizID, question_id, sendValue, $this, 'input', $i_this, $this.find('.qmn_fill_blank').index($i_this));
			} else if (qmn_quiz_data[quizID].enable_quick_correct_answer_info != 0) {
				let data = qsm_question_quick_result_js(question_id, sendValue, 'input', qmn_quiz_data[quizID].enable_quick_correct_answer_info, quizID);
				$this.find('.quick-question-res-p, .qsm-inline-correct-info').remove();
				if (0 < value.length && data.success != '') {
					$this.append('<div class="qsm-inline-correct-info">' + qsm_check_shortcode(data.message) + '</div>');
				}
			}
			jQuery(document).trigger('qsm_after_select_answer', [quizID, question_id, value, $this, 'input', $this.find('.qmn_fill_blank').index($i_this)]);
		}, 2000);
	});

	//inline result status function

	// Autocomplete off
	jQuery('.qsm-quiz-container').find('.qmn_quiz_id').each(function () {
		var quizID = jQuery(this).val();
		if (qmn_quiz_data[quizID].form_disable_autofill == 1) {
			jQuery('#quizForm' + quizID).attr('autocomplete', 'off');
		}
	});

	jQuery(document).on('change ', '.qsm-multiple-response-input', function (e) {
		let $i_this = jQuery(this);
		let quizID = jQuery(this).parents('.qsm-quiz-container').find('.qmn_quiz_id').val();
		let $quizForm = QSM.getQuizForm(quizID);
		let question_id = jQuery(this).attr('name').split('question')[1],
		$this = jQuery(this).parents('.quiz_section');
		let parent = jQuery(this).closest('.qmn_check_answers');
		let checkedValues = parent.find('input[type="checkbox"]:checked').map(function() {
			return jQuery(this).val();
		}).get();
		if (qmn_quiz_data[quizID].end_quiz_if_wrong > 0 && !jQuery(this).parents('.qsm-quiz-container').find('.mlw_next:visible').length ) {
			qsm_submit_quiz_if_answer_wrong(question_id, checkedValues, $this, $quizForm, 'checkbox');
		}
		if (qmn_quiz_data[quizID].enable_quick_result_mc == 1) {
			qsm_show_inline_result(quizID, question_id, checkedValues, $this, 'checkbox', $i_this)
		}else if (qmn_quiz_data[quizID].enable_quick_correct_answer_info != 0) {
			let data = qsm_question_quick_result_js(question_id, checkedValues, 'checkbox', qmn_quiz_data[quizID].enable_quick_correct_answer_info,quizID);
			$this.find('.quick-question-res-p, .qsm-inline-correct-info').remove();
			if ( 0 < checkedValues.length && data.success != '') {
				$this.append('<div class="qsm-inline-correct-info">' + qsm_check_shortcode(data.message) + '</div>');
			}
		}
		jQuery(document).trigger('qsm_after_select_answer', [quizID, question_id, checkedValues, $this, 'checkbox']);
	});

	//Ajax upload file code
	jQuery('.quiz_section .mlw_answer_file_upload').on('change', function () {
		var $this = jQuery(this);
		var file_data = jQuery(this).prop('files')[0];
		var form_data = new FormData();
		form_data.append('file', file_data);
		form_data.append('action', 'qsm_upload_image_fd_question');
		var question_id = $this.parent('.quiz_section').find('.mlw_file_upload_media_id').attr("name").replace('question', '');
		form_data.append('question_id', question_id);
		$this.next('.loading-uploaded-file').show();
		$this.parent('.quiz_section').find('.qsm-file-upload-status').removeClass('qsm-error qsm-success');
		$this.parent('.quiz_section').find('.qsm-file-upload-status').addClass('qsm-processing');
		$this.parent('.quiz_section').find('.qsm-file-upload-status').html('Uploading...').show();
		$this.parent('.quiz_section').find('.qsm-file-upload-name').html(jQuery(this)[0].files[0].name).show();
		jQuery(".qsm-submit-btn, .mlw_custom_next").attr('disabled', true);
		jQuery.ajax({
			url: qmn_ajax_object.ajaxurl,
			type: 'POST',
			data: form_data,
			cache: false,
			contentType: false,
			processData: false,
			success: function (response) {
				var obj = jQuery.parseJSON(response);
				$this.next('.loading-uploaded-file').hide();
				jQuery(".qsm-submit-btn, .mlw_custom_next").attr('disabled', false);
				if (obj.type == 'success') {
					$this.next().next().next('.remove-uploaded-file').show();
					$this.parent('.quiz_section').find('.mlw_file_upload_hidden_nonce').val(obj.wp_nonoce);
					$this.parent('.quiz_section').find('.mlw_file_upload_hidden_path').val(obj.file_path);
					$this.parent('.quiz_section').find('.mlw_file_upload_media_id').val(obj.media_id);
					$this.parent('.quiz_section').find('.qsm-file-upload-status').hide();
					$this.parent('.quiz_section').find('.qsm-file-upload-status').removeClass('qsm-processing qsm-error');
					$this.parent('.quiz_section').find('.qsm-file-upload-status').addClass('qsm-success').text(obj.message);
					$this.parent('.quiz_section').find('.qsm-file-upload-status').show();
				} else {
					$this.parent('.quiz_section').find('.qsm-file-upload-status').removeClass('qsm-processing qsm-success');
					$this.parent('.quiz_section').find('.qsm-file-upload-status').addClass('qsm-error').text('').text(obj.message);
					$this.parent('.quiz_section').find('.qsm-file-upload-status').show();
					$this.parent('.quiz_section').find('.mlw_answer_file_upload').val('');
				}
				// triggers after file uploads
				jQuery(document).trigger('qsm_after_file_upload', [$this.parent(), obj]);
			}
		});
		return false;
	});

	//Ajax remove file code
	jQuery(document).on('click ', '.quiz_section .remove-uploaded-file', function () {
		let $this = jQuery(this);
		let media_id = jQuery(this).parent('.quiz_section').find('.mlw_file_upload_media_id').val();
		let nonce = jQuery(this).parent('.quiz_section').find('.mlw_file_upload_hidden_nonce').val();
		let form_data = new FormData();
		form_data.append('action', 'qsm_remove_file_fd_question');
		form_data.append('media_id', media_id);
		form_data.append('nonce', nonce);
		$this.parent('.quiz_section').find('.qsm-file-upload-status').removeClass('qsm-processing qsm-success');
		$this.parent('.quiz_section').find('.qsm-file-upload-status').addClass('qsm-error');
		$this.parent('.quiz_section').find('.qsm-file-upload-status').html('Removing...').show();
		$this.parent('.quiz_section').find('.qsm-file-upload-name').html('').show();
		jQuery.ajax({
			url: qmn_ajax_object.ajaxurl,
			type: 'POST',
			data: form_data,
			cache: false,
			contentType: false,
			processData: false,
			success: function (response) {
				let obj = jQuery.parseJSON(response);
				if (obj.type == 'success') {
					$this.hide();
					$this.parent('.quiz_section').find('.mlw_file_upload_hidden_path').val('');
					$this.parent('.quiz_section').find('.mlw_file_upload_hidden_nonce').val('');
					$this.parent('.quiz_section').find('.mlw_file_upload_media_id').val('');
					$this.parent('.quiz_section').find('.mlw_answer_file_upload').val('');
					$this.parent('.quiz_section').find('.qsm-file-upload-status').text(obj.message);
				} else {
					$this.parent('.quiz_section').find('.qsm-file-upload-status').text('').text(obj.message);
					$this.parent('.quiz_section').find('.qsm-file-upload-status').show();
				}
				// triggers after file remove
				jQuery(document).trigger('qsm_after_file_remove', [$this.parent(), obj]);
			}
		});
		return false;
	});
	jQuery('.quiz_section .qsm-file-upload-container').on('click', function (e) {
		e.preventDefault();
		jQuery(this).prev('.mlw_answer_file_upload').trigger('click');
	});
	jQuery('.quiz_section .qsm-file-upload-container').on(
		'dragover',
		function (e) {
			e.preventDefault();
			e.stopPropagation();
			jQuery(this).addClass('file-hover');
		}
	)
	jQuery('.quiz_section .qsm-file-upload-container').on(
		'dragenter',
		function (e) {
			e.preventDefault();
			e.stopPropagation();
		}
	)
	jQuery('.quiz_section .qsm-file-upload-container').on(
		'dragleave',
		function (e) {
			e.preventDefault();
			e.stopPropagation();
			jQuery(this).removeClass('file-hover');
		}
	)
	jQuery('.quiz_section .qsm-file-upload-container').on(
		'drop',
		function (e) {
			jQuery(this).removeClass('file-hover');
			jQuery(this).find('.qsm-file-upload-name').html(e.originalEvent.dataTransfer.files[0].name).fadeIn();
			if (e.originalEvent.dataTransfer) {
				if (e.originalEvent.dataTransfer.files.length) {
					e.preventDefault();
					e.stopPropagation();
					jQuery(this).prev('.mlw_answer_file_upload').prop('files', e.originalEvent.dataTransfer.files);
					jQuery(this).prev('.mlw_answer_file_upload').trigger('change');
				}
			}
		}
	);
	jQuery('.quiz_section .qsm-file-upload-container').on('mouseleave', function () {
		jQuery(this).removeClass('file-hover');
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
			QSM.nextPage(quiz_id);
			qsmScrollTo($container);
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
		var sTop = window.screen.height / 2 - (218);
		var sLeft = window.screen.width / 2 - (313);
		var sqShareOptions = "height=400,width=580,toolbar=0,status=0,location=0,menubar=0,directories=0,scrollbars=0,top=" + sTop + ",left=" + sLeft;
		window.open(url, "Share", sqShareOptions);
		return false;
	});
});

const videoAttributePatterns = [
	/\ssrc="([^"]+)"/,
	/\smp4="([^"]+)"/,
	/\sm4v="([^"]+)"/,
	/\swebm="([^"]+)"/,
	/\sogv="([^"]+)"/,
	/\swmv="([^"]+)"/,
	/\sflv="([^"]+)"/,
	/\swidth="(\d+)"/,
	/\sheight="(\d+)"/
];

function parseAttributes(match, src, width, height) {
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
}

function generateVideoTag(src, width, height, content) {
	return `<video src="${src}" width="${width}" height="${height}" controls>${content}</video>`;
}

function qsm_check_shortcode(message = null) {
	const videoContentRegex = /\[video(?:\s(?:src|mp4|m4v|webm|ogv|wmv|flv|width|height)="[^"]*")*\](.*?)\[\/video\]/g;
	let videoMatch = message.match(videoContentRegex);

	if (videoMatch) {
		let videoHTML = message.replace(videoContentRegex, function(match, content) {
			const { src, width, height } = parseAttributes(match);
			const videoTag = generateVideoTag(src, width, height, content);
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
}

function qsm_show_inline_result(quizID, question_id, value, $this, answer_type, $i_this, index = null) {
	jQuery('.qsm-spinner-loader').remove();
	addSpinnerLoader($this,$i_this);
	let data = qsm_question_quick_result_js(question_id, value, answer_type, qmn_quiz_data[quizID].enable_quick_correct_answer_info,quizID);
	$this.find('.quick-question-res-p, .qsm-inline-correct-info').remove();
	$this.find('.qmn_radio_answers').children().removeClass('data-correct-answer');
	if ( 0 < value.length && data.success == 'correct') {
		$this.append('<div style="color: green" class="quick-question-res-p qsm-correct-answer-info">' + qmn_quiz_data[quizID].quick_result_correct_answer_text + '</div>')
		$this.append('<div class="qsm-inline-correct-info">' + qsm_check_shortcode(data.message) + '</div>');
	} else if (0 < value.length && data.success == 'incorrect') {
		$this.find('.qmn_radio_answers input[value="' + data.correct_index + '"]').parent().addClass('data-correct-answer');
		$this.append('<div style="color: red" class="quick-question-res-p qsm-incorrect-answer-info">' + qmn_quiz_data[quizID].quick_result_wrong_answer_text + '</div>')
		$this.append('<div class="qsm-inline-correct-info">' + qsm_check_shortcode(data.message) + '</div>');
	}
	if (1 != qmn_quiz_data[quizID].disable_mathjax) {
		MathJax.typesetPromise();
	}
	jQuery('.qsm-spinner-loader').remove();
	jQuery(document).trigger('qsm_show_inline_result_after', [quizID, question_id, value, $this, answer_type, $i_this, index]);
}
function addSpinnerLoader($this,$i_this) {
	if ($this.find('.mlw_answer_open_text').length) {
		$this.find('.mlw_answer_open_text').after('<div class="qsm-spinner-loader" style="font-size: 2.5px;margin-left:10px;"></div>');
	  } else if ($this.find('.mlw_answer_number').length) {
		$this.find('.mlw_answer_number').after('<div class="qsm-spinner-loader" style="font-size: 2.5px;margin-left:10px;"></div>');
	  } else {
		$i_this.next('.qsm-input-label').after('<div class="qsm-spinner-loader" style="font-size: 2.5px;"></div>');
	  }
  }

// captcha question type
var mlw_code;
jQuery(document).ready(function () {
	let captchaElement = jQuery('#mlw_code_captcha');
	if (captchaElement.length !== 0) {
		mlw_code = '';
		var mlw_chars = '0123456789ABCDEFGHIJKL!@#$%^&*()MNOPQRSTUVWXTZabcdefghiklmnopqrstuvwxyz';
		var mlw_code_length = 5;
		for (var i = 0; i < mlw_code_length; i++) {
			var rnum = Math.floor(Math.random() * mlw_chars.length);
			mlw_code += mlw_chars.substring(rnum, rnum + 1);
		}
		var mlw_captchaCTX = document.getElementById('mlw_captcha').getContext('2d');
		mlw_captchaCTX.font = 'normal 24px Verdana';
		mlw_captchaCTX.strokeStyle = '#000000';
		mlw_captchaCTX.clearRect(0, 0, 100, 50);
		mlw_captchaCTX.strokeText(mlw_code, 10, 30, 70);
		mlw_captchaCTX.textBaseline = 'middle';
		document.getElementById('mlw_code_captcha').value = mlw_code;
	}
});

var quizType = 'default';

//check max lengh
function checkMaxLength(obj){
    var value = obj.value;
    var maxlength = obj.maxLength;
    if (value.length > parseInt(maxlength)) {
        obj.value = value.slice(0, parseInt(maxlength));
    }
}
let submit_status = true;
function qsm_submit_quiz_if_answer_wrong(question_id, value, $this, $quizForm, answer_type = '') {
	let quiz_id = $quizForm.closest('.qmn_quiz_container').find('.qmn_quiz_id').val();
	let data = qsm_question_quick_result_js(question_id, value, answer_type, qmn_quiz_data[quiz_id].enable_quick_correct_answer_info,quiz_id);
	QSM.changes(data, question_id.replace(/\D/g, ""), quiz_id);
	if (data.success == 'incorrect' && submit_status) {
		$quizForm.closest('.qmn_quiz_container').find('[class*="Required"]').removeClass();
		$quizForm.closest('.qmn_quiz_container').find('.qsm-submit-btn').trigger('click');
	}
	if (1 != qmn_quiz_data[quiz_id].disable_mathjax) {
		MathJax.typesetPromise();
	}
}

function qsm_question_quick_result_js(question_id, answer, answer_type = '', show_correct_info = '',quiz_id='') {
	if (typeof encryptedData[quiz_id] !== 'undefined') {
		let decryptedBytes = CryptoJS.AES.decrypt(encryptedData[quiz_id], encryptionKey[quiz_id]);
		let decryptedData = decryptedBytes.toString(CryptoJS.enc.Utf8);
		let decrypt = JSON.parse(decryptedData);
		question_id = typeof question_id !== 'undefined' ? parseInt(question_id) : 0;
		answer = typeof answer !== 'undefined' ? answer : '';
		answer_type = typeof answer_type !== 'undefined' ? answer_type : '';
		let answer_array = decrypt[question_id].answer_array;
		let settings = decrypt[question_id].settings;
		let correct_info_text = decrypt[question_id].correct_info_text;
		let correct_answer_logic = decrypt.correct_answer_logic;
		show_correct_info = typeof show_correct_info !== 'undefined' && show_correct_info != 0 ? show_correct_info : '';
		let got_ans = false;
		let correct_answer = false;
		let count = 0;
		var index = typeof index !== 'undefined' ? index : 0;
		let correct_index = 0;
		let answer_count = 0;
		let total_correct_answer = 0;
		if (answer_array && false === got_ans) {
			for ( let key in answer_array) {
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

		jQuery(document).trigger('qsm_question_quick_result_js_after', [returnObject, correct_answer, answer, answer_array, answer_type, settings, decrypt, question_id]);
		return returnObject;
	}
}

jQuery(document).on('click', function(event) {
	if (jQuery(event.target).closest('.qsm-quiz-container').length) {
		jQuery('.qsm-quiz-container').removeClass('qsm-recently-active');
		jQuery(event.target).closest('.qsm-quiz-container').addClass('qsm-recently-active');
	} else {
		jQuery('.qsm-quiz-container').removeClass('qsm-recently-active');
	}
});

jQuery(document).keydown(function(event) {
	if (jQuery('.qsm-quiz-container.qsm-recently-active').length) {
		jQuery(document).trigger('qsm_keyboard_quiz_action_start', event);
		if (jQuery(event.target).is('input')) {
			// Check if the parent div has the class 'qsm_contact_div'
			if (jQuery(event.target).closest('div.qsm_contact_div').length > 0) {
				return;
			}
		}
        if ([39, 37, 13, 9].includes(event.keyCode)) {
            event.preventDefault();
        }
        if (event.keyCode === 39) {
            jQuery('.qsm-quiz-container.qsm-recently-active').find('.mlw_next:visible').click();
        }
        if (event.keyCode === 37) {
            jQuery('.qsm-quiz-container.qsm-recently-active').find('.mlw_previous:visible').click();
        }
        if (event.keyCode === 13 && jQuery('textarea:focus').length === 0) {
            jQuery('.qsm-quiz-container.qsm-recently-active').find('.qsm-submit-btn:visible').click();
            jQuery('.qsm-quiz-container.qsm-recently-active').find('.mlw_next:visible').click();
        }
        if (event.keyCode === 40 && jQuery('.qsm-quiz-container.qsm-recently-active .qsm-question-wrapper.qsm-active-question:visible .qmn_radio_answers:not(.qsm_multiple_grid_answers)').length) {
            event.preventDefault();
            let checkedInputs = jQuery('.qsm-quiz-container.qsm-recently-active .qsm-question-wrapper.qsm-active-question .qmn_radio_answers .qmn_mc_answer_wrap input:checked, .qsm-quiz-container.qsm-recently-active .qsm-question-wrapper.qsm-active-question .qmn_radio_answers .mlw_horizontal_choice input:checked');
            if (checkedInputs.length === 0) {
                jQuery('.qsm-quiz-container.qsm-recently-active .qsm-question-wrapper.qsm-active-question .qmn_radio_answers').find('input:first').click();
            } else {
                let nextInput = checkedInputs.closest('.qmn_mc_answer_wrap, .mlw_horizontal_choice').next('.qmn_mc_answer_wrap, .mlw_horizontal_choice').find('input[type="radio"]');
                if (nextInput.length) {
                    nextInput.click();
                } else {
                    jQuery('.qsm-quiz-container.qsm-recently-active .qsm-question-wrapper.qsm-active-question .qmn_radio_answers').find('input:first').click();
                }
            }
        }
        if (event.keyCode === 38 && jQuery('.qsm-quiz-container.qsm-recently-active .qsm-question-wrapper.qsm-active-question:visible .qmn_radio_answers:not(.qsm_multiple_grid_answers)').length) {
            event.preventDefault();
            let checkedInputs = jQuery('.qsm-quiz-container.qsm-recently-active .qsm-question-wrapper.qsm-active-question .qmn_radio_answers .qmn_mc_answer_wrap input:checked, .qsm-quiz-container.qsm-recently-active .qsm-question-wrapper.qsm-active-question .qmn_radio_answers .mlw_horizontal_choice input:checked');
            if (checkedInputs.length === 0) {
                jQuery('.qsm-quiz-container.qsm-recently-active .qsm-question-wrapper.qsm-active-question .qmn_radio_answers').find('.qmn_mc_answer_wrap input:last, .mlw_horizontal_choice input:last').click();
            } else {
                let prevInput = checkedInputs.closest('.qmn_mc_answer_wrap, .mlw_horizontal_choice').prev('.qmn_mc_answer_wrap, .mlw_horizontal_choice').find('input[type="radio"]');
                if (prevInput.length) {
                    prevInput.click();
                } else {
                    jQuery('.qsm-quiz-container.qsm-recently-active .qsm-question-wrapper.qsm-active-question .qmn_radio_answers').find('.qmn_mc_answer_wrap input:last, .mlw_horizontal_choice input:last').click();
                }
            }
        }
        if (event.shiftKey && event.keyCode === 9) {
            let active_question = jQuery('.qsm-quiz-container.qsm-recently-active .qsm-question-wrapper.qsm-active-question');
            if (active_question.length) {
                jQuery('.qsm-quiz-container.qsm-recently-active .qsm-question-wrapper').removeClass("qsm-active-question");
                active_question.prev('.qsm-question-wrapper:visible').addClass("qsm-active-question");
            } else {
                jQuery(".qsm-quiz-container.qsm-recently-active .qsm-question-wrapper:visible:first-child").addClass("qsm-active-question");
            }
        } else if (event.keyCode === 9) {
            let active_question = jQuery('.qsm-quiz-container.qsm-recently-active .qsm-question-wrapper.qsm-active-question');
            if (active_question.length) {
                jQuery('.qsm-quiz-container.qsm-recently-active .qsm-question-wrapper').removeClass("qsm-active-question");
                active_question.next('.qsm-question-wrapper:visible').addClass("qsm-active-question");
            } else {
                jQuery(".qsm-quiz-container.qsm-recently-active .qsm-question-wrapper:visible:first").addClass("qsm-active-question");
            }
        }
        if (event.keyCode === 9) {
            let active_question = jQuery('.qsm-quiz-container.qsm-recently-active .qsm-question-wrapper.qsm-active-question');
            if (active_question.length) {
                jQuery('.qsm-quiz-container.qsm-recently-active .qsm-question-wrapper.qsm-active-question input:first').focus();
            }
        }
		jQuery(document).trigger('qsm_keyboard_quiz_action_end', event);
    }
});


