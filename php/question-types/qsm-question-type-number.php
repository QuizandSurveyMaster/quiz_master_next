<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
* 
*
* 
*/

/**
 * This function shows the content of the multiple choice question.
 *
 * @params $id The ID of the multiple choice question
 * @params $question The question that is being edited.
 * @params @answers The array that contains the answers to the question.
 *
 * @since 4.4.0
 */
function qmn_number_display( $id, $question, $answers ) {
	global $mlwQuizMasterNext;
	$required       = $mlwQuizMasterNext->pluginHelper->get_question_setting( $id, 'required' );
	$limit_text     = $mlwQuizMasterNext->pluginHelper->get_question_setting( $id, 'limit_text' );
	$limit_text_att = $limit_text ? "maxlength='" . $limit_text . "' oninput='javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);'" : '';
	if ( 0 == $required ) {
		$mlw_require_class = 'mlwRequiredNumber';
	} else {
		$mlw_require_class = '';
	}
	// $question_title = apply_filters('the_content', $question);
	$new_question_title = $mlwQuizMasterNext->pluginHelper->get_question_setting( $id, 'question_title' );
	qsm_question_title_func( $question, '', $new_question_title, $id );
	?>
	<input type="number" <?php echo esc_attr( $limit_text_att ); ?> class="mlw_answer_number <?php echo esc_attr( $mlw_require_class ); ?>" name="question<?php echo esc_attr( $id ); ?>" />
	<?php
	echo apply_filters( 'qmn_number_display_front', '', $id, $question, $answers );
}

/**
 * This function determines how the number question type is graded.
 *
 * @params $id The ID of the multiple choice question
 * @params $question The question that is being edited.
 * @params @answers The array that contains the answers to the question.
 * @return $return_array Returns the graded question to the results page
 * @since  4.4.0
 */
function qmn_number_review( $id, $question, $answers ) {
	$return_array = array(
		'points'       => 0,
		'correct'      => 'incorrect',
		'user_text'    => '',
		'correct_text' => '',
	);
	//
	if ( isset( $_POST[ 'question' . $id ] ) ) {
		$question_input  = sanitize_textarea_field( wp_unslash( $_POST[ 'question' . $id ] ) );
		$mlw_user_answer = htmlspecialchars_decode( $question_input, ENT_QUOTES );
	} else {
		$mlw_user_answer = ' ';
	}
	$return_array['user_text'] = $mlw_user_answer;
	foreach ( $answers as $answer ) {
		$return_array['correct_text'] = strval( htmlspecialchars_decode( $answer[0], ENT_QUOTES ) );
		if ( strtoupper( $return_array['user_text'] ) == strtoupper( $return_array['correct_text'] ) ) {
			$return_array['correct'] = 'correct';
			$return_array['points']  = $answer[1];
			break;
		}
	}
	/**
	 * Hook to filter answers array
	 */
	return apply_filters( 'qmn_number_review', $return_array, $answers );
}