<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
* 
*
* 
*/

/**
 * This function displays the fill in the blank question
 *
 * @params $id The ID of the multiple choice question
 * @params $question The question that is being edited.
 * @params @answers The array that contains the answers to the question.
 * @since  4.4.0
 */
function qmn_fill_blank_display( $id, $question, $answers ) {
	global $mlwQuizMasterNext;
	$required       = $mlwQuizMasterNext->pluginHelper->get_question_setting( $id, 'required' );
	$autofill       = $mlwQuizMasterNext->pluginHelper->get_question_setting( $id, 'autofill' );
	$limit_text     = $mlwQuizMasterNext->pluginHelper->get_question_setting( $id, 'limit_text' );
	$autofill_att   = $autofill ? "autocomplete='off' " : '';
	$limit_text_att = $limit_text ? "maxlength='" . $limit_text . "' " : '';
	if ( 0 == $required ) {
		$mlw_require_class = 'mlwRequiredText';
	} else {
		$mlw_require_class = '';
	}
	$input_text = '<input ' . $autofill_att . $limit_text_att . " type='text' class='qmn_fill_blank $mlw_require_class' name='question" . $id . "[]' />";
	if ( strpos( $question, '%BLANK%' ) !== false ) {
		$question = str_replace( '%BLANK%', $input_text, do_shortcode( htmlspecialchars_decode( $question, ENT_QUOTES ) ) );
	}
	// $question_title = apply_filters('the_content', $question);
	$new_question_title = $mlwQuizMasterNext->pluginHelper->get_question_setting( $id, 'question_title' );
	qsm_question_title_func( $question, '', $new_question_title, $id );
	echo apply_filters( 'qmn_fill_blank_display_front', '', $id, $question, $answers );
}

/**
 * This function determines how the fill in the blank question is graded.
 *
 * @params $id The ID of the multiple choice question
 * @params $question The question that is being edited.
 * @params @answers The array that contains the answers to the question.
 * @return $return_array Returns the graded question to the results page
 * @since  4.4.0
 */
function qmn_fill_blank_review( $id, $question, $answers ) {
	global $mlwQuizMasterNext;
	$match_answer = $mlwQuizMasterNext->pluginHelper->get_question_setting( $id, 'matchAnswer' );
	$return_array = array(
		'points'            => 0,
		'correct'           => 'incorrect',
		'user_text'         => '',
		'correct_text'      => '',
		'user_compare_text' => '',
	);
	if ( strpos( $question, '%BLANK%' ) !== false || strpos( $question, '%blank%' ) !== false ) {
		$return_array['question_text'] = str_replace( array( '%BLANK%', '%blank%' ), array( '__________', '__________' ), do_shortcode( htmlspecialchars_decode( $question, ENT_QUOTES ) ) );
	}
	$correct_text = $user_input = $user_text = array();

	if ( isset( $_POST[ 'question' . $id ] ) && ! empty( $_POST[ 'question' . $id ] ) ) {
		$question_input = array_map( 'sanitize_textarea_field', wp_unslash( $_POST[ 'question' . $id ] ) );

		foreach ( $question_input as $input ) {
			$decode_user_answer = strval( stripslashes( htmlspecialchars_decode( $input, ENT_QUOTES ) ) );
			$mlw_user_answer    = trim( preg_replace( '/\s\s+/', ' ', str_replace( "\n", ' ', $decode_user_answer ) ) );
			$user_input[]       = mb_strtoupper( $mlw_user_answer );
			$user_text[]        = $mlw_user_answer;
		}
	}

	$total_correct = $user_correct = 0;
	if ( 'sequence' === $match_answer ) {
		foreach ( $answers as $key => $answer ) {
			$decode_user_text = strval( htmlspecialchars_decode( $answer[0], ENT_QUOTES ) );
			$decode_user_text = trim( preg_replace( '/\s\s+/', ' ', str_replace( "\n", ' ', $decode_user_text ) ) );

			if ( mb_strtoupper( $decode_user_text ) == $user_input[ $key ] ) {
				$return_array['points'] += $answer[1];
				$user_correct           += 1;
			}
			$total_correct++;
			$correct_text[] = $answers[ $key ][0];
		}

		$return_array['correct_text'] = strval( htmlspecialchars_decode( implode( '.', $correct_text ), ENT_QUOTES ) );

		$return_array['user_text']         = implode( '.', $user_text );
		$return_array['user_compare_text'] = implode( '=====', $user_text );

		if ( $total_correct == $user_correct ) {
			$return_array['correct'] = 'correct';
		}
	} else {
		$answers_array = array();
		$correct       = true;
		//
		foreach ( $answers as $answer ) {
			$decode_user_text = strval( htmlspecialchars_decode( $answer[0], ENT_QUOTES ) );
			$decode_user_text = trim( preg_replace( '/\s\s+/', ' ', str_replace( "\n", ' ', $decode_user_text ) ) );
			$answers_array[]  = mb_strtoupper( $decode_user_text );
		}
		$total_user_input = sizeof( $user_input );
		$total_option     = sizeof( $answers );
		if ( $total_user_input < $total_option ) {
			foreach ( $user_input as $k => $input ) {
				$key = array_search( $input, $answers_array, true );
				if ( false !== $key ) {
					$return_array['points'] += $answers[ $key ][1];
				} else {
					$correct = false;
				}
				$correct_text[] = $answers[ $key ][0];
			}
		} else {
			foreach ( $answers_array as $k => $answer ) {
				$key = array_search( $answer, $user_input, true );
				if ( false !== $key ) {
					$return_array['points'] += $answers[ $k ][1];
				} else {
					$correct = false;
				}
				$correct_text[] = $answers[ $k ][0];
			}
		}
		if ( $correct ) {
			$return_array['correct'] = 'correct';
		}
		$return_array['user_text']         = implode( '.', $user_text );
		$return_array['correct_text']      = implode( '.', $correct_text );
		$return_array['user_compare_text'] = implode( '=====', $user_text );
	}

	/**
	 * Hook to filter answers array
	 */

	return apply_filters( 'qmn_fill_blank_review', $return_array, $answers );
}