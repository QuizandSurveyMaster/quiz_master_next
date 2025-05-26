<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * This function shows the content of the multiple response question
 *
 * @params $id The ID of the multiple choice question
 * @params $question The question that is being edited.
 * @params @answers The array that contains the answers to the question.
 *
 * @since 4.4.0
 */
function qmn_multiple_response_display( $id, $question, $answers ) {
	$limit_mr_text = '';
	global $mlwQuizMasterNext;
	$required                = $mlwQuizMasterNext->pluginHelper->get_question_setting( $id, 'required' );
	$limit_multiple_response = $mlwQuizMasterNext->pluginHelper->get_question_setting( $id, 'limit_multiple_response' );
	// Retrieve display_answer_limit
	$display_answer_limit    = $mlwQuizMasterNext->pluginHelper->get_question_setting( $id, 'display_answer_limit' );
	$display_answer_limit    = intval( $display_answer_limit );

	if ( $limit_multiple_response > 0 ) {
		$limit_mr_text = 'onchange=qsmCheckMR(this,' . $limit_multiple_response . ')';
	}
	$mlw_class = '';
	if ( 0 == $required ) {
		$mlw_class = 'mlwRequiredRadio';
	}
	$mlw_class = apply_filters( 'qsm_multiple_response_classes', $mlw_class, $id );
	$new_question_title = $mlwQuizMasterNext->pluginHelper->get_question_setting( $id, 'question_title' );
	$answerEditor       = $mlwQuizMasterNext->pluginHelper->get_question_setting( $id, 'answerEditor' );
	$image_width = $mlwQuizMasterNext->pluginHelper->get_question_setting( $id, 'image_size-width' );
	$image_height = $mlwQuizMasterNext->pluginHelper->get_question_setting( $id, 'image_size-height' );
	qsm_question_title_func( $question, '', $new_question_title, $id );
	?>
	<fieldset>
		<legend></legend>
	<div class="qmn_check_answers <?php echo esc_attr( $mlw_class ); ?>">
		<?php
		if ( is_array( $answers ) ) {
			// Prepare Answer Lists
			$correct_answers   = array();
			$incorrect_answers = array();

			foreach ( $answers as $answer_index => $answer_item ) {
				if ( isset( $answer_item[2] ) && 1 == $answer_item[2] ) {
					$correct_answers[ $answer_index ] = $answer_item;
				} else {
					$incorrect_answers[ $answer_index ] = $answer_item;
				}
			}

			// Logic for Selecting Answers to Display
			$answers_to_display = array();
			$num_correct        = count( $correct_answers );
			$num_incorrect      = count( $incorrect_answers );

			if ( $display_answer_limit > 0 && ( $num_correct + $num_incorrect ) > $display_answer_limit ) {
				// Add all correct answers
				foreach ( $correct_answers as $key => $value ) {
					$answers_to_display[ $key ] = $value;
				}

				$slots_for_incorrect = $display_answer_limit - $num_correct;

				if ( $slots_for_incorrect > 0 && $num_incorrect > $slots_for_incorrect ) {
					$shuffled_incorrect_keys = array_keys( $incorrect_answers );
					shuffle( $shuffled_incorrect_keys );
					foreach ( array_slice( $shuffled_incorrect_keys, 0, $slots_for_incorrect ) as $key ) {
						$answers_to_display[ $key ] = $incorrect_answers[ $key ];
					}
				} elseif ( $slots_for_incorrect > 0 ) {
					// Add all incorrect answers if they fit
					foreach ( $incorrect_answers as $key => $value ) {
						$answers_to_display[ $key ] = $value;
					}
				}
				// If $slots_for_incorrect <= 0, only correct answers are displayed.
				// If $num_correct > $display_answer_limit, all correct answers are shown.
			} else {
				// No limit, or total answers within limit - show all
				foreach ( $correct_answers as $key => $value ) {
					$answers_to_display[ $key ] = $value;
				}
				foreach ( $incorrect_answers as $key => $value ) {
					$answers_to_display[ $key ] = $value;
				}
			}

			// Shuffle and Display
			$display_keys = array_keys( $answers_to_display );
			shuffle( $display_keys );
			
			$shuffled_answers_to_display = array();
			foreach ( $display_keys as $key ) {
				$shuffled_answers_to_display[ $key ] = $answers_to_display[ $key ];
			}

			// The existing HTML generation loop will now use $shuffled_answers_to_display
			$mlw_answer_total = 0;
			foreach ( $shuffled_answers_to_display as $answer_index => $answer ) { // Changed $answers to $shuffled_answers_to_display
				$add_label  = apply_filters( 'qsm_question_addlabel',$answer_index,$answer,count($shuffled_answers_to_display)); // count changed
				$mrq_checkbox_class = '';
				$add_label_value = isset($add_label[ $answer_index ]) ? $add_label[ $answer_index ] : '';
				if ( empty( $add_label[ $answer_index ] ) ) {
					$mrq_checkbox_class = "mrq_checkbox_class";
				}
				$mlw_answer_total++;
				if ( '' !== $answer[0] ) {
					$answer_class = apply_filters( 'qsm_answer_wrapper_class', '', $answer, $id );
					$answer_class = 'image' === $answerEditor ? $answer_class.' qmn_image_option' : '';
					?>
					<div class="qsm_check_answer <?php echo esc_attr( $answer_class.' '.$mrq_checkbox_class ); ?>">
						<input type="checkbox" class="qsm-multiple-response-input" <?php echo esc_attr( $limit_mr_text ); ?> name="question<?php echo esc_attr( $id ) . '[]'; ?>" id="question<?php echo esc_attr( $id ) . '_' . esc_attr( $mlw_answer_total ); ?>" value="<?php echo esc_attr( $answer_index ); ?>" />
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
								<img class="qsm-multiple-response-img" alt="<?php echo esc_attr( $new_question_title ); ?>" src="<?php echo esc_url( trim( htmlspecialchars_decode( $answer[0], ENT_QUOTES ) ) ); ?>"  style="<?php echo esc_attr( $size_style ); ?>" />
								<span class="qsm_image_caption">
									<?php
									$caption_text = trim( htmlspecialchars_decode( $answer[3], ENT_QUOTES ) );
									$caption_text = $mlwQuizMasterNext->pluginHelper->qsm_language_support( $caption_text, 'caption-' . $id . '-' . $answer_index, 'QSM Answers' );
									echo wp_kses_post( $add_label_value )." ".esc_html( $caption_text );
									?>
								</span>
								<?php
							} else {
								$answer_text = trim( htmlspecialchars_decode( $add_label_value." ". $answer[0], ENT_QUOTES ) );
								$answer_text = $mlwQuizMasterNext->pluginHelper->qsm_language_support( $answer_text, 'answer-' . $id . '-' . $answer_index, 'QSM Answers' );
								echo wp_kses_post( do_shortcode( $answer_text ) );
							}
							?>
						</label>
					</div>
					<?php
				}
			}
		}
		?>
	</div>
	</fieldset>
	<?php
	echo apply_filters( 'qmn_multiple_response_display_front', '', $id, $question, $answers );
}

/**
 * This function determines how the multiple response is graded,
 *
 * @params $id The ID of the multiple choice question
 * @params $question The question that is being edited.
 * @params @answers The array that contains the answers to the question.
 * @return $return_array Returns the graded question to the results page
 * @since  4.4.0
 */
function qmn_multiple_response_review( $id, $question, $answers ) {
	$current_question               = new QSM_Question_Review_Choice( $id, $question, $answers );
	$user_text_array                = $current_question->get_user_answer();
	$correct_text_array             = $current_question->get_correct_answer();
	$return_array['user_text']      = ! empty( $user_text_array ) ? implode( ', ', $user_text_array ) : '';
	$return_array['correct_text']   = ! empty( $correct_text_array ) ? implode( ', ', $correct_text_array ) : '';
	$return_array['correct']        = $current_question->get_answer_status();
	$return_array['points']         = $current_question->get_points();
	$return_array['user_answer']    = $user_text_array;
	$return_array['correct_answer'] = $correct_text_array;
	/**
	 * Hook to filter answers array
	*/
	return apply_filters( 'qmn_multiple_response_review', $return_array, $answers );
}
