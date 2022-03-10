<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
} 

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
	// $question_title = apply_filters('the_content', $question);
	$new_question_title = $mlwQuizMasterNext->pluginHelper->get_question_setting( $id, 'question_title' );
	qsm_question_title_func( $question, '', $new_question_title, $id );
	?>
	<textarea class="mlw_answer_open_text <?php echo esc_attr( $mlw_require_class ); ?>" cols="70" rows="5" name="question<?php echo esc_attr( $id ); ?>" <?php if ( $limit_text ) : ?>maxlength="<?php echo esc_attr( $limit_text ); ?>"<?php endif; ?> /></textarea>
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
	return apply_filters( 'qmn_large_open_review', $return_array, $answers );
}