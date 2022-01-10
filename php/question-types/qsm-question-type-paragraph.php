<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
* 
*
* 
*/

/**
 * This function displays the content of the large open question.
 *
 * @params $id The ID of the multiple choice question
 * @params $question The question that is being edited.
 * @params @answers The array that contains the answers to the question.
 *
 * @since 4.4.0
 */
function qmn_large_open_display( $id, $question, $answers ) {
	global $mlwQuizMasterNext;
	$required   = $mlwQuizMasterNext->pluginHelper->get_question_setting( $id, 'required' );
	$limit_text = $mlwQuizMasterNext->pluginHelper->get_question_setting( $id, 'limit_text' );
	if ( 0 == $required ) {
		$mlw_require_class = 'mlwRequiredText';
	} else {
		$mlw_require_class = '';
	}
	$limit_text_att = $limit_text ? "maxlength='" . $limit_text . "' " : '';
	// $question_title = apply_filters('the_content', $question);
	$new_question_title = $mlwQuizMasterNext->pluginHelper->get_question_setting( $id, 'question_title' );
	qsm_question_title_func( $question, '', $new_question_title, $id );
	?>
	<textarea class="mlw_answer_open_text <?php echo esc_attr( $mlw_require_class ); ?>" <?php echo esc_attr( $limit_text_att ); ?> cols="70" rows="5" name="question<?php echo esc_attr( $id ); ?>" /></textarea>
	<?php
	echo apply_filters( 'qmn_large_open_display_front', '', $id, $question, $answers );
}

/**
 * This function determines how the large open question is graded
 *
 * @params $id The ID of the multiple choice question
 * @params $question The question that is being edited.
 * @params @answers The array that contains the answers to the question.
 * @return $return_array Returns the graded question to the results page
 * @since  4.4.0
 */
function qmn_large_open_review( $id, $question, $answers ) {
	$return_array = array(
		'points'       => 0,
		'correct'      => 'incorrect',
		'user_text'    => '',
		'correct_text' => '',
	);
	//
	if ( isset( $_POST[ 'question' . $id ] ) ) {
		$question_input     = sanitize_textarea_field( wp_unslash( $_POST[ 'question' . $id ] ) );
		$decode_user_answer = strval( htmlspecialchars_decode( $question_input, ENT_QUOTES ) );
		$mlw_user_answer    = trim( str_replace( ' ', '', preg_replace( '/\s\s+/', '', $decode_user_answer ) ) );
	} else {
		$mlw_user_answer = ' ';
	}
	$return_array['user_text'] = $decode_user_answer;
	foreach ( $answers as $answer ) {
		$return_array['correct_text'] = $decode_correct_text = strval( htmlspecialchars_decode( $answer[0], ENT_QUOTES ) );
		$decode_correct_text          = trim( str_replace( ' ', '', preg_replace( '/\s\s+/', '', $decode_correct_text ) ) );
		if ( mb_strtoupper( $mlw_user_answer ) == mb_strtoupper( $decode_correct_text ) ) {
			$return_array['correct'] = 'correct';
			$return_array['points']  = $answer[1];
			break;
		}
	}
	/**
	 * Hook to filter answers array
	 */
	return apply_filters( 'qmn_large_open_review', $return_array, $answers );
}