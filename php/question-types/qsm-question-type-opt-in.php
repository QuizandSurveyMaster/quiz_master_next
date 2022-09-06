<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * This function displays the accept question
 *
 * @params $id The ID of the multiple choice question
 * @params $question The question that is being edited.
 * @params @answers The array that contains the answers to the question.
 *
 * @since 4.4.0
 */
function qmn_accept_display( $id, $question, $answers ) {
	global $mlwQuizMasterNext;
	$required = $mlwQuizMasterNext->pluginHelper->get_question_setting( $id, 'required' );
	if ( 0 == $required ) {
		$mlw_require_class = 'mlwRequiredAccept';
	} else {
		$mlw_require_class = '';
	}
	?>
	<div class="qmn_accept_answers">
		<input type="checkbox" id="mlwAcceptance" name="<?php echo esc_attr( 'question' . $id ); ?>" class="<?php echo esc_attr( $mlw_require_class ); ?>" />
		<label class="qsm-input-label" for="mlwAcceptance">
			<span class="qmn_accept_text">
			<?php
				$question = $mlwQuizMasterNext->pluginHelper->qsm_language_support( htmlspecialchars_decode( $question, ENT_QUOTES ), "question-description-{$id}", 'QSM Questions' );
				echo do_shortcode( wp_kses_post( $question ) );
			?>
			</span>
		</label>
	</div>
	<?php
	echo apply_filters( 'qmn_accept_display_front', '', $id, $question, $answers );
}

function qmn_opt_in_review( $id, $question, $answers ) {
	if ( isset( $_POST[ 'question' . $id ] ) && 'on' == sanitize_text_field( wp_unslash( $_POST[ 'question' . $id ] ) ) ) {
		$user_compare_text = 'opted';
	} else {
		$user_compare_text = 'not-opted';
	}
	$current_question                  = new QSM_Question_Review_Choice( $id, $question, $answers );
	$user_text_array                   = $current_question->get_user_answer();
	$correct_text_array                = $current_question->get_correct_answer();
	$return_array['user_answer']       = $user_text_array;
	$return_array['correct_answer']    = $correct_text_array;
	$return_array['user_text']         = ! empty( $user_text_array ) ? implode( '.', $user_text_array ) : '';
	$return_array['correct_text']      = ! empty( $correct_text_array ) ? implode( '.', $correct_text_array ) : '';
	$return_array['correct']           = 'opt-in';
	$return_array['points']            = '';
	$return_array['user_compare_text'] = $user_compare_text;
	return apply_filters( 'qmn_opt_in_review', $return_array, $answers );
}
