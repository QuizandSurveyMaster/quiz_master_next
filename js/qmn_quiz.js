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

function qmnDisplayError( message, field ) {
	jQuery( '#mlw_error_message' ).addClass( 'qmn_error_message' );
	jQuery( '#mlw_error_message_bottom' ).addClass( 'qmn_error_message' );
	jQuery( '.qmn_error_message' ).text( message );
	field.closest( '.quiz_section' ).addClass( 'qmn_error' );
}

function qmnResetError() {
	jQuery( '.qmn_error_message' ).text( '' );
	jQuery( '#mlw_error_message' ).removeClass( 'qmn_error_message' );
	jQuery( '#mlw_error_message_bottom' ).removeClass( 'qmn_error_message' );
	jQuery( '.quiz_section' ).removeClass( 'qmn_error' );
}

function qmnValidation( element ) {
	var result = true;
	qmnResetError();
	jQuery( element ).each(function(){
		if ( jQuery( this ).attr( 'class' )) {
			if( jQuery( this ).attr( 'class' ).indexOf( 'mlwEmail' ) > -1 && this.value != "" ) {
				var x = this.value;
				var atpos = x.indexOf('@');
				var dotpos = x.lastIndexOf( '.' );
				if ( atpos < 1 || dotpos < atpos + 2 || dotpos + 2>= x.length ) {
					qmnDisplayError( email_error, jQuery( this ) );
					result = false;
				}
			}
			if ( window.sessionStorage.getItem( 'mlw_time_quiz' + qmn_quiz_id ) == null ||
			window.sessionStorage.getItem( 'mlw_time_quiz'+qmn_quiz_id ) > 0.08 ) {

				if( jQuery( this ).attr( 'class' ).indexOf( 'mlwRequiredNumber' ) > -1 && this.value == "" && this.value != NaN ) {
					qmnDisplayError( number_error, jQuery( this ) );
					result =  false;
				}
				if( jQuery( this ).attr( 'class' ).indexOf( 'mlwRequiredText' ) > -1 && this.value == "" ) {
					qmnDisplayError( empty_error, jQuery( this ) );
					result =  false;
				}
				if( jQuery( this ).attr( 'class' ).indexOf( 'mlwRequiredCaptcha' ) > -1 && this.value != mlw_code ) {
					qmnDisplayError( incorrect_error, jQuery( this ) );
					result =  false;
				}
				if( jQuery( this ).attr( 'class' ).indexOf( 'mlwRequiredAccept' ) > -1 && !this.checked ) {
					qmnDisplayError( empty_error, jQuery( this ) );
					result =  false;
				}
				if( jQuery( this ).attr( 'class' ).indexOf( 'mlwRequiredRadio' ) > -1 ) {
					check_val = jQuery( this ).find( 'input:checked' ).val();
					if ( check_val == "No Answer Provided" ) {
						qmnDisplayError( empty_error, jQuery( this ) );
						mlw_validateResult =  false;
					}
				}
				if( jQuery( this ).attr( 'class' ).indexOf( 'mlwRequiredCheck' ) > -1 ) {
					if ( ! jQuery( this ).find( 'input:checked' ).length ) {
						qmnDisplayError( empty_error, jQuery( this ) );
						result =  false;
					}
				}
			}
		}
	});
	return result;
}

function qmnFormSubmit() {
	var result = qmnValidation( '#quizForm *' );

	if ( ! result ) { return result; }

	jQuery( '.mlw_qmn_quiz input:radio' ).attr( 'disabled', false );
	jQuery( '.mlw_qmn_quiz input:checkbox' ).attr( 'disabled', false );
	jQuery( '.mlw_qmn_quiz select' ).attr( 'disabled', false );
	jQuery( '.mlw_qmn_question_comment' ).attr( 'disabled', false );
	jQuery( '.mlw_answer_open_text' ).attr( 'disabled', false );


	var data = {
		action: 'qmn_process_quiz',
		quizID: qmn_quiz_id,
		quizData: jQuery( '#quizForm' ).serialize()
	};

	jQuery.post( qmn_ajax_object.ajaxurl, data, function( response ) {
		qmnDisplayResults( JSON.parse( response ) );
	});

	return false;
}

function qmnDisplayResults( results ) {
	jQuery( '#quizForm' ).hide();
	if ( results.redirect ) {
		window.location.replace( results.redirect_url );
	} else {
		jQuery( '.qmn_quiz_container' ).append( '<div class="qmn_results_page"></div>' );
		jQuery( '.qmn_results_page' ).html( results.display );
		qmnReturnToTop();
	}
}

jQuery( '.mlw_qmn_quiz' ).tooltip();
jQuery( '.mlw_qmn_quiz input' ).on( 'keypress', function ( e ) {
	if ( e.which === 13 ) {
		e.preventDefault();
	}
});

jQuery( '.qmn_quiz_form' ).on( "submit", function( event ) {
  event.preventDefault();
	qmnFormSubmit();
});

var myVar=setInterval("qmnTimeTakenTimer();",1000);

if (qmn_ajax_correct) {
	jQuery('.qmn_quiz_radio').change(function() {
		var chosen_answer = jQuery(this).val();
		var question_id = jQuery(this).attr('name').replace(/question/i,'');
		var chosen_id = jQuery(this).attr('id');
		jQuery.each(qmn_question_list, function(i, value) {
			if (question_id == value.question_id) {
				jQuery.each(value.answers, function(j, answer) {
					if ( answer[0] === chosen_answer ) {
						if ( answer[2] !== 1) {
							jQuery('#'+chosen_id).parent().addClass("qmn_incorrect_answer");
						}
					}
					if ( answer[2] === 1) {
						jQuery(':radio[name=question'+question_id+'][value="'+answer[0]+'"]').parent().addClass("qmn_correct_answer");
					}
				});
			}
		});
	});
}

if (qmn_disable_answer) {
	jQuery('.qmn_quiz_radio').change(function() {
		var radio_group = jQuery(this).attr('name');
		jQuery('input[type=radio][name='+radio_group+']').prop('disabled',true);
	});
}
