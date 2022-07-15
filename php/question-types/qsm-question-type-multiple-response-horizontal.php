<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
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
	$limit_multiple_response = $mlwQuizMasterNext->pluginHelper->get_question_setting( $id, 'limit_multiple_response' );
	$limit_mr_text           = '';
	if ( $limit_multiple_response > 0 ) {
		$limit_mr_text = 'onchange=qsmCheckMR(this,' . $limit_multiple_response . ')';
	}
	$new_question_title = $mlwQuizMasterNext->pluginHelper->get_question_setting( $id, 'question_title' );
	$answerEditor       = $mlwQuizMasterNext->pluginHelper->get_question_setting( $id, 'answerEditor' );
	$image_width = $mlwQuizMasterNext->pluginHelper->get_question_setting( $id, 'image_size-width' );
	$image_height = $mlwQuizMasterNext->pluginHelper->get_question_setting( $id, 'image_size-height' );
	qsm_question_title_func( $question, '', $new_question_title, $id );
	?>
	<div class="qmn_check_answers qmn_multiple_horizontal_check <?php echo esc_attr( $mlw_require_class ); ?>">
		<?php
		if ( is_array( $answers ) ) {
			$mlw_answer_total = 0;
			foreach ( $answers as $answer_index => $answer ) {
				$mlw_answer_total++;
				if ( '' !== $answer[0] ) {
					?>
				<span class="mlw_horizontal_multiple">
					<input type="checkbox" <?php echo esc_attr( $limit_mr_text ); ?> name="question<?php echo esc_attr( $id ) . '[]'; ?>" id="question<?php echo esc_attr( $id ) . '_' . esc_attr( $mlw_answer_total ); ?>" value="<?php echo esc_attr( $answer_index ); ?>" />
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
							<img alt="<?php echo esc_attr( $new_question_title ); ?>" src="<?php echo esc_url( trim( htmlspecialchars_decode( $answer[0], ENT_QUOTES ) ) ); ?>"  style="<?php echo esc_attr( $size_style ); ?>"  />
							<span class="qsm_image_caption">
								<?php
								$caption_text = trim( htmlspecialchars_decode( $answer[3], ENT_QUOTES ) );
								$caption_text = $mlwQuizMasterNext->pluginHelper->qsm_language_support( $caption_text, 'caption-' . $caption_text, 'QSM Answers' );
								echo esc_html( $caption_text );
								?>
							</span>
							<?php
						} else {
							$answer_text = trim( htmlspecialchars_decode( $answer[0], ENT_QUOTES ) );
							$answer_text = $mlwQuizMasterNext->pluginHelper->qsm_language_support( $answer_text, 'answer-' . $answer_text, 'QSM Answers' );
							echo do_shortcode( wp_kses_post( $answer_text ) );
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
	$current_question               = new QSM_Question_Review_Choice( $id, $question, $answers );
	$user_text_array                = $current_question->get_user_answer();
	$correct_text_array             = $current_question->get_correct_answer();
	$return_array['user_text']      = ! empty( $user_text_array ) ? implode( '.', $user_text_array ) : '';
	$return_array['correct_text']   = ! empty( $correct_text_array ) ? implode( '.', $correct_text_array ) : '';
	$return_array['correct']        = $current_question->get_answer_status();
	$return_array['points']         = $current_question->get_points();
	$return_array['user_answer']    = $user_text_array;
	$return_array['correct_answer'] = $correct_text_array;
	/**
	 * Hook to filter answers array
	 */
	return apply_filters( 'qmn_horizontal_multiple_response_review', $return_array, $answers );
}
