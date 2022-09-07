<?php
function qsm_tempvar_qa_text_qt_choice( $total_answers, $answers_from_response, $grading_system, $question_settings, $form_type = 0 ) {
	global $mlwQuizMasterNext;
	$question_with_answer_text = '';
	$class                     = '';
	$optin                     = '';
	$hide                      = '';
	foreach ( $total_answers as $single_answer_key => $single_answer ) {
		if ( 8 == $answers_from_response['question_type'] && 'not-opted' == $answers_from_response['user_compare_text'] ) {
			$class = 'not-opted';
		}
		if ( 8 == $answers_from_response['question_type'] ) {
			$quiz_options  = $mlwQuizMasterNext->quiz_settings->get_quiz_options();
			$settings_quiz = maybe_unserialize( $quiz_options->quiz_settings );
			$options_quiz  = maybe_unserialize( $settings_quiz['quiz_options'] );
			$optin         = $options_quiz['show_optin'];
			if ( 0 == $optin ) {
				$hide = 'hide';
			}
		}
		$user_answer_array = isset( $answers_from_response['user_answer'] ) && is_array( $answers_from_response['user_answer'] ) ? $answers_from_response['user_answer'] : array();
		$user_answer_keys  = ! empty( $user_answer_array ) ? array_keys( $user_answer_array ) : array();
		$is_answer_correct = false;
		$is_user_answer    = false;
		if ( 1 === intval( $single_answer[2] ) ) {
			$is_answer_correct = true;
		}
		if ( in_array( $single_answer_key, $user_answer_keys, true ) ) {
			$is_user_answer = true;
		}
		$image_class = '';
		$caption = '';
		if ( isset( $question_settings['answerEditor'] ) && 'image' === $question_settings['answerEditor'] ) {
			$size_style = '';
			if ( ! empty($question_settings['image_size-width']) ) {
				$size_style .= 'width:'.$question_settings['image_size-width'].'px !important;';
			}
			if ( ! empty($question_settings['image_size-height']) ) {
				$size_style .= ' height:'.$question_settings['image_size-height'].'px !important;';
			}
			if ( ! empty($single_answer[3]) ) {
				$caption = ' <span class="qsm_image_result_caption_default">'.$single_answer[3].'</span>';
			}
			$show_user_answer = '<img src="' . $mlwQuizMasterNext->pluginHelper->qsm_language_support( $single_answer[0], 'answer-' . $single_answer[0], 'QSM Answers' ) . '" style="' . esc_attr( $size_style ) . '" />';
			$image_class      = 'qmn_image_option';
		} else {
			$show_user_answer = $mlwQuizMasterNext->pluginHelper->qsm_language_support( $single_answer[0], 'answer-' . $single_answer[0], 'QSM Answers' );
			$image_class      = '';
		}
		$close_span = '</span>';
		if ( 0 == $form_type && ( 0 === intval( $grading_system ) || 3 === intval( $grading_system ) ) ) {
			if ( $is_user_answer && $is_answer_correct ) {
				$question_with_answer_text .= '<span class="qsm-text-correct-option qsm-text-user-correct-answer ' . $hide . ' ' . $class . ' ' . $image_class . '">' . $show_user_answer .$caption.$close_span;
			} elseif ( ! $is_user_answer && $is_answer_correct ) {
				$question_with_answer_text .= '<span class="qsm-text-correct-option ' . $hide . ' ' . $class . ' ' . $image_class . '">' . $show_user_answer .$caption.$close_span;
			} elseif ( $is_user_answer && ! $is_answer_correct ) {
				$question_with_answer_text .= '<span class="qsm-text-wrong-option ' . $hide . ' ' . $class . ' ' . $image_class . '">' . $show_user_answer .$caption.$close_span;
			} else {
				$question_with_answer_text .= '<span class="qsm-text-simple-option ' . $hide . ' ' . $class . ' ' . $image_class . '">' . $show_user_answer .$caption.$close_span;
			}
		} else {
			if ( $is_user_answer ) {
				$question_with_answer_text .= '<span class="qsm-text-correct-option ' . $hide . ' ' . $class . ' ' . $image_class . '">' . $show_user_answer .$caption.$close_span;
			} else {
				$question_with_answer_text .= '<span class="qsm-text-simple-option ' . $hide . ' ' . $class . ' ' . $image_class . '">' . $show_user_answer .$caption.$close_span;
			}
		}
	}
	return $question_with_answer_text;
}
