//polar question type

jQuery(document).ready(function() {
	qsmPolarSlider('question');
});
jQuery(document).on('qsm_after_quiz_submit',function(event,quiz_form_id){
	event.preventDefault();
	qsmPolarSlider('answer');
});

function qsmPolarSlider(page){
	let polarQuestions = jQuery('.question-type-polar-s');
	if (polarQuestions.length >0){
		polarQuestions.each( function(){			
			let polarQuestion= jQuery(this).find('.slider-main-wrapper div');
			let questionID = polarQuestion.attr('id').replace('slider-','');
			qsmPolarSliderEach(polarQuestion,questionID,page);
			if ('answer'=== page){
				let maxHeight = Math.max.apply(null, jQuery(".mlw-qmn-question-result-"+questionID+" >> div").map(
					function() {
						return jQuery(this).height();
					}).get());
				jQuery('.mlw-qmn-question-result-'+questionID).height(maxHeight);					
			} else {
				let maxHeight = Math.max.apply(null,
					jQuery(".question-section-id-"+questionID+" .question-type-polar-s > div").map(function() {
					return jQuery(this).height();
				}).get());
				jQuery('.question-section-id-'+questionID+' .question-type-polar-s').height(maxHeight);
			}
		});
	}	
}

function qsmPolarSliderEach(polarQuestion,questionID,page){
	let isReverse = Boolean(parseInt(polarQuestion.attr("data-is_reverse")));
	let answer1 = parseInt( polarQuestion.attr("data-answer1") );
	let answer2 = parseInt( polarQuestion.attr("data-answer2") );
	let max;
	let min;
	if (isReverse){
		max = answer1;
		min = answer2;
	} else {
		max = answer2;
		min = answer1;
	}
	let step = 1 ;
	let value;
	if ('answer'=== page){
		value = parseInt( polarQuestion.attr("data-answer_value") );
	} else { 
		value = parseInt((max-min)/2) + min ;
	}
	polarQuestion.slider({
		max: max,
		min: min,
		isRTL:isReverse,
		step: step,
		range: false,
		value: value,
		slide: function slider_slide(event, ui) {
			if('answer'=== page){
				return false;
			}			
		},
		change: function ( event, ui ){
			if('answer'!== page){
				qsmPolarSliderQuestionChange(ui,questionID, answer1, answer2, value , isReverse );
			}
		},
		create: function (event, ui){
			if('answer'=== page){
				jQuery(document).trigger('qsm_after_display_result',[ this, ui ]);
			} else {
				qsmPolarSliderQuestionCreate(questionID, '' );
			}
		}
	});
}

function qsmPolarSliderQuestionChange(ui,questionID, answer1, answer2, value , isReverse){
	jQuery('.question-section-id-'+questionID+'  .question-type-polar-s').find(
		'.qmn_polar').val(ui.value);
	let lowerMidClass = '.left-polar-title';
	let upperMidClass = '.right-polar-title';
	if (isReverse){
		lowerMidClass = '.right-polar-title';
		upperMidClass = '.left-polar-title';
	}
	if ( ui.value == answer1 ) {
		jQuery('.question-section-id-'+questionID+'  .question-type-polar-s').find(
			'.left-polar-title').css('font-weight', '900');
		jQuery('.question-section-id-'+questionID+'  .question-type-polar-s').find(
			'.right-polar-title').css('font-weight', '100');
	} else if (ui.value == answer2 ) {
		jQuery('.question-section-id-'+questionID+'  .question-type-polar-s').find(
			'.left-polar-title').css('font-weight', '100');
		jQuery('.question-section-id-'+questionID+'  .question-type-polar-s').find(
			'.right-polar-title').css('font-weight', '900');
	} else if (ui.value == value) {
		jQuery('.question-section-id-'+questionID+'  .question-type-polar-s').find(
			'.left-polar-title').css('font-weight', '400');
		jQuery('.question-section-id-'+questionID+'  .question-type-polar-s').find(
			'.right-polar-title').css('font-weight', '400');
	} else if (ui.value < value ) {
		jQuery('.question-section-id-'+questionID+'  .question-type-polar-s').find(
			lowerMidClass).css('font-weight', '600');
		jQuery('.question-section-id-'+questionID+'  .question-type-polar-s').find(
			upperMidClass).css('font-weight', '400');
	} else if (ui.value > value ) {
		jQuery('.question-section-id-'+questionID+'  .question-type-polar-s').find(
			lowerMidClass).css('font-weight', '400');
		jQuery('.question-section-id-'+questionID+'  .question-type-polar-s').find(
			upperMidClass).css('font-weight', '600');
	}
}

function qsmPolarSliderQuestionCreate(questionID, value){
	jQuery('.question-section-id-'+questionID+' .question-type-polar-s').find(
		'.left-polar-title').css('font-weight', '400');
	jQuery('.question-section-id-'+questionID+' .question-type-polar-s').find(
		'.right-polar-title').css('font-weight', '400');
	jQuery('.question-section-id-'+questionID+' .question-type-polar-s').find(
		'.qmn_polar').val(value);
}