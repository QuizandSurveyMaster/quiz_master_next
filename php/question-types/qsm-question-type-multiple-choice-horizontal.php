<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 *
 *
 *
 */

/**
 * This function shows the content of the horizontal multiple choice question.
 *
 * @params $id The ID of the multiple choice question
 * @params $question The question that is being edited.
 * @params @answers The array that contains the answers to the question.
 *
 * @since 4.4.0
 */
function qmn_horizontal_multiple_choice_display( $id, $question, $answers ) {
	global $mlwQuizMasterNext;
	$required = $mlwQuizMasterNext->pluginHelper->get_question_setting( $id, 'required' );
	$mlw_class = '';
	if ( 0 == $required ) {
		$mlw_class = 'mlwRequiredRadio';
	}
	$answers = apply_filters( 'qsm_horizontal_multiple_choice_display_before', $answers, $id, $question );
	$mlw_class = apply_filters( 'qsm_horizontal_multiple_choice_classes', $mlw_class, $id );
	$answerEditor       = $mlwQuizMasterNext->pluginHelper->get_question_setting( $id, 'answerEditor' );
	$new_question_title = $mlwQuizMasterNext->pluginHelper->get_question_setting( $id, 'question_title' );
	$image_width = $mlwQuizMasterNext->pluginHelper->get_question_setting( $id, 'image_size-width' );
	$image_height = $mlwQuizMasterNext->pluginHelper->get_question_setting( $id, 'image_size-height' );
	$answer_limit = $mlwQuizMasterNext->pluginHelper->get_question_setting( $id, 'answer_limit' );
	$limited_answers = ! empty( $answer_limit ) ? $mlwQuizMasterNext->pluginHelper->qsm_get_limited_options( $answers, intval($answer_limit) ) : $answers;
	$answers = isset( $limited_answers['final'] ) ? $limited_answers['final'] : $answers;
	$answer_limit_keys = isset( $limited_answers['answer_limit_keys'] ) ? $limited_answers['answer_limit_keys'] : '';
	qsm_question_title_func( $question, 'horizontal_multiple_choice', $new_question_title, $id );
	?>
	<fieldset>
		<legend></legend>
	<div class="qmn_radio_answers qmn_radio_horizontal_answers <?php echo esc_attr( $mlw_class ); ?>">
		<?php
		if ( is_array( $answers ) ) {
			$mlw_answer_total = 0;
			foreach ( $answers as $answer_index => $answer ) {
				$add_label  = apply_filters( 'qsm_question_addlabel',$answer_index,$answer,count($answers));
				$mrq_checkbox_class = '';
				$add_label_value = isset($add_label[ $answer_index ]) ? $add_label[ $answer_index ] : '';
				if ( empty( $add_label[ $answer_index ] ) ) {
					$mrq_checkbox_class = "mrq_checkbox_class";
				}
				$mlw_answer_total++;
				$other_option_class = '';
				$other_option_class = apply_filters( 'qsm_multiple_choice_other_option_classes', $other_option_class, $mlw_answer_total, $id, $answers );
				if ( '' !== $answer[0] ) {
					$answer_class = apply_filters( 'qsm_answer_wrapper_class', '', $answer, $id );
					$answer_class .= 'image' === $answerEditor ? ' qmn_image_option' : '';
					?>
					<span class="mlw_horizontal_choice <?php echo esc_attr( $answer_class.' '.$mrq_checkbox_class ); ?>">
						<input type="radio" class="qmn_quiz_radio qmn-multiple-choice-input <?php echo esc_attr( $other_option_class ); ?>" name="question<?php echo esc_attr( $id ); ?>" id="question<?php echo esc_attr( $id ) . '_' . esc_attr( $mlw_answer_total ); ?>" value="<?php echo esc_attr( $answer_index ); ?>" />
						<label class="qsm-input-label" for="question<?php echo esc_attr( $id ) . '_' . esc_attr( $mlw_answer_total ); ?>">
							<?php
							if ( 'image' === $answerEditor ) {
								$size_style = '';
								if ( ! empty($image_width) ) {
									$size_style .= 'width:'.$image_width.'px !important;';
								}
								if ( ! empty($image_height) ) {
									$size_style .= ' height:'.$image_height.'px !important;';
								}
								?>
								<img class="qsm-multiple-choice-horizontal-img" alt="<?php echo esc_attr( $new_question_title ); ?>" src="<?php echo esc_url( trim( htmlspecialchars_decode( $answer[0], ENT_QUOTES ) ) ); ?>" style="<?php echo esc_attr( $size_style ); ?>" />
								<span class="qsm_image_caption">
									<?php
									$caption_text = trim( htmlspecialchars_decode( $answer[3], ENT_QUOTES ) );
									$caption_text = $mlwQuizMasterNext->pluginHelper->qsm_language_support( $caption_text, 'caption-' . $id . '-' . $answer_index, 'QSM Answers' );
									echo  wp_kses_post( $add_label_value )." ".esc_html( $caption_text );
									?>
								</span>
								<?php
							} else {
								$answer_text = trim( htmlspecialchars_decode($add_label_value." ". $answer[0], ENT_QUOTES ) );
								$answer_text = $mlwQuizMasterNext->pluginHelper->qsm_language_support( $answer_text, 'answer-' . $id . '-' . $answer_index, 'QSM Answers' );
								echo wp_kses_post( do_shortcode( $answer_text ) );
							}
							?>
						</label>
						<?php
						echo apply_filters( 'qsm_multiple_choice_horizontal_display_loop', '', $id, $question, $answer, $mlw_answer_total );
						?>
					</span>
					<?php
				}
			}
			echo apply_filters( 'qmn_horizontal_multiple_choice_question_display', '', $id, $question, $answers );
			?>
			<label style="display: none !important;" for="<?php echo esc_attr( 'question' . $id . '_none' ); ?>"><?php esc_attr_e( 'None', 'quiz-master-next' ); ?></label>
			<input type="radio" style="display: none;" name="question<?php echo esc_attr( $id ); ?>" id="question<?php echo esc_attr( $id ); ?>_none" checked="checked" value="" />
			<?php
		}
		?>
	</div>
	</fieldset>
	<input type="hidden" name="answer_limit_keys_<?php echo esc_attr( $id ); ?>" value="<?php echo esc_attr( $answer_limit_keys ); ?>" />
	<?php
	echo apply_filters( 'qmn_horizontal_multiple_choice_display_front', '', $id, $question, $answers );
}

/**
 * This function determines how the question is graded.
 *
 * @params $id The ID of the multiple choice question
 * @params $question The question that is being edited.
 * @params @answers The array that contains the answers to the question.
 * @return $return_array Returns the graded question to the results page
 * @since  4.4.0
 */
function qmn_horizontal_multiple_choice_review( $id, $question, $answers ) {
	$current_question               = new QSM_Question_Review_Choice( $id, $question, $answers );
	$current_question               = apply_filters( 'qmn_multiple_choice_review_before', $current_question, $id, $question, $answers );
	$user_text_array                = $current_question->get_user_answer( 'single_response' );
	$correct_text_array             = $current_question->get_correct_answer();
	$return_array['user_text']      = ! empty( $user_text_array ) ? implode( ', ', $user_text_array ) : '';
	$return_array['correct_text']   = ! empty( $correct_text_array ) ? implode( ', ', $correct_text_array ) : '';
	$return_array['correct']        = $current_question->get_answer_status( 'single_response' );
	$return_array['points']         = $current_question->get_points();
	$return_array['user_answer']    = $user_text_array;
	$return_array['correct_answer'] = $correct_text_array;
	$return_array['answer_limit_keys'] = isset( $_POST[ 'answer_limit_keys_'.$id ] ) ? sanitize_text_field( wp_unslash( $_POST[ 'answer_limit_keys_'.$id ] ) ) : '';
	/**
	 * Hook to filter answers array
	 */
	return apply_filters( 'qmn_horizontal_multiple_choice_review', $return_array, $answers );
}
