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

function qmn_multiple_choice_display( $id, $question, $answers ) {
	global $mlwQuizMasterNext;
	$answerEditor       = $mlwQuizMasterNext->pluginHelper->get_question_setting( $id, 'answerEditor' );
	$required           = $mlwQuizMasterNext->pluginHelper->get_question_setting( $id, 'required' );
	$new_question_title = $mlwQuizMasterNext->pluginHelper->get_question_setting( $id, 'question_title' );
	$image_width = $mlwQuizMasterNext->pluginHelper->get_question_setting( $id, 'image_size-width' );
	$image_height = $mlwQuizMasterNext->pluginHelper->get_question_setting( $id, 'image_size-height' );
	$mlw_class = '';
	$add_label = array();
	if ( 0 == $required ) {
		$mlw_class = 'mlwRequiredRadio';
	}
	$mlw_class = apply_filters( 'qsm_multiple_choice_classes', $mlw_class, $id );
	// $question_title = apply_filters('the_content', $question);
	qsm_question_title_func( $question, 'multiple_choice', $new_question_title, $id );
	?>
	<fieldset>
		<legend></legend>
	<div class='qmn_radio_answers <?php echo esc_attr( $mlw_class ); ?>'>
		<?php
		if ( is_array( $answers ) ) {
			$mlw_answer_total = 0;
			foreach ( $answers as $answer_index => $answer ) {
				$add_label  = apply_filters( 'qsm_question_addlabel',$answer_index,$answer,count($answers));
				$add_label_value = isset($add_label[ $answer_index ]) ? $add_label[ $answer_index ] : '';
				$mrq_checkbox_class = '';
				if ( empty( $add_label_value ) ) {
					$mrq_checkbox_class = "mrq_checkbox_class";
				}
				$mlw_answer_total++;
				$other_option_class = '';
				$other_option_class = apply_filters( 'qsm_multiple_choice_other_option_classes', $other_option_class, $mlw_answer_total, $id, $answers );
				if ( '' !== $answer[0] ) {
					$answer_class = apply_filters( 'qsm_answer_wrapper_class', '', $answer, $id );
					if ( 'rich' === $answerEditor ) {
						?>
						<div class='qmn_mc_answer_wrap <?php echo esc_attr( $answer_class.' '.$mrq_checkbox_class ); ?>' id='question<?php echo esc_attr( $id ); ?>-<?php echo esc_attr( $mlw_answer_total ); ?>'>
						<?php
					} elseif ( 'image' === $answerEditor ) {
						?>
						<div class='qmn_mc_answer_wrap qmn_image_option <?php echo esc_attr( $answer_class.' '.$mrq_checkbox_class ); ?>' id='question<?php echo esc_attr( $id ); ?>-<?php echo esc_attr( $mlw_answer_total ); ?>'>
						<?php
					} else {
						?>
						<div class="qmn_mc_answer_wrap <?php echo esc_attr( $answer_class.' '.$mrq_checkbox_class ); ?>" id="question<?php echo esc_attr( $id ); ?>-<?php echo esc_attr( $mlw_answer_total ); ?> ">
						<?php
					}
					?>
					<input type='radio' class='qmn_quiz_radio qmn-multiple-choice-input <?php echo esc_attr( $other_option_class ); ?>' name="<?php echo esc_attr( 'question' . $id ); ?>" id="<?php echo esc_attr( 'question' . $id . '_' . $mlw_answer_total ); ?>" value="<?php echo esc_attr( $answer_index ); ?>" />
					<label class="qsm-input-label" for="<?php echo esc_attr( 'question' . $id . '_' . $mlw_answer_total ); ?>">
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
						<img class="qsm-multiple-choice-img" alt="<?php echo esc_attr( $new_question_title ); ?>" src="<?php echo esc_url( trim( htmlspecialchars_decode( $answer[0], ENT_QUOTES ) ) ); ?>"  style="<?php echo esc_attr( $size_style ); ?>" />
						<span class="qsm_image_caption">
							<?php
							$caption_text = trim( htmlspecialchars_decode($answer[3], ENT_QUOTES ) );
							$caption_text = $mlwQuizMasterNext->pluginHelper->qsm_language_support( $caption_text, 'caption-' . $id . '-' . $answer_index, 'QSM Answers' );
							echo  wp_kses_post( $add_label_value )." ".esc_html( $caption_text );
							?>
						</span>
						<?php
					} else {
						$answer_text = trim( htmlspecialchars_decode($add_label_value." ".$answer[0], ENT_QUOTES ) );
						$answer_text = $mlwQuizMasterNext->pluginHelper->qsm_language_support( $answer_text, 'answer-' . $id . '-' . $answer_index, 'QSM Answers' );
						echo wp_kses_post( do_shortcode($answer_text ) );
					}
					?>
					</label>
					<?php
					echo apply_filters( 'qsm_multiple_choice_display_loop', ' ', $id, $question, $answers );
					?>
				</div>
					<?php
				}
				//}
			}
			echo apply_filters( 'qsm_multiple_choice_display_after_loop', ' ', $id, $question, $answers );
			?>
			<label style="display: none !important;" for="<?php echo esc_attr( 'question' . $id . '_none' ); ?>"><?php esc_attr_e( 'None', 'quiz-master-next' ); ?></label>
			<input type="radio" style="display: none;" name="<?php echo esc_attr( 'question' . $id ); ?>" id="<?php echo esc_attr( 'question' . $id . '_none' ); ?>" checked="checked" value="" />
			<?php
		}
		?>
	</div>
	</fieldset>
	<?php
	echo apply_filters( 'qmn_multiple_choice_display_front', '', $id, $question, $answers );
}

/**
 * This function determines how the multiple choice question is graded.
 *
 * @params $id The ID of the multiple choice question
 * @params $question The question that is being edited.
 * @params @answers The array that contains the answers to the question.
 * @return $return_array Returns the graded question to the results page
 * @since  4.4.0
 */
function qmn_multiple_choice_review( $id, $question, $answers ) {
	$current_question               = new QSM_Question_Review_Choice( $id, $question, $answers );
	$current_question               = apply_filters( 'qmn_multiple_choice_review_before', $current_question, $id, $question, $answers );
	$user_text_array                = $current_question->get_user_answer();
	$correct_text_array             = $current_question->get_correct_answer();
	$return_array['user_answer']    = $user_text_array;
	$return_array['correct_answer'] = $correct_text_array;
	$return_array['user_text']      = ! empty( $user_text_array ) ? implode( ', ', $user_text_array ) : '';
	$return_array['correct_text']   = ! empty( $correct_text_array ) ? implode( ', ', $correct_text_array ) : '';
	$return_array['correct']        = $current_question->get_answer_status();
	$return_array['points']         = $current_question->get_points();
	return apply_filters( 'qmn_multiple_choice_review', $return_array, $answers );
}
