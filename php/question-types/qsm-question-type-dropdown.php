<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
} 

/**
 * This function shows the content of the multiple choice question.
 *
 * @params $id The ID of the multiple choice question
 * @params $question The question that is being edited.
 * @params @answers The array that contains the answers to the question.
 *
 * @since 4.4.0
 */
function qmn_drop_down_display( $id, $question, $answers ) {
	global $mlwQuizMasterNext;
	$required = $mlwQuizMasterNext->pluginHelper->get_question_setting( $id, 'required' );
	if ( 0 == $required ) {
		$require_class = 'qsmRequiredSelect';
	} else {
		$require_class = '';
	}
	$new_question_title = $mlwQuizMasterNext->pluginHelper->get_question_setting( $id, 'question_title' );
	qsm_question_title_func( $question, '', $new_question_title, $id );
	?>
	<select class="qsm_select qsm_dropdown <?php echo esc_attr( $require_class ); ?>" name="question<?php echo esc_attr( $id ); ?>">
	<option disabled selected value><?php echo esc_html__( 'Please select your answer', 'quiz-master-next' ); ?></option>
		<?php
		if ( is_array( $answers ) ) {
			$mlw_answer_total = 0;
			foreach ( $answers as $answer_index => $answer ) {
				$mlw_answer_total++;
				if ( '' !== $answer[0] ) {
					$answer_text = trim( htmlspecialchars_decode( $answer[0], ENT_QUOTES ) );
					$answer_text = $mlwQuizMasterNext->pluginHelper->qsm_language_support( $answer_text, "answer-" . $answer_text, "QSM Answers" );
					?>
					<option value="<?php echo esc_attr( $answer_index ); ?>"><?php echo esc_html( $answer_text ); ?></option>
					<?php
				}
			}
		}
		?>
 	</select>
	<?php
	echo apply_filters( 'qmn_drop_down_display_front', '', $id, $question, $answers );
}

/**
 * This function determines how the question is graded
 *
 * @params $id The ID of the multiple choice question
 * @params $question The question that is being edited.
 * @params @answers The array that contains the answers to the question.
 * @return $return_array Returns the graded question to the results page
 * @since  4.4.0
 */
function qmn_drop_down_review( $id, $question, $answers ) {
	$current_question               = new QSM_Question_Review_Choice( $id, $question, $answers );
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
	return apply_filters( 'qmn_drop_down_review', $return_array, $answers );
}