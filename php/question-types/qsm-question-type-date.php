<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
} 

/**
 * This function shows the content of the date field
 *
 * @params $id The ID of the multiple choice question
 * @params $question The question that is being edited.
 * @params @answers The array that contains the answers to the question.
 *
 * @since 6.3.7
 */
function qmn_date_display( $id, $question, $answers ) {
	global $mlwQuizMasterNext;
	$required = $mlwQuizMasterNext->pluginHelper->get_question_setting( $id, 'required' );
	if ( 0 == $required ) {
		$mlw_require_class = 'mlwRequiredDate';
	} else {
		$mlw_require_class = '';
	}
	$new_question_title = $mlwQuizMasterNext->pluginHelper->get_question_setting( $id, 'question_title' );
	qsm_question_title_func( $question, '', $new_question_title, $id );
	?>
	<input type="date" class="mlw_answer_date <?php echo esc_attr( $mlw_require_class ); ?>" name="question<?php echo esc_attr( $id ); ?>" id="question<?php echo esc_attr( $id ); ?>" value="" />
	<?php
	echo apply_filters( 'qmn_date_display_front', '', $id, $question, $answers );
}

/**
 * This function reviews the date type.
 *
 * @params $id The ID of the multiple choice question
 * @params $question The question that is being edited.
 * @params @answers The array that contains the answers to the question.
 * @return $return_array Returns the graded question to the results page
 * @since  6.3.7
 */
function qmn_date_review( $id, $question, $answers ) {
	$current_question               = new QSM_Question_Review_Text( $id, $question, $answers );
	$user_text_array                = $current_question->get_user_answer();
	$correct_text_array             = $current_question->get_correct_answer();
	$return_array['user_text']      = ! empty( $user_text_array ) ? implode( '.', $user_text_array ) : '' ;
	$return_array['correct_text']   = ! empty( $correct_text_array ) ? implode( '.', $correct_text_array ) : '';
	$return_array['correct']        = $current_question->get_answer_status();
	$return_array['points']         = $current_question->get_points();
	$return_array['user_answer']    = $user_text_array;
	$return_array['correct_answer'] = $correct_text_array ;
	/**
	 * Hook to filter answers array
	 */
	return apply_filters( 'qmn_date_review', $return_array, $answers );
}