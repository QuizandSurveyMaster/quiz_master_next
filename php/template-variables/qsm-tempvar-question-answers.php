<?php
function qsm_tempvar_qa_text_qt_choice( $total_answers, $answers_from_response, $grading_system, $question_settings, $form_type = 0 ) {
	global $mlwQuizMasterNext;
	$question_with_answer_text = '';
	$class                     = '';
	foreach ( $total_answers as $single_answer_key => $single_answer ) {
		if ( 8 == $answers_from_response['question_type'] && 'not-opted' == $answers_from_response['user_compare_text'] ) {
			$class = 'not-opted';
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
			$hide_correct_answer = $mlwQuizMasterNext->pluginHelper->get_section_setting( 'quiz_options', 'hide_correct_answer' );
			if ( $is_user_answer && $is_answer_correct ) {
				$question_with_answer_text .= '<span class="qsm-text-correct-option qsm-text-user-correct-answer ' .  $class . ' ' . $image_class . '">' . $show_user_answer .$caption.$close_span;
			} elseif ( ! $is_user_answer && $is_answer_correct && 1 != $hide_correct_answer ) {
				$question_with_answer_text .= '<span class="qsm-text-correct-option ' .  $class . ' ' . $image_class . '">' . $show_user_answer .$caption.$close_span;
			} elseif ( $is_user_answer && ! $is_answer_correct ) {
				$question_with_answer_text .= '<span class="qsm-text-wrong-option ' .  $class . ' ' . $image_class . '">' . $show_user_answer .$caption.$close_span;
			} else {
				$question_with_answer_text .= '<span class="qsm-text-simple-option ' .  $class . ' ' . $image_class . '">' . $show_user_answer .$caption.$close_span;
			}
		} else {
			if ( $is_user_answer ) {
				$question_with_answer_text .= '<span class="qsm-text-correct-option ' .  $class . ' ' . $image_class . '">' . $show_user_answer .$caption.$close_span;
			} else {
				$question_with_answer_text .= '<span class="qsm-text-simple-option ' .  $class . ' ' . $image_class . '">' . $show_user_answer .$caption.$close_span;
			}
		}
	}
	return $question_with_answer_text;
}
