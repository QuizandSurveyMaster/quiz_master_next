/**************************
 * Quiz And Survey Master
 *************************/

/**************************
 * This object contains the newer functions. All global functions under are slowly
 * being deprecated and replaced with rewritten newer functions
 **************************/

var QSM;
var QSMPageTimer;
(function ($) {
	QSM = {
		/**
		 * Initializes all quizzes or surveys on the page
		 */
		init: function() {
			// Makes sure we have quizzes on this page
			if ( typeof qmn_quiz_data != 'undefined' && qmn_quiz_data) {
				// hide the recaptcha by default
				$( '.g-recaptcha' ).hide();
				// Cycle through all quizzes
				_.each( qmn_quiz_data, function( quiz ) {
					quizID = parseInt( quiz.quiz_id );
					QSM.initPagination( quizID );
					if (qmn_quiz_data[quizID].hasOwnProperty('advanced_timer')) {
						QSMPageTimer.endPageTimer(quizID, true);
					}
					if ( quiz.hasOwnProperty( 'timer_limit' ) && 0 != quiz.timer_limit ) {
						QSM.initTimer( quizID );
					}
				});
			}
		},

		/**
		 * Sets up timer for a quiz
		 *
		 * @param int quizID The ID of the quiz
		 */
		initTimer: function( quizID ) {
                        
                        jQuery(document).trigger('qsm_init_timer_before', [quizID]);
                        
			// Gets our form
			var $quizForm = QSM.getQuizForm(quizID);

			// Creates timer status key.
			qmn_quiz_data[ quizID ].timerStatus = false;

			// If we are using the newer pagination system...
			if (0 < $quizForm.children('.qsm-page').length) {
				// If there is a first page...
				if (qmn_quiz_data[quizID].hasOwnProperty('first_page') && qmn_quiz_data[quizID].first_page) {
					// ... attach an event handler to the click event to activate the timer.
					$('#quizForm' + quizID).closest('.qmn_quiz_container').find('.mlw_next').on('click', function (event) {
						event.preventDefault();
						if (qmn_quiz_data[quizID].hasOwnProperty('advanced_timer')) {
							var start_timer = parseInt(qmn_quiz_data[quizID].advanced_timer.start_timer_page);                                                        
							if ($('#quizForm' + quizID).closest('.qmn_quiz_container').find('.qmn_pagination > .current_page_hidden').val() == start_timer) {
								QSM.activateTimer(quizID);
								$('#quizForm' + quizID).closest('.qmn_quiz_container').find('.stoptimer-p').show();
							}
						} else {
							if ( ! qmn_quiz_data[ quizID ].timerStatus && qmnValidatePage( 'quizForm' + quizID ) ) {
							QSM.activateTimer(quizID);
								$('#quizForm' + quizID).closest('.qmn_quiz_container').find('.stoptimer-p').show();
                                                    }
                                                }
					});
				// ...else, activate the timer on page load.
				} else {
					QSM.activateTimer(quizID);
					$('#quizForm' + quizID).closest('.qmn_quiz_container').find('.stoptimer-p').show();
				}
			// ...else, we must be using the questions per page option.
			} else {
				if (qmn_quiz_data[quizID].hasOwnProperty('pagination') && qmn_quiz_data[quizID].first_page) {
					$('#quizForm' + quizID).closest('.qmn_quiz_container').find('.mlw_next').on('click', function (event) {
						event.preventDefault();
						if (qmn_quiz_data[quizID].hasOwnProperty('advanced_timer')) {
							var start_timer = parseInt(qmn_quiz_data[quizID].advanced_timer.start_timer_page);                                                        
							if ($('#quizForm' + quizID).closest('.qmn_quiz_container').find('.qmn_pagination > .slide_number_hidden').val() == start_timer) {
								QSM.activateTimer(quizID);
								$('#quizForm' + quizID).closest('.qmn_quiz_container').find('.stoptimer-p').show();
							}
						} else {
							if (!qmn_quiz_data[ quizID ].timerStatus && (0 == $('.quiz_begin:visible').length || (1 == $('.quiz_begin:visible').length && qmnValidatePage('quizForm' + quizID)))) {
								QSM.activateTimer(quizID);
								$('#quizForm' + quizID).closest('.qmn_quiz_container').find('.stoptimer-p').show();
							}
						}
					});
				} else {
					QSM.activateTimer(quizID);
					$('#quizForm' + quizID).closest('.qmn_quiz_container').find('.stoptimer-p').show();
				}
			}
                        jQuery(document).trigger('qsm_init_timer_after', [quizID]);
		},
		/**
		 * Starts the timer for the quiz.
		 *
		 * @param int quizID The ID of the quiz.
		 */
		activateTimer: function( quizID ) {
                        
                        jQuery(document).trigger('qsm_activate_time_before', [quizID, qmn_quiz_data]);
			// Gets our form.
			var $timer = QSM.getTimer( quizID );

			// Sets up our variables.
			qmn_quiz_data[ quizID ].timerStatus = true;
			var seconds = 0;

			// Calculates starting time.
			var timerStarted = localStorage.getItem( 'mlw_started_quiz' + quizID );
			var timerRemaning = localStorage.getItem( 'mlw_time_quiz' + quizID );
			if ( 'yes' == timerStarted && 0 < timerRemaning ) {
				seconds = parseInt( timerRemaning );
			} else {
				seconds = parseFloat( qmn_quiz_data[ quizID ].timer_limit ) * 60;
			}
			qmn_quiz_data[ quizID ].timerRemaning = seconds;

                        //hidden timer
                        jQuery(".hiddentimer").html(seconds);

			// Makes the timer appear.
			$timer.show();
			$timer.text( QSM.secondsToTimer( seconds ) );

			// Sets up timer interval.
			qmn_quiz_data[ quizID ].timerInterval = setInterval( QSM.timer, 1000, quizID );
                        jQuery(document).trigger('qsm_activate_time_after', [quizID, qmn_quiz_data]);
		},
		/**
		 * Reduces the timer by one second and checks if timer is 0
		 *
		 * @param int quizID The ID of the quiz.
		 */
		timer: function( quizID ) {
			qmn_quiz_data[ quizID ].timerRemaning -= 1;
			if ( 0 > qmn_quiz_data[ quizID ].timerRemaning ) {
				qmn_quiz_data[ quizID ].timerRemaning = 0;
			}
			var secondsRemaining = qmn_quiz_data[ quizID ].timerRemaning;
			var display = QSM.secondsToTimer( secondsRemaining );

			// Sets our local storage values for the timer being started and current timer value.
			localStorage.setItem( 'mlw_time_quiz' + quizID, secondsRemaining );
			localStorage.setItem( 'mlw_started_quiz' + quizID, "yes" );

			// Updates timer element and title on browser tab.
			var $timer = QSM.getTimer( quizID );
			$timer.text( display );
			document.title = display + ' ' + qsmTitleText;

			/*CUSTOM TIMER*/
			if(qmn_quiz_data[quizID].hasOwnProperty('advanced_timer') && qmn_quiz_data[quizID].advanced_timer.timer_design == 'big_timer'){
				$(".second.circle").parent('.mlw_quiz_form').addClass('qsm_big_timer');
				$(".second.circle").show();
				$(".second.circle strong").html(display);
				var datashow =( $(".hiddentimer").html() - secondsRemaining ) / $(".hiddentimer").html();
				$(".second.circle").circleProgress({
					startAngle: 11,
					value: datashow,
					animation: false,
					fill: {gradient: ["#00bb40", "#00511c"]}
				});
			}

			var $quizForm = QSM.getQuizForm( quizID );
			var total_seconds = parseFloat( qmn_quiz_data[ quizID ].timer_limit ) * 60;
			var ninety_sec = total_seconds - ( total_seconds * 90 / 100 );
			if(ninety_sec == secondsRemaining){
				$quizForm.closest( '.qmn_quiz_container' ).find('.qsm_ninety_warning').fadeIn();
			}

			// If timer is run out, disable fields.
			if ( 0 >= secondsRemaining ) {
				clearInterval( qmn_quiz_data[ quizID ].timerInterval );
				$( ".mlw_qmn_quiz input:radio" ).attr( 'disabled', true );
				$( ".mlw_qmn_quiz input:checkbox" ).attr( 'disabled', true );
				$( ".mlw_qmn_quiz select" ).attr( 'disabled', true );
				$( ".mlw_qmn_question_comment" ).attr( 'disabled', true );
				$( ".mlw_answer_open_text" ).attr( 'disabled', true );
				$( ".mlw_answer_number" ).attr( 'readonly', true );

				$quizForm.closest( '.qmn_quiz_container' ).addClass( 'qsm_timer_ended' );
				$quizForm.closest( '.qmn_quiz_container' ).prepend('<p style="color: red;">Quiz time is over</p>');
				if(qmn_quiz_data[ quizID ].enable_result_after_timer_end == 1){
					$quizForm.closest( '.qmn_quiz_container' ).find('.qsm-submit-btn').trigger('click');
				}else{
					MicroModal.show( 'modal-3' );
				}
				return;
			}
		},
		/**
		 * Clears timer interval
		 *
		 * @param int quizID The ID of the quiz
		 */
		endTimer: function( quizID ) {
			localStorage.setItem( 'mlw_time_quiz' + quizID, 'completed' );
			localStorage.setItem( 'mlw_started_quiz' + quizID, 'no' );
			document.title = qsmTitleText;
			if ( typeof qmn_quiz_data[ quizID ].timerInterval != 'undefined' ) {
				clearInterval( qmn_quiz_data[ quizID ].timerInterval );
			}
                        jQuery(document).trigger('qsm_end_timer', [quizID, qmn_quiz_data]);
		},
		/**
		 * Converts seconds to 00:00:00 format
		 *
		 * @param int seconds The number of seconds
		 * @return string A string in H:M:S format
		 */
		secondsToTimer: function( seconds ) {
			var formattedTime = '';
			seconds = parseInt( seconds );

			// Prepares the hours part.
			var hours = Math.floor( seconds / 3600 );
			if ( 0 === hours) {
				formattedTime = '00:';
			} else if ( 10 > hours ) {
				formattedTime = '0' + hours + ':';
			} else {
				formattedTime = hours + ':';
			}

			// Prepares the minutes part.
			var minutes = Math.floor( ( seconds % 3600 ) / 60 );
			if ( 0 === minutes) {
				formattedTime = formattedTime + '00:';
			} else if ( 10 > minutes ) {
				formattedTime = formattedTime + '0' + minutes + ':';
			} else {
				formattedTime = formattedTime + minutes + ':';
			}

			// Prepares the seconds part.
			var remainder = Math.floor( ( seconds % 60 ) );
			if ( 0 === remainder) {
				formattedTime = formattedTime + '00';
			} else if ( 10 > remainder ) {
				formattedTime = formattedTime + '0' + remainder;
			} else {
				formattedTime = formattedTime + remainder;
			}
			return formattedTime;
		},
		/**
		 * Gets the jQuery object for the timer
		 */
		getTimer: function( quizID ) {
			var $quizForm = QSM.getQuizForm( quizID );
			return $quizForm.children( '.mlw_qmn_timer' );
		},
		/**
		 * Sets up pagination for a quiz
		 *
		 * @param int quizID The ID of the quiz.
		 */
		initPagination: function( quizID ) {                    
			var $quizForm = QSM.getQuizForm( quizID );
			if ( 0 < $quizForm.children( '.qsm-page' ).length ) {
				$quizForm.children( '.qsm-page' ).hide();
				template = wp.template( 'qsm-pagination' );
				$quizForm.append( template() );
				if( $quizForm.find( '.qsm-pagination > .current_page_hidden' ).length == 0){
					$quizForm.find( '.qsm-pagination' ).append( '<input type="hidden" value="0" name="current_page" class="current_page_hidden" />');
				}
				if ( '1' == qmn_quiz_data[ quizID ].progress_bar ) {
                                        jQuery(document).trigger('qsm_init_progressbar_before', [quizID, qmn_quiz_data]);
					$('#quizForm' + quizID).find('.qsm-progress-bar').show();
					qmn_quiz_data[ quizID ].bar = new ProgressBar.Line('#quizForm' + quizID + ' .qsm-progress-bar', {
						strokeWidth: 2,
						easing: 'easeInOut',
						duration: 1400,
						color: '#3498db',
						trailColor: '#eee',
						trailWidth: 1,
						svgStyle: {width: '100%', height: '100%'},
						text: {
						  style: {
							// color: '#999',
							position: 'absolute',
							padding: 0,
							margin: 0,
							top: 0,
							right: '10px',
							'font-size': '13px',
							'font-weight': 'bold',
							transform: null
						  },
						  autoStyleContainer: false
						},
						from: {color: '#3498db'},
						to: {color: '#ED6A5A'},
						step: function(state, bar) {
							//bar.setText(Math.round(bar.value() * 100) + ' %');
						}
					});
                                        jQuery(document).trigger('qsm_init_progressbar_after', [quizID, qmn_quiz_data]);
				}
				QSM.goToPage( quizID, 1 );
				$quizForm.find( '.qsm-pagination .qsm-next' ).on( 'click', function( event ) {                                        
                                        jQuery(document).trigger('qsm_next_button_click_before', [quizID]);
					event.preventDefault();
					QSM.nextPage( quizID );
					var $container = jQuery( '#quizForm' + quizID ).closest( '.qmn_quiz_container' );                                        
                                        if( qmn_quiz_data[ quizID ].disable_scroll_next_previous_click != 1 ){
                                            qsmScrollTo( $container );
                                        }
                                        jQuery(document).trigger('qsm_next_button_click_after', [quizID]);
				});
				$quizForm.find( '.qsm-pagination .qsm-previous' ).on( 'click', function( event ) {
                                        jQuery(document).trigger('qsm_previous_button_click_before', [quizID]);
					event.preventDefault();
					QSM.prevPage( quizID );
					var $container = jQuery( '#quizForm' + quizID ).closest( '.qmn_quiz_container' );
                                        if( qmn_quiz_data[ quizID ].disable_scroll_next_previous_click != 1 ){
                                            qsmScrollTo( $container );
                                        }					
                                        jQuery(document).trigger('qsm_previous_button_click_after', [quizID]);
				});
			}
		},
		/**
		 * Navigates quiz to specific page
		 *
		 * @param int pageNumber The number of the page
		 */
		goToPage: function( quizID, pageNumber ) {
                        jQuery(document).trigger('qsm_go_to_page_before', [quizID, pageNumber]);
			var $quizForm = QSM.getQuizForm( quizID );
			var $pages = $quizForm.children( '.qsm-page' );
			var $currentPage = $quizForm.children( '.qsm-page:nth-of-type(' + pageNumber + ')' );
			$pages.hide();
			$currentPage.show();

			if (qmn_quiz_data[quizID].hasOwnProperty('advanced_timer')) {
				QSMPageTimer.endPageTimer(quizID);
				QSMPageTimer.initPageTimer(quizID, $currentPage);
			}

			$quizForm.find('.current_page_hidden').val( pageNumber - 1 );
			$quizForm.find( '.qsm-previous' ).hide();
			$quizForm.find( '.qsm-next' ).hide();
			$quizForm.find( '.qsm-submit-btn' ).hide();
			$quizForm.find( '.g-recaptcha' ).hide();
			if ( pageNumber < $pages.length ) {
				$quizForm.find( '.qsm-next' ).show();
			} else {
				$quizForm.find( '.qsm-submit-btn' ).show();
				$quizForm.find( '.g-recaptcha' ).show();
			}
			if ( 1 < pageNumber ) {
				$quizForm.find( '.qsm-previous' ).show();
			}
			if (1 == $currentPage.data('prevbtn')) {
				$quizForm.find( '.qsm-previous' ).hide();
			}
			if ( '1' == qmn_quiz_data[ quizID ].progress_bar ) {
                                var current_page = jQuery('#quizForm' + quizID).find('.current_page_hidden').val();
                                var total_page_length = $pages.length - 1;
                                if( qmn_quiz_data[ quizID ].contact_info_location == 0 ){
                                    //Do nothing.
                                }else if( qmn_quiz_data[ quizID ].contact_info_location == 1 ){
                                    if($quizForm.children( '.qsm-page' ).find('.qsm_contact_div ').length > 0){
                                        //total_page_length = total_page_length - 1;
                                    }
                                }
                                var animate_value = current_page / total_page_length;
                                if( animate_value <= 1){
                                    qmn_quiz_data[ quizID ].bar.animate( animate_value );
                                    var old_text = jQuery( '#quizForm' + quizID ).find( '.progressbar-text' ).text().replace(' %', '');
                                    var new_text = Math.round(animate_value * 100);
                                    jQuery({
                                        Counter: old_text
                                    }).animate({
                                        Counter: new_text
                                    }, {
                                        duration: 1000,
                                        easing: 'swing',
                                        step: function () {
                                            jQuery( '#quizForm' + quizID ).find( '.progressbar-text' ).text(Math.round(this.Counter) + ' %');
                                        }
                                    });
                                }
			}
			QSM.savePage( quizID, pageNumber );
                        jQuery(document).trigger('qsm_go_to_page_after', [quizID, pageNumber]);
		},
		/**
		 * Moves forward or backwards through the pages
		 *
		 * @param int quizID The ID of the quiz
		 * @param int difference The number of pages to forward or back
		 */
		changePage: function( quizID, difference ) {
			var page = QSM.getPage( quizID );
			page += difference;
			QSM.goToPage( quizID, page );
		},
		nextPage: function( quizID ) {
			if ( qmnValidatePage( 'quizForm' + quizID ) ) {
				QSM.changePage( quizID, 1 );
			}
		},
		prevPage: function( quizID ) {
			QSM.changePage( quizID, -1 );
		},
		savePage: function( quizID, pageNumber ) {
			sessionStorage.setItem( 'quiz' + quizID + 'page', pageNumber );
		},
		getPage: function( quizID ) {
			pageNumber = parseInt( sessionStorage.getItem( 'quiz' + quizID + 'page' ) );
			if ( isNaN( pageNumber ) || null == pageNumber ) {
				pageNumber = 1;
			}
			return pageNumber;
		},
		/**
		 * Scrolls to the top of supplied element
		 *
		 * @param jQueryObject The jQuery version of an element. i.e. $('#quizForm3')
		 */
		scrollTo: function( $element ) {
			jQuery( 'html, body' ).animate(
				{
					scrollTop: $element.offset().top - 150
				},
			1000 );
		},
		/**
		 * Gets the jQuery object of the quiz form
		 */
		getQuizForm: function( quizID ) {
			return $( '#quizForm' + quizID );
		}
	};

	QSMPageTimer = {
		/**
		 * Init Page Timer
		 */
		initPageTimer: function (quizID, $currentpage) {
                        jQuery(document).trigger('qsm_init_page_timer_before', [quizID, $currentpage]);
			var $quizForm = QSM.getQuizForm(quizID);
			var pid = $currentpage.data('pid');
			if (undefined != pid) {
				var $qpages = qmn_quiz_data[ quizID ].qpages;
				var $curr_page_opt = $qpages[pid];
				if ($curr_page_opt.hasOwnProperty('pagetimer') && 0 != $curr_page_opt.pagetimer) {
					var $timer_box = $currentpage.find('.qsm-pagetimer');
					var seconds = 0;
					var timerStarted = localStorage.getItem('mlw_started_q' + quizID + '_page' + pid);
					var timerStoped = localStorage.getItem('mlw_stoped_q' + quizID + '_page' + pid);
					var timerRemaning = localStorage.getItem('mlw_time_q' + quizID + '_page' + pid);
					if (timerStoped != 'undefined' && timerStoped > 0) {
						seconds = timerStoped;
					} else {
						if ('yes' == timerStarted) {
							if (0 < timerRemaning) {
								seconds = parseInt(timerRemaning);
							}
						} else {
							seconds = parseFloat($curr_page_opt.pagetimer) * 60;
						}
					}
					qmn_quiz_data[ quizID ].qpages[ pid ].timerRemaning = seconds;
					/* Makes the timer appear. */
					$timer_box.show();
					$timer_box.text(QSMPageTimer.secondsToTimer(seconds));
					/* Sets up timer interval. */
					qmn_quiz_data[ quizID ].qpages[ pid ].timerInterval = setInterval(QSMPageTimer.timer, 1000, quizID, pid, $timer_box);
				}
				$currentpage.find('.page_intro_wrapper video').each(function(){
					var $this = jQuery(this);
					var src = $this.find('source').attr('src');
					$this.attr('src', src)
					$this.load();
					$this.get(0).play();
				});
			}
                        jQuery(document).trigger('qsm_init_page_timer_after',[quizID, $currentpage]);
		},
		/**
		 * Reduces the timer by one second and checks if timer is 0
		 * @param int quizID The ID of the quiz.
		 */
		timer: function (quizID, pid, $timer_box) {
			var $quizForm = QSM.getQuizForm(quizID);
			var $page = qmn_quiz_data[ quizID ].qpages[pid];
			qmn_quiz_data[ quizID ].qpages[ pid ].timerRemaning -= 1;
			if (0 > qmn_quiz_data[ quizID ].qpages[ pid ].timerRemaning) {
				qmn_quiz_data[ quizID ].qpages[ pid ].timerRemaning = 0;
			}
			var total_seconds = parseFloat($page.pagetimer) * 60;
			var secondsRemaining = qmn_quiz_data[ quizID ].qpages[ pid ].timerRemaning;
			var display = QSMPageTimer.secondsToTimer(secondsRemaining);
			$timer_box.text(display);
			var pageTimeTaken = total_seconds - secondsRemaining;
			jQuery('#pagetime_'+pid).val(pageTimeTaken);
			/* Sets our local storage values for the timer being started and current timer value. */
			localStorage.setItem('mlw_started_q' + quizID + '_page' + pid, "yes");
			localStorage.setItem('mlw_time_q' + quizID + '_page' + pid, secondsRemaining);
			if ($page.hasOwnProperty('pagetimer_warning') && 0 != $page.pagetimer_warning) {
				var page_warning_sec = parseFloat($page.pagetimer_warning) * 60;
				if (page_warning_sec == secondsRemaining) {
					$timer_box.parents('.qsm-page').find('.qsm-pagetimer-warning').fadeIn();
				}
			}
			/* If timer is run out, disable fields. */
			if (0 >= secondsRemaining) {
				clearInterval(qmn_quiz_data[ quizID ].qpages[ pid ].timerInterval);

				$(".qsm-page:visible input:radio").attr('disabled', true);
				$(".qsm-page:visible input:checkbox").attr('disabled', true);
				$(".qsm-page:visible select").attr('disabled', true);
				$(".qsm-page:visible .mlw_qmn_question_comment").attr('disabled', true);
				$(".qsm-page:visible .mlw_answer_open_text").attr('disabled', true);
				$(".qsm-page:visible .mlw_answer_number").attr('disabled', true);

				QSMPageTimer.endPageTimer(quizID);
				MicroModal.show('modal-page-1');
				return;
			}
		},
		/**
		 * Clears timer interval
		 * @param int quizID The ID of the quiz
		 */
		endPageTimer: function (quizID, clearStorage = false) {
			jQuery.each(qmn_quiz_data[quizID].qpages, function (i, value) {
				if (value.hasOwnProperty('pagetimer') && 0 != value.pagetimer) {
					if (clearStorage) {
						localStorage.removeItem('mlw_started_q' + quizID + '_page' + value.id);
						localStorage.removeItem('mlw_stoped_q' + quizID + '_page' + value.id);
						localStorage.removeItem('mlw_time_q' + quizID + '_page' + value.id);
					}
					var secondsRemaining = qmn_quiz_data[ quizID ].qpages[ value.id ].timerRemaning;
					localStorage.setItem('mlw_stoped_q' + quizID + '_page' + value.id, secondsRemaining);
					localStorage.setItem('mlw_time_q' + quizID + '_page' + value.id, 'completed');
					if (typeof qmn_quiz_data[ quizID ].qpages[ value.id ].timerInterval != 'undefined') {
						clearInterval(qmn_quiz_data[ quizID ].qpages[ value.id ].timerInterval);
					}
				}
			});
		},
		/**
		 * Converts seconds to 00:00:00 format
		 * @param int seconds The number of seconds
		 * @return string A string in H:M:S format
		 */
		secondsToTimer: function (seconds) {
			var formattedTime = '';
			seconds = parseInt(seconds);
			var hours = Math.floor(seconds / 3600);
			if (0 === hours) {
				formattedTime = '00:';
			} else if (10 > hours) {
				formattedTime = '0' + hours + ':';
			} else {
				formattedTime = hours + ':';
			}
			var minutes = Math.floor((seconds % 3600) / 60);
			if (0 === minutes) {
				formattedTime = formattedTime + '00:';
			} else if (10 > minutes) {
				formattedTime = formattedTime + '0' + minutes + ':';
			} else {
				formattedTime = formattedTime + minutes + ':';
			}
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
	}

	// On load code
	$(function() {

		// Legacy init.
		qmnInit();

		// Call main initialization.
		QSM.init();
	});

	jQuery("input[name='timer_ms']").each(function(){
		var timems = qsmTimeInMS();
		jQuery(this).val(timems);
	});
}(jQuery));

// Global Variables
var qsmTitleText = document.title;

/**
 * Validates an email ID.
 *
 * @param email The Email Id to validate.
 * @returns Boolean
 */
function isEmail( email ) {
	var regex = /^([a-zA-Z0-9_\.\-\+])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/;
	if( ! regex.test( email ) ) {
		return false;
	}else{
		return true;
	}
}


/**
* Limit multiple response based on question limit
* @returns {undefined}
*/
function qsmCheckMR(event, limit){
   var checked = jQuery(event).parents('.quiz_section').find(':checkbox:checked').length;
   if (checked > limit) {
        event.checked = false;
        if( jQuery(event).parents('.quiz_section').find('.multi-checkbox-limit-reach').length == 0 ){
            jQuery(event).parents('.quiz_section').append('<span class="multi-checkbox-limit-reach">'+ qmn_ajax_object.multicheckbox_limit_reach +'</span>');
        }        
    } else {
        jQuery(event).parents('.quiz_section').find('.multi-checkbox-limit-reach').remove();
    }
}

function qmnTimeTakenTimer() {
	var x = +jQuery( '#timer' ).val();
	if ( NaN === x ) {
		x = 0;
	}
	x = x + 1;
	jQuery( '#timer' ).val( x );
}

function qsmEndTimeTakenTimer() {
	clearInterval( qsmTimerInterval );
}

function qsmTimeInMS() {
	var d = new Date();
	var n = d.getTime();
	return n;
}

function qmnClearField( field ) {
	if ( field.defaultValue == field.value ) field.value = '';
}

function qsmScrollTo( $element ) {
        if($element.length > 0){
            jQuery(document).trigger('qsm_scroll_to_top_before', [$element]);
            jQuery( 'html, body' ).animate( { scrollTop: $element.offset().top - 150 }, 1000 );
            jQuery(document).trigger('qsm_scroll_to_top_after', [$element]);
        }
}

function qmnDisplayError( message, field, quiz_form_id ) {
	jQuery( '#' + quiz_form_id + ' .qmn_error_message_section' ).addClass( 'qmn_error_message' );
	jQuery( '#' + quiz_form_id + ' .qmn_error_message' ).text( message );
	field.closest( '.quiz_section' ).addClass( 'qmn_error' );
}

function qmnResetError( quiz_form_id ) {
	jQuery( '#' + quiz_form_id + ' .qmn_error_message' ).text( '' );
	jQuery( '#' + quiz_form_id + ' .qmn_error_message_section' ).removeClass( 'qmn_error_message' );
	jQuery( '#' + quiz_form_id + ' .quiz_section' ).removeClass( 'qmn_error' );
}

function qmnValidation( element, quiz_form_id ) {
        jQuery(document).trigger('qsm_before_validation', [element, quiz_form_id]);
	var result = true;
	var quiz_id = +jQuery( '#' + quiz_form_id ).find( '.qmn_quiz_id' ).val();
	var email_error = qmn_quiz_data[ quiz_id ].error_messages.email;
	var number_error = qmn_quiz_data[ quiz_id ].error_messages.number;
	var empty_error = qmn_quiz_data[ quiz_id ].error_messages.empty;
	var incorrect_error = qmn_quiz_data[ quiz_id ].error_messages.incorrect;
	qmnResetError( quiz_form_id );        
	jQuery( element ).each(function(){
		if ( jQuery( this ).attr( 'class' )) {
			if( jQuery( this ).attr( 'class' ).indexOf( 'mlwEmail' ) !== -1 && this.value !== "" ) {
				// Remove any trailing and preceeding space.
				var x = jQuery.trim( this.value );
				if ( ! isEmail( x ) ) {
					qmnDisplayError( email_error, jQuery( this ), quiz_form_id );
					result = false;
				}
			}
                        var by_pass = true;
                        if( qmn_quiz_data[ quizID ].timer_limit_val > 0 && qmn_quiz_data[quiz_id].hasOwnProperty('skip_validation_time_expire') && qmn_quiz_data[quiz_id].skip_validation_time_expire == 0 ){
                            by_pass = false;
                        }                        
                        
			if ( localStorage.getItem( 'mlw_time_quiz' + quiz_id ) === null || localStorage.getItem( 'mlw_time_quiz'+quiz_id ) > 0.08 || by_pass === false ) {

				if( jQuery( this ).attr( 'class' ).indexOf( 'mlwRequiredNumber' ) > -1 && this.value === "" && +this.value != NaN ) {
					qmnDisplayError( number_error, jQuery( this ), quiz_form_id );
					result =  false;
				}
                                if( jQuery( this ).attr( 'class' ).indexOf( 'mlwRequiredDate' ) > -1 && this.value === "" ) {
					qmnDisplayError( empty_error, jQuery( this ), quiz_form_id );
					result =  false;
				}
				if( jQuery( this ).attr( 'class' ).indexOf( 'mlwRequiredText' ) > -1 && jQuery.trim(this.value) === "" ) {
					qmnDisplayError( empty_error, jQuery( this ), quiz_form_id );
					result =  false;
				}
				if( jQuery( this ).attr( 'class' ).indexOf( 'mlwRequiredCaptcha' ) > -1 && this.value != mlw_code ) {
					qmnDisplayError( incorrect_error, jQuery( this ), quiz_form_id );
					result =  false;
				}
				if( jQuery( this ).attr( 'class' ).indexOf( 'mlwRequiredAccept' ) > -1 && ! jQuery( this ).prop( 'checked' ) ) {
					qmnDisplayError( empty_error, jQuery( this ), quiz_form_id );
					result =  false;
				}                                
				if( jQuery( this ).attr( 'class' ).indexOf( 'mlwRequiredRadio' ) > -1 ) {
					check_val = jQuery( this ).find( 'input:checked' ).val();
					if ( check_val == "No Answer Provided" || check_val == "" ) {
						qmnDisplayError( empty_error, jQuery( this ), quiz_form_id );
						result =  false;
					}
				}
				if( jQuery( this ).attr( 'class' ).indexOf( 'mlwRequiredFileUpload' ) > -1 ) {
					var selected_file = jQuery( this ).get(0).files.length;
					if ( selected_file === 0 ) {
						qmnDisplayError( empty_error, jQuery( this ), quiz_form_id );
						result =  false;
					}
				}
				if( jQuery( this ).attr( 'class' ).indexOf( 'qsmRequiredSelect' ) > -1 ) {
					check_val = jQuery( this ).val();
					if ( check_val == "No Answer Provided" || check_val == "" ) {
						qmnDisplayError( empty_error, jQuery( this ), quiz_form_id );
						result =  false;
					}
				}
				if( jQuery( this ).attr( 'class' ).indexOf( 'mlwRequiredCheck' ) > -1 ) {
					if ( ! jQuery( this ).find( 'input:checked' ).length ) {
						qmnDisplayError( empty_error, jQuery( this ), quiz_form_id );
						result =  false;
					}
				}
                //Google recaptcha validation
				if( jQuery( this ).attr( 'class' ).indexOf( 'g-recaptcha-response' ) > -1 ) {
					if(grecaptcha.getResponse() == "") {
						alert('ReCaptcha is missing');
						result =  false;
					}
				}
			}
		}
	});
        jQuery(document).trigger('qsm_after_validation', [element, quiz_form_id]);
	return result;
}

function getFormData($form){
    var unindexed_array = $form.serializeArray();
    var indexed_array = {};

    jQuery.map(unindexed_array, function(n, i){
        indexed_array[n['name']] = n['value'];
    });

    return indexed_array;
}

function qmnFormSubmit( quiz_form_id ) {
	var quiz_id = +jQuery( '#' + quiz_form_id ).find( '.qmn_quiz_id' ).val();
	var $container = jQuery( '#' + quiz_form_id ).closest( '.qmn_quiz_container' );
	var result = qmnValidation( '#' + quiz_form_id + ' *', quiz_form_id );


	if ( ! result ) { return result; }

	/**
	* Update Timer in MS
	*/
	
	var timer_ms = jQuery('#' + quiz_form_id).find("input[name='timer_ms']").val();
	var new_timer_ms = qsmTimeInMS();
	jQuery('#' + quiz_form_id).find("input[name='timer_ms']").val(Math.abs(new_timer_ms - timer_ms));

	jQuery( '.mlw_qmn_quiz input:radio' ).attr( 'disabled', false );
	jQuery( '.mlw_qmn_quiz input:checkbox' ).attr( 'disabled', false );
	jQuery( '.mlw_qmn_quiz select' ).attr( 'disabled', false );
	jQuery( '.mlw_qmn_question_comment' ).attr( 'disabled', false );
	jQuery( '.mlw_answer_open_text' ).attr( 'disabled', false );

        //Convert serialize data into index array
        var unindexed_array = jQuery( '#' + quiz_form_id ).serializeArray();
        var fd = new FormData();
        jQuery.each(unindexed_array,function(key,input){
            fd.append(input.name,input.value);
        });
        fd.append("action", 'qmn_process_quiz');


	qsmEndTimeTakenTimer();
	if (qmn_quiz_data[quizID].hasOwnProperty('advanced_timer')) {
		QSMPageTimer.endPageTimer(quiz_id);
	}
	if ( qmn_quiz_data[quiz_id].hasOwnProperty( 'timer_limit' ) ) {
		QSM.endTimer( quiz_id );
	}
        jQuery(document).trigger('qsm_before_quiz_submit', [quiz_form_id]);
	jQuery( '#' + quiz_form_id + ' input[type=submit]' ).attr( 'disabled', 'disabled' );
	qsmDisplayLoading( $container );
	// console.log( 'submitted data:' + unindexed_array );
        jQuery.ajax({
            url: qmn_ajax_object.ajaxurl,
            data: fd,
            contentType: false,
            processData: false,
            type: 'POST',
            success: function( response ){
                qmnDisplayResults( JSON.parse( response ), quiz_form_id, $container );
            }
        });
        jQuery(document).trigger('qsm_after_quiz_submit', [quiz_form_id]);
	return false;
}

function qsmDisplayLoading( $container ) {
	$container.empty();
	$container.append( '<div class="qsm-spinner-loader"></div>' );
	qsmScrollTo( $container );
}

function qmnDisplayResults( results, quiz_form_id, $container ) {
	$container.empty();
	if ( results.redirect ) {
		window.location.replace( results.redirect );
	} else {
		$container.append( '<div class="qmn_results_page"></div>' );
		$container.find( '.qmn_results_page' ).html( results.display );
		qsmScrollTo( $container );
                MathJax.Hub.queue.Push(["Typeset", MathJax.Hub]);
	}
}

function qmnInit() {
	if ( typeof qmn_quiz_data != 'undefined' && qmn_quiz_data ) {
		for ( var key in qmn_quiz_data ) {
			if ( qmn_quiz_data[key].ajax_show_correct === '1' ) {
				jQuery( '#quizForm' + qmn_quiz_data[key].quiz_id + ' .qmn_quiz_radio').change(function() {
					var chosen_answer = jQuery(this).val();
					var question_id = jQuery(this).attr('name').replace(/question/i,'');
					var chosen_id = jQuery(this).attr('id');
					jQuery.each( qmn_quiz_data[key].question_list, function( i, value ) {
						if ( question_id == value.question_id ) {
							jQuery.each( value.answers, function(j, answer ) {
								if ( answer[0] === chosen_answer ) {
									if ( answer[2] !== 1) {
										jQuery( '#'+chosen_id ).parent().addClass( "qmn_incorrect_answer" );
									}
								}
								if ( answer[2] === 1) {
									jQuery( ':radio[name=question'+question_id+'][value="'+answer[0]+'"]' ).parent().addClass( "qmn_correct_answer" );
								}
							});
						}
					});
				});
			}

			if ( qmn_quiz_data[key].disable_answer === '1' ) {
				jQuery( '#quizForm' + qmn_quiz_data[key].quiz_id + ' .qmn_quiz_radio').change(function() {
					var radio_group = jQuery(this).attr('name');
					jQuery('input[type=radio][name='+radio_group+']').prop('disabled',true);
				});
			}

			if ( qmn_quiz_data[key].hasOwnProperty('pagination') ) {
		            qmnInitPagination( qmn_quiz_data[key].quiz_id );
			}
		}
	}
}

//Function to validate the answers provided in quiz
function qmnValidatePage( quiz_form_id ) {
	var result = qmnValidation( '#' + quiz_form_id + ' .quiz_section:visible *', quiz_form_id );
	return result;
}

//Function to advance quiz to next page
function qmnNextSlide( pagination, go_to_top, quiz_form_id ) {
        jQuery(document).trigger('qsm_next_button_click_before', [quiz_form_id]);
	var quiz_id = +jQuery( quiz_form_id ).find( '.qmn_quiz_id' ).val();
	var $container = jQuery( quiz_form_id ).closest( '.qmn_quiz_container' );
	var slide_number = +$container.find( '.slide_number_hidden' ).val();	
	var page_number = +$container.find( '.current_page_hidden' ).val();	
	var section_totals = +$container.find( '.total_sections_hidden' ).val();
	if(pagination == 1){
		section_totals = section_totals - 1;
	}        
        var total_pages = $container.find( '.total_pages_hidden' ).val();
        
	jQuery( quiz_form_id + " .qsm-auto-page-row" ).hide();
        if( page_number >= total_pages ){
            alert('Next page not found.');
            $container.find( ".mlw_next" ).hide();
            jQuery( quiz_form_id + " .qsm-auto-page-row.quiz_end" ).show();
            return true;
        }
        if( slide_number == 0 && page_number == 0 ){
            jQuery( quiz_form_id + " .qsm-auto-page-row.quiz_begin" ).show();
            $container.find( ".mlw_previous" ).hide();
            $container.find( '.current_page_hidden' ).val( '1' );
        } else if( total_pages == parseInt( page_number ) + 1 ){ //Last page
            $container.find( ".mlw_next" ).hide();
            jQuery( quiz_form_id + " .qsm-auto-page-row.quiz_end" ).show();
            $container.find( '.slide_number_hidden' ).val( parseInt( slide_number ) + 1 );
            $container.find( '.current_page_hidden' ).val( parseInt( page_number ) + 1 );
            $container.find( ".mlw_previous" ).show();            
            $container.find( '.g-recaptcha' ).show();
        }else if( slide_number >=0 && page_number >= 1 ){
            $container.find( '.qsm-auto-page-row.qsm-apc-' + page_number ).show();
            $container.find( '.slide_number_hidden' ).val( parseInt( slide_number ) + 1 );
            $container.find( '.current_page_hidden' ).val( parseInt( page_number ) + 1 );
            $container.find( ".mlw_previous" ).show();
        }
        if( jQuery(quiz_form_id + " .quiz_section.quiz_end").is(':visible') ){
            var button_width = jQuery( quiz_form_id + ' .qsm-submit-btn' ).width();
            var progress_width = jQuery( quiz_form_id ).parent().find('.qmn_pagination').width();                    
            jQuery( quiz_form_id ).parent().find('.qmn_pagination').css('width', progress_width - button_width - 40 );
        }else{
            jQuery( quiz_form_id ).parent().find('.qmn_pagination').css('width', '100%' );
        }
        if (go_to_top == 1 && qmn_quiz_data[ quiz_id ].disable_scroll_next_previous_click != 1 ) {
            qsmScrollTo( $container );
	}
        //Show the page counts
        if(page_number > 0 && jQuery( quiz_form_id ).closest( '.qmn_quiz_container' ).find('.pages_count').length > 0){
            var actual_pages = total_pages - 2;
            if( page_number <= actual_pages ){
                jQuery( quiz_form_id ).closest( '.qmn_quiz_container' ).find('.pages_count').text('').text( page_number + qmn_ajax_object.out_of_text + actual_pages);
                jQuery( quiz_form_id ).closest( '.qmn_quiz_container' ).find('.pages_count').show();
            }else{
                jQuery( quiz_form_id ).closest( '.qmn_quiz_container' ).find('.pages_count').hide();
            }
        }else{
            jQuery( quiz_form_id ).closest( '.qmn_quiz_container' ).find('.pages_count').hide();
        }        
        qmnInitProgressbarOnClick(quiz_id, page_number, total_pages);
        jQuery(document).trigger('qsm_next_button_click_after', [quiz_form_id]);
}

function qmnPrevSlide( pagination, go_to_top, quiz_form_id ) {
        jQuery(document).trigger('qsm_previous_button_click_before', [quiz_form_id]);
	var quiz_id = +jQuery( quiz_form_id ).find( '.qmn_quiz_id' ).val();
	var $container = jQuery( quiz_form_id ).closest( '.qmn_quiz_container' );
	var slide_number = +$container.find( '.slide_number_hidden' ).val();
	var previous = +$container.find( '.previous_amount_hidden' ).val();
	var section_totals = +$container.find( '.total_sections_hidden' ).val();
        var page_number = +$container.find( '.current_page_hidden' ).val();	
        var total_pages = $container.find( '.total_pages_hidden' ).val();
	jQuery( quiz_form_id + " .qsm-auto-page-row" ).hide();
	jQuery( quiz_form_id + " .g-recaptcha" ).hide();
        var slide_original_val = parseInt( slide_number ) - 1;
        if( slide_original_val == 0 ){
            $container.find( ".mlw_next" ).show();
            jQuery( quiz_form_id + " .qsm-auto-page-row.quiz_begin" ).show();
            $container.find( '.slide_number_hidden' ).val( slide_original_val );
            $container.find( '.current_page_hidden' ).val( parseInt( page_number ) - 1 );
            $container.find( ".mlw_previous" ).hide();
        } else {
            $container.find( '.qsm-auto-page-row.qsm-apc-' + slide_original_val ).show();
            $container.find( '.slide_number_hidden' ).val( slide_original_val );
            $container.find( '.current_page_hidden' ).val( parseInt( page_number ) - 1 );
            $container.find( ".mlw_next" ).show();
        }
	if (go_to_top == 1 && qmn_quiz_data[ quiz_id ].disable_scroll_next_previous_click != 1 ) {
		qsmScrollTo( $container );
	}
        if(page_number > 0 && jQuery( quiz_form_id ).closest( '.qmn_quiz_container' ).find('.pages_count').length > 0){
            var actual_pages = total_pages - 2;            
            if( slide_original_val <= actual_pages ){                
                jQuery( quiz_form_id ).closest( '.qmn_quiz_container' ).find('.pages_count').text('').text( slide_original_val + qmn_ajax_object.out_of_text + actual_pages);
                if(slide_original_val == 0){
                    jQuery( quiz_form_id ).closest( '.qmn_quiz_container' ).find('.pages_count').hide();
                }else{
                    jQuery( quiz_form_id ).closest( '.qmn_quiz_container' ).find('.pages_count').show();
                }
            }else{
                jQuery( quiz_form_id ).closest( '.qmn_quiz_container' ).find('.pages_count').hide();
            }
        }
        jQuery( quiz_form_id ).parent().find('.qmn_pagination').css('width', '100%' );
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
function qmnInitProgressbarOnClick(quiz_id, page_number, total_page_number){    
    if ( '1' == qmn_quiz_data[ quiz_id ].progress_bar ) {
        var qmn_total_questions = qmn_quiz_data[quiz_id].pagination.total_questions;        
        var pagination = qmn_quiz_data[quiz_id].pagination.amount;        
        total_page_number = Math.ceil(qmn_total_questions / pagination);        
        total_page_number = total_page_number + 1; //Increase for quiz end section
        var animate_value = page_number / total_page_number;        
        if( animate_value <= 1){
            qmn_quiz_data[ quiz_id ].bar.animate( animate_value );
            var old_text = jQuery( '#qsm_progress_bar_' + quiz_id ).find( '.progressbar-text' ).text().replace(' %', '');
            var new_text = Math.round(animate_value * 100);
            jQuery({
                Counter: old_text
            }).animate({
                Counter: new_text
            }, {
                duration: 1000,
                easing: 'swing',
                step: function () {
                    jQuery( '#qsm_progress_bar_' + quiz_id ).find( '.progressbar-text' ).text(Math.round(this.Counter) + ' %');
                }
            });
        }
    }
}

function qmnUpdatePageNumber( amount, quiz_form_id ) {
	var current_page = +jQuery( quiz_form_id ).closest( '.qmn_quiz_container' ).find( '.current_page_hidden' ).val();
	var total_pages = jQuery( quiz_form_id ).closest( '.qmn_quiz_container' ).find( '.total_pages_hidden' ).val();
	current_page += amount;
	//jQuery( quiz_form_id ).siblings( '.qmn_pagination' ).find( " .qmn_page_counter_message" ).text( current_page + "/" + total_pages );
}

function qmnInitPagination( quiz_id ) {
	var qmn_section_total = +qmn_quiz_data[quiz_id].pagination.total_questions;	                
	var qmn_total_pages = Math.ceil( qmn_section_total / +qmn_quiz_data[quiz_id].pagination.amount );
        
        qmn_total_pages = qmn_total_pages + 1; //quiz begin
        qmn_total_pages = qmn_total_pages + 1; //quiz end
        
	jQuery( '#quizForm' + quiz_id ).closest( '.qmn_quiz_container' ).append( '<div class="qmn_pagination border margin-bottom"></div>' );
	jQuery( '#quizForm' + quiz_id ).closest( '.qmn_quiz_container' ).find( '.qmn_pagination' ).append( '<input type="hidden" value="0" name="slide_number" class="slide_number_hidden" />')
		.append( '<input type="hidden" value="0" name="current_page" class="current_page_hidden" />')
		.append( '<input type="hidden" value="' + qmn_total_pages + '" name="total_pages" class="total_pages_hidden" />')
		.append( '<input type="hidden" value="' + qmn_section_total + '" name="total_sections" class="total_sections_hidden" />')
		.append( '<input type="hidden" value="0" name="previous_amount" class="previous_amount_hidden" />')
		.append( '<a class="qmn_btn mlw_qmn_quiz_link mlw_previous" href="#">' + qmn_quiz_data[quiz_id].pagination.previous_text + '</a>' )
		.append( '<span class="qmn_page_message"></span>' )
		.append( '<div class="qmn_page_counter_message"></div>' )
                .append( '<div class="qsm-progress-bar" id="qsm_progress_bar_'+ quiz_id +'" style="display:none;"><div class="progressbar-text"></div></div>' )
		.append( '<a class="qmn_btn mlw_qmn_quiz_link mlw_next" href="#">' + qmn_quiz_data[quiz_id].pagination.next_text + '</a>' );

        if ( '1' == qmn_quiz_data[ quiz_id ].progress_bar ) {
            jQuery(document).trigger('qsm_init_progressbar_before', [quiz_id, qmn_quiz_data]);
            jQuery('#quizForm' + quiz_id).closest( '.qmn_quiz_container' ).find('.qsm-progress-bar').show();
            qmn_quiz_data[ quiz_id ].bar = new ProgressBar.Line('#qsm_progress_bar_' + quiz_id, {
                    strokeWidth: 2,
                    easing: 'easeInOut',
                    duration: 500,
                    color: '#3498db',
                    trailColor: '#eee',
                    trailWidth: 1,
                    svgStyle: {width: '100%', height: '100%'},
                    text: {
                      style: {
                            // color: '#999',
                            position: 'absolute',
                            padding: 0,
                            margin: 0,
                            top: 0,
                            right: '10px',
                            'font-size': '13px',
                            'font-weight': 'bold',
                            transform: null
                      },
                      autoStyleContainer: false
                    },
                    from: {color: '#3498db'},
                    to: {color: '#ED6A5A'},
                    step: function(state, bar) {
                    }
            });
            jQuery(document).trigger('qsm_init_progressbar_after', [quiz_id, qmn_quiz_data]);
        }

	jQuery(".mlw_next").click(function(event) {                
		event.preventDefault();                
		var quiz_id = +jQuery( this ).closest( '.qmn_quiz_container' ).find( '.qmn_quiz_id' ).val();
                jQuery(document).trigger('qsm_auto_next_button_click_before', [quiz_id]);
		if ( qmnValidatePage( 'quizForm' + quiz_id ) ) {
			qmnNextSlide( qmn_quiz_data[quiz_id].pagination.amount, 1, '#quizForm' + quiz_id );
		}
	});

	jQuery(".mlw_previous").click(function(event) {
		event.preventDefault();
		var quiz_id = +jQuery( this ).closest( '.qmn_quiz_container' ).find( '.qmn_quiz_id' ).val();
		qmnPrevSlide( qmn_quiz_data[quiz_id].pagination.amount, 1, '#quizForm' + quiz_id );
	});

	if ( qmn_quiz_data[quiz_id].first_page ) {
		qmnNextSlide( 1, 0, '#quizForm' + quiz_id );
	} else {
		qmnNextSlide( qmn_quiz_data[quiz_id].pagination.amount, 0, '#quizForm' + quiz_id );
	}

}

function qmnSocialShare( network, mlw_qmn_social_text, mlw_qmn_title, facebook_id, share_url ) {
	var sTop = window.screen.height / 2 - ( 218 );
	var sLeft = window.screen.width / 2 - ( 313 );
	var sqShareOptions = "height=400,width=580,toolbar=0,status=0,location=0,menubar=0,directories=0,scrollbars=0,top=" + sTop + ",left=" + sLeft;
	var pageUrl = window.location.href;
	var pageUrlEncoded = encodeURIComponent( share_url );
	var url = '';
	if ( network == 'facebook' ) {
		url = "https://www.facebook.com/dialog/feed?"	+ "display=popup&" + "app_id="+facebook_id +
			"&" + "link=" + pageUrlEncoded + "&" + "name=" + encodeURIComponent(mlw_qmn_social_text) +
			"&" + "description=";
	}
	if ( network == 'twitter' )	{
		url = "https://twitter.com/intent/tweet?text=" + encodeURIComponent(mlw_qmn_social_text);
	}
	window.open( url, "Share", sqShareOptions );
	return false;
}

jQuery(function() {
	jQuery( '.qmn_quiz_container' ).tooltip();

	jQuery( '.qmn_quiz_container input' ).on( 'keypress', function ( e ) {
		if ( e.which === 13 ) {
			e.preventDefault();
		}
	});

	jQuery(document).on( 'click', ".qsm-submit-btn", function( event ) {
		event.preventDefault();
		var form = jQuery(this).closest('form')[0];
		qmnFormSubmit( form.id );
	});

	jQuery(document).on('click', '.btn-reload-quiz', function (e) {
		e.preventDefault();
		var quiz_id = jQuery(this).data('quiz_id');
		var parent_div = jQuery(this).parents('.qsm-quiz-container');
		qsmDisplayLoading(parent_div);
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
				QSM.init();
				qmnInit();
				QSM.initTimer(quiz_id);
				jQuery("input[name='timer_ms']").each(function () {
					var timems = qsmTimeInMS();
					jQuery(this).val(timems);
				});
				setInterval(qmnTimeTakenTimer, 1000);
				MathJax.Hub.queue.Push(["Typeset", MathJax.Hub]);
			},
			error: function (errorThrown) {
				console.log('error');
			}
		});
	});

	jQuery(document).on('change','.qmn_radio_answers input',function(e){
		var quizID = jQuery(this).parents('.qsm-quiz-container').find('.qmn_quiz_id').val();
		if (qmn_quiz_data[ quizID ].enable_quick_result_mc == 1) {
			var question_id = jQuery(this).attr('name').split('question')[1],
					value = jQuery(this).val(),
					$this = jQuery(this).parents('.quiz_section');
			jQuery.ajax({
				type: 'POST',
				url: qmn_ajax_object.ajaxurl,
				data: {
					action: "qsm_get_question_quick_result",
					question_id: question_id,
					answer: value,
					show_correct_info: qmn_quiz_data[ quizID ].enable_quick_correct_answer_info
				},
				success: function (response) {
					var data = jQuery.parseJSON(response);
					$this.find('.quick-question-res-p').remove();
					$this.find('.qsm-inline-correct-info').remove();
					if (data.success == 'correct') {
						$this.append('<div style="color: green" class="quick-question-res-p">' + qmn_quiz_data[ quizID ].quick_result_correct_answer_text + '</div>')
						$this.append('<div class="qsm-inline-correct-info">' + data.message + '</div>');
					} else if (data.success == 'incorrect') {
						$this.append('<div style="color: red" class="quick-question-res-p">' + qmn_quiz_data[ quizID ].quick_result_wrong_answer_text + '</div>')
						$this.append('<div class="qsm-inline-correct-info">' + data.message + '</div>');
					}
					MathJax.Hub.queue.Push(["Typeset", MathJax.Hub]);
				},
				error: function (errorThrown) {
					alert(errorThrown);
				}
			});
		}
	});

	 // Autocomplete off
	
		var quizID = jQuery('.qsm-quiz-container').find('.qmn_quiz_id').val();
		var $quizForm = QSM.getQuizForm( quizID );
		if (qmn_quiz_data[ quizID ].form_disable_autofill == 1) {
			jQuery('#quizForm'+quizID).attr('autocomplete' , 'off');
		}
		
	

	// End Quiz If Wrong
	jQuery(document).on('change ','.qmn_radio_answers input , .qmn_check_answers input , .qsm_select',function(e){
		var quizID = jQuery(this).parents('.qsm-quiz-container').find('.qmn_quiz_id').val();
		var $quizForm = QSM.getQuizForm( quizID );
		if (qmn_quiz_data[ quizID ].end_quiz_if_wrong == 1) {
			var question_id = jQuery(this).attr('name').split('question')[1],
					value = jQuery(this).val(),
					$this = jQuery(this).parents('.quiz_section');
			jQuery.ajax({
				type: 'POST',
				url: qmn_ajax_object.ajaxurl,
				data: {
					action: "qsm_get_question_quick_result",
					question_id: question_id,
					answer: value,
					show_correct_info: qmn_quiz_data[ quizID ].enable_quick_correct_answer_info
				},
				success: function (response) {
					var data = jQuery.parseJSON(response);
					$this.find('.quick-question-res-p').remove();
					$this.find('.qsm-inline-correct-info').remove();
					if (data.success == 'correct') {
						
					} else if (data.success == 'incorrect') {
						
						

						$this.append('<div style="color: red" class="quick-question-res-p">' + qmn_quiz_data[ quizID ].quick_result_wrong_answer_text + '</div>')
						$this.append('<div class="qsm-inline-correct-info">' + data.message + '</div>');

						 setTimeout(function() {
   							 $quizForm.closest( '.qmn_quiz_container' ).find('.qsm-submit-btn').trigger('click');
  						}, 1000);

						
					}
					MathJax.Hub.queue.Push(["Typeset", MathJax.Hub]);
				},
				error: function (errorThrown) {
					alert(errorThrown);
				}
			});
		}
	});

	//Ajax upload file code
	jQuery('.quiz_section .mlw_answer_file_upload').on('change', function(){
		var $this = jQuery(this);
		var hidden_val = jQuery(this).parent('.quiz_section').find('.mlw_file_upload_hidden_path').val();
		var file_data = jQuery(this).prop('files')[0];
		var form_data = new FormData();
		form_data.append('file', file_data);
		form_data.append('action', 'qsm_upload_image_fd_question');
		var question_id = $this.parent('.quiz_section').find('.mlw_file_upload_hidden_value').attr("name").replace('question','');
		form_data.append('question_id', question_id);
		$this.next('.loading-uploaded-file').show();
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
				if(obj.type == 'success'){
					$this.next().next('.remove-uploaded-file').show();
					$this.next().next().next('.mlw_file_upload_hidden_value').val(obj.file_url);
					$this.parent('.quiz_section').find('.mlw_file_upload_hidden_path').val(obj.file_path);
					$this.parent('.quiz_section').find('.mlw-file-upload-error-msg').hide();
				}else{
					$this.parent('.quiz_section').find('.mlw-file-upload-error-msg').text('').text(obj.message);
					$this.parent('.quiz_section').find('.mlw-file-upload-error-msg').show();
					$this.parent('.quiz_section').find('.mlw_answer_file_upload').val('');
				}
			}
		});
		return false;
	});

	//Ajax remove file code
	jQuery('.quiz_section .remove-uploaded-file').on('click', function(){
		var $this = jQuery(this);
		var file_data = jQuery(this).parent('.quiz_section').find('.mlw_file_upload_hidden_path').val();
		var form_data = new FormData();
		form_data.append('action', 'qsm_remove_file_fd_question');
		form_data.append('file_url', file_data);
		jQuery.ajax({
			url: qmn_ajax_object.ajaxurl,
			type: 'POST',
			data: form_data,
			cache: false,
			contentType: false,
			processData: false,
			success: function (response) {
				var obj = jQuery.parseJSON(response);
				if(obj.type == 'success'){
					$this.hide();
					$this.parent('.quiz_section').find('.mlw_file_upload_hidden_value').val('');
					$this.parent('.quiz_section').find('.mlw_file_upload_hidden_path').val('');
					$this.parent('.quiz_section').find('.mlw_answer_file_upload').val('');
					$this.parent('.quiz_section').find('.mlw-file-upload-error-msg').hide();
				}else{
					$this.parent('.quiz_section').find('.mlw-file-upload-error-msg').text('').text(obj.message);
					$this.parent('.quiz_section').find('.mlw-file-upload-error-msg').show();
				}
			}
		});
		return false;
	});

	//Deselect all answer on select
	jQuery('.qsm-deselect-answer').click(function(e){
		e.preventDefault();
		jQuery(this).parents('.quiz_section').find('input[type="radio"]').prop('checked', false);
		jQuery(this).parents('.quiz_section').find('input[type="radio"][value="No Answer Provided"]').prop('checked', true);
		jQuery(this).parents('.quiz_section').find('input[type="radio"][value=""]').prop('checked', true);
	});

	//Submit the form on popup click
	jQuery(document).on( 'click', '.submit-the-form', function(e) {
		e.preventDefault();
		// Triggger the click event on the quiz form's submit button.
		jQuery( '.qsm-submit-btn' ).trigger( 'click' );
		jQuery('#modal-3').removeClass('is-open');
	});

	jQuery('.pagetime-goto-nextpage').click(function (e) {
		e.preventDefault();
		var quiz_id = jQuery(this).data('quiz_id');
		QSM.nextPage(quiz_id);
		var $container = jQuery('#quizForm' + quiz_id).closest('.qmn_quiz_container');
		qsmScrollTo($container);
	});
        
	jQuery(document).on('keyup', '.mlwPhoneNumber', function(e){
            this.value = this.value.replace(/[^- +()0-9\.]/g,'');
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

var qsmTimerInterval = setInterval( qmnTimeTakenTimer, 1000 );
