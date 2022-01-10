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
function qmn_drop_down_display( $id, $question, $answers ) {
	global $mlwQuizMasterNext;
	$required = $mlwQuizMasterNext->pluginHelper->get_question_setting( $id, 'required' );
	if ( 0 == $required ) {
		$require_class = 'qsmRequiredSelect';
	} else {
		$require_class = '';
	}
	// $question_title = apply_filters('the_content', $question);
	$new_question_title = $mlwQuizMasterNext->pluginHelper->get_question_setting( $id, 'question_title' );
	qsm_question_title_func( $question, '', $new_question_title, $id );
	?>
	<select class="qsm_select qsm_dropdown <?php echo esc_attr( $require_class ); ?>" name="question<?php echo esc_attr( $id ); ?>"><option value=""><?php echo esc_html__( 'Please select your answer', 'quiz-master-next' ); ?></option>
		<?php
		if ( is_array( $answers ) ) {
			$mlw_answer_total = 0;
			foreach ( $answers as $answer ) {
				$mlw_answer_total++;
				if ( '' !== $answer[0] ) {
					?>
				<option value="<?php echo esc_attr( $answer[0] ); ?>"><?php echo esc_html( htmlspecialchars_decode( $answer[0], ENT_QUOTES ) ); ?></option>
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
	global $mlwQuizMasterNext;
	$answerEditor = $mlwQuizMasterNext->pluginHelper->get_question_setting( $id, 'answerEditor' );
	$return_array = array(
		'points'       => 0,
		'correct'      => 'incorrect',
		'user_text'    => '',
		'correct_text' => '',
	);
	//
	if ( isset( $_POST[ 'question' . $id ] ) ) {
		$mlw_user_answer = sanitize_text_field( wp_unslash( $_POST[ 'question' . $id ] ) );
		$mlw_user_answer = trim( stripslashes( htmlspecialchars_decode( $mlw_user_answer, ENT_QUOTES ) ) );
	} else {
		$mlw_user_answer = ' ';
	}
	foreach ( $answers as $answer ) {
		$answers_loop = trim( stripslashes( htmlspecialchars_decode( $answer[0], ENT_QUOTES ) ) );
		if ( $mlw_user_answer == $answers_loop ) {
			$return_array['points']    = $answer[1];
			$return_array['user_text'] = strval( htmlspecialchars_decode( $answer[0], ENT_QUOTES ) );
			if ( 1 == $answer[2] ) {
				$return_array['correct'] = 'correct';
			}
		}
		if ( 1 == $answer[2] ) {
			$return_array['correct_text'] = htmlspecialchars_decode( $answer[0], ENT_QUOTES );
		}
	}
	/**
	 * Hook to filter answers array
	 */
	return apply_filters( 'qmn_drop_down_review', $return_array, $answers );
}