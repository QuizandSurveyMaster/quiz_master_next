//polar question type

(function ($) {
	let polarQuestions = jQuery('.question-type-polar-s');
	if(polarQuestions.length >0){
		let page = 'question';
		if(jQuery('body').hasClass('wp-admin')){
			page = 'admin'
		}
		qsmPolarSlider(page,polarQuestions);
	}
	jQuery(document).on('qsm_after_quiz_submit',function(event,quiz_form_id){
		event.preventDefault();
		let parentDivClass    = 'qsm-quiz-container-'+quiz_form_id.replace(new RegExp(/[a-zA-Z]/g),'');
		polarQuestions        = jQuery('.'+parentDivClass).find('.qmn_question_answer').find('.mlw_qmn_question').find('.question-type-polar-s');
		if(polarQuestions.length >0){
			qsmPolarSlider('answer', polarQuestions);
		}
		
	});

	function qsmPolarSlider(page , polarQuestions){
		polarQuestions.each( function(){
			let polarQuestion = jQuery(this).find('.slider-main-wrapper div');
			let questionID    = polarQuestion.attr('id').replace('slider-','');
			qsmPolarSliderEach(polarQuestion,questionID,page);
		});
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
		if ('answer'=== page || 'admin' === page){
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
				if('answer'=== page || 'admin' === page){
					return false;
				}
				else{
					jQuery('.question-section-id-'+questionID+'  .question-type-polar-s').find(
						'.ui-slider-handle').text( ui.value );
				}
			},
			change: function ( event, ui ){
				if('answer'!== page || 'admin' !== page){
					qsmPolarSliderQuestionChange(ui,questionID, answer1, answer2, value , isReverse );

				}
			},
			create: function (event, ui){
				if('answer'=== page){
					jQuery(document).trigger('qsm_after_display_result',[ this, ui ]);
					jQuery(this).find('a').css({'display':'flex','align-items':'center','justify-content':'center','text-decoration':'none','color':'white'});
					jQuery(this).find('a').html('<p style="margin:0;">'+value+'</p>');
				} else if ( 'admin' === page ) {
					jQuery(this).find('a').css({'display':'flex','align-items':'center','justify-content':'center','text-decoration':'none','color':'white'});
					jQuery(this).find('a').html('<p style="margin:0;">'+value+'</p>');
				} else {
					qsmPolarSliderQuestionCreate(questionID );
				}
				if ( isNaN(value) ){
					jQuery(this).find('a').hide();
				}
			}

		});
	}

	function qsmPolarSliderQuestionChange(ui,questionID, answer1, answer2, value , isReverse){
		jQuery('.question-section-id-'+questionID+'  .question-type-polar-s').find(
			'.qmn_polar').val(ui.value);
		jQuery('.question-section-id-'+questionID+'  .question-type-polar-s').find(
				'.ui-slider-handle').text(ui.value);
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
				'.left-polar-title img').css('opacity', " 1 ");
			jQuery('.question-section-id-'+questionID+'  .question-type-polar-s').find(
				'.right-polar-title').css('font-weight', '100');
			jQuery('.question-section-id-'+questionID+'  .question-type-polar-s').find(
				'.right-polar-title img').css('opacity', "0.4");
		} else if (ui.value == answer2 ) {
			jQuery('.question-section-id-'+questionID+'  .question-type-polar-s').find(
				'.left-polar-title').css('font-weight', '100');
			jQuery('.question-section-id-'+questionID+'  .question-type-polar-s').find(
				'.left-polar-title img').css('opacity', "0.4");
			jQuery('.question-section-id-'+questionID+'  .question-type-polar-s').find(
				'.right-polar-title').css('font-weight', '900');
			jQuery('.question-section-id-'+questionID+'  .question-type-polar-s').find(
				'.right-polar-title img').css('opacity', "1");
		} else if (ui.value == value) {
			jQuery('.question-section-id-'+questionID+'  .question-type-polar-s').find(
				'.left-polar-title').css('font-weight', '400');
			jQuery('.question-section-id-'+questionID+'  .question-type-polar-s').find(
				'.left-polar-title img').css('opacity', "0.5");
			jQuery('.question-section-id-'+questionID+'  .question-type-polar-s').find(
				'.right-polar-title').css('font-weight', '400');
			jQuery('.question-section-id-'+questionID+'  .question-type-polar-s').find(
				'.right-polar-title img').css('opacity', "0.5");
		} else if (ui.value < value ) {
			jQuery('.question-section-id-'+questionID+'  .question-type-polar-s').find(
				lowerMidClass).css('font-weight', '600');
			jQuery('.question-section-id-'+questionID+'  .question-type-polar-s').find(
				'.left-polar-title img').css('opacity', "0.8");
			jQuery('.question-section-id-'+questionID+'  .question-type-polar-s').find(
				upperMidClass).css('font-weight', '400');
			jQuery('.question-section-id-'+questionID+'  .question-type-polar-s').find(
				'.right-polar-title img').css('opacity', "0.5");
		} else if (ui.value > value ) {
			jQuery('.question-section-id-'+questionID+'  .question-type-polar-s').find(
				lowerMidClass).css('font-weight', '400');
			jQuery('.question-section-id-'+questionID+'  .question-type-polar-s').find(
				'.left-polar-title img').css('opacity', "0.5");
			jQuery('.question-section-id-'+questionID+'  .question-type-polar-s').find(
				upperMidClass).css('font-weight', '600');
			jQuery('.question-section-id-'+questionID+'  .question-type-polar-s').find(
				'.right-polar-title img').css('opacity', "0.8");	
		}
	}

	function qsmPolarSliderQuestionCreate(questionID){

		jQuery('.question-section-id-'+questionID+' .question-type-polar-s').find(
			'.left-polar-title').css('font-weight', '400');
		jQuery('.question-section-id-'+questionID+'  .question-type-polar-s img').find(
			'.left-polar-title img').css('opacity', "0.5");
		jQuery('.question-section-id-'+questionID+' .question-type-polar-s').find(
			'.right-polar-title').css('font-weight', '400');
		jQuery('.question-section-id-'+questionID+'  .question-type-polar-s img').find(
			'.right-polar-title img').css('opacity', "0.5");
	}
}(jQuery));
