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
	if ( 0 == $required ) {
		$mlw_require_class = 'mlwRequiredRadio';
	} else {
		$mlw_require_class = '';
	}
	// $question_title = apply_filters('the_content', $question);
	qsm_question_title_func( $question, 'multiple_choice', $new_question_title, $id );
	?>
	<div class='qmn_radio_answers <?php echo esc_attr( $mlw_require_class ); ?>'>
		<?php
		if ( is_array( $answers ) ) {
			$mlw_answer_total = 0;
			foreach ( $answers as $answer_index => $answer ) {
				$mlw_answer_total++;
				if ( '' !== $answer[0] ) {
					$answer_class = apply_filters( 'qsm_answer_wrapper_class', '', $answer, $id );
					if ( 'rich' === $answerEditor ) {
						?>
						<div class='qmn_mc_answer_wrap <?php echo esc_attr( $answer_class ); ?>' id='question<?php echo esc_attr( $id ); ?>-<?php echo esc_attr( $mlw_answer_total ); ?>'>
						<?php
					} elseif ( 'image' === $answerEditor ) {
						?>
						<div class='qmn_mc_answer_wrap qmn_image_option <?php echo esc_attr( $answer_class ); ?>' id='question<?php echo esc_attr( $id ); ?>-<?php echo esc_attr( $mlw_answer_total ); ?>'>
						<?php
					} else {
						?>
						<div class="qmn_mc_answer_wrap <?php echo esc_attr( $answer_class ); ?>" id="question<?php echo esc_attr( $id ); ?>-<?php echo esc_attr( $mlw_answer_total ); ?> ">
						<?php
					}
					?>
					<input type='radio' class='qmn_quiz_radio' name="<?php echo esc_attr( 'question' . $id ); ?>" id="<?php echo esc_attr( 'question' . $id . '_' . $mlw_answer_total ); ?>" value="<?php echo esc_attr( $answer_index ); ?>" />
					<label class="qsm-input-label" for="<?php echo esc_attr( 'question' . $id . '_' . $mlw_answer_total ); ?>">
					<?php
					if ( 'image' === $answerEditor ) {
						?>
						<img alt="<?php echo esc_attr( $new_question_title ); ?>" src="<?php echo esc_url( trim( htmlspecialchars_decode( $answer[0], ENT_QUOTES ) ) ); ?>" />
						<span class="qsm_image_caption">
							<?php echo esc_html( trim( htmlspecialchars_decode( $answer[3], ENT_QUOTES ) ) ); ?>
						</span>
						<?php
					} else {
						echo wp_kses_post( trim( do_shortcode( htmlspecialchars_decode( $answer[0], ENT_QUOTES ) ) ) );
					}
					?>
					</label>
					<?php
					echo apply_filters( 'qsm_multiple_choice_display_loop', ' ', $id, $question, $answers );
					?>
				</div>
					<?php
				}
			}
			?>
			<input type="radio" style="display: none;" name="<?php echo esc_attr( 'question' . $id ); ?>" id="<?php echo esc_attr( 'question' . $id . '_none' ); ?>" checked="checked" value="" />
			<?php
		}
		?>
	</div>
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
	$user_text_array                = $current_question->get_user_answer();
	$correct_text_array             = $current_question->get_correct_answer();
	$return_array['user_answer']    = $user_text_array;
	$return_array['correct_answer'] = $correct_text_array ;
	$return_array['user_text']      = ! empty( $user_text_array ) ? implode( '.', $user_text_array ) : '' ;
	$return_array['correct_text']   = ! empty( $correct_text_array ) ? implode( '.', $correct_text_array ) : '';
	$return_array['correct']        = $current_question->get_answer_status();
	$return_array['points']         = $current_question->get_points();
	return apply_filters( 'qmn_multiple_choice_review', $return_array, $answers );
}