//Global Variables
var qmn_timer_activated = false;

function qmnTimeTakenTimer() {
	var x = +document.getElementById("timer").value;
	x = x + 1;
	document.getElementById("timer").value = x;
}
function qmnClearField(field) {
	if (field.defaultValue == field.value) field.value = '';
}

function qmnReturnToTop() {
	jQuery('html, body').animate({scrollTop: jQuery('.qmn_quiz_container').offset().top - 150}, 1000);
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
			if ( window.sessionStorage.getItem( 'mlw_time_quiz' + qmn_quiz_id ) === null ||
			window.sessionStorage.getItem( 'mlw_time_quiz'+qmn_quiz_id ) > 0.08 ) {

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
				if( jQuery( this ).attr( 'class' ).indexOf( 'mlwRequiredAccept' ) > -1 && !this.checked ) {
					qmnDisplayError( empty_error, jQuery( this ), quiz_form_id );
					result =  false;
				}
				if( jQuery( this ).attr( 'class' ).indexOf( 'mlwRequiredRadio' ) > -1 ) {
					check_val = jQuery( this ).find( 'input:checked' ).val();
					if ( check_val == "No Answer Provided" ) {
						qmnDisplayError( empty_error, jQuery( this ), quiz_form_id );
						mlw_validateResult =  false;
					}
				}
				if( jQuery( this ).attr( 'class' ).indexOf( 'mlwRequiredCheck' ) > -1 ) {
					if ( ! jQuery( this ).find( 'input:checked' ).length ) {
						qmnDisplayError( empty_error, jQuery( this ), quiz_form_id );
						result =  false;
					}
				}
			}
		}
	});
	return result;
}

function qmnFormSubmit( quiz_form_id ) {
	var container = jQuery( '#' + quiz_form_id ).closest( '.qmn_quiz_container' );
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

	jQuery.post( qmn_ajax_object.ajaxurl, data, function( response ) {
		qmnDisplayResults( JSON.parse( response ), quiz_form_id, container );
	});

	return false;
}

function qmnDisplayResults( results, quiz_form_id, container ) {
	jQuery( '#' + quiz_form_id ).hide();
	if ( results.redirect ) {
		window.location.replace( results.redirect_url );
	} else {
		container.append( '<div class="qmn_results_page"></div>' );
		container.find( '.qmn_results_page' ).html( results.display );
		qmnReturnToTop();
	}
}

function qmnInit() {
	for ( var key in qmn_quiz_data ) {
		if ( qmn_quiz_data[key].ajax_show_correct === '0' ) {
			jQuery( '#quizForm ' + qmn_quiz_data[key].quiz_id + ' .qmn_quiz_radio').change(function() {
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

		if ( qmn_quiz_data[key].disable_answer === '0' ) {
			jQuery( '#quizForm ' + qmn_quiz_data[key].quiz_id + ' .qmn_quiz_radio').change(function() {
				var radio_group = jQuery(this).attr('name');
				jQuery('input[type=radio][name='+radio_group+']').prop('disabled',true);
			});
		}

		if (typeof qmnInitPagination === "function") {
	    qmnInitPagination( qmn_quiz_data[key].quiz_id );
		}

		if (typeof qmnInitTimer === "function") {
	    qmnInitTimer( qmn_quiz_data[key].quiz_id );
		}
	}
}


function qmnActivateTimer() {
	jQuery("#mlw_qmn_timer").show();
	qmn_timer_activated = true;
	var minutes = 0;
	if (window.sessionStorage.getItem('mlw_started_quiz'+qmn_quiz_id) == "yes" && window.sessionStorage.getItem('mlw_time_quiz'+qmn_quiz_id) >= 0)
	{
		minutes = window.sessionStorage.getItem('mlw_time_quiz'+qmn_quiz_id);
	}
	else
	{
		minutes = qmn_timer_limit;
	}
	window.amount = (minutes*60);
	window.titleText = window.document.title;
	document.getElementById("mlw_qmn_timer").innerHTML = qmnMinToSec(window.amount);
	window.counter=setInterval(qmnTimer, 1000);
}

function qmnTimer() {
	window.amount=window.amount-1;
	if (window.amount < 0)
	{
		window.amount = 0;
	}
	window.sessionStorage.setItem('mlw_time_quiz'+qmn_quiz_id, window.amount/60);
	window.sessionStorage.setItem('mlw_started_quiz'+qmn_quiz_id, "yes");
	document.getElementById("mlw_qmn_timer").innerHTML = qmnMinToSec(window.amount);
	window.document.title = qmnMinToSec(window.amount) + " " + window.titleText;
	if (window.amount <= 0)
	{
		clearInterval(window.counter);
		jQuery( ".mlw_qmn_quiz input:radio" ).attr('disabled',true);
		jQuery( ".mlw_qmn_quiz input:checkbox" ).attr('disabled',true);
		jQuery( ".mlw_qmn_quiz select" ).attr('disabled',true);
		jQuery( ".mlw_qmn_question_comment" ).attr('disabled',true);
		jQuery( ".mlw_answer_open_text" ).attr('disabled',true);
		jQuery( ".mlw_answer_number" ).attr('disabled',true);
		//document.quizForm.submit();
		return;
	}
}

function qmnEndTimer() {
	window.sessionStorage.setItem('mlw_time_quiz'+qmn_quiz_id, 'completed');
	window.sessionStorage.setItem('mlw_started_quiz'+qmn_quiz_id, 'no');
}

function qmnInitTimer( quiz_id ) {
	if (typeof qmnInitPagination === "function" && firstPage) {
		jQuery(".mlw_next").click(function(event) {
			event.preventDefault();
			if ( !qmn_timer_activated ) {
				qmnActivateTimer();
			}
		});
	} else {
		setTimeout(qmnActivateTimer, 100);
	}
}

function qmnMinToSec( amount ) {
	var timer_display = '';
	var hours = Math.floor(amount/3600);
	if (hours == '0')
	{
		timer_display = timer_display +"00:";
	}
	else if (hours < 10)
	{
		timer_display = timer_display + '0' + hours + ":";
	}
	else
	{
		timer_display = timer_display + hours + ":";
	}
	var minutes = Math.floor((amount % 3600)/60);
	if (minutes == '0')
	{
		timer_display = timer_display +"00:";
	}
	else if (minutes < 10)
	{
		timer_display = timer_display + '0' + minutes + ":";
	}
	else
	{
		timer_display = timer_display + minutes + ":";
	}
	var seconds = Math.floor(amount % 60);
	if (seconds == '0')
	{
		timer_display = timer_display +"00";
	}
	else if (seconds < 10)
	{
		timer_display = timer_display +'0' + seconds;
	}
	else
	{
		timer_display = timer_display + seconds;
	}
	return timer_display;
}


//Function to validate the answers provided in quiz
function qmnValidatePage( quiz_form_id ) {
	var slide_number = jQuery( quiz_form_id ).closest( '.qmn_quiz_container' ).find( '.slide_number_hidden' ).val();
	var result = qmnValidation( quiz_form_id + " .quiz_section.slide" + slide_number + ' *' );
	return result;
}


//Function to advance quiz to next page
function nextSlide( pagination, go_to_top, quiz_form_id ) {
	var slide_number = jQuery( quiz_form_id ).closest( '.qmn_quiz_container' ).find( '.slide_number_hidden' ).val();
	var previous = jQuery( quiz_form_id ).closest( '.qmn_quiz_container' ).find( '.previous_amount_hidden' ).val();
	jQuery( quiz_form_id +" .quiz_section" ).hide();
	for ( var i = 0; i < pagination; i++ ) {
		if (i == 0 && previous == 1 && slide_number > 1) {
			slide_number = slide_number + pagination;
		} else {
			slide_number++;
		}
		if (slide_number < 1) {
			slide_number = 1;
		}
		jQuery( quiz_form_id + " .mlw_qmn_quiz_link.mlw_previous" ).hide();

    if (firstPage) {
      if (slide_number > 1) {
				jQuery( quiz_form_id + " .mlw_qmn_quiz_link.mlw_previous" ).show();
      }
    } else {
			if (slide_number > pagination) {
				jQuery( quiz_form_id + " .mlw_qmn_quiz_link.mlw_previous" ).show();
			}
    }
		if (slide_number == qmn_section_total) {
			jQuery( quiz_form_id + " .mlw_qmn_quiz_link.mlw_next" ).hide();
		}
		if (slide_number < qmn_section_total) {
			jQuery( quiz_form_id + " .mlw_qmn_quiz_link.mlw_next" ).show();
		}
		jQuery( quiz_form_id + " .quiz_section.slide" + slide_number ).show();
	}

	jQuery( quiz_form_id ).closest( '.qmn_quiz_container' ).find( '.slide_number_hidden' ).val( slide_number );
	jQuery( quiz_form_id ).closest( '.qmn_quiz_container' ).find( '.previous_amount_hidden' ).val( 0 );

	qmnUpdatePageNumber( 1, quiz_form_id );

	if (go_to_top == 1) {
		qmnReturnToTop();
	}
}

function prevSlide( pagination, go_to_top, quiz_form_id ) {
	var slide_number = jQuery( quiz_form_id ).closest( '.qmn_quiz_container' ).find( '.slide_number_hidden' ).val();
	var previous = jQuery( quiz_form_id ).closest( '.qmn_quiz_container' ).find( '.previous_amount_hidden' ).val();

	jQuery( quiz_form_id + " .quiz_section" ).hide();
	for (var i = 0; i < pagination; i++) {
		if (i == 0 && previous == 0)	{
			slide_number = slide_number - pagination;
		} else {
			slide_number--;
		}
		if (slide_number < 1) {
			slide_number = 1;
		}

		jQuery( quiz_form_id + " .mlw_qmn_quiz_link.mlw_previous" ).hide();

		if (firstPage) {
			if (slide_number > 1) {
				jQuery( quiz_form_id + " .mlw_qmn_quiz_link.mlw_previous" ).show();
			}
		} else {
			if (slide_number > pagination) {
				jQuery( quiz_form_id + " .mlw_qmn_quiz_link.mlw_previous" ).show();
			}
		}
		if (slide_number == qmn_section_total) {
			jQuery( quiz_form_id + " .mlw_qmn_quiz_link.mlw_next" ).hide();
		}
		if (slide_number < qmn_section_total) {
			jQuery( quiz_form_id + " .mlw_qmn_quiz_link.mlw_next" ).show();
		}
		jQuery( quiz_form_id + " .quiz_section.slide" + slide_number ).show();
	}

	qmnUpdatePageNumber( -1, quiz_form_id );

	jQuery( quiz_form_id ).closest( '.qmn_quiz_container' ).find( '.slide_number_hidden' ).val( slide_number );
	jQuery( quiz_form_id ).closest( '.qmn_quiz_container' ).find( '.previous_amount_hidden' ).val( 0 );

	if (go_to_top == 1) {
		qmnReturnToTop();
	}
}

function qmnUpdatePageNumber( amount, quiz_form_id ) {
	var current_page = +jQuery( quiz_form_id ).closest( '.qmn_quiz_container' ).find( '.current_page_hidden' ).val();
	var total_pages = jQuery( quiz_form_id ).closest( '.qmn_quiz_container' ).find( '.total_pages_hidden' ).val();
	current_page += amount;
	jQuery( quiz_form_id + " .qmn_page_counter_message" ).text( current_page + "/" + total_pages );
}

function qmnInitPagination( quiz_id ) {

	var qmn_section_total = qmn_quiz_data[quiz_id].pagination.total_questions + 1;
	if ( qmn_quiz_data[quiz_id].pagination.section_comments === '0' ) {
		qmn_section_total += 1;
	}
	var qmn_total_pages = Math.ceil( qmn_section_total / qmn_quiz_data[quiz_id].pagination.amount );
	if ( qmn_quiz_data[quiz_id].first_page ) {
		qmn_total_pages += 1;
		qmn_section_total += 1;
	}

	if ( qmn_quiz_data[quiz_id].first_page ) {
	  nextSlide(1, 0, '.qmn_quiz_form');
	} else {
	  nextSlide(qmn_pagination, 0, '.qmn_quiz_form');
	}

	jQuery( '#quizForm' + quiz_id + ' .quiz_section' ).hide();
	jQuery( '#quizForm' + quiz_id + ' .quiz_section' ).append( "<br />" );
	jQuery( '#quizForm' + quiz_id + ' .qmn_quiz_container' ).append( '<div class="qmn_pagination border margin-bottom"></div>' );
	jQuery( '#quizForm' + quiz_id + ' .qmn_pagination' ).append( '<input type="hidden" value="0" name="slide_number" class="slide_number_hidden" />');
	jQuery( '#quizForm' + quiz_id + ' .qmn_pagination' ).append( '<input type="hidden" value="0" name="current_page" class="current_page_hidden" />');
	jQuery( '#quizForm' + quiz_id + ' .qmn_pagination' ).append( '<input type="hidden" value="' + qmn_total_pages + '" name="total_pages" class="total_pages_hidden" />');
	jQuery( '#quizForm' + quiz_id + ' .qmn_pagination' ).append( '<input type="hidden" value="' + qmn_section_total + '" name="total_sections" class="total_sections_hidden" />');
	jQuery( '#quizForm' + quiz_id + ' .qmn_pagination' ).append( '<input type="hidden" value="0" name="previous_amount" class="previous_amount_hidden" />');
	jQuery( '#quizForm' + quiz_id + ' .qmn_pagination' ).append( '<a class="qmn_btn mlw_qmn_quiz_link mlw_previous" href="#">'+qmn_pagination_previous_text+'</a>' );
	jQuery( '#quizForm' + quiz_id + ' .qmn_pagination' ).append( '<span class="qmn_page_message"></span>' );
	jQuery( '#quizForm' + quiz_id + ' .qmn_pagination' ).append( '<div class="qmn_page_counter_message"></div>' );
	jQuery( '#quizForm' + quiz_id + ' .qmn_pagination' ).append( '<a class="qmn_btn mlw_qmn_quiz_link mlw_next" href="#">'+qmn_pagination_next_text+'</a>' );
}

function mlw_qmn_share(network, mlw_qmn_social_text, mlw_qmn_title, facebook_id)
{
	var sTop = window.screen.height/2-(218);
	var sLeft = window.screen.width/2-(313);
	var sqShareOptions = "height=400,width=580,toolbar=0,status=0,location=0,menubar=0,directories=0,scrollbars=0,top=" + sTop + ",left=" + sLeft;
	var pageUrl = window.location.href;
	var pageUrlEncoded = encodeURIComponent(pageUrl);
	if (network == 'facebook')
	{
		var Url = "https://www.facebook.com/dialog/feed?"
			+ "display=popup&"
			+ "app_id="+facebook_id+"&"
			+ "link=" + pageUrlEncoded + "&"
			+ "name=" + encodeURIComponent(mlw_qmn_social_text) + "&"
			+ "description=  &"
			+ "redirect_uri=http://www.mylocalwebstop.com/mlw_qmn_close.html";
	}
	if (network == 'twitter')
	{
		var Url = "https://twitter.com/intent/tweet?text=" + encodeURIComponent(mlw_qmn_social_text);
	}
	window.open(Url, "Share", sqShareOptions);
	return false;
}


jQuery(".mlw_next").click(function(event) {
	event.preventDefault();
	var quiz_form_id = '#' + jQuery( this ).closest( '.qmn_quiz_container' ).find( '.qmn_quiz_form' ).attr( 'id' );
	if ( qmnValidatePage( quiz_form_id ) ) {
		nextSlide(qmn_pagination, 1, quiz_form_id);
	}
});

jQuery(".mlw_previous").click(function(event) {
	event.preventDefault();
	var quiz_form_id = '#' + jQuery( this ).closest( '.qmn_quiz_container' ).find( '.qmn_quiz_form' ).attr( 'id' );
	prevSlide(qmn_pagination, 1, quiz_form_id);
});



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

var myVar = setInterval( qmnTimeTakenTimer, 1000 );
