<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
} 

/**
 * This function shows the content of the small open answer question.
 *
 * @params $id The ID of the multiple choice question
 * @params $question The question that is being edited.
 * @params @answers The array that contains the answers to the question.
 *
 * @since 4.4.0
 */
function qmn_small_open_display( $id, $question, $answers ) {
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
	// $question_title = apply_filters('the_content', $question);
	$new_question_title = $mlwQuizMasterNext->pluginHelper->get_question_setting( $id, 'question_title' );
	qsm_question_title_func( $question, '', $new_question_title, $id );
	?>
	<input <?php echo esc_attr( $autofill_att . $limit_text_att ); ?> type="text" class="mlw_answer_open_text <?php echo esc_attr( $mlw_require_class ); ?>" name="question<?php echo esc_attr( $id ); ?>" />
	<?php
	echo apply_filters( 'qmn_small_open_display_front', '', $id, $question, $answers );
}

/**
 * This function reviews the small open answer.
 *
 * @params $id The ID of the multiple choice question
 * @params $question The question that is being edited.
 * @params @answers The array that contains the answers to the question.
 * @return $return_array Returns the graded question to the results page
 * @since  4.4.0
 */
function qmn_small_open_review( $id, $question, $answers ) {
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
	return apply_filters( 'qmn_small_open_review', $return_array, $answers );
}