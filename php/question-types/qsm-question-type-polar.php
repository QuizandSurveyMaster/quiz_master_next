<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
} 

/**
 * This function displays the fill in the blank question
 *
 * @params $id The ID of the multiple choice question
 * @params $question The question that is being edited.
 * @params @answers The array that contains the answers to the question.
 * @since  6.4.1
 */
function qmn_polar_display( $id, $question, $answers ) {
	global $mlwQuizMasterNext;
	$required       = $mlwQuizMasterNext->pluginHelper->get_question_setting( $id, 'required' );
	$autofill       = $mlwQuizMasterNext->pluginHelper->get_question_setting( $id, 'autofill' );
	$limit_text     = $mlwQuizMasterNext->pluginHelper->get_question_setting( $id, 'limit_text' );
	$autofill_att   = $autofill ? "autocomplete='off' " : '';
	$limit_text_att = $limit_text ? "maxlength='" . $limit_text . "' " : '';
	$input_text     = '';
	$first_point    = isset( $answers[0][1] ) ? intval( $answers[0][1] ) : 0;
	$second_point   = isset( $answers[1][1] ) ? intval( $answers[1][1] ) : 0;
	$is_reverse     = false;
	$check_point    = $second_point;
	if ( $first_point > $second_point ) {
		$is_reverse  = true;
		$check_point = $first_point;
	}
	$total_answer      = count( $answers );
	$id                = esc_attr( intval( $id ) );
	$answar1           = $first_point;
	$answar2           = $second_point;
	$slider_data_atts  = '';
	$slider_data_atts .= ' data-answer1=' . $answar1 . ' ';
	$slider_data_atts .= ' data-answer2=' . $answar2 . ' ';
	$slider_data_atts .= ' data-is_reverse=' . intval( $is_reverse ) . ' ';
	$slider_data_atts .= ' data-is_required=' . $required . ' ';
	if ( 0 == $required ) {
		$mlw_require_class = 'mlwRequiredText';
	} else {
		$mlw_require_class = '';
	}
	$new_question_title = $mlwQuizMasterNext->pluginHelper->get_question_setting( $id, 'question_title' );
	qsm_question_title_func( $question, '', $new_question_title, $id );

	?>
	<span class="mlw_qmn_question question-type-polar-s">
		<div class='left-polar-title'> <?php echo esc_html( $answers[0][0] ); ?> </div>
		<div class='slider-main-wrapper'>
			<input type='hidden' class='qmn_polar <?php echo esc_attr( $mlw_require_class ); ?>' id='question<?php echo esc_attr( $id ); ?>' name='question<?php echo esc_attr( $id ); ?>' value=''/>
			<div id="slider-<?php echo esc_attr( $id ); ?>" <?php echo esc_attr( $slider_data_atts ); ?> ></div>
		</div>
		<div class='right-polar-title'><?php echo esc_html( $answers[1][0] ); ?></div>
	</span>
	<?php

}

/**
 * This function determines how the fill in the blank question is graded.
 *
 * @params $id The ID of the multiple choice question
 * @params $question The question that is being edited.
 * @params @answers The array that contains the answers to the question.
 * @return $return_array Returns the graded question to the results page
 * @since  6.4.1
 */
function qmn_polar_review( $id, $question, $answers ) {
	$return_array = array(
		'points'       => 0,
		'correct'      => 'incorrect',
		'user_text'    => '',
		'correct_text' => '',
	);
	if ( strpos( $question, '%POLAR_SLIDER%' ) !== false || strpos( $question, '%polar_slider%' ) !== false ) {
		$return_array['question_text'] = str_replace( array( '%POLAR_SLIDER%', '%polar_slider%' ), array( '__________', '__________' ), do_shortcode( htmlspecialchars_decode( $question, ENT_QUOTES ) ) );
	}
	if ( isset( $_POST[ 'question' . $id ] ) ) {
		$decode_user_answer = sanitize_textarea_field( wp_unslash( $_POST[ 'question' . $id ] ) );
		$mlw_user_answer    = trim( preg_replace( '/\s\s+/', ' ', str_replace( "\n", ' ', $decode_user_answer ) ) );
	} else {
		$mlw_user_answer = ' ';
	}
	$return_array['user_text'] = $mlw_user_answer;
	$return_array['points']    = $mlw_user_answer;
	foreach ( $answers as $answer ) {
		$decode_correct_text          = $answer[1];
		$return_array['correct_text'] = trim( preg_replace( '/\s\s+/', ' ', str_replace( "\n", ' ', $decode_correct_text ) ) );
		if ( trim( $decode_correct_text ) == $return_array['user_text'] && isset( $answer[2] ) && 1 == $answer[2] ) {
			$return_array['correct'] = 'correct';
			break;
		}
	}
	/**
	 * Hook to filter answers array
	*/
	return apply_filters( 'qmn_polar_review', $return_array, $answers );
}