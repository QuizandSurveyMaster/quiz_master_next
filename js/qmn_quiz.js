function qmn_timer() {
	var x = +document.getElementById("timer").value;
	x = x + 1;
	document.getElementById("timer").value = x;
}
function clear_field(field) {
	if (field.defaultValue == field.value) field.value = '';
}

function qmn_display_error( message, field ) {
	jQuery( '#mlw_error_message' ).addClass( 'qmn_error_message' );
	jQuery( '#mlw_error_message_bottom' ).addClass( 'qmn_error_message' );
	jQuery( '.qmn_error_message' ).text( message );
	field.closest( '.qmn_section' ).addClass( 'qmn_error' );
}

function qmn_reset_error() {
	jQuery( '.qmn_error_message' ).text( '' );
	jQuery( '#mlw_error_message' ).removeClass( 'qmn_error_message' );
	jQuery( '#mlw_error_message_bottom' ).removeClass( 'qmn_error_message' );
}

function qmn_reset_field_error( field ) {
	field.closest( '.qmn_section' ).removeClass( 'qmn_error' );
}

function mlw_validateForm() {
	mlw_validateResult = true;

	jQuery('#quizForm *').each(function(){
		qmn_reset_field_error( jQuery( this ) );
		if (jQuery(this).attr('class')) {
			if(jQuery(this).attr('class').indexOf('mlwEmail') > -1 && this.value != "") {
				var x=this.value;
				var atpos=x.indexOf('@');
				var dotpos=x.lastIndexOf('.');
				if (atpos<1 || dotpos<atpos+2 || dotpos+2>=x.length) {
					qmn_display_error( email_error, jQuery(this) );
					mlw_validateResult =  false;
				}
			}
			if (window.sessionStorage.getItem('mlw_time_quiz'+qmn_quiz_id) == null || window.sessionStorage.getItem('mlw_time_quiz'+qmn_quiz_id) > 0.08) {

				if(jQuery(this).attr('class').indexOf('mlwRequiredNumber') > -1 && this.value == "" && +this.value != NaN) {
					qmn_display_error( number_error, jQuery(this) );
					mlw_validateResult =  false;
				}
				if(jQuery(this).attr('class').indexOf('mlwRequiredText') > -1 && this.value == "") {
					qmn_display_error( empty_error, jQuery(this) );
					mlw_validateResult =  false;
				}
				if(jQuery(this).attr('class').indexOf('mlwRequiredCaptcha') > -1 && this.value != mlw_code) {
					qmn_display_error( incorrect_error, jQuery(this) );
					mlw_validateResult =  false;
				}
				if(jQuery(this).attr('class').indexOf('mlwRequiredAccept') > -1 && !this.checked) {
					qmn_display_error( empty_error, jQuery(this) );
					mlw_validateResult =  false;
				}
				if(jQuery(this).attr('class').indexOf('mlwRequiredRadio') > -1) {
					check_val = jQuery(this).find('input:checked').val();
					if (check_val == "No Answer Provided") {
						qmn_display_error( empty_error, jQuery(this) );
						mlw_validateResult =  false;
					}
				}
				if(jQuery(this).attr('class').indexOf('mlwRequiredCheck') > -1) {
					if (!jQuery(this).find('input:checked').length) {
						qmn_display_error( empty_error, jQuery(this) );
						mlw_validateResult =  false;
					}
				}
			}
		}
	});

	if (!mlw_validateResult) {return mlw_validateResult;}

	jQuery( '.mlw_qmn_quiz input:radio' ).attr('disabled',false);
	jQuery( '.mlw_qmn_quiz input:checkbox' ).attr('disabled',false);
	jQuery( '.mlw_qmn_quiz select' ).attr('disabled',false);
	jQuery( '.mlw_qmn_question_comment' ).attr('disabled',false);
	jQuery( '.mlw_answer_open_text' ).attr('disabled',false);
}

jQuery( '.mlw_qmn_quiz' ).tooltip();
jQuery( '.mlw_qmn_quiz input' ).on( 'keypress', function (e) {
	if ( e.which === 13 ) {
		e.preventDefault();
	}
});
var myVar=setInterval("qmn_timer();",1000);

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
