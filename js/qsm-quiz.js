/**************************
 * Quiz And Survey Master 
 *************************/

/**************************
 * This object contains the newer functions. All global functions under are slowly 
 * being deprecated and replaced with rewritten newer functions
 **************************/
var QSM;
(function ($) {
	QSM = {
		/**
		 * Initializes all quizzes or surveys on the page
		 */
		init: function() {
			// Makes sure we have quizzes on this page
			if ( typeof qmn_quiz_data != 'undefined' && qmn_quiz_data) {
				// Cycle through all quizzes
				_.each( qmn_quiz_data, function( quiz ) {
					quizID = parseInt( quiz.quiz_id );
					QSM.initPagination( quizID );
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

			// Gets our form
			var $quizForm = QSM.getQuizForm( quizID );

			// Creates timer status key.
			qmn_quiz_data[ quizID ].timerStatus = false;

			// If we are using the newer pagination system...
			if ( 0 < $quizForm.children( '.qsm-page' ).length ) {
				// If there is a first page...
				if ( qmn_quiz_data[quizID].hasOwnProperty('first_page') && qmn_quiz_data[quizID].first_page ) {
					// ... attach an event handler to the click event to activate the timer.
					$( '#quizForm' + quizID ).closest( '.qmn_quiz_container' ).find( '.mlw_next' ).on( 'click', function(event) {
						event.preventDefault();
						if ( ! qmn_quiz_data[ quizID ].timerStatus && qmnValidatePage( 'quizForm' + quizID ) ) {
							QSM.activateTimer( quizID );
						}
					});
				// ...else, activate the timer on page load.
				} else {
					QSM.activateTimer( quizID );
				}
			// ...else, we must be using the questions per page option.
			} else {
				if ( qmn_quiz_data[quizID].hasOwnProperty('pagination') && qmn_quiz_data[quizID].first_page ) {
					$( '#quizForm' + quizID ).closest( '.qmn_quiz_container' ).find( '.mlw_next' ).on( 'click', function(event) {
						event.preventDefault();
						if ( ! qmn_quiz_data[ quizID ].timerStatus && ( 0 == $( '.quiz_begin:visible' ).length || ( 1 == $( '.quiz_begin:visible' ).length && qmnValidatePage( 'quizForm' + quizID ) ) ) ) {
							QSM.activateTimer( quizID );
						}
					});
				} else {
					QSM.activateTimer( quizID );
				}
			}
		},
		/**
		 * Starts the timer for the quiz.
		 *
		 * @param int quizID The ID of the quiz.
		 */
		activateTimer: function( quizID ) {
			
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

			// Makes the timer appear.
			$timer.show();
			$timer.text( QSM.secondsToTimer( seconds ) );

			// Sets up timer interval.
			qmn_quiz_data[ quizID ].timerInterval = setInterval( QSM.timer, 1000, quizID );
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

			// If timer is run out, disable fields.
			if ( 0 >= secondsRemaining ) {
				clearInterval( qmn_quiz_data[ quizID ].timerInterval );
				$( ".mlw_qmn_quiz input:radio" ).attr( 'disabled', true );
				$( ".mlw_qmn_quiz input:checkbox" ).attr( 'disabled', true );
				$( ".mlw_qmn_quiz select" ).attr( 'disabled', true );
				$( ".mlw_qmn_question_comment" ).attr( 'disabled', true );
				$( ".mlw_answer_open_text" ).attr( 'disabled', true );
				$( ".mlw_answer_number" ).attr( 'disabled', true );

				var $quizForm = QSM.getQuizForm( quizID );
				$quizForm.closest( '.qmn_quiz_container' ).addClass( 'qsm_timer_ended' );
                                $quizForm.closest( '.qmn_quiz_container' ).prepend('<p style="color: red;">Quiz time is over</p>');
                                //$( ".qsm-submit-btn" ).remove();
                                if(qmn_ajax_object.enable_result_after_timer_end == 1){
                                    $quizForm.closest( '.qmn_quiz_container' ).find('form').submit();
                                }else{
                                    alert('You are not able to attemp remaining part of quiz but you can submit the quiz!')
                                }
				//document.quizForm.submit();
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
				if ( '1' == qmn_quiz_data[ quizID ].progress_bar ) {
					$( '#qsm-progress-bar' ).show();
					qmn_quiz_data[ quizID ].bar = new ProgressBar.Line('#qsm-progress-bar', {
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
						  bar.setText(Math.round(bar.value() * 100) + ' %');
						}
					});
				}
				QSM.goToPage( quizID, 1 );
				$quizForm.find( '.qsm-pagination .qsm-next' ).on( 'click', function( event ) {
					event.preventDefault();
					QSM.nextPage( quizID );
				});
				$quizForm.find( '.qsm-pagination .qsm-previous' ).on( 'click', function( event ) {
					event.preventDefault();
					QSM.prevPage( quizID );
				});
			}			
		},
		/**
		 * Navigates quiz to specific page
		 *
		 * @param int pageNumber The number of the page
		 */
		goToPage: function( quizID, pageNumber ) {
			var $quizForm = QSM.getQuizForm( quizID );
			var $pages = $quizForm.children( '.qsm-page' );
			$pages.hide();
			$quizForm.children( '.qsm-page:nth-of-type(' + pageNumber + ')' ).show();
			$quizForm.find( '.qsm-previous' ).hide();
			$quizForm.find( '.qsm-next' ).hide();
			$quizForm.find( '.qsm-submit-btn' ).hide();
			if ( pageNumber < $pages.length ) {
				$quizForm.find( '.qsm-next' ).show();
			} else {
				$quizForm.find( '.qsm-submit-btn' ).show();
			}
			if ( 1 < pageNumber ) {
				$quizForm.find( '.qsm-previous' ).show();
			}
			if ( '1' == qmn_quiz_data[ quizID ].progress_bar ) {
				qmn_quiz_data[ quizID ].bar.animate( pageNumber / $pages.length );
			}
			QSM.savePage( quizID, pageNumber );
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

	// On load code
	$(function() {

		// Legacy init.
		qmnInit();

		// Call main initialization.
		QSM.init();               
	});

}(jQuery));

// Global Variables
var qsmTitleText = document.title;

/**
* Limit multiple response based on question limit
* @returns {undefined}
*/
function qsmCheckMR(event, limit){
   var checked = jQuery(event).parents('.quiz_section').find(':checkbox:checked').length;   
   if (checked > limit) {
        event.checked = false;
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

function qmnClearField( field ) {
	if ( field.defaultValue == field.value ) field.value = '';
}

function qsmScrollTo( $element ) {
        if($element.length > 0){
            jQuery( 'html, body' ).animate( { scrollTop: $element.offset().top - 150 }, 1000 );
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
	var result = true;
	var quiz_id = +jQuery( '#' + quiz_form_id ).find( '.qmn_quiz_id' ).val();
	var email_error = qmn_quiz_data[ quiz_id ].error_messages.email;
	var number_error = qmn_quiz_data[ quiz_id ].error_messages.number;
	var empty_error = qmn_quiz_data[ quiz_id ].error_messages.empty;
	var incorrect_error = qmn_quiz_data[ quiz_id ].error_messages.incorrect;
	qmnResetError( quiz_form_id );        
	jQuery( element ).each(function(){
		if ( jQuery( this ).attr( 'class' )) {
			if( jQuery( this ).attr( 'class' ).indexOf( 'mlwEmail' ) > -1 && this.value !== "" ) {
				var x = this.value;
				var atpos = x.indexOf('@');
				var dotpos = x.lastIndexOf( '.' );
				if ( atpos < 1 || dotpos < atpos + 2 || dotpos + 2>= x.length ) {
					qmnDisplayError( email_error, jQuery( this ), quiz_form_id );
					result = false;
				}
			}
			if ( localStorage.getItem( 'mlw_time_quiz' + quiz_id ) === null || localStorage.getItem( 'mlw_time_quiz'+quiz_id ) > 0.08 ) {

				if( jQuery( this ).attr( 'class' ).indexOf( 'mlwRequiredNumber' ) > -1 && this.value === "" && +this.value != NaN ) {
					qmnDisplayError( number_error, jQuery( this ), quiz_form_id );
					result =  false;
				}
                                
				if( jQuery( this ).attr( 'class' ).indexOf( 'mlwRequiredText' ) > -1 && this.value === "" ) {
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
					if ( check_val == "No Answer Provided" ) {
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
					if ( check_val == "No Answer Provided" ) {
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
	return result;
}

function qmnFormSubmit( quiz_form_id ) {
	var quiz_id = +jQuery( '#' + quiz_form_id ).find( '.qmn_quiz_id' ).val();
	var $container = jQuery( '#' + quiz_form_id ).closest( '.qmn_quiz_container' );
	var result = qmnValidation( '#' + quiz_form_id + ' *', quiz_form_id );

	if ( ! result ) { return result; }

	jQuery( '.mlw_qmn_quiz input:radio' ).attr( 'disabled', false );
	jQuery( '.mlw_qmn_quiz input:checkbox' ).attr( 'disabled', false );
	jQuery( '.mlw_qmn_quiz select' ).attr( 'disabled', false );
	jQuery( '.mlw_qmn_question_comment' ).attr( 'disabled', false );
	jQuery( '.mlw_answer_open_text' ).attr( 'disabled', false );

	var data = {
		action: 'qmn_process_quiz',
		quizData: jQuery( '#' + quiz_form_id ).serialize()
	};

	qsmEndTimeTakenTimer();

	if ( qmn_quiz_data[quiz_id].hasOwnProperty( 'timer_limit' ) ) {
		QSM.endTimer( quiz_id );
	}

	jQuery( '#' + quiz_form_id + ' input[type=submit]' ).attr( 'disabled', 'disabled' );
	qsmDisplayLoading( $container );

	jQuery.post( qmn_ajax_object.ajaxurl, data, function( response ) {
		qmnDisplayResults( JSON.parse( response ), quiz_form_id, $container );
	});

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
	var quiz_id = +jQuery( quiz_form_id ).find( '.qmn_quiz_id' ).val();
	var $container = jQuery( quiz_form_id ).closest( '.qmn_quiz_container' );
	var slide_number = +$container.find( '.slide_number_hidden' ).val();
	var previous = +$container.find( '.previous_amount_hidden' ).val();
	var section_totals = +$container.find( '.total_sections_hidden' ).val();

	jQuery( quiz_form_id + " .quiz_section" ).hide();
	for ( var i = 0; i < pagination; i++ ) {
		if (i === 0 && previous === 1 && slide_number > 1) {
			slide_number = slide_number + pagination;
		} else {
			slide_number++;
		}
		if (slide_number < 1) {
			slide_number = 1;
		}
		$container.find( ".mlw_qmn_quiz_link.mlw_previous" ).hide();

    if ( qmn_quiz_data[ quiz_id ].first_page ) {
      if (slide_number > 1) {
				$container.find( ".mlw_qmn_quiz_link.mlw_previous" ).show();
      }
    } else {
			if (slide_number > pagination) {
				$container.find( ".mlw_qmn_quiz_link.mlw_previous" ).show();
			}
    }
		if (slide_number == section_totals) {
			$container.find( ".mlw_qmn_quiz_link.mlw_next" ).hide();
		}
		if (slide_number < section_totals) {
			$container.find( ".mlw_qmn_quiz_link.mlw_next" ).show();
		}
		jQuery( quiz_form_id + " .quiz_section.slide" + slide_number ).show();
	}

	jQuery( quiz_form_id ).closest( '.qmn_quiz_container' ).find( '.slide_number_hidden' ).val( slide_number );
	jQuery( quiz_form_id ).closest( '.qmn_quiz_container' ).find( '.previous_amount_hidden' ).val( 0 );

	qmnUpdatePageNumber( 1, quiz_form_id );

	if (go_to_top == 1) {
		qsmScrollTo( $container );
	}
        var page_number = slide_number - pagination;
        if(page_number > 0 && jQuery( quiz_form_id ).closest( '.qmn_quiz_container' ).find('.pages_count').length > 0){
            var hiddem_page_number = jQuery( quiz_form_id ).closest( '.qmn_quiz_container' ).find('.current_page_hidden').val();
            if(hiddem_page_number > 0){
                page_number = parseInt(hiddem_page_number) + 1;
            }
            var total_questions = jQuery( quiz_form_id ).closest( '.qmn_quiz_container' ).find('[class*="question-section-id-"]').length;
            var total_page_number = Math.ceil(total_questions / pagination);            
            jQuery( quiz_form_id ).closest( '.qmn_quiz_container' ).find('.pages_count').text('').text( page_number + ' out of ' + total_page_number);
            jQuery( quiz_form_id ).closest( '.qmn_quiz_container' ).find('.pages_count').show();
            jQuery( quiz_form_id ).closest( '.qmn_quiz_container' ).find('.current_page_hidden').val( page_number );            
        }else{
            jQuery( quiz_form_id ).closest( '.qmn_quiz_container' ).find('.pages_count').hide();
        }
        
}

function qmnPrevSlide( pagination, go_to_top, quiz_form_id ) {
	var quiz_id = +jQuery( quiz_form_id ).find( '.qmn_quiz_id' ).val();
	var $container = jQuery( quiz_form_id ).closest( '.qmn_quiz_container' );
	var slide_number = +$container.find( '.slide_number_hidden' ).val();
	var previous = +$container.find( '.previous_amount_hidden' ).val();
	var section_totals = +$container.find( '.total_sections_hidden' ).val();

	jQuery( quiz_form_id + " .quiz_section" ).hide();
	for (var i = 0; i < pagination; i++) {
		if (i === 0 && previous === 0)	{
			slide_number = slide_number - pagination;
		} else {
			slide_number--;
		}
		if (slide_number < 1) {
			slide_number = 1;
		}

		$container.find( ".mlw_qmn_quiz_link.mlw_previous" ).hide();

		if ( qmn_quiz_data[ quiz_id ].first_page ) {
			if (slide_number > 1) {
				$container.find( ".mlw_qmn_quiz_link.mlw_previous" ).show();
			}
		} else {
			if (slide_number > pagination) {
				$container.find( ".mlw_qmn_quiz_link.mlw_previous" ).show();
			}
		}
		if (slide_number == section_totals) {
			$container.find( ".mlw_qmn_quiz_link.mlw_next" ).hide();
		}
		if (slide_number < section_totals) {
			$container.find( ".mlw_qmn_quiz_link.mlw_next" ).show();
		}
		jQuery( quiz_form_id + " .quiz_section.slide" + slide_number ).show();
	}

	qmnUpdatePageNumber( -1, quiz_form_id );

	jQuery( quiz_form_id ).closest( '.qmn_quiz_container' ).find( '.slide_number_hidden' ).val( slide_number );
	jQuery( quiz_form_id ).closest( '.qmn_quiz_container' ).find( '.previous_amount_hidden' ).val( 0 );

	if (go_to_top == 1) {
		qsmScrollTo( $container );
	}
        var hiddem_page_number = jQuery( quiz_form_id ).closest( '.qmn_quiz_container' ).find('.current_page_hidden').val();
        if(hiddem_page_number > 0 && jQuery( quiz_form_id ).closest( '.qmn_quiz_container' ).find('.pages_count').length > 0){
            var page_number = parseInt(hiddem_page_number) - 1;
            var total_questions = jQuery( quiz_form_id ).closest( '.qmn_quiz_container' ).find('[class*="question-section-id-"]').length;
            var total_page_number = Math.ceil(total_questions / pagination);            
            jQuery( quiz_form_id ).closest( '.qmn_quiz_container' ).find('.pages_count').text('').text( page_number + ' out of ' + total_page_number);
            if(page_number == 0){
                jQuery( quiz_form_id ).closest( '.qmn_quiz_container' ).find('.pages_count').hide();
            }else{
                jQuery( quiz_form_id ).closest( '.qmn_quiz_container' ).find('.pages_count').show();
            }            
            jQuery( quiz_form_id ).closest( '.qmn_quiz_container' ).find('.current_page_hidden').val( page_number );            
        }
}

function qmnUpdatePageNumber( amount, quiz_form_id ) {
	var current_page = +jQuery( quiz_form_id ).closest( '.qmn_quiz_container' ).find( '.current_page_hidden' ).val();
	var total_pages = jQuery( quiz_form_id ).closest( '.qmn_quiz_container' ).find( '.total_pages_hidden' ).val();
	current_page += amount;
	//jQuery( quiz_form_id ).siblings( '.qmn_pagination' ).find( " .qmn_page_counter_message" ).text( current_page + "/" + total_pages );
}

function qmnInitPagination( quiz_id ) {

	var qmn_section_total = +qmn_quiz_data[quiz_id].pagination.total_questions + 1;
	if ( qmn_quiz_data[quiz_id].pagination.section_comments === '0' ) {
		qmn_section_total += 1;
	}
	var qmn_total_pages = Math.ceil( qmn_section_total / +qmn_quiz_data[quiz_id].pagination.amount );
	if ( qmn_quiz_data[quiz_id].first_page ) {
		qmn_total_pages += 1;
		qmn_section_total += 1;
	}

	jQuery( '#quizForm' + quiz_id + ' .quiz_section' ).hide();
	jQuery( '#quizForm' + quiz_id + ' .quiz_section' ).append( "<br />" );
	jQuery( '#quizForm' + quiz_id ).closest( '.qmn_quiz_container' ).append( '<div class="qmn_pagination border margin-bottom"></div>' );
	jQuery( '#quizForm' + quiz_id ).closest( '.qmn_quiz_container' ).find( '.qmn_pagination' ).append( '<input type="hidden" value="0" name="slide_number" class="slide_number_hidden" />')
		.append( '<input type="hidden" value="0" name="current_page" class="current_page_hidden" />')
		.append( '<input type="hidden" value="' + qmn_total_pages + '" name="total_pages" class="total_pages_hidden" />')
		.append( '<input type="hidden" value="' + qmn_section_total + '" name="total_sections" class="total_sections_hidden" />')
		.append( '<input type="hidden" value="0" name="previous_amount" class="previous_amount_hidden" />')
		.append( '<a class="qmn_btn mlw_qmn_quiz_link mlw_previous" href="#">' + qmn_quiz_data[quiz_id].pagination.previous_text + '</a>' )
		.append( '<span class="qmn_page_message"></span>' )
		.append( '<div class="qmn_page_counter_message"></div>' )
		.append( '<a class="qmn_btn mlw_qmn_quiz_link mlw_next" href="#">' + qmn_quiz_data[quiz_id].pagination.next_text + '</a>' );

	jQuery(".mlw_next").click(function(event) {
		event.preventDefault();
		var quiz_id = +jQuery( this ).closest( '.qmn_quiz_container' ).find( '.qmn_quiz_id' ).val();
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

function qmnSocialShare( network, mlw_qmn_social_text, mlw_qmn_title, facebook_id ) {
	var sTop = window.screen.height / 2 - ( 218 );
	var sLeft = window.screen.width / 2 - ( 313 );
	var sqShareOptions = "height=400,width=580,toolbar=0,status=0,location=0,menubar=0,directories=0,scrollbars=0,top=" + sTop + ",left=" + sLeft;
	var pageUrl = window.location.href;
	var pageUrlEncoded = encodeURIComponent( pageUrl );
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
	
	jQuery( '.qmn_quiz_form' ).on( "submit", function( event ) {
	  event.preventDefault();
		qmnFormSubmit( this.id );
	});
        
        jQuery(document).on('click','.btn-reload-quiz',function(e){
            e.preventDefault();
            var quiz_id = jQuery(this).data('quiz_id');
            var parent_div = jQuery(this).parents('.qsm-quiz-container');
            qsmDisplayLoading( parent_div );
            jQuery.ajax({
                type: 'POST',
                url: qmn_ajax_object.ajaxurl,
                data: {
                    action: "qsm_get_quiz_to_reload",                    
                    quiz_id: quiz_id,
                },
                success: function (response) {                    
                    parent_div.replaceWith(response);
                    QSM.initPagination( quiz_id );
                },
                error: function (errorThrown) {
                    alert(errorThrown);
                }
            });
        });
        
        jQuery(document).on('change','.qmn_radio_answers input',function(e){
            if(qmn_ajax_object.enable_quick_result_mc == 1){
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
                    },
                    success: function (response) {
                        $this.find('.quick-question-res-p').remove();
                        if(response == 'correct'){
                            $this.append('<p style="color: green" class="quick-question-res-p"><b>Correct!</b> You have selected correct answer.</p>')
                        }else if(response == 'incorrect'){
                            $this.append('<p style="color: red" class="quick-question-res-p"><b>Wrong!</b> You have selected wrong answer.</p>')
                        }                        
                    },
                    error: function (errorThrown) {
                        alert(errorThrown);
                    }
                });
            }
        });
        
        /*jQuery('.qmn_radio_answers > .qmn_mc_answer_wrap').on('click',function(event){
            var radButton = jQuery(this).find('input[type=radio]');                        
            if(event.target.className == 'qmn_quiz_radio'){
                return true;
            }
            if(radButton.is(':checked')){
              jQuery(radButton).prop("checked", false);
            } else {
              jQuery(radButton).prop("checked", true);
            }
        });*/
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
                        $this.next('.remove-uploaded-file').show();
                        $this.next().next('.mlw_file_upload_hidden_value').val(obj.file_url);
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
});

var qsmTimerInterval = setInterval( qmnTimeTakenTimer, 1000 );
