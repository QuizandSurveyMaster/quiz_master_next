<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
* 
*
* 
*/

/**
 * This function displays the content of the multiple response question type
 *
 * @params $id The ID of the multiple choice question
 * @params $question The question that is being edited.
 * @params @answers The array that contains the answers to the question.
 *
 * @since 4.4.0
 */
function qmn_horizontal_multiple_response_display( $id, $question, $answers ) {
	global $mlwQuizMasterNext;
	$required = $mlwQuizMasterNext->pluginHelper->get_question_setting( $id, 'required' );
	if ( 0 == $required ) {
		$mlw_require_class = 'mlwRequiredCheck';
	} else {
		$mlw_require_class = '';
	}
	// $question_title = apply_filters('the_content', $question);
	$limit_multiple_response = $mlwQuizMasterNext->pluginHelper->get_question_setting( $id, 'limit_multiple_response' );
	$limit_mr_text           = '';
	if ( $limit_multiple_response > 0 ) {
		$limit_mr_text = 'onchange="qsmCheckMR(this,' . $limit_multiple_response . ')"';
	}
	$new_question_title = $mlwQuizMasterNext->pluginHelper->get_question_setting( $id, 'question_title' );
	$answerEditor       = $mlwQuizMasterNext->pluginHelper->get_question_setting( $id, 'answerEditor' );
	qsm_question_title_func( $question, '', $new_question_title, $id );
	?>
	<div class="qmn_check_answers qmn_multiple_horizontal_check <?php echo esc_attr( $mlw_require_class ); ?>">
		<?php
		if ( is_array( $answers ) ) {
			$mlw_answer_total = 0;
			foreach ( $answers as $answer ) {
				$mlw_answer_total++;
				if ( '' !== $answer[0] ) {
					?>
				<input type="hidden" name="question<?php echo esc_attr( $id ); ?> " value="This value does not matter" />
					<span class="mlw_horizontal_multiple"><input type="checkbox" <?php echo esc_attr( $limit_mr_text ); ?> name="question<?php echo esc_attr( $id ) . '_' . esc_attr( $mlw_answer_total ); ?>" id="question<?php echo esc_attr( $id ) . '_' . esc_attr( $mlw_answer_total ); ?>" value="<?php echo esc_attr( $answer[0] ); ?>" />
						<label for="question<?php echo esc_attr( $id ) . '_' . esc_attr( $mlw_answer_total ); ?>">
							<?php
							if ( 'image' === $answerEditor ) {
							?>
								<img alt="<?php echo esc_attr( $new_question_title ); ?>" src="<?php echo esc_url( trim( htmlspecialchars_decode( $answer[0], ENT_QUOTES ) ) ); ?>" />
								<?php
								} else {
									echo wp_kses_post( trim( do_shortcode( htmlspecialchars_decode( $answer[0], ENT_QUOTES ) ) ) );
								}
								?>
							</label>
					</span>
					<?php
				}
			}
		}
		?>
	</div>
	<?php
	echo apply_filters( 'qmn_horizontal_multiple_response_display_front', '', $id, $question, $answers );
}

/**
 * This function determines how the multiple response is graded.
 *
 * @params $id The ID of the multiple choice question
 * @params $question The question that is being edited.
 * @params @answers The array that contains the answers to the question.
 * @return $return_array Returns the graded question to the Results page
 * @since  4.4.0
 */
function qmn_horizontal_multiple_response_review( $id, $question, $answers ) {
	$return_array  = array(
		'points'            => 0,
		'correct'           => 'incorrect',
		'user_text'         => '',
		'correct_text'      => '',
		'user_compare_text' => '',
	);
	$user_correct  = 0;
	$total_correct = 0;
	$total_answers = count( $answers );
	$correct_text  = array();
	foreach ( $answers as $answer ) {
		for ( $i = 1; $i <= $total_answers; $i++ ) {
			if ( isset( $_POST[ 'question' . $id . '_' . $i ] ) && htmlspecialchars( sanitize_textarea_field( wp_unslash( $_POST[ 'question' . $id . '_' . $i ] ) ), ENT_QUOTES ) == esc_attr( $answer[0] ) ) {
				$return_array['points']            += $answer[1];
				$return_array['user_text']         .= strval( htmlspecialchars_decode( $answer[0], ENT_QUOTES ) ) . '.';
				$return_array['user_compare_text'] .= sanitize_textarea_field( strval( htmlspecialchars_decode( $answer[0], ENT_QUOTES ) ) ) . '=====';
				if ( 1 == $answer[2] ) {
					$user_correct += 1;
				} else {
					$user_correct = -1;
				}
			}
		}
		if ( 1 == $answer[2] ) {
			$correct_text[] = stripslashes( htmlspecialchars_decode( $answer[0], ENT_QUOTES ) );
			$total_correct++;
		}
	}
	if ( $user_correct == $total_correct ) {
		$return_array['correct'] = 'correct';
	}
	$return_array['correct_text'] = implode( '.', $correct_text );
	/**
	 * Hook to filter answers array
	 */
	return apply_filters( 'qmn_horizontal_multiple_response_review', $return_array, $answers );
}
